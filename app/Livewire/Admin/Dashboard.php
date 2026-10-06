<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\KycDocument;
use App\Models\Listing;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public $expiringFilter = 3;

    public function render()
    {
        $adminCredit = \App\Models\WalletTransaction::where('type', 'credit')
            ->whereIn('reference_type', [\App\Models\Booking::class, \App\Models\Subscription::class])
            ->sum('amount');
        $adminReserve = \App\Models\ReserveHistory::where('status', 'active')
            ->sum('amount');

        $stats = [
            'totalRevenue' => $adminCredit - $adminReserve,
            'activePartners'       => User::where('role', 'partner')->where('status', 'active')->count(),
            'activeCustomers'      => User::where('role', 'customer')->where('status', 'active')->count(),
            'activeSubscriptions'  => Subscription::where('status', 'active')->count(),
            'activePartnerSubscriptions' => \App\Models\PartnerSubscription::where('status', 'active')->count(),
            'expiringSubscriptions'=> Subscription::expiring(7)->count(),
            'pendingKyc'           => KycDocument::where('status', 'pending')->count(),
            // Listings
            'totalListings'        => Listing::count(),
            'pendingListings'      => Listing::where('status', 'pending')->count(),
            'draftListings'        => Listing::where('status', 'draft')->count(),
            'rejectedListings'     => Listing::where('status', 'rejected')->count(),
            'approvedListings'     => Listing::where('status', 'approved')->count(),

            'pendingVisits'        => \App\Models\VisitBooking::where('status', 'pending')->count(),
            'totalVisits'          => \App\Models\VisitBooking::count(),
            'totalWalletBalances'  => User::where('role', 'partner')->sum('wallet_balance'),
            'totalCustomerWalletBalances' => User::where('role', 'customer')->sum('wallet_balance'),
            'pendingWithdrawals'   => \App\Models\WithdrawalRequest::where('status', 'pending')->count(),
            'reserveTotal'         => \App\Models\ReserveHistory::count(),
            'reserveHold'          => \App\Models\ReserveHistory::where('status', 'active')->count(),
            'reserveReturn'        => \App\Models\ReserveHistory::where('status', '!=', 'active')->count(),
            // Additional metrics for new sidebar features
            'totalCategories'      => Category::count(),
            'totalAppBanners'      => \App\Models\Banner::count(),
            'totalCoupons'         => \App\Models\Coupon::count(),
            'totalReviews'         => \App\Models\ListingReview::count(),
            'totalPartnerPackages' => \App\Models\PartnerPackage::count(),
            'totalBookings'        => \App\Models\Booking::whereNotIn('status', ['pending_otp', 'pending_payment', 'cancelled', 'rejected'])->count(),
            'bookingStats' => [
                'total'       => \App\Models\Booking::count(),
                'pending_otp' => \App\Models\Booking::where('status', 'pending_otp')->count(),
                'confirmed'   => \App\Models\Booking::whereIn('status', ['confirmed', 'pending_payment'])->count(),
                'active'      => \App\Models\Booking::where('status', 'active')->count(),
                'completed'   => \App\Models\Booking::where('status', 'completed')->count(),
                'cancelled'   => \App\Models\Booking::whereIn('status', ['cancelled', 'rejected'])->count(),
            ],
            'totalInvoices'        => \App\Models\Invoice::count(),
            'totalPayments'        => \App\Models\Payment::count(),
            'pendingCustomerKyc'   => \App\Models\CustomerKyc::where('status', 'pending')->count(),
            'kycFields'            => \App\Models\KycRequirement::count(),
            'totalAttendance'      => \App\Models\EmployeeAttendance::count(),
        ];

        // ── Monthly Bookings Sales (last 12 months) ──────────────────────
        $monthlySales = \App\Models\Booking::where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->whereNotIn('status', ['pending_otp', 'pending_payment', 'cancelled', 'rejected'])
            ->select(
                \Illuminate\Support\Facades\DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                \Illuminate\Support\Facades\DB::raw('SUM(final_amount) as total'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $salesLabels = [];
        $salesAmounts = [];
        $salesCounts  = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthKey = now()->subMonths($i)->format('Y-m');
            $label    = now()->subMonths($i)->format('M Y');
            $salesLabels[]  = $label;
            $salesAmounts[] = $monthlySales[$monthKey]->total ?? 0;
            $salesCounts[]  = $monthlySales[$monthKey]->count ?? 0;
        }

        // ── Highest Performing Listings (by booking count) ───────────────
        $listingBookingStats = \Illuminate\Support\Facades\DB::table('bookings')
            ->join('packages', 'packages.id', '=', 'bookings.package_id')
            ->join('listings', 'listings.id', '=', 'packages.listing_id')
            ->whereNull('bookings.deleted_at')
            ->whereNull('packages.deleted_at')
            ->whereNull('listings.deleted_at')
            ->whereNotIn('bookings.status', ['pending_otp', 'pending_payment', 'cancelled', 'rejected'])
            ->select(
                'listings.id as listing_id',
                \Illuminate\Support\Facades\DB::raw('COUNT(bookings.id) as booking_count'),
                \Illuminate\Support\Facades\DB::raw('SUM(bookings.final_amount) as total_revenue')
            )
            ->groupBy('listings.id')
            ->orderByDesc('booking_count')
            ->limit(5)
            ->get()
            ->keyBy('listing_id');

        $topListingIds = $listingBookingStats->keys()->toArray();

        $highestListings = Listing::with('category')
            ->when(!empty($topListingIds), fn($q) => $q->whereIn('id', $topListingIds))
            ->when(empty($topListingIds), fn($q) => $q->limit(5))
            ->get();

        if (count($highestListings) < 5) {
            $otherListings = Listing::with('category')
                ->when(!empty($topListingIds), fn($q) => $q->whereNotIn('id', $topListingIds))
                ->limit(5 - count($highestListings))
                ->get();
            $highestListings = $highestListings->concat($otherListings);
        }

        $highestListings = $highestListings
            ->sortByDesc(fn($l) => $listingBookingStats[$l->id]->booking_count ?? 0)
            ->values();

        // Attach stats to each listing
        foreach ($highestListings as $listing) {
            $stats_row = $listingBookingStats[$listing->id] ?? null;
            $listing->booking_count = $stats_row ? $stats_row->booking_count : 0;
            $listing->total_revenue = $stats_row ? $stats_row->total_revenue : 0;
        }

        // ── Category Usage (how many listings per category) ──────────────
        $categoryUsage = Category::withCount('listings')
            ->having('listings_count', '>', 0)
            ->orderByDesc('listings_count')
            ->get();

        $chartData = [
            'salesLabels'  => $salesLabels,
            'salesAmounts' => $salesAmounts,
            'salesCounts'  => $salesCounts,
            'highestListingsLabels' => $highestListings->pluck('title')->toArray(),
            'highestListingsCounts' => $highestListings->pluck('booking_count')->toArray(),
            'categoryLabels' => $categoryUsage->pluck('name')->toArray(),
            'categoryData'   => $categoryUsage->pluck('listings_count')->toArray(),
        ];

        $recentPayments = Payment::with(['subscription.customer', 'subscription.package'])
            ->where('status', 'paid')
            ->latest()
            ->limit(8)
            ->get();

        $roomOccupancy = \Illuminate\Support\Facades\DB::table('categories')
            ->join('listings', 'categories.id', '=', 'listings.category_id')
            ->join('floors', 'listings.id', '=', 'floors.listing_id')
            ->join('rooms', 'floors.id', '=', 'rooms.floor_id')
            ->select('categories.name as category_name',
                     \Illuminate\Support\Facades\DB::raw('SUM(rooms.capacity) as capacity'),
                     \Illuminate\Support\Facades\DB::raw('SUM(rooms.available_beds) as available'))
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->keyBy('category_name');

        $subscriptionsSub = \Illuminate\Support\Facades\DB::table('subscriptions')
            ->join('bookings', 'subscriptions.booking_id', '=', 'bookings.id')
            ->where('subscriptions.status', 'active')
            ->select('bookings.shift_id', \Illuminate\Support\Facades\DB::raw('SUM(subscriptions.beds_booked) as total_enrolled'))
            ->groupBy('bookings.shift_id');

        $shiftOccupancy = \Illuminate\Support\Facades\DB::table('categories')
            ->join('listings', 'categories.id', '=', 'listings.category_id')
            ->join('listing_shifts', 'listings.id', '=', 'listing_shifts.listing_id')
            ->leftJoinSub($subscriptionsSub, 'subs', function($join) {
                $join->on('listing_shifts.id', '=', 'subs.shift_id');
            })
            ->select('categories.name as category_name',
                     \Illuminate\Support\Facades\DB::raw('SUM(listing_shifts.max_members) as capacity'),
                     \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(subs.total_enrolled), 0) as enrolled'))
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->keyBy('category_name');

        $categoriesWithListings = \Illuminate\Support\Facades\DB::table('categories')
            ->join('listings', 'categories.id', '=', 'listings.category_id')
            ->whereNull('listings.deleted_at')
            ->select('categories.name as category_name')
            ->distinct()
            ->get()
            ->pluck('category_name');

        $occupancyByCategory = [];
        foreach ($categoriesWithListings as $cat) {
            $r = $roomOccupancy[$cat] ?? null;
            $s = $shiftOccupancy[$cat] ?? null;
            
            $total_capacity = ($r->capacity ?? 0) + ($s->capacity ?? 0);
            $total_available = ($r->available ?? 0) + max(0, ($s->capacity ?? 0) - ($s->enrolled ?? 0));
            
            $occupancyByCategory[] = [
                'category' => $cat,
                'occupied' => max(0, $total_capacity - $total_available),
                'available' => (int) $total_available
            ];
        }
        
        $chartData['occupancyByCategory'] = $occupancyByCategory;

        $recentPartners = User::where('role', 'partner')->latest()->limit(5)->get();

        $stats['recentBookings'] = \App\Models\Booking::with(['customer', 'package'])
            ->latest()->limit(10)->get();
        $stats['recentTransactions'] = \App\Models\WalletTransaction::with('user')->latest()->limit(10)->get();
        $stats['recentRenewedSubscriptions'] = Subscription::with(['customer', 'package'])->where('status', 'active')->latest('updated_at')->limit(10)->get();

        $filter = $this->expiringFilter;

        if ($filter === 'expired') {
            $stats['customerExpiring'] = Subscription::with(['customer', 'package'])
                ->where('expires_at', '<', now())
                ->orderBy('expires_at', 'desc')
                ->limit(10)
                ->get();

            $stats['partnerExpiring'] = \App\Models\PartnerSubscription::with(['partner', 'package'])
                ->where('expires_at', '<', now())
                ->orderBy('expires_at', 'desc')
                ->limit(10)
                ->get();
        } else {
            $days = (int) $filter;
            $stats['customerExpiring'] = Subscription::with(['customer', 'package'])
                ->active()
                ->where('expires_at', '>=', now())
                ->whereDate('expires_at', '<=', now()->addDays($days)->endOfDay())
                ->orderBy('expires_at', 'asc')
                ->limit(10)
                ->get();

            $stats['partnerExpiring'] = \App\Models\PartnerSubscription::with(['partner', 'package'])
                ->where('status', 'active')
                ->where('expires_at', '>=', now())
                ->whereDate('expires_at', '<=', now()->addDays($days)->endOfDay())
                ->orderBy('expires_at', 'asc')
                ->limit(10)
                ->get();
        }

        return view('livewire.admin.dashboard', compact('stats', 'recentPayments', 'recentPartners', 'chartData', 'highestListings'))
            ->layout('layouts.app', [
                'panelName'    => 'Admin Panel',
                'pageTitle'    => 'Dashboard',
                'pageSubtitle' => 'Platform overview & analytics',
                'sidebarLinks' => view('partials.sidebar-admin'),
            ]);
    }
}
