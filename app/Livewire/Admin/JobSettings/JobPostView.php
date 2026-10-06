<?php

namespace App\Livewire\Admin\JobSettings;

use App\Models\JobPost;
use Livewire\Component;

class JobPostView extends Component
{
    public $jobPost;

    public function mount($id)
    {
        abort_unless(auth()->user()->can('admin_system_modules'), 403);
        
        $this->jobPost = JobPost::with(['department', 'creator', 'partner', 'plan', 'branch'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.admin.job-settings.job-post-view')
            ->layout('layouts.app', [
                'panelName' => 'Admin Panel',
                'pageTitle' => 'Job Post Details',
                'pageSubtitle' => 'View complete details of the job posting',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
