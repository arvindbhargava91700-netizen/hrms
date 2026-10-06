<?php

namespace App\Livewire\Admin;

use App\Models\Subscription;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Livewire\Attributes\Url;

class Subscriptions extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $sortBy = 'latest';
    public string $categoryFilter = '';
    public ?string $viewingId = null;
    #[Url] public ?string $partner = null;
    #[Url] public ?string $customer = null;
    #[Url] public ?string $listing = null;

    public ?string $schedulingId = null;
    public string $scheduledPaymentDate = '';
    public float $scheduledPaymentAmount = 0;

    protected string $paginationTheme = 'bootstrap';
    
    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingSortBy() { $this->resetPage(); }
    public function updatingCategoryFilter() { $this->resetPage(); }

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'subscriptions',
            'search' => $this->search,
            'status' => $this->statusFilter,
            'sort' => $this->sortBy,
        ]);
    }

    public function cancel(string $id): void
    {
        $subscription = Subscription::findOrFail($id);
        $subscription->update(['status' => 'cancelled']);
        
        app(\App\Services\WhatsAppNotificationService::class)->sendSubscriptionCancelled($subscription);
        
        if ($subscription->customer?->fcm_token) {
            app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                $subscription->customer->fcm_token,
                'Subscription Cancelled',
                'Your subscription has been cancelled.',
                ['subscription_id' => $subscription->id, 'type' => 'subscription_cancelled']
            );
        }
        
        session()->flash('success', 'Subscription cancelled successfully.');
    }

    public function openSchedulePaymentModal(string $id): void
    {
        $subscription = Subscription::with('package')->findOrFail($id);
        
        $this->schedulingId = $id;
        $this->scheduledPaymentDate = '';
        $this->scheduledPaymentAmount = $subscription->package ? $subscription->package->price : 0;
        
        $this->dispatch('show-schedule-modal');
    }

    public function saveScheduledPayment(): void
    {
        $this->validate([
            'scheduledPaymentDate' => 'required|date|after_or_equal:today',
        ]);

        $subscription = Subscription::findOrFail($this->schedulingId);
        
        $subscription->update([
            'scheduled_payment_date' => $this->scheduledPaymentDate,
            'pending_amount'         => $this->scheduledPaymentAmount,
            'grace_period_active'    => true,
            'status'                 => 'active'
        ]);
        
        $this->dispatch('hide-schedule-modal');
        $this->dispatch('toast', message: 'Payment scheduled successfully.', type: 'success');
        
        $this->schedulingId = null;
    }

    public function markScheduledPaid(string $id): void
    {
        $subscription = Subscription::with('package.listing.partner')->findOrFail($id);
        
        $amount = $subscription->pending_amount;
        $partner = $subscription->package->listing->partner;
        
        if ($partner) {
            app(\App\Http\Controllers\Api\PaymentController::class)->processSubscriptionCommission($subscription, $amount, 'cash');
        }

        \App\Models\Invoice::create([
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
        
        if ($subscription->package) {
            $cycle = $subscription->package->billing_cycle;
            $expires = \Carbon\Carbon::parse($subscription->expires_at);
            if ($cycle === 'yearly') $expires->addYear();
            elseif ($cycle === 'quarterly') $expires->addMonths(3);
            elseif ($cycle === 'weekly') $expires->addWeeks(1);
            else $expires->addMonth();
            $subscription->update(['expires_at' => $expires]);
        }

        $this->dispatch('toast', message: 'Scheduled payment marked as paid.', type: 'success');
    }

    public function cancelScheduledPayment(string $id): void
    {
        $subscription = Subscription::findOrFail($id);
        
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

    public function renewSubscriptionOnline(string $id)
    {
        $subscription = Subscription::with('package.listing.partner.tpiMerchant', 'customer')->findOrFail($id);
        
        if ($subscription->status !== 'expired' && $subscription->status !== 'active') {
            session()->flash('error', 'Only active or expired subscriptions can be renewed.');
            return;
        }

        $partnerUser = $subscription->package->listing->partner ?? null;
        $merchantId = $partnerUser ? $partnerUser->getResolvedTpiMerchantId() : null;

        if (!$merchantId) {
            session()->flash('error', 'Partner TPI Pay gateway is not setup and no default gateway found.');
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
        $subscription = Subscription::with(['booking.package.listing', 'customer', 'package'])->findOrFail($id);
        
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
        
        $totalAmt = (float) $package->price;
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($subscription, $booking, $package, $totalAmt) {
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
                
            $newExpiry = $durationDays > 0 ? $startDate->copy()->addDays($durationDays) : null;
            
            $subscription->update([
                'status'       => 'active',
                'leave_status' => 'none',
                'auto_renew'   => true,
                'expires_at'   => $newExpiry,
            ]);
        });
        
        if ($this->viewingId == $id) {
            $this->viewDetails($id);
        }
        
        session()->flash('success', 'Subscription successfully renewed with cash payment.');
    }

    public function viewDetails(string $id): void
    {
        $this->viewingId = $id;
    }

    public function closeView(): void
    {
        $this->viewingId = null;
    }

    public function render()
    {
        $query = Subscription::with(['customer', 'package.listing.category', 'invoices', 'booking'])
            ->when($this->search, function ($query) {
                $query->whereHas('customer', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('mobile', 'like', '%' . $this->search . '%');
                });
            });

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->partner) {
            $query->whereHas('package.listing', fn($q) => $q->where('partner_id', $this->partner));
        }

        if ($this->customer) {
            $query->where('customer_id', $this->customer);
        }

        if ($this->listing) {
            $query->whereHas('package', fn($q) => $q->where('listing_id', $this->listing));
        }

        if ($this->categoryFilter) {
            $query->whereHas('package.listing', fn($q) => $q->where('category_id', $this->categoryFilter));
        }

        // Sorting
        $query = match($this->sortBy) {
            'expiring' => $query->orderBy('expires_at', 'asc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'customer' => $query->orderByRaw('(SELECT name FROM users WHERE users.id = subscriptions.customer_id) ASC'),
            default => $query->latest(),
        };

        $subscriptions = $query->paginate(15);

        $viewingSubscription = $this->viewingId 
            ? Subscription::with([
                'customer', 'package.listing.category', 
                'payments' => fn($q) => $q->latest(), 
                'invoices' => fn($q) => $q->latest(),
                'room', 'booking'
            ])->find($this->viewingId)
            : null;

        $categories = \App\Models\Category::orderBy('name')->get();

        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'expiring' => Subscription::where('status', 'active')->where('expires_at', '<=', now()->addDays(7))->count(),
            'expired' => Subscription::whereIn('status', ['expired', 'cancelled'])->count(),
        ];

        return view('livewire.admin.subscriptions', compact('subscriptions', 'viewingSubscription', 'categories', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Subscriptions',
                'pageSubtitle' => 'Manage all customer subscriptions',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
