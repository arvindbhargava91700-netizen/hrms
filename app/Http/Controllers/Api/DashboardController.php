<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Get recent bookings for the customer.
     */
    public function recentBookings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get 5 recent bookings
        $recentBookings = \App\Models\Booking::with([
            'package.listing.category',
            'package.listing.images',
            'room',
            'invoices',
            'coupon'
        ])
        ->where('customer_id', $user->id)
        ->latest()
        ->take(5)
        ->get();

        // Format bookings similar to BookingController
        $formattedBookings = $recentBookings->map(function ($b) {
            $listing = $b->package?->listing;
            return [
                'booking_id'      => $b->id,
                'status'          => $b->status,
                'payment_method'  => $b->payment_method,
                'created_at'      => $b->created_at,
                'listing'         => $listing ? [
                    'id'       => $listing->id,
                    'title'    => $listing->title,
                    'category' => $listing->category->name ?? null,
                    'image'    => $listing->images->first()
                        ? asset('storage/' . $listing->images->first()->image_path)
                        : null,
                ] : null,
                'plan'   => $b->package ? [
                    'id'            => $b->package->id,
                    'name'          => $b->package->name,
                    'duration'      => $b->package->duration_label,
                    'price'         => (float) $b->package->price,
                ] : null,
                'billing' => [
                    'final_amount'    => (float) $b->final_amount,
                    'reserve_amount'  => $b->security_deposit > 0 ? (float) $b->final_amount : 0,
                ],
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $formattedBookings
        ]);
    }

    /**
     * Get recent transactions (payments) for the customer.
     */
    public function recentTransactions(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get 5 recent payments (transactions)
        $recentPayments = \App\Models\Payment::with(['invoice', 'subscription.package.listing'])
            ->whereHas('subscription', fn($q) => $q->where('customer_id', $user->id))
            ->latest()
            ->take(5)
            ->get();

        $formattedPayments = $recentPayments->map(function ($p) {
            return [
                'id'         => $p->id,
                'gateway'    => $p->gateway,
                'amount'     => (float) $p->amount,
                'status'     => $p->status,
                'paid_at'    => $p->paid_at,
                'listing_name' => $p->subscription?->package?->listing?->title ?? 'Unknown',
                'plan_name'  => $p->subscription?->package?->name ?? 'Unknown',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => $formattedPayments
        ]);
    }
}
