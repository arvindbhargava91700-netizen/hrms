<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\TaskStatus;
use Illuminate\Support\Str;

class TaskStatuses extends Component
{
    use HasPartnerId;

    public $taskStatuses = [];
    public $isModalOpen = false;
    public $editingStatusId = null;

    public $name = '';
    public $color = 'primary';
    public $order = 0;
    public $is_completed = false;
    public $status = true;

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('task_viewAny') || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $this->ensureDefaultStatuses();
        $this->loadTaskStatuses();
    }

    public function ensureDefaultStatuses()
    {
        $partnerId = $this->getPartnerId();
        $count = TaskStatus::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->count();

        if ($count === 0) {
            $defaults = [
                ['name' => 'Pending', 'slug' => 'pending', 'color' => 'warning', 'order' => 1, 'is_completed' => false],
                ['name' => 'In Progress', 'slug' => 'in_progress', 'color' => 'primary', 'order' => 2, 'is_completed' => false],
                ['name' => 'Under Review', 'slug' => 'under_review', 'color' => 'info', 'order' => 3, 'is_completed' => false],
                ['name' => 'Completed', 'slug' => 'completed', 'color' => 'success', 'order' => 4, 'is_completed' => true],
                ['name' => 'Cancelled', 'slug' => 'cancelled', 'color' => 'danger', 'order' => 5, 'is_completed' => false],
            ];

            foreach ($defaults as $d) {
                TaskStatus::create([
                    'partner_id'   => $this->requirePartnerId(),
                    'name'         => $d['name'],
                    'slug'         => $d['slug'],
                    'color'        => $d['color'],
                    'order'        => $d['order'],
                    'is_completed' => $d['is_completed'],
                    'status'       => true,
                ]);
            }
        }
    }

    public function loadTaskStatuses()
    {
        $this->taskStatuses = TaskStatus::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })
            ->orderBy('order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function createStatus()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $this->reset(['editingStatusId', 'name', 'color', 'is_completed']);
        $this->color = 'primary';
        $this->status = true;
        $this->is_completed = false;
        $this->order = count($this->taskStatuses) + 1;
        $this->isModalOpen = true;
    }

    public function editStatus($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $item = TaskStatus::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        $this->editingStatusId = $item->id;
        $this->name = $item->name;
        $this->color = $item->color ?? 'primary';
        $this->order = (int) $item->order;
        $this->is_completed = (bool) $item->is_completed;
        $this->status = (bool) $item->status;
        $this->isModalOpen = true;
    }

    public function saveStatus()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $this->validate([
            'name'         => 'required|string|max:255',
            'color'        => 'required|string|max:50',
            'order'        => 'required|integer|min:0',
            'is_completed' => 'boolean',
            'status'       => 'boolean',
        ]);

        $isCompleted = $this->is_completed || Str::contains(strtolower($this->name), ['complete', 'done', 'finish', 'closed']);

        $match = ['id' => $this->editingStatusId];
        $match['partner_id'] = $this->getPartnerId();

        TaskStatus::updateOrCreate(
            $match,
            [
                'name'         => $this->name,
                'slug'         => Str::slug($this->name),
                'color'        => $this->color,
                'order'        => (int) $this->order,
                'is_completed' => (bool) $isCompleted,
                'status'       => (bool) $this->status,
            ]
        );

        $this->isModalOpen = false;
        session()->flash('success_task_status', 'Task status saved successfully.');
        $this->loadTaskStatuses();
    }

    public function toggleActive($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $item = TaskStatus::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        $item->status = !$item->status;
        $item->save();
        $this->loadTaskStatuses();
    }

    public function deleteStatus($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);
        TaskStatus::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id)->delete();
        session()->flash('success_task_status', 'Task status deleted successfully.');
        $this->loadTaskStatuses();
    }

    public function render()
    {
        return view('livewire.partner.hrms.task-statuses')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Task Statuses',
                'pageSubtitle' => 'Manage custom task workflow statuses',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
