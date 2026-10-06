<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WalletController extends Controller
{
    /**
     * Get reserve history (security deposits) for the customer.
     */
    public function reserveHistory(Request $request): JsonResponse
    {
        $customerId = $request->user()->id;

        $reserveHistories = \App\Models\ReserveHistory::with([
            'partner:id,name,mobile,email', 
            'booking.package.listing'
        ])
            ->where('customer_id', $customerId)
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
                'partner' => $rh->partner ? [
                    'name' => $rh->partner->name,
                    'mobile' => $rh->partner->mobile,
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
    /**
     * Get wallet transaction history.
     */
    public function history(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $transactions = \App\Models\WalletTransaction::where('user_id', $userId)
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    /**
     * Get wallet summary (balance, total credit, total debit).
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $userId = $user->id;

        $totalCredit = \App\Models\WalletTransaction::where('user_id', $userId)
            ->where('type', 'credit')
            ->sum('amount');

        $totalDebit = \App\Models\WalletTransaction::where('user_id', $userId)
            ->where('type', 'debit')
            ->sum('amount');

        return response()->json([
            'status' => 'success',
            'data' => [
                'wallet_balance' => (float) $user->wallet_balance,
                'total_credit' => (float) $totalCredit,
                'total_debit' => (float) $totalDebit,
            ]
        ]);
    }



    /**
     * Initiate an online wallet recharge via TPI Pay
     */
    public function rechargeOnline(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = $request->user();

        $rechargeRequest = \App\Models\WalletRechargeRequest::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'transaction_id' => 'pending', // Temporary
            'status' => 'pending',
            'notes' => 'Online Recharge'
        ]);

        $callbackUrl = route('payment.tpi.success', ['recharge' => $rechargeRequest->id]);

        $admin = \App\Models\User::whereIn('role', ['super_admin'])->first();
        $merchantId = $admin ? ($admin->tpiMerchant->tpi_merchant_id ?? null) : null;
        if (!$merchantId) {
            $merchantId = \App\Models\SystemSetting::getSetting('default_merchant_id');
        }
        if (!$merchantId) {
            return response()->json(['status' => 'error', 'message' => 'Admin TPI Pay gateway is not setup.'], 422);
        }

        $paymentService = app(\App\Services\TpiPaymentService::class);
        $paymentResponse = $paymentService->createPayment([
            'merchantId' => $merchantId,
            'orderId' => 'REC-' . $rechargeRequest->id . '-' . time(),
            'amount' => $request->amount,
            'customerName' => $user->name,
            'email' => $user->email,
            'phone' => $user->mobile ?? '9999999999',
            'surl' => $callbackUrl,
            'furl' => $callbackUrl,
            'productInfo' => 'Wallet Recharge',
            'requestFlow' => 'CUSTOM_CHECKOUT'
        ]);

        $paymentId = $paymentResponse['paymentId'] ?? null;
        $paymentLink = $paymentResponse['paymentLink'] ?? null;

        if ($paymentId && $paymentLink) {
            // Update the transaction_id with the actual paymentId from TPI Pay
            $rechargeRequest->update(['transaction_id' => $paymentId]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment initiated successfully.',
                'payment_url' => $paymentLink
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to initiate payment.',
            'error' => $paymentResponse['message'] ?? 'Unknown error'
        ], 500);
    }

    /**
     * Request a wallet withdrawal
     */
    public function requestWithdrawal(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $user = $request->user();

        if ($user->wallet_balance < $request->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient wallet balance.'
            ], 422);
        }

        // Deduct from wallet balance immediately (hold)
        $user->decrement('wallet_balance', $request->amount);

        // Record a transaction for the withdrawal
        \App\Models\WalletTransaction::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'type' => 'debit',
            'description' => 'Withdrawal Request',
            'reference_type' => 'withdrawal',
            'reference_id' => null // will update after request creation if needed
        ]);

        $withdrawalRequest = \App\Models\WithdrawalRequest::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Withdrawal request submitted successfully.',
            'data' => $withdrawalRequest
        ]);
    }



    /**
     * Get withdrawal requests history
    */
    public function withdrawalHistory(Request $request): JsonResponse
    {
        $withdrawals = \App\Models\WithdrawalRequest::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $withdrawals
        ]);
    }
}
