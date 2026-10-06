<?php

namespace App\Livewire\Admin\Reports\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\Training;
use App\Models\EmployeeTraining;
use Carbon\Carbon;

class TrainingReport extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $filterProgramId = '';
    public $filterStatus = 'all';
    public $filterBranchId = '';
    public $filterDepartmentId = '';
    public $filterResult = 'all';
    public $search = '';
    public $partnerFilter = '';
    public $perPage = 10;

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterProgramId() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterBranchId() { $this->resetPage(); }
    public function updatingFilterDepartmentId() { $this->resetPage(); }
    public function updatingFilterResult() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('training_viewAny') ||
            auth()->user()->canAccess('training_view') ||
            auth()->user()->canAccess('training_viewteam'),
            403
        );
    }

    public function getPartnerId()
    {
        return auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
    }

    public function render()
    {
        

        // 1. Filtered Assignments Base Query
        $query = EmployeeTraining::with(['training', 'employee', 'department', 'branch'])
            ->whereHas('training', function($q) {
                $q;
            });

        if (!empty($this->filterProgramId)) {
            $query->where('training_id', $this->filterProgramId);
        }
        if ($this->filterStatus !== 'all' && !empty($this->filterStatus)) {
            $query->where('status', $this->filterStatus);
        }
        if (!empty($this->filterBranchId)) {
            $query->where('branch_id', $this->filterBranchId);
        }
        if (!empty($this->filterDepartmentId)) {
            $query->where('department_id', $this->filterDepartmentId);
        }
        if ($this->filterResult !== 'all' && !empty($this->filterResult)) {
            $query->where('result', $this->filterResult);
        }
        if (!empty($this->search)) {
            $s = '%' . $this->search . '%';
            $query->where(function($q) use ($s) {
                $q->whereHas('employee', function($eq) use ($s) {
                    $eq->where('name', 'like', $s)->orWhere('employee_code', 'like', $s);
                })->orWhereHas('training', function($tq) use ($s) {
                    $tq->where('title', 'like', $s)->orWhere('trainer', 'like', $s);
                });
            });
        }

        $allFilteredAssignments = (clone $query)->get();

        // 2. Summary KPI Metrics
        $totalAssigned = $allFilteredAssignments->count();
        $totalCompleted = $allFilteredAssignments->where('status', 'completed')->count();
        $totalPending = $allFilteredAssignments->whereIn('status', ['assigned', 'in_progress'])->count();
        $avgAttendance = $totalAssigned > 0 ? round($allFilteredAssignments->avg('attendance_percentage'), 1) : 0;
        $avgAssessmentScore = $totalCompleted > 0 ? round($allFilteredAssignments->where('status', 'completed')->avg('assessment_score'), 1) : 0;

        // 3. Monthly Trends for Chart (Current Year)
        $currentYear = Carbon::now()->year;
        $monthlyTrends = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create($currentYear, $m, 1)->format('M');
            $monthlyAssigned = EmployeeTraining::whereHas('training', function($q) {
                $q;
            })->whereYear('assigned_at', $currentYear)->whereMonth('assigned_at', $m)->count();

            $monthlyCompleted = EmployeeTraining::whereHas('training', function($q) {
                $q;
            })->where('status', 'completed')->whereYear('assigned_at', $currentYear)->whereMonth('assigned_at', $m)->count();

            $monthlyTrends[] = [
                'month'     => $monthName,
                'assigned'  => $monthlyAssigned,
                'completed' => $monthlyCompleted,
            ];
        }

        // 4. Department Breakdown for Chart
        $departments = Department::query()->orderBy('name')->get();
        $deptBreakdown = [];
        foreach ($departments as $dept) {
            $deptCompleted = EmployeeTraining::where('department_id', $dept->id)->where('status', 'completed')->count();
            if ($deptCompleted > 0) {
                $deptBreakdown[] = [
                    'department' => $dept->name,
                    'completed'  => $deptCompleted,
                ];
            }
        }

        // Paginated Assignments
        $paginatedAssignments = $query->orderByDesc('created_at')->paginate($this->perPage);

        // Programs & Branches Filter Options
        $trainingPrograms = Training::query()->orderBy('title')->get();
        $branches = HrmsBranch::query()->orderBy('name')->get();

        return view('livewire.admin.reports.hrms.training-report', [
            'totalAssigned'       => $totalAssigned,
            'totalCompleted'      => $totalCompleted,
            'totalPending'        => $totalPending,
            'avgAttendance'       => $avgAttendance,
            'avgAssessmentScore'  => $avgAssessmentScore,
            'monthlyTrends'       => $monthlyTrends,
            'deptBreakdown'       => $deptBreakdown,
            'paginatedAssignments' => $paginatedAssignments,
            'trainingPrograms'    => $trainingPrograms,
            'branches'            => $branches,
            'departments'         => $departments,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Training Report',
            'pageSubtitle' => 'Employee training assignments, attendance, and assessment scores',
            'sidebarLinks' => view(auth()->check() && auth()->user()->role === 'employee' ? 'partials.sidebar-employee' : 'partials.sidebar-admin'),
        ]);
    }
}
