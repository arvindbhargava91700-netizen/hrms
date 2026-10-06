<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\Listing;
use Livewire\WithPagination;

class ListingReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $startDate = '';
    public $endDate = '';
    public $status = '';
    public $category_id = '';

    public function updated($propertyName)
    {
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = Listing::query()->withCount(['subscriptions', 'visitBookings', 'reviews']);
        if (!$isSuperAdmin) {
            $query->where('partner_id', $partnerId);
        }
        
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%');
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

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        return $query;
    }

    public function getStatsProperty()
    {
        $baseQuery = $this->getBaseQuery();
        
        return [
            'total_listings' => (clone $baseQuery)->count(),
            'active_listings' => (clone $baseQuery)->where('status', 'approved')->count(),
            'total_bookings' => (clone $baseQuery)->get()->sum('subscriptions_count'),
            'total_visits' => (clone $baseQuery)->get()->sum('visit_bookings_count'),
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
            "Content-Disposition" => "attachment; filename=listing_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Title', 'Category', 'Gender', 'City', 'Phone', 'Bookings', 'Visits', 'Reviews', 'Status', 'Created At'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    substr($row->id, 0, 8),
                    $row->title,
                    optional($row->category)->name ?? '-',
                    $row->gender_type ?? '-',
                    $row->city ?? '-',
                    $row->phone ?? '-',
                    $row->subscriptions_count,
                    $row->visit_bookings_count,
                    $row->reviews_count,
                    ucfirst($row->status),
                    $row->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.listing-report', [
            'reportData' => $this->reportData,
            'stats'      => $this->stats,
            'categories' => \App\Models\Category::orderBy('name')->get(),
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Listing Report',
            'pageSubtitle' => 'View your listings performance',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
