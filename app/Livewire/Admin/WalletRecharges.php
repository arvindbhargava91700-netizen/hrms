<?php

namespace App\Livewire\Admin;

use App\Models\WalletRechargeRequest;
use Livewire\Component;
use Livewire\WithPagination;

class WalletRecharges extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $status = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updateStatus($id, $status)
    {
        $recharge = WalletRechargeRequest::findOrFail($id);
        
        if ($recharge->status !== 'pending') {
            return;
        }

        if ($status === 'approved') {
            // Add to partner wallet
            $recharge->user->increment('wallet_balance', $recharge->amount);
            
            // Create transaction record
            \App\Models\WalletTransaction::create([
                'user_id' => $recharge->user_id,
                'amount' => $recharge->amount,
                'type' => 'credit',
                'description' => 'Wallet Recharge Approved',
                'reference_type' => 'recharge',
                'reference_id' => $recharge->id,
            ]);

            $recharge->update(['approved_at' => now(), 'status' => 'approved']);
            session()->flash('success', 'Recharge approved and amount added to partner wallet.');
        } else if ($status === 'rejected') {
            $recharge->update(['status' => 'rejected']);
            session()->flash('success', 'Recharge request rejected.');
        }
    }

    private function buildQuery()
    {
        $query = WalletRechargeRequest::with('user');

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

        return $query;
    }

    public function exportCsv()
    {
        $recharges = $this->buildQuery()->latest()->get();
        $csvFileName = 'wallet-recharges-' . now()->format('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($recharges) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Partner Name', 'Partner Email', 'Amount', 'Transaction ID', 'Status', 'Requested On', 'Approved On', 'Notes']);

            foreach ($recharges as $r) {
                fputcsv($file, [
                    $r->id,
                    $r->user->name ?? 'N/A',
                    $r->user->email ?? 'N/A',
                    $r->amount,
                    $r->transaction_id,
                    $r->status,
                    $r->created_at ? $r->created_at->format('Y-m-d H:i:s') : 'N/A',
                    $r->approved_at ? $r->approved_at->format('Y-m-d H:i:s') : 'N/A',
                    $r->notes
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $recharges = $this->buildQuery()->latest()->paginate(15);
        
        return view('livewire.admin.wallet-recharges', compact('recharges'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Wallet Recharges',
                'pageSubtitle' => 'Manage partner wallet recharges',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
