<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\ProductCategory;
use Livewire\WithPagination;

class ProductCategoryReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $search = '';
    public $status = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $query = ProductCategory::query()->where('partner_id', $partnerId);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->withCount('products')->latest()->paginate(20);
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->withCount('products')->latest()->get();
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=product_category_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Category Name', 'Total Products', 'Status', 'Created At'];
        
        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->id,
                    $row->name,
                    $row->products_count ?? 0,
                    ucfirst($row->status ?? 'active'),
                    $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-',
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.hrms.product-category-report', [
            'reportData' => $this->reportData,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Product Category Report',
            'pageSubtitle' => 'View and filter product categories by name and status',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
