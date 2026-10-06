<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\User;
use App\Models\Booking;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CustomerReport extends Component
{
    use WithPagination;

    public $search = '';
    public $startDate = '';
    public $endDate = '';

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
        
        // Customers are users who have bookings with this partner's listings
        $customerIds = Booking::where(function($q) use ($partnerListings) {
            $q->whereHas('package', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            })->orWhereHas('shift', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            })->orWhereHas('room.floor', function($q) use ($partnerListings) {
                $q->whereIn('listing_id', $partnerListings);
            });
        })->pluck('customer_id')->unique();
        
        $query = User::whereIn('id', $customerIds);
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%');
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

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->latest()->paginate(20);
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=customer_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Name', 'Email', 'Mobile', 'Status', 'Joined Date'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    substr($row->id, 0, 8),
                    $row->name,
                    $row->email,
                    $row->mobile,
                    $row->status,
                    $row->created_at->format('Y-m-d')
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.customer-report', [
            'reportData' => $this->reportData,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Customer Report',
            'pageSubtitle' => 'View customers who booked your services',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
