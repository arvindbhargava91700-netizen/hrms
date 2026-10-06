<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\Designation;
use App\Models\EmployeeSalaryStructure;
use App\Models\EmployeePayroll;
use App\Models\EmployeeAttendance;
use App\Models\CommissionPayout;
use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;
use Carbon\Carbon;

class EmployeeCost extends Component
{
    use WithPagination, HasPartnerId;

    protected $paginationTheme = 'bootstrap';

    public $filterYear;
    public $filterMonth = 'all';
    public $filterBranchId = '';
    public $filterDepartmentId = '';
    public $filterDesignationId = '';
    public $filterEmployeeId = '';
    public $search = '';
    public $perPage = 10;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterYear() { $this->resetPage(); }
    public function updatingFilterMonth() { $this->resetPage(); }
    public function updatingFilterBranchId() { $this->resetPage(); }
    public function updatingFilterDepartmentId() { $this->resetPage(); }
    public function updatingFilterDesignationId() { $this->resetPage(); }
    public function updatingFilterEmployeeId() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('employee_cost_view') ||
            auth()->user()->canAccess('employeecost_viewany') ||
            auth()->user()->canAccess('employeecost_viewBranch') ||
            auth()->user()->canAccess('employeecost_viewTeam') ||
            auth()->user()->canAccess('salary_viewAny'),
            403
        );

        $this->filterYear = date('Y');
    }

    public function render()
    {
        $partnerId = $this->getPartnerId();
        $year = $this->filterYear ?: date('Y');

        // 1. Base Employees Query
        $employeesQuery = User::where('role', 'employee');

        // Apply filters with proper partner scoping
        if (!empty($this->filterBranchId)) {
            $employeesQuery->where('branch_id', $this->filterBranchId);
        }
        if (!empty($this->filterDepartmentId)) {
            $employeesQuery->where('department_id', $this->filterDepartmentId);
        }
        if (!empty($this->filterDesignationId)) {
            $employeesQuery->where('designation_id', $this->filterDesignationId);
        }
        if (!empty($this->filterEmployeeId)) {
            $employeesQuery->where('id', $this->filterEmployeeId);
        }
        if (!empty($this->search)) {
            $search = $this->search;
            $employeesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $allFilteredEmployees = (clone $employeesQuery)->with(['department', 'branch', 'designation'])->get();
        $employeeIds = $allFilteredEmployees->pluck('id')->toArray();

        // 2. Fetch Salary Structures for filtered employees
        $salaryStructures = EmployeeSalaryStructure::whereIn('employee_id', $employeeIds)
            ->get()
            ->keyBy('employee_id');

        // 3. Fetch Payrolls for filtered period
        $payrollsQuery = EmployeePayroll::whereIn('employee_id', $employeeIds)
            ->where('year', $year);

        if ($this->filterMonth !== 'all' && !empty($this->filterMonth)) {
            $formattedMonth = sprintf('%02d', (int)$this->filterMonth);
            $payrollsQuery->where(function($q) use ($formattedMonth) {
                $q->where('month', $formattedMonth)->orWhere('month', (string)(int)$formattedMonth);
            });
        }

        $payrolls = $payrollsQuery->get();
        $payrollsByEmployee = $payrolls->groupBy('employee_id');

        // 4. Fetch Attendance for Overtime calculation
        $attendanceQuery = EmployeeAttendance::whereIn('employee_id', $employeeIds)
            ->whereYear('date', $year);

        if ($this->filterMonth !== 'all' && !empty($this->filterMonth)) {
            $attendanceQuery->whereMonth('date', $this->filterMonth);
        }

        $attendances = $attendanceQuery->where('working_minutes', '>', 480)->get();
        $attendanceByEmployee = $attendances->groupBy('employee_id');

        // 5. Fetch Commission Payouts for Incentives
        $commissionsQuery = CommissionPayout::whereIn('employee_id', $employeeIds)
            ->where('year', $year);

        if ($this->filterMonth !== 'all' && !empty($this->filterMonth)) {
            $formattedMonth = sprintf('%02d', (int)$this->filterMonth);
            $commissionsQuery->where(function($q) use ($formattedMonth) {
                $q->where('month', $formattedMonth)->orWhere('month', (string)(int)$formattedMonth);
            });
        }

        $commissions = $commissionsQuery->get();
        $commissionsByEmployee = $commissions->groupBy('employee_id');

        // 6. Compute Employee-level metrics array
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
                $monthlySalary = $empPayrolls->sum('gross_pay');
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
            $incentiveFromCommission = $empCommissions->sum('total_payout');
            $incentiveFromPayroll = 0;
            foreach ($empPayrolls as $p) {
                $incentiveFromPayroll += floatval($p->bonuses) + floatval($p->commissions);
                $breakdown = is_array($p->allowances_breakdown) ? $p->allowances_breakdown : [];
                $incentiveFromPayroll += floatval($breakdown['performance_incentive'] ?? 0) + floatval($breakdown['sales_incentive'] ?? 0);
            }

            $incentiveCost = max($incentiveFromCommission, $incentiveFromPayroll);

            $totalEmpCost = $monthlySalary + $otCost + $incentiveCost;

            $employeeMetrics[$empId] = [
                'employee' => $emp,
                'monthly_salary' => $monthlySalary,
                'annual_ctc' => $annualCtc,
                'ot_hours' => $otHours,
                'ot_cost' => $otCost,
                'incentive_cost' => $incentiveCost,
                'total_cost' => $totalEmpCost,
            ];

            $totalSalaryCost += $monthlySalary;
            $totalCtcCost += $annualCtc;
            $totalOvertimeHours += $otHours;
            $totalOvertimeCost += $otCost;
            $totalIncentiveCost += $incentiveCost;
        }

        $totalEmployees = count($allFilteredEmployees);
        $totalEmployeeCost = $totalSalaryCost + $totalOvertimeCost + $totalIncentiveCost;

        // 7. Department Salary Cost Breakdown & Percentage
        $deptAttrition = Department::get()->map(function ($dept) use ($employeeMetrics) {
            $deptEmployees = collect($employeeMetrics)->filter(fn($item) => $item['employee']->department_id == $dept->id);
            $salaryCost = $deptEmployees->sum('monthly_salary');
            $empCount = $deptEmployees->count();

            return [
                'id' => $dept->id,
                'department' => $dept->name,
                'employees' => $empCount,
                'salary_cost' => $salaryCost,
            ];
        })->filter(fn($item) => $item['employees'] > 0 || $item['salary_cost'] > 0)->values();

        // Calculate % share for departments
        $deptCostBreakdown = $deptAttrition->map(function ($item) use ($totalSalaryCost) {
            $item['percentage'] = $totalSalaryCost > 0 ? round(($item['salary_cost'] / $totalSalaryCost) * 100, 1) : 0;
            return $item;
        });

        // 8. Branch Cost Breakdown
        $branchCostBreakdown = HrmsBranch::get()->map(function ($branch) use ($employeeMetrics) {
            $branchEmployees = collect($employeeMetrics)->filter(fn($item) => $item['employee']->branch_id == $branch->id);
            $salaryCost = $branchEmployees->sum('monthly_salary');
            $otCost = $branchEmployees->sum('ot_cost');
            $incentiveCost = $branchEmployees->sum('incentive_cost');
            $totalCost = $salaryCost + $otCost + $incentiveCost;
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

            // Payroll salary for month
            $mPayrolls = EmployeePayroll::whereIn('employee_id', $employeeIds)
                ->where('year', $year)
                ->where(function($q) use ($formattedM, $m) {
                    $q->where('month', $formattedM)->orWhere('month', (string)$m);
                })->get();

            $mSalary = $mPayrolls->sum('gross_pay');
            if ($mSalary == 0 && $mPayrolls->isEmpty()) {
                // Estimated monthly salary base
                $mSalary = collect($employeeMetrics)->sum('monthly_salary');
            }

            // OT for month
            $mAttendances = EmployeeAttendance::whereIn('employee_id', $employeeIds)
                ->whereBetween('date', [$mStart->format('Y-m-d'), $mEnd->format('Y-m-d')])
                ->where('working_minutes', '>', 480)
                ->get();
            $mOtMins = $mAttendances->sum(fn($a) => max(0, $a->working_minutes - 480));
            $mOtHours = round($mOtMins / 60, 1);
            $mHourlyRate = ($totalEmployees > 0) ? (($mSalary / $totalEmployees) / 200) : 0;
            $mOtCost = round($mOtHours * $mHourlyRate, 2);

            // Incentives for month
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

        // 10. Paginated Employee Cost Table
        $paginatedEmployeeIds = (clone $employeesQuery)->orderBy('name')->paginate($this->perPage);
        $paginatedMetrics = collect($paginatedEmployeeIds->items())->map(function ($emp) use ($employeeMetrics) {
            return $employeeMetrics[$emp->id] ?? [
                'employee' => $emp,
                'monthly_salary' => 0,
                'annual_ctc' => 0,
                'ot_hours' => 0,
                'ot_cost' => 0,
                'incentive_cost' => 0,
                'total_cost' => 0,
            ];
        });

// Branches, Departments, Designations, Employees for filter dropdowns
        $branches = HrmsBranch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $designations = Designation::orderBy('name')->get();
        
        $allEmployeesListQuery = User::where('role', 'employee');
        
        if (!empty($this->filterBranchId)) {
            $allEmployeesListQuery->where('branch_id', $this->filterBranchId);
        }
        if (!empty($this->filterDepartmentId)) {
            $allEmployeesListQuery->where('department_id', $this->filterDepartmentId);
        }
        $allEmployeesList = $allEmployeesListQuery->orderBy('name')->get();

        return view('livewire.partner.hrms.employee-cost', [
            'totalEmployees' => $totalEmployees,
            'totalCtcCost' => $totalCtcCost,
            'totalSalaryCost' => $totalSalaryCost,
            'totalOvertimeCost' => $totalOvertimeCost,
            'totalOvertimeHours' => $totalOvertimeHours,
            'totalIncentiveCost' => $totalIncentiveCost,
            'totalEmployeeCost' => $totalEmployeeCost,
            'deptCostBreakdown' => $deptCostBreakdown,
            'branchCostBreakdown' => $branchCostBreakdown,
            'monthlyTrends' => $monthlyTrends,
            'paginatedEmployeeIds' => $paginatedEmployeeIds,
            'paginatedMetrics' => $paginatedMetrics,
            'branches' => $branches,
            'departments' => $departments,
            'designations' => $designations,
            'allEmployeesList' => $allEmployeesList,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Employee Cost Analytics',
            'pageSubtitle' => 'Track total employee CTC, salary costs, overtime, and incentives',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
