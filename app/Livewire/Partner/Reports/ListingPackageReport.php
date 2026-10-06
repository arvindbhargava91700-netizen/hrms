<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\Package;
use Livewire\WithPagination;

class ListingPackageReport extends Component
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

        $listingsQuery = \App\Models\Listing::query();
        if (!$isSuperAdmin) {
            $listingsQuery->where('partner_id', $partnerId);
        }
        $partnerListings = $listingsQuery->pluck('id');

        $query = Package::with(['listing', 'room'])->whereIn('listing_id', $partnerListings)->withCount('subscriptions');
        
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('room_type', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        if ($this->type !== '') {
            $query->where('type', $this->type);
        }


        return $query;
    }

    public function getStatsProperty()
    {
        $baseQuery = clone $this->getBaseQuery();
        
        return [
            'total_packages' => (clone $baseQuery)->count(),
            'total_subscriptions' => (clone $baseQuery)->get()->sum('subscriptions_count'),
        ];
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
            "Content-Disposition" => "attachment; filename=listing_package_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Package Name', 'Listing', 'Room Type', 'Occupancy', 'Duration', 'Price', 'Type', 'Subscriptions', 'Date'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    substr($row->id, 0, 8),
                    $row->name,
                    optional($row->listing)->title ?? '-',
                    $row->room_type ?? '-',
                    $row->occupancy_type ?? '-',
                    $row->duration_days ?? '-',
                    $row->price,
                    $row->type,
                    $row->subscriptions_count,
                    $row->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.listing-package-report', [
            'reportData' => $this->reportData,
            'stats'      => $this->stats,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Listing Packages Report',
            'pageSubtitle' => 'View performance of your listing packages',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
