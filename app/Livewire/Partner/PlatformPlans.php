<?php

namespace App\Livewire\Partner;

use App\Models\PartnerPackage;
use App\Models\PartnerSubscription;
use App\Models\WalletTransaction;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PlatformPlans extends Component
{
    use HasPartnerWorkspaceScope;

    public $viewPackage = null;
    public $useWallet = true;

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('platform_plan_viewany'), 403);
    }

    public function viewPackageDetails($id)
    {
        $this->viewPackage = PartnerPackage::with('systemModules')->find($id);
        $this->dispatch('show-package-modal');
    }

    public function processSubscription($packageId)
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('platform_plan_create'), 403);
        $package = PartnerPackage::where('is_active', true)->findOrFail($packageId);
        $partner = \App\Models\User::findOrFail($this->requirePartnerIdForWrite());

        $activeSub = PartnerSubscription::where('partner_id', $partner->id)
            ->where('status', 'active')
            ->where('partner_package_id', $package->id)
            ->first();

        if ($activeSub && ($package->duration_days == 0 || ($activeSub->expires_at && !$activeSub->expires_at->endOfDay()->isPast()))) {
            session()->flash('error', 'Your current plan is still active and has not expired yet.');
            return;
        }

        $walletBalance = (float) $partner->wallet_balance;
        $payable = (float) $package->price;
        $walletUsed = $this->useWallet ? min($payable, $walletBalance) : 0;
        $onlinePayable = $payable - $walletUsed;

        if ($onlinePayable <= 0) {
            // Fully paid by wallet or free
            DB::transaction(function () use ($package, $partner, $walletUsed) {
                if ($walletUsed > 0) {
                    $partner->decrement('wallet_balance', $walletUsed);
                    WalletTransaction::create([
                        'user_id' => $partner->id,
                        'amount' => $walletUsed,
                        'type' => 'debit',
                        'description' => 'Purchased Platform Package: ' . $package->name,
                        'reference_type' => 'platform_subscription',
                    ]);
                }

                PartnerSubscription::where('partner_id', $partner->id)->where('status', 'active')->update(['status' => 'cancelled']);
                $expiresAt = $package->duration_days > 0 ? now()->addDays($package->duration_days - 1)->endOfDay() : null;
                $sub = PartnerSubscription::create([
                    'partner_id' => $partner->id,
                    'partner_package_id' => $package->id,
                    'starts_at' => now(),
                    'expires_at' => $expiresAt,
                    'status' => 'active',
                ]);
                
                \App\Models\TransactionHistory::create([
                    'transaction_id' => (string) \Illuminate\Support\Str::uuid(),
                    'user_id' => $partner->id,
                    'type' => 'subscription',
                    'reference_id' => $sub->id,
                    'total_amount' => $package->price,
                    'wallet_deducted' => $walletUsed,
                    'online_payable' => 0,
                    'platform_fee' => 0,
                    'net_amount' => 0,
                    'status' => 'completed',
                    'description' => 'Platform Subscription paid fully via Wallet'
                ]);
            });

            \App\Services\PackageService::enforceLimits($partner->id);
            session()->flash('success', 'Successfully purchased and activated ' . $package->name . '!');
        } else {
            // Partial wallet, remaining online
            $expiresAt = $package->duration_days > 0 ? now()->addDays($package->duration_days - 1)->endOfDay() : null;
            $subscription = PartnerSubscription::create([
                'partner_id' => $partner->id,
                'partner_package_id' => $package->id,
                'status' => 'pending',
                'starts_at' => now(),
                'expires_at' => $expiresAt,
            ]);
            
            $txn = \App\Models\TransactionHistory::create([
                'transaction_id' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $partner->id,
                'type' => 'subscription',
                'reference_id' => $subscription->id,
                'total_amount' => $package->price,
                'wallet_deducted' => $walletUsed,
                'online_payable' => $onlinePayable,
                'platform_fee' => 0,
                'net_amount' => $onlinePayable,
                'status' => 'pending',
                'description' => 'Subscription: ' . $package->name
            ]);

            $merchantId = $partner->getResolvedTpiMerchantId();

            try {
                $tpiService = app(\App\Services\TpiPaymentService::class);
                $resp = $tpiService->createPayment([
                    'merchantId' => $merchantId,
                    'orderId' => $txn->transaction_id,
                    'amount' => (float)$onlinePayable,
                    'customerName' => $partner->name,
                    'customerEmail' => $partner->email ?? 'noreply@feetrack.in',
                    'customerPhone' => $partner->mobile ?? '9999999999',
                    'description' => 'Platform Subscription: ' . $package->name,
                    'surl' => route('partner.subscription.callback', ['subscription' => $subscription->id, 'txn' => $txn->id]),
                    'furl' => route('partner.subscription.callback', ['subscription' => $subscription->id, 'txn' => $txn->id]),
                ]);

                if ($resp && isset($resp['paymentLink'])) {
                    if (isset($resp['paymentId'])) {
                        $txn->update(['transaction_id' => $resp['paymentId']]);
                    }
                    return redirect()->away($resp['paymentLink']);
                }
                
                $subscription->delete();
                $txn->delete();
                session()->flash('error', 'Could not initiate payment with gateway.');
            } catch (\Exception $e) {
                $subscription->delete();
                $txn->delete();
                session()->flash('error', 'Payment gateway error: ' . $e->getMessage());
            }
        }
    }

    public function cancelPendingSubscription()
    {
        $partnerId = $this->requirePartnerIdForWrite();
        $pending = PartnerSubscription::where('partner_id', $partnerId)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            \App\Models\TransactionHistory::where('reference_id', $pending->id)
                ->where('type', 'subscription')
                ->where('status', 'pending')
                ->delete();
                
            $pending->delete();
            session()->flash('success', 'Pending subscription request cancelled.');
        }
    }

    public function render()
    {
        $activeSubscription = null;
        $pendingSubscription = null;
        $partnerId = auth()->user()->isSuperAdmin()
            ? $this->selectedWorkspacePartnerId()
            : $this->getPartnerId();
        if (filled($partnerId)) {
            $activeSubscription = PartnerSubscription::with('package')
                ->where('partner_id', $partnerId)
                ->where('status', 'active')
                ->first();
            
            $pendingSubscription = PartnerSubscription::with('package')
                ->where('partner_id', $partnerId)
                ->where('status', 'pending')
                ->first();
        }

        $packages = PartnerPackage::with('systemModules')->where('is_active', true)->orderBy('price')->get();

        return view('livewire.partner.platform-plans', compact('activeSubscription', 'pendingSubscription', 'packages'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Platform Plans',
                'pageSubtitle' => 'Manage your Feetrack subscription',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
