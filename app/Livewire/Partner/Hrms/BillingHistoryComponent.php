<?php

namespace App\Livewire\Partner\Hrms;

use App\Models\TransactionHistory;
use Livewire\Component;
use Livewire\WithPagination;

class BillingHistoryComponent extends Component
{
    use WithPagination, \App\Livewire\Partner\Hrms\HasPartnerId;
    
    protected $paginationTheme = 'bootstrap';
    public $branch_id = '';
    public $statusFilter = 'All';

    public function mount() {
        abort_unless(
            auth()->user()->isPartner() ||
            auth()->user()->canAccess('jobpost_viewAny') ||
            auth()->user()->canAccess('jobpost_viewOwn') ||
            auth()->user()->canAccess('jobpost_viewTeam') ||
            auth()->user()->canAccess('jobpost_viewBranch'),
            403
        );
    }

    public function updatingBranchId()
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
        $query = TransactionHistory::where('user_id', $this->getPartnerId())
            ->where('type', 'job_post');

        if ($this->branch_id) {
            $query->whereHas('jobPost', function ($q) {
                $q->where('branch_id', $this->branch_id);
            });
        }
        
        if ($this->statusFilter !== 'All') {
            $status = strtolower($this->statusFilter);
            if ($status === 'success') {
                $status = 'completed';
            }
            $query->where('status', $status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);
        
        $branches = \App\Models\HrmsBranch::where(function ($q) { if (!auth()->user()->isSuperAdmin()) { $q->where('partner_id', $this->getPartnerId()); } })->orderBy('name')->get();

        return view('livewire.partner.hrms.billing-history-component', compact('transactions', 'branches'))
            ->layout('layouts.app', [
                'panelName' => 'HRMS Module',
                'pageTitle' => 'Billing History',
                'pageSubtitle' => 'View your job posting billing and usage history',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}

