<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\ListingReview;
use Livewire\WithPagination;

class ReviewReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $startDate = '';
    public $endDate = '';
    public $rating = '';

    public function updated($propertyName)
    {
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = ListingReview::with(['listing', 'customer']);
        if (!$isSuperAdmin) {
            $query->whereHas('listing', function($q) use ($partnerId) {
                $q->where('partner_id', $partnerId);
            });
        }
        
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('listing', function($q) {
                    $q->where('title', 'like', '%' . $this->search . '%');
                })->orWhereHas('customer', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            });
        }
        
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        
        if ($this->rating !== '') {
            $query->where('rating', $this->rating);
        }

        return $query;
    }

    public function getStatsProperty()
    {
        $baseQuery = $this->getBaseQuery();
        
        return [
            'total_reviews' => (clone $baseQuery)->count(),
            'average_rating' => round((clone $baseQuery)->avg('rating') ?? 0, 1),
            'five_star' => (clone $baseQuery)->where('rating', 5)->count(),
            'one_star' => (clone $baseQuery)->where('rating', 1)->count(),
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
            "Content-Disposition" => "attachment; filename=review_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Listing', 'Customer', 'Rating', 'Comment', 'Date'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id,
                    optional($row->listing)->title ?? '-',
                    optional($row->customer)->name ?? '-',
                    $row->rating,
                    $row->comment,
                    $row->created_at->format('Y-m-d H:i:s')
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.review-report', [
            'reportData' => $this->reportData,
            'stats'      => $this->stats,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Review Report',
            'pageSubtitle' => 'View all customer reviews for your listings',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
