<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\Subscription;
use Livewire\WithPagination;

class PaymentHistoryReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $startDate = '';
    public $endDate = '';

    public function updated($propertyName)
    {
        $this->resetPage();
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
        return $this->getBaseQuery()->with(['booking.customer', 'subscription.customer'])->latest('created_at')->paginate(20);
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['booking.customer', 'subscription.customer'])->latest('created_at')->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=payment_history_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $callback = function() use($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Transaction ID', 'Customer', 'Gross Amount', 'Platform Fee', 'Net Amount', 'Payment Method', 'Status', 'Date']);
            
            foreach ($data as $row) {
                $customerName = '-';
                if ($row->booking && $row->booking->customer) {
                    $customerName = $row->booking->customer->name;
                } elseif ($row->subscription && $row->subscription->customer) {
                    $customerName = $row->subscription->customer->name;
                }

                $netAmt = $this->getNetAmount($row->booking_id ?? $row->subscription_id);
                $platFee = $row->amount - $netAmt;

                fputcsv($file, [
                    $row->id,
                    $row->receipt_number ?? $row->gateway_ref ?? '-',
                    $customerName,
                    $row->amount,
                    $platFee,
                    $netAmt,
                    $row->gateway ?? '-',
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
        return view('livewire.partner.reports.payment-history-report', [
            'reportData' => $this->reportData,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Payment History Report',
            'pageSubtitle' => 'View all received payments',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
