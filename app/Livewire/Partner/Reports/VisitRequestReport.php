<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\VisitBooking;
use Livewire\WithPagination;

class VisitRequestReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $startDate = '';
    public $endDate = '';
    public $status = '';

    public function updated($propertyName)
    {
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = VisitBooking::with(['listing', 'customer']);
        if (!$isSuperAdmin) {
            $query->where('partner_id', $partnerId);
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
            $query->whereDate('visit_date', '>=', $this->startDate);
        }
        
        if ($this->endDate) {
            $query->whereDate('visit_date', '<=', $this->endDate);
        }
        
        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        return $query;
    }

    public function getStatsProperty()
    {
        $baseQuery = $this->getBaseQuery();
        
        return [
            'total_visits' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'completed' => (clone $baseQuery)->where('status', 'completed')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
        ];
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->orderBy('visit_date', 'desc')->paginate(20);
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->orderBy('visit_date', 'desc')->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=visit_request_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Listing', 'Customer', 'Phone', 'Visit Date', 'Visit Time', 'Status', 'Note'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    substr($row->id, 0, 8),
                    optional($row->listing)->title ?? '-',
                    optional($row->customer)->name ?? '-',
                    optional($row->customer)->phone ?? '-',
                    $row->visit_date,
                    $row->visit_time,
                    ucfirst($row->status),
                    $row->note
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.visit-request-report', [
            'reportData' => $this->reportData,
            'stats'      => $this->stats,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Visit Request Report',
            'pageSubtitle' => 'View all customer visit requests for your listings',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
