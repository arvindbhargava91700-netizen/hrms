<?php

namespace App\Livewire\Admin;

use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;

class Invoices extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = '';
    public string $viewMode = 'subscription';
    public ?string $viewingInvoiceId = null;
    public ?string $viewingPaymentId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedViewMode(): void
    {
        $this->resetPage();
    }

    public function viewInvoice(string $id): void
    {
        $this->viewingPaymentId = null;
        $this->viewingInvoiceId = $id;
    }

    public function viewPayment(string $id): void
    {
        $this->viewingInvoiceId = null;
        $this->viewingPaymentId = $id;
    }

    public function closeReceipt(): void
    {
        $this->viewingInvoiceId = null;
        $this->viewingPaymentId = null;
    }

    public function render()
    {
        $subscriptionInvoices = Invoice::with(['subscription.customer', 'subscription.package.listing', 'payments'])
            ->when($this->search, function ($query) {
                $query->where(function ($searchQuery) {
                    $searchQuery->where('invoice_number', 'like', "%{$this->search}%")
                        ->orWhereHas('subscription.customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
                        ->orWhereHas('subscription.package.listing', fn ($listingQuery) => $listingQuery->where('title', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15, ['*'], 'subscriptionPage');

        $paymentReceipts = Payment::with(['subscription.customer', 'subscription.package.listing', 'invoice'])
            ->when($this->search, function ($query) {
                $query->where(function ($searchQuery) {
                    $searchQuery->where('gateway_ref', 'like', "%{$this->search}%")
                        ->orWhereHas('subscription.customer', fn ($customerQuery) => $customerQuery->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
                        ->orWhereHas('subscription.package.listing', fn ($listingQuery) => $listingQuery->where('title', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15, ['*'], 'paymentPage');

        $viewingInvoice = $this->viewingInvoiceId
            ? Invoice::with(['subscription.customer', 'subscription.package.listing', 'payments'])
                ->find($this->viewingInvoiceId)
            : null;

        $viewingPayment = $this->viewingPaymentId
            ? Payment::with(['subscription.customer', 'subscription.package.listing', 'invoice', 'booking.customer', 'booking.package.listing'])
                ->find($this->viewingPaymentId)
            : null;

        return view('livewire.admin.invoices', compact('subscriptionInvoices', 'paymentReceipts', 'viewingInvoice', 'viewingPayment'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Invoices',
                'pageSubtitle' => 'Manage subscription invoices and payment receipts',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
