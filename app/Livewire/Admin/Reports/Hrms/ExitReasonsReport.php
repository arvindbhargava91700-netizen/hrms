<?php

namespace App\Livewire\Admin\Reports\Hrms;

use App\Models\EmployeeExit;
use App\Models\HrmsBranch;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ExitReasonsReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filterType = 'all';

    public $search = '';

    public $perPage = 10;

    public $branchId = '';

    public $teamId = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
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

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('exitreason_view') ||
            auth()->user()->canAccess('exitreason_viewAny'),
            403
        );
    }

    public function clearFilters()
    {
        $this->filterType = 'all';
        $this->search = '';
        $this->branchId = '';
        $this->teamId = '';
        $this->resetPage();
    }

    public function render()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = EmployeeExit::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->with(['employee', 'branch', 'department', 'designation']);

        if ($this->filterType !== 'all' && ! empty($this->filterType)) {
            $query->where('exit_type', $this->filterType);
        }

        if (! empty($this->branchId)) {
            $query->where('branch_id', $this->branchId);
        }

        if (! empty($this->teamId)) {
            $query->whereHas('employee', function ($q) {
                $q->where('reporting_to', $this->teamId);
            });
        }

        if (! empty($this->search)) {
            $s = '%'.$this->search.'%';
            $query->where(function ($q) use ($s) {
                $q->where('exit_reason', 'like', $s)
                    ->orWhereHas('employee', function ($eq) use ($s) {
                        $eq->where('name', 'like', $s);
                    });
            });
        }

        $allExits = (clone $query)->get();

        $totalExits = $allExits->count();
        $voluntaryCount = $allExits->where('exit_type', 'voluntary')->count();
        $involuntaryCount = $allExits->where('exit_type', 'involuntary')->count();

        // Exit reasons breakdown
        $exitReasonsBreakdown = $allExits->groupBy('exit_reason')->map(function ($group) use ($totalExits) {
            return [
                'reason' => $group->first()->exit_reason ?? 'Not Specified',
                'count' => $group->count(),
                'percentage' => $totalExits > 0 ? round(($group->count() / $totalExits) * 100, 1) : 0,
            ];
        })->sortByDesc('count')->values()->toArray();

        $paginatedExits = $query->orderByDesc('exit_date')->paginate($this->perPage);

        $branches = HrmsBranch::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->where('status', 'active')->orderBy('name')->get();
        $teams = $this->getTeams($partnerId);

        return view('livewire.admin.reports.hrms.exit-reasons-report', [
            'totalExits' => $totalExits,
            'voluntaryCount' => $voluntaryCount,
            'involuntaryCount' => $involuntaryCount,
            'exitReasonsBreakdown' => $exitReasonsBreakdown,
            'paginatedExits' => $paginatedExits,
            'branches' => $branches,
            'teams' => $teams,
        ])->layout('layouts.app', [
            'panelName' => 'Partner Panel',
            'pageTitle' => 'Exit Reasons Report',
            'pageSubtitle' => 'Employee exits grouped by exit reason with branch & team filters',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }

    private function getTeams($partnerId)
    {
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
}
