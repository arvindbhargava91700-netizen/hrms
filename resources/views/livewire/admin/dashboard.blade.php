<div>
    {{-- ════════════════════════════════════════════════
         LISTING OVERVIEW STATS
    ════════════════════════════════════════════════ --}}
    <h6 class="mb-3 text-uppercase text-muted fw-bold" style="font-size: 12px; letter-spacing: 1px;">Listing Overview</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.listings') }}'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid #6366f1;">
                <div class="stat-icon" style="background: rgba(99,102,241,0.1);"><i class="bi bi-grid-3x3-gap" style="color:#6366f1;"></i></div>
                <div>
                    <div class="stat-label">Total Listings</div>
                    <div class="stat-value" style="color:#6366f1;">{{ number_format($stats['totalListings'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.listings') }}'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-check2-circle text-success"></i></div>
                <div>
                    <div class="stat-label">Approved</div>
                    <div class="stat-value text-success">{{ number_format($stats['approvedListings'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.listings') }}'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-warning);">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-hourglass-split text-warning"></i></div>
                <div>
                    <div class="stat-label">Total Pending</div>
                    <div class="stat-value text-warning">{{ number_format($stats['pendingListings'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.listings') }}'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid #64748b;">
                <div class="stat-icon" style="background: rgba(100,116,139,0.1);"><i class="bi bi-pencil-square" style="color:#64748b;"></i></div>
                <div>
                    <div class="stat-label">Total Draft</div>
                    <div class="stat-value" style="color:#64748b;">{{ number_format($stats['draftListings'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.listings') }}'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-danger);">
                <div class="stat-icon" style="background: rgba(220,53,69,0.1);"><i class="bi bi-x-circle text-danger"></i></div>
                <div>
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value text-danger">{{ number_format($stats['rejectedListings'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Overview -->
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size: 12px; letter-spacing: 1px;">Booking Overview</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.bookings') }}'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid #3b82f6;">
                <div class="stat-icon" style="background: rgba(59,130,246,0.1);"><i class="bi bi-calendar2-check" style="color:#3b82f6;"></i></div>
                <div>
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-value" style="color:#3b82f6;">{{ number_format($stats['bookingStats']['total'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.bookings') }}'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-warning);">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-shield-lock text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending OTP</div>
                    <div class="stat-value text-warning">{{ number_format($stats['bookingStats']['pending_otp'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.bookings') }}'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-info);">
                <div class="stat-icon bg-info-soft"><i class="bi bi-credit-card text-info"></i></div>
                <div>
                    <div class="stat-label">Confirmed</div>
                    <div class="stat-value text-info">{{ number_format($stats['bookingStats']['confirmed'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.bookings') }}'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid #10b981;">
                <div class="stat-icon" style="background: rgba(16,185,129,0.1);"><i class="bi bi-check-all" style="color:#10b981;"></i></div>
                <div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-value" style="color:#10b981;">{{ number_format($stats['bookingStats']['completed'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.bookings') }}'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-danger);">
                <div class="stat-icon" style="background: rgba(220,53,69,0.1);"><i class="bi bi-x-circle text-danger"></i></div>
                <div>
                    <div class="stat-label">Cancelled</div>
                    <div class="stat-value text-danger">{{ number_format($stats['bookingStats']['cancelled'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Finance & Earnings -->
    <h6 class="mb-3 text-uppercase text-muted fw-bold" style="font-size: 12px; letter-spacing: 1px;">Finance & Earnings</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.transaction-history') }}'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-currency-rupee text-success"></i></div>
                <div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value text-success">₹{{ number_format($stats['totalRevenue'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.partners') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="stat-label">Partner Wallets</div>
                    <div class="stat-value">₹{{ number_format($stats['totalWalletBalances'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.customers') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-info-soft"><i class="bi bi-wallet2 text-info"></i></div>
                <div>
                    <div class="stat-label">Customer Wallets</div>
                    <div class="stat-value text-info">₹{{ number_format($stats['totalCustomerWalletBalances'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.withdrawals') }}'" style="cursor: pointer; transition: transform 0.2s; {{ ($stats['pendingWithdrawals'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-bank text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending Withdrawals</div>
                    <div class="stat-value">{{ number_format($stats['pendingWithdrawals'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.subscriptions') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-purple-soft"><i class="bi bi-card-checklist"></i></div>
                <div>
                    <div class="stat-label">Customer Subs</div>
                    <div class="stat-value">{{ number_format($stats['activeSubscriptions'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.partner-subscriptions') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-indigo-soft" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="stat-label">Partner Subs</div>
                    <div class="stat-value text-indigo" style="color: #6366f1;">{{ number_format($stats['activePartnerSubscriptions'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.transaction-history') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-secondary bg-opacity-10"><i class="bi bi-receipt text-secondary"></i></div>
                <div>
                    <div class="stat-label">Total Invoices</div>
                    <div class="stat-value">{{ number_format($stats['totalInvoices'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.transaction-history') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-success bg-opacity-10"><i class="bi bi-cash text-success"></i></div>
                <div>
                    <div class="stat-label">Total Payments</div>
                    <div class="stat-value">{{ number_format($stats['totalPayments'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.reports.partner-packages') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-primary bg-opacity-10"><i class="bi bi-box text-primary"></i></div>
                <div>
                    <div class="stat-label">Partner Packages</div>
                    <div class="stat-value">{{ number_format($stats['totalPartnerPackages'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Management & Operations -->
    <h6 class="mb-3 text-uppercase text-muted fw-bold" style="font-size: 12px; letter-spacing: 1px;">Users & Operations</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.partners') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-person-workspace"></i></div>
                <div>
                    <div class="stat-label">Active Partners</div>
                    <div class="stat-value">{{ number_format($stats['activePartners'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.customers') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-info-soft"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-label">Active Customers</div>
                    <div class="stat-value">{{ number_format($stats['activeCustomers'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.kyc') }}'" style="cursor: pointer; transition: transform 0.2s; {{ ($stats['pendingKyc'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-shield-check text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending Partner KYC</div>
                    <div class="stat-value">{{ number_format($stats['pendingKyc'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.customer-kyc') }}'" style="cursor: pointer; transition: transform 0.2s; {{ ($stats['pendingCustomerKyc'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-person-vcard text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending Cust. KYC</div>
                    <div class="stat-value">{{ number_format($stats['pendingCustomerKyc'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.kyc.fields') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-secondary bg-opacity-10"><i class="bi bi-ui-checks-grid text-secondary"></i></div>
                <div>
                    <div class="stat-label">KYC Fields</div>
                    <div class="stat-value">{{ number_format($stats['kycFields'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.visits') }}'" style="cursor: pointer; transition: transform 0.2s; {{ ($stats['pendingVisits'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-geo-alt text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending Visits</div>
                    <div class="stat-value">{{ number_format($stats['pendingVisits'] ?? 0) }} <span class="fs-14 fw-normal text-muted">/ {{ $stats['totalVisits'] ?? 0 }}</span></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.attendance') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-success-soft"><i class="bi bi-fingerprint"></i></div>
                <div>
                    <div class="stat-label">Total Attendance</div>
                    <div class="stat-value">{{ number_format($stats['totalAttendance'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Platform Content -->
    <h6 class="mb-3 text-uppercase text-muted fw-bold" style="font-size: 12px; letter-spacing: 1px;">Platform Content</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.categories') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-tags"></i></div>
                <div>
                    <div class="stat-label">Categories</div>
                    <div class="stat-value">{{ number_format($stats['totalCategories'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.banners') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-info-soft"><i class="bi bi-images"></i></div>
                <div>
                    <div class="stat-label">App Banners</div>
                    <div class="stat-value">{{ number_format($stats['totalAppBanners'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.coupons') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-success-soft"><i class="bi bi-ticket-perforated"></i></div>
                <div>
                    <div class="stat-label">Coupons</div>
                    <div class="stat-value">{{ number_format($stats['totalCoupons'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.listings') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-purple-soft"><i class="bi bi-shop"></i></div>
                <div>
                    <div class="stat-label">Total Listings</div>
                    <div class="stat-value">{{ number_format($stats['totalListings'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.reviews') }}'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-star"></i></div>
                <div>
                    <div class="stat-label">Total Reviews</div>
                    <div class="stat-value">{{ number_format($stats['totalReviews'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Security Deposit Overview -->
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size: 12px; letter-spacing: 1px;">Security Deposit Overview</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.reserve-histories') }}'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid #8b5cf6;">
                <div class="stat-icon" style="background: rgba(139,92,246,0.1);"><i class="bi bi-shield-check" style="color:#8b5cf6;"></i></div>
                <div>
                    <div class="stat-label">Total Reserve</div>
                    <div class="stat-value" style="color:#8b5cf6;">{{ number_format($stats['reserveTotal'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.reserve-histories') }}'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid var(--bs-warning);">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-pause-circle text-warning"></i></div>
                <div>
                    <div class="stat-label">Total Hold</div>
                    <div class="stat-value text-warning">{{ number_format($stats['reserveHold'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='{{ route('admin.reserve-histories') }}'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-arrow-return-left text-success"></i></div>
                <div>
                    <div class="stat-label">Total Return</div>
                    <div class="stat-value text-success">{{ number_format($stats['reserveReturn'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════
         CHARTS SECTION
    ════════════════════════════════════════════════ --}}
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size: 12px; letter-spacing: 1px;">Analytics & Charts</h6>
    <div class="row g-4 mb-4" wire:ignore>
        <!-- Monthly Sales Chart -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">Monthly Listing Sales</h6>
                        <small class="text-muted">Booking revenue & count over last 12 months</small>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3">Last 12 Months</span>
                </div>
                <div class="card-body p-4">
                    <canvas id="monthlySalesChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Listing Status Donut -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Listing Status</h6>
                    <small class="text-muted">Distribution by status</small>
                </div>
                <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                    <canvas id="listingStatusChart" height="200" style="max-height:200px;"></canvas>
                    <div class="mt-3 w-100">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><span class="badge" style="background:#6366f1;">&nbsp;</span> Approved</span>
                            <strong>{{ number_format($stats['approvedListings'] ?? 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span><span class="badge bg-warning">&nbsp;</span> Pending</span>
                            <strong>{{ number_format($stats['pendingListings'] ?? 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span><span class="badge bg-secondary">&nbsp;</span> Draft</span>
                            <strong>{{ number_format($stats['draftListings'] ?? 0) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span><span class="badge bg-danger">&nbsp;</span> Rejected</span>
                            <strong>{{ number_format($stats['rejectedListings'] ?? 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4" wire:ignore>
        <!-- Highest Performing Listings Bar Chart -->
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Top Listings by Bookings</h6>
                    <small class="text-muted">Highest performing listings across platform</small>
                </div>
                <div class="card-body p-4">
                    @if(empty($chartData['highestListingsLabels']))
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart fs-1 opacity-25 d-block mb-2"></i>
                            No listing data yet.
                        </div>
                    @else
                        <canvas id="highestListingsChart" height="160"></canvas>
                    @endif
                </div>
            </div>
        </div>

        <!-- Category Usage -->
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Most Used Categories</h6>
                    <small class="text-muted">Platform-wide listings by category</small>
                </div>
                <div class="card-body p-4 d-flex flex-column align-items-center justify-content-center">
                    @if(empty($chartData['categoryLabels']))
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-tag fs-1 opacity-25 d-block mb-2"></i>
                            No categories yet.
                        </div>
                    @else
                        <canvas id="categoryUsageChart" height="200" style="max-height:200px;"></canvas>
                        <div class="mt-4 w-100">
                            @foreach($chartData['categoryLabels'] ?? [] as $index => $catName)
                            @php
                                $colors = ['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'];
                                $colorIdx = $index % count($colors);
                            @endphp
                            <div class="d-flex justify-content-between align-items-center small mb-2">
                                <span><span class="badge" style="background:{{ $colors[$colorIdx] }};">&nbsp;</span> {{ $catName }}</span>
                                <strong class="text-dark">{{ $chartData['categoryData'][$index] }}</strong>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Category Occupancy Chart -->
    <div class="row g-4 mb-4" wire:ignore>
        <div class="col-xl-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Category Occupancy (Occupied vs Available)</h6>
                    <small class="text-muted">Platform-wide occupancy stats by category</small>
                </div>
                <div class="card-body p-4" style="height: 250px;">
                    @if(empty($chartData['occupancyByCategory']))
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart fs-1 opacity-25 d-block mb-2"></i>
                            No occupancy data available.
                        </div>
                    @else
                        <canvas id="occupancyChart"></canvas>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row g-4 mt-1 mb-4">
        <div class="col-xl-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar2-check me-2 text-primary"></i> Recent Bookings</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Customer</th>
                                    <th>Package</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recentBookings'] ?? [] as $booking)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-medium text-dark">{{ $booking->customer->name ?? 'Unknown' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-dark small">{{ $booking->package->name ?? '-' }}</div>
                                    </td>
                                    <td><span class="fw-bold text-success">₹{{ number_format($booking->final_amount) }}</span></td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending_otp' => 'warning',
                                                'pending_payment' => 'warning',
                                                'confirmed' => 'info',
                                                'active' => 'success',
                                                'completed' => 'primary',
                                                'cancelled' => 'danger',
                                                'rejected' => 'danger'
                                            ];
                                            $color = $statusColors[$booking->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}-soft text-{{ $color }} rounded-pill px-2 py-1" style="font-size: 11px;">{{ ucwords(str_replace('_', ' ', $booking->status)) }}</span>
                                    </td>
                                    <td><span class="text-muted small">{{ \Carbon\Carbon::parse($booking->created_at)->format('M d, Y') }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted small">No recent bookings.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-wallet2 me-2 text-warning"></i> Recent Transactions</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">User</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recentTransactions'] ?? [] as $tx)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-medium text-dark">{{ $tx->user->name ?? 'Unknown' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-dark small" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">{{ $tx->description }}</div>
                                    </td>
                                    <td>
                                        @if($tx->type === 'credit')
                                            <span class="fw-bold text-success">+₹{{ number_format($tx->amount) }}</span>
                                        @else
                                            <span class="fw-bold text-danger">-₹{{ number_format($tx->amount) }}</span>
                                        @endif
                                    </td>
                                    <td><span class="text-muted small">{{ \Carbon\Carbon::parse($tx->created_at)->format('M d, g:i A') }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted small">No recent transactions.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscriptions Activity Section -->
    <div class="row g-4 mt-1 mb-4">
        <div class="col-xl-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-arrow-repeat me-2 text-success"></i> Recent Active Subscriptions</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Customer</th>
                                    <th>Package</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recentRenewedSubscriptions'] ?? [] as $sub)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-medium text-dark">{{ $sub->customer->name ?? 'Unknown' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-dark small">{{ $sub->package->name ?? '-' }}</div>
                                    </td>
                                    <td><span class="text-muted small">{{ \Carbon\Carbon::parse($sub->expires_at)->format('M d, Y') }}</span></td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted small">No active subscriptions.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Expiring Subscriptions Section -->
    <div class="row g-4 mt-1 mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 fw-bold text-uppercase text-muted" style="font-size: 12px; letter-spacing: 1px;">Expiring Subscriptions</h6>
                <div style="width: 150px;">
                    <select class="form-select form-select-sm shadow-sm" wire:model.live="expiringFilter">
                        <option value="0">Today</option>
                        <option value="3">Next 3 Days</option>
                        <option value="7">Next 1 Week</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="col-xl-6 mt-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-person-workspace me-2 text-primary"></i> Partner Subscriptions</h6>
                    <a href="{{ route('admin.partner-subscriptions') }}" class="btn btn-sm btn-light fs-12 text-muted fw-medium border shadow-sm">View All <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Partner</th>
                                    <th>Package</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['partnerExpiring'] ?? [] as $sub)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-medium text-dark">{{ $sub->partner->name ?? 'Unknown' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-dark small">{{ $sub->package->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill">{{ \Carbon\Carbon::parse($sub->expires_at)->diffForHumans() }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted small">No expiring partner subscriptions.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-6 mt-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-people me-2 text-info"></i> Customer Subscriptions</h6>
                    <a href="{{ route('admin.reports.subscriptions') }}" class="btn btn-sm btn-light fs-12 text-muted fw-medium border shadow-sm">View All <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Customer</th>
                                    <th>Package</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['customerExpiring'] ?? [] as $sub)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-medium text-dark">{{ $sub->customer->name ?? 'Unknown' }}</div>
                                    </td>
                                    <td>
                                        <div class="text-dark small">{{ $sub->package->name ?? '-' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill">{{ \Carbon\Carbon::parse($sub->expires_at)->diffForHumans() }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4 text-muted small">No expiring customer subscriptions.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    {{-- ════════════════════════════════════════════════
         CHART.JS SCRIPTS
    ════════════════════════════════════════════════ --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('livewire:initialized', function () {
        const chartData = @json($chartData ?? []);
        
        // ── Monthly Sales Chart ───────────────────────────────────────────
        const salesLabels  = chartData.salesLabels || [];
        const salesAmounts = chartData.salesAmounts || [];
        const salesCounts  = chartData.salesCounts || [];

        const salesCtx = document.getElementById('monthlySalesChart');
        if (salesCtx && salesLabels.length > 0) {
            new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: salesLabels,
                    datasets: [
                        {
                            label: 'Revenue (₹)',
                            data: salesAmounts,
                            backgroundColor: 'rgba(99,102,241,0.18)',
                            borderColor: '#6366f1',
                            borderWidth: 2,
                            borderRadius: 8,
                            yAxisID: 'y',
                            order: 2,
                        },
                        {
                            label: 'Bookings Count',
                            data: salesCounts,
                            type: 'line',
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16,185,129,0.15)',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#10b981',
                            pointRadius: 4,
                            tension: 0.4,
                            fill: false,
                            yAxisID: 'y1',
                            order: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, padding: 15, font: { size: 12 } } },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    if (ctx.dataset.yAxisID === 'y') return ' Revenue: ₹' + ctx.parsed.y.toLocaleString();
                                    return ' Bookings: ' + ctx.parsed.y;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            type: 'linear',
                            position: 'left',
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: {
                                callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v),
                                font: { size: 11 }
                            }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { stepSize: 1, font: { size: 11 } }
                        }
                    }
                }
            });
        }

        // ── Listing Status Donut ─────────────────────────────────────────
        const statusCtx = document.getElementById('listingStatusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Pending', 'Draft', 'Rejected'],
                    datasets: [{
                        data: [{{ $stats['approvedListings'] ?? 0 }}, {{ $stats['pendingListings'] ?? 0 }}, {{ $stats['draftListings'] ?? 0 }}, {{ $stats['rejectedListings'] ?? 0 }}],
                        backgroundColor: ['#6366f1','#f59e0b','#94a3b8','#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: ctx => ' ' + ctx.label + ': ' + ctx.parsed
                            }
                        }
                    }
                }
            });
        }

        // ── Highest Listings Bar Chart ───────────────────────────────────
        if (chartData.highestListingsLabels && chartData.highestListingsLabels.length > 0) {
            const hlCtx = document.getElementById('highestListingsChart');
            if (hlCtx) {
                const hlLabels = chartData.highestListingsLabels.map(t => t.length > 20 ? t.substring(0,20) + '…' : t);
                const hlBookings = chartData.highestListingsCounts;
                
                new Chart(hlCtx, {
                    type: 'bar',
                    data: {
                        labels: hlLabels,
                        datasets: [
                            {
                                label: 'Bookings',
                                data: hlBookings,
                                backgroundColor: ['#6366f1','#8b5cf6','#a78bfa','#c4b5fd','#ddd6fe'],
                                borderRadius: 8,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ' Bookings: ' + ctx.parsed.y
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                            y: {
                                position: 'left',
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: { stepSize: 1, font: { size: 11 } }
                            }
                        }
                    }
                });
            }
        }

        // ── Category Usage Donut ─────────────────────────────────────────
        if (chartData.categoryLabels && chartData.categoryLabels.length > 0) {
            const catCtx = document.getElementById('categoryUsageChart');
            if (catCtx) {
                new Chart(catCtx, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.categoryLabels,
                        datasets: [{
                            data: chartData.categoryData,
                            backgroundColor: ['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'],
                            borderWidth: 0,
                            hoverOffset: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '72%',
                        plugins: {
                            legend: { display: false },
                        }
                    }
                });
            }
        }

        // ── Category Occupancy (Stacked Bar) ─────────────────────────────
        if (chartData.occupancyByCategory && chartData.occupancyByCategory.length > 0) {
            const occCtx = document.getElementById('occupancyChart');
            if (occCtx) {
                const occLabels = chartData.occupancyByCategory.map(item => item.category.length > 20 ? item.category.substring(0,20) + '…' : item.category);
                const occOccupied = chartData.occupancyByCategory.map(item => item.occupied);
                const occAvailable = chartData.occupancyByCategory.map(item => item.available);
                
                new Chart(occCtx, {
                    type: 'bar',
                    data: {
                        labels: occLabels,
                        datasets: [
                            {
                                label: 'Occupied',
                                data: occOccupied,
                                backgroundColor: 'rgba(239, 68, 68, 0.85)', // red
                                borderRadius: 4,
                                maxBarThickness: 40,
                            },
                            {
                                label: 'Available',
                                data: occAvailable,
                                backgroundColor: 'rgba(16, 185, 129, 0.85)', // green
                                borderRadius: 4,
                                maxBarThickness: 40,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { usePointStyle: true, font: { size: 12 } } },
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        },
                        scales: {
                            x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } },
                            y: {
                                stacked: true,
                                position: 'left',
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: { stepSize: 1, font: { size: 11 } }
                            }
                        }
                    }
                });
            }
        }
    });
    </script>
    @endpush
