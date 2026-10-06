<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function initiatePayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'gateway'    => 'required|string|in:razorpay,stripe,tpipay'
        ]);

        $invoice = Invoice::find($request->invoice_id);

        if ($invoice->isPaid()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invoice is already paid.'
            ], 422);
        }

        if ($request->gateway === 'tpipay') {
            $booking = \App\Models\Booking::with('package.listing.partner.tpiMerchant', 'customer')->find($invoice->booking_id);
            $merchant = $booking->package->listing->partner->tpiMerchant ?? null;
            $merchantId = $merchant->tpi_merchant_id ?? null;

            if (!$merchantId) {
                $admin = \App\Models\User::whereIn('role', ['super_admin'])->first();
                $merchantId = $admin ? ($admin->tpiMerchant->tpi_merchant_id ?? null) : null;
            }
            
            if (!$merchantId) {
                $merchantId = \App\Models\SystemSetting::getSetting('default_merchant_id');
            }

            if (!$merchantId) {
                return response()->json(['status' => 'error', 'message' => 'Partner TPI Pay gateway is not setup and no default gateway found.'], 422);
            }

            try {
                $tpiService = app(\App\Services\TpiPaymentService::class);
                $resp = $tpiService->createPayment([
                    'merchantId' => $merchantId,
                    'orderId' => 'INV-' . substr($invoice->id, 0, 8) . '-' . time(),
                    'amount' => (float)$invoice->total,
                    'customerName' => $booking->customer->name,
                    'email' => $booking->customer->email,
                    'phone' => $booking->customer->mobile ?? '9999999999',
                    'surl' => url('/api/payment/tpi/success'),
                    'furl' => url('/api/payment/tpi/failure'),
                    'productInfo' => substr($booking->package->name, 0, 100),
                    'requestFlow' => 'CUSTOM_CHECKOUT'
                ]);

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'order_id' => $resp['paymentId'] ?? null,
                        'accessKey' => $resp['accessKey'] ?? null,
                        'payment_link' => $resp['paymentLink'] ?? null,
                        'amount' => $invoice->total,
                        'currency' => 'INR',
                        'gateway' => 'tpipay'
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        }

        // STUB: Here you would integrate Razorpay/Stripe Order generation
        $gatewayOrderId = 'order_' . Str::random(10);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'order_id' => $gatewayOrderId,
                'amount'   => $invoice->total,
                'currency' => 'INR',
                'gateway'  => $request->gateway
            ]
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'invoice_id'     => 'required|exists:invoices,id',
            'gateway'        => 'required|string',
            'gateway_ref'    => 'required|string',
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        if ($invoice->isPaid()) {
            return response()->json(['status' => 'error', 'message' => 'Invoice already paid.'], 422);
        }

        // STUB: Verify gateway signature (e.g., Razorpay signature)

        $booking = \App\Models\Booking::with('package.listing')->find($invoice->booking_id);

        if (!$booking || !in_array($booking->status, ['pending_payment', 'pending', 'confirmed'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or unconfirmed booking.'], 422);
        }

        $payment = Payment::updateOrCreate(
            ['booking_id' => $booking->id, 'invoice_id' => $invoice->id, 'status' => 'pending'],
            [
                'gateway'         => $request->gateway,
                'gateway_ref'     => $request->gateway_ref,
                'amount'          => $invoice->total,
                'status'          => 'paid',
                'paid_at'         => now(),
            ]
        );

        $invoice->update(['status' => 'paid']);
        $booking->update(['status' => 'pending_otp']);

        // Generate and send the OTP
        try {
            app(\App\Services\BookingService::class)->sendOtp($booking);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Auto OTP generation failed after payment: ' . $e->getMessage());
        }

        if ($booking->customer?->fcm_token) {
            app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                $booking->customer->fcm_token,
                'Payment Successful',
                'Your payment has been verified. Please verify your OTP with the partner to complete your booking!',
                ['booking_id' => $booking->id, 'type' => 'payment_success']
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment successful. Please verify OTP to complete booking.',
            'data'    => [
                'payment' => $payment,
            ]
        ]);
    }

    public function tpiSuccess(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('TPI Pay Success Callback', $request->all());

        $easepayid = $request->input('easepayid') ?? $request->input('paymentId');
        $bkg = $request->input('bkg');
        $sub = $request->input('sub');
        $recharge = $request->input('recharge');
        
        if ($recharge) {
            $rechargeReq = \App\Models\WalletRechargeRequest::find($recharge);
            if (!$rechargeReq) {
                return response("<html><body><h2>Recharge Verification Failed</h2><p>Record not found.</p></body></html>");
            }
            
            // TPI Pay might not pass the paymentId in the callback payload, so use the one we saved
            if (!$easepayid) {
                $easepayid = $rechargeReq->transaction_id;
            }

            if ($rechargeReq->status === 'approved') {
                return response()->json(['status' => 'success', 'message' => 'Already verified.']);
            }

            $tpiService = app(\App\Services\TpiPaymentService::class);
            try {
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
                    $rechargeReq->update([
                        'status' => 'approved',
                        'approved_at' => now()
                    ]);

                    $user = \App\Models\User::find($rechargeReq->user_id);
                    if ($user) {
                        $totalAmount = $rechargeReq->amount;
                        
                        // Platform fee is NOT deducted during wallet recharge (0 fee)
                        $adminAmount = 0;
                        $partnerAmount = $totalAmount - $adminAmount;

                        $user->increment('wallet_balance', $partnerAmount);
                        \App\Models\WalletTransaction::create([
                            'user_id'        => $user->id,
                            'type'           => 'credit',
                            'amount'         => $partnerAmount,
                            'description'    => "Recharge of ₹" . number_format($totalAmount, 2) . " (Platform fee deducted: ₹" . number_format($adminAmount, 2) . ")",
                            'reference_type' => \App\Models\WalletRechargeRequest::class,
                            'reference_id'   => $rechargeReq->id,
                        ]);

                        $admin = \App\Models\User::where('role', 'super_admin')->first();
                        if ($admin && $adminAmount > 0) {
                            $admin->increment('wallet_balance', $adminAmount);
                            
                            \App\Models\WalletTransaction::create([
                                'user_id'        => $admin->id,
                                'type'           => 'credit',
                                'amount'         => $adminAmount,
                                'description'    => "Admin fee of ₹" . number_format($adminAmount, 2) . " from wallet recharge of ₹" . number_format($totalAmount, 2) . " by " . $user->name,
                                'reference_type' => \App\Models\WalletRechargeRequest::class,
                                'reference_id'   => $rechargeReq->id,
                            ]);
                        }
                    }
                    if ($user) {
                        try {
                            app(\App\Services\WhatsAppNotificationService::class)->sendWalletRechargeSuccess($user, $totalAmount);
                            
                            if ($user->fcm_token) {
                                app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                                    $user->fcm_token,
                                    'Wallet Recharge Successful 💰',
                                    "Your wallet has been recharged with ₹" . number_format($totalAmount, 2) . ". Your new balance is ₹" . number_format($user->wallet_balance, 2) . ".",
                                    ['type' => 'wallet_recharge_success']
                                );
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Wallet recharge notification failed: ' . $e->getMessage());
                        }
                    }

                    return response()->json(['status' => 'success', 'message' => 'Recharge successful']);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('TPI Status Check Error for Recharge ' . $easepayid . ': ' . $e->getMessage());
            }

            return response("<html><body><h2>Recharge Verification Failed</h2><p>Your payment could not be verified.</p></body></html>");
        }

        $pendingPayments = [];

        if ($easepayid) {
            $paymentData = Payment::where('gateway_ref', $easepayid)->first();
            if ($paymentData) {
                $pendingPayments[] = $paymentData;
            }
        } elseif ($bkg) {
            $pendingPayments = Payment::where('booking_id', $bkg)->where('status', 'pending')->get();
        } elseif ($sub) {
            $pendingPayments = Payment::where('subscription_id', $sub)->where('status', 'pending')->get();
        }

        if (count($pendingPayments) === 0) {
            // Check if already paid
            if ($bkg) {
                $paid = Payment::where('booking_id', $bkg)->where('status', 'paid')->first();
                if ($paid) return response()->json(['status' => 'success', 'message' => 'Already verified.']);
            } elseif ($sub) {
                $paid = Payment::where('subscription_id', $sub)->where('status', 'paid')->first();
                if ($paid) return response()->json(['status' => 'success', 'message' => 'Already verified.']);
            }
            return response()->json(['status' => 'error', 'message' => 'Payment record not found.'], 404);
        }

        $paymentData = null;
        $tpiService = app(\App\Services\TpiPaymentService::class);

        foreach ($pendingPayments as $payment) {
            if ($payment->gateway_ref) {
                try {
                    $check = $tpiService->checkPaymentStatus($payment->gateway_ref);
                    
                    $isSuccess = false;
                    if (isset($check['status']) && strtolower($check['status']) === 'success') {
                        $isSuccess = true;
                    } elseif (isset($check['status']) && $check['status'] === true && !isset($check['data'])) {
                        $isSuccess = true;
                    } elseif (isset($check['data']['status']) && strtolower($check['data']['status']) === 'success') {
                        $isSuccess = true; // Handle potential nested status
                    }

                    if ($isSuccess) {
                        $paymentData = $payment;
                        break; // Found the successful payment
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('TPI Status Check Error for ' . $payment->gateway_ref . ': ' . $e->getMessage());
                }
            }
        }

        if (!$paymentData) {
            \Illuminate\Support\Facades\Log::warning('TPI Pay Success Callback: Status check failed for all pending attempts.');
            return response("<html><body><h2>Payment Verification Failed</h2><p>Your payment could not be verified.</p></body></html>");
        }

        if ($paymentData->status === 'paid') {
            return response()->json(['status' => 'success', 'message' => 'Already verified.']);
        }

        // Handle Subscription Renewal
        if ($paymentData->subscription_id) {
            $subscription = \App\Models\Subscription::with('package', 'customer')->find($paymentData->subscription_id);
            if ($subscription) {
                $paymentData->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);

                // Create Invoice for Renewal
                $invoice = Invoice::create([
                    'subscription_id' => $subscription->id,
                    'amount'          => $paymentData->amount,
                    'tax'             => 0,
                    'total'           => $paymentData->amount,
                    'due_date'        => now()->toDateString(),
                    'status'          => 'paid',
                ]);
                $paymentData->update(['invoice_id' => $invoice->id]);

                // Extend Subscription
                $startDate = max(now(), \Carbon\Carbon::parse($subscription->expires_at));
                $subscription->update([
                    'status' => 'active',
                    'expires_at' => $startDate->addDays((int) $subscription->package->duration_days)->toDateString(),
                ]);

                // Distribute Commission for the renewal
                $this->processSubscriptionCommission($subscription, $paymentData->amount);

                return response("<html><body><h2>Payment Successful</h2><p>Your subscription has been renewed. Please return to the app.</p><script>setTimeout(function(){ window.close(); }, 3000);</script></body></html>");
            }
        }

        // Handle Booking Payment
        $booking = \App\Models\Booking::with('package.listing', 'customer')->find($paymentData->booking_id);

        if (!$booking || !in_array($booking->status, ['pending_payment', 'pending', 'confirmed'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid or unconfirmed booking.'], 422);
        }

        $paymentData->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        // Generate Invoice now
        $invoice = Invoice::create([
            'booking_id'      => $booking->id,
            'amount'          => $paymentData->amount,
            'tax'             => 0,
            'total'           => $paymentData->amount,
            'due_date'        => now()->toDateString(),
            'status'          => 'paid',
        ]);
        
        $paymentData->update(['invoice_id' => $invoice->id]);

        $booking->update(['status' => 'pending_otp']);

        // Generate and send the OTP
        try {
            app(\App\Services\BookingService::class)->sendOtp($booking);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Auto OTP generation failed after payment: ' . $e->getMessage());
        }

        if ($booking->customer?->fcm_token) {
            app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                $booking->customer->fcm_token,
                'Payment Successful',
                'Your payment has been verified. Please verify your OTP with the partner to complete your booking!',
                ['booking_id' => $booking->id, 'type' => 'payment_success']
            );
        }

        // Return a simple HTML page that closes the window or communicates with the app
        return response("<html><body><h2>Payment Successful</h2><p>Your payment was successful. Please return to the app.</p><script>setTimeout(function(){ window.close(); }, 3000);</script></body></html>");
    }

    public function tpiFailure(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('TPI Pay Failure Callback', $request->all());

        $easepayid = $request->input('easepayid') ?? $request->input('paymentId');
        $bkg = $request->input('bkg');
        
        $payment = null;
        if ($easepayid) {
            $payment = Payment::where('gateway_ref', $easepayid)->first();
        } elseif ($bkg) {
            $payment = Payment::where('booking_id', $bkg)->where('status', 'pending')->first();
        } elseif ($request->input('sub')) {
            $payment = Payment::where('subscription_id', $request->input('sub'))->where('status', 'pending')->first();
        }

        if ($payment) {
            $payment->update(['status' => 'failed']);
        }

        return response("<html><body><h2>Payment Failed</h2><p>Your payment failed or was cancelled. Please try again in the app.</p><script>setTimeout(function(){ window.close(); }, 3000);</script></body></html>");
    }

    public function history(Request $request)
    {
        $payments = Payment::with(['invoice', 'subscription.package.room'])
            ->whereHas('subscription', fn($q) => $q->where('customer_id', $request->user()->id))
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $payments
        ]);
    }

    public function initiateAutopay(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'gateway'    => 'required|string|in:razorpay'
        ]);

        $booking = \App\Models\Booking::with('package')->findOrFail($request->booking_id);

        if ($booking->customer_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Booking must be OTP-confirmed before autopay setup.',
            ], 422);
        }

        $gatewaySubId = 'sub_' . Str::random(14);
        $booking->update([
            'payment_method' => 'auto_pay',
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'gateway_subscription_id' => $gatewaySubId,
                'amount'                  => (float) ($booking->final_amount ?: $booking->package->price),
                'currency'                => 'INR',
                'gateway'                 => $request->gateway
            ]
        ]);
    }

    public function verifyAutopay(Request $request)
    {
        $request->validate([
            'booking_id'               => 'required|exists:bookings,id',
            'gateway_subscription_id'  => 'required|string',
            'razorpay_payment_id'      => 'required|string',
            'razorpay_signature'       => 'required|string',
        ]);

        $booking = \App\Models\Booking::with(['invoices', 'package.listing'])->findOrFail($request->booking_id);

        if ($booking->customer_id !== $request->user()->id) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Booking must be OTP-confirmed before autopay setup.',
            ], 422);
        }

        // STUB: Verify razorpay_signature here using Razorpay SDK.

        // Create subscription since it's paid
        $subscription = Subscription::create([
            'customer_id'     => $booking->customer_id,
            'package_id'      => $booking->package_id,
            'room_id'         => $booking->room_id,
            'occupancy_type'  => $booking->occupancy_type,
            'beds_booked'     => $booking->beds_booked,
            'starts_at'       => now()->toDateString(),
            'expires_at'      => now()->addDays((int) $booking->package->duration_days)->toDateString(),
            'status'          => 'active',
            'auto_renew'      => true,
            'payment_method'  => 'auto_pay',
            'booking_id'      => $booking->id,
            'gateway_subscription_id' => $request->gateway_subscription_id,
        ]);

        $booking->update(['status' => 'completed']);

        // Process Partner & Admin Commission Wallet distribution
        $this->processCommission($booking);

        // Mark the first invoice as paid
        $invoice = $booking->invoices()->first();
        if ($invoice) {
            $invoice->update(['status' => 'paid', 'subscription_id' => $subscription->id]);

            Payment::updateOrCreate(
                ['booking_id' => $booking->id, 'invoice_id' => $invoice->id, 'status' => 'pending'],
                [
                    'subscription_id' => $subscription->id,
                    'gateway'         => 'razorpay',
                    'gateway_ref'     => $request->razorpay_payment_id,
                    'amount'          => $invoice->total,
                    'status'          => 'paid',
                    'paid_at'         => now(),
                ]
            );
        }

        app(\App\Services\WhatsAppNotificationService::class)->sendPaymentCompleted($subscription);
        if ($booking->customer?->fcm_token) {
            app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                $booking->customer->fcm_token,
                'Autopay Setup Successful',
                'Your autopay setup is complete and subscription is active!',
                ['booking_id' => $booking->id, 'type' => 'autopay_success']
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Autopay setup successfully verified.',
            'data'    => $subscription
        ]);
    }

    public function processInvoicePayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'gateway'    => 'required|in:razorpay,offline'
        ]);

        $invoice = Invoice::findOrFail($request->invoice_id);

        if ($invoice->isPaid()) {
            return response()->json(['status' => 'error', 'message' => 'Invoice already paid.'], 422);
        }

        // STUB: Verify gateway signature (e.g., Razorpay signature)

        $booking = \App\Models\Booking::with('package.listing')->find($invoice->booking_id);

        if (!$booking || $booking->status !== 'confirmed') {
            return response()->json(['status' => 'error', 'message' => 'Invalid or unconfirmed booking.'], 422);
        }

        // Create subscription since it's paid
        $subscription = Subscription::create([
            'customer_id'     => $booking->customer_id,
            'package_id'      => $booking->package_id,
            'room_id'         => $booking->room_id,
            'occupancy_type'  => $booking->occupancy_type,
            'beds_booked'     => $booking->beds_booked,
            'starts_at'       => now()->toDateString(),
            'expires_at'      => now()->addDays((int) $booking->package->duration_days)->toDateString(),
        ]);

        $invoice->update(['status' => 'paid', 'subscription_id' => $subscription->id]);
        $booking->update(['status' => 'completed']);

        // Process Partner & Admin Commission Wallet distribution
        $this->processCommission($booking);

        if ($subscription && $subscription->auto_renew && !$subscription->gateway_subscription_id) {
            $subscription->update(['gateway_subscription_id' => 'sub_' . Str::random(14)]);
        }

        if ($booking->customer?->fcm_token) {
            app(\App\Services\FirebaseNotificationService::class)->sendNotification(
                $booking->customer->fcm_token,
                'Payment Successful',
                'Your payment has been verified. Subscription is now active!',
                ['booking_id' => $booking->id, 'type' => 'payment_success']
            );
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment successful',
            'data'    => [
                'subscription' => $subscription,
            ]
        ]);
    }

    private function calculatePlatformFee($partner, $totalAmount, $paymentMethod)
    {
        $isOffline = in_array(strtolower($paymentMethod), ['cash', 'offline']);
        $adminAmount = 0;

        $activeSub = \App\Models\PartnerSubscription::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->with('package')
            ->first();

        if ($activeSub && $activeSub->package) {
            $package = $activeSub->package;
            $ranges = $isOffline ? $package->offline_commission_ranges : $package->commission_ranges;
            $type = $isOffline ? $package->offline_commission_type : $package->commission_type;
            $value = $isOffline ? $package->offline_commission_value : $package->commission_value;

            if (is_array($ranges)) {
                foreach ($ranges as $range) {
                    $min = (float) ($range['min_amount'] ?? 0);
                    $max = (isset($range['max_amount']) && $range['max_amount'] !== '') ? (float) $range['max_amount'] : PHP_FLOAT_MAX;
                    if ($totalAmount >= $min && $totalAmount <= $max) {
                        $type = $range['type'] ?? $type;
                        $value = $range['value'] ?? $value;
                        break;
                    }
                }
            }

            $value = (float) $value;
            if ($type === 'percent' || $type === 'percentage') {
                $adminAmount = ($totalAmount * $value) / 100;
            } else {
                $adminAmount = $value;
            }
        } else {
            $adminCommissionPerc = 10;
            $setting = \App\Models\SystemSetting::where('key', 'admin_commission_percentage')->first();
            if ($setting && $setting->value) {
                $adminCommissionPerc = (float) $setting->value;
            }
            $adminAmount = ($totalAmount * $adminCommissionPerc) / 100;
        }

        if ($adminAmount > $totalAmount) {
            $adminAmount = $totalAmount;
        }

        return $adminAmount;
    }

    public function processCommission(\App\Models\Booking $booking)
    {
        $booking->loadMissing(['package.listing.partner', 'room.floor.listing.partner']);
        $partner = $booking->package->listing->partner ?? ($booking->room->floor->listing->partner ?? null);
        if (!$partner) return;

        $totalAmount = $booking->final_amount;
        $adminAmount = $this->calculatePlatformFee($partner, $totalAmount, $booking->payment_method);
        $partnerAmount = $totalAmount - $adminAmount;
        
        $partner->increment('wallet_balance', $partnerAmount);
        
        \App\Models\WalletTransaction::create([
            'user_id'        => $partner->id,
            'type'           => 'credit',
            'amount'         => $partnerAmount,
            'description'    => "Payment of ₹" . number_format($totalAmount, 2) . " for booking #{$booking->id} (Platform fee deducted: ₹" . number_format($adminAmount, 2) . ")",
            'reference_type' => \App\Models\Booking::class,
            'reference_id'   => $booking->id,
        ]);
        
        $admin = \App\Models\User::where('role', 'super_admin')->first();
        if ($admin && $adminAmount > 0) {
            $admin->increment('wallet_balance', $adminAmount);
            
            \App\Models\WalletTransaction::create([
                'user_id'        => $admin->id,
                'type'           => 'credit',
                'amount'         => $adminAmount,
                'description'    => "Admin fee of ₹" . number_format($adminAmount, 2) . " from booking #{$booking->id} payment of ₹" . number_format($totalAmount, 2) . " by " . $partner->name,
                'reference_type' => \App\Models\Booking::class,
                'reference_id'   => $booking->id,
            ]);
        }
        
        \App\Models\TransactionHistory::create([
            'transaction_id' => 'BKG-' . $booking->id . '-' . time(),
            'user_id' => $partner->id,
            'type' => 'booking',
            'reference_id' => $booking->id,
            'total_amount' => $totalAmount,
            'platform_fee' => $adminAmount,
            'net_amount' => $partnerAmount,
            'status' => 'completed',
            'description' => "Booking commission processed",
        ]);
    }

    public function processSubscriptionCommission(\App\Models\Subscription $subscription, $totalAmount, $paymentMethod = 'online')
    {
        $subscription->loadMissing(['package.listing.partner']);
        $partner = $subscription->package->listing->partner ?? null;
        if (!$partner) return;

        $adminAmount = $this->calculatePlatformFee($partner, $totalAmount, $paymentMethod);
        $partnerAmount = $totalAmount - $adminAmount;
        
        $partner->increment('wallet_balance', $partnerAmount);
        
        \App\Models\WalletTransaction::create([
            'user_id'        => $partner->id,
            'type'           => 'credit',
            'amount'         => $partnerAmount,
            'description'    => "Payment of ₹" . number_format($totalAmount, 2) . " for subscription renewal #{$subscription->id} (Platform fee deducted: ₹" . number_format($adminAmount, 2) . ")",
            'reference_type' => \App\Models\Subscription::class,
            'reference_id'   => $subscription->id,
        ]);
        
        $admin = \App\Models\User::where('role', 'super_admin')->first();
        if ($admin && $adminAmount > 0) {
            $admin->increment('wallet_balance', $adminAmount);
            
            \App\Models\WalletTransaction::create([
                'user_id'        => $admin->id,
                'type'           => 'credit',
                'amount'         => $adminAmount,
                'description'    => "Admin fee of ₹" . number_format($adminAmount, 2) . " from subscription renewal #{$subscription->id} payment of ₹" . number_format($totalAmount, 2) . " by " . $partner->name,
                'reference_type' => \App\Models\Subscription::class,
                'reference_id'   => $subscription->id,
            ]);
        }
        
        \App\Models\TransactionHistory::create([
            'transaction_id' => 'SUB-' . $subscription->id . '-' . time(),
            'user_id' => $partner->id,
            'type' => 'subscription',
            'reference_id' => $subscription->id,
            'total_amount' => $totalAmount,
            'platform_fee' => $adminAmount,
            'net_amount' => $partnerAmount,
            'status' => 'completed',
            'description' => "Subscription renewal processed",
        ]);
    }
}
