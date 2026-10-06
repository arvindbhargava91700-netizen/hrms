<?php

namespace App\Livewire\Partner;

use App\Models\Subscription;
use Livewire\Component;
use Livewire\WithPagination;
use App\Services\BookingService;

class Subscriptions extends Component
{
    use WithPagination, HasPartnerWorkspaceScope;

    protected string $paginationTheme = 'bootstrap';

    public string $search = '';
    public string $statusFilter = '';
    public string $expireFilter = '';
    public ?string $viewingId = null;

    public ?string $schedulingId = null;
    public string $scheduledPaymentDate = '';
    public float $scheduledPaymentAmount = 0;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingExpireFilter(): void
    {
        $this->resetPage();
    }

    public function mount()
    {
        abort_unless(auth()->user()->canAccess('subscription_viewany') ||
         auth()->user()->canAccess('subscription_viewown'), 403, 'Unauthorized access.');
        if (request()->has('expireFilter')) {
            $this->expireFilter = request()->query('expireFilter');
        }
    }

    protected function scopedSubscriptionQuery(array $with = [])
    {
        return Subscription::with($with)->whereHas('package.listing', function ($query) {
            $this->scopePartnerRecords($query);
        });
    }

    public function viewSubscription(string $id): void
    {
        \Log::info("Partner viewSubscription called for ID: " . $id);
        abort_unless(auth()->user()->canAccess('subscription_viewany') || auth()->user()->canAccess('subscription_viewown'), 403, 'Unauthorized access.');
        $this->viewingId = $id;
    }

    public function closeView(): void
    {
        $this->viewingId = null;
    }

    public function cancel(string $id): void
    {
        abort_unless(auth()->user()->canAccess('subscription_update'), 403, 'Unauthorized access.');
        $subscription = $this->scopedSubscriptionQuery()->findOrFail($id);
        
        $subscription->update([
            'status' => 'cancelled',
            'expires_at' => now(),
        ]);
        
        $this->dispatch('toast', message: 'Subscription cancelled.', type: 'info');
    }

    public function openSchedulePaymentModal(string $id): void
    {
        abort_unless(auth()->user()->canAccess('subscription_update'), 403, 'Unauthorized access.');
        $subscription = $this->scopedSubscriptionQuery(['package'])->findOrFail($id);
        
        $this->schedulingId = $id;
        $this->scheduledPaymentDate = '';
        // Auto-calculate pending amount from package price
        $this->scheduledPaymentAmount = $subscription->package ? $subscription->package->price : 0;
        
        $this->dispatch('show-schedule-modal');
    }

    public function saveScheduledPayment(): void
    {
        abort_unless(auth()->user()->canAccess('subscription_update'), 403, 'Unauthorized access.');
        
        $this->validate([
            'scheduledPaymentDate' => 'required|date|after_or_equal:today',
        ]);

        $subscription = $this->scopedSubscriptionQuery()->findOrFail($this->schedulingId);
        
        $subscription->update([
            'scheduled_payment_date' => $this->scheduledPaymentDate,
            'pending_amount'         => $this->scheduledPaymentAmount,
            'grace_period_active'    => true,
            'status'                 => 'active' // Keep it active during grace period
        ]);
        
        $this->dispatch('hide-schedule-modal');
        $this->dispatch('toast', message: 'Payment scheduled successfully.', type: 'success');
        
        $this->schedulingId = null;
    }

    public function markScheduledPaid(string $id): void
    {
        abort_unless(auth()->user()->canAccess('subscription_update'), 403, 'Unauthorized access.');
        $subscription = $this->scopedSubscriptionQuery(['package.listing.partner'])->findOrFail($id);
        
        $amount = $subscription->pending_amount;
        $partner = $subscription->package->listing->partner;
        
        // Use PaymentController to process commission
        app(\App\Http\Controllers\Api\PaymentController::class)->processSubscriptionCommission($subscription, $amount, 'cash');

        // Generate invoice
        $invoice = \App\Models\Invoice::create([
            'subscription_id' => $subscription->id,
            'invoice_number'  => 'INV-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'amount'          => $amount,
            'tax'             => 0,
            'total'           => $amount,
            'status'          => 'paid',
            'due_date'        => now(),
            'paid_at'         => now(),
        ]);
        
        $subscription->update([
            'scheduled_payment_date' => null,
            'pending_amount'         => 0,
            'grace_period_active'    => false,
        ]);
        
        // Better handling of expiry extension based on package
        if ($subscription->package) {
            $cycle = $subscription->package->billing_cycle; // 'monthly', 'quarterly', 'yearly'
            $expires = \Carbon\Carbon::parse($subscription->expires_at);
            if ($cycle === 'yearly') $expires->addYear();
            elseif ($cycle === 'quarterly') $expires->addMonths(3);
            elseif ($cycle === 'weekly') $expires->addWeeks(1);
            else $expires->addMonth();
            $subscription->update(['expires_at' => $expires]);
        }

        $this->dispatch('toast', message: 'Scheduled payment marked as paid.', type: 'success');
    }

