<?php

namespace App\Livewire\Admin\Reports;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BusinessReport extends Component
{
    use WithPagination;

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function exportCsv()
    {
        $query = $this->getBaseQuery();
        
        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Business Name (Partner)', 'Email', 'Mobile', 'Total Listings', 'Total Bookings', 'Active Subscriptions', 'Total Revenue', 'Net Earn']);

            $query->chunk(200, function ($partners) use ($output) {
                foreach ($partners as $partner) {
                    fputcsv($output, [
                        $partner->name,
                        $partner->email,
                        $partner->mobile,
                        $partner->listings_count,
                        $partner->total_bookings,
                        $partner->active_subscriptions,
                        '₹' . number_format($partner->total_revenue, 2),
                        '₹' . number_format($partner->net_earn, 2),
                    ]);
                }
            });
            fclose($output);
        }, 'business-report-' . now()->format('Y-m-d_His') . '.csv');
    }

    private function getBaseQuery()
    {
        $query = User::role('partner')
            ->withCount('listings')
            ->addSelect([
                'total_bookings' => DB::table('bookings')
                    ->join('packages', 'packages.id', '=', 'bookings.package_id')
                    ->join('listings', 'listings.id', '=', 'packages.listing_id')
                    ->whereColumn('listings.partner_id', 'users.id')
                    ->whereNotIn('bookings.status', ['pending_otp', 'pending_payment', 'cancelled', 'rejected'])
                    ->selectRaw('COUNT(bookings.id)'),
                    
                'active_subscriptions' => DB::table('subscriptions')
                    ->join('bookings', 'subscriptions.booking_id', '=', 'bookings.id')
                    ->join('packages', 'packages.id', '=', 'bookings.package_id')
                    ->join('listings', 'listings.id', '=', 'packages.listing_id')
                    ->whereColumn('listings.partner_id', 'users.id')
                    ->where('subscriptions.status', 'active')
                    ->selectRaw('COUNT(subscriptions.id)'),
                    
                'total_revenue' => DB::table('bookings')
                    ->join('packages', 'packages.id', '=', 'bookings.package_id')
                    ->join('listings', 'listings.id', '=', 'packages.listing_id')
                    ->whereColumn('listings.partner_id', 'users.id')
                    ->whereNotIn('bookings.status', ['pending_otp', 'pending_payment', 'cancelled', 'rejected'])
                    ->selectRaw('COALESCE(SUM(bookings.final_amount - bookings.security_deposit), 0)'),
                    
                'net_earn' => \App\Models\WalletTransaction::selectRaw('COALESCE(SUM(amount), 0)')
                    ->whereColumn('user_id', 'users.id')
                    ->where('type', 'credit')
                    ->whereIn('reference_type', [\App\Models\Booking::class, \App\Models\Subscription::class])
            ]);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    public function getStatsProperty()
    {
        $query = clone $this->getBaseQuery();
        $totalListings = 0;
        $totalBookings = 0;
        $activeSubscriptions = 0;
        $totalRevenue = 0;
        $netEarn = 0;

        foreach ($query->get() as $partner) {
            $totalListings += (int) $partner->listings_count;
            $totalBookings += (int) $partner->total_bookings;
            $activeSubscriptions += (int) $partner->active_subscriptions;
            $totalRevenue += (float) $partner->total_revenue;
            $netEarn += (float) $partner->net_earn;
        }

        return [
            'totalListings' => $totalListings,
            'totalBookings' => $totalBookings,
            'activeSubscriptions' => $activeSubscriptions,
            'totalRevenue' => $totalRevenue,
            'netEarn' => $netEarn,
        ];
    }

    public function render()
    {
        return view('livewire.admin.reports.business-report', [
            'records' => $this->getBaseQuery()->paginate(15),
            'stats' => $this->stats
        ])->layout('layouts.app', [
            'panelName'    => 'Admin Panel',
            'pageTitle'    => 'Business Report',
            'pageSubtitle' => 'View performance across all partners',
            'sidebarLinks' => view('partials.sidebar-admin'),
        ]);
    }
}
