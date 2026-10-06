<?php

namespace App\Livewire\Admin\Reports;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerReport extends Component
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
        $query = User::where('role', 'customer')->withCount('visitBookings');
        $this->applyFilters($query);

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Name', 'Email', 'Mobile', 'Status', 'Total Bookings', 'Registration Date']);

            $query->chunk(200, function ($customers) use ($output) {
                foreach ($customers as $customer) {
                    fputcsv($output, [
                        $customer->name, $customer->email, $customer->mobile, $customer->status,
                        $customer->visit_bookings_count,
                        $customer->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($output);
        }, 'customer-report-' . now()->format('Y-m-d_His') . '.csv');
    }

    private function applyFilters($query)
    {
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%');
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
        $query = User::where('role', 'customer')->withCount('visitBookings');
        $this->applyFilters($query);

        $records = $query->latest()->paginate(15);

        return view('livewire.admin.reports.customer-report', compact('records'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Customer Report',
                'pageSubtitle' => 'View Customer Report',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
