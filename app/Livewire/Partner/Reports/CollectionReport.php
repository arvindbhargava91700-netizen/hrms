<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Subscription;
use Livewire\WithPagination;
use Carbon\Carbon;

class CollectionReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $dateFilter = 'today'; // today, yesterday, last_month, custom
    public $startDate = '';
    public $endDate = '';

    public function mount()
    {
        $this->applyDateFilter();
    }

    public function setDateFilter($filter)
    {
        $this->dateFilter = $filter;
        $this->applyDateFilter();
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->dateFilter = 'custom';
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->dateFilter = 'custom';
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    private function applyDateFilter()
    {
        switch ($this->dateFilter) {
            case 'today':
                $this->startDate = Carbon::today()->format('Y-m-d');
                $this->endDate = Carbon::today()->format('Y-m-d');
                break;
            case 'yesterday':
                $this->startDate = Carbon::yesterday()->format('Y-m-d');
                $this->endDate = Carbon::yesterday()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'custom':
                // Do not auto-set dates if custom is selected
                break;
        }
    }

    public function getBaseQuery()
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $listingsQuery = \App\Models\Listing::query();
        if (!$isSuperAdmin) {
            $listingsQuery->where('partner_id', $partnerId);
        }
        $partnerListings = $listingsQuery->pluck('id');
        $bookingIds = Booking::where(function($q) use ($partnerListings) {
            $q->whereHas('package', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            })->orWhereHas('shift', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            })->orWhereHas('room.floor', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            });
        })->pluck('id');

        $subscriptionIds = Subscription::where(function($q) use ($partnerListings) {
            $q->whereHas('package', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            })->orWhereHas('room.floor', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            });
        })->pluck('id');

        $query = Payment::whereIn('booking_id', $bookingIds)->orWhereIn('subscription_id', $subscriptionIds);
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('transaction_id', 'like', '%' . $this->search . '%')
                  ->orWhereHas('booking.customer', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('subscription.customer', function($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query;
    }

    public function getNetAmount($referenceId)
    {
        if (auth()->user()->role === 'super_admin') {
            return \App\Models\WalletTransaction::where('reference_id', $referenceId)
                ->whereIn('user_id', \App\Models\User::where('role', 'partner')->select('id'))
                ->where('type', 'credit')
                ->sum('amount');
        }

        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return \App\Models\WalletTransaction::where('reference_id', $referenceId)
            ->where('user_id', $partnerId)
            ->where('type', 'credit')
            ->value('amount') ?? 0;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['booking.customer', 'subscription.customer'])->latest()->paginate(20);
    }

    public function getSummaryProperty()
    {
        $baseQuery = $this->getBaseQuery();
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $successfulQuery = (clone $baseQuery)->whereIn('status', ['success', 'completed', 'paid']);
        $totalGross = (clone $baseQuery)->sum('amount');
        $successfulGross = $successfulQuery->sum('amount');
        $failedPending = (clone $baseQuery)->whereNotIn('status', ['success', 'completed', 'paid'])->sum('amount');

        $walletQuery = \App\Models\WalletTransaction::query();
        if ($isSuperAdmin) {
            $walletQuery->whereIn('user_id', \App\Models\User::where('role', 'partner')->select('id'));
        } else {
            $walletQuery->where('user_id', $partnerId);
        }
        $netCollections = $walletQuery->where('type', 'credit')
            ->where(function ($q) use ($successfulQuery) {
                $q->whereIn('reference_id', (clone $successfulQuery)->select('booking_id')->whereNotNull('booking_id'))
                  ->orWhereIn('reference_id', (clone $successfulQuery)->select('subscription_id')->whereNotNull('subscription_id'));
            })
            ->sum('amount');

        $platformFee = $successfulGross - $netCollections;

        return [
            'total_collections' => $totalGross,
            'successful' => $successfulGross,
            'failed_pending' => $failedPending,
            'net_collections' => $netCollections,
            'platform_fee' => $platformFee,
        ];
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['booking.customer', 'subscription.customer'])->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=collection_report_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $callback = function() use($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Transaction ID', 'Customer Name', 'Gross Amount', 'Platform Fee', 'Net Amount', 'Method', 'Status', 'Date']);
            
            foreach ($data as $row) {
                $customerName = 'N/A';
                if ($row->booking && $row->booking->customer) {
                    $customerName = $row->booking->customer->name;
                } elseif ($row->subscription && $row->subscription->customer) {
                    $customerName = $row->subscription->customer->name;
                }
                
                $netAmount = $this->getNetAmount($row->booking_id ?? $row->subscription_id);
                    
                $platformFee = $row->amount - $netAmount;

                fputcsv($file, [
                    $row->id,
                    $row->receipt_number ?? $row->gateway_ref,
                    $customerName,
                    $row->amount,
                    $platformFee,
                    $netAmount,
                    $row->gateway,
                    $row->status,
                    $row->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.collection-report', [
            'reportData' => $this->reportData,
            'summary' => $this->summary,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Collection Report',
            'pageSubtitle' => 'View total collected payments over specific periods',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
