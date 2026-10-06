<?php

namespace App\Livewire\Admin\Reports;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class PartnerReport extends Component
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
        $query = User::where('role', 'partner')->withCount(['listings', 'subscriptions']);
        $this->applyFilters($query);

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Name', 'Email', 'Mobile', 'Status', 'Total Listings', 'Total Subscriptions', 'Registration Date']);

            $query->chunk(200, function ($partners) use ($output) {
                foreach ($partners as $partner) {
                    fputcsv($output, [
                        $partner->name, $partner->email, $partner->mobile, $partner->status,
                        $partner->listings_count, $partner->subscriptions_count,
                        $partner->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($output);
        }, 'partner-report-' . now()->format('Y-m-d_His') . '.csv');
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
        $query = User::where('role', 'partner')->withCount(['listings', 'subscriptions']);
        $this->applyFilters($query);

        $records = $query->latest()->paginate(15);

        return view('livewire.admin.reports.partner-report', compact('records'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Partner Report',
                'pageSubtitle' => 'View Partner Report',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
