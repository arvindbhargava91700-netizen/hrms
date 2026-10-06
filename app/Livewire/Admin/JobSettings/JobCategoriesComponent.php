<?php

namespace App\Livewire\Admin\JobSettings;

use App\Models\JobCategory;
use Livewire\Component;
use Livewire\WithPagination;

class JobCategoriesComponent extends Component
{
    use WithPagination;

    public $name, $categoryId;
    public $isEditMode = false;
    public $showModal = false;

    public function create()
    {
        $this->resetInputFields();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:job_categories,name',
        ]);

        JobCategory::create([
            'name' => $this->name,
            'is_active' => true,
        ]);

        session()->flash('message', 'Category Created Successfully.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $category = JobCategory::findOrFail($id);
        $this->categoryId = $id;
        $this->name = $category->name;
        
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:job_categories,name,' . $this->categoryId,
        ]);

        $category = JobCategory::find($this->categoryId);
        $category->update([
            'name' => $this->name,
        ]);

        session()->flash('message', 'Category Updated Successfully.');
        $this->closeModal();
    }

    public function delete($id)
    {
        JobCategory::find($id)->delete();
        session()->flash('message', 'Category Deleted Successfully.');
    }

    public function toggleActive($id)
    {
        $category = JobCategory::find($id);
        $category->is_active = !$category->is_active;
        $category->save();
        session()->flash('message', 'Category status updated.');
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->categoryId = null;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInputFields();
    }

    public function render()
    {
        return view('livewire.admin.job-settings.job-categories-component', [
            'categories' => JobCategory::latest()->paginate(10)
        ])->layout('layouts.app', [
            'panelName' => 'Admin Panel',
            'pageTitle' => 'Job Categories',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
