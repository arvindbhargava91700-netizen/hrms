<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransactionHistory as TransactionHistoryModel;

class TransactionHistory extends Component
{
    use WithPagination;
    use HasPartnerWorkspaceScope;

    protected $paginationTheme = 'bootstrap';

    public $type = '';
    public $status = '';
    public $selectedTransaction = null;
    public $showModal = false;

    public function viewDetails($id)
    {
        $this->selectedTransaction = $this->scopePartnerRecords(
            TransactionHistoryModel::query(),
            'user_id'
        )->find($id);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTransaction = null;
    }

    public function updatedType()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->scopePartnerRecords(TransactionHistoryModel::query(), 'user_id');

        if ($this->type !== '') {
            $query->where('type', $this->type);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $totalAmount = (clone $query)->sum('total_amount');
        $platformFee = (clone $query)->sum('platform_fee');
        
        $bookingIds = (clone $query)->where('type', 'booking')->pluck('reference_id');
        $securityAmount = \App\Models\Booking::whereIn('id', $bookingIds)->sum('security_deposit');

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.partner.transaction-history', [
            'transactions' => $transactions,
            'totalAmount' => $totalAmount,
            'platformFee' => $platformFee,
            'securityAmount' => $securityAmount,
        ])->layout('layouts.app', [
            'panelName' => 'Partner Panel',
            'pageTitle' => 'Transaction History',
            'pageSubtitle' => 'View all your financial transactions',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
