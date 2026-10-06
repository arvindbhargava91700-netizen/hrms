<?php

namespace App\Livewire\Admin\Reports\Hrms;

use App\Models\Department;
use App\Models\EmployeeExit;
use App\Models\ExitReason;
use App\Models\HrmsBranch;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AttritionReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filterYear;

    public $filterMonth = 'all';

    public $filterBranchId = '';

    public $teamId = '';

    public $filterDepartmentId = '';

    public $filterExitType = 'all';

    public $filterExitReason = 'all';

    public $search = '';

    public $perPage = 10;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterYear()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatingFilterBranchId()
    {
        $this->resetPage();
    }

    public function updatingTeamId()
    {
        $this->resetPage();
    }

    public function updatingFilterDepartmentId()
    {
        $this->resetPage();
    }

    public function updatingFilterExitType()
    {
        $this->resetPage();
    }

    public function updatingFilterExitReason()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('attrition_view') ||
            auth()->user()->canAccess('attrition_viewAny'),
            403
        );

        $this->filterYear = Carbon::now()->year;
    }

    public function render()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $selectedYear = (int) ($this->filterYear ?: Carbon::now()->year);

        // 1. Base Query
        $query = EmployeeExit::with(['employee', 'branch', 'department'])
            ->when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))
            ->whereYear('exit_date', $selectedYear);

        if ($this->filterMonth !== 'all' && ! empty($this->filterMonth)) {
            $query->whereMonth('exit_date', (int) $this->filterMonth);
        }
        if (! empty($this->filterBranchId)) {
            $query->where('branch_id', $this->filterBranchId);
        }
        if (! empty($this->teamId)) {
            $query->whereHas('employee', function ($q) {
                $q->where('reporting_to', $this->teamId);
            });
        }
        if (! empty($this->filterDepartmentId)) {
            $query->where('department_id', $this->filterDepartmentId);
        }
        if ($this->filterExitType !== 'all' && ! empty($this->filterExitType)) {
            $query->where('exit_type', $this->filterExitType);
        }
        if ($this->filterExitReason !== 'all' && ! empty($this->filterExitReason)) {
            $query->where('exit_reason', $this->filterExitReason);
        }
        if (! empty($this->search)) {
            $s = '%'.$this->search.'%';
            $query->whereHas('employee', function ($q) use ($s) {
                $q->where('name', 'like', $s)->orWhere('email', 'like', $s);
            });
        }

        $allFilteredExits = (clone $query)->get();

        // Summary Calculations
        $totalExits = $allFilteredExits->count();
        $voluntaryExits = $allFilteredExits->where('exit_type', 'voluntary')->count();
        $involuntaryExits = $allFilteredExits->where('exit_type', 'involuntary')->count();

        // Average Headcount Calculation
        $totalStaff = User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))->where('role', 'employee')->count();
        $avgHeadcount = max(1, $totalStaff + ($totalExits / 2));
        $attritionRate = round(($totalExits / $avgHeadcount) * 100, 2);

        // Exit Reasons Distribution
        $exitReasonsBreakdown = $allFilteredExits->groupBy('exit_reason')->map(function ($group, $reason) use ($totalExits) {
            return [
                'reason' => $reason,
                'count' => $group->count(),
                'percentage' => $totalExits > 0 ? round(($group->count() / $totalExits) * 100, 1) : 0,
            ];
        })->sortByDesc('count')->values()->toArray();

        // Department Attrition Breakdown
        $departments = Department::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->get();
        $deptBreakdown = [];
        foreach ($departments as $dept) {
            $deptExits = $allFilteredExits->where('department_id', $dept->id)->count();
            if ($deptExits > 0) {
                $deptBreakdown[] = [
                    'department' => $dept->name,
                    'count' => $deptExits,
                ];
            }
        }

        // Monthly Trends
        $monthlyTrends = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create($selectedYear, $m, 1)->format('M');
            $mExits = EmployeeExit::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))
                ->whereYear('exit_date', $selectedYear)
                ->whereMonth('exit_date', $m)
                ->count();
            $monthlyTrends[] = [
                'month' => $monthName,
                'exits' => $mExits,
            ];
        }

        $paginatedExits = $query->orderByDesc('exit_date')->paginate($this->perPage);

        $branches = HrmsBranch::when($partnerId, fn ($q) => $q->where('partner_id', $partnerId))->orderBy('name')->get();
        $teams = $this->getTeams($partnerId);
        $exitReasonsList = ExitReason::where(function ($q) use ($partnerId) {
            $q->when($partnerId, fn ($sub) => $sub->whereNull('partner_id')->orWhere('partner_id', $partnerId));
        })->where('is_active', true)->pluck('name')->unique()->toArray();

        return view('livewire.admin.reports.hrms.attrition-report', [
            'totalExits' => $totalExits,
            'voluntaryExits' => $voluntaryExits,
            'involuntaryExits' => $involuntaryExits,
            'attritionRate' => $attritionRate,
            'exitReasonsBreakdown' => $exitReasonsBreakdown,
            'deptBreakdown' => $deptBreakdown,
            'monthlyTrends' => $monthlyTrends,
            'paginatedExits' => $paginatedExits,
            'branches' => $branches,
            'teams' => $teams,
            'departments' => $departments,
            'exitReasonsList' => $exitReasonsList,
        ])->layout('layouts.app', [
            'panelName' => 'Partner Panel',
            'pageTitle' => 'Attrition Report',
            'pageSubtitle' => 'Employee exit trends, voluntary vs involuntary attrition analysis',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }

    private function getTeams($partnerId)
    {
        $reportingQuery = User::when($partnerId, fn ($q) => $q->where('parent_id', $partnerId))
            ->where('role', 'employee')
            ->whereNotNull('reporting_to');

        if ($this->filterBranchId) {
            $reportingQuery->where('branch_id', $this->filterBranchId);
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