    public $actionRefundMethod = 'wallet';
    public $processingLeaveId = null;

    public function initiateApproveLeave($id)
    {
        abort_unless(auth()->user()->canAccess('subscription_update'), 403, 'Unauthorized access.');
        $this->processingLeaveId = $id;
        $this->dispatch('show-approve-modal');
    }

    public function approveLeaveRequest()
    {
        abort_unless(auth()->user()->canAccess('subscription_update'), 403, 'Unauthorized access.');
        if (!$this->processingLeaveId) return;

        $subscription = $this->scopedSubscriptionQuery(['booking.room'])->findOrFail($this->processingLeaveId);
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($subscription) {
            $subscription->update([
                'leave_status' => 'approved',
                'status' => 'cancelled',
            ]);

            $booking = $subscription->booking;
            if ($booking) {
                // Free up the bed/room if applicable
                if ($booking->room_id && $booking->occupancy_type) {
                    $room = $booking->room;
                    if ($room) {
                        if ($booking->occupancy_type === 'full_room') {
                            $room->increment('available_beds', $room->capacity);
                        } elseif ($booking->occupancy_type === 'per_bed') {
                            $room->increment('available_beds', $booking->beds_booked);
                        }
                    }
                }

                $reserve = \App\Models\ReserveHistory::where('booking_id', $booking->id)
                            ->where('status', 'active')
                            ->first();

                if ($reserve) {
                    $refundAmt = (float) $reserve->amount;

                    if ($this->actionRefundMethod === 'wallet') {
                        $partner = \App\Models\User::find($booking->package?->listing?->partner_id);
                        if ($partner) {
                            $partner->decrement('wallet_balance', $refundAmt);
                            
                            \App\Models\WalletTransaction::create([
                                'user_id' => $partner->id,
                                'amount' => -$refundAmt,
                                'type' => 'debit',
                                'description' => 'Reserve amount refunded to customer (Wallet)',
                                'reference_type' => 'booking_cancellation',
                                'reference_id' => $booking->id,
                            ]);
                        }

                        $customer = \App\Models\User::find($booking->customer_id);
                        if ($customer) {
                            $customer->increment('wallet_balance', $refundAmt);

                            \App\Models\WalletTransaction::create([
                                'user_id' => $customer->id,
                                'amount' => $refundAmt,
                                'type' => 'credit',
                                'description' => 'Reserve amount refunded to main wallet',
                                'reference_type' => 'booking_cancellation',
                                'reference_id' => $booking->id,
                            ]);
                        }
                        
                        $reserve->update(['status' => 'refunded_wallet']);
                    } else if ($this->actionRefundMethod === 'cash') {
                        $reserve->update(['status' => 'refunded_cash']);
                    }
                }
            }
        });
        
