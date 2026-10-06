<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\Training as TrainingModel;
use App\Models\EmployeeTraining;
use App\Models\TrainingAttendance;
use App\Models\TrainingAssessment;
use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;
use Carbon\Carbon;

class Training extends Component
{
    use WithPagination, HasPartnerId, HasHrmsFilters;

    protected $paginationTheme = 'bootstrap';

    public $activeTab = 'dashboard'; // 'dashboard', 'programs', 'assignments'

    // Filters
    public $filterYear;
    public $filterMonth = 'all';
    public $filterBranchId = '';
    public $filterDepartmentId = '';
    public $filterTrainingId = '';
    public $filterStatus = 'all';
    public $search = '';
    public $perPage = 10;

    // Training Master Modal State
    public $showTrainingModal = false;
    public $editingTrainingId = null;
    public $title = '';
    public $description = '';
    public $trainer = '';
    public $training_type = 'classroom';
    public $start_date = '';
    public $end_date = '';
    public $duration = 1;
    public $passing_score = 60.00;
    public $status = 'scheduled';

    // Assignment Modal State
    public $showAssignModal = false;
    public $selectedTrainingId = '';
    public $assignBranchId = '';
    public $assignDepartmentId = '';
    public $selectedEmployeeIds = [];
    public $selectAllEmployees = false;
    public $assignStartDate = '';
    public $assignEndDate = '';

    // Attendance Modal State
    public $showAttendanceModal = false;
    public $attAssignmentId = null;
    public $attDate = '';
    public $attStatus = 'present';
    public $attRemarks = '';

    // Assessment Modal State
    public $showAssessmentModal = false;
    public $assessAssignmentId = null;
    public $assessMaxScore = 100.00;
    public $assessObtainedScore = 0.00;
    public $assessRemarks = '';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterYear() { $this->resetPage(); }
    public function updatingFilterMonth() { $this->resetPage(); }
    public function updatingFilterBranchId() { $this->resetPage(); }
    public function updatingFilterDepartmentId() { $this->resetPage(); }
    public function updatingFilterTrainingId() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function mount()
    {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('training_view') ||
            auth()->user()->canAccess('training_viewany') ||
            auth()->user()->canAccess('training_viewBranch') ||
            auth()->user()->canAccess('training_viewTeam') ||
            auth()->user()->canAccess('employeecost_viewany'),
            403
        );

