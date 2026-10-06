<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\Room;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use App\Mail\CustomerPaymentReminderMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionService
{
    /**
     * Subscribe a customer to a package and generate the first invoice.
     */
    public function subscribe(User $customer, Package $package, bool $autoRenew = true): Subscription
    {
        return DB::transaction(function () use ($customer, $package, $autoRenew) {
            $startsAt = Carbon::today();
            $expiresAt = $startsAt->copy()->addDays($package->duration_days - 1)->endOfDay();
            $room = $package->room ? Room::whereKey($package->room_id)->lockForUpdate()->first() : null;
            $bedsBooked = 1;
            $occupancyType = $package->occupancy_type ?? 'standard';

            if ($room) {
                $isFullRoom = $occupancyType === 'full_room';
                $availableBeds = (int) $room->available_beds;

                if ($isFullRoom) {
                    if ($availableBeds < (int) $room->capacity) {
                        throw new \RuntimeException('This room is no longer available for full-room booking.');
                    }
                    $bedsBooked = (int) $room->capacity;
                    $room->update(['available_beds' => 0]);
                } else {
                    if ($availableBeds < 1) {
                        throw new \RuntimeException('No beds are available in this room.');
                    }
                    $bedsBooked = 1;
                    $room->decrement('available_beds');
                }
            }

            // Create Subscription
            $subscription = Subscription::create([
                'customer_id' => $customer->id,
                'package_id'  => $package->id,
                'room_id'     => $room?->id,
                'occupancy_type' => $room ? $occupancyType : 'standard',
                'beds_booked' => $bedsBooked,
                'starts_at'   => $startsAt,
                'expires_at'  => $expiresAt,
                'status'      => 'pending',
                'auto_renew'  => $autoRenew,
            ]);

            // Generate Invoice
            $this->generateInvoice($subscription, $package->price);

            return $subscription;
        });
    }

    /**
     * Generate an invoice for a subscription.
     */
    public function generateInvoice(Subscription $subscription, float $amount): Invoice
    {
        $tax = $amount * 0.18; // 18% GST Example
        $total = $amount + $tax;

        return Invoice::create([
            'subscription_id' => $subscription->id,
            'invoice_number'  => 'INV-' . strtoupper(Str::random(8)),
            'amount'          => $amount,
            'tax'             => $tax,
            'total'           => $total,
            'due_date'        => Carbon::today()->addDays(3),
            'status'          => 'sent',
        ]);
    }

    /**
     * Renew an existing active/expired subscription.
     */
    public function renew(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            $package = $subscription->package;

            // If it's already expired, new start date is today. If active, append to end.
            $startsAt = $subscription->isExpired() ? Carbon::today() : $subscription->expires_at;
            if ($subscription->isExpired()) {
                $expiresAt = $startsAt->copy()->addDays($package->duration_days - 1)->endOfDay();
            } else {
                $expiresAt = $startsAt->copy()->addDays($package->duration_days);
            }

            $subscription->update([
                'starts_at'      => $startsAt,
                'expires_at'     => $expiresAt,
                'status'         => 'active',
                'booking_status' => 'active',
            ]);

            $this->generateInvoice($subscription, $package->price);

            return $subscription;
        });
    }

    /**
     * Check subscriptions for expiry and process auto-renewals.
     */
    public function processDailyRenewals(): void
    {
        // 0. Handle overdue scheduled payments (grace period expired without payment)
        $overdueScheduled = Subscription::with(['package.listing.partner', 'customer'])
            ->where('grace_period_active', true)
            ->whereDate('scheduled_payment_date', '<', Carbon::today())
            ->get();

        foreach ($overdueScheduled as $sub) {
            $this->handleOverdueScheduledPayment($sub);
        }

        // 1. Mark expired ones as expired if they don't auto-renew AND no active grace period is protecting them
        Subscription::where('status', 'active')
            ->where('auto_renew', false)
            ->whereDate('expires_at', '<', Carbon::today())
            ->where(function($q) {
                $q->where('grace_period_active', false)
                  ->orWhere(function($sq) {
                      $sq->where('grace_period_active', true)
                         ->whereDate('scheduled_payment_date', '<', Carbon::today());
                  });
            })
            ->update([
                'status'         => 'expired',
                'booking_status' => 'completed',
            ]);

        // 2. Auto-renew subscriptions expiring today
        $expiringToday = Subscription::where('status', 'active')
            ->where('auto_renew', true)
            ->where('grace_period_active', false)
            ->whereDate('expires_at', Carbon::today())
            ->get();

        foreach ($expiringToday as $sub) {
            $this->renew($sub);
        }
    }

    private function handleOverdueScheduledPayment(Subscription $sub): void
    {
        $package = $sub->package;
        $partner = $package?->listing?->partner;
        $customer = $sub->customer;

        if (!$package || !$partner || !$customer) {
            $sub->update(['status' => 'expired', 'grace_period_active' => false]);
            return;
        }

        // Calculate per-day cost
        $cycle = $package->billing_cycle; // 'monthly', 'quarterly', 'yearly'
        $daysInCycle = 30;
        if ($cycle === 'yearly') $daysInCycle = 365;
        elseif ($cycle === 'quarterly') $daysInCycle = 90;
        elseif ($cycle === 'weekly') $daysInCycle = 7;
        
        $perDayCost = $package->price / $daysInCycle;
        
        // Days elapsed in grace period
        $daysElapsed = Carbon::parse($sub->expires_at)->diffInDays(Carbon::parse($sub->scheduled_payment_date));
        if ($daysElapsed < 0) $daysElapsed = 0;
        
        $totalDeduction = round($perDayCost * $daysElapsed, 2);
        if ($totalDeduction > $sub->pending_amount) {
            $totalDeduction = $sub->pending_amount;
        }

        // Find active reserve
        $reserve = \App\Models\ReserveHistory::where('customer_id', $customer->id)
            ->where('partner_id', $partner->id)
            ->where('status', 'active')
            ->first();

        if ($reserve && $reserve->amount > 0) {
            $actualDeduction = min($totalDeduction, $reserve->amount);
            $refundAmount = $reserve->amount - $actualDeduction;

            // Transfer deduction to Partner
            if ($actualDeduction > 0) {
                $partner->increment('wallet_balance', $actualDeduction);
                \App\Models\WalletTransaction::create([
                    'user_id' => $partner->id,
                    'type' => 'credit',
                    'amount' => $actualDeduction,
                    'description' => "Recovered unpaid scheduled payment for Subscription #{$sub->id} from Reserve",
                    'reference_type' => Subscription::class,
                    'reference_id' => $sub->id,
                ]);
            }

            // Refund remainder to Customer
            if ($refundAmount > 0) {
                $customer->increment('wallet_balance', $refundAmount);
                \App\Models\WalletTransaction::create([
                    'user_id' => $customer->id,
                    'type' => 'credit',
                    'amount' => $refundAmount,
                    'description' => "Security deposit refunded after deduction for unpaid Subscription #{$sub->id}",
                    'reference_type' => \App\Models\ReserveHistory::class,
                    'reference_id' => $reserve->id,
                ]);
            }

            $reserve->update([
                'status' => 'refunded_wallet',
            ]);
        }

        $sub->update([
            'status' => 'expired',
            'booking_status' => 'completed',
            'grace_period_active' => false,
        ]);
    }

    /**
     * Send payment reminders (due/overdue invoices & upcoming autopays).
     */
    public function sendPaymentReminders(): void
    {
        // 1. Due / Overdue Payments
        $unpaidInvoices = Invoice::with(['subscription.customer', 'subscription.package.listing'])
            ->where('status', 'sent')
            ->get();

        foreach ($unpaidInvoices as $invoice) {
            $subscription = $invoice->subscription;
            if (!$subscription) continue;
            
            $customer = $subscription->customer;
            $package = $subscription->package;
            if (!$customer || !$package) continue;

            if ($invoice->due_date->isPast() || $invoice->due_date->isToday()) {
                try {
                    \Illuminate\Support\Facades\Mail::to($customer->email)->send(
                        new CustomerPaymentReminderMail($customer, $invoice, $package, 'due')
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to send payment due email: ' . $e->getMessage());
                }

                if ($customer->fcm_token) {
                    app(FirebaseNotificationService::class)->sendNotification(
                        $customer->fcm_token,
                        'Payment Overdue Alert',
                        "Your payment of ₹" . number_format($invoice->total, 2) . " for {$package->listing->title} is due/overdue."
                    );
                }
            }
        }

        // 2. Upcoming Autopays (remind 2 days before renewal)
        $upcomingAutopays = Subscription::with(['customer', 'package.listing'])
            ->where('status', 'active')
            ->where('auto_renew', true)
            ->whereDate('expires_at', Carbon::today()->addDays(2))
            ->get();

        foreach ($upcomingAutopays as $sub) {
            $customer = $sub->customer;
            $package = $sub->package;
            if (!$customer || !$package) continue;

            $amount = $package->price;
            $tax = $amount * 0.18;
            $total = $amount + $tax;

            $dummyInvoice = new Invoice([
                'total' => $total,
                'due_date' => $sub->expires_at,
            ]);

            try {
                \Illuminate\Support\Facades\Mail::to($customer->email)->send(
                    new CustomerPaymentReminderMail($customer, $dummyInvoice, $package, 'next')
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send autopay reminder email: ' . $e->getMessage());
            }

            if ($customer->fcm_token) {
                app(FirebaseNotificationService::class)->sendNotification(
                    $customer->fcm_token,
                    'Upcoming Autopay Reminder',
                    "Your subscription for {$package->listing->title} will automatically renew on " . $sub->expires_at->format('d M, Y') . ". Amount: ₹" . number_format($total, 2)
                );
            }
        }
    }
}
