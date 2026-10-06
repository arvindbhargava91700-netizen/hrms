<?php

namespace App\Livewire\Partner;

use App\Models\Payment;
use App\Models\Package;
use Livewire\Component;
use Livewire\WithPagination;

class Payments extends Component
{
    use WithPagination;
    use HasPartnerWorkspaceScope;

    public string $search = '';
    public string $statusFilter = '';
    public ?string $viewingPaymentId = null;

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

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('payment_viewany') || auth()->user()->canAccess('payment_viewown'), 403);
    }

    public function render()
    {
        $packageIds = Package::whereHas('listing', fn($q) => $this->scopePartnerRecords($q))->pluck('id');

        $query = Payment::with(['subscription.customer', 'subscription.package.listing', 'invoice', 'booking.customer', 'booking.package.listing'])
            ->where(function ($q) use ($packageIds) {
                $q->whereHas('subscription', fn ($query) => $query->whereIn('package_id', $packageIds))
                  ->orWhereHas('booking', fn ($query) => $query->whereIn('package_id', $packageIds));
            });
            
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('payment_viewany')) {
            $query->where('created_by', auth()->id());
        }

        $payments = $query->when($this->search, fn($q) => $q->where(function($sq) {
                $sq->whereHas('subscription.customer', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                   ->orWhereHas('booking.customer', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                   ->orWhere('gateway_ref', 'like', "%{$this->search}%");
            }))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        $viewingPayment = $this->viewingPaymentId
            ? Payment::with(['subscription.customer', 'subscription.package.listing', 'invoice', 'booking.customer', 'booking.package.listing'])
                ->where(function ($query) use ($packageIds) {
                    $query->whereHas('subscription', fn ($subscriptionQuery) => $subscriptionQuery->whereIn('package_id', $packageIds))
                        ->orWhereHas('booking', fn ($bookingQuery) => $bookingQuery->whereIn('package_id', $packageIds));
                })
                ->find($this->viewingPaymentId)
            : null;

        return view('livewire.partner.payments', compact('payments', 'viewingPayment'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Payments',
                'pageSubtitle' => 'View your listing payments',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
