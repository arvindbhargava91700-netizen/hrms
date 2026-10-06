<?php

namespace App\Livewire\Partner;

use App\Services\BookingService;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Bookings extends Component
{
    use WithPagination, HasPartnerWorkspaceScope;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $bookingFilter = '';
    public ?string $verifyingId = null;
    public string $otp = '';
    public ?string $viewingId = null;

    // Calendar Properties
    public string $viewMode = 'list';
    public $currentMonth;
    public $currentYear;
    public ?string $selectedDate = null;

    public function mount()
    {
        abort_unless(auth()->user()->canAccess('booking_viewany') || auth()->user()->canAccess('booking_viewown'), 403, 'Unauthorized access.');
        $this->currentMonth = date('n');
        $this->currentYear = date('Y');
    }

    protected function scopedBookingQuery()
    {
        return Booking::whereHas('package.listing', function ($query) {
            $this->scopePartnerRecords($query);
        });
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBookingFilter(): void
    {
        $this->resetPage();
    }

    public function viewBooking(string $id): void
    {
        abort_unless(auth()->user()->canAccess('booking_viewany') || auth()->user()->canAccess('booking_viewown'), 403, 'Unauthorized access.');
        $this->viewingId = $id;
        $this->selectedDate = null;
    }

    public function switchView(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function previousMonth(): void
    {
        if ($this->currentMonth == 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }
    }

    public function nextMonth(): void
    {
        if ($this->currentMonth == 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }
    }

    public function viewDateBookings(string $date): void
    {
        $this->selectedDate = $date;
    }

    public function closeDateBookings(): void
    {
        $this->selectedDate = null;
    }

    public function sendOtp(string $id, BookingService $service): void
    {
        abort_unless(auth()->user()->canAccess('booking_status_update'), 403, 'Unauthorized access.');
        $booking = $this->scopedBookingQuery()->findOrFail($id);

        try {
            $service->sendOtp($booking);
            session()->flash('success', 'OTP sent to customer successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openVerify(string $id): void
    {
        abort_unless(auth()->user()->canAccess('booking_status_update'), 403, 'Unauthorized access.');
        $this->verifyingId = $id;
        $this->otp = '';
        $this->resetValidation();
    }

    public function closeVerify(): void
    {
        $this->verifyingId = null;
        $this->otp = '';
    }

    public function verifyOtp(BookingService $service): void
    {
        abort_unless(auth()->user()->canAccess('booking_status_update'), 403, 'Unauthorized access.');
        $this->validate([
            'otp' => 'required|string|size:6'
        ]);

        $booking = $this->scopedBookingQuery()->findOrFail($this->verifyingId);

        try {
            if ($service->verifyOtp($booking, $this->otp)) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($booking) {
                    $booking->update(['status' => 'completed']);
                    $subscription = \App\Models\Subscription::create([
                        'customer_id'     => $booking->customer_id,
                        'package_id'      => $booking->package_id,
                        'room_id'         => $booking->room_id,
                        'occupancy_type'  => $booking->occupancy_type,
                        'beds_booked'     => $booking->beds_booked,
                        'starts_at'       => now()->toDateString(),
                        'expires_at'      => now()->addDays((int) ($booking->package->duration_days ?? 30))->toDateString(),
                        'status'          => 'active',
                        'auto_renew'      => $booking->payment_method === 'auto_pay',
                        'booking_id'      => $booking->id,
                    ]);
                    $invoice = $booking->invoices()->first();
                    if ($invoice) {
                        $invoice->update(['status' => 'paid', 'subscription_id' => $subscription->id]);
                    }

                    if ($booking->payment_method === 'cash') {
                        \App\Models\Payment::create([
                            'booking_id'      => $booking->id,
                            'subscription_id' => $subscription->id,
                            'invoice_id'      => $invoice?->id,
                            'gateway_ref'     => 'CASH_' . time(),
                            'gateway'         => 'cash',
                            'amount'          => $booking->final_amount,
                            'status'          => 'paid',
                            'paid_at'         => now(),
                        ]);
                    }

                    $partnerId = $booking->package?->listing?->partner_id;
                    $securityDeposit = $booking->security_deposit ?? 0;
                    if ($partnerId && $securityDeposit > 0) {
                        \App\Models\ReserveHistory::create([
                            'partner_id'  => $partnerId,
                            'customer_id' => $booking->customer_id,
                            'booking_id'  => $booking->id,
                            'amount'      => $securityDeposit,
                            'status'      => 'active',
                        ]);
                    }

                    app(\App\Http\Controllers\Api\PaymentController::class)->processCommission($booking);

                    if ($subscription->auto_renew && !$subscription->gateway_subscription_id) {
                        $subscription->update(['gateway_subscription_id' => 'sub_' . \Illuminate\Support\Str::random(14)]);
                    }
                });
                session()->flash('success', 'Booking confirmed and payment processed successfully.');
                $this->closeVerify();
            } else {
                $this->addError('otp', 'Invalid or expired OTP.');
            }
        } catch (\Exception $e) {
            $this->addError('otp', $e->getMessage());
        }
    }

    public function markPaymentReceived(string $id): void
    {
        abort_unless(auth()->user()->canAccess('booking_status_update'), 403, 'Unauthorized access.');
        $booking = $this->scopedBookingQuery()->findOrFail($id);

        if ($booking->status === 'confirmed' && $booking->payment_method === 'cash') {
            DB::transaction(function () use ($booking) {
                // Create subscription since it's paid
                $subscription = Subscription::create([
                    'customer_id'     => $booking->customer_id,
                    'package_id'      => $booking->package_id,
                    'room_id'         => $booking->room_id,
                    'occupancy_type'  => $booking->occupancy_type,
                    'beds_booked'     => $booking->beds_booked,
                    'starts_at'       => now()->toDateString(),
                    'expires_at'      => now()->addDays((int) ($booking->package->duration_days ?? 30))->toDateString(),
                    'status'          => 'active',
                    'auto_renew'      => false,
                    'booking_id'      => $booking->id,
                ]);

                $booking->update(['status' => 'completed']);
                
                // Mark payments as successful if they exist for this booking
                if ($booking->invoices->count() > 0) {
                    foreach ($booking->invoices as $invoice) {
                        if ($invoice->status !== 'paid') {
                            $invoice->update(['status' => 'paid', 'subscription_id' => $subscription->id]);
                        }
                    }
                }
                
                // Create a payment record
                Payment::create([
                    'booking_id' => $booking->id,
                    'subscription_id' => $subscription->id,
                    'gateway_ref' => 'CASH_' . time(),
                    'gateway' => 'cash',
                    'amount' => $booking->final_amount,
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
                
                // Add to Reserve History
                $partnerId = $booking->package?->listing?->partner_id;
                $securityDeposit = $booking->security_deposit ?? 0;
                if ($partnerId && $securityDeposit > 0) {
                    \App\Models\ReserveHistory::create([
                        'partner_id' => $partnerId,
                        'customer_id' => $booking->customer_id,
                        'booking_id' => $booking->id,
                        'amount' => $securityDeposit,
                        'status' => 'active',
                    ]);
                }
            });
            session()->flash('success', 'Payment marked as received and booking completed.');
        } else {
            session()->flash('error', 'Cannot mark payment received for this booking.');
        }
    }

    public function render(BookingService $bookingService)
    {
        $bookingsQuery = auth()->user()->isSuperAdmin()
            ? $this->scopedBookingQuery()->with(['customer', 'package.listing', 'invoices', 'payments'])
            : $bookingService->partnerBookingQuery($this->getPartnerId());

        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('booking_viewany')) {
            $bookingsQuery->where('created_by', auth()->id());
        }

        if ($this->search) {
            $bookingsQuery->whereHas('customer', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%");
            });
        }

        if ($this->bookingFilter) {
            $bookingsQuery->where('status', $this->bookingFilter);
        }

        $bookings = $bookingsQuery->latest()->paginate(15);

        $viewingBooking = null;
        if ($this->viewingId) {
            $viewQuery = $this->scopedBookingQuery()->with(['customer', 'package.listing', 'room', 'shift', 'invoices', 'payments']);
            if (!auth()->user()->isPartner() && !auth()->user()->canAccess('booking_viewany')) {
                $viewQuery->where('created_by', auth()->id());
            }
            $viewingBooking = $viewQuery->find($this->viewingId);
        }

        // Calendar Logic
        $daysInMonth = \Carbon\Carbon::create($this->currentYear, $this->currentMonth)->daysInMonth;
        $startDayOfWeek = \Carbon\Carbon::create($this->currentYear, $this->currentMonth, 1)->dayOfWeek;
        
        $monthBookings = collect();
        if ($this->viewMode === 'calendar') {
            $calQuery = auth()->user()->isSuperAdmin()
                ? $this->scopedBookingQuery()
                : $bookingService->partnerBookingQuery($this->getPartnerId());
            if (!auth()->user()->isPartner() && !auth()->user()->canAccess('booking_viewany')) {
                $calQuery->where('created_by', auth()->id());
            }

            $monthBookings = $calQuery->whereYear('created_at', $this->currentYear)
                ->whereMonth('created_at', $this->currentMonth)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');
        }

        $dateBookings = [];
        if ($this->selectedDate) {
            $dateQuery = (auth()->user()->isSuperAdmin()
                ? $this->scopedBookingQuery()
                : $bookingService->partnerBookingQuery($this->getPartnerId()))
                ->with(['customer', 'package.listing']);
            if (!auth()->user()->isPartner() && !auth()->user()->canAccess('booking_viewany')) {
                $dateQuery->where('created_by', auth()->id());
            }
            $dateBookings = $dateQuery->whereDate('created_at', $this->selectedDate)
                ->latest()
                ->get();
        }

        return view('livewire.partner.bookings', compact('bookings', 'viewingBooking', 'daysInMonth', 'startDayOfWeek', 'monthBookings', 'dateBookings'))
            ->layout('layouts.app', [
                'panelName'    => 'Workspace',
                'pageTitle'    => 'Bookings',
                'pageSubtitle' => 'Manage your bookings and confirm payments',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
