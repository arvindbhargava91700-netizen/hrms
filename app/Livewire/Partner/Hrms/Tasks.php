<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\EmployeeTask;
use App\Models\TaskRemark;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Livewire\Partner\Hrms\Traits\HasHrmsFilters;
use App\Models\TaskStatus;

class Tasks extends Component
{
    use HasPartnerId, HasHrmsFilters;

    public $tasks = [];
    public $employees = [];
    public $taskStatuses = [];
    
    public $isModalOpen = false;
    public $editingId = null;

    public $employee_id, $title, $description, $status = 'pending', $start_date, $end_date;

    // Status update (assigned employee only)
    public $isStatusModalOpen = false;
    public $statusUpdateId = null;
    public $updateStatus = '';
    public $remark = '';

    // Remarks (separate table, any viewer can add)
    public $isRemarkModalOpen = false;
    public $remarkTaskId = null;
    public $remarkText = '';

    // View remarks
    public $isViewRemarksOpen = false;
    public $viewRemarkTaskTitle = '';
    public $viewRemarks = [];

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('task_viewAny') || auth()->user()->canAccess('task_viewOwn') || auth()->user()->canAccess('task_viewBranch') || auth()->user()->canAccess('task_viewteam'), 403);
        // If user only has viewOwn and tries to access team scope, block them
        if (request('scope') === 'team' && !auth()->user()->isPartner() && !auth()->user()->canAccess('task_viewAny') && !auth()->user()->canAccess('task_viewBranch') && !auth()->user()->canAccess('task_viewteam')) {
            abort(403);
        }
        $this->loadData();
    }

    public function loadData()
    {
        $query = EmployeeTask::with(['employee', 'assigner']);

        if (request('scope') === 'me' && !auth()->user()->isPartner()) {
            $query->where('employee_id', Auth::id());
        } else {
            $query = $this->applyHrmsFilters($query, 'employee_id', 'task_viewAny');
        }

        $query->whereHas('employee', function ($employeeQuery) {
            $employeeQuery->where(function ($q) {
                $q->whereNull('resignation_date')
                    ->orWhereColumn('users.resignation_date', '>', 'employee_tasks.created_at');
            })->where(function ($q) {
                $q->whereNull('termination_date')
                    ->orWhereColumn('users.termination_date', '>', 'employee_tasks.created_at');
            });
        });

        $this->tasks = $query->orderBy('created_at', 'desc')->get();

        $employeeIds = (request('scope') === 'me' && !auth()->user()->isPartner())
            ? [Auth::id()]
            : $this->getTeamEmployeeIds('task_viewAny');
        $this->employees = User::whereIn('id', $employeeIds)
            ->availableForHrmsAssignment()
            ->get();
        $this->taskStatuses = TaskStatus::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->where('status', true)->orderBy('order')->get();
    }

    public function createTask()
    {
        abort_unless($this->canCreateTask(), 403);
        $this->reset(['editingId', 'employee_id', 'title', 'description', 'start_date', 'end_date']);
        if (request('scope') === 'me' && !auth()->user()->isPartner()) {
            $this->employee_id = Auth::id();
        }
        $this->status = $this->taskStatuses->first()->slug ?? 'pending';
        $this->isModalOpen = true;
    }

    public function editTask($id)
    {
        abort_unless(auth()->user()->canAccess('task_update'), 403);
        $task = EmployeeTask::findOrFail($id);
        $this->editingId = $task->id;
        $this->employee_id = $task->employee_id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->status = $task->status;
        $this->start_date = $task->start_date ? Carbon::parse($task->start_date)->format('Y-m-d') : null;
        $this->end_date = $task->end_date ? Carbon::parse($task->end_date)->format('Y-m-d') : null;
        
        $this->isModalOpen = true;
    }

    public function saveTask()
    {
        abort_unless($this->editingId ? auth()->user()->canAccess('task_update') : $this->canCreateTask(), 403);

        if (request('scope') === 'me' && !auth()->user()->isPartner()) {
            $this->employee_id = Auth::id();
        }

        $this->validate([
            'employee_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        abort_unless(User::whereKey($this->employee_id)->availableForHrmsAssignment()->exists(), 403);

        EmployeeTask::updateOrCreate(
            ['id' => $this->editingId],
            [
                'employee_id' => $this->employee_id,
                'assigned_by' => $this->editingId ? EmployeeTask::find($this->editingId)->assigned_by : Auth::id(),
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->status,
                'start_date' => $this->start_date,
                'due_date' => $this->end_date ?: $this->start_date,
                'end_date' => $this->end_date,
            ]
        );

        $this->isModalOpen = false;
        session()->flash('success', 'Task saved successfully.');
        $this->loadData();
    }

    private function canCreateTask(): bool
    {
        $user = auth()->user();

        if ($user->isPartner()) {
            return true;
        }

        return $user->canAccess('task_create')
            || (request('scope') === 'me' && $user->canAccess('task_viewOwn'));
    }

    public function openStatusUpdate($id)
    {
        $task = EmployeeTask::findOrFail($id);
        abort_unless($task->employee_id === Auth::id(), 403);

        $this->statusUpdateId = $task->id;
        $this->updateStatus = $task->status;
        $this->remark = $task->remark ?? '';
        $this->isStatusModalOpen = true;
    }

    public function saveStatusUpdate()
    {
        $task = EmployeeTask::findOrFail($this->statusUpdateId);
        abort_unless($task->employee_id === Auth::id(), 403);

        $this->validate([
            'updateStatus' => 'required|string|max:100',
            'remark' => 'nullable|string',
        ]);

        $task->update([
            'status' => $this->updateStatus,
            'remark' => $this->remark,
        ]);

        $this->isStatusModalOpen = false;
        $this->reset(['statusUpdateId', 'updateStatus', 'remark']);
        session()->flash('success', 'Task status updated successfully.');
        $this->loadData();
    }

    public function openAddRemark($id)
    {
        $this->remarkTaskId = $id;
        $this->remarkText = '';
        $this->isRemarkModalOpen = true;
    }

    public function saveRemark()
    {
        $this->validate([
            'remarkText' => 'required|string',
        ]);

        TaskRemark::create([
            'task_id' => $this->remarkTaskId,
            'user_id' => Auth::id(),
            'remark' => $this->remarkText,
        ]);

        $this->isRemarkModalOpen = false;
        $this->reset(['remarkTaskId', 'remarkText']);
        session()->flash('success', 'Remark added successfully.');
    }

    public function openViewRemarks($id)
    {
        $task = EmployeeTask::with(['remarks' => fn ($q) => $q->with('user')->latest()])->findOrFail($id);
        $this->viewRemarkTaskTitle = $task->title;
        $this->viewRemarks = $task->remarks;
        $this->isViewRemarksOpen = true;
    }

    public function render()
    {
        return view('livewire.partner.hrms.tasks')
            ->layout('layouts.app', [
                'panelName'    => 'HRMS Module',
                'pageTitle'    => 'Employee Tasks',
                'pageSubtitle' => 'Manage tasks and assignments',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
