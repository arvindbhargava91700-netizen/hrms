<?php

namespace App\Livewire\Partner\Reports;

use Livewire\Component;
use App\Models\PartnerSubscription;
use Livewire\WithPagination;

class PartnerSubscriptionReport extends Component
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

        $query = PartnerSubscription::query();
        if (!$isSuperAdmin) {
            $query->where('partner_id', $partnerId);
        }
        
        if ($this->search) {
            $query->whereHas('package', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
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
        return $this->getBaseQuery()->with(['package'])->latest()->paginate(20);
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['package'])->latest()->get();
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=partner_subscription_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Package Name', 'Price', 'Start Date', 'End Date', 'Status'];
        
        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    substr($row->id, 0, 8),
                    optional($row->package)->name ?? '-',
                    optional($row->package)->price ?? '-',
                    $row->starts_at?->format('Y-m-d') ?? '-',
                    $row->expires_at?->format('Y-m-d') ?? '-',
                    $row->status
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.partner-subscription-report', [
            'reportData' => $this->reportData,
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Partner Subscription Report',
            'pageSubtitle' => 'View your subscription history',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
