<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\EmployeeExit;
use App\Models\ExitReason;
use App\Models\User;
use App\Models\HrmsBranch;
use App\Models\Department;
use Carbon\Carbon;

class AttritionReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filterYear;
    public $filterMonth = 'all';
    public $filterBranchId = '';
    public $filterDepartmentId = '';
    public $filterExitType = 'all';
    public $filterExitReason = 'all';
    public $search = '';
    public $partnerFilter = '';
    public $perPage = 10;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterYear() { $this->resetPage(); }
    public function updatingFilterMonth() { $this->resetPage(); }
    public function updatingFilterBranchId() { $this->resetPage(); }
    public function updatingFilterDepartmentId() { $this->resetPage(); }
    public function updatingFilterExitType() { $this->resetPage(); }
    public function updatingFilterExitReason() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

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

    public function getPartnerId()
    {
        return auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
    }

    public function render()
    {
        
        $selectedYear = (int) ($this->filterYear ?: Carbon::now()->year);

        // 1. Base Query
        $query = EmployeeExit::with(['employee', 'branch', 'department'])
            
            ->whereYear('exit_date', $selectedYear);

        if ($this->filterMonth !== 'all' && !empty($this->filterMonth)) {
            $query->whereMonth('exit_date', (int) $this->filterMonth);
        }
        if (!empty($this->filterBranchId)) {
            $query->where('branch_id', $this->filterBranchId);
        }
        if (!empty($this->filterDepartmentId)) {
            $query->where('department_id', $this->filterDepartmentId);
        }
        if ($this->filterExitType !== 'all' && !empty($this->filterExitType)) {
            $query->where('exit_type', $this->filterExitType);
        }
        if ($this->filterExitReason !== 'all' && !empty($this->filterExitReason)) {
            $query->where('exit_reason', $this->filterExitReason);
        }
        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
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
        $totalStaff = User::where('role', 'employee')->count();
        $avgHeadcount = max(1, $totalStaff + ($totalExits / 2));
        $attritionRate = round(($totalExits / $avgHeadcount) * 100, 2);

        // Exit Reasons Distribution
        $exitReasonsBreakdown = $allFilteredExits->groupBy('exit_reason')->map(function ($group, $reason) use ($totalExits) {
            return [
                'reason' => $reason,
                'count'  => $group->count(),
                'percentage' => $totalExits > 0 ? round(($group->count() / $totalExits) * 100, 1) : 0,
            ];
        })->sortByDesc('count')->values()->toArray();

        // Department Attrition Breakdown
        $departments = Department::query()->get();
        $deptBreakdown = [];
        foreach ($departments as $dept) {
            $deptExits = $allFilteredExits->where('department_id', $dept->id)->count();
            if ($deptExits > 0) {
                $deptBreakdown[] = [
                    'department' => $dept->name,
                    'count'      => $deptExits,
                ];
            }
        }

        // Monthly Trends
        $monthlyTrends = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create($selectedYear, $m, 1)->format('M');
            $mExits = EmployeeExit::query()
                ->whereYear('exit_date', $selectedYear)
                ->whereMonth('exit_date', $m)
                ->count();
            $monthlyTrends[] = [
                'month' => $monthName,
                'exits' => $mExits,
            ];
        }

        $paginatedExits = $query->orderByDesc('exit_date')->paginate($this->perPage);

        $branches = HrmsBranch::query()->orderBy('name')->get();
        $exitReasonsList = ExitReason::where(function($q) {
            $q->whereNull('partner_id')->orWhere('partner_id', "");
        })->where('is_active', true)->pluck('name')->unique()->toArray();

        return view('livewire.admin.reports.hrms.attrition-report', [
            'totalExits'           => $totalExits,
            'voluntaryExits'       => $voluntaryExits,
            'involuntaryExits'     => $involuntaryExits,
            'attritionRate'        => $attritionRate,
            'exitReasonsBreakdown' => $exitReasonsBreakdown,
            'deptBreakdown'        => $deptBreakdown,
            'monthlyTrends'        => $monthlyTrends,
            'paginatedExits'       => $paginatedExits,
            'branches'             => $branches,
            'departments'          => $departments,
            'exitReasonsList'      => $exitReasonsList,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Attrition Report',
            'pageSubtitle' => 'Employee exit trends, voluntary vs involuntary attrition analysis',
            'sidebarLinks' => view(auth()->check() && auth()->user()->role === 'employee' ? 'partials.sidebar-employee' : 'partials.sidebar-admin'),
        ]);
    }
}
