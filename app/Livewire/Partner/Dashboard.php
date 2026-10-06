<?php

namespace App\Livewire\Partner;

use App\Models\Listing;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Department;
use App\Models\EmployeeAttendance;
use App\Models\EmployeeTask;
use App\Models\Notice;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Package;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component 
{
    public $expiringFilter = 3;
    public $performanceBranch = '';
    public $performanceDepartment = '';
    public $performanceEmployee = '';

  
        public function render()
    {
        $user = auth()->user();
        $partnerId = $user->isPartner() ? $user->id : $user->parent_id;
        $partner = $user->isPartner() ? $user : User::find($partnerId);
        $partnerIds = $user->isAdmin()
            ? User::where('role', 'partner')->pluck('id')
            : collect([$partnerId])->filter();

        // Get IDs of packages belonging to this partner
        $packageIds = Package::whereHas('listing', fn ($q) => $q->whereIn('partner_id', $partnerIds))->pluck('id');

        // ── Listing Stats ────────────────────────────────────────────────
        $totalListings   = Listing::whereIn('partner_id', $partnerIds)->count();
        $pendingListings = Listing::whereIn('partner_id', $partnerIds)->where('status', 'pending')->count();
        $draftListings   = Listing::whereIn('partner_id', $partnerIds)->where('status', 'draft')->count();
        $approvedListings= Listing::whereIn('partner_id', $partnerIds)->where('status', 'approved')->count();
        $rejectedListings= Listing::whereIn('partner_id', $partnerIds)->where('status', 'rejected')->count();

        // ── Monthly Bookings Sales (last 12 months) ──────────────────────
        $monthlySales = Booking::whereIn('package_id', $packageIds)
            ->where('created_at', '>=', now()->subMonths(11)->startOfMonth())
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(final_amount) as total'),
                DB::raw('COUNT(*) as count')
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
        // Get booking stats per listing via DB
        $listingBookingStats = DB::table('bookings')
            ->join('packages', 'packages.id', '=', 'bookings.package_id')
            ->join('listings', 'listings.id', '=', 'packages.listing_id')
            ->whereIn('listings.partner_id', $partnerIds)
            ->whereNull('bookings.deleted_at')
            ->whereNull('packages.deleted_at')
            ->whereNull('listings.deleted_at')
            ->select(
                'listings.id as listing_id',
                DB::raw('COUNT(' . DB::getTablePrefix() . 'bookings.id) as booking_count'),
                DB::raw('SUM(' . DB::getTablePrefix() . 'bookings.final_amount) as total_revenue')
            )
            ->groupBy('listings.id')
            ->orderByDesc('booking_count')
            ->limit(5)
            ->get()
            ->keyBy('listing_id');

        $topListingIds = $listingBookingStats->keys()->toArray();

        $highestListings = Listing::whereIn('partner_id', $partnerIds)
            ->with('category')
            ->when(!empty($topListingIds), fn($q) => $q->whereIn('id', $topListingIds))
            ->when(empty($topListingIds), fn($q) => $q->limit(5))
            ->get()
            ->sortByDesc(fn($l) => $listingBookingStats[$l->id]->booking_count ?? 0)
            ->take(5)
            ->values();

        // Attach stats to each listing
        foreach ($highestListings as $listing) {
            $stats_row = $listingBookingStats[$listing->id] ?? null;
            $listing->booking_count = $stats_row ? $stats_row->booking_count : 0;
            $listing->total_revenue = $stats_row ? $stats_row->total_revenue : 0;
        }

        // ── Category Usage (how many listings per category) ──────────────
        $categoryUsage = Category::withCount(['listings' => fn ($q) => $q->whereIn('partner_id', $partnerIds)])
            ->having('listings_count', '>', 0)
            ->orderByDesc('listings_count')
            ->get();

        // ── Standard Finance Stats ────────────────────────────────────────
        $totalCredit = \App\Models\WalletTransaction::whereIn('user_id', $partnerIds)
            ->where('type', 'credit')
            ->whereIn('reference_type', [\App\Models\Booking::class, \App\Models\Subscription::class])
            ->sum('amount');
        $totalReserve = \App\Models\ReserveHistory::whereIn('partner_id', $partnerIds)
            ->where('status', 'active')
            ->sum('amount');

        $stats = [
            'totalRevenue' => $totalCredit - $totalReserve,
            'activeListings'       => $approvedListings,
            'totalListings'        => $totalListings,
            'pendingListings'      => $pendingListings,
            'draftListings'        => $draftListings,
            'approvedListings'     => $approvedListings,
            'rejectedListings'     => $rejectedListings,
            'activeCustomers'      => Subscription::where('status', 'active')
                ->whereIn('package_id', $packageIds)
                ->distinct('customer_id')
                ->count('customer_id'),
            'activeSubscriptions'  => Subscription::where('status', 'active')
                ->whereIn('package_id', $packageIds)
                ->count(),
            'walletBalance'        => $user->isAdmin()
                ? User::whereIn('id', $partnerIds)->sum('wallet_balance')
                : ($partner ? $partner->wallet_balance : 0),
            'pendingWithdrawals'   => \App\Models\WithdrawalRequest::whereIn('user_id', $partnerIds)
                ->where('status', 'pending')
                ->sum('amount'),
            'reserveTotal'         => \App\Models\ReserveHistory::whereIn('partner_id', $partnerIds)->count(),
            'reserveHold'          => \App\Models\ReserveHistory::whereIn('partner_id', $partnerIds)->where('status', 'active')->count(),
            'reserveReturn'        => \App\Models\ReserveHistory::whereIn('partner_id', $partnerIds)->where('status', '!=', 'active')->count(),
            'totalBookings'        => Booking::whereIn('package_id', $packageIds)->count(),
            'bookingStats' => [
                'total'       => Booking::whereIn('package_id', $packageIds)->count(),
                'pending_otp' => Booking::whereIn('package_id', $packageIds)->where('status', 'pending_otp')->count(),
                'confirmed'   => Booking::whereIn('package_id', $packageIds)->whereIn('status', ['confirmed', 'pending_payment'])->count(),
                'active'      => Booking::whereIn('package_id', $packageIds)->where('status', 'active')->count(),
                'completed'   => Booking::whereIn('package_id', $packageIds)->where('status', 'completed')->count(),
                'cancelled'   => Booking::whereIn('package_id', $packageIds)->whereIn('status', ['cancelled', 'rejected'])->count(),
            ],
        ];

        // ── HRMS ──────────────────────────────────────────────────────────
        $hasHrms = \App\Models\PartnerSubscription::whereIn('partner_id', $partnerIds)
            ->where('status', 'active')
            ->whereHas('package.systemModules', fn($q) => $q->where('slug', 'hrms'))
            ->exists();

        $activeNotices = collect();
        $deptStats = [];

        if ($hasHrms) {
            if ($user->canAccess('staff_viewany') || $user->canAccess('attendance_viewany')) {
                $stats['hrmsStaff']            = User::whereIn('parent_id', $partnerIds)->where('role', 'employee')->count();
                $stats['hrmsDepts']            = Department::whereIn('partner_id', $partnerIds)->count();
                $stats['hrmsAttendanceToday']  = EmployeeAttendance::whereHas('employee', fn($q) => $q->whereIn('parent_id', $partnerIds))
                    ->whereDate('date', today())->count();
            }

            $departments = collect();
            if ($user->isPartner() || $user->canAccess('department_viewany')) {
                $departments = Department::whereIn('partner_id', $partnerIds)->with(['branchHeads.head', 'employees'])->get();
            } elseif ($user->canAccess('department_viewteam')) {
                $departments = Department::whereIn('partner_id', $partnerIds)
                    ->where(function($q) use ($user) {
                        $q->whereHas('branchHeads', function($q2) use ($user) {
                            $q2->where('head_id', $user->id);
                        })->orWhere('id', $user->department_id);
                    })->with(['branchHeads.head', 'employees'])->get();
            }

            foreach ($departments as $dept) {
                $empIds = $dept->employees->pluck('id');
                $staffCount = $empIds->count();
                $attendanceToday = EmployeeAttendance::whereIn('employee_id', $empIds)
                    ->whereDate('date', today())
                    ->whereNotNull('check_in')
                    ->count();

                $deptStats[] = [
                    'id'              => $dept->id,
                    'name'            => $dept->name,
                    'head'            => $dept->branchHeads->first() && $dept->branchHeads->first()->head ? $dept->branchHeads->first()->head->name : 'N/A',
                    'staffCount'      => $staffCount,
                    'attendanceToday' => $attendanceToday,
                ];
            }
            $stats['departmentPerformance'] = $deptStats;

            if ($user->role === 'partner' || $user->canAccess('staff_viewany')) {
                $stats['recentTasks'] = EmployeeTask::with('employee')
                    ->whereHas('employee', fn($q) => $q->whereIn('parent_id', $partnerIds))
                    ->latest()
                    ->limit(10)
                    ->get();
                    
                // Team-wise top achievers and totals (All)
                $achieversList = [];
                $teamTotalBusiness = 0;
                $teamTotalTarget = 0;
                $commissionSvc = new \App\Services\CommissionService();
                
                $employeesQuery = User::whereIn('parent_id', $partnerIds)->where('role', 'employee');
                
                if (!empty($this->performanceBranch)) {
                    $employeesQuery->where('branch_id', $this->performanceBranch);
                }
                if (!empty($this->performanceDepartment)) {
                    $employeesQuery->where('department_id', $this->performanceDepartment);
                }
                if (!empty($this->performanceEmployee)) {
                    $employeesQuery->where('id', $this->performanceEmployee);
                }

                $teamEmployees = $employeesQuery->get();
                
                $achieversData = $this->calculateTeamAchievers($teamEmployees);
                
                $stats['teamAchievers'] = $achieversData['achievers'];
                $stats['teamTotalBusiness'] = $achieversData['totalBusiness'];
                $stats['teamTotalTarget'] = $achieversData['totalTarget'];
                $stats['teamTotalCommission'] = $achieversData['totalCommission'];
                
                // Provide filter data
                $stats['performanceBranches'] = \App\Models\HrmsBranch::whereIn('partner_id', $partnerIds)->get();
                $stats['performanceDepartments'] = \App\Models\Department::whereIn('partner_id', $partnerIds)->get();
                $stats['performanceEmployees'] = User::whereIn('parent_id', $partnerIds)->where('role', 'employee')->get();
            }
        }

        $recentSubscriptions = Subscription::with(['customer', 'package'])
            ->whereIn('package_id', $packageIds)
            ->latest()
            ->limit(5)
            ->get();

        $stats['recentBookings'] = Booking::with(['customer', 'package'])
            ->whereIn('package_id', $packageIds)
            ->latest()
            ->limit(5)
            ->get();

        $stats['recentTransactions'] = \App\Models\WalletTransaction::whereIn('user_id', $partnerIds)
            ->latest()
            ->limit(5)
            ->get();

        $stats['expiringSubscriptionsList'] = Subscription::with(['customer', 'package'])
            ->whereIn('package_id', $packageIds)
            ->expiring(30)
            ->limit(5)
            ->get();

        $stats['recentRenewedSubscriptions'] = Subscription::with(['customer', 'package'])
            ->whereIn('package_id', $packageIds)
            ->where('status', 'active')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        $myTarget = null;
        if ($hasHrms) {
            $structure = \App\Models\EmployeeSalaryStructure::where('employee_id', $user->id)->first();
            if ($structure && in_array($structure->salary_type, ['base_plus_target', 'commission_only'])) {
                $commissionService = new \App\Services\CommissionService();
                $month = now()->format('m');
                $year  = now()->format('Y');
                $commissionData = $commissionService->calculateEmployeeCommission($user, $month, $year);
                $myTarget = (object) [
                    'business_target'   => $structure->monthly_target,
                    'business_achieved' => $commissionData['target_achieved'],
                ];
            }
        }

        return view('livewire.partner.dashboard', compact(
            'stats', 'recentSubscriptions', 'hasHrms', 'activeNotices', 'myTarget',
            'salesLabels', 'salesAmounts', 'salesCounts',
            'highestListings', 'categoryUsage',
            'totalListings', 'pendingListings', 'draftListings', 'approvedListings', 'rejectedListings'
        ))->layout('layouts.app', [
            'panelName'    => 'Partner Panel',
            'pageTitle'    => 'Dashboard',
            'pageSubtitle' => 'Welcome back, ' . $user->name,
            'sidebarLinks' => view('partials.sidebar-partner'),
        ]);
    }

    private function calculateTeamAchievers($teamMembers)
    {
        $commissionSvc = new \App\Services\CommissionService();
        $teamTotalCommission = 0;
        $teamTotalBusiness = 0;
        $teamTotalTarget = 0;
        $achieversList = [];

        $memberIds = $teamMembers->pluck('id');
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $tasks = \App\Models\EmployeeTask::whereIn('employee_id', $memberIds)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("employee_id, COUNT(*) as total, SUM(status = 'completed') as completed")
            ->groupBy('employee_id')->get()->keyBy('employee_id');

        $attendance = \App\Models\EmployeeAttendance::whereIn('employee_id', $memberIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotIn('status', ['absent', 'leave'])
            ->selectRaw("employee_id, COUNT(*) as days")
            ->groupBy('employee_id')->get()->keyBy('employee_id');

        $merchants = \App\Models\Lead::whereIn('assigned_to', $memberIds)
            ->where('status', 'won')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('assigned_to, COUNT(*) as total')
            ->groupBy('assigned_to')->get()->keyBy('assigned_to');

        $targets = \App\Models\EmployeeSalaryStructure::whereIn('employee_id', $memberIds)
            ->get(['employee_id', 'merchant_target', 'monthly_target'])
            ->keyBy('employee_id');

        $monthlyAchieved = \App\Models\LeadOrder::whereIn('employee_id', $memberIds)
            ->whereBetween('created_at', [$start, $end])
            ->where(function ($query) {
                $query->where('target_credited', true)->orWhere('approval_status', 'completed');
            })
            ->selectRaw('employee_id, SUM(paid_amount) as achieved')
            ->groupBy('employee_id')->get()->keyBy('employee_id');

        $daysInMonth = now()->daysInMonth;

        foreach ($teamMembers as $member) {
            $mData = $commissionSvc->calculateEmployeeCommission($member, now()->month, now()->year);
            $targetRequired = (float)($mData['target_required'] ?? 0);
            $business = (float)($mData['new_business'] ?? 0);
            $pct = $targetRequired > 0 ? ($business / $targetRequired) * 100 : ($business > 0 ? 100 : 0);

            $teamTotalCommission += $mData['total_commission'] ?? 0;
            $teamTotalBusiness += $business;
            $teamTotalTarget += $targetRequired;

            // Performance Score
            $task = $tasks->get($member->id);
            $merchant = $merchants->get($member->id);
            $attendanceDays = (int) ($attendance->get($member->id)->days ?? 0);
            $totalTasks = (int) ($task->total ?? 0);
            $completedTasks = (int) ($task->completed ?? 0);

            $merchantTarget = (float) ($targets->get($member->id)->merchant_target ?? 0);
            $monthlyTarget = (float) ($targets->get($member->id)->monthly_target ?? 0);
            $monthlyBusiness = (float) ($monthlyAchieved->get($member->id)->achieved ?? 0);
            $merchantAchieved = (int) ($merchant->total ?? 0);

            $merchantPercentage = $merchantTarget > 0 ? min(100, ($merchantAchieved / $merchantTarget) * 100) : 0;
            $monthlyPercentage = $monthlyTarget > 0 ? min(100, ($monthlyBusiness / $monthlyTarget) * 100) : 0;

            $attendanceScore = min(25, round(($attendanceDays / max(1, $daysInMonth)) * 25));
            $taskScore = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 25) : 0;
            $merchantScore = round($merchantPercentage * 0.25);
            $monthlyTargetScore = round($monthlyPercentage * 0.25);
            $totalScore = $attendanceScore + $taskScore + $merchantScore + $monthlyTargetScore;
            $grade = $totalScore >= 90 ? 'A+' : ($totalScore >= 80 ? 'A' : ($totalScore >= 70 ? 'B+' : ($totalScore >= 60 ? 'B' : 'C')));

            $achieversList[] = [
                'user' => $member,
                'name' => $member->name,
                'employee_id' => $member->id,
                'employee_code' => $member->employee_code,
                'department' => $member->department?->name ?? 'N/A',
                'business' => $business,
                'target' => $targetRequired,
                'pct' => $pct,
                'tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'attendance' => $attendanceDays,
                'attendance_score' => $attendanceScore,
                'task_score' => $taskScore,
                'merchants' => $merchantAchieved,
                'merchant_target' => $merchantTarget,
                'merchant_score' => $merchantScore,
                'monthly_achieved' => $monthlyBusiness,
                'monthly_target' => $monthlyTarget,
                'monthly_percentage' => $monthlyPercentage,
                'monthly_target_score' => $monthlyTargetScore,
                'total_score' => $totalScore,
                'score' => $totalScore,
                'grade' => $grade,
                'days_in_month' => $daysInMonth
            ];
        }

        usort($achieversList, function ($a, $b) {
            if ($a['score'] == $b['score']) {
                return $b['business'] <=> $a['business'];
            }
            return $b['score'] <=> $a['score'];
        });

        return [
            'achievers' => collect(array_slice($achieversList, 0, 10)),
            'totalBusiness' => $teamTotalBusiness,
            'totalTarget' => $teamTotalTarget,
            'totalCommission' => $teamTotalCommission,
        ];
    }
}
