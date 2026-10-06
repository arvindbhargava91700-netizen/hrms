<?php

namespace App\Livewire\Admin\Reports;

use App\Models\WalletTransaction;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionLedgerReport extends Component
{
    use WithPagination;

    public string $search = '';
    public string $startDate = '';
    public string $endDate = '';
    public string $typeFilter = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingTypeFilter() { $this->resetPage(); }

    public function export()
    {
        $query = WalletTransaction::with('user');
        $this->applyFilters($query);

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Txn ID', 'User', 'Type', 'Amount', 'Description', 'Date']);

            $query->chunk(200, function ($transactions) use ($output) {
                foreach ($transactions as $txn) {
                    fputcsv($output, [
                        $txn->id,
                        $txn->user->name ?? 'System',
                        $txn->type,
                        $txn->amount,
                        $txn->description,
                        $txn->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($output);
        }, 'transaction-ledger-' . now()->format('Y-m-d_His') . '.csv');
    }

    private function applyFilters($query)
    {
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('user', function($q2) {
                      $q2->where('name', 'like', '%' . $this->search . '%')
                         ->orWhere('email', 'like', '%' . $this->search . '%');
                  });
            });
        }
        if (!empty($this->typeFilter)) {
            $query->where('type', $this->typeFilter);
        }
        if (!empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if (!empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
    }

    public function render()
    {
        $query = WalletTransaction::with('user');
        $this->applyFilters($query);

        $referenceIds = (clone $query)->pluck('reference_id')->filter()->unique();
        $totalAmount = \App\Models\TransactionHistory::whereIn('reference_id', $referenceIds)->sum('total_amount');
        $platformFee = \App\Models\TransactionHistory::whereIn('reference_id', $referenceIds)->sum('platform_fee');
        
        $bookingIds = (clone $query)->where('reference_type', \App\Models\Booking::class)->pluck('reference_id')->filter()->unique();
        $securityAmount = \App\Models\Booking::whereIn('id', $bookingIds)->sum('security_deposit');

        $records = $query->latest()->paginate(15);

        return view('livewire.admin.reports.transaction-ledger-report', compact('records', 'totalAmount', 'platformFee', 'securityAmount'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Transaction Ledger',
                'pageSubtitle' => 'View Transaction Ledger',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
