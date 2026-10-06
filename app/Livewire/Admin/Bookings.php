<?php

namespace App\Livewire\Admin;

use App\Services\BookingService;
use App\Models\Subscription;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Bookings extends Component
{
    use WithPagination;

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

    protected string $paginationTheme = 'bootstrap';

    public function mount()
    {
        $this->currentMonth = date('n');
        $this->currentYear = date('Y');
    }

    protected function bookingService(): BookingService
    {
        return app(BookingService::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBookingFilter(): void
    {
        $this->resetPage();
    }

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'bookings',
            'search' => $this->search,
            'status' => $this->bookingFilter,
        ]);
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

    public function sendOtp(string $id): void
    {
        $booking = Booking::findOrFail($id);

        if (! $this->bookingService()->canResendOtp($booking)) {
            session()->flash('error', 'Please wait 60 seconds before sending OTP again.');
            return;
        }

        try {
            $this->bookingService()->sendOtp($booking);
            $this->bookingService()->markOtpSent($booking);
            session()->flash('success', 'OTP sent to customer mobile (' . ($booking->customer->mobile ?? 'N/A') . ').');
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function openVerify(string $id): void
    {
        Booking::findOrFail($id);
        $this->verifyingId = $id;
        $this->otp = '';
        $this->resetValidation();
    }

    public function closeVerify(): void
    {
        $this->verifyingId = null;
        $this->otp = '';
        $this->resetValidation();
    }

    public function verifyOtp(): void
    {
        $this->validate([
            'otp' => 'required|string|size:6',
        ]);

        $booking = Booking::findOrFail($this->verifyingId);

        try {
            if (! $this->bookingService()->verifyOtp($booking, $this->otp)) {
                $this->addError('otp', 'Invalid OTP. Ask the customer for the code sent to their mobile.');
                return;
            }

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
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
            $this->closeVerify();
            return;
        }

        session()->flash('success', 'Booking confirmed and payment processed successfully.');
        $this->closeVerify();
    }

    public function viewBooking(string $id): void
    {
        $this->viewingId = $id;
        $this->selectedDate = null;
    }

    public function markPaymentReceived(string $id): void
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'confirmed' || $booking->payment_method !== 'cash') {
            session()->flash('error', 'Cannot mark this booking as paid.');
            return;
        }

        DB::transaction(function () use ($booking) {
            $invoice = $booking->invoices()->first();

            $subscription = Subscription::create([
                'customer_id'     => $booking->customer_id,
                'package_id'      => $booking->package_id,
                'room_id'         => $booking->room_id,
                'occupancy_type'  => $booking->occupancy_type,
                'beds_booked'     => $booking->beds_booked,
                'starts_at'       => now()->toDateString(),
                'expires_at'      => now()->addDays((int) $booking->package->duration_days)->toDateString(),
                'status'          => 'active',
                'auto_renew'      => false,
                'booking_id'      => $booking->id,
            ]);

            \App\Models\Payment::create([
                'subscription_id' => $subscription->id,
                'booking_id'      => $booking->id,
                'invoice_id'      => $invoice?->id,
                'gateway'         => 'cash',
                'amount'          => $booking->final_amount,
                'status'          => 'paid',
                'paid_at'         => now(),
            ]);

            $booking->update(['status' => 'completed']);
            if ($invoice) {
                $invoice->update(['status' => 'paid', 'subscription_id' => $subscription->id]);
            }
            
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
            
            app(\App\Services\WhatsAppNotificationService::class)->sendPaymentCompleted($subscription);
            
            if ($booking->customer?->fcm_token) {
                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                    $booking->customer->fcm_token,
                    'Payment Received',
                    'Your manual payment has been received and subscription is active!',
                    ['booking_id' => $booking->id, 'type' => 'payment_success']
                );
            }
        });

        session()->flash('success', 'Payment marked as received. Subscription is now active!');
    }

    public function render()
    {
        $bookings = Booking::with(['customer', 'package.listing'])
            ->when($this->search, fn ($q) => $q->whereHas('customer', fn ($q2) =>
                $q2->where('name', 'like', "%{$this->search}%")
                    ->orWhere('mobile', 'like', "%{$this->search}%")
            ))
            ->when($this->bookingFilter, fn ($q) => $q->where('status', $this->bookingFilter))
            ->latest()
            ->paginate(15);

        $viewingBooking = $this->viewingId
            ? Booking::with(['customer', 'package.listing', 'invoices'])->find($this->viewingId)
            : null;

        $stats = [
            'total' => Booking::count(),
            'pending' => Booking::where('status', 'pending')->count(),
            'confirmed' => Booking::where('status', 'confirmed')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
        ];

        // Calendar Logic
        $daysInMonth = \Carbon\Carbon::create($this->currentYear, $this->currentMonth)->daysInMonth;
        $startDayOfWeek = \Carbon\Carbon::create($this->currentYear, $this->currentMonth, 1)->dayOfWeek;
        
        $monthBookings = collect();
        if ($this->viewMode === 'calendar') {
            $monthBookings = Booking::whereYear('created_at', $this->currentYear)
                ->whereMonth('created_at', $this->currentMonth)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');
        }

        $dateBookings = [];
        if ($this->selectedDate) {
            $dateBookings = Booking::with(['customer', 'package.listing'])
                ->whereDate('created_at', $this->selectedDate)
                ->latest()
                ->get();
        }

        return view('livewire.admin.bookings', compact('bookings', 'viewingBooking', 'stats', 'daysInMonth', 'startDayOfWeek', 'monthBookings', 'dateBookings'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Bookings',
                'pageSubtitle' => 'Send OTP, confirm bookings, track payment status',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
