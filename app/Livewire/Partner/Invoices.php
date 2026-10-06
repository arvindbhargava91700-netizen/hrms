<?php

namespace App\Livewire\Partner;

use App\Models\Invoice;
use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;

class Invoices extends Component
{
    use WithPagination, HasPartnerWorkspaceScope;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = '';
    public string $viewMode = 'subscription';
    
    public ?string $viewingInvoiceId = null;
    public ?string $viewingPaymentId = null;

    public function mount()
    {
        abort_unless(auth()->user()->canAccess('invoice_viewany') || auth()->user()->canAccess('invoice_viewown'), 403, 'Unauthorized access.');
    }

    public function updatingSearch(): void
    {
        $this->resetPage('subscriptionPage');
        $this->resetPage('paymentPage');
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage('subscriptionPage');
        $this->resetPage('paymentPage');
    }

    public function viewInvoice(string $id): void
    {
        abort_unless(auth()->user()->canAccess('invoice_viewany') || auth()->user()->canAccess('invoice_viewown'), 403, 'Unauthorized access.');
        $this->viewingInvoiceId = $id;
        $this->viewingPaymentId = null;
    }

    public function viewPayment(string $id): void
    {
        abort_unless(auth()->user()->canAccess('invoice_viewany') || auth()->user()->canAccess('invoice_viewown'), 403, 'Unauthorized access.');
        $this->viewingPaymentId = $id;
        $this->viewingInvoiceId = null;
    }

    public function closeReceipt(): void
    {
        $this->viewingInvoiceId = null;
        $this->viewingPaymentId = null;
    }

    public function render()
    {
        $invoiceQuery = Invoice::with(['subscription.customer', 'subscription.package.listing', 'payments'])
            ->whereHas('subscription.package.listing', function ($query) {
                $this->scopePartnerRecords($query);
            });
            
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('invoice_viewany')) {
            $invoiceQuery->where('created_by', auth()->id());
        }

        if ($this->search) {
            $invoiceQuery->where(function ($q) {
                $q->where('invoice_number', 'like', "%{$this->search}%")
                  ->orWhereHas('subscription.customer', function ($cq) {
                      $cq->where('name', 'like', "%{$this->search}%")
                         ->orWhere('email', 'like', "%{$this->search}%")
                         ->orWhere('mobile', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('subscription.package.listing', function ($lq) {
                      $lq->where('title', 'like', "%{$this->search}%");
                  });
            });
        }
        if ($this->statusFilter) {
            $invoiceQuery->where('status', $this->statusFilter);
        }
        $subscriptionInvoices = $invoiceQuery->latest()->paginate(15, ['*'], 'subscriptionPage');

        $paymentQuery = Payment::with(['subscription.customer', 'subscription.package.listing', 'invoice'])
            ->whereHas('subscription.package.listing', function ($query) {
                $this->scopePartnerRecords($query);
            });
            
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('invoice_viewany')) {
            $paymentQuery->where('created_by', auth()->id());
        }

        if ($this->search) {
            $paymentQuery->where(function ($q) {
                $q->where('gateway_ref', 'like', "%{$this->search}%")
                  ->orWhereHas('subscription.customer', function ($cq) {
                      $cq->where('name', 'like', "%{$this->search}%")
                         ->orWhere('email', 'like', "%{$this->search}%")
                         ->orWhere('mobile', 'like', "%{$this->search}%");
                  })
                  ->orWhereHas('subscription.package.listing', function ($lq) {
                      $lq->where('title', 'like', "%{$this->search}%");
                  });
            });
        }
        if ($this->statusFilter) {
            $paymentQuery->where('status', $this->statusFilter);
        }
        $paymentReceipts = $paymentQuery->latest()->paginate(15, ['*'], 'paymentPage');

        $viewingInvoice = null;
        if ($this->viewingInvoiceId) {
            $viewQuery = Invoice::whereHas('subscription.package.listing', function ($query) {
                $this->scopePartnerRecords($query);
            });
            if (!auth()->user()->isPartner() && !auth()->user()->canAccess('invoice_viewany')) {
                $viewQuery->where('created_by', auth()->id());
            }
            $viewingInvoice = $viewQuery->find($this->viewingInvoiceId);
        }
        
        $viewingPayment = null;
        if ($this->viewingPaymentId) {
            $viewQuery = Payment::with(['subscription.customer', 'subscription.package.listing', 'invoice', 'booking.customer', 'booking.package.listing'])
                ->where(function ($query) {
                    $query->whereHas('subscription.package.listing', function ($listingQuery) {
                        $this->scopePartnerRecords($listingQuery);
                    })->orWhereHas('booking.package.listing', function ($listingQuery) {
                        $this->scopePartnerRecords($listingQuery);
                    });
                });
            if (!auth()->user()->isPartner() && !auth()->user()->canAccess('invoice_viewany')) {
                $viewQuery->where('created_by', auth()->id());
            }
            $viewingPayment = $viewQuery->find($this->viewingPaymentId);
        }

        return view('livewire.partner.invoices', compact('subscriptionInvoices', 'paymentReceipts', 'viewingInvoice', 'viewingPayment'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Invoices',
                'pageSubtitle' => 'Track customer invoices and payment receipts',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
