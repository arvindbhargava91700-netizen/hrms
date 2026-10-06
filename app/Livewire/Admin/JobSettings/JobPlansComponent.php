<?php

namespace App\Livewire\Admin\JobSettings;

use App\Models\JobPlan;
use Livewire\Component;
use Livewire\WithPagination;

class JobPlansComponent extends Component
{
    use WithPagination;

    public $name, $price, $validity_days, $job_limit, $is_featured;
    public $planId;
    public $isEditMode = false;
    public $showModal = false;
    
    public $viewPlanData = null;
    public $showViewModal = false;

    public $features = []; // Array of feature strings

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'validity_days' => 'required|integer|min:1',
        'job_limit' => 'nullable|integer|min:1',
        'is_featured' => 'boolean',
        'features.*' => 'nullable|string',
    ];

    public function addFeature()
    {
        $this->features[] = '';
    }

    public function removeFeature($index)
    {
        unset($this->features[$index]);
        $this->features = array_values($this->features);
    }

    public function viewPlan($id)
    {
        $this->viewPlanData = JobPlan::findOrFail($id);
        $this->showViewModal = true;
        $this->showModal = false;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewPlanData = null;
    }

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
        $this->showViewModal = false;
    }

    public function store()
    {
        $this->validate();

        JobPlan::create([
            'name' => $this->name,
            'price' => $this->price,
            'validity_days' => $this->validity_days,
            'job_limit' => $this->job_limit ?: null,
            'is_featured' => $this->is_featured ? true : false,
            'features' => array_values(array_filter($this->features)),
            'is_active' => true,
        ]);

        session()->flash('message', 'Job Plan Created Successfully.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $plan = JobPlan::findOrFail($id);
        $this->planId = $id;
        $this->name = $plan->name;
        $this->price = $plan->price;
        $this->validity_days = $plan->validity_days;
        $this->job_limit = $plan->job_limit;
        $this->is_featured = $plan->is_featured;
        $this->features = $plan->features ?? [];
        
        $this->isEditMode = true;
        $this->showModal = true;
        $this->showViewModal = false;
    }

    public function update()
    {
        $this->validate();

        $plan = JobPlan::find($this->planId);
        $plan->update([
            'name' => $this->name,
            'price' => $this->price,
            'validity_days' => $this->validity_days,
            'job_limit' => $this->job_limit ?: null,
            'is_featured' => $this->is_featured ? true : false,
            'features' => array_values(array_filter($this->features)),
        ]);

        session()->flash('message', 'Job Plan Updated Successfully.');
        $this->closeModal();
    }

    public function delete($id)
    {
        JobPlan::find($id)->delete();
        session()->flash('message', 'Job Plan Deleted Successfully.');
    }

    public function toggleActive($id)
    {
        $plan = JobPlan::find($id);
        $plan->is_active = !$plan->is_active;
        $plan->save();
        session()->flash('message', 'Plan status updated.');
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->price = '';
        $this->validity_days = '';
        $this->job_limit = '';
        $this->is_featured = false;
        $this->features = [];
        $this->planId = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInputFields();
    }

    public function render()
    {
        return view('livewire.admin.job-settings.job-plans-component', [
            'plans' => JobPlan::latest()->paginate(10)
        ])->layout('layouts.app', [
            'panelName' => 'Admin Panel',
            'pageTitle' => 'Job Plans',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
