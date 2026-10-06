<?php

namespace App\Livewire\Admin;

use App\Models\WithdrawalRequest;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Response;

class Withdrawals extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $status = '';
    public $startDate = '';
    public $endDate = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updateStatus($id, $status)
    {
        $withdrawal = WithdrawalRequest::findOrFail($id);
        
        // Prevent changing status if already processed
        if ($withdrawal->status !== 'pending') {
            return;
        }

        if ($status === 'approved') {
            // Check if partner still has enough balance
            if ($withdrawal->user->wallet_balance < $withdrawal->amount) {
                session()->flash('error', 'Partner has insufficient wallet balance to process this withdrawal.');
                return;
            }

            // Calculate platform fee
            $adminAmount = $this->calculatePlatformFee($withdrawal->user, $withdrawal->amount, 'online');
            $partnerAmount = $withdrawal->amount - $adminAmount;

            // Deduct the full requested balance from partner
            $withdrawal->user->decrement('wallet_balance', $withdrawal->amount);
            
            // Create transaction record for partner (full amount debit)
            \App\Models\WalletTransaction::create([
                'user_id' => $withdrawal->user_id,
                'amount' => $withdrawal->amount,
                'type' => 'debit',
                'description' => 'Withdrawal Request Approved (Fee: ₹' . number_format($adminAmount, 2) . ', Net Paid: ₹' . number_format($partnerAmount, 2) . ')',
                'reference_type' => 'withdrawal',
                'reference_id' => $withdrawal->id,
            ]);

            // Credit admin with platform fee
            $admin = \App\Models\User::where('role', 'super_admin')->first();
            if ($admin && $adminAmount > 0) {
                $admin->increment('wallet_balance', $adminAmount);
                \App\Models\WalletTransaction::create([
                    'user_id' => $admin->id,
                    'amount' => $adminAmount,
                    'type' => 'credit',
                    'description' => 'Platform fee from withdrawal #' . $withdrawal->id . ' by ' . $withdrawal->user->name,
                    'reference_type' => 'withdrawal',
                    'reference_id' => $withdrawal->id,
                ]);
            }

            \App\Models\TransactionHistory::create([
                'transaction_id' => 'WDR-' . $withdrawal->id . '-' . time(),
                'user_id' => $withdrawal->user_id,
                'type' => 'withdrawal',
                'reference_id' => $withdrawal->id,
                'total_amount' => $withdrawal->amount,
                'platform_fee' => $adminAmount,
                'net_amount' => $partnerAmount,
                'status' => 'completed',
                'description' => 'Withdrawal Request Approved & Paid',
            ]);

            $withdrawal->update(['paid_at' => now(), 'status' => 'approved']);
            session()->flash('success', 'Withdrawal approved and amount deducted from partner wallet.');
        } else if ($status === 'rejected') {
            $withdrawal->update(['status' => 'rejected']);
            session()->flash('success', 'Withdrawal request rejected.');
        }
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

    private function buildQuery()
    {
        $query = WithdrawalRequest::with('user');

        if (!empty($this->search)) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        if (!empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if (!empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query;
    }

    public function export()
    {
        $withdrawals = $this->buildQuery()->latest()->get();

        $csvFileName = 'withdrawals-' . now()->format('Ymd_His') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($withdrawals) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Partner Name', 'Partner Email', 'Partner Mobile', 'Amount', 'Payment Method', 'Status', 'Requested On', 'Paid On']);

            foreach ($withdrawals as $w) {
                fputcsv($file, [
                    $w->id,
                    $w->user->name ?? 'N/A',
                    $w->user->email ?? 'N/A',
                    $w->user->mobile ?? 'N/A',
                    $w->amount,
                    $w->payment_method,
                    $w->status,
                    $w->created_at->format('Y-m-d H:i:s'),
                    $w->paid_at ? $w->paid_at->format('Y-m-d H:i:s') : 'N/A'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $withdrawals = $this->buildQuery()->latest()->paginate(15);
        
        $stats = [
            'total_requests' => WithdrawalRequest::count(),
            'pending_amount' => WithdrawalRequest::where('status', 'pending')->sum('amount'),
            'approved_amount' => WithdrawalRequest::where('status', 'approved')->sum('amount'),
            'pending_count' => WithdrawalRequest::where('status', 'pending')->count(),
        ];

        return view('livewire.admin.withdrawals', compact('withdrawals', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Withdrawal Requests',
                'pageSubtitle' => 'Manage partner payouts',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
