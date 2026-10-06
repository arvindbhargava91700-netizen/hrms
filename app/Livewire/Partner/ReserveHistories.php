<?php

namespace App\Livewire\Partner;

use App\Models\ReserveHistory;
use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;

class ReserveHistories extends Component
{
    use WithPagination, HasPartnerWorkspaceScope;

    public string $search = '';
    public string $statusFilter = '';
    public ?string $viewingBookingId = null;

    protected string $paginationTheme = 'bootstrap';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('wallet_viewany'), 403, 'Unauthorized access.');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function viewBooking(string $bookingId): void
    {
        $this->viewingBookingId = $bookingId;
    }
    
    public function closeBookingView(): void
    {
        $this->viewingBookingId = null;
    }

    public function render()
    {
        $reserveHistories = $this->scopePartnerRecords(ReserveHistory::with(['partner', 'customer', 'booking.package.listing', 'booking.room', 'booking.shift', 'booking.invoices', 'booking.payments']))
            ->when($this->search, function($q) {
                $q->where(function($q2) {
                    $q2->whereHas('customer', fn($q3) => $q3->where('name', 'like', "%{$this->search}%"))
                       ->orWhereHas('booking', fn($q3) => $q3->where('id', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);
            
        $viewingBooking = $this->viewingBookingId
            ? Booking::with(['customer', 'package.listing', 'room', 'shift', 'invoices', 'payments'])
                ->whereHas('package.listing', fn ($query) => $this->scopePartnerRecords($query))
                ->find($this->viewingBookingId)
            : null;

        return view('livewire.partner.reserve-histories', compact('reserveHistories', 'viewingBooking'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Reserve History',
                'pageSubtitle' => 'View all security deposits held',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
