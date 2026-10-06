<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;

class Products extends Component
{
    use HasPartnerId;

    public $products = [];
    public $categories = [];
    public $isModalOpen = false;
    public $editingId = null;
    public $name, $category_id, $amount, $status = 'active';
    public $gst_type = 'notinclude';
    public $gst_percent = 0;

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('lead_viewAny'), 403);
        $this->loadData();
    }

    public function loadData()
    {
        $this->categories = ProductCategory::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->where('status', 'active')->get();
        $this->products = Product::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })
            ->with('category')
            ->orderBy('name')
            ->get();
    }

    public function updatedGstType($value)
    {
        if ($value === 'notinclude') {
            $this->gst_percent = 0;
        } elseif ($value === 'include' && (float)$this->gst_percent <= 0) {
            $this->gst_percent = 18;
        }
    }

    public function createProduct()
    {
        $this->reset(['editingId', 'name', 'category_id', 'amount']);
        $this->gst_type = 'notinclude';
        $this->gst_percent = 0;
        $this->status = 'active';
        $this->isModalOpen = true;
    }

    public function editProduct($id)
    {
        $product = Product::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->amount = $product->amount;
        $this->gst_type = $product->gst_type ?: 'notinclude';
        $this->gst_percent = $product->gst_percent ?: 0;
        $this->status = $product->status;
        $this->isModalOpen = true;
    }

    public function saveProduct()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:product_categories,id',
            'amount' => 'required|numeric|min:0',
            'gst_type' => 'required|in:include,notinclude',
            'status' => 'required|in:active,inactive',
        ];

        if ($this->gst_type === 'include') {
            $rules['gst_percent'] = 'required|numeric|min:0.01|max:100';
        } else {
            $rules['gst_percent'] = 'nullable|numeric|min:0';
        }

        $this->validate($rules, [
            'gst_percent.required' => 'Please specify the GST percentage when GST is included.',
            'gst_percent.min' => 'GST percentage must be greater than 0%.',
        ]);

        Product::updateOrCreate(
            ['id' => $this->editingId],
            [
                'partner_id' => $this->requirePartnerId(),
                'category_id' => $this->category_id,
                'name' => $this->name,
                'amount' => $this->amount,
                'gst_type' => $this->gst_type,
                'gst_percent' => $this->gst_type === 'include' ? (float)$this->gst_percent : 0,
                'status' => $this->status,
            ]
        );

        $this->isModalOpen = false;
        session()->flash('success', 'Product saved successfully.');
        $this->loadData();
    }

    public function deleteProduct($id)
    {
        $product = Product::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->findOrFail($id);
        $product->delete();
        session()->flash('success', 'Product deleted successfully.');
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.partner.hrms.products')
            ->layout('layouts.app', [
                'panelName'    => 'HRMS Module',
                'pageTitle'    => 'Products',
                'pageSubtitle' => 'Manage Products',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
