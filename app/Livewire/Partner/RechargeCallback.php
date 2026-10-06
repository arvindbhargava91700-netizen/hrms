<?php

namespace App\Livewire\Partner;

use Livewire\Component;
use Illuminate\Http\Request;
use App\Models\WalletRechargeRequest;
use App\Models\WalletTransaction;
use App\Models\User;
use App\Models\PartnerSubscription;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class RechargeCallback extends Component
{
    public $status = 'verifying'; // verifying, success, failed
    public $message = 'Verifying your payment...';
    
    public function mount(Request $request)
    {
        $rechargeId = $request->input('recharge');
        $easepayid = $request->input('easepayid') ?? $request->input('paymentId');
        
        $rechargeReq = null;
        if ($rechargeId) {
            $rechargeReq = WalletRechargeRequest::find($rechargeId);
        } elseif ($easepayid) {
            $rechargeReq = WalletRechargeRequest::where('transaction_id', $easepayid)->first();
        }

        if (!$rechargeReq) {
            $this->status = 'failed';
            $this->message = 'Recharge request not found.';
            return;
        }

        if (!$easepayid) {
            $easepayid = $rechargeReq->transaction_id;
        }
        
        if (!$easepayid) {
            $this->status = 'failed';
            $this->message = 'Invalid payment callback received. No transaction ID found.';
            return;
        }

        if ($rechargeReq->status === 'approved') {
            $this->status = 'success';
            $this->message = 'Payment was already verified and funds added to your wallet.';
            return;
        }
        
        if ($rechargeReq->status !== 'pending') {
            $this->status = 'failed';
            $this->message = 'Recharge request already processed or rejected.';
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
                $rechargeReq->update([
                    'status' => 'approved',
                    'approved_at' => now()
                ]);

                $user = User::find($rechargeReq->user_id);
                if ($user) {
                    $totalAmount = $rechargeReq->amount;
                    
                    // Platform fee is NOT deducted during wallet recharge (0 fee)
                    $adminAmount = 0;
                    $partnerAmount = $totalAmount - $adminAmount;

                    $user->increment('wallet_balance', $partnerAmount);
                    
                    WalletTransaction::create([
                        'user_id'        => $user->id,
                        'type'           => 'credit',
                        'amount'         => $partnerAmount,
                        'description'    => "Recharge of ₹" . number_format($totalAmount, 2) . " (Platform fee deducted: ₹" . number_format($adminAmount, 2) . ")",
                        'reference_type' => WalletRechargeRequest::class,
                        'reference_id'   => $rechargeReq->id,
                    ]);

                    $admin = User::where('role', 'super_admin')->first();
                    if ($admin && $adminAmount > 0) {
                        $admin->increment('wallet_balance', $adminAmount);
                        
                        WalletTransaction::create([
                            'user_id'        => $admin->id,
                            'type'           => 'credit',
                            'amount'         => $adminAmount,
                            'description'    => "Admin fee of ₹" . number_format($adminAmount, 2) . " from wallet recharge of ₹" . number_format($totalAmount, 2) . " by " . $user->name,
                            'reference_type' => WalletRechargeRequest::class,
                            'reference_id'   => $rechargeReq->id,
                        ]);
                    }

                    \App\Models\TransactionHistory::create([
                        'transaction_id' => $rechargeReq->transaction_id ?? 'RCH-' . $rechargeReq->id . '-' . time(),
                        'user_id' => $user->id,
                        'type' => 'recharge',
                        'reference_id' => $rechargeReq->id,
                        'total_amount' => $totalAmount,
                        'wallet_deducted' => 0,
                        'online_payable' => $totalAmount,
                        'payment_gateway_id' => $easepayid,
                        'platform_fee' => $adminAmount,
                        'net_amount' => $partnerAmount,
                        'status' => 'completed',
                        'description' => "Online wallet recharge via gateway",
                    ]);
                }

                $this->status = 'success';
                $this->message = 'Recharge successful! The funds have been added to your wallet.';
            } else {
                $rechargeReq->update(['status' => 'rejected']);
                $this->status = 'failed';
                $this->message = 'Your payment could not be verified or was cancelled.';
            }
        } catch (\Exception $e) {
            Log::error('TPI Status Check Error for Recharge ' . $easepayid . ': ' . $e->getMessage());
            $this->status = 'failed';
            $this->message = 'An error occurred while verifying the payment. Please contact support if the amount was deducted.';
        }
    }

    private function calculatePlatformFee($partner, $totalAmount, $paymentMethod)
    {
        $isOffline = in_array(strtolower($paymentMethod), ['cash', 'offline']);
        $adminAmount = 0;

        $activeSub = PartnerSubscription::where('partner_id', $partner->id)
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
            $setting = SystemSetting::where('key', 'admin_commission_percentage')->first();
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

    public function render()
    {
        return view('livewire.partner.recharge-callback')
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Recharge Status',
                'pageSubtitle' => 'Verifying your payment',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
