<?php

namespace App\Livewire\Admin\Reports\Hrms;

use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class StaffReport extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $search = '';

    public $branchId = '';

    public $teamId = '';

    public $departmentId = '';

    public $roleName = '';

    public $status = '';

    public function updatingSearch()
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

        $query = User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))->where('role', 'employee');

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('mobile', 'like', '%'.$search.'%')
                    ->orWhere('employee_code', 'like', '%'.$search.'%');
            });
        }

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        if ($this->teamId) {
            $query->where('reporting_to', $this->teamId);
        }

        if ($this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        if ($this->roleName) {
            $query->role($this->roleName);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()
            ->with(['branch', 'reportingTo', 'department', 'roles'])
            ->orderBy('branch_id')
            ->orderBy('reporting_to')
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

        // Find all managers/leads that employees report to
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

        // Fallback to employees with reportees
        return User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))
            ->where('role', 'employee')
            ->whereHas('reportees')
            ->orderBy('name')
            ->get();
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
        $data = $this->getBaseQuery()->with(['branch', 'reportingTo', 'department', 'roles'])->latest()->get();
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=staff_report.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['Emp Code', 'Name', 'Branch', 'Team / Reporting To', 'Department', 'Role', 'Email', 'Mobile', 'Employment Type', 'Basic Salary', 'Joining Date', 'Status'];

        $callback = function () use ($data, $columns, $partnerId) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $row) {
                $roleName = $row->roles->first() ? str_replace([$partnerId.'_', '_'.$partnerId], '', $row->roles->first()->name) : '-';
                fputcsv($file, [
                    $row->employee_code ?: substr($row->id, 0, 8),
                    $row->name,
                    optional($row->branch)->name ?: 'Main Branch',
                    optional($row->reportingTo)->name ?: 'Direct / None',
                    optional($row->department)->name ?: '-',
                    $roleName,
                    $row->email,
                    $row->mobile ?: '-',
                    ucfirst(str_replace('_', ' ', $row->employment_type ?? 'full_time')),
                    $row->basic_salary,
                    $row->joining_date ? Carbon::parse($row->joining_date)->format('d M Y') : '-',
                    ucfirst($row->status),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.staff-report', [
            'reportData' => $this->reportData,
            'branches' => $this->branches,
            'teams' => $this->teams,
            'departments' => $this->departments,
            'roles' => $this->roles,
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Panel',
            'pageTitle' => 'Staff Report',
            'pageSubtitle' => 'View staff details, branch, and team hierarchy',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
