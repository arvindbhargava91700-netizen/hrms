<?php

namespace App\Livewire\Partner;

use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Wallet extends Component
{
    use HasPartnerWorkspaceScope, WithPagination;

    public $startDate;
    public $endDate;
    public $statusFilter = '';

    public $amount;
    public $payment_method;
    public $notes;

    public $recharge_amount;
    public $transaction_id;
    public string $recharge_notes = '';

    public ?string $viewingBookingId = null;

    protected string $paginationTheme = 'bootstrap';

    public function viewBooking(string $bookingId): void
    {
        $this->viewingBookingId = $bookingId;
    }

    public function closeBookingView(): void
    {
        $this->viewingBookingId = null;
    }

    public $transactionTypeFilter = '';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('wallet_viewany') || auth()->user()->canAccess('wallet_viewown'), 403, 'Unauthorized access.');
    }

    public function initiateRecharge()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('wallet_create'), 403, 'Unauthorized access.');
        $partnerId = $this->requirePartnerIdForWrite();
        $partnerUser = User::findOrFail($partnerId);

        $this->validate([
            'recharge_amount' => 'required|numeric|min:1'
        ]);

        $transactionId = 'RCH-' . time() . '-' . rand(1000, 9999);

        $rechargeReq = \App\Models\WalletRechargeRequest::create([
            'user_id' => $partnerId,
            'amount' => $this->recharge_amount,
            'transaction_id' => $transactionId,
            'status' => 'pending'
        ]);

        $merchantId = $partnerUser->getResolvedTpiMerchantId();

        if (!$merchantId) {
            session()->flash('error', 'Payment gateway is not configured.');
            $this->dispatch('close-modal');
            return;
        }

        try {
            $tpiService = app(\App\Services\TpiPaymentService::class);
            $resp = $tpiService->createPayment([
                'merchantId' => $merchantId,
                'orderId' => $transactionId,
                'amount' => (float)$this->recharge_amount,
                'customerName' => $partnerUser->name,
                'email' => $partnerUser->email,
                'phone' => $partnerUser->mobile ?? '9999999999',
                'surl' => route('partner.recharge.callback', ['recharge' => $rechargeReq->id]),
                'furl' => route('partner.recharge.callback', ['recharge' => $rechargeReq->id]),
                'productInfo' => 'Wallet Recharge',
                'requestFlow' => 'CUSTOM_CHECKOUT'
            ]);

            if (isset($resp['paymentId'])) {
                $rechargeReq->update(['transaction_id' => $resp['paymentId']]);
            }

            if (isset($resp['paymentLink'])) {
                return redirect()->away($resp['paymentLink']);
            }

            session()->flash('error', 'Failed to generate payment link.');
        } catch (\Exception $e) {
            session()->flash('error', 'Payment gateway error: ' . $e->getMessage());
        }

        $this->dispatch('close-modal');
    }

    public function requestWithdrawal()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('wallet_create'), 403, 'Unauthorized access.');

        $partnerId = $this->requirePartnerIdForWrite();
        $partnerUser = User::findOrFail($partnerId);

        // Prevent multiple pending withdrawals
        if (WithdrawalRequest::where('user_id', $partnerId)->where('status', 'pending')->exists()) {
            session()->flash('error', 'You already have a pending withdrawal request. Please wait for it to be processed.');
            $this->dispatch('close-modal');
            return;
        }

        $this->validate([
            'amount' => 'required|numeric|min:1|max:' . $partnerUser->wallet_balance,
            'payment_method' => 'required|string|max:255',
        ]);

        WithdrawalRequest::create([
            'user_id' => $partnerId,
            'amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'status' => 'pending',
        ]);

        session()->flash('success', 'Withdrawal requested successfully. The amount will be deducted upon approval.');
        $this->reset(['amount', 'payment_method', 'notes']);
        $this->dispatch('close-modal');
    }

    protected function buildWithdrawalQuery()
    {
        $query = $this->scopePartnerRecords(WithdrawalRequest::query(), 'user_id');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        
        return $query;
    }

    public function exportWithdrawals()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('wallet_viewany') || auth()->user()->canAccess('wallet_viewown'), 403, 'Unauthorized access.');
        $withdrawals = $this->buildWithdrawalQuery()->latest()->get();

        $csvFileName = 'my-withdrawals-' . now()->format('Ymd_His') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($withdrawals) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Requested On', 'Amount', 'Payment Method', 'Status', 'Paid On', 'Notes']);

            foreach ($withdrawals as $w) {
                fputcsv($file, [
                    $w->id,
                    $w->created_at->format('Y-m-d H:i:s'),
                    $w->amount,
                    $w->payment_method,
                    $w->status,
                    $w->paid_at ? $w->paid_at->format('Y-m-d') : 'N/A',
                    $w->notes
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $partnerId = auth()->user()->isSuperAdmin()
            ? $this->requirePartnerIdForWrite()
            : $this->getPartnerId();
        $user = User::findOrFail($partnerId);
        
        $transactionQuery = $this->scopePartnerRecords(WalletTransaction::query(), 'user_id');
        
        if ($this->transactionTypeFilter === 'withdrawal') {
            $transactionQuery->where('reference_type', 'withdrawal');
        }

        $transactions = $transactionQuery->latest()->paginate(10, ['*'], 'transactionsPage');
            
        $withdrawals = $this->buildWithdrawalQuery()->latest()->paginate(10, ['*'], 'withdrawalsPage');

        $rechargeRequests = $this->scopePartnerRecords(\App\Models\WalletRechargeRequest::query(), 'user_id')
            ->latest()
            ->paginate(5, ['*'], 'rechargesPage');

        $reserveHistories = $this->scopePartnerRecords(\App\Models\ReserveHistory::with('customer', 'booking.package.listing', 'booking.room', 'booking.shift', 'booking.invoices', 'booking.payments'))
            ->latest()
            ->paginate(10, ['*'], 'reservesPage');
            
        $viewingBooking = $this->viewingBookingId
            ? \App\Models\Booking::with(['customer', 'package.listing', 'room', 'shift', 'invoices', 'payments'])
                ->whereHas('package.listing', fn ($query) => $this->scopePartnerRecords($query))
                ->find($this->viewingBookingId)
            : null;

        return view('livewire.partner.wallet', compact('user', 'transactions', 'withdrawals', 'rechargeRequests', 'reserveHistories', 'viewingBooking'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Wallet',
                'pageSubtitle' => 'Manage your earnings and withdrawals',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }
}