        $this->filterYear = date('Y');
        $this->start_date = date('Y-m-d');
        $this->end_date = date('Y-m-d', strtotime('+7 days'));
        $this->assignStartDate = date('Y-m-d');
        $this->assignEndDate = date('Y-m-d', strtotime('+7 days'));
        $this->attDate = date('Y-m-d');
    }

    // ── Master Training CRUD ──────────────────────────────────────────
    public function openTrainingModal($id = null)
    {
        $this->resetValidation();
        if ($id) {
            $t = TrainingModel::findOrFail($id);
            $this->editingTrainingId = $t->id;
            $this->title = $t->title;
            $this->description = $t->description;
            $this->trainer = $t->trainer;
            $this->training_type = $t->training_type;
            $this->start_date = $t->start_date ? $t->start_date->format('Y-m-d') : '';
            $this->end_date = $t->end_date ? $t->end_date->format('Y-m-d') : '';
            $this->duration = $t->duration;
            $this->passing_score = $t->passing_score;
            $this->status = $t->status;
        } else {
            $this->editingTrainingId = null;
            $this->title = '';
            $this->description = '';
            $this->trainer = '';
            $this->training_type = 'classroom';
            $this->start_date = date('Y-m-d');
            $this->end_date = date('Y-m-d', strtotime('+7 days'));
            $this->duration = 1;
            $this->passing_score = 60.00;
            $this->status = 'scheduled';
        }

        $this->showTrainingModal = true;
    }

    public function closeTrainingModal()
    {
        $this->showTrainingModal = false;
    }

    public function saveTraining()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'passing_score' => 'required|numeric|min:0|max:100',
            'duration' => 'required|integer|min:1',
        ]);

        $partnerId = $this->getPartnerId();

        $match = ['id' => $this->editingTrainingId];
        if (!auth()->user()->isSuperAdmin()) {
            $match['partner_id'] = $partnerId;
        } elseif (!$this->editingTrainingId) {
            $match['partner_id'] = $this->requirePartnerId();
        }

        TrainingModel::updateOrCreate(
            $match,
            [
                'title' => $this->title,
                'description' => $this->description,
                'trainer' => $this->trainer,
                'training_type' => $this->training_type,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date ?: null,
                'duration' => $this->duration,
                'passing_score' => $this->passing_score,
                'status' => $this->status,
                'created_by' => auth()->id(),
            ]
        );

        session()->flash('success', $this->editingTrainingId ? 'Training program updated successfully.' : 'New Training program created successfully.');
        $this->closeTrainingModal();
    }

    public function deleteTraining($id)
    {
        $t = TrainingModel::findOrFail($id);
        $t->delete();
        session()->flash('success', 'Training program deleted successfully.');
    }

    // ── Bulk Employee Assignment ──────────────────────────────────────
    public function openAssignModal($trainingId = null)
    {
        $this->resetValidation();
        $this->selectedTrainingId = $trainingId ?: '';
        $this->selectedEmployeeIds = [];
        $this->selectAllEmployees = false;
        $this->assignBranchId = '';
        $this->assignDepartmentId = '';
        $this->showAssignModal = true;
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
    }

    public function toggleSelectAllEmployees()
    {
        if ($this->selectAllEmployees) {
            $partnerId = $this->getPartnerId();
            $query = User::where(function ($q) use ($partnerId) {
                $q->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('parent_id', $partnerId); } })->orWhere('id', $partnerId);
            })->whereIn('role', ['employee', 'manager']);

            if (!empty($this->assignBranchId)) {
                $query->where('branch_id', $this->assignBranchId);
            }
            if (!empty($this->assignDepartmentId)) {
                $query->where('department_id', $this->assignDepartmentId);
            }

            $this->selectedEmployeeIds = $query->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedEmployeeIds = [];
        }
    }

    public function assignEmployees()
    {
        $this->validate([
            'selectedTrainingId' => 'required|exists:trainings,id',
            'selectedEmployeeIds' => 'required|array|min:1',
            'assignStartDate' => 'required|date',
            'assignEndDate' => 'nullable|date|after_or_equal:assignStartDate',
        ]);

        $partnerId = $this->getPartnerId();
        $training = TrainingModel::findOrFail($this->selectedTrainingId);

        $assignedCount = 0;
        foreach ($this->selectedEmployeeIds as $empId) {
            $emp = User::find($empId);
            if (!$emp) continue;

            EmployeeTraining::firstOrCreate(
                [
                    'partner_id' => $this->requirePartnerId(),
                    'training_id' => $training->id,
                    'employee_id' => $emp->id,
                ],
                [
                    'branch_id' => $emp->branch_id,
                    'department_id' => $emp->department_id,
                    'assigned_at' => now(),
                    'start_date' => $this->assignStartDate,
                    'status' => 'assigned',
                    'total_sessions' => $training->duration ?: 1,
                    'sessions_attended' => 0,
                    'attendance_percentage' => 0.00,
                    'maximum_score' => 100.00,
                    'obtained_score' => 0.00,
                    'assessment_score' => 0.00,
                    'result' => 'pending',
                    'created_by' => auth()->id(),
                ]
            );
            $assignedCount++;
        }

        session()->flash('success', "Assigned training program to {$assignedCount} employees successfully.");
        $this->closeAssignModal();
    }

    // ── Attendance Logging ───────────────────────────────────────────
    public function openAttendanceModal($assignmentId)
    {
        $this->resetValidation();
        $this->attAssignmentId = $assignmentId;
        $this->attDate = date('Y-m-d');
        $this->attStatus = 'present';
        $this->attRemarks = '';
        $this->showAttendanceModal = true;
    }

    public function closeAttendanceModal()
    {
        $this->showAttendanceModal = false;
    }

    public function saveAttendance()
    {
        $this->validate([
            'attDate' => 'required|date',
            'attStatus' => 'required|in:present,absent,late',
        ]);

        $assignment = EmployeeTraining::findOrFail($this->attAssignmentId);

        TrainingAttendance::updateOrCreate(
            [
                'employee_training_id' => $assignment->id,
                'date' => $this->attDate,
            ],
            [
                'training_id' => $assignment->training_id,
                'employee_id' => $assignment->employee_id,
                'status' => $this->attStatus,
                'remarks' => $this->attRemarks,
            ]
        );

        // Recalculate Attendance Percentage
        $presentCount = TrainingAttendance::where('employee_training_id', $assignment->id)
            ->whereIn('status', ['present', 'late'])
            ->count();

        $totalSessions = max(1, $assignment->total_sessions);
        $attPercentage = round(($presentCount / $totalSessions) * 100, 2);

        $assignment->update([
            'sessions_attended' => $presentCount,
            'attendance_percentage' => min(100.00, $attPercentage),
            'status' => ($assignment->status === 'assigned') ? 'in_progress' : $assignment->status,
        ]);

        session()->flash('success', 'Training session attendance logged successfully.');
        $this->closeAttendanceModal();
    }

    // ── Assessment Scoring ───────────────────────────────────────────
    public function openAssessmentModal($assignmentId)
    {
        $this->resetValidation();
        $assignment = EmployeeTraining::findOrFail($assignmentId);
        $this->assessAssignmentId = $assignment->id;
        $this->assessMaxScore = $assignment->maximum_score ?: 100.00;
        $this->assessObtainedScore = $assignment->obtained_score ?: 0.00;
        $this->assessRemarks = $assignment->remarks ?: '';
        $this->showAssessmentModal = true;
    }

    public function closeAssessmentModal()
    {
        $this->showAssessmentModal = false;
    }

    public function saveAssessment()
    {
        $this->validate([
            'assessMaxScore' => 'required|numeric|min:1',
            'assessObtainedScore' => 'required|numeric|min:0|lte:assessMaxScore',
        ]);

        $assignment = EmployeeTraining::with('training')->findOrFail($this->assessAssignmentId);

        $scorePct = ($this->assessMaxScore > 0) ? round(($this->assessObtainedScore / $this->assessMaxScore) * 100, 2) : 0;
        $passingScore = $assignment->training ? $assignment->training->passing_score : 60;
        $result = ($scorePct >= $passingScore) ? 'passed' : 'failed';

        TrainingAssessment::create([
            'employee_training_id' => $assignment->id,
            'training_id' => $assignment->training_id,
            'employee_id' => $assignment->employee_id,
            'assessment_date' => date('Y-m-d'),
            'maximum_score' => $this->assessMaxScore,
            'obtained_score' => $this->assessObtainedScore,
            'score_percentage' => $scorePct,
            'result' => $result,
            'remarks' => $this->assessRemarks,
        ]);

        $assignment->update([
            'maximum_score' => $this->assessMaxScore,
            'obtained_score' => $this->assessObtainedScore,
            'assessment_score' => $scorePct,
            'result' => $result,
            'remarks' => $this->assessRemarks,
        ]);

        session()->flash('success', "Assessment score saved. Result: " . strtoupper($result));
        $this->closeAssessmentModal();
    }

    // ── Mark Completed ───────────────────────────────────────────────
    public function markCompleted($assignmentId)
    {
        $assignment = EmployeeTraining::findOrFail($assignmentId);
        $assignment->update([
            'status' => 'completed',
            'completion_date' => date('Y-m-d'),
        ]);

        session()->flash('success', 'Employee training marked as completed successfully.');
    }

    public function render()
    {
        $partnerId = $this->getPartnerId();
        $year = $this->filterYear ?: date('Y');

        // 1. Base Query for Employee Trainings
        $assignmentsQuery = EmployeeTraining::whereYear('assigned_at', $year);
            
        $assignmentsQuery = $this->applyHrmsFilters($assignmentsQuery, 'employee_id', 'training_viewany');

        if ($this->filterMonth !== 'all' && !empty($this->filterMonth)) {
            $assignmentsQuery->whereMonth('assigned_at', $this->filterMonth);
        }
        if (!empty($this->filterBranchId)) {
            $assignmentsQuery->where('branch_id', $this->filterBranchId);
        }
        if (!empty($this->filterDepartmentId)) {
            $assignmentsQuery->where('department_id', $this->filterDepartmentId);
        }
        if (!empty($this->filterTrainingId)) {
            $assignmentsQuery->where('training_id', $this->filterTrainingId);
        }
        if ($this->filterStatus !== 'all' && !empty($this->filterStatus)) {
            $assignmentsQuery->where('status', $this->filterStatus);
        }
        if (!empty($this->search)) {
            $search = $this->search;
            $assignmentsQuery->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            })->orWhereHas('training', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('trainer', 'like', "%{$search}%");
            });
        }

        $allFilteredAssignments = (clone $assignmentsQuery)->with(['employee', 'training', 'branch', 'department'])->get();

        // 2. Summary KPI Metrics
        $totalAssigned = $allFilteredAssignments->count();
        $totalCompleted = $allFilteredAssignments->where('status', 'completed')->count();
        $totalCancelled = $allFilteredAssignments->where('status', 'cancelled')->count();
        $pendingTraining = max(0, $totalAssigned - $totalCompleted - $totalCancelled);

        $avgAttendance = $totalAssigned > 0 ? round($allFilteredAssignments->avg('attendance_percentage'), 1) : 0;
        $avgAssessmentScore = $totalAssigned > 0 ? round($allFilteredAssignments->avg('assessment_score'), 1) : 0;

        // 3. Department-wise Breakdown
        $deptBreakdown = Department::get()->map(function ($dept) use ($allFilteredAssignments) {
            $deptItems = $allFilteredAssignments->where('department_id', $dept->id);
            $assigned = $deptItems->count();
            $completed = $deptItems->where('status', 'completed')->count();
            $cancelled = $deptItems->where('status', 'cancelled')->count();
            $pending = max(0, $assigned - $completed - $cancelled);
            $att = $assigned > 0 ? round($deptItems->avg('attendance_percentage'), 1) : 0;
            $score = $assigned > 0 ? round($deptItems->avg('assessment_score'), 1) : 0;

            return [
                'id' => $dept->id,
                'department' => $dept->name,
                'assigned' => $assigned,
                'completed' => $completed,
                'pending' => $pending,
                'attendance_pct' => $att,
                'score_pct' => $score,
            ];
        })->filter(fn($d) => $d['assigned'] > 0)->values();

        // 4. Branch-wise Breakdown
        $branchBreakdown = HrmsBranch::get()->map(function ($branch) use ($allFilteredAssignments) {
            $branchItems = $allFilteredAssignments->where('branch_id', $branch->id);
            $assigned = $branchItems->count();
            $completed = $branchItems->where('status', 'completed')->count();
            $cancelled = $branchItems->where('status', 'cancelled')->count();
            $pending = max(0, $assigned - $completed - $cancelled);
            $att = $assigned > 0 ? round($branchItems->avg('attendance_percentage'), 1) : 0;
            $score = $assigned > 0 ? round($branchItems->avg('assessment_score'), 1) : 0;

            return [
                'id' => $branch->id,
                'branch' => $branch->name,
                'assigned' => $assigned,
                'completed' => $completed,
                'pending' => $pending,
                'attendance_pct' => $att,
                'score_pct' => $score,
            ];
        })->filter(fn($b) => $b['assigned'] > 0)->values();

        // 5. Training Program Summary
        $programBreakdown = TrainingModel::get()->map(function ($prog) use ($allFilteredAssignments) {
            $progItems = $allFilteredAssignments->where('training_id', $prog->id);
            $assigned = $progItems->count();
            $completed = $progItems->where('status', 'completed')->count();
            $cancelled = $progItems->where('status', 'cancelled')->count();
            $pending = max(0, $assigned - $completed - $cancelled);
            $att = $assigned > 0 ? round($progItems->avg('attendance_percentage'), 1) : 0;
            $score = $assigned > 0 ? round($progItems->avg('assessment_score'), 1) : 0;

            return [
                'id' => $prog->id,
                'title' => $prog->title,
                'trainer' => $prog->trainer,
                'assigned' => $assigned,
                'completed' => $completed,
                'pending' => $pending,
                'attendance_pct' => $att,
                'score_pct' => $score,
            ];
        })->values();

        // 6. Monthly Trends Breakdown (12 Months)
        $monthlyTrends = [];
        for ($m = 1; $m <= 12; $m++) {
            $mStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
            $mEnd = Carbon::createFromDate($year, $m, 1)->endOfMonth();

            $mItems = EmployeeTraining::whereBetween('assigned_at', [$mStart, $mEnd])
                ->get();

            $mAssigned = $mItems->count();
            $mCompleted = $mItems->where('status', 'completed')->count();
            $mCancelled = $mItems->where('status', 'cancelled')->count();
            $mPending = max(0, $mAssigned - $mCompleted - $mCancelled);
            $mAtt = $mAssigned > 0 ? round($mItems->avg('attendance_percentage'), 1) : 0;
            $mScore = $mAssigned > 0 ? round($mItems->avg('assessment_score'), 1) : 0;

            $monthlyTrends[] = [
                'month' => $mStart->format('M'),
                'assigned' => $mAssigned,
                'completed' => $mCompleted,
                'pending' => $mPending,
                'attendance_pct' => $mAtt,
                'score_pct' => $mScore,
            ];
        }

        // 7. Paginated Assignments & Training Programs Table
        $paginatedAssignments = (clone $assignmentsQuery)->with(['employee', 'training', 'branch', 'department'])
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        $trainingProgramsList = TrainingModel::orderBy('title')->get();
        $branches = HrmsBranch::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        // Employees for bulk assignment filter
        $assignableEmployeesQuery = User::whereIn('role', ['employee', 'manager']);

        if (!empty($this->assignBranchId)) {
            $assignableEmployeesQuery->where('branch_id', $this->assignBranchId);
        }
        if (!empty($this->assignDepartmentId)) {
            $assignableEmployeesQuery->where('department_id', $this->assignDepartmentId);
        }

        $assignableEmployees = $assignableEmployeesQuery->orderBy('name')->get();

        return view('livewire.partner.hrms.training', [
            'totalAssigned' => $totalAssigned,
            'totalCompleted' => $totalCompleted,
            'avgAttendance' => $avgAttendance,
            'avgAssessmentScore' => $avgAssessmentScore,
            'pendingTraining' => $pendingTraining,
            'deptBreakdown' => $deptBreakdown,
            'branchBreakdown' => $branchBreakdown,
            'programBreakdown' => $programBreakdown,
            'monthlyTrends' => $monthlyTrends,
            'paginatedAssignments' => $paginatedAssignments,
            'trainingProgramsList' => $trainingProgramsList,
            'branches' => $branches,
            'departments' => $departments,
            'assignableEmployees' => $assignableEmployees,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Module',
            'pageTitle'    => 'Training Management',
            'pageSubtitle' => 'Track training assignments, attendance, assessment scores, and completion progress',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
