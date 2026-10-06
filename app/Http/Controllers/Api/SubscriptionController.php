<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    
    /**
     * List all subscriptions for the authenticated customer.
     */
    public function index(Request $request): JsonResponse
    {
        $subscriptions = Subscription::with([
            'package.listing.category',
            'package.listing.images',
            'room',
            'invoices',
            'payments'
        ])
        ->where('customer_id', $request->user()->id)
        ->latest()
        ->paginate(15);

        $subscriptions->getCollection()->transform(fn($s) => $this->formatSubscription($s));

        return response()->json([
            'status' => 'success',
            'data'   => $subscriptions
        ]);
    }

    /**
     * Show a specific subscription detail for the customer.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $subscription = Subscription::with([
            'package.listing.category',
            'package.listing.images',
            'room',
            'invoices',
            'payments',
            'booking'
        ])
        ->where('customer_id', $request->user()->id)
        ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $this->formatSubscription($subscription, true)
        ]);
    }

    /**
     * Format a subscription for API response.
     */
    private function formatSubscription(Subscription $s, bool $detailed = false): array
    {
        $listing = $s->package?->listing;
        
        $base = [
            'id'             => $s->id,
            'status'         => $s->status,
            'starts_at'      => $s->starts_at?->toDateString(),
            'expires_at'     => $s->expires_at?->toDateString(),
            'auto_renew'     => (bool) $s->auto_renew,
            'is_active'      => $s->isActive(),
            'listing'        => $listing ? [
                'id'       => $listing->id,
                'title'    => $listing->title,
                'category' => $listing->category->name ?? null,
                'address'  => $listing->address,
                'image'    => $listing->images->first()
                    ? asset('storage/' . $listing->images->first()->image_path)
                    : null,
            ] : null,
            'plan' => $s->package ? [
                'id'       => $s->package->id,
                'name'     => $s->package->name,
                'duration' => $s->package->duration_label,
            ] : null,
            'room' => $s->room ? [
                'room_number' => $s->room->room_number,
                'room_type'   => $s->room->room_type,
            ] : null,
            'created_at' => $s->created_at,
        ];

        if ($detailed) {
            $base['booking_id'] = $s->booking_id;
            
            $base['invoices'] = $s->invoices->map(fn($inv) => [
                'id'             => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'amount'         => (float) $inv->amount,
                'total'          => (float) $inv->total,
                'status'         => $inv->status,
                'due_date'       => $inv->due_date,
            ]);
            
            $base['payments'] = $s->payments->map(fn($p) => [
                'id'         => $p->id,
                'gateway'    => $p->gateway,
                'amount'     => (float) $p->amount,
                'status'     => $p->status,
                'paid_at'    => $p->paid_at,
            ]);
        }

        return $base;
    }

    /**
     * Customer requests to cancel a subscription/move-out.
     */
    public function requestCancellation(Request $request, string $id): JsonResponse
    {
        $subscription = Subscription::where('customer_id', $request->user()->id)->findOrFail($id);

        if ($subscription->leave_status == 'requested') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cancellation request has already been submitted.'
            ], 422);
        }

        if($subscription->leave_status == 'rejected') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cancellation request has already been rejected.'
            ], 422);
        }

        if($subscription->leave_status == 'approved') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cancellation request has already been approved.'
            ], 422);
        }

        $subscription->update([
            'leave_status' => 'requested',
            'auto_renew' => false, 
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Cancellation request submitted successfully. Waiting for partner approval.',
            'data'    => $subscription
        ]);
    }

    /**
     * Get the customer's cancellation request history.
     */
    public function cancellationHistory(Request $request): JsonResponse
    {
        $cancellations = Subscription::with([
            'package.listing.category',
            'package.listing.images',
            'room',
            'booking'
        ])
        ->where('customer_id', $request->user()->id)
        ->whereIn('leave_status', ['requested', 'approved', 'rejected'])
        ->latest('updated_at')
        ->paginate(15);

        $cancellations->getCollection()->transform(fn($s) => $this->formatSubscription($s, false));

        return response()->json([
            'status' => 'success',
            'data'   => $cancellations
        ]);
    }
    /**
     * Renew an active or expired subscription online via TPI Pay.
     */
    public function renewOnline(Request $request, string $id): JsonResponse
    {
        $subscription = Subscription::with(['booking.package.listing.partner.tpiMerchant', 'customer', 'package'])
            ->where('customer_id', $request->user()->id)
            ->findOrFail($id);
            
        if ($subscription->status !== 'expired' && $subscription->status !== 'active') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Only active or expired subscriptions can be renewed.'
            ], 422);
        }

        $merchant = $subscription->booking->package->listing->partner->tpiMerchant ?? null;
        $merchantId = $merchant->tpi_merchant_id ?? null;
        
        if (!$merchantId) {
            $merchantId = \App\Models\SystemSetting::getSetting('default_merchant_id');
        }

        if (!$merchantId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Online payments are currently unavailable (No Merchant ID).'
            ], 503);
        }

        try {
            $tpiService = app(\App\Services\TpiPaymentService::class);
            $amount = $subscription->package->price;
            $customerName = $subscription->customer->name ?? 'Customer';
            $customerEmail = $subscription->customer->email ?? 'noreply@feetrack.com';
            $customerPhone = $subscription->customer->mobile ?? '9999999999';

            $orderId = 'SUB-' . substr($subscription->id, 0, 8) . '-' . time();

            $resp = $tpiService->createPayment([
                'merchantId' => $merchantId,
                'orderId' => $orderId,
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
            $accessKey = $resp['accessKey'] ?? null;

            if ($paymentId && $paymentLink) {
                \App\Models\Payment::create([
                    'subscription_id' => $subscription->id,
                    'gateway'         => 'tpipay',
                    'gateway_ref'     => $paymentId,
                    'amount'          => $amount,
                    'status'          => 'pending'
                ]);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'Payment link generated successfully.',
                    'data'    => [
                        'order_id'     => $orderId,
                        'accessKey'    => $accessKey,
                        'payment_link' => $paymentLink,
                        'amount'       => (float)$amount,
                        'currency'     => 'INR',
                        'gateway'      => 'tpipay'
                    ]
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to generate payment link. Invalid API response.'
            ], 500);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Customer Subscription Renew Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Payment Gateway Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
