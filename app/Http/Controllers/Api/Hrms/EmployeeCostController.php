<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\Designation;
use App\Models\EmployeeSalaryStructure;
use App\Models\EmployeePayroll;
use App\Models\EmployeeAttendance;
use App\Models\CommissionPayout;
use Carbon\Carbon;

class EmployeeCostController extends Controller
{
    /**
     * Helper to get partner_id for HRMS API authenticated user
     */
    private function getPartnerId()
    {
        $user = auth('hrms_api')->user();
        return $user->isPartner() ? $user->id : $user->parent_id;
    }

    /**
     * Get Employee Cost Analytics & Reporting
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $partnerId = $this->getPartnerId();

        $year = $request->input('year', date('Y'));
        $month = $request->input('month', 'all');
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $designationId = $request->input('designation_id');
        $employeeId = $request->input('employee_id');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        // 1. Base Employees Query
        $employeesQuery = User::where(function ($q) use ($partnerId) {
            $q->where('parent_id', $partnerId)
              ->orWhere('id', $partnerId);
        })->whereIn('role', ['employee', 'manager']);

        // Apply filters
        if (!empty($branchId)) {
            $employeesQuery->where('branch_id', $branchId);
        }
        if (!empty($departmentId)) {
            $employeesQuery->where('department_id', $departmentId);
        }
        if (!empty($designationId)) {
            $employeesQuery->where('designation_id', $designationId);
        }
        if (!empty($employeeId)) {
            $employeesQuery->where('id', $employeeId);
        }
        if (!empty($search)) {
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $allFilteredEmployees = (clone $employeesQuery)->with(['department', 'branch', 'designation'])->get();
        $employeeIds = $allFilteredEmployees->pluck('id')->toArray();

        // 2. Salary Structures
        $salaryStructures = EmployeeSalaryStructure::whereIn('employee_id', $employeeIds)
            ->get()
            ->keyBy('employee_id');

        // 3. Payrolls
        $payrollsQuery = EmployeePayroll::whereIn('employee_id', $employeeIds)
            ->where('year', $year);

        if ($month !== 'all' && !empty($month)) {
            $formattedMonth = sprintf('%02d', (int)$month);
            $payrollsQuery->where(function($q) use ($formattedMonth) {
                $q->where('month', $formattedMonth)->orWhere('month', (string)(int)$formattedMonth);
            });
        }

        $payrolls = $payrollsQuery->get();
        $payrollsByEmployee = $payrolls->groupBy('employee_id');

        // 4. Attendance Overtime
        $attendanceQuery = EmployeeAttendance::whereIn('employee_id', $employeeIds)
            ->whereYear('date', $year);

        if ($month !== 'all' && !empty($month)) {
            $attendanceQuery->whereMonth('date', $month);
        }

        $attendances = $attendanceQuery->where('working_minutes', '>', 480)->get();
        $attendanceByEmployee = $attendances->groupBy('employee_id');

        // 5. Commission Payouts / Incentives
        $commissionsQuery = CommissionPayout::whereIn('employee_id', $employeeIds)
            ->where('year', $year);

        if ($month !== 'all' && !empty($month)) {
            $formattedMonth = sprintf('%02d', (int)$month);
            $commissionsQuery->where(function($q) use ($formattedMonth) {
                $q->where('month', $formattedMonth)->orWhere('month', (string)(int)$formattedMonth);
            });
        }

        $commissions = $commissionsQuery->get();
        $commissionsByEmployee = $commissions->groupBy('employee_id');

        // 6. Compute Employee-level metrics
        $employeeMetrics = [];
        $totalSalaryCost = 0;
        $totalCtcCost = 0;
        $totalOvertimeCost = 0;
        $totalOvertimeHours = 0;
        $totalIncentiveCost = 0;

        foreach ($allFilteredEmployees as $emp) {
            $empId = $emp->id;
            $struct = $salaryStructures->get($empId);
            $empPayrolls = $payrollsByEmployee->get($empId, collect());
            $empAttendances = $attendanceByEmployee->get($empId, collect());
            $empCommissions = $commissionsByEmployee->get($empId, collect());

            // Monthly Salary Cost
            if ($empPayrolls->isNotEmpty()) {
                $monthlySalary = floatval($empPayrolls->sum('gross_pay'));
            } else {
                $monthlySalary = $struct ? floatval($struct->gross_salary) : floatval($emp->basic_salary ?: 0);
            }

            // Annual CTC
            $annualCtc = $struct ? (floatval($struct->gross_salary) * 12) : (floatval($emp->basic_salary ?: 0) * 12);

            // Overtime Calculation
            $otMins = $empAttendances->sum(fn($a) => max(0, $a->working_minutes - 480));
            $otHours = round($otMins / 60, 1);
            $hourlyRate = ($monthlySalary > 0) ? ($monthlySalary / 200) : 0;
            $otCostFromAttendance = round($otHours * $hourlyRate, 2);

            // Overtime from payroll breakdown
            $otCostFromPayroll = 0;
            foreach ($empPayrolls as $p) {
                $breakdown = is_array($p->allowances_breakdown) ? $p->allowances_breakdown : [];
                $otCostFromPayroll += floatval($breakdown['overtime'] ?? 0);
            }

            $otCost = max($otCostFromAttendance, $otCostFromPayroll);

            // Incentive / Commission Calculation
            $incentiveFromCommission = floatval($empCommissions->sum('total_payout'));
            $incentiveFromPayroll = 0;
            foreach ($empPayrolls as $p) {
                $incentiveFromPayroll += floatval($p->bonuses) + floatval($p->commissions);
                $breakdown = is_array($p->allowances_breakdown) ? $p->allowances_breakdown : [];
                $incentiveFromPayroll += floatval($breakdown['performance_incentive'] ?? 0) + floatval($breakdown['sales_incentive'] ?? 0);
            }

            $incentiveCost = max($incentiveFromCommission, $incentiveFromPayroll);

            $totalEmpCost = round($monthlySalary + $otCost + $incentiveCost, 2);

            $employeeMetrics[$empId] = [
                'employee_id' => $emp->id,
                'employee_name' => $emp->name,
                'employee_code' => $emp->employee_code,
                'email' => $emp->email,
                'mobile' => $emp->mobile,
                'department' => $emp->department?->name ?? 'N/A',
                'branch' => $emp->branch?->name ?? 'N/A',
                'designation' => $emp->designation?->name ?? 'N/A',
                'monthly_salary' => round($monthlySalary, 2),
                'annual_ctc' => round($annualCtc, 2),
                'ot_hours' => $otHours,
                'ot_cost' => round($otCost, 2),
                'incentive_cost' => round($incentiveCost, 2),
                'total_cost' => $totalEmpCost,
            ];

            $totalSalaryCost += $monthlySalary;
            $totalCtcCost += $annualCtc;
            $totalOvertimeHours += $otHours;
            $totalOvertimeCost += $otCost;
            $totalIncentiveCost += $incentiveCost;
        }

        $totalEmployees = count($allFilteredEmployees);
        $totalEmployeeCost = round($totalSalaryCost + $totalOvertimeCost + $totalIncentiveCost, 2);

        // 7. Department Salary Cost Breakdown
        $deptCostBreakdown = Department::where('partner_id', $partnerId)->get()->map(function ($dept) use ($employeeMetrics, $totalSalaryCost) {
            $deptEmployees = collect($employeeMetrics)->filter(fn($item) => $item['department'] == $dept->name);
            $salaryCost = round($deptEmployees->sum('monthly_salary'), 2);
            $empCount = $deptEmployees->count();
            $percentage = $totalSalaryCost > 0 ? round(($salaryCost / $totalSalaryCost) * 100, 1) : 0;

            return [
                'id' => $dept->id,
                'department' => $dept->name,
                'employees' => $empCount,
                'salary_cost' => $salaryCost,
                'percentage' => $percentage,
            ];
        })->filter(fn($item) => $item['employees'] > 0 || $item['salary_cost'] > 0)->values();

        // 8. Branch Cost Breakdown
        $branchCostBreakdown = HrmsBranch::where('partner_id', $partnerId)->get()->map(function ($branch) use ($employeeMetrics) {
            $branchEmployees = collect($employeeMetrics)->filter(fn($item) => $item['branch'] == $branch->name);
            $salaryCost = round($branchEmployees->sum('monthly_salary'), 2);
            $otCost = round($branchEmployees->sum('ot_cost'), 2);
            $incentiveCost = round($branchEmployees->sum('incentive_cost'), 2);
            $totalCost = round($salaryCost + $otCost + $incentiveCost, 2);
            $empCount = $branchEmployees->count();

            return [
                'id' => $branch->id,
                'branch' => $branch->name,
                'employees' => $empCount,
                'salary_cost' => $salaryCost,
                'ot_cost' => $otCost,
                'incentive_cost' => $incentiveCost,
                'total_cost' => $totalCost,
            ];
        })->filter(fn($item) => $item['employees'] > 0 || $item['total_cost'] > 0)->values();

        // 9. Monthly Trends Breakdown (12 months for year)
        $monthlyTrends = [];
        for ($m = 1; $m <= 12; $m++) {
            $formattedM = sprintf('%02d', $m);
            $mStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
            $mEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();

            $mPayrolls = EmployeePayroll::whereIn('employee_id', $employeeIds)
                ->where('year', $year)
                ->where(function($q) use ($formattedM, $m) {
                    $q->where('month', $formattedM)->orWhere('month', (string)$m);
                })->get();

            $mSalary = $mPayrolls->sum('gross_pay');
            if ($mSalary == 0 && $mPayrolls->isEmpty()) {
                $mSalary = collect($employeeMetrics)->sum('monthly_salary');
            }

            $mAttendances = EmployeeAttendance::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$mStart->format('Y-m-d'), $mEnd->format('Y-m-d')])
                ->where('working_minutes', '>', 480)
                ->get();
            $mOtMins = $mAttendances->sum(fn($a) => max(0, $a->working_minutes - 480));
            $mOtHours = round($mOtMins / 60, 1);
            $mHourlyRate = ($totalEmployees > 0) ? (($mSalary / $totalEmployees) / 200) : 0;
            $mOtCost = round($mOtHours * $mHourlyRate, 2);

            $mCommissions = CommissionPayout::whereIn('employee_id', $employeeIds)
                ->where('year', $year)
                ->where(function($q) use ($formattedM, $m) {
                    $q->where('month', $formattedM)->orWhere('month', (string)$m);
                })->sum('total_payout');

            $monthlyTrends[] = [
                'month' => $mStart->format('M'),
                'salary_cost' => round($mSalary, 2),
                'ot_cost' => round($mOtCost, 2),
                'incentive_cost' => round($mCommissions, 2),
                'total_cost' => round($mSalary + $mOtCost + $mCommissions, 2),
            ];
        }

        // 10. Paginated Employee Cost Data
        $paginatedEmployees = (clone $employeesQuery)->orderBy('name')->paginate($perPage);
        $items = collect($paginatedEmployees->items())->map(function ($emp) use ($employeeMetrics) {
            return $employeeMetrics[$emp->id] ?? [
                'employee_id' => $emp->id,
                'employee_name' => $emp->name,
                'employee_code' => $emp->employee_code,
                'email' => $emp->email,
                'mobile' => $emp->mobile,
                'department' => $emp->department?->name ?? 'N/A',
                'branch' => $emp->branch?->name ?? 'N/A',
                'designation' => $emp->designation?->name ?? 'N/A',
                'monthly_salary' => 0,
                'annual_ctc' => 0,
                'ot_hours' => 0,
                'ot_cost' => 0,
                'incentive_cost' => 0,
                'total_cost' => 0,
            ];
        });

        $paginatedData = [
            'current_page' => $paginatedEmployees->currentPage(),
            'data' => $items,
            'first_page_url' => $paginatedEmployees->url(1),
            'from' => $paginatedEmployees->firstItem(),
            'last_page' => $paginatedEmployees->lastPage(),
            'last_page_url' => $paginatedEmployees->url($paginatedEmployees->lastPage()),
            'next_page_url' => $paginatedEmployees->nextPageUrl(),
            'path' => $paginatedEmployees->path(),
            'per_page' => $paginatedEmployees->perPage(),
            'prev_page_url' => $paginatedEmployees->previousPageUrl(),
            'to' => $paginatedEmployees->lastItem(),
            'total' => $paginatedEmployees->total(),
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Employee cost analytics fetched successfully.',
            'summary' => [
                'total_employees' => $totalEmployees,
                'total_ctc_cost' => round($totalCtcCost, 2),
                'total_salary_cost' => round($totalSalaryCost, 2),
                'total_overtime_hours' => round($totalOvertimeHours, 1),
                'total_overtime_cost' => round($totalOvertimeCost, 2),
                'total_incentive_cost' => round($totalIncentiveCost, 2),
                'total_employee_cost' => $totalEmployeeCost,
            ],
            'dept_cost_breakdown' => $deptCostBreakdown,
            'branch_cost_breakdown' => $branchCostBreakdown,
            'monthly_trends' => $monthlyTrends,
            'data' => $paginatedData,
        ]);
    }

    /**
     * Export Employee Cost Report as CSV stream
     */
    public function exportCsv(Request $request)
    {
        $partnerId = $this->getPartnerId();

        $year = $request->input('year', date('Y'));
        $month = $request->input('month', 'all');
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $designationId = $request->input('designation_id');
        $employeeId = $request->input('employee_id');

        $employeesQuery = User::where(function ($q) use ($partnerId) {
            $q->where('parent_id', $partnerId)->orWhere('id', $partnerId);
        })->whereIn('role', ['employee', 'manager']);

        if (!empty($branchId)) $employeesQuery->where('branch_id', $branchId);
        if (!empty($departmentId)) $employeesQuery->where('department_id', $departmentId);
        if (!empty($designationId)) $employeesQuery->where('designation_id', $designationId);
        if (!empty($employeeId)) $employeesQuery->where('id', $employeeId);

        $employees = $employeesQuery->with(['department', 'branch', 'designation'])->get();
        $employeeIds = $employees->pluck('id')->toArray();

        $salaryStructures = EmployeeSalaryStructure::whereIn('employee_id', $employeeIds)->get()->keyBy('employee_id');

        $payrollsQuery = EmployeePayroll::whereIn('employee_id', $employeeIds)->where('year', $year);
        if ($month !== 'all' && !empty($month)) {
            $formattedMonth = sprintf('%02d', (int)$month);
            $payrollsQuery->where(function($q) use ($formattedMonth) {
                $q->where('month', $formattedMonth)->orWhere('month', (string)(int)$formattedMonth);
            });
        }
        $payrollsByEmployee = $payrollsQuery->get()->groupBy('employee_id');

        $commissionsQuery = CommissionPayout::whereIn('employee_id', $employeeIds)->where('year', $year);
        if ($month !== 'all' && !empty($month)) {
            $formattedMonth = sprintf('%02d', (int)$month);
            $commissionsQuery->where(function($q) use ($formattedMonth) {
                $q->where('month', $formattedMonth)->orWhere('month', (string)(int)$formattedMonth);
            });
        }
        $commissionsByEmployee = $commissionsQuery->get()->groupBy('employee_id');

        $filename = "employee_cost_report_" . date('Y_m_d_H_i') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($employees, $salaryStructures, $payrollsByEmployee, $commissionsByEmployee) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee Name', 'Employee Code', 'Email', 'Branch', 'Department', 'Designation', 'Monthly Salary', 'Annual CTC', 'Overtime Cost', 'Incentive Cost', 'Total Cost']);

            foreach ($employees as $emp) {
                $struct = $salaryStructures->get($emp->id);
                $empPayrolls = $payrollsByEmployee->get($emp->id, collect());
                $empCommissions = $commissionsByEmployee->get($emp->id, collect());

                $monthlySalary = $empPayrolls->isNotEmpty() ? floatval($empPayrolls->sum('gross_pay')) : ($struct ? floatval($struct->gross_salary) : floatval($emp->basic_salary ?: 0));
                $annualCtc = $struct ? (floatval($struct->gross_salary) * 12) : (floatval($emp->basic_salary ?: 0) * 12);

                $otCost = 0;
                foreach ($empPayrolls as $p) {
                    $breakdown = is_array($p->allowances_breakdown) ? $p->allowances_breakdown : [];
                    $otCost += floatval($breakdown['overtime'] ?? 0);
                }

                $incentiveCost = floatval($empCommissions->sum('total_payout'));

                fputcsv($file, [
                    $emp->name,
                    $emp->employee_code ?? 'N/A',
                    $emp->email,
                    $emp->branch?->name ?? 'N/A',
                    $emp->department?->name ?? 'N/A',
                    $emp->designation?->name ?? 'N/A',
                    round($monthlySalary, 2),
                    round($annualCtc, 2),
                    round($otCost, 2),
                    round($incentiveCost, 2),
                    round($monthlySalary + $otCost + $incentiveCost, 2)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
