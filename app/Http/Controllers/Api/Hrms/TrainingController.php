<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Department;
use App\Models\HrmsBranch;
use App\Models\Training as TrainingModel;
use App\Models\EmployeeTraining;
use App\Models\TrainingAttendance;
use App\Models\TrainingAssessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrainingController extends Controller
{
    /**
     * Helper to get partner_id for HRMS API authenticated user
     */
    private function getPartnerId()
    {
        $user = auth('hrms_api')->user();
        return $user->isPartner() ? $user->id : $user->parent_id;
    }

    /**
     * Get Training Dashboard Analytics & Paginated Assignments
     */
    public function dashboard(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', 'all');
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $trainingId = $request->input('training_id');
        $status = $request->input('status', 'all');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 10);

        // 1. Base Query for Employee Trainings
        $assignmentsQuery = EmployeeTraining::where('partner_id', $partnerId)
            ->whereYear('assigned_at', $year);

        if ($month !== 'all' && !empty($month)) {
            $assignmentsQuery->whereMonth('assigned_at', $month);
        }
        if (!empty($branchId)) {
            $assignmentsQuery->where('branch_id', $branchId);
        }
        if (!empty($departmentId)) {
            $assignmentsQuery->where('department_id', $departmentId);
        }
        if (!empty($trainingId)) {
            $assignmentsQuery->where('training_id', $trainingId);
        }
        if ($status !== 'all' && !empty($status)) {
            $assignmentsQuery->where('status', $status);
        }
        if (!empty($search)) {
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
        $deptBreakdown = Department::where('partner_id', $partnerId)->get()->map(function ($dept) use ($allFilteredAssignments) {
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
        $branchBreakdown = HrmsBranch::where('partner_id', $partnerId)->get()->map(function ($branch) use ($allFilteredAssignments) {
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
        $programBreakdown = TrainingModel::where('partner_id', $partnerId)->get()->map(function ($prog) use ($allFilteredAssignments) {
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

            $mItems = EmployeeTraining::where('partner_id', $partnerId)
                ->whereBetween('assigned_at', [$mStart, $mEnd])
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

        // 7. Paginated Assignments Table
        $paginatedAssignments = (clone $assignmentsQuery)
            ->with(['employee', 'training', 'branch', 'department'])
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'message' => 'Training dashboard analytics fetched successfully.',
            'summary' => [
                'total_assigned' => $totalAssigned,
                'total_completed' => $totalCompleted,
                'pending_training' => $pendingTraining,
                'avg_attendance_pct' => $avgAttendance,
                'avg_assessment_score_pct' => $avgAssessmentScore,
            ],
            'dept_breakdown' => $deptBreakdown,
            'branch_breakdown' => $branchBreakdown,
            'program_breakdown' => $programBreakdown,
            'monthly_trends' => $monthlyTrends,
            'data' => $paginatedAssignments,
        ]);
    }

    // ── Master Training Programs CRUD ──────────────────────────────────────────

    /**
     * Get list of Master Training Programs
     */
    public function getPrograms(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $query = TrainingModel::where('partner_id', $partnerId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('trainer', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $programs = $query->withCount('assignments')->orderBy('title')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Training programs fetched successfully.',
            'data' => $programs,
        ]);
    }

    /**
     * Single Training Program details
     */
    public function showProgram($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $program = TrainingModel::where('partner_id', $partnerId)
            ->withCount('assignments')
            ->with(['creator'])
            ->find($id);

        if (!$program) {
            return response()->json([
                'status' => 'error',
                'message' => 'Training program not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Training program details fetched successfully.',
            'data' => $program,
        ]);
    }

    /**
     * Create new Master Training Program
     */
    public function storeProgram(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $user = auth('hrms_api')->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer' => 'nullable|string|max:255',
            'training_type' => 'nullable|string|in:classroom,online,workshop,on_job,certification',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'duration' => 'required|integer|min:1',
            'passing_score' => 'required|numeric|min:0|max:100',
            'status' => 'nullable|string|in:scheduled,active,completed,cancelled',
        ]);

        $program = TrainingModel::create([
            'partner_id' => $partnerId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'trainer' => $validated['trainer'] ?? null,
            'training_type' => $validated['training_type'] ?? 'classroom',
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'duration' => $validated['duration'],
            'passing_score' => $validated['passing_score'],
            'status' => $validated['status'] ?? 'scheduled',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Training program created successfully.',
            'data' => $program,
        ], 201);
    }

    /**
     * Update Master Training Program
     */
    public function updateProgram(Request $request, $id): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $user = auth('hrms_api')->user();

        $program = TrainingModel::where('partner_id', $partnerId)->find($id);

        if (!$program) {
            return response()->json([
                'status' => 'error',
                'message' => 'Training program not found.'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'trainer' => 'nullable|string|max:255',
            'training_type' => 'nullable|string|in:classroom,online,workshop,on_job,certification',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'duration' => 'sometimes|required|integer|min:1',
            'passing_score' => 'sometimes|required|numeric|min:0|max:100',
            'status' => 'nullable|string|in:scheduled,active,completed,cancelled',
        ]);

        $validated['updated_by'] = $user->id;
        $program->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Training program updated successfully.',
            'data' => $program,
        ]);
    }

    /**
     * Delete Master Training Program
     */
    public function destroyProgram($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $program = TrainingModel::where('partner_id', $partnerId)->find($id);

        if (!$program) {
            return response()->json([
                'status' => 'error',
                'message' => 'Training program not found.'
            ], 404);
        }

        $program->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Training program deleted successfully.'
        ]);
    }

    // ── Bulk Employee Assignments ──────────────────────────────────────────────

    /**
     * Bulk Assign Training Program to Employees
     */
    public function assignEmployees(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $user = auth('hrms_api')->user();

        $validated = $request->validate([
            'training_id' => 'required|exists:trainings,id',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $training = TrainingModel::where('partner_id', $partnerId)->findOrFail($validated['training_id']);

        $assignedCount = 0;
        $createdAssignments = [];

        DB::beginTransaction();
        try {
            foreach ($validated['employee_ids'] as $empId) {
                $emp = User::where(function ($q) use ($partnerId) {
                    $q->where('parent_id', $partnerId)->orWhere('id', $partnerId);
                })->find($empId);

                if (!$emp) continue;

                $assignment = EmployeeTraining::firstOrCreate(
                    [
                        'partner_id' => $partnerId,
                        'training_id' => $training->id,
                        'employee_id' => $emp->id,
                    ],
                    [
                        'branch_id' => $emp->branch_id,
                        'department_id' => $emp->department_id,
                        'assigned_at' => now(),
                        'start_date' => $validated['start_date'],
                        'status' => 'assigned',
                        'total_sessions' => $training->duration ?: 1,
                        'sessions_attended' => 0,
                        'attendance_percentage' => 0.00,
                        'maximum_score' => 100.00,
                        'obtained_score' => 0.00,
                        'assessment_score' => 0.00,
                        'result' => 'pending',
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]
                );

                $createdAssignments[] = $assignment;
                $assignedCount++;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => "Successfully assigned training program to {$assignedCount} employees.",
                'count' => $assignedCount,
                'data' => $createdAssignments,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to assign training: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single training assignment details
     */
    public function showAssignment($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $assignment = EmployeeTraining::where('partner_id', $partnerId)
            ->with(['employee', 'training', 'branch', 'department', 'attendances', 'assessments'])
            ->find($id);

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Training assignment not found.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Training assignment details fetched successfully.',
            'data' => $assignment
        ]);
    }

    /**
     * Mark training assignment as completed
     */
    public function markCompleted($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();
        $user = auth('hrms_api')->user();

        $assignment = EmployeeTraining::where('partner_id', $partnerId)->find($id);

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Training assignment not found.'
            ], 404);
        }

        $assignment->update([
            'status' => 'completed',
            'completion_date' => date('Y-m-d'),
            'updated_by' => $user->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Employee training marked as completed successfully.',
            'data' => $assignment->fresh(['employee', 'training'])
        ]);
    }

    /**
     * Delete training assignment
     */
    public function destroyAssignment($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $assignment = EmployeeTraining::where('partner_id', $partnerId)->find($id);

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Training assignment not found.'
            ], 404);
        }

        $assignment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Training assignment deleted successfully.'
        ]);
    }

    // ── Training Session Attendance ──────────────────────────────────────────

    /**
     * Log training session attendance
     */
    public function logAttendance(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $validated = $request->validate([
            'employee_training_id' => 'required|exists:employee_trainings,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late',
            'remarks' => 'nullable|string|max:500',
        ]);

        $assignment = EmployeeTraining::where('partner_id', $partnerId)->find($validated['employee_training_id']);

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee training assignment not found.'
            ], 404);
        }

        $attendance = TrainingAttendance::updateOrCreate(
            [
                'employee_training_id' => $assignment->id,
                'date' => $validated['date'],
            ],
            [
                'training_id' => $assignment->training_id,
                'employee_id' => $assignment->employee_id,
                'status' => $validated['status'],
                'remarks' => $validated['remarks'] ?? null,
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

        return response()->json([
            'status' => 'success',
            'message' => 'Training session attendance logged successfully.',
            'attendance' => $attendance,
            'assignment' => $assignment->fresh()
        ]);
    }

    /**
     * Get attendance history for an assignment
     */
    public function getAttendanceLogs($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $assignment = EmployeeTraining::where('partner_id', $partnerId)->find($id);

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Training assignment not found.'
            ], 404);
        }

        $attendances = TrainingAttendance::where('employee_training_id', $assignment->id)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Attendance logs fetched successfully.',
            'data' => $attendances
        ]);
    }

    // ── Training Assessment Scoring ──────────────────────────────────────────

    /**
     * Save training assessment score
     */
    public function saveAssessment(Request $request): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $validated = $request->validate([
            'employee_training_id' => 'required|exists:employee_trainings,id',
            'maximum_score' => 'required|numeric|min:1',
            'obtained_score' => 'required|numeric|min:0|lte:maximum_score',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $assignment = EmployeeTraining::where('partner_id', $partnerId)
            ->with('training')
            ->find($validated['employee_training_id']);

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee training assignment not found.'
            ], 404);
        }

        $maxScore = floatval($validated['maximum_score']);
        $obtainedScore = floatval($validated['obtained_score']);
        $scorePct = ($maxScore > 0) ? round(($obtainedScore / $maxScore) * 100, 2) : 0;
        $passingScore = $assignment->training ? $assignment->training->passing_score : 60;
        $result = ($scorePct >= $passingScore) ? 'passed' : 'failed';

        $assessment = TrainingAssessment::create([
            'employee_training_id' => $assignment->id,
            'training_id' => $assignment->training_id,
            'employee_id' => $assignment->employee_id,
            'assessment_date' => date('Y-m-d'),
            'maximum_score' => $maxScore,
            'obtained_score' => $obtainedScore,
            'score_percentage' => $scorePct,
            'result' => $result,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $assignment->update([
            'maximum_score' => $maxScore,
            'obtained_score' => $obtainedScore,
            'assessment_score' => $scorePct,
            'result' => $result,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Assessment score saved successfully. Result: " . strtoupper($result),
            'assessment' => $assessment,
            'assignment' => $assignment->fresh()
        ]);
    }

    /**
     * Get assessment history for an assignment
     */
    public function getAssessmentLogs($id): JsonResponse
    {
        $partnerId = $this->getPartnerId();

        $assignment = EmployeeTraining::where('partner_id', $partnerId)->find($id);

        if (!$assignment) {
            return response()->json([
                'status' => 'error',
                'message' => 'Training assignment not found.'
            ], 404);
        }

        $assessments = TrainingAssessment::where('employee_training_id', $assignment->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Assessment logs fetched successfully.',
            'data' => $assessments
        ]);
    }

    /**
     * Export Training assignments report as CSV stream
     */
    public function exportCsv(Request $request)
    {
        $partnerId = $this->getPartnerId();

        $year = $request->input('year', date('Y'));
        $month = $request->input('month', 'all');
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $trainingId = $request->input('training_id');
        $status = $request->input('status', 'all');

        $query = EmployeeTraining::where('partner_id', $partnerId)
            ->whereYear('assigned_at', $year);

        if ($month !== 'all' && !empty($month)) $query->whereMonth('assigned_at', $month);
        if (!empty($branchId)) $query->where('branch_id', $branchId);
        if (!empty($departmentId)) $query->where('department_id', $departmentId);
        if (!empty($trainingId)) $query->where('training_id', $trainingId);
        if ($status !== 'all' && !empty($status)) $query->where('status', $status);

        $assignments = $query->with(['employee', 'training', 'branch', 'department'])->get();

        $filename = "training_report_" . date('Y_m_d_H_i') . ".csv";
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($assignments) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Employee Name', 'Employee Code', 'Training Title', 'Trainer', 'Branch', 'Department', 'Start Date', 'Status', 'Sessions Attended', 'Attendance %', 'Obtained Score', 'Score %', 'Result']);

            foreach ($assignments as $a) {
                fputcsv($file, [
                    $a->employee?->name ?? 'N/A',
                    $a->employee?->employee_code ?? 'N/A',
                    $a->training?->title ?? 'N/A',
                    $a->training?->trainer ?? 'N/A',
                    $a->branch?->name ?? 'N/A',
                    $a->department?->name ?? 'N/A',
                    $a->start_date ? $a->start_date->format('Y-m-d') : 'N/A',
                    ucfirst($a->status),
                    $a->sessions_attended,
                    $a->attendance_percentage . '%',
                    $a->obtained_score . '/' . $a->maximum_score,
                    $a->assessment_score . '%',
                    strtoupper($a->result)
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
