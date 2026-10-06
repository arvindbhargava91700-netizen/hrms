<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\Booking;
use Livewire\WithPagination;

class BookingReport extends Component
{
    use WithPagination;

    public $search = '';
    public $startDate = '';
    public $endDate = '';
    public $status = '';

    protected string $paginationTheme = 'bootstrap';
    
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
        
        $query = Booking::where(function($q) use ($partnerListings) {
            $q->whereHas('package', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            })->orWhereHas('shift', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            })->orWhereHas('room.floor', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            });
        });
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('booking_id', 'like', '%' . $this->search . '%')
                  ->orWhereHas('customer', function($q) {
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
        
        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['customer', 'package.listing', 'shift.listing', 'room.floor.listing'])->latest()->paginate(20);
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['customer', 'package.listing', 'shift.listing', 'room.floor.listing'])->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=booking_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Booking ID', 'Customer Name', 'Customer Mobile', 'Listing Title', 'Room', 'Beds', 'Occupancy', 'Package', 'Shift', 'Payment Method', 'Discount', 'Final Amount', 'Net Earning', 'Status', 'Date'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->booking_id ?? substr($row->id, 0, 8),
                    optional($row->customer)->name ?? '-',
                    optional($row->customer)->mobile ?? '-',
                    optional($row->listing)->title ?? '-',
                    optional($row->room)->room_number ?? '-',
                    $row->beds_booked ?? '-',
                    $row->occupancy_type ?? '-',
                    optional($row->package)->name ?? '-',
                    optional($row->shift)->name ?? '-',
                    $row->payment_method ?? '-',
                    $row->discount_amount ?? 0,
                    $row->amount ?? $row->final_amount,
                    $row->net_earn,
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
        return view('livewire.partner.reports.booking-report', [
            'reportData' => $this->reportData,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Booking Report',
            'pageSubtitle' => 'View booking performance',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
