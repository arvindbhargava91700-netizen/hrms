<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    /**
     * Get reserve history for the partner.
     */
    public function reserveHistory(Request $request): JsonResponse
    {
        $partnerId = $request->user()->id;

        $reserveHistories = \App\Models\ReserveHistory::with([
            'customer:id,name,mobile,email,profile_image', 
            'booking.package.listing'
        ])
            ->where('partner_id', $partnerId)
            ->latest()
            ->paginate(15);

        $formatted = $reserveHistories->map(function ($rh) {
            $booking = $rh->booking;
            return [
                'id' => $rh->id,
                'amount' => (float) $rh->amount,
                'status' => $rh->status,
                'created_at' => $rh->created_at,
                'booking_id' => $rh->booking_id,
                'customer' => $rh->customer ? [
                    'id' => $rh->customer->id,
                    'name' => $rh->customer->name,
                    'mobile' => $rh->customer->mobile,
                    'email' => $rh->customer->email,
                    'profile_image' => $rh->customer->profile_image ? asset('storage/' . $rh->customer->profile_image) : null,
                ] : null,
                'listing' => $booking?->package?->listing ? [
                    'id' => $booking->package->listing->id,
                    'title' => $booking->package->listing->title,
                ] : null,
                'breakdown' => $booking ? [
                    'total' => (float) $booking->final_amount,
                    'rent' => (float) ($booking->final_amount - $booking->security_deposit),
                    'deposit' => (float) $booking->security_deposit,
                ] : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'current_page' => $reserveHistories->currentPage(),
                'last_page' => $reserveHistories->lastPage(),
                'per_page' => $reserveHistories->perPage(),
                'total' => $reserveHistories->total(),
                'history' => $formatted
            ]
        ]);
    }
}
