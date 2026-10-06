<?php

namespace App\Livewire\Admin;

use App\Models\Payment;
use App\Models\Subscription;
use Carbon\Carbon;
use Livewire\Component;

class Reports extends Component
{
    public string $dateRange = 'this_month';
    public array $trendLabels = [];
    public array $trendValues = [];

    public function getExportUrlProperty(): string
    {
        return route('admin.export', [
            'module' => 'reports',
            'date_range' => $this->dateRange,
        ]);
    }

    public function render()
    {
        $startDate = match ($this->dateRange) {
            'today'      => Carbon::today(),
            'this_week'  => Carbon::now()->startOfWeek(),
            'this_month' => Carbon::now()->startOfMonth(),
            'this_year'  => Carbon::now()->startOfYear(),
            default      => Carbon::now()->startOfMonth(),
        };
        $endDate = Carbon::now();

        $revenue = Payment::where('status', 'paid')
            ->where('paid_at', '>=', $startDate)
            ->sum('amount');

        $newSubscriptions = Subscription::where('starts_at', '>=', $startDate)->count();

        [$trendLabels, $trendValues] = $this->buildTrendData($startDate, $endDate);

        $this->trendLabels = $trendLabels;
        $this->trendValues = $trendValues;

        return view('livewire.admin.reports', compact('revenue', 'newSubscriptions', 'trendLabels', 'trendValues'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Reports',
                'pageSubtitle' => 'Financial and usage analytics',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }

    private function buildTrendData(Carbon $startDate, Carbon $endDate): array
    {
        if ($this->dateRange === 'this_year') {
            $series = Payment::where('status', 'paid')
                ->whereBetween('paid_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
                ->selectRaw("DATE_FORMAT(paid_at, '%Y-%m') as period, SUM(amount) as total")
                ->groupBy('period')
                ->orderBy('period')
                ->pluck('total', 'period');

            $labels = [];
            $values = [];
            $cursor = $startDate->copy()->startOfMonth();

            while ($cursor->lte($endDate)) {
                $key = $cursor->format('Y-m');
                $labels[] = $cursor->format('M Y');
                $values[] = (float) ($series[$key] ?? 0);
                $cursor->addMonth();
            }

            return [$labels, $values];
        }

        $series = Payment::where('status', 'paid')
            ->whereBetween('paid_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->selectRaw('DATE(paid_at) as period, SUM(amount) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period');

        $labels = [];
        $values = [];
        $cursor = $startDate->copy()->startOfDay();

        while ($cursor->lte($endDate)) {
            $key = $cursor->format('Y-m-d');
            $labels[] = $cursor->format('d M');
            $values[] = (float) ($series[$key] ?? 0);
            $cursor->addDay();
        }

        return [$labels, $values];
    }
}
