<?php

namespace App\Livewire\Admin\Reports;

use App\Models\Listing;
use Livewire\Component;
use Livewire\WithPagination;

class ListingReport extends Component
{
    use WithPagination;

    public string $search = '';
    public string $startDate = '';
    public string $endDate = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }

    public function export()
    {
        $query = Listing::with(['category', 'partner']);
        $this->applyFilters($query);

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Title', 'Partner', 'Category', 'Status', 'Base Price', 'Created On']);

            $query->chunk(200, function ($listings) use ($output) {
                foreach ($listings as $listing) {
                    fputcsv($output, [
                        $listing->title,
                        $listing->partner->name ?? 'N/A',
                        $listing->category->name ?? 'N/A',
                        $listing->status,
                        $listing->base_price,
                        $listing->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($output);
        }, 'listing-report-' . now()->format('Y-m-d_His') . '.csv');
    }

    private function applyFilters($query)
    {
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('partner', function($q2) {
                      $q2->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }
        if (!empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if (!empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
    }

    public function render()
    {
        $query = Listing::with(['category', 'partner']);
        $this->applyFilters($query);

        $records = $query->latest()->paginate(15);

        return view('livewire.admin.reports.listing-report', compact('records'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Listing Report',
                'pageSubtitle' => 'View Listing Report',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
