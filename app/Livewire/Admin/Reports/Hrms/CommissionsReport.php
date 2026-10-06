<?php

namespace App\Livewire\Admin\Reports\Hrms;

use App\Models\CommissionPayout;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class CommissionsReport extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public $search = '';

    public $branchId = '';

    public $teamId = '';

    public $departmentId = '';

    public $roleName = '';

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

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        return CommissionPayout::query()->whereHas('employee', function ($q) use ($partnerId) {
            $q->when($partnerId, fn ($q) => $q->where('parent_id', $partnerId));

            if ($this->branchId) {
                $q->where('branch_id', $this->branchId);
            }

            if ($this->teamId) {
                $q->where('reporting_to', $this->teamId);
            }
        });
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['employee'])->latest()->paginate(20);
    }

    public function getDepartmentsProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        return Department::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->get();
    }

    public function getBranchesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        return HrmsBranch::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
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

    public function getRolesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        return Role::where('name', 'like', '%'.$partnerId.'%')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['employee'])->latest()->get();
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=commissions_report.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = ['ID', 'Employee Name', 'Month/Year', 'Target', 'Commission Earned', 'Recovery Earned', 'Net Payout', 'Status'];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {
                fputcsv($file, $this->mapCsvRow($row));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.admin.reports.hrms.commissions-report', [
            'reportData' => $this->reportData,
            'branches' => $this->branches,
            'teams' => $this->teams,
            'departments' => $this->departments,
            'roles' => $this->roles,
        ])->layout('layouts.app', [
            'panelName' => 'HRMS Panel',
            'pageTitle' => 'Commissions Report',
            'pageSubtitle' => 'View staff details and performance',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
