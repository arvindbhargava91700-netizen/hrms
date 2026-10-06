<?php

namespace App\Livewire\Admin\Reports;

use App\Models\PartnerSubscription;
use Livewire\Component;
use Livewire\WithPagination;

class PartnerPackageReport extends Component
{
    use WithPagination;

    public string $search = '';
    public string $startDate = '';
    public string $endDate = '';

    public string $expiresFilter = '';

    protected string $paginationTheme = 'bootstrap';

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }
    public function updatingExpiresFilter() { $this->resetPage(); }

    public function export()
    {
        $query = PartnerSubscription::with(['partner', 'package']);
        $this->applyFilters($query);

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Partner', 'Partner Email', 'Package', 'Amount', 'Status', 'Start Date', 'End Date', 'Created On']);

            $query->chunk(200, function ($subscriptions) use ($output) {
                foreach ($subscriptions as $sub) {
                    fputcsv($output, [
                        $sub->partner->name ?? 'N/A',
                        $sub->partner->email ?? '',
                        $sub->package->name ?? 'N/A',
                        $sub->package->price ?? 0,
                        $sub->status,
                        $sub->starts_at ? $sub->starts_at->format('Y-m-d') : 'N/A',
                        $sub->expires_at ? $sub->expires_at->format('Y-m-d') : 'N/A',
                        $sub->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($output);
        }, 'partner-plan-report-' . now()->format('Y-m-d_His') . '.csv');
    }

    private function applyFilters($query)
    {
        if (!empty($this->search)) {
            $query->whereHas('partner', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }
        if (!empty($this->startDate)) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if (!empty($this->endDate)) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        if (!empty($this->expiresFilter)) {
            if ($this->expiresFilter === 'today') {
                $query->where('expires_at', '>=', now())
                      ->whereDate('expires_at', '<=', now()->endOfDay());
            } elseif ($this->expiresFilter === '3days') {
                $query->where('expires_at', '>=', now())
                      ->whereDate('expires_at', '<=', now()->addDays(3)->endOfDay());
            } elseif ($this->expiresFilter === 'week') {
                $query->where('expires_at', '>=', now())
                      ->whereDate('expires_at', '<=', now()->addDays(7)->endOfDay());
            } elseif ($this->expiresFilter === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }
    }

    public function render()
    {
        $query = PartnerSubscription::with(['partner', 'package']);
        $this->applyFilters($query);

        $records = $query->latest()->paginate(15);

        $stats = [
            'total'     => \App\Models\PartnerSubscription::count(),
            'pending'   => \App\Models\PartnerSubscription::where('status', 'pending')->count(),
            'active'    => \App\Models\PartnerSubscription::where('status', 'active')->where(function($q) {
                               $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                           })->count(),
            'expired'   => \App\Models\PartnerSubscription::where('status', 'expired')->orWhere(function($q) {
                               $q->where('status', 'active')->where('expires_at', '<', now());
                           })->count(),
            'cancelled' => \App\Models\PartnerSubscription::where('status', 'cancelled')->count(),
            'rejected'  => \App\Models\PartnerSubscription::where('status', 'rejected')->count(),
        ];

        return view('livewire.admin.reports.partner-package-report', compact('records', 'stats'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Partner Subscription Report',
                'pageSubtitle' => 'View Partner Subscription Report',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
