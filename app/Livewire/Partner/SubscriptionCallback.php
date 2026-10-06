<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use Illuminate\Http\Request;
use App\Models\PartnerSubscription;
use App\Models\PartnerPackage;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SubscriptionCallback extends Component
{
    public $status = 'verifying'; // verifying, success, failed
    public $message = 'Verifying your payment...';
    
    public function mount(Request $request)
    {
        $subscriptionId = $request->input('subscription');
        $easepayid = $request->input('easepayid') ?? $request->input('paymentId');
        
        $sub = null;
        if ($subscriptionId) {
            $sub = PartnerSubscription::with('package')->find($subscriptionId);
        }

        if (!$sub) {
            $this->status = 'failed';
            $this->message = 'Subscription request not found.';
            return;
        }

        if (!$easepayid) {
            $txnId = $request->input('txn');
            if ($txnId) {
                $txn = \App\Models\TransactionHistory::find($txnId);
                if ($txn) {
                    $easepayid = $txn->transaction_id;
                }
            }
        }

        if (!$easepayid) {
            $this->status = 'failed';
            $this->message = 'Invalid payment callback received. No transaction ID found.';
            return;
        }

        if ($sub->status === 'active') {
            $this->status = 'success';
            $this->message = 'Payment was already verified and subscription activated.';
            return;
        }
        
        if ($sub->status !== 'pending') {
            $this->status = 'failed';
            $this->message = 'Subscription request already processed or rejected.';
            return;
        }

        try {
            $tpiService = app(\App\Services\TpiPaymentService::class);
            $check = $tpiService->checkPaymentStatus($easepayid);
            
            $isSuccess = false;
            if (isset($check['status']) && strtolower($check['status']) === 'success') {
                $isSuccess = true;
            } elseif (isset($check['status']) && $check['status'] === true && !isset($check['data'])) {
                $isSuccess = true;
            } elseif (isset($check['data']['status']) && strtolower($check['data']['status']) === 'success') {
                $isSuccess = true;
            }

            if ($isSuccess) {
                DB::transaction(function () use ($sub, $easepayid) {
                    $partner = User::find($sub->partner_id);
                    $package = $sub->package;

                    // Cancel any currently active subscriptions
                    PartnerSubscription::where('partner_id', $partner->id)
                        ->where('status', 'active')
                        ->where('id', '!=', $sub->id)
                        ->update(['status' => 'cancelled']);

                    // Calculate expiration date
                    $expiresAt = $package->duration_days > 0 
                        ? now()->addDays($package->duration_days - 1)->endOfDay() 
                        : null;

                    $sub->update([
                        'status' => 'active',
                        'starts_at' => now(),
                        'expires_at' => $expiresAt,
                    ]);

                    // Enforce new package limits (downgrades)
                    \App\Services\PackageService::enforceLimits($partner->id);

                    // Find the pending transaction to get wallet deduction
                    $txnId = request()->input('txn');
                    $txn = \App\Models\TransactionHistory::find($txnId);
                    $walletUsed = 0;
                    if ($txn) {
                        $walletUsed = max(0, $txn->total_amount - $txn->net_amount);
                        
                        // Update the transaction
                        $txn->update([
                            'status' => 'completed',
                            'payment_gateway_id' => $easepayid // Update with gateway transaction id
                        ]);
                    }

                    // Deduct wallet if used
                    if ($walletUsed > 0) {
                        $partner->decrement('wallet_balance', $walletUsed);
                        \App\Models\WalletTransaction::create([
                            'user_id' => $partner->id,
                            'amount' => $walletUsed,
                            'type' => 'debit',
                            'description' => 'Purchased Platform Package (Partial): ' . $package->name,
                            'reference_type' => 'platform_subscription',
                        ]);
                    }

                    // If no txn was found or created, ensure we still log it for safety (fallback)
                    if (!$txn) {
                        \App\Models\TransactionHistory::create([
                            'transaction_id' => (string) \Illuminate\Support\Str::uuid(),
                            'user_id' => $partner->id,
                            'type' => 'subscription',
                            'reference_id' => $sub->id,
                            'total_amount' => $package->price,
                            'wallet_deducted' => 0,
                            'online_payable' => $package->price,
                            'payment_gateway_id' => $easepayid,
                            'platform_fee' => 0, 
                            'net_amount' => $package->price,
                            'status' => 'completed',
                            'description' => 'Platform Subscription paid via Online Gateway'
                        ]);
                    }
                });

                $this->status = 'success';
                $this->message = 'Payment successful! Your new platform plan is now active.';
            } else {
                $sub->update(['status' => 'failed']);
                $this->status = 'failed';
                $this->message = 'Your payment could not be verified or was cancelled.';
            }
        } catch (\Exception $e) {
            Log::error('TPI Status Check Error for Subscription ' . $easepayid . ': ' . $e->getMessage());
            $this->status = 'failed';
            $this->message = 'An error occurred while verifying the payment. Please contact support if the amount was deducted.';
        }
    }

    public function render()
    {
        return view('livewire.partner.subscription-callback')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Subscription Status',
                'pageSubtitle' => 'Verifying your payment',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
