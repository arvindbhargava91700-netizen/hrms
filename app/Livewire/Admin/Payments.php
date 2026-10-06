<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class Payments extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public ?string $viewingPaymentId = null;
    #[Url] public ?string $customer = null;

    protected string $paginationTheme = 'bootstrap';
    
    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }

    public function viewPayment(string $id): void
    {
        $this->viewingPaymentId = $id;
    }

    public function closeReceipt(): void
    {
        $this->viewingPaymentId = null;
    }

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'payments',
            'search' => $this->search,
            'status' => $this->statusFilter,
        ]);
    }

    public function render()
    {
        $payments = Payment::with(['subscription.customer', 'invoice'])
            ->when($this->search, fn($q) => $q->whereHas('subscription.customer', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                                             ->orWhere('gateway_ref', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->customer, fn($q) => $q->whereHas('subscription', fn($q2) => $q2->where('customer_id', $this->customer)))
            ->latest()
            ->paginate(15);

        $stats = [
            'total_volume' => Payment::where('status', 'paid')->sum('amount'),
            'paid_count' => Payment::where('status', 'paid')->count(),
            'pending_count' => Payment::where('status', 'pending')->count(),
            'failed_count' => Payment::where('status', 'failed')->count(),
        ];

        $viewingPayment = $this->viewingPaymentId
            ? Payment::with(['subscription.customer', 'subscription.package.listing', 'invoice', 'booking.customer', 'booking.package.listing'])
                ->find($this->viewingPaymentId)
            : null;

        return view('livewire.admin.payments', compact('payments', 'stats', 'viewingPayment'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Payments',
                'pageSubtitle' => 'View all platform transactions',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
