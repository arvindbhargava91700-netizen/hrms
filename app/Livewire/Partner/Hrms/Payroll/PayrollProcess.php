<?php

namespace App\Livewire\Partner\Hrms\Payroll;

use Livewire\Component;
use App\Models\User;
use App\Models\EmployeePayroll;
use Carbon\Carbon;

use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;

class PayrollProcess extends Component
{
    use \App\Livewire\Partner\Hrms\HasPartnerId, HasHrmsFilters;
    
    public $month;
    public $year;

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('payroll_viewAny') ||
            auth()->user()->canAccess('payroll_viewBranch') ||
            auth()->user()->canAccess('payroll_viewTeam') ||
            auth()->user()->canAccess('payroll_viewOwn'),
            403
        );
        
        $this->month = date('m');
        $this->year = date('Y');
    }

    public function processPayroll_old()
    {
       
        $this->validate([
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2020|max:2099',
        ]);

        $formattedMonth = str_pad($this->month, 2, '0', STR_PAD_LEFT);
        $partnerId = $this->getPartnerId();
        
        // Get all employees for this partner/manager who have a basic salary set
        $query = User::whereNotNull('basic_salary')
            ->where('basic_salary', '>', 0);
        
        $query = $this->applyHrmsFilters($query, 'id', 'payroll_viewAny');
        $employees = $query->get();
            
        $processedCount = 0;
        
        foreach ($employees as $employee) {
            // Check if payroll already exists for this month/year
            $exists = EmployeePayroll::where('employee_id', $employee->id)
                ->where('month', $formattedMonth)
                ->where('year', $this->year)
                ->exists();
                
            if (!$exists) {
                // Calculate Advances
                $advances = \App\Models\AdvancePayment::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->where('deduction_year', $this->year)
                    ->where('deduction_month', $formattedMonth)
                    ->sum('amount');
                    
                $structure = \App\Models\EmployeeSalaryStructure::where('employee_id', $employee->id)->first();
                $allowances = [];
                $regularDeductions = [];
                $basic_salary = $employee->basic_salary;

                if ($structure) {
                    $basic_salary = $structure->basic_salary ?: $basic_salary;
                    $allowances = is_array($structure->allowances) ? $structure->allowances : [];
                    $regularDeductions = is_array($structure->deductions) ? $structure->deductions : [];
                    
                    // TARGET CHECK
                    $commissionService = new \App\Services\CommissionService();
                    $commissionData = $commissionService->calculateEmployeeCommission($employee, $formattedMonth, $this->year);
                    
                    $monthlyTarget = (float)($structure->monthly_target ?? 0);
                    $newBusiness = $commissionData['new_business'] ?? 0;
                    
                    $targetAchieved = ($newBusiness >= $monthlyTarget && $monthlyTarget > 0);
                    
                    if (!$targetAchieved) {
                        // Remove/zero-out Performance Incentive & Sales Incentive
                        if (isset($allowances['performance_incentive'])) {
                            $allowances['performance_incentive'] = 0;
                        }
                        if (isset($allowances['sales_incentive'])) {
                            $allowances['sales_incentive'] = 0;
                        }
                    }
                }

                $totalAllowances = 0;
                foreach ($allowances as $val) {
                    $totalAllowances += floatval($val ?: 0);
                }

                $totalRegularDeductions = 0;
                foreach ($regularDeductions as $val) {
                    $totalRegularDeductions += floatval($val ?: 0);
                }

                $totalDeductions = $totalRegularDeductions + $advances;
                
                $deductionsBreakdown = $regularDeductions;
                if ($advances > 0) {
                    $deductionsBreakdown['salary_advance'] = $advances;
                }

                $grossPay = $basic_salary + $totalAllowances;
                $netPay = max(0, $grossPay - $totalDeductions);

                EmployeePayroll::create([
                    'employee_id' => $employee->id,
                    'month' => $formattedMonth,
                    'year' => $this->year,
                    'basic_salary' => $basic_salary,
                    'allowances_breakdown' => $allowances,
                    'deductions' => $totalDeductions,
                    'deductions_breakdown' => $deductionsBreakdown,
                    'bonuses' => 0,
                    'commissions' => 0,
                    'commissions_breakdown' => [],
                    'gross_pay' => $grossPay,
                    'net_pay' => $netPay,
                    'status' => 'pending'
                ]);

                if ($advances > 0) {
                    \App\Models\AdvancePayment::where('employee_id', $employee->id)
                        ->where('status', 'approved')
                        ->where('deduction_year', $this->year)
                        ->where('deduction_month', $formattedMonth)
                        ->update(['status' => 'deducted']);
                }

                $processedCount++;
            }
        }
        if ($processedCount > 0) {
            session()->flash('message', "Successfully generated {$processedCount} payroll drafts for {$formattedMonth}/{$this->year}. You can now review them in Payroll Drafts.");
        } else {
            session()->flash('info', "No new payrolls to generate for this period. Either all employees already have drafts, or they do not have a basic salary set in Salary Management.");
        }
    }

    public function processPayroll()
    {
        $this->validate([
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric|min:2020|max:2099',
        ]);

        $formattedMonth = str_pad($this->month, 2, '0', STR_PAD_LEFT);
        $year = $this->year;

        $startDate = Carbon::createFromDate((int)$year, (int)$this->month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $query = User::whereNotNull('basic_salary')->where('basic_salary', '>', 0);
        $query = $this->applyHrmsFilters($query, 'id', 'payroll_viewAny');
        $employees = $query->get();
        $processedCount=0;

        $payrolls = [];

        foreach ($employees as $employee) {
            $exists = EmployeePayroll::where('employee_id', $employee->id)
                ->where('month', $formattedMonth)
                ->where('year', $year)
                ->exists();

            if ($exists) {
                continue;
            }

            $structure = \App\Models\EmployeeSalaryStructure::where('employee_id', $employee->id)->first();
            $allowances = $structure && is_array($structure->allowances) ? $structure->allowances : [];
            $regularDeductions = $structure && is_array($structure->deductions) ? $structure->deductions : [];
            $basicSalary = $structure && $structure->basic_salary ? $structure->basic_salary : $employee->basic_salary;

            if ($structure) {
                $commissionService = new \App\Services\CommissionService();
                $commissionData = $commissionService->calculateEmployeeCommission($employee, $formattedMonth, $year);
                $monthlyTarget = (float)($structure->monthly_target ?? 0);
                $newBusiness = $commissionData['new_business'] ?? 0;

                if (!($newBusiness >= $monthlyTarget && $monthlyTarget > 0)) {
                    if (isset($allowances['performance_incentive'])) $allowances['performance_incentive'] = 0;
                    if (isset($allowances['sales_incentive'])) $allowances['sales_incentive'] = 0;
                }
            }

            $totalAllowances = array_sum(array_map('floatval', array_values($allowances)));
            $regularDeductionTotal = array_sum(array_map('floatval', array_values($regularDeductions)));

            $advances = (float) \App\Models\AdvancePayment::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where('deduction_year', $year)
                ->where('deduction_month', $formattedMonth)
                ->sum('amount');

            // ─── 1. LEAVE MANAGEMENT ───────────────────────────────────────────
            $approvedLeaves = \App\Models\EmployeeLeave::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                          $q2->where('start_date', '<=', $startDate)->where('end_date', '>=', $endDate);
                      });
                })
                ->get();

            $paidLeaveDays = 0;         // eligible paid leave (full days)
            $unpaidLeaveDays = 0;       // unpaid / loss-of-pay (full days)
            $halfDayPaidLeaves = 0;     // half-day leaves under a paid category
            $halfDayUnpaidLeaves = 0;   // half-day leaves under an unpaid category

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

            // Attendance for the month (used to compute salary on actual present days)
            $attendances = \App\Models\EmployeeAttendance::where('employee_id', $employee->id)
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
            $approvedExpenses = \App\Models\Expense::where('employee_id', $employee->id)
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

            $payrolls[] = [
                'employee' => ['id' => $employee->id, 'name' => $employee->name, 'email' => $employee->email],
                'period' => "{$formattedMonth}/{$year}",
                'salary' => [
                    'basic_salary' => round($basicSalary, 2),
                    'allowances' => $allowances,
                    'total_allowances' => round($totalAllowances, 2),
                    'gross_for_rate' => round($grossForRate, 2),
                    'days_in_month' => $daysInMonth,
                    'daily_rate' => round($dailyRate, 2),
                ],
                'leave_management' => [
                    'approved_leaves' => $approvedLeaves->count(),
                    'paid_leave_days' => $paidLeaveDays,
                    'unpaid_leave_days' => $unpaidLeaveDays,
                    'half_day_paid_leaves' => $halfDayPaidLeaves,
                    'half_day_unpaid_leaves' => $halfDayUnpaidLeaves,
                    'paid_leave_amount' => round($paidLeaveAmount, 2),
                    'half_day_paid_amount' => round($halfDayPaidAmount, 2),
                    'unpaid_leave_deduction' => round($unpaidLeaveDeduction, 2),
                ],
                'attendance' => [
                    'present_days' => $presentDays,
                    'half_day_attendance_days' => $halfDayAttendanceDays,
                    'late_days' => $lateDays,
                ],
                'expense_management' => [
                    'approved_expenses' => $approvedExpenses->count(),
                    'expense_reimbursement' => round($expenseReimbursement, 2),
                    'expenses' => $approvedExpenses->map(fn($e) => ['id' => $e->id, 'category' => $e->category, 'amount' => (float) $e->amount, 'date' => $e->date])->values()->toArray(),
                ],
                'computation' => [
                    'earned_salary' => round($earnedSalary, 2),
                    'expense_reimbursement' => round($expenseReimbursement, 2),
                    'gross_pay' => round($grossPay, 2),
                    'total_deductions' => round($totalDeductions, 2),
                    'net_pay' => round($netPay, 2),
                ],
                'calculation' => [
                    'earnings_added' => [
                        'basic_salary' => [
                            'amount' => round((float) $basicSalary, 2),
                            'note' => 'Monthly basic salary',
                        ],
                        'allowances' => [
                            'amount' => round($totalAllowances, 2),
                            'note' => 'Sum of all allowances',
                            'breakdown' => $allowances,
                        ],
                        'present_days_salary' => [
                            'amount' => round($earnedFromPresent, 2),
                            'note' => "Daily rate {$dailyRate} x present days {$presentDays}",
                        ],
                        'half_day_attendance_salary' => [
                            'amount' => round($earnedFromHalfDayAttendance, 2),
                            'note' => "50% of daily rate {$dailyRate} x half-day attendance {$halfDayAttendanceDays}",
                        ],
                        'paid_leave_salary' => [
                            'amount' => round($paidLeaveAmount, 2),
                            'note' => "Daily rate {$dailyRate} x paid leave days {$paidLeaveDays}",
                        ],
                        'half_day_paid_leave_salary' => [
                            'amount' => round($halfDayPaidAmount, 2),
                            'note' => "50% of daily rate {$dailyRate} x half-day paid leaves {$halfDayPaidLeaves}",
                        ],
                        'expense_reimbursement' => [
                            'amount' => round($expenseReimbursement, 2),
                            'note' => 'Sum of approved expense claims',
                            'breakdown' => $approvedExpenses->map(fn($e) => ['category' => $e->category, 'amount' => (float) $e->amount, 'date' => $e->date])->values()->toArray(),
                        ],
                    ],
                    'deductions_removed' => [
                        'regular_deductions' => [
                            'amount' => round($regularDeductionTotal, 2),
                            'note' => 'Sum of fixed deductions (PF, ESI, PT, TDS, etc.)',
                            'breakdown' => $regularDeductions,
                        ],
                        'advance_payments' => [
                            'amount' => round($advances, 2),
                            'note' => 'Approved advances deducted this month',
                        ],
                        'unpaid_leave_deduction' => [
                            'amount' => round($unpaidLeaveDeduction, 2),
                            'note' => "Daily rate {$dailyRate} x unpaid leave days {$unpaidLeaveDays} + (50% daily rate x half-day unpaid leaves {$halfDayUnpaidLeaves})",
                        ],
                    ],
                    'summary_formula' => [
                        'total_added' => round($grossPay, 2),
                        'total_deducted' => round($totalDeductions, 2),
                        'net_pay' => round($netPay, 2),
                        'formula' => 'Net Pay = Gross Pay (Basic + Allowances + Present Days + Paid Leaves + Half Days + Expense Reimbursement) - Total Deductions (Regular + Advances + Unpaid Leaves)',
                    ],
                ],
                'payroll_data' => [
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
                ],
            ];

            EmployeePayroll::create([
                'employee_id' => $employee->id,
                'month' => $formattedMonth,
                'year' => $year,
                'basic_salary' => $basicSalary,
                'allowances_breakdown' => $allowancesBreakdown,
                'deductions' => $totalDeductions,
                'deductions_breakdown' => $deductionsBreakdown,
                'bonuses' => 0,
                'commissions' => 0,
                'commissions_breakdown' => [],
                'gross_pay' => $grossPay,
                'net_pay' => $netPay,
                'status' => 'pending',
            ]);

            if ($advances > 0) {
                \App\Models\AdvancePayment::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->where('deduction_year', $year)
                    ->where('deduction_month', $formattedMonth)
                    ->update(['status' => 'deducted']);
            }

            $processedCount++;
        }

        // dd([
        //     'summary' => ['generated' => count($payrolls), 'period' => "{$formattedMonth}/{$year}"],
        //     'payrolls' => $payrolls,
        // ]);

               if ($processedCount > 0) {
            session()->flash('message', "Successfully generated {$processedCount} payroll drafts for {$formattedMonth}/{$this->year}. You can now review them in Payroll Drafts.");
        } else {
            session()->flash('info', "No new payrolls to generate for this period. Either all employees already have drafts, or they do not have a basic salary set in Salary Management.");
        }
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

    private function leaveIsHalfDay($leave)
    {
        if (strtolower((string) $leave->type) === 'half_day') return true;
        if ($leave->leaveCategory && str_contains(strtolower($leave->leaveCategory->name), 'half')) return true;
        return false;
    }

    public function render()
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[str_pad($m, 2, '0', STR_PAD_LEFT)] = Carbon::create()->month($m)->format('F');
        }
        
        $years = range(date('Y') - 1, date('Y') + 1);

        return view('livewire.partner.hrms.payroll.payroll-process', [
            'months' => $months,
            'years' => $years
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Process Payroll',
            'pageSubtitle' => 'Generate monthly payroll drafts',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
