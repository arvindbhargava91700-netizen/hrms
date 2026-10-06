<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Payment;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    /**
     * Get a paginated list of transactions (payments) for the customer.
     */
    public function listTransactions(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Payment::with(['invoice', 'booking.package.listing', 'subscription.package.listing'])
            ->where(function ($q) use ($user) {
                $q->whereHas('booking', fn($sq) => $sq->where('customer_id', $user->id))
                  ->orWhereHas('subscription', fn($sq) => $sq->where('customer_id', $user->id));
            });

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('gateway') && $request->gateway) {
            $query->where('gateway', $request->gateway);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);

        // Format to provide clear listing/plan names for the frontend
        $transactions->getCollection()->transform(function ($p) {
            $listing = $p->booking?->package?->listing ?? $p->subscription?->package?->listing;
            $package = $p->booking?->package ?? $p->subscription?->package;
            
            return [
                'id'           => $p->id,
                'gateway_ref'  => $p->gateway_ref,
                'gateway'      => $p->gateway,
                'amount'       => (float) $p->amount,
                'status'       => $p->status,
                'paid_at'      => $p->paid_at,
                'created_at'   => $p->created_at,
                'listing_name' => $listing->title ?? 'Unknown',
                'plan_name'    => $package->name ?? 'Unknown',
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'transactions' => $transactions,
                'customer' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
                ]
            ]
        ]);
    }

    /**
     * Get transaction details.
     */
    public function transactionDetails(Request $request, $id): JsonResponse
    {
        $user = $request->user();

        $transaction = Payment::with(['invoice', 'booking.package.listing', 'subscription.package.listing'])
            ->where('id', $id)
            ->where(function ($query) use ($user) {
                $query->whereHas('booking', fn($q) => $q->where('customer_id', $user->id))
                      ->orWhereHas('subscription', fn($q) => $q->where('customer_id', $user->id));
            })
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found.'
            ], 404);
        }
        
        $listing = $transaction->booking?->package?->listing ?? $transaction->subscription?->package?->listing;
        $package = $transaction->booking?->package ?? $transaction->subscription?->package;

        $formattedTransaction = [
            'id'           => $transaction->id,
            'gateway_ref'  => $transaction->gateway_ref,
            'gateway'      => $transaction->gateway,
            'amount'       => (float) $transaction->amount,
            'status'       => $transaction->status,
            'paid_at'      => $transaction->paid_at,
            'created_at'   => $transaction->created_at,
            'invoice'      => $transaction->invoice,
            'listing'      => $listing,
            'plan'         => $package,
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'transaction' => $formattedTransaction,
                'customer' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
                ]
            ]
        ]);
    }
}
