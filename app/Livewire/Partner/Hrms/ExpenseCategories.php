<?php

namespace App\Livewire\Partner\Hrms;

use Livewire\Component;
use App\Models\ExpenseCategory;

class ExpenseCategories extends Component
{
    public $categories = [];

    // Form inputs
    public $isEditMode = false;
    public $editCategoryId = null;
    public $name = '';
    public $type = 'per_unit'; // per_unit, max_limit, actual
    public $unit_name = 'KM';
    public $rate_per_unit = 6.00;
    public $max_limit_amount = '';
    public $status = 'active';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $this->loadData();
    }

    public function loadData()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $this->categories = ExpenseCategory::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })
            ->latest()
            ->get();
    }

    public function saveCategory()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);

        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:per_unit,max_limit,actual',
            'status' => 'required|in:active,inactive',
        ];

        if ($this->type === 'per_unit') {
            $rules['unit_name'] = 'required|string|max:50';
            $rules['rate_per_unit'] = 'required|numeric|min:0.01';
        } elseif ($this->type === 'max_limit') {
            $rules['max_limit_amount'] = 'required|numeric|min:0.01';
        }

        $this->validate($rules);

        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $data = [
            'partner_id' => $partnerId,
            'name' => $this->name,
            'type' => $this->type,
            'unit_name' => $this->type === 'per_unit' ? $this->unit_name : null,
            'rate_per_unit' => $this->type === 'per_unit' ? (float)$this->rate_per_unit : 0,
            'max_limit_amount' => $this->type === 'max_limit' ? (float)$this->max_limit_amount : null,
            'status' => $this->status,
        ];

        if ($this->isEditMode && $this->editCategoryId) {
            $cat = ExpenseCategory::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->find($this->editCategoryId);
            if ($cat) {
                $cat->update($data);
                session()->flash('success', 'Expense category updated successfully!');
            }
        } else {
            ExpenseCategory::create($data);
            session()->flash('success', 'Expense category created successfully!');
        }

        $this->resetForm();
        $this->loadData();
    }

    public function editCategory($id)
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $cat = ExpenseCategory::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->find($id);

        if ($cat) {
            $this->isEditMode = true;
            $this->editCategoryId = $cat->id;
            $this->name = $cat->name;
            $this->type = $cat->type;
            $this->unit_name = $cat->unit_name ?? 'KM';
            $this->rate_per_unit = $cat->rate_per_unit;
            $this->max_limit_amount = $cat->max_limit_amount;
            $this->status = $cat->status;
        }
    }

    public function deleteCategory($id)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'), 403);
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $cat = ExpenseCategory::where(function ($q) use ($partnerId) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $partnerId); } })->find($id);

        if ($cat) {
            $cat->delete();
            session()->flash('success', 'Expense category deleted!');
            $this->loadData();
        }
    }

    public function resetForm()
    {
        $this->isEditMode = false;
        $this->editCategoryId = null;
        $this->name = '';
        $this->type = 'per_unit';
        $this->unit_name = 'KM';
        $this->rate_per_unit = 6.00;
        $this->max_limit_amount = '';
        $this->status = 'active';
    }

    public function render()
    {
        return view('livewire.partner.hrms.expense-categories');
    }
}
