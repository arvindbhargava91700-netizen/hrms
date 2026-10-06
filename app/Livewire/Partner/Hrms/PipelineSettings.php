<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\PipelineStage;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class PipelineSettings extends Component
{
    public $stages = [];
    public $departments = [];

    // Form inputs
    public $isEditMode = false;
    public $editStageId = null;
    public $name = '';
    public $department_id = '';
    public $assigned_to = '';
    public $counts_towards_target = false;

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $this->loadData();
    }

    public function loadData()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $this->stages = PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->with(['department', 'assignedUser'])
            ->orderBy('order_index')
            ->get();
            
        $this->departments = Department::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->get();
    }

    public function saveStage()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $this->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required',
            'assigned_to' => 'nullable|exists:users,id',
            'counts_towards_target' => 'boolean',
        ]);

        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        if ($this->counts_towards_target) {
            // Unset counts_towards_target from all other stages for this partner (only 1 allowed)
            PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
                ->where('id', '!=', $this->editStageId)
                ->update(['counts_towards_target' => false]);
        }

        $assignedTo = !empty($this->assigned_to) ? $this->assigned_to : null;

        if ($this->isEditMode && $this->editStageId) {
            $stage = PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->find($this->editStageId);
            if ($stage) {
                $stage->update([
                    'name' => $this->name,
                    'department_id' => $this->department_id,
                    'assigned_to' => $assignedTo,
                    'counts_towards_target' => $this->counts_towards_target,
                ]);
                session()->flash('success', 'Pipeline stage updated successfully!');
            }
        } else {
            $maxOrder = PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->max('order_index');
            
            PipelineStage::create([
                'partner_id' => tap($partnerId, fn ($id) => abort_unless($id, 403, 'Select a partner before creating partner-owned HRMS data.')),
                'name' => $this->name,
                'department_id' => $this->department_id,
                'assigned_to' => $assignedTo,
                'counts_towards_target' => $this->counts_towards_target,
                'order_index' => $maxOrder !== null ? $maxOrder + 1 : 1,
            ]);
            session()->flash('success', 'Pipeline stage added successfully!');
        }

        $this->resetForm();
        $this->loadData();
    }

    public function editStage($id)
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $stage = PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->find($id);
        
        if ($stage) {
            $this->isEditMode = true;
            $this->editStageId = $stage->id;
            $this->name = $stage->name;
            $this->department_id = $stage->department_id;
            $this->assigned_to = $stage->assigned_to ?? '';
            $this->counts_towards_target = (bool) $stage->counts_towards_target;
        }
    }

    public function deleteStage($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);
        
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $stage = PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->find($id);
        
        if ($stage) {
            $stage->delete();
            $this->reorderStagesAfterDelete();
            session()->flash('success', 'Pipeline stage deleted!');
            $this->loadData();
        }
    }

    public function moveUp($id)
    {
        $this->swapOrder($id, 'up');
    }

    public function moveDown($id)
    {
        $this->swapOrder($id, 'down');
    }

    private function swapOrder($id, $direction)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        
        $stage = PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->find($id);
        if (!$stage) return;

        $targetOrder = $direction === 'up' ? $stage->order_index - 1 : $stage->order_index + 1;
        
        $swapWith = PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->where('order_index', $targetOrder)
            ->first();

        if ($swapWith) {
            DB::transaction(function () use ($stage, $swapWith, $targetOrder) {
                $originalOrder = $stage->order_index;
                $stage->update(['order_index' => $targetOrder]);
                $swapWith->update(['order_index' => $originalOrder]);
            });
            $this->loadData();
        }
    }

    private function reorderStagesAfterDelete()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $stages = PipelineStage::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->orderBy('order_index')->get();
        
        $index = 1;
        foreach ($stages as $stage) {
            $stage->update(['order_index' => $index]);
            $index++;
        }
    }

    public function resetForm()
    {
        $this->isEditMode = false;
        $this->editStageId = null;
        $this->name = '';
        $this->department_id = '';
        $this->assigned_to = '';
        $this->counts_towards_target = false;
    }

    public function render()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $staffQuery = \App\Models\User::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('parent_id', $partnerId); } })
            ->where('role', 'employee')
            ->with(['department', 'designation']);

        if (!empty($this->department_id)) {
            $staffQuery->where('department_id', $this->department_id);
        }

        $availableStaff = $staffQuery->orderBy('name')->get();

        return view('livewire.partner.hrms.pipeline-settings', [
            'availableStaff' => $availableStaff,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Pipeline Configuration',
            'pageSubtitle' => 'Manage stages for the order pipeline',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}

