<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\WalletTransaction;
use Livewire\WithPagination;

class WalletHistoryReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $startDate = '';
    public $endDate = '';
    public $type = '';

    public function updated($propertyName)
    {
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = WalletTransaction::query();
        if ($isSuperAdmin) {
            $query->whereIn('user_id', \App\Models\User::where('role', 'partner')->select('id'));
        } else {
            $query->where('user_id', $partnerId);
        }
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('reference_id', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        if ($this->type !== '') {
            $query->where('type', $this->type); // credit or debit
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->latest()->paginate(20);
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=wallet_history_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Type', 'Amount', 'Description', 'Reference ID', 'Date'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    substr($row->id, 0, 8),
                    $row->type,
                    $row->amount,
                    $row->description,
                    $row->reference_id,
                    $row->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        $query = $this->getBaseQuery();
        
        $referenceIds = (clone $query)->pluck('reference_id')->filter()->unique();
        $totalAmount = \App\Models\TransactionHistory::whereIn('reference_id', $referenceIds)->sum('total_amount');
        $platformFee = \App\Models\TransactionHistory::whereIn('reference_id', $referenceIds)->sum('platform_fee');
        
        $bookingIds = (clone $query)->where('reference_type', \App\Models\Booking::class)->pluck('reference_id')->filter()->unique();
        $securityAmount = \App\Models\Booking::whereIn('id', $bookingIds)->sum('security_deposit');

        return view('livewire.partner.reports.wallet-history-report', [
            'reportData' => $this->reportData,
            'totalAmount' => $totalAmount,
            'platformFee' => $platformFee,
            'securityAmount' => $securityAmount,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Wallet History Report',
            'pageSubtitle' => 'View your wallet transactions',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
