<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;

class ProductCategories extends Component
{
    use HasPartnerId;

    public $categories = [];
    public $isModalOpen = false;
    public $editingId = null;
    public $name, $status = 'active';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('lead_viewAny'), 403);
        $this->loadData();
    }

    public function loadData()
    {
        $this->categories = ProductCategory::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })
            ->orderBy('name')
            ->get();
    }

    public function createCategory()
    {
        $this->reset(['editingId', 'name']);
        $this->status = 'active';
        $this->isModalOpen = true;
    }

    public function editCategory($id)
    {
        $category = ProductCategory::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->status = $category->status;
        $this->isModalOpen = true;
    }

    public function saveCategory()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        ProductCategory::updateOrCreate(
            ['id' => $this->editingId],
            [
                'partner_id' => $this->requirePartnerId(),
                'name' => $this->name,
                'status' => $this->status,
            ]
        );

        $this->isModalOpen = false;
        session()->flash('success', 'Product Category saved successfully.');
        $this->loadData();
    }

    public function deleteCategory($id)
    {
        $category = ProductCategory::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        $category->delete();
        session()->flash('success', 'Product Category deleted successfully.');
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.partner.hrms.product-categories')
            ->layout('layouts.app', [
                'panelName'    => 'HRMS Module',
                'pageTitle'    => 'Product Categories',
                'pageSubtitle' => 'Manage Product Categories',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
