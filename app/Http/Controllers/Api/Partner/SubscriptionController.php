<?php

namespace App\Http\Controllers\Api\Partner;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * Partner approves a customer's cancellation request.
     */
    public function approveCancellation(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'refund_method' => 'required|in:wallet,cash',
        ]);

        $subscription = Subscription::with(['booking.room'])->findOrFail($id);

        if ($subscription->leave_status !== 'requested') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cancellation request not found or already processed.'
            ], 422);
        }

        DB::transaction(function () use ($subscription, $request) {
            $subscription->update([
                'leave_status' => 'approved',
                'status' => 'cancelled', // Or expired/completed
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
                    // Refunding the FULL amount (First month + Deposit) as recorded in reserve history
                    $refundAmt = (float) $reserve->amount;

                    if ($request->refund_method === 'wallet') {
                        $partner = User::find($booking->package?->listing?->partner_id);
                        if ($partner) {
                            $partner->decrement('wallet_balance', $refundAmt);
                            
                            WalletTransaction::create([
                                'user_id' => $partner->id,
                                'amount' => -$refundAmt,
                                'type' => 'debit',
                                'description' => 'Reserve amount refunded to customer (Wallet)',
                                'reference_type' => 'booking_cancellation',
                                'reference_id' => $booking->id,
                            ]);
                        }

                        $customer = User::find($booking->customer_id);
                        if ($customer) {
                            $customer->increment('wallet_balance', $refundAmt);

                            WalletTransaction::create([
                                'user_id' => $customer->id,
                                'amount' => $refundAmt,
                                'type' => 'credit',
                                'description' => 'Reserve amount refunded to main wallet',
                                'reference_type' => 'booking_cancellation',
                                'reference_id' => $booking->id,
                            ]);
                        }
                        
                        $reserve->update(['status' => 'refunded_wallet']);
                    } else if ($request->refund_method === 'cash') {
                        // Partner handed cash to customer, so digital wallets are unaffected
                        $reserve->update(['status' => 'refunded_cash']);
                    }
                }
            }
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Cancellation request approved successfully.',
        ]);
    }

    /**
     * Partner rejects a customer's cancellation request.
     */
    public function rejectCancellation(Request $request, string $id): JsonResponse
    {
        $subscription = Subscription::findOrFail($id);

        if ($subscription->leave_status !== 'requested') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Cancellation request not found or already processed.'
            ], 422);
        }

        $subscription->update([
            'leave_status' => null, // Reset status
            'auto_renew' => true, // Re-enable auto-renew if it was disabled
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Cancellation request rejected successfully.',
        ]);
    }
}
