<?php

namespace App\Livewire\Admin;

use App\Models\ReserveHistory;
use App\Models\Booking;
use Livewire\Component;
use Livewire\WithPagination;

class ReserveHistories extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public ?string $viewingBookingId = null;

    protected string $paginationTheme = 'bootstrap';

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
        $reserveHistories = ReserveHistory::with(['partner', 'customer', 'booking.package.listing', 'booking.room', 'booking.shift', 'booking.invoices', 'booking.payments'])
            ->when($this->search, function($q) {
                $q->whereHas('partner', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('customer', fn($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('booking', fn($q2) => $q2->where('id', 'like', "%{$this->search}%"));
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);
            
        $viewingBooking = $this->viewingBookingId ? Booking::with(['customer', 'package.listing', 'room', 'shift', 'invoices', 'payments'])->find($this->viewingBookingId) : null;

        return view('livewire.admin.reserve-histories', compact('reserveHistories', 'viewingBooking'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Reserve History',
                'pageSubtitle' => 'View all security deposits held by partners',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
