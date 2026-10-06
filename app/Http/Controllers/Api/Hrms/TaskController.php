<?php

namespace App\Http\Controllers\Api\Hrms;

use App\Http\Controllers\Api\Hrms\Traits\HasHrmsApiFilters;
use App\Http\Controllers\Controller;
use App\Models\EmployeeTask;
use App\Models\TaskRemark;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class TaskController extends Controller
{
    use HasHrmsApiFilters;

    /**
     * Get own tasks
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $tasks = EmployeeTask::with('assigner')
            ->whereHas('employee', fn ($q) => $q->activeForHrms())
            ->orderBy('created_at', 'desc')
            ->get()
            ->each(fn ($task) => $this->markOverdue($task));

        $message = $tasks->isEmpty() ? 'No tasks found.' : 'Tasks fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $tasks,
        ]);
    }

    /**
     * Whether the user has the task update permission (strict DB check).
     * Admins are always allowed; partners mostly get this permission too.
     */
    protected function canUpdateTaskStatus(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        try {
            return $user->hasPermissionTo('task_update', 'web');
        } catch (PermissionDoesNotExist $e) {
            return false;
        }
    }

    /**
     * Update task status
     */
  public function updateStatus(Request $request, $id): JsonResponse
{
    $user = auth('hrms_api')->user();

    $task = EmployeeTask::findOrFail($id);
     

    if (! $user->canAccess('task_update')) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized.',
        ], 403);
    }

    $request->validate([
        'status' => [
            'required',
            'string',
            Rule::exists('task_statuses', 'slug'),
        ],
        'remark' => 'nullable|string',
    ]);

    $task->update([
        'status' => $request->status,
        'remark' => $request->remark,
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Task status updated.',
        'data' => $task,
    ]);
}

    /**
     * Get team tasks
     */
    public function teamTasks(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $this->getTeamEmployeeIds('task_viewAny');

        $query = EmployeeTask::whereIn('employee_id', $teamIds)
            ->whereHas('employee', fn ($q) => $q->activeForHrms())
            ->with(['employee', 'assigner']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->orderBy('created_at', 'desc')->paginate(15);
        $tasks->getCollection()->each(fn ($task) => $this->markOverdue($task));

        $message = $tasks->isEmpty() ? 'No team tasks found.' : 'Team tasks fetched successfully.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $tasks,
        ]);
    }

    /**
     * Get users assignable for tasks in the same branch
     */
    public function assignableUsers(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        if (! $user->isPartner() && ! $user->canAccess('task_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $query = User::where('role', 'employee')
            ->where('parent_id', $this->getPartnerId())
            ->availableForHrmsAssignment();

        if ($user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        $users = $query->select('id', 'name', 'email', 'employee_code', 'profile_image')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Assignable users fetched successfully.',
            'data' => $users,
        ]);
    }

    /**
     * Assign a task to a team member (mirrors Tasks::saveTask for scope=all)
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('hrms_api')->user();

        // Access (mirrors canCreateTask: partner always, else task_create)
        if (! $user->isPartner() && ! $user->canAccess('task_create')) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $partnerId = $this->getPartnerId();

        // Active partner task statuses (mirrors loadData taskStatuses)
        $statuses = TaskStatus::where('partner_id', $partnerId)->where('status', true)->orderBy('order')->get();
        $statusSlugs = $statuses->pluck('slug')->all();
        $defaultStatus = $statusSlugs[0] ?? 'pending';
        $allowedStatuses = array_values(array_unique(array_merge($statusSlugs, ['pending', 'in_progress', 'completed'])));

        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['nullable', 'string', 'max:100', Rule::in($allowedStatuses)],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($request->filled('start_date') && $request->filled('end_date') &&
            Carbon::parse($request->end_date)->lt(Carbon::parse($request->start_date))) {
            return response()->json([
                'status' => 'error',
                'message' => 'The end date must be a date after or equal to the start date.',
                'errors' => ['end_date' => ['The end date must be a date after or equal to the start date.']],
            ], 422);
        }

        $allowedIds = $this->getTeamEmployeeIds('task_viewAny');

        // Employee must be in allowed scope AND available for assignment (mirrors the select2 options + saveTask gate)
        $employee = User::whereKey($request->employee_id)
            ->whereIn('id', $allowedIds)
            ->availableForHrmsAssignment()
            ->first();

        if (! $employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee is not allowed or not available for task assignment (resigned/terminated).',
            ], 403);
        }

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date)->format('Y-m-d') : null;
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date)->format('Y-m-d') : ($request->filled('due_date') ? Carbon::parse($request->due_date)->format('Y-m-d') : null);
        if (! $endDate) {
            $endDate = $startDate;
        }

        $task = EmployeeTask::create([
            'employee_id' => $request->employee_id,
            'assigned_by' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->filled('status') ? $request->status : $defaultStatus,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'due_date' => $endDate,
        ]);

        $task->load(['employee:id,name,employee_code,email', 'assigner:id,name']);
        $this->markOverdue($task);

        return response()->json([
            'status' => 'success',
            'message' => 'Task assigned successfully.',
            'data' => $task,
        ], 201);
    }

    /**
     * Update task details (Manager)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $user->getTeamIds();

        $task = EmployeeTask::findOrFail($id);

        if (! $user->canAccess('task_update') || ! in_array($task->employee_id, $teamIds)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:pending,in_progress,completed',
        ]);

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'status' => $request->status ?? $task->status,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Task updated successfully.',
            'data' => $task,
        ]);
    }

    /**
     * Delete a task (Manager)
     */
    public function destroy($id): JsonResponse
    {
        $user = auth('hrms_api')->user();
        $teamIds = $user->getTeamIds();

        $task = EmployeeTask::findOrFail($id);

        if (! $user->canAccess('task_delete') || ! in_array($task->employee_id, $teamIds)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        }

        $task->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Task deleted successfully.',
        ]);
    }

    /**
     * Add remark to a task
     */
    public function addRemark(Request $request, $id): JsonResponse
    {
        $request->validate([
            'remark' => 'required|string|max:2000',
        ]);

        $task = EmployeeTask::whereHas('employee', fn ($q) => $q->activeForHrms())->find($id);
        if (! $task) {
            return response()->json(['status' => 'error', 'message' => 'Task not found.'], 404);
        }

        $remark = TaskRemark::create([
            'task_id' => $task->id,
            'user_id' => auth('hrms_api')->id(),
            'remark' => $request->remark,
        ]);

        $remark->load('user:id,name');

        return response()->json([
            'status' => 'success',
            'message' => 'Remark added successfully.',
            'data' => $remark,
        ], 201);
    }

    /**
     * View remarks for a task
     */
    public function viewRemarks(Request $request, $id): JsonResponse
    {
        $task = EmployeeTask::with(['remarks' => fn ($q) => $q->with('user:id,name')->latest()])
            ->whereHas('employee', fn ($q) => $q->activeForHrms())
            ->find($id);

        if (! $task) {
            return response()->json(['status' => 'error', 'message' => 'Task not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Remarks fetched successfully.',
            'data' => [
                'task_id' => $task->id,
                'title' => $task->title,
                'remarks' => $task->remarks->map(fn ($r) => [
                    'id' => $r->id,
                    'user_name' => $r->user->name ?? null,
                    'remark' => $r->remark,
                    'created_at' => $r->created_at ? $r->created_at->format('M d, Y h:i A') : null,
                ]),
            ],
        ]);
    }

    /**
     * Mark whether a task is overdue and how many days overdue.
     * Mirrors the web page logic in livewire/partner/hrms/tasks.blade.php
     */
    protected function markOverdue(EmployeeTask $task): EmployeeTask
    {
        $matchedStatus = TaskStatus::where('partner_id', $this->getPartnerId())
            ->where(fn ($q) => $q->where('slug', $task->status)->orWhere('name', $task->status))
            ->first();
        $isCompleted = ($task->status === 'completed') || ($matchedStatus && $matchedStatus->is_completed);

        $endDateVal = $task->end_date ?? $task->due_date;
        $parsedEndDate = $endDateVal ? Carbon::parse($endDateVal) : null;
        $endDateCarbon = $parsedEndDate ? $parsedEndDate->copy()->endOfDay() : null;

        $task->is_overdue = ! $isCompleted && $endDateCarbon && $endDateCarbon->isPast();
        $task->overdue_days = $task->is_overdue && $parsedEndDate ? (int) $parsedEndDate->diffInDays(Carbon::now()) : 0;

        return $task;
    }
}
