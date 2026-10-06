<?php

namespace App\Livewire\Partner\Reports;

use App\Models\Listing;
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
            fputcsv($output, ['Listing Name', 'Category', 'City', 'Total Bookings', 'Active Subscriptions', 'Total Revenue', 'Net Earn']);

            $query->chunk(200, function ($listings) use ($output) {
                foreach ($listings as $listing) {
                    fputcsv($output, [
                        $listing->title,
                        $listing->category->name ?? 'N/A',
                        $listing->city ?? 'N/A',
                        $listing->total_bookings,
                        $listing->active_subscriptions,
                        '₹' . number_format($listing->total_revenue, 2),
                        '₹' . number_format($listing->net_earn, 2),
                    ]);
                }
            });
            fclose($output);
        }, 'business-report-' . now()->format('Y-m-d_His') . '.csv');
    }

    private function getBaseQuery()
    {
        $isSuperAdmin = auth()->user()->role === 'super_admin';
        $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id;

        $query = Listing::with(['category'])
            ->addSelect([
                'total_bookings' => DB::table('bookings')
                    ->join('packages', 'packages.id', '=', 'bookings.package_id')
                    ->whereColumn('packages.listing_id', 'listings.id')
                    ->whereNotIn('bookings.status', ['pending_otp', 'pending_payment', 'cancelled', 'rejected'])
                    ->selectRaw('COUNT(' . DB::getTablePrefix() . 'bookings.id)'),
                    
                'active_subscriptions' => DB::table('subscriptions')
                    ->join('bookings', 'subscriptions.booking_id', '=', 'bookings.id')
                    ->join('packages', 'packages.id', '=', 'bookings.package_id')
                    ->whereColumn('packages.listing_id', 'listings.id')
                    ->where('subscriptions.status', 'active')
                    ->selectRaw('COUNT(' . DB::getTablePrefix() . 'subscriptions.id)'),
                    
                'total_revenue' => DB::table('bookings')
                    ->join('packages', 'packages.id', '=', 'bookings.package_id')
                    ->whereColumn('packages.listing_id', 'listings.id')
                    ->whereNotIn('bookings.status', ['pending_otp', 'pending_payment', 'cancelled', 'rejected'])
                    ->selectRaw('COALESCE(SUM(' . DB::getTablePrefix() . 'bookings.final_amount - ' . DB::getTablePrefix() . 'bookings.security_deposit), 0)'),
                    
                'net_earn' => DB::table('wallet_transactions')
                    ->selectRaw('
                        (SELECT COALESCE(SUM(amount), 0) FROM wallet_transactions wt 
                         JOIN bookings b ON b.id = wt.reference_id 
                         JOIN packages p ON p.id = b.package_id 
                         WHERE p.listing_id = listings.id AND wt.reference_type = ? AND wt.type = "credit" AND wt.user_id = listings.partner_id)
                        +
                        (SELECT COALESCE(SUM(amount), 0) FROM wallet_transactions wt 
                         JOIN subscriptions s ON s.id = wt.reference_id 
                         JOIN bookings b ON b.id = s.booking_id 
                         JOIN packages p ON p.id = b.package_id 
                         WHERE p.listing_id = listings.id AND wt.reference_type = ? AND wt.type = "credit" AND wt.user_id = listings.partner_id)
                    ', [\App\Models\Booking::class, \App\Models\Subscription::class])
                    ->limit(1)
            ]);

        if (!$isSuperAdmin) {
            $query->where('partner_id', $partnerId);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('city', 'like', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    public function getStatsProperty()
    {
        $query = clone $this->getBaseQuery();
        $totalBookings = 0;
        $activeSubscriptions = 0;
        $totalRevenue = 0;
        $netEarn = 0;

        foreach ($query->get() as $listing) {
            $totalBookings += (int) $listing->total_bookings;
            $activeSubscriptions += (int) $listing->active_subscriptions;
            $totalRevenue += (float) $listing->total_revenue;
            $netEarn += (float) $listing->net_earn;
        }

        return [
            'totalBookings' => $totalBookings,
            'activeSubscriptions' => $activeSubscriptions,
            'totalRevenue' => $totalRevenue,
            'netEarn' => $netEarn,
        ];
    }

    public function render()
    {
        return view('livewire.partner.reports.business-report', [
            'records' => $this->getBaseQuery()->paginate(15),
            'stats' => $this->stats
        ])->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Business Report',
            'pageSubtitle' => 'View business and revenue by listing',
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }
}
