<?php

namespace App\Livewire\Admin\Reports\Hrms;

use App\Models\Department;
use App\Models\EmployeePayroll;
use App\Models\HrmsBranch;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class SalaryManagementReport extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $search = '';

    public $branchId = '';

    public $teamId = '';

    public $employeeId = '';

    public $departmentId = '';

    public $roleName = '';

    public $status = '';

    public $month = '';

    public $year = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedBranchId()
    {
        $this->teamId = '';
        $this->employeeId = '';
        $this->resetPage();
    }

    public function updatedTeamId()
    {
        $this->employeeId = '';
        $this->resetPage();
    }

    public function updatedEmployeeId()
    {
        $this->resetPage();
    }

    public function updatedDepartmentId()
    {
        $this->resetPage();
    }

    public function updatedRoleName()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedMonth()
    {
        $this->resetPage();
    }

    public function updatedYear()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->branchId = '';
        $this->teamId = '';
        $this->employeeId = '';
        $this->departmentId = '';
        $this->roleName = '';
        $this->status = '';
        $this->month = '';
        $this->year = '';
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = EmployeePayroll::query()->whereHas('employee', function ($q) use ($partnerId) {
            $q->when($partnerId, fn ($q) => $q->where('parent_id', $partnerId));

            if ($this->search) {
                $search = $this->search;
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('employee_code', 'like', '%'.$search.'%');
                });
            }

            if ($this->branchId) {
                $q->where('branch_id', $this->branchId);
            }

            if ($this->teamId) {
                $q->where('reporting_to', $this->teamId);
            }

            if ($this->employeeId) {
                $q->where('id', $this->employeeId);
            }

            if ($this->departmentId) {
                $q->where('department_id', $this->departmentId);
            }

            if ($this->roleName) {
                $q->role($this->roleName);
            }
        });

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->month) {
            $query->where('month', $this->month);
        }

        if ($this->year) {
            $query->where('year', $this->year);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()
            ->with(['employee.branch', 'employee.reportingTo', 'employee.department'])
            ->latest()
            ->paginate(20);
    }

    public function getBranchesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        return HrmsBranch::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->where('status', 'active')->orderBy('name')->get();
    }

    public function getTeamsProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $reportingQuery = User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))
            ->where('role', 'employee')
            ->whereNotNull('reporting_to');

        if ($this->branchId) {
            $reportingQuery->where('branch_id', $this->branchId);
        }

        $leadIds = $reportingQuery->pluck('reporting_to')->unique();

        if ($leadIds->isNotEmpty()) {
            return User::whereIn('id', $leadIds)->orderBy('name')->get();
        }

        return User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))
            ->where('role', 'employee')
            ->whereHas('reportees')
            ->orderBy('name')
            ->get();
    }

    public function getEmployeesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $query = User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))->where('role', 'employee');

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->teamId) {
            $query->where('reporting_to', $this->teamId);
        }

        return $query->orderBy('name')->get();
    }

    public function getDepartmentsProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        return Department::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->orderBy('name')->get();
    }

    public function getRolesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        return Role::where('name', 'like', '%'.$partnerId.'%')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['employee.branch', 'employee.reportingTo', 'employee.department'])->latest()->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=salary-management_report.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'ID', 'Emp Code', 'Employee Name', 'Branch', 'Team / Reporting Manager', 'Department',
            'Month/Year', 'Basic Salary', 'Allowances', 'Bonuses', 'Commissions', 'Gross Pay',
            'Deductions', 'Net Pay', 'Status',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id,
                    optional($row->employee)->employee_code ?? (optional($row->employee)->id ? substr(optional($row->employee)->id, 0, 8) : ''),
                    optional($row->employee)->name ?? '-',
                    optional(optional($row->employee)->branch)->name ?: 'Main Branch',
                    optional(optional($row->employee)->reportingTo)->name ?: 'Direct / None',
                    optional(optional($row->employee)->department)->name ?: '-',
                    $row->month.' / '.$row->year,
                    $row->basic_salary,
                    $this->getAllowances($row->allowances_breakdown),
                    $row->bonuses,
                    $row->commissions,
                    $row->gross_pay,
                    $row->deductions,
                    $row->net_pay,
                    ucfirst($row->status ?? 'pending'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getAllowances($breakdown)
    {
        if (is_array($breakdown)) {
            return array_sum(array_column($breakdown, 'amount'));
        }
        $decoded = json_decode($breakdown, true);
        if (is_array($decoded)) {
            return array_sum(array_column($decoded, 'amount'));
        }

        return 0;
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.salary-management-report', [
            'reportData' => $this->reportData,
            'branches' => $this->branches,
            'teams' => $this->teams,
            'employees' => $this->employees,
            'departments' => $this->departments,
            'roles' => $this->roles,
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Panel',
            'pageTitle' => 'Salary Management Report',
            'pageSubtitle' => 'View staff salary breakdown, allowances, deductions, and payment details',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
