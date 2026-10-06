<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\EmployeePayroll;
use App\Models\User;
use App\Models\Department;
use App\Models\HrmsBranch;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Livewire\WithPagination;

class PayrollReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $branchId = '';
    public $teamId = '';
    public $departmentId = '';
    public $roleName = '';
    public $status = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedBranchId()
    {
        $this->teamId = '';
        $this->resetPage();
    }

    public function updatedTeamId()
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

    public function clearFilters()
    {
        $this->search = '';
        $this->branchId = '';
        $this->teamId = '';
        $this->departmentId = '';
        $this->roleName = '';
        $this->status = '';
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $query = EmployeePayroll::query()->whereHas('employee', function($q) use ($partnerId) {
            $q->where('parent_id', $partnerId);

            if ($this->search) {
                $search = $this->search;
                $q->where(function($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('employee_code', 'like', '%' . $search . '%');
                });
            }

            if ($this->branchId) {
                $q->where('branch_id', $this->branchId);
            }

            if ($this->teamId) {
                $q->where('reporting_to', $this->teamId);
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

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['employee.branch', 'employee.reportingTo', 'employee.department'])->latest()->paginate(20);
    }

    public function getBranchesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return HrmsBranch::where('partner_id', $partnerId)->where('status', 'active')->orderBy('name')->get();
    }

    public function getTeamsProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $reportingQuery = User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereNotNull('reporting_to');
            
        if ($this->branchId) {
            $reportingQuery->where('branch_id', $this->branchId);
        }
        
        $leadIds = $reportingQuery->pluck('reporting_to')->unique();

        if ($leadIds->isNotEmpty()) {
            return User::whereIn('id', $leadIds)->orderBy('name')->get();
        }

        return User::where('parent_id', $partnerId)
            ->where('role', 'employee')
            ->whereHas('reportees')
            ->orderBy('name')
            ->get();
    }

    public function getDepartmentsProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return Department::where('partner_id', $partnerId)->orderBy('name')->get();
    }

    public function getRolesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return Role::where('name', 'like', '%' . $partnerId . '%')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['employee.branch', 'employee.reportingTo', 'employee.department'])->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=payroll_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Emp Code', 'Employee Name', 'Branch', 'Team / Reporting Manager', 'Department', 'Month/Year', 'Presents', 'Absents', 'Leaves', 'Expenses', 'Net Pay', 'Status'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                $stats = $this->getStats($row->employee_id, $row->month, $row->year);
                fputcsv($file, [
                    $row->id,
                    optional($row->employee)->employee_code ?? (optional($row->employee)->id ? substr(optional($row->employee)->id, 0, 8) : ''),
                    optional($row->employee)->name ?? '-',
                    optional(optional($row->employee)->branch)->name ?: 'Main Branch',
                    optional(optional($row->employee)->reportingTo)->name ?: 'Direct / None',
                    optional(optional($row->employee)->department)->name ?: '-',
                    $row->month . ' / ' . $row->year,
                    $stats['presents'],
                    $stats['absents'],
                    $stats['leaves'],
                    $stats['expenses'],
                    $row->net_pay,
                    ucfirst($row->status)
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.hrms.payroll-report', [
            'reportData'  => $this->reportData,
            'branches'    => $this->branches,
            'teams'       => $this->teams,
            'departments' => $this->departments,
            'roles'       => $this->roles,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Payroll Report',
            'pageSubtitle' => 'View staff details and performance',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    public function getStats($employeeId, $month, $year)
    {
        $presents = \App\Models\EmployeeAttendance::where('employee_id', $employeeId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'present')->count();
        $absents = \App\Models\EmployeeAttendance::where('employee_id', $employeeId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'absent')->count();
        $leaves = \App\Models\EmployeeLeave::where('employee_id', $employeeId)->whereMonth('start_date', $month)->whereYear('start_date', $year)->where('status', 'approved')->count();
        $expenses = \App\Models\Expense::where('employee_id', $employeeId)->whereMonth('date', $month)->whereYear('date', $year)->where('status', 'approved')->sum('amount');
        
        return [
            'presents' => $presents,
            'absents' => $absents,
            'leaves' => $leaves,
            'expenses' => $expenses
        ];
    }
}
