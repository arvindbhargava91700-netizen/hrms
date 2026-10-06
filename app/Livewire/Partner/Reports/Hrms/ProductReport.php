<?php

namespace App\Livewire\Partner\Reports\Hrms;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductCategory;
use Livewire\WithPagination;

class ProductReport extends Component
{
    use WithPagination;
    protected string $paginationTheme = 'bootstrap';

    public $categoryId = '';
    public $productId = '';
    public $status = '';
    public $search = '';

    public function updatedCategoryId()
    {
        $this->productId = '';
        $this->resetPage();
    }

    public function updatedProductId()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->categoryId = '';
        $this->productId = '';
        $this->status = '';
        $this->search = '';
        $this->resetPage();
    }

    public function getBaseQuery()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $query = Product::query()->where('partner_id', $partnerId);

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        if ($this->productId) {
            $query->where('id', $this->productId);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->search) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhereHas('category', function ($cq) use ($s) {
                      $cq->where('name', 'like', "%{$s}%");
                  });
            });
        }

        return $query;
    }

    public function getReportDataProperty()
    {
        return $this->getBaseQuery()->with(['category'])->latest()->paginate(20);
    }

    public function getCategoriesProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        return ProductCategory::where('partner_id', $partnerId)->orderBy('name')->get();
    }

    public function getProductsListProperty()
    {
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;
        $query = Product::where('partner_id', $partnerId);

        if ($this->categoryId) {
            $query->where('category_id', $this->categoryId);
        }

        return $query->orderBy('name')->get();
    }

    public function exportCsv()
    {
        $data = $this->getBaseQuery()->with(['category'])->latest()->get();
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=product_report.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['ID', 'Product Name', 'Category', 'Base Amount', 'GST Type', 'GST (%)', 'Total Price', 'Status', 'Created At'];
        
        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($data as $row) {
                $isGstIncluded = $row->gst_type === 'include' && (float) $row->gst_percent > 0;
                $gstRate = $isGstIncluded ? (float) $row->gst_percent : 0;
                $gstAmount = $isGstIncluded ? ((float) $row->amount * $gstRate / 100) : 0;
                $totalPrice = (float) $row->amount + $gstAmount;
                
                fputcsv($file, [
                    $row->id,
                    $row->name,
                    optional($row->category)->name ?? '-',
                    $row->amount,
                    $row->gst_type === 'include' ? 'Include' : 'Not Include',
                    $gstRate . '%',
                    $totalPrice,
                    ucfirst($row->status ?? 'active'),
                    $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-',
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function render()
    {
        return view('livewire.partner.reports.hrms.product-report', [
            'reportData'   => $this->reportData,
            'categories'   => $this->categories,
            'productsList' => $this->productsList,
        ])->layout('layouts.app', [
            'panelName'    => 'HRMS Panel',
            'pageTitle'    => 'Product Report',
            'pageSubtitle' => 'View and filter products by category and status',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
