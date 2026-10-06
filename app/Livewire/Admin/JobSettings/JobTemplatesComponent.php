<?php

namespace App\Livewire\Admin\JobSettings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\JobTemplate;

class JobTemplatesComponent extends Component
{
    use WithPagination;

    public $viewTemplate = null;
    public $isViewModalOpen = false;

    public function viewTemplateDetails($id)
    {
        $this->viewTemplate = JobTemplate::find($id);
        if ($this->viewTemplate) {
            // decode json if it's string
            if (is_string($this->viewTemplate->default_screening_questions)) {
                $this->viewTemplate->default_screening_questions = json_decode($this->viewTemplate->default_screening_questions, true) ?? [];
            }
        }
        $this->isViewModalOpen = true;
    }

    public function closeViewModal()
    {
        $this->isViewModalOpen = false;
        $this->viewTemplate = null;
    }

    public function delete($id)
    {
        JobTemplate::find($id)->delete();
        session()->flash('message', 'Job Template Deleted Successfully.');
    }

    public function toggleActive($id)
    {
        $template = JobTemplate::find($id);
        $template->is_active = !$template->is_active;
        $template->save();
        session()->flash('message', 'Template status updated.');
    }

    public function render()
    {
        return view('livewire.admin.job-settings.job-templates-component', [
            'templates' => JobTemplate::latest()->paginate(10)
        ])->layout('layouts.app', [
            'panelName' => 'Admin Panel',
            'pageTitle' => 'Job Templates',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
