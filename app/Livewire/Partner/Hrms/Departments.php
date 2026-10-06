<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\Department;
use App\Models\HrmsBranch;
use Illuminate\Support\Facades\Auth;
use App\Models\DepartmentBranchHead;

class Departments extends Component
{
    use HasPartnerId;
    public $departments = [];
    public $isModalOpen = false;
    public $editingDepartmentId = null;
    
    public $name, $description, $parent_id;
    public $partner_id; // For super_admin to select partner
    public $branch_heads = []; // [branch_id => user_id]
    
    public $employees = [];
    public $branches = [];
    public $partners = [];

    public function mount()
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->canAccess('department_viewAny'), 403);
        $this->loadDepartments();
        
        if (auth()->user()->isSuperAdmin()) {
            $this->partners = \App\Models\User::where('role', 'partner')->get(['id', 'name']);
        }
    }

    public function loadDepartments()
    {
        $this->departments = Department::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })
            ->with(['parent', 'branchHeads.head', 'branchHeads.branch'])
            ->withCount('employees')
            ->orderBy('sort_order')
            ->orderBy('created_at', 'desc')
            ->get();
            
        $this->employees = \App\Models\User::whereIn('id', $this->getTeamEmployeeIds())->get();
        $this->branches = HrmsBranch::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->get();
    }

    public function createDepartment()
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->canAccess('department_create'), 403);
        $this->reset(['editingDepartmentId', 'name', 'description', 'parent_id', 'branch_heads', 'partner_id']);
        $this->isModalOpen = true;
    }

    public function editDepartment($id)
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->canAccess('department_update'), 403);
        $dept = Department::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->with('branchHeads')->findOrFail($id);
        
        $this->editingDepartmentId = $dept->id;
        $this->name = $dept->name;
        $this->description = $dept->description;
        $this->parent_id = $dept->parent_id;
        
        if (auth()->user()->isSuperAdmin()) {
            $this->partner_id = $dept->partner_id;
        }
        
        $this->branch_heads = [];
        foreach ($dept->branchHeads as $bh) {
            $this->branch_heads[$bh->branch_id] = $bh->head_id;
        }
        
        $this->isModalOpen = true;
    }

    public function saveDepartment()
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->canAccess($this->editingDepartmentId ? 'department_update' : 'department_create'), 403);
        
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            // 'parent_id' => 'nullable|exists:departments,id',
            'branch_heads' => 'array',
        ];
        
        if (auth()->user()->isSuperAdmin()) {
            $rules['partner_id'] = 'nullable|exists:users,id';
        }
        
        $this->validate($rules);

        $match = ['id' => $this->editingDepartmentId];
        if (!auth()->user()->isSuperAdmin()) {
            $match['partner_id'] = $this->getPartnerId();
        }

        $dept = Department::updateOrCreate(
            $match,
            [
                'name' => $this->name,
                'description' => $this->description,
                'parent_id' => $this->parent_id ?: null,
                'partner_id' => auth()->user()->isSuperAdmin() ? $this->partner_id : $this->getPartnerId(),
            ]
        );

        // New departments appear at the top of the list
        if (!$this->editingDepartmentId) {
            $dept->update(['sort_order' => 0]);
        }

        // Sync Branch Heads
        DepartmentBranchHead::where('department_id', $dept->id)->delete();
        
        foreach ($this->branch_heads as $branch_id => $head_id) {
            if ($head_id) {
                DepartmentBranchHead::create([
                    'department_id' => $dept->id,
                    'branch_id' => $branch_id,
                    'head_id' => $head_id,
                ]);
            }
        }

        $this->isModalOpen = false;
        session()->flash('success', 'Department saved successfully.');
        $this->loadDepartments();
    }
    
    public function deleteDepartment($id)
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->canAccess('department_delete'), 403);
        Department::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id)->delete();
        $this->loadDepartments();
    }

    /**
     * Update the display order of departments after a drag-and-drop reorder.
     */
    public function updateSortOrder($orderedIds)
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->canAccess('department_update'), 403);

        $partnerId = $this->getPartnerId();
        $ids = array_filter(array_map('intval', (array) $orderedIds));

        $order = 1;
        foreach ($ids as $id) {
            Department::where('id', $id)
                ->where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
                ->update(['sort_order' => $order++]);
        }

        $this->loadDepartments();
    }

    public function render()
    {
        return view('livewire.partner.hrms.departments')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Departments',
                'pageSubtitle' => 'Manage organization departments',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
