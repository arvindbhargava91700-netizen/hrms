<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\LeaveCategory;
use Illuminate\Support\Facades\Auth;

class LeaveCategories extends Component
{
    use HasPartnerId;

    public $categories = [];
    public $isModalOpen = false;
    public $editingCategoryId = null;

    public $name;
    public $days = 0;
    public $is_unlimited = false;
    public $status = true;

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('leavecategory_viewany') || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $this->loadCategories();
    }

    public function loadCategories()
    {
        $this->categories = LeaveCategory::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->orderBy('name')->get();
    }

    public function createCategory()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('leavecategory_create') || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $this->reset(['editingCategoryId', 'name', 'days', 'status', 'is_unlimited']);
        $this->status = true;
        $this->is_unlimited = false;
        $this->isModalOpen = true;
    }

    public function editCategory($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('leavecategory_update') || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $cat = LeaveCategory::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        $this->editingCategoryId = $cat->id;
        $this->name = $cat->name;
        $this->days = $cat->days;
        $this->is_unlimited = (bool) ($cat->is_unlimited ?? false);
        $this->status = (bool) $cat->status;
        $this->isModalOpen = true;
    }

    public function updatedIsUnlimited($value)
    {
        if ($value) {
            $this->days = 0;
        }
    }

    public function saveCategory()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess($this->editingCategoryId ? 'leavecategory_update' : 'leavecategory_create') || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $this->validate([
            'name' => 'required|string|max:255',
            'days' => $this->is_unlimited ? 'nullable|integer|min:0' : 'required|integer|min:0',
            'is_unlimited' => 'boolean',
            'status' => 'boolean',
        ]);

        $match = ['id' => $this->editingCategoryId];
        if (!auth()->user()->isSuperAdmin()) {
            $match['partner_id'] = $this->getPartnerId();
        } elseif (!$this->editingCategoryId) {
            $match['partner_id'] = $this->requirePartnerId();
        }

        LeaveCategory::updateOrCreate(
            $match,
            [
                'name' => $this->name,
                'days' => $this->is_unlimited ? 0 : (int)$this->days,
                'is_unlimited' => (bool)$this->is_unlimited,
                'status' => $this->status,
            ]
        );

        $this->isModalOpen = false;
        session()->flash('success', 'Leave category saved successfully.');
        $this->loadCategories();
    }

    public function deleteCategory($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('leavecategory_delete') || auth()->user()->canAccess('hrmssetting_manage'), 403);
        LeaveCategory::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id)->delete();
        session()->flash('success', 'Leave category deleted successfully.');
        $this->loadCategories();
    }

    public function render()
    {
        return view('livewire.partner.hrms.leave-categories')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Leave Categories',
                'pageSubtitle' => 'Manage leave types and default yearly balances',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
