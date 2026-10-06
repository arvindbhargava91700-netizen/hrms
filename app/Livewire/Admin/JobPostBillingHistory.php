<?php

namespace App\Livewire\Admin;

use App\Models\TransactionHistory;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class JobPostBillingHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    
    public $partnerFilter = '';
    public $statusFilter = 'All';

    public function mount()
    {
        abort_unless(auth()->user()->can('admin_finance_earnings'), 403);
    }

    public function updatingPartnerFilter()
    {
        $this->resetPage();
    }

    public function setStatusFilter($status)
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function render()
    {
        $query = TransactionHistory::with('user')->where('type', 'job_post');

        if ($this->partnerFilter) {
            $query->where('user_id', $this->partnerFilter);
        }

        if ($this->statusFilter !== 'All') {
            $status = strtolower($this->statusFilter);
            if ($status === 'success') {
                $status = 'completed';
            }
            $query->where('status', $status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);
        $partners = User::where('role', 'partner')->orderBy('name')->get();

        return view('livewire.admin.job-post-billing-history', compact('transactions', 'partners'))
            ->layout('layouts.app', [
                'panelName' => 'Admin Panel',
                'pageTitle' => 'Job Post Billing History',
                'pageSubtitle' => 'View all job posting billing and usage history',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
