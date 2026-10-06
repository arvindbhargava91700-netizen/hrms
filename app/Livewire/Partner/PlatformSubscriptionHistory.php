<?php

namespace App\Livewire\Partner;

use App\Models\PartnerSubscription;
use Livewire\Component;

class PlatformSubscriptionHistory extends Component
{
    use HasPartnerWorkspaceScope;

    public $startDate = '';
    public $endDate = '';
    public $statusFilter = '';

    public function mount()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('platform_plan_viewany'), 403);
    }

    public function render()
    {
        $historyQuery = $this->scopePartnerRecords(PartnerSubscription::with('package'));
        
        if ($this->statusFilter) {
            $historyQuery->where('status', $this->statusFilter);
        }
        if ($this->startDate) {
            $historyQuery->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $historyQuery->whereDate('created_at', '<=', $this->endDate);
        }
        $history = $historyQuery->latest()->get();

        return view('livewire.partner.platform-subscription-history', compact('history'))
            ->layout('layouts.app', [
                'panelName'    => 'Partner Panel',
                'pageTitle'    => 'Subscription History',
                'pageSubtitle' => 'View your past and active platform plan requests',
                'sidebarLinks' => view('partials.sidebar-partner'),
            ]);
    }

    public function exportHistory()
    {
        abort_unless(auth()->user()->isPartner() || auth()->user()->canAccess('platform_plan_viewany'), 403);
        $historyQuery = $this->scopePartnerRecords(PartnerSubscription::with('package'));
        if ($this->statusFilter) {
            $historyQuery->where('status', $this->statusFilter);
        }
        if ($this->startDate) {
            $historyQuery->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $historyQuery->whereDate('created_at', '<=', $this->endDate);
        }
        $history = $historyQuery->latest()->get();

        $csvFileName = 'platform-plans-history-' . now()->format('Ymd_His') . '.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$csvFileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($history) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Package Name', 'Purchased On', 'Starts At', 'Expires At', 'Status']);

            foreach ($history as $sub) {
                fputcsv($file, [
                    $sub->id,
                    $sub->package->name ?? 'Unknown',
                    $sub->created_at->format('Y-m-d H:i:s'),
                    $sub->starts_at ? \Carbon\Carbon::parse($sub->starts_at)->format('Y-m-d') : 'N/A',
                    $sub->expires_at ? \Carbon\Carbon::parse($sub->expires_at)->format('Y-m-d') : 'Lifetime',
                    $sub->status
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
