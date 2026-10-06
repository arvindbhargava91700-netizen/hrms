<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\EmployeePayroll;
use App\Models\EmployeeSalaryStructure;
use Carbon\Carbon;

class EmployeeCostReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filterYear;
    public $filterBranchId = '';
    public $filterDepartmentId = '';
    public $search = '';
    public $partnerFilter = '';
    public $perPage = 10;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterYear() { $this->resetPage(); }
    public function updatingFilterBranchId() { $this->resetPage(); }
    public function updatingFilterDepartmentId() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('employeecost_view') ||
            auth()->user()->canAccess('employeecost_viewany') ||
            auth()->user()->canAccess('employeecost_viewteam'),
            403
        );

        $this->filterYear = Carbon::now()->year;
    }

    public function getPartnerId()
    {
        return auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
    }

    public function render()
    {
        
        $selectedYear = (int) ($this->filterYear ?: Carbon::now()->year);

        // 1. Base Query for Employees
        $employeesQuery = User::with(['department', 'branch'])
            ->where(function ($q) {
                $q->orWhere('id', "");
            })
            ->whereIn('role', ['employee', 'manager']);

        if (!empty($this->filterBranchId)) {
            $employeesQuery->where('branch_id', $this->filterBranchId);
        }
        if (!empty($this->filterDepartmentId)) {
            $employeesQuery->where('department_id', $this->filterDepartmentId);
        }
        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
            $employeesQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', $s)
                    ->orWhere('email', 'like', $s)
                    ->orWhere('employee_code', 'like', $s);
            });
        }

        $allFilteredEmployees = (clone $employeesQuery)->get();
        $filteredEmployeeIds = $allFilteredEmployees->pluck('id')->toArray();

        // Fetch Salary Structures
        $salaryStructures = EmployeeSalaryStructure::whereIn('employee_id', $filteredEmployeeIds)
            ->get()
            ->keyBy('employee_id');

        // 2. Fetch Payroll Data
        $payrolls = EmployeePayroll::whereIn('employee_id', $filteredEmployeeIds)
            ->where('year', $selectedYear)
            ->get();

        // Summary Calculations
        $totalSalaryCost = (float) $payrolls->sum('net_pay');
        $totalOvertimeCost = (float) $payrolls->sum('bonuses');
        $totalIncentiveCost = (float) $payrolls->sum('commissions');
        $totalEmployeeCost = $totalSalaryCost + $totalOvertimeCost + $totalIncentiveCost;

        $totalEmployeeCount = count($filteredEmployeeIds);
        $totalAnnualCTC = $allFilteredEmployees->sum(function ($emp) use ($salaryStructures) {
            $st = $salaryStructures[$emp->id] ?? null;
            return $st ? (float) $st->annual_ctc : (float) (($emp->base_salary ?? 0) * 12);
        });
        $averageCTC = $totalEmployeeCount > 0 ? ($totalAnnualCTC / $totalEmployeeCount) : 0;

        // Department Cost Breakdown
        $departments = Department::query()->get();
        $deptCostBreakdown = [];
        foreach ($departments as $dept) {
            $deptEmpIds = $allFilteredEmployees->where('department_id', $dept->id)->pluck('id');
            $deptSalary = (float) $payrolls->whereIn('employee_id', $deptEmpIds)->sum('net_pay');
            $deptOT = (float) $payrolls->whereIn('employee_id', $deptEmpIds)->sum('bonuses');
            $deptInc = (float) $payrolls->whereIn('employee_id', $deptEmpIds)->sum('commissions');
            $deptTotal = $deptSalary + $deptOT + $deptInc;

            if ($deptEmpIds->count() > 0 || $deptTotal > 0) {
                $deptCostBreakdown[] = [
                    'department_id' => $dept->id,
                    'department'    => $dept->name,
                    'employee_count' => $deptEmpIds->count(),
                    'salary_cost'   => $deptSalary,
                    'ot_cost'       => $deptOT,
                    'incentive_cost' => $deptInc,
                    'total_cost'    => $deptTotal,
                ];
            }
        }

        // Branch Cost Breakdown
        $branches = HrmsBranch::query()->get();
        $branchCostBreakdown = [];
        foreach ($branches as $branch) {
            $branchEmpIds = $allFilteredEmployees->where('branch_id', $branch->id)->pluck('id');
            $branchSalary = (float) $payrolls->whereIn('employee_id', $branchEmpIds)->sum('net_pay');
            $branchOT = (float) $payrolls->whereIn('employee_id', $branchEmpIds)->sum('bonuses');
            $branchInc = (float) $payrolls->whereIn('employee_id', $branchEmpIds)->sum('commissions');
            $branchTotal = $branchSalary + $branchOT + $branchInc;

            if ($branchEmpIds->count() > 0 || $branchTotal > 0) {
                $branchCostBreakdown[] = [
                    'branch_id'     => $branch->id,
                    'branch'        => $branch->name,
                    'employee_count' => $branchEmpIds->count(),
                    'salary_cost'   => $branchSalary,
                    'ot_cost'       => $branchOT,
                    'incentive_cost' => $branchInc,
                    'total_cost'    => $branchTotal,
                ];
            }
        }

        // Monthly Trends
        $monthlyTrends = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create($selectedYear, $m, 1)->format('M');
            $mStr = sprintf('%02d', $m);
            $mPayrolls = $payrolls->filter(function($p) use ($m, $mStr) {
                return (int)$p->month === $m || (string)$p->month === $mStr;
            });
            $monthlyTrends[] = [
                'month'          => $monthName,
                'salary_cost'    => (float) $mPayrolls->sum('net_pay'),
                'ot_cost'        => (float) $mPayrolls->sum('bonuses'),
                'incentive_cost' => (float) $mPayrolls->sum('commissions'),
                'total_cost'     => (float) ($mPayrolls->sum('net_pay') + $mPayrolls->sum('bonuses') + $mPayrolls->sum('commissions')),
            ];
        }

        // Employee-wise Breakdown
        $paginatedEmployees = $employeesQuery->orderBy('name')->paginate($this->perPage);
        $paginatedMetrics = collect($paginatedEmployees->items())->map(function ($emp) use ($payrolls, $salaryStructures) {
            $empPayrolls = $payrolls->where('employee_id', $emp->id);
            $st = $salaryStructures[$emp->id] ?? null;
            $monthlySalary = $st ? (float) $st->monthly_ctc : (float) ($emp->base_salary ?? 0);
            $annualCTC = $st ? (float) $st->annual_ctc : ($monthlySalary * 12);
            $otCost = (float) $empPayrolls->sum('bonuses');
            $incentiveCost = (float) $empPayrolls->sum('commissions');
            $salaryCost = (float) $empPayrolls->sum('net_pay');
            $totalCost = $salaryCost + $otCost + $incentiveCost;

            return [
                'employee'       => $emp,
                'monthly_salary' => $monthlySalary,
                'annual_ctc'     => $annualCTC,
                'salary_cost'    => $salaryCost,
                'ot_cost'        => $otCost,
                'incentive_cost' => $incentiveCost,
                'total_cost'     => $totalCost > 0 ? $totalCost : $annualCTC,
            ];
        });

        return view('livewire.admin.reports.hrms.employee-cost-report', [
            'totalEmployeeCost'   => $totalEmployeeCost,
            'totalSalaryCost'     => $totalSalaryCost,
            'totalOvertimeCost'   => $totalOvertimeCost,
            'totalIncentiveCost'  => $totalIncentiveCost,
            'totalAnnualCTC'      => $totalAnnualCTC,
            'averageCTC'          => $averageCTC,
            'totalEmployeeCount'  => $totalEmployeeCount,
            'deptCostBreakdown'   => $deptCostBreakdown,
            'branchCostBreakdown' => $branchCostBreakdown,
            'monthlyTrends'       => $monthlyTrends,
            'paginatedEmployees'  => $paginatedEmployees,
            'paginatedMetrics'    => $paginatedMetrics,
            'branches'            => $branches,
            'departments'         => $departments,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Employee Cost Report',
            'pageSubtitle' => 'Organization employee CTC, salary, overtime, and incentive analysis',
            'sidebarLinks' => view(auth()->check() && auth()->user()->role === 'employee' ? 'partials.sidebar-employee' : 'partials.sidebar-admin'),
        ]);
    }
}
