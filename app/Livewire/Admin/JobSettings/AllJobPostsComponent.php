<?php

namespace App\Livewire\Admin\JobSettings;

use App\Models\JobPost;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;

class AllJobPostsComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $statusFilter = '';
    public $partnerFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPartnerFilter()
    {
        $this->resetPage();
    }

    public function mount()
    {
        abort_unless(auth()->user()->can('admin_system_modules'), 403);
    }

    public function render()
    {
        $query = JobPost::query()->with(['department', 'creator', 'partner', 'plan']);

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('job_title', 'like', "%{$search}%")
                  ->orWhere('job_code', 'like', "%{$search}%");
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->partnerFilter) {
            $query->where('partner_id', $this->partnerFilter);
        }

        $jobPosts = $query->orderBy('created_at', 'desc')->paginate(10);
        $partners = User::where('role', 'partner')->orderBy('name')->get();

        return view('livewire.admin.job-settings.all-job-posts-component', [
            'jobPosts' => $jobPosts,
            'partners' => $partners,
        ])->layout('layouts.app', [
            'panelName' => 'Admin Panel',
            'pageTitle' => 'All Job Posts',
            'pageSubtitle' => 'View all job posts across partners',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