        $this->dispatch('hide-approve-modal');
        $this->processingLeaveId = null;
        session()->flash('success', 'Subscription cancellation approved and reserve refunded.');
    }

    public function rejectLeaveRequest($id)
    {
        abort_unless(auth()->user()->canAccess('subscription_update'), 403, 'Unauthorized access.');
        $subscription = $this->scopedSubscriptionQuery()->findOrFail($id);
        
        $subscription->update([
            'leave_status' => 'none',
            'auto_renew' => true,
        ]);
        
        session()->flash('success', 'Subscription cancellation rejected.');
    }

    public function renewSubscriptionOnline(string $id)
    {
        abort_unless(auth()->user()->canAccess('subscription_update') || auth()->user()->isPartner(), 403, 'Unauthorized access.');
        
        $subscription = $this->scopedSubscriptionQuery(['booking.package.listing.partner.tpiMerchant', 'customer', 'package'])->findOrFail($id);
        
        if ($subscription->status !== 'expired' && $subscription->status !== 'active') {
            session()->flash('error', 'Only active or expired subscriptions can be renewed.');
            return;
        }

        $merchant = $subscription->booking->package->listing->partner->tpiMerchant ?? null;
        $merchantId = ($merchant && $merchant->status === 'active') ? $merchant->tpi_merchant_id : null;
        
        if (!$merchantId) {
            $merchantId = \App\Models\SystemSetting::getSetting('default_merchant_id');
        }

        if (!$merchantId) {
            session()->flash('error', 'Online payments are currently unavailable (No Merchant ID). Please use Renew (Cash).');
            return;
        }

        try {
            $tpiService = app(\App\Services\TpiPaymentService::class);
            $amount = $subscription->package->price;
            $customerName = $subscription->customer->name ?? 'Customer';
            $customerEmail = $subscription->customer->email ?? 'noreply@feetrack.com';
            $customerPhone = $subscription->customer->mobile ?? '9999999999';

            $resp = $tpiService->createPayment([
                'merchantId' => $merchantId,
                'orderId' => 'SUB-' . substr($subscription->id, 0, 8) . '-' . time(),
                'amount' => (float)$amount,
                'customerName' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
                'surl' => url('/api/payment/tpi/success?sub=' . $subscription->id),
                'furl' => url('/api/payment/tpi/failure?sub=' . $subscription->id),
                'productInfo' => substr($subscription->package->name, 0, 100),
                'requestFlow' => 'CUSTOM_CHECKOUT'
            ]);

            $paymentId = $resp['paymentId'] ?? null;
            $paymentLink = $resp['paymentLink'] ?? null;

            if ($paymentId && $paymentLink) {
                \App\Models\Payment::create([
                    'subscription_id' => $subscription->id,
                    'gateway'         => 'tpipay',
                    'gateway_ref'     => $paymentId,
                    'amount'          => $amount,
                    'status'          => 'pending'
                ]);

                return redirect()->away($paymentLink);
            } else {
                session()->flash('error', 'Failed to generate payment link. Invalid API response.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Payment Gateway Error: ' . $e->getMessage());
        }
    }

    public function renewSubscriptionCash(string $id)
    {
        abort_unless(auth()->user()->canAccess('subscription_update') || auth()->user()->isPartner(), 403, 'Unauthorized access.');
        
        $subscription = $this->scopedSubscriptionQuery(['booking.package.listing', 'customer', 'package'])->findOrFail($id);
        
        if ($subscription->status !== 'expired' && $subscription->status !== 'active') {
            session()->flash('error', 'Only active or expired subscriptions can be renewed.');
            return;
        }

        $booking = $subscription->booking;
        $package = $subscription->package;
        
        if (!$booking || !$package) {
            session()->flash('error', 'Cannot renew: missing booking or package details.');
            return;
        }
        
        $partnerId = $subscription->package?->listing?->partner_id;
        $partner = \App\Models\User::find($partnerId);
        if (!$partner) {
            session()->flash('error', 'Partner account not found.');
            return;
        }
        
        $totalAmt = (float) $package->price;
        $commData = \App\Services\PackageService::calculateCommission($partnerId, $totalAmt, 'offline');
        $commissionAmt = $commData['commission'];
        
        if ($commissionAmt > 0 && $partner->wallet_balance < $commissionAmt) {
            session()->flash('error', "Insufficient wallet balance for platform fee (₹{$commissionAmt}). Your current balance is ₹{$partner->wallet_balance}. Please recharge your wallet to renew this subscription.");
            return;
        }
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($subscription, $booking, $package, $partner, $commissionAmt, $totalAmt) {
            if ($commissionAmt > 0) {
                $partner->decrement('wallet_balance', $commissionAmt);
                \App\Models\WalletTransaction::create([
                    'user_id'        => $partner->id,
                    'amount'         => -$commissionAmt,
                    'type'           => 'debit',
                    'description'    => "Platform fee deduction for offline cash renewal of subscription #{$subscription->id}",
                    'reference_type' => Subscription::class,
                    'reference_id'   => (string) $subscription->id,
                ]);
            }
            
            $invoice = \App\Models\Invoice::create([
                'created_by'      => auth()->id(),
                'booking_id'      => $booking->id,
                'subscription_id' => $subscription->id,
                'invoice_number'  => 'INV-' . strtoupper(\Illuminate\Support\Str::random(8)),
                'amount'          => $totalAmt,
                'tax'             => 0,
                'total'           => $totalAmt,
                'due_date'        => now(),
                'status'          => 'paid',
            ]);
            
            \App\Models\Payment::create([
                'created_by'      => auth()->id(),
                'booking_id'      => $booking->id,
                'subscription_id' => $subscription->id,
                'invoice_id'      => $invoice->id,
                'gateway'         => 'cash',
                'gateway_ref'     => 'RENEW_CASH_' . time(),
                'amount'          => $totalAmt,
                'status'          => 'paid',
                'paid_at'         => now(),
            ]);
            
            $durationDays = (int) $package->duration_days;
            $startDate = ($subscription->expires_at && $subscription->expires_at->isFuture()) 
                ? $subscription->expires_at 
                : now();
                
            if ($subscription->expires_at && $subscription->expires_at->isFuture()) {
                $newExpiry = $durationDays > 0 ? $startDate->copy()->addDays($durationDays) : null;
            } else {
                $newExpiry = $durationDays > 0 ? now()->addDays($durationDays - 1)->endOfDay() : null;
            }
            
            $subscription->update([
                'status'       => 'active',
                'leave_status' => 'none',
                'auto_renew'   => true,
                'expires_at'   => $newExpiry,
            ]);
        });
        
        if ($this->viewingId == $id) {
            $this->viewSubscription($id);
        }
        
        session()->flash('success', 'Subscription successfully renewed with cash payment.');
    }

    public function cancelScheduledPayment(string $id): void
    {
        abort_unless(auth()->user()->canAccess('subscription_update'), 403, 'Unauthorized access.');
        $subscription = $this->scopedSubscriptionQuery()->findOrFail($id);
        
        $status = 'active';
        if ($subscription->expires_at && $subscription->expires_at->isPast()) {
            $status = 'expired';
        }
        
        $subscription->update([
            'scheduled_payment_date' => null,
            'pending_amount'         => 0,
            'grace_period_active'    => false,
            'status'                 => $status
        ]);
        
        $this->dispatch('toast', message: 'Scheduled payment cancelled.', type: 'info');
    }

    public function render(BookingService $bookingService)
    {
        $query = auth()->user()->isSuperAdmin()
            ? $this->scopedSubscriptionQuery()
            : $bookingService->partnerSubscriptionQuery($this->getPartnerId());
        
        $query->with(['customer', 'package.listing.category', 'invoices', 'booking']);

        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('subscription_viewany')) {
            $query->where('created_by', auth()->id());
        }
        
        if ($this->search) {
            $query->whereHas('customer', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%");
            });
        }
        
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->expireFilter === 'today') {
            $query->whereDate('expires_at', now()->format('Y-m-d'));
        } elseif ($this->expireFilter === '3days') {
            $query->whereDate('expires_at', '>=', now()->format('Y-m-d'))
                  ->whereDate('expires_at', '<=', now()->addDays(3)->format('Y-m-d'));
        } elseif ($this->expireFilter === 'week') {
            $query->whereDate('expires_at', '>=', now()->format('Y-m-d'))
                  ->whereDate('expires_at', '<=', now()->addDays(7)->format('Y-m-d'));
        } elseif ($this->expireFilter === 'expired') {
            $query->where('status', 'expired');
        }

        $subscriptions = $query->latest()->paginate(15);
        
        $viewingSubscription = null;
        if ($this->viewingId) {
            $viewQuery = $this->scopedSubscriptionQuery([
                'customer', 'package.listing.category', 
                'payments' => fn($q) => $q->latest(), 
                'invoices' => fn($q) => $q->latest(), 
                'room', 'booking'
            ]);
            if (!auth()->user()->isPartner() && !auth()->user()->canAccess('subscription_viewany')) {
                $viewQuery->where('created_by', auth()->id());
            }
            $viewingSubscription = $viewQuery->find($this->viewingId);
        }

        $categories = \App\Models\Category::orderBy('name')->get();

        $statsQuery = $this->scopedSubscriptionQuery();
        
        if (!auth()->user()->isPartner() && !auth()->user()->canAccess('subscription_viewany')) {
            $statsQuery->where('created_by', auth()->id());
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'active' => (clone $statsQuery)->where('status', 'active')->count(),
            'expiring' => (clone $statsQuery)->where('status', 'active')->where('expires_at', '<=', now()->addDays(7))->count(),
            'expired' => (clone $statsQuery)->whereIn('status', ['expired', 'cancelled'])->count(),
        ];

        return view('livewire.partner.subscriptions', compact('subscriptions', 'viewingSubscription', 'categories', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Subscriptions',
                'pageSubtitle' => 'Manage active customer subscriptions',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
