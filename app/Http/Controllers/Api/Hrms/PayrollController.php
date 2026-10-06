<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\EmployeePayroll;
use App\Models\EmployeeSalaryStructure;
use App\Models\AdvancePayment;
use App\Models\EmployeeLeave;
use App\Models\EmployeeAttendance;
use App\Models\Expense;
use App\Models\SalaryAdjustment;
use App\Services\CommissionService;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Process monthly payroll drafts for the authenticated user's team.
     */
    public function process(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        $request->validate([
            'month' => 'nullable|numeric|min:1|max:12',
            'year'  => 'nullable|numeric|min:2020|max:2099',
        ]);

        $month = $request->input('month', date('m'));
        $year  = $request->input('year', date('Y'));

        $formattedMonth = str_pad($month, 2, '0', STR_PAD_LEFT);
        $year = (int) $year;

        $startDate = Carbon::createFromDate($year, (int) $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $teamIds = $this->getTeamEmployeeIds();
        if (empty($teamIds)) {
            return response()->json([
                'status'  => 'success',
                'message' => 'No employees found in your team to process payroll.',
                'data'    => ['processed' => 0, 'period' => "{$formattedMonth}/{$year}"]
            ]);
        }

        $employees = User::whereIn('id', $teamIds)
            ->whereNotNull('basic_salary')
            ->where('basic_salary', '>', 0)
            ->get();

        $processedCount = 0;
        $payrolls = [];

        foreach ($employees as $employee) {
            $exists = EmployeePayroll::where('employee_id', $employee->id)
                ->where('month', $formattedMonth)
                ->where('year', $year)
                ->exists();

            if ($exists) {
                continue;
            }

            $structure = EmployeeSalaryStructure::where('employee_id', $employee->id)->first();
            $allowances = $structure && is_array($structure->allowances) ? $structure->allowances : [];
            $regularDeductions = $structure && is_array($structure->deductions) ? $structure->deductions : [];
            $basicSalary = $structure && $structure->basic_salary ? $structure->basic_salary : $employee->basic_salary;

            if ($structure) {
                $commissionService = new CommissionService();
                $commissionData = $commissionService->calculateEmployeeCommission($employee, $formattedMonth, $year);
                $monthlyTarget = (float) ($structure->monthly_target ?? 0);
                $newBusiness = $commissionData['new_business'] ?? 0;

                if (!($newBusiness >= $monthlyTarget && $monthlyTarget > 0)) {
                    if (isset($allowances['performance_incentive'])) $allowances['performance_incentive'] = 0;
                    if (isset($allowances['sales_incentive'])) $allowances['sales_incentive'] = 0;
                }
            }

            $totalAllowances = array_sum(array_map('floatval', array_values($allowances)));
            $regularDeductionTotal = array_sum(array_map('floatval', array_values($regularDeductions)));

            $advances = (float) AdvancePayment::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where('deduction_year', $year)
                ->where('deduction_month', $formattedMonth)
                ->sum('amount');

            // ─── 1. LEAVE MANAGEMENT ───────────────────────────────────────────
            $approvedLeaves = EmployeeLeave::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                          $q2->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate);
                      });
                })
                ->get();

            $paidLeaveDays = 0;
            $unpaidLeaveDays = 0;
            $halfDayPaidLeaves = 0;
            $halfDayUnpaidLeaves = 0;

            foreach ($approvedLeaves as $leave) {
                $from = Carbon::parse($leave->start_date)->max($startDate);
                $to = Carbon::parse($leave->end_date)->min($endDate);
                $isHalfDay = $this->leaveIsHalfDay($leave);
                $isPaid = $this->leaveIsPaid($leave);

                for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                    if ($d->isSunday()) continue;

                    if ($isHalfDay) {
                        if ($isPaid) $halfDayPaidLeaves += 1;
                        else $halfDayUnpaidLeaves += 1;
                    } else {
                        if ($isPaid) $paidLeaveDays += 1;
                        else $unpaidLeaveDays += 1;
                    }
                }
            }

            // Attendance for the month
            $attendances = EmployeeAttendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();

            $presentDays = 0;
            $halfDayAttendanceDays = 0;
            $lateDays = 0;
            foreach ($attendances as $att) {
                $attDate = Carbon::parse($att->date);
                if ($attDate->isSunday()) continue;
                if ($att->status === 'leave' || $att->status === 'weekOff' || $att->status === 'holiday') continue;
                if ($att->status === 'absent') continue;

                if (in_array($att->status, ['punch_out', 'short_leave', 'late']) && $att->check_in) {
                    $presentDays += 1;
                } elseif (in_array($att->status, ['half_day', 'punch_in'])) {
                    $halfDayAttendanceDays += 1;
                }

                if ((int) $att->late_minutes > 0) {
                    $lateDays += 1;
                }
            }

            // Daily rate from gross (basic + allowances)
            $grossForRate = (float) $basicSalary + $totalAllowances;
            $dailyRate = $daysInMonth > 0 ? $grossForRate / $daysInMonth : 0;

            $earnedFromPresent = $dailyRate * $presentDays;
            $earnedFromHalfDayAttendance = $dailyRate * 0.5 * $halfDayAttendanceDays;
            $paidLeaveAmount = $dailyRate * $paidLeaveDays;
            $halfDayPaidAmount = $dailyRate * 0.5 * $halfDayPaidLeaves;
            $unpaidLeaveDeduction = ($dailyRate * $unpaidLeaveDays) + ($dailyRate * 0.5 * $halfDayUnpaidLeaves);

            // ─── 2. EXPENSE MANAGEMENT ─────────────────────────────────────────
            $approvedExpenses = Expense::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
                ->get();
            $expenseReimbursement = (float) $approvedExpenses->sum('amount');

            $earnedSalary = $earnedFromPresent + $earnedFromHalfDayAttendance + $paidLeaveAmount + $halfDayPaidAmount;
            $grossPay = $earnedSalary + $expenseReimbursement;
            $totalDeductions = $regularDeductionTotal + $advances + $unpaidLeaveDeduction;
            $netPay = max(0, $grossPay - $totalDeductions);

            $deductionsBreakdown = $regularDeductions;
            if ($advances > 0) $deductionsBreakdown['salary_advance'] = round($advances, 2);
            if ($unpaidLeaveDeduction > 0) $deductionsBreakdown['unpaid_leave'] = round($unpaidLeaveDeduction, 2);

            $allowancesBreakdown = $allowances;
            if ($expenseReimbursement > 0) $allowancesBreakdown['expense_reimbursement'] = round($expenseReimbursement, 2);

            $payrollData = [
                'employee_id' => $employee->id,
                'month' => $formattedMonth,
                'year' => $year,
                'basic_salary' => round($basicSalary, 2),
                'allowances_breakdown' => $allowancesBreakdown,
                'deductions' => round($totalDeductions, 2),
                'deductions_breakdown' => $deductionsBreakdown,
                'bonuses' => 0,
                'commissions' => 0,
                'commissions_breakdown' => [],
                'gross_pay' => round($grossPay, 2),
                'net_pay' => round($netPay, 2),
                'status' => 'pending',
            ];

            EmployeePayroll::create($payrollData);

            if ($advances > 0) {
                AdvancePayment::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->where('deduction_year', $year)
                    ->where('deduction_month', $formattedMonth)
                    ->update(['status' => 'deducted']);
            }

            $payrolls[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'period' => "{$formattedMonth}/{$year}",
                'gross_pay' => round($grossPay, 2),
                'total_deductions' => round($totalDeductions, 2),
                'net_pay' => round($netPay, 2),
            ];

            $processedCount++;
        }

        if ($processedCount > 0) {
            $message = "Successfully generated {$processedCount} payroll drafts for {$formattedMonth}/{$year}.";
        } else {
            $message = "No new payrolls to generate for this period. Either all employees already have drafts, or they do not have a basic salary set in Salary Management.";
        }

        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => [
                'processed' => $processedCount,
                'period'    => "{$formattedMonth}/{$year}",
                'payrolls'  => $payrolls,
            ]
        ]);
    }

    private function leaveIsPaid($leave)
    {
        $unpaidMarkers = ['unpaid', 'loss of pay', 'lop', 'without pay', 'no pay'];
        $name = strtolower((string) $leave->type);

        if ($leave->leaveCategory && $leave->leaveCategory->name) {
            $name = strtolower($leave->leaveCategory->name);
        }

        foreach ($unpaidMarkers as $marker) {
            if (str_contains($name, $marker)) return false;
        }

        return true;
    }

    private function getPartnerId()
    {
        $user = auth('hrms_api')->user();
        return $user->role === 'employee' ? $user->parent_id : $user->id;
    }

    private function getTeamEmployeeIds($viewAnyPermission = null)
    {
        $user = auth('hrms_api')->user();

        // Partner or User with viewAny permission sees ALL employees
        if ($user->role === 'partner' || ($viewAnyPermission && $user->canAccess($viewAnyPermission))) {
            return \App\Models\User::where('parent_id', $this->getPartnerId())
                ->where('role', 'employee')
                ->pluck('id')
                ->toArray();
        }

        if ($viewAnyPermission) {
            $module = str_replace('_viewAny', '', $viewAnyPermission);

            if ($user->canAccess($module . '_viewTeam') || $user->canAccess($module . '_viewteam')) {
                return $user->getTeamIds();
            }

            if ($user->canAccess($module . '_viewOwn') || $user->canAccess($module . '_viewown')) {
                return [$user->id];
            }
        }

        // Fallback if no specific permission string provided or no specific match
        return $user->getTeamIds();
    }

    private function leaveIsHalfDay($leave)
    {
        if (strtolower((string) $leave->type) === 'half_day') return true;
        if ($leave->leaveCategory && str_contains(strtolower($leave->leaveCategory->name), 'half')) return true;
        return false;
    }

    /**
     * List pending payroll drafts for the authenticated user's team.
     */
    public function drafts(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $this->getTeamEmployeeIds();

        $query = EmployeePayroll::whereIn('employee_id', $teamIds)
            ->where('status', 'pending')
            ->with('employee');

        if ($request->filled('month')) {
            $query->where('month', str_pad($request->input('month'), 2, '0', STR_PAD_LEFT));
        }
        if ($request->filled('year')) {
            $query->where('year', $request->input('year'));
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        $query->orderBy('year', 'desc')->orderBy('month', 'desc');

        $perPage = (int) $request->input('per_page', 15);
        $drafts = $query->paginate($perPage);

        $drafts->through(function ($draft) {
            $totalAllowances = 0;
            if (is_array($draft->allowances_breakdown)) {
                $totalAllowances = array_sum(array_map('floatval', $draft->allowances_breakdown));
            }

            $totalDeductions = 0;
            if (is_array($draft->deductions_breakdown)) {
                $totalDeductions = array_sum(array_map('floatval', $draft->deductions_breakdown));
            }

            return [
                'id'                => $draft->id,
                'employee'          => $draft->employee ? [
                    'id'    => $draft->employee->id,
                    'name'  => $draft->employee->name,
                    'email' => $draft->employee->email,
                ] : null,
                'month'             => $draft->month,
                'year'              => $draft->year,
                'period'            => date('F', mktime(0, 0, 0, (int) $draft->month, 1)) . ' ' . $draft->year,
                'basic_salary'      => (float) $draft->basic_salary,
                'allowances'        => $draft->allowances_breakdown,
                'total_allowances'  => round($totalAllowances, 2),
                'deductions'        => $draft->deductions_breakdown,
                'total_deductions'  => round($totalDeductions, 2),
                'bonuses'           => (float) $draft->bonuses,
                'gross_pay'         => (float) $draft->gross_pay,
                'net_pay'           => (float) $draft->net_pay,
                'status'            => $draft->status,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $drafts
        ]);
    }

    /**
     * View a single payroll (with recalculated month salary data).
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $this->getTeamEmployeeIds();

        $payroll = EmployeePayroll::with(['employee', 'salaryAdjustments'])->find($id);
        if (!$payroll) {
            return response()->json(['status' => 'error', 'message' => 'Payroll not found.'], 404);
        }
        if (!in_array($payroll->employee_id, $teamIds)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $monthSalaryData = $this->calculateMonthSalary($payroll);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'payroll'         => $payroll,
                'month_salary'    => $monthSalaryData,
            ]
        ]);
    }

    /**
     * Adjust a pending payroll draft (mirrors the Payroll Drafts screen).
     */
    public function adjust(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $this->getTeamEmployeeIds();

        $payroll = EmployeePayroll::with('salaryAdjustments')->find($id);
        if (!$payroll) {
            return response()->json(['status' => 'error', 'message' => 'Payroll not found.'], 404);
        }
        if (!in_array($payroll->employee_id, $teamIds)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'basic_salary'      => 'required|numeric|min:0',
            'bonuses'           => 'required|numeric|min:0',
            'allowances'        => 'nullable|array',
            'allowances.*'      => 'nullable|numeric|min:0',
            'deductions'        => 'nullable|array',
            'deductions.*'      => 'nullable|numeric|min:0',
            'present_days'      => 'required|integer|min:0|max:31',
            'half_days'         => 'required|integer|min:0|max:31',
            'paid_leave_days'   => 'required|integer|min:0|max:31',
            'unpaid_leave_days' => 'required|integer|min:0|max:31',
            'absent_days'       => 'required|integer|min:0|max:31',
            'advance_pay'       => 'required|numeric|min:0',
        ]);

        $allowances = [];
        foreach (($request->input('allowances', []) ?: []) as $key => $val) {
            $allowances[$key] = round((float) $val, 2);
        }
        $deductions = [];
        foreach (($request->input('deductions', []) ?: []) as $key => $val) {
            $deductions[$key] = round((float) $val, 2);
        }

        $editBasicSalary = (float) $request->input('basic_salary');
        $editBonuses = (float) $request->input('bonuses');
        $editAdvancePay = (float) $request->input('advance_pay');

        $origBasic = (float) $payroll->basic_salary;
        $origAllowances = is_array($payroll->allowances_breakdown) ? $payroll->allowances_breakdown : [];
        $origBonuses = (float) $payroll->bonuses;
        $origDeductions = is_array($payroll->deductions_breakdown) ? $payroll->deductions_breakdown : [];
        $monthData = $this->calculateMonthSalary($payroll);

        $this->storeAdjustment($payroll->id, 'basic_salary', null, $origBasic, $editBasicSalary);

        foreach ($allowances as $key => $val) {
            $orig = (float) ($origAllowances[$key] ?? 0);
            if (abs($orig - $val) > 0.001) {
                $this->storeAdjustment($payroll->id, 'allowance', $key, $orig, $val);
            }
        }

        if (abs($origBonuses - $editBonuses) > 0.001) {
            $this->storeAdjustment($payroll->id, 'bonus', null, $origBonuses, $editBonuses);
        }

        foreach ($deductions as $key => $val) {
            if ($key === 'salary_advance') continue;
            $orig = (float) ($origDeductions[$key] ?? 0);
            if (abs($orig - $val) > 0.001) {
                $this->storeAdjustment($payroll->id, 'deduction', $key, $orig, $val);
            }
        }

        $this->storeAdjustment($payroll->id, 'present_days', null, $monthData['present_days'], (int) $request->input('present_days'));
        $this->storeAdjustment($payroll->id, 'half_days', null, $monthData['half_day_days'], (int) $request->input('half_days'));
        $this->storeAdjustment($payroll->id, 'paid_leave_days', null, $monthData['paid_leave_days'], (int) $request->input('paid_leave_days'));
        $this->storeAdjustment($payroll->id, 'unpaid_leave_days', null, $monthData['unpaid_leave_days'], (int) $request->input('unpaid_leave_days'));
        $this->storeAdjustment($payroll->id, 'absent_days', null, $monthData['absent_days'], (int) $request->input('absent_days'));

        $advanceOrig = (float) ($payroll->deductions_breakdown['salary_advance'] ?? 0);
        $this->storeAdjustment($payroll->id, 'advance_pay', null, $advanceOrig, $editAdvancePay);

        if ($editAdvancePay > 0) {
            $deductions['salary_advance'] = round($editAdvancePay, 2);
        } else {
            unset($deductions['salary_advance']);
        }
        $totalAllowances = array_sum($allowances);

        $rateBase = array_diff_key($allowances, ['expense_reimbursement' => 0]);
        $rateBaseTotal = array_sum(array_map('floatval', $rateBase));
        $daysInMonth = max(1, $monthData['days_in_month']);
        $dailyRate = round(($editBasicSalary + $rateBaseTotal) / $daysInMonth, 2);

        $thisMonthSalary = ($dailyRate * (int) $request->input('present_days'))
            + ($dailyRate * 0.5 * (int) $request->input('half_days'))
            + ($dailyRate * (int) $request->input('paid_leave_days'));

        $unpaidLeaveDeduction = $dailyRate * (int) $request->input('unpaid_leave_days');
        $absentDeduction = $dailyRate * (int) $request->input('absent_days');

        if ($unpaidLeaveDeduction > 0) {
            $deductions['unpaid_leave'] = round($unpaidLeaveDeduction, 2);
        } else {
            unset($deductions['unpaid_leave']);
        }
        if ($absentDeduction > 0) {
            $deductions['absent'] = round($absentDeduction, 2);
        } else {
            unset($deductions['absent']);
        }

        $grossPay = round($thisMonthSalary + $totalAllowances + $editBonuses, 2);
        $totalDeductions = array_sum($deductions);
        $netPay = round(max(0, $grossPay - $totalDeductions), 2);

        $payroll->update([
            'basic_salary'         => round($editBasicSalary, 2),
            'allowances_breakdown' => $allowances,
            'bonuses'              => round($editBonuses, 2),
            'deductions'           => $totalDeductions,
            'deductions_breakdown' => $deductions,
            'gross_pay'            => $grossPay,
            'net_pay'              => $netPay,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => "Payroll adjustments stored for {$payroll->employee->name}.",
            'data'    => [
                'gross_pay' => $grossPay,
                'net_pay'   => $netPay,
                'payroll'   => $payroll->fresh(),
            ]
        ]);
    }

    /**
     * Approve / mark a payroll draft as paid (mirrors the Payroll Approval screen).
     */
    public function approve(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $this->getTeamEmployeeIds();

        $payroll = EmployeePayroll::find($id);
        if (!$payroll) {
            return response()->json(['status' => 'error', 'message' => 'Payroll not found.'], 404);
        }
        if (!in_array($payroll->employee_id, $teamIds)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $payroll->update(['status' => 'paid']);

        return response()->json([
            'status'  => 'success',
            'message' => "Payroll marked as Paid for {$payroll->employee->name}.",
            'data'    => ['payroll' => $payroll->fresh()]
        ]);
    }

    private function storeAdjustment($payrollId, $type, $key, $original, $adjusted)
    {
        SalaryAdjustment::updateOrCreate(
            [
                'employee_payroll_id' => $payrollId,
                'adjustment_type'      => $type,
                'field_key'           => $key,
            ],
            [
                'original_amount' => round($original, 2),
                'adjusted_amount' => round($adjusted, 2),
            ]
        );
    }

    private function calculateMonthSalary($payroll)
    {
        $month = (int) $payroll->month;
        $year = (int) $payroll->year;

        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $allowances = is_array($payroll->allowances_breakdown) ? $payroll->allowances_breakdown : [];
        $allowances = array_diff_key($allowances, ['expense_reimbursement' => 0]);
        $totalAllowances = array_sum(array_map('floatval', array_values($allowances)));
        $grossForRate = (float) $payroll->basic_salary + $totalAllowances;
        $dailyRate = $daysInMonth > 0 ? $grossForRate / $daysInMonth : 0;

        $attendances = EmployeeAttendance::where('employee_id', $payroll->employee_id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        $presentDays = 0;
        $halfDayAttendanceDays = 0;
        $absentDays = 0;
        foreach ($attendances as $att) {
            $attDate = Carbon::parse($att->date);
            if ($attDate->isSunday()) continue;
            if (in_array($att->status, ['leave', 'weekOff', 'holiday'])) continue;
            if ($att->status === 'absent') {
                $absentDays += 1;
                continue;
            }
            if (in_array($att->status, ['punch_out', 'short_leave', 'late']) && $att->check_in) {
                $presentDays += 1;
            } elseif (in_array($att->status, ['half_day', 'punch_in'])) {
                $halfDayAttendanceDays += 1;
            }
        }

        $approvedLeaves = EmployeeLeave::where('employee_id', $payroll->employee_id)
            ->where('status', 'approved')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q2) use ($startDate, $endDate) {
                      $q2->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate);
                  });
            })
            ->get();

        $paidLeaveDays = 0;
        $unpaidLeaveDays = 0;
        $halfDayPaidLeaves = 0;
        foreach ($approvedLeaves as $leave) {
            $from = Carbon::parse($leave->start_date)->max($startDate);
            $to = Carbon::parse($leave->end_date)->min($endDate);
            $isHalfDay = $this->leaveIsHalfDay($leave);
            $isPaid = $this->leaveIsPaid($leave);

            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                if ($d->isSunday()) continue;
                if ($isHalfDay) {
                    if ($isPaid) $halfDayPaidLeaves += 1;
                } else {
                    if ($isPaid) $paidLeaveDays += 1;
                    else $unpaidLeaveDays += 1;
                }
            }
        }

        $monthSalary = ($dailyRate * $presentDays)
            + ($dailyRate * 0.5 * $halfDayAttendanceDays)
            + ($dailyRate * $paidLeaveDays)
            + ($dailyRate * 0.5 * $halfDayPaidLeaves);

        return [
            'amount'               => round($monthSalary, 2),
            'present_days'         => $presentDays,
            'half_day_days'        => $halfDayAttendanceDays,
            'paid_leave_days'      => $paidLeaveDays,
            'unpaid_leave_days'    => $unpaidLeaveDays,
            'absent_days'          => $absentDays,
            'half_day_paid_leaves' => $halfDayPaidLeaves,
            'daily_rate'           => round($dailyRate, 2),
            'days_in_month'        => $daysInMonth,
        ];
    }


       public function reports(Request $request): JsonResponse
    {
       
        $user = auth('hrms_api')->user();

        $query = \App\Models\AdvancePayment::with('employee')
            ->where('partner_id', $user->id);

        // Restrict to own records for non-partners without viewAny permission
        if (!$user->isPartner() && !$user->canAccess('advance_payments_viewAny')) {
            $query->where('employee_id', $user->id);
        } elseif ($request->filled('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('deduction_month')) {
            $query->where('deduction_month', str_pad($request->input('deduction_month'), 2, '0', STR_PAD_LEFT));
        }
        if ($request->filled('deduction_year')) {
            $query->where('deduction_year', $request->input('deduction_year'));
        }
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }

        $perPage = (int) $request->input('per_page', 15);
        $advances = $query->orderBy('created_at', 'desc')->paginate($perPage);
      

        // Summary aggregations (across the same filtered set, ignoring pagination)
        $summaryBase = (clone $query)->get();
        $summary = [
            'total_count'        => $summaryBase->count(),
            'total_amount'       => round($summaryBase->sum('amount'), 2),
            'total_pending'      => round($summaryBase->where('status', 'pending')->sum('amount'), 2),
            'total_approved'     => round($summaryBase->where('status', 'approved')->sum('amount'), 2),
            'total_deducted'     => round($summaryBase->where('status', 'deducted')->sum('amount'), 2),
            'count_pending'      => $summaryBase->where('status', 'pending')->count(),
            'count_approved'     => $summaryBase->where('status', 'approved')->count(),
            'count_deducted'     => $summaryBase->where('status', 'deducted')->count(),
        ];

        $advances->through(function ($adv) {
            return [
                'id'              => $adv->id,
                'employee'        => $adv->employee ? [
                    'id'    => $adv->employee->id,
                    'name'  => $adv->employee->name,
                    'email' => $adv->employee->email,
                ] : null,
                'amount'          => (float) $adv->amount,
                'reason'          => $adv->reason,
                'status'          => $adv->status,
                'is_deducted'     => (bool) $adv->is_deducted,
                'deduction_month' => $adv->deduction_month,
                'deduction_year'  => $adv->deduction_year,
                'request_date'    => $adv->request_date,
            ];
        });

        return response()->json([
            'status'  => 'success',
            'data'    => $advances,
            'summary' => $summary,
        ]);
    }
}
