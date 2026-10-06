<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TransactionHistory as TransactionHistoryModel;
use App\Models\User;

class TransactionHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $type = '';
    public $status = '';
    public $search = '';
    public $selectedTransaction = null;
    public $startDate = '';
    public $endDate = '';
    public $showModal = false;

    public function viewDetails($id)
    {
        $this->selectedTransaction = TransactionHistoryModel::with('user')->find($id);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTransaction = null;
    }

    public function updatedType() { $this->resetPage(); }
    public function updatedStatus() { $this->resetPage(); }
    public function updatedSearch() { $this->resetPage(); }
    public function updatedStartDate() { $this->resetPage(); }
    public function updatedEndDate() { $this->resetPage(); }

    public function render()
    {
        $query = TransactionHistoryModel::with('user');

        if ($this->type !== '') {
            $query->where('type', $this->type);
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->startDate !== '') {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate !== '') {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        if ($this->search !== '') {
            $query->where(function($q) {
                $q->whereHas('user', function ($q2) {
                    $q2->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('mobile', 'like', '%' . $this->search . '%');
                })->orWhere('transaction_id', 'like', '%' . $this->search . '%');
            });
        }

        $totalAmount = (clone $query)->sum('total_amount');
        $platformFee = (clone $query)->sum('platform_fee');
        
        // Security amount from bookings linked to these transactions
        $bookingIds = (clone $query)->where('type', 'booking')->pluck('reference_id');
        $securityAmount = \App\Models\Booking::whereIn('id', $bookingIds)->sum('security_deposit');

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('livewire.admin.transaction-history', [
            'transactions' => $transactions,
            'totalAmount' => $totalAmount,
            'platformFee' => $platformFee,
            'securityAmount' => $securityAmount,
        ])->layout('layouts.app', [
            'panelName' => 'Admin Panel',
            'pageTitle' => 'Global Transaction History',
            'pageSubtitle' => 'View all financial transactions across the platform',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
