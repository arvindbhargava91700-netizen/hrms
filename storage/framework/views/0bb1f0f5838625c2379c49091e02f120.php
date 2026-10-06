<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Welcome back, <?php echo e(auth()->user()->name); ?>!</h4>
            <p class="text-muted mb-0"><?php echo e(now()->format('l, jS F Y')); ?></p>
        </div>
        <div class="d-flex gap-2">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('leave_create')): ?>
                <a href="<?php echo e(route('partner.hrms.leaves.apply')); ?>" class="btn btn-light shadow-sm border"><i class="bi bi-umbrella me-1"></i> Apply Leave</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('task_create')): ?>
                <a href="<?php echo e(route('partner.hrms.tasks')); ?>" class="btn btn-light shadow-sm border"><i class="bi bi-list-task me-1"></i> New Task</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() && auth()->user()->kycDocument?->status !== 'approved'): ?>
        <div class="alert alert-warning mb-4 d-flex align-items-center gap-3 rounded-4 border-0 shadow-sm">
            <div class="bg-warning bg-opacity-25 p-2 rounded-circle text-warning"><i class="bi bi-exclamation-triangle fs-4"></i></div>
            <div>
                <div class="fw-bold text-dark">KYC Verification <?php echo e(auth()->user()->kycDocument ? 'Pending' : 'Required'); ?></div>
                <div class="text-muted" style="font-size:13px;">Your account functionality is limited until your KYC and Bank details are verified.</div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!auth()->user()->kycDocument): ?>
                <a href="<?php echo e(route('partner.kyc')); ?>" class="btn btn-sm btn-dark ms-auto rounded-pill px-3">Complete KYC</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <h6 class="mb-3 text-uppercase text-muted fw-bold" style="font-size: 12px; letter-spacing: 1px;">Listing Overview</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.listings')); ?>'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid #6366f1;">
                <div class="stat-icon" style="background: rgba(99,102,241,0.1);"><i class="bi bi-grid-3x3-gap" style="color:#6366f1;"></i></div>
                <div>
                    <div class="stat-label">Total Listings</div>
                    <div class="stat-value" style="color:#6366f1;"><?php echo e(number_format($totalListings)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.listings')); ?>'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-check2-circle text-success"></i></div>
                <div>
                    <div class="stat-label">Approved</div>
                    <div class="stat-value text-success"><?php echo e(number_format($approvedListings)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.listings')); ?>'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-warning);">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-hourglass-split text-warning"></i></div>
                <div>
                    <div class="stat-label">Total Pending</div>
                    <div class="stat-value text-warning"><?php echo e(number_format($pendingListings)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.listings')); ?>'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid #64748b;">
                <div class="stat-icon" style="background: rgba(100,116,139,0.1);"><i class="bi bi-pencil-square" style="color:#64748b;"></i></div>
                <div>
                    <div class="stat-label">Total Draft</div>
                    <div class="stat-value" style="color:#64748b;"><?php echo e(number_format($draftListings)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.listings')); ?>'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-danger);">
                <div class="stat-icon" style="background: rgba(220,53,69,0.1);"><i class="bi bi-x-circle text-danger"></i></div>
                <div>
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value text-danger"><?php echo e(number_format($rejectedListings)); ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size: 12px; letter-spacing: 1px;">Booking Overview</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.bookings')); ?>'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid #3b82f6;">
                <div class="stat-icon" style="background: rgba(59,130,246,0.1);"><i class="bi bi-calendar2-check" style="color:#3b82f6;"></i></div>
                <div>
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-value" style="color:#3b82f6;"><?php echo e(number_format($stats['bookingStats']['total'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.bookings')); ?>'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-warning);">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-shield-lock text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending OTP</div>
                    <div class="stat-value text-warning"><?php echo e(number_format($stats['bookingStats']['pending_otp'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.bookings')); ?>'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-info);">
                <div class="stat-icon bg-info-soft"><i class="bi bi-credit-card text-info"></i></div>
                <div>
                    <div class="stat-label">Confirmed</div>
                    <div class="stat-value text-info"><?php echo e(number_format($stats['bookingStats']['confirmed'] ?? 0)); ?></div>
                </div>
            </div>
        </div>

        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.bookings')); ?>'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid #10b981;">
                <div class="stat-icon" style="background: rgba(16,185,129,0.1);"><i class="bi bi-check-all" style="color:#10b981;"></i></div>
                <div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-value" style="color:#10b981;"><?php echo e(number_format($stats['bookingStats']['completed'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.bookings')); ?>'" style="cursor: pointer; transition: transform 0.2s;" style="border-left: 4px solid var(--bs-danger);">
                <div class="stat-icon" style="background: rgba(220,53,69,0.1);"><i class="bi bi-x-circle text-danger"></i></div>
                <div>
                    <div class="stat-label">Cancelled</div>
                    <div class="stat-value text-danger"><?php echo e(number_format($stats['bookingStats']['cancelled'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <h6 class="mb-3 text-uppercase text-muted fw-bold" style="font-size: 12px; letter-spacing: 1px;">Finance & Earnings</h6>
    <div class="row g-3 mb-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('wallet_viewAny') || auth()->user()->canAccess('wallet_viewOwn')): ?>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.wallet')); ?>'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-success-soft"><i class="bi bi-wallet2 text-success"></i></div>
                <div>
                    <div class="stat-label">Wallet Balance</div>
                    <div class="stat-value">₹<?php echo e(number_format($stats['walletBalance'] ?? 0, 2)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.wallet')); ?>'" style="cursor: pointer; transition: transform 0.2s; <?php echo e(($stats['pendingWithdrawals'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : ''); ?>">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-hourglass-split text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending Withdrawals</div>
                    <div class="stat-value">₹<?php echo e(number_format($stats['pendingWithdrawals'] ?? 0, 2)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.transaction-history')); ?>'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid var(--bs-primary);">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-currency-rupee text-primary"></i></div>
                <div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value text-primary">₹<?php echo e(number_format($stats['totalRevenue'] ?? 0, 2)); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('customer_viewAny') || auth()->user()->canAccess('customer_viewOwn')): ?>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.customers')); ?>'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-info-soft"><i class="bi bi-people text-info"></i></div>
                <div>
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-value"><?php echo e(number_format($stats['activeCustomers'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('subscription_viewAny') || auth()->user()->canAccess('subscription_viewOwn')): ?>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.subscriptions')); ?>'" style="cursor: pointer; transition: transform 0.2s;">
                <div class="stat-icon bg-purple-soft"><i class="bi bi-card-checklist"></i></div>
                <div>
                    <div class="stat-label">Active Subs</div>
                    <div class="stat-value"><?php echo e(number_format($stats['activeSubscriptions'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Security Deposit Overview -->
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size: 12px; letter-spacing: 1px;">Security Deposit Overview</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.reserve-histories')); ?>'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid #8b5cf6;">
                <div class="stat-icon" style="background: rgba(139,92,246,0.1);"><i class="bi bi-shield-check" style="color:#8b5cf6;"></i></div>
                <div>
                    <div class="stat-label">Total Reserve</div>
                    <div class="stat-value" style="color:#8b5cf6;"><?php echo e(number_format($stats['reserveTotal'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.reserve-histories')); ?>'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid var(--bs-warning);">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-pause-circle text-warning"></i></div>
                <div>
                    <div class="stat-label">Total Hold</div>
                    <div class="stat-value text-warning"><?php echo e(number_format($stats['reserveHold'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.reserve-histories')); ?>'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-arrow-return-left text-success"></i></div>
                <div>
                    <div class="stat-label">Total Return</div>
                    <div class="stat-value text-success"><?php echo e(number_format($stats['reserveReturn'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
    </div>

    
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size: 12px; letter-spacing: 1px;">Analytics & Charts</h6>
    <div class="row g-4 mb-4">
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
                            <strong><?php echo e($approvedListings); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span><span class="badge bg-warning">&nbsp;</span> Pending</span>
                            <strong><?php echo e($pendingListings); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                            <span><span class="badge bg-secondary">&nbsp;</span> Draft</span>
                            <strong><?php echo e($draftListings); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span><span class="badge bg-danger">&nbsp;</span> Rejected</span>
                            <strong><?php echo e($rejectedListings); ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Highest Performing Listings Bar Chart -->
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Top Listings by Bookings</h6>
                    <small class="text-muted">Highest performing listings based on total bookings</small>
                </div>
                <div class="card-body p-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($highestListings->isEmpty()): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart fs-1 opacity-25 d-block mb-2"></i>
                            No listing data yet.
                        </div>
                    <?php else: ?>
                        <canvas id="highestListingsChart" height="160"></canvas>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Category Usage -->
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Category Services Used</h6>
                    <small class="text-muted">Total listings by category</small>
                </div>
                <div class="card-body p-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($categoryUsage->isEmpty()): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-tag fs-1 opacity-25 d-block mb-2"></i>
                            No categories yet.
                        </div>
                    <?php else: ?>
                        <?php $maxCat = $categoryUsage->max('listings_count') ?: 1; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $categoryUsage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $percent = round(($cat->listings_count / $maxCat) * 100);
                            $colors = ['#6366f1','#06b6d4','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'];
                            $colorIdx = $loop->index % count($colors);
                        ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-semibold text-dark"><?php echo e($cat->name); ?></span>
                                <span class="badge rounded-pill text-white" style="background:<?php echo e($colors[$colorIdx]); ?>; font-size:10px;"><?php echo e($cat->listings_count); ?> listing<?php echo e($cat->listings_count != 1 ? 's' : ''); ?></span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 99px;">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo e($percent); ?>%; background: <?php echo e($colors[$colorIdx]); ?>; border-radius: 99px;" aria-valuenow="<?php echo e($percent); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                    <small class="text-muted">Occupancy stats for your listings by category</small>
                </div>
                <div class="card-body p-4" style="height: 250px;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($chartData['occupancyByCategory'])): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart fs-1 opacity-25 d-block mb-2"></i>
                            No occupancy data available.
                        </div>
                    <?php else: ?>
                        <canvas id="occupancyChart"></canvas>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($highestListings->isNotEmpty()): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 rounded-top-4">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-trophy me-2 text-warning"></i>Highest Performing Listings</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 border-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 border-0 text-muted fw-semibold small">#</th>
                            <th class="border-0 text-muted fw-semibold small">Listing Name</th>
                            <th class="border-0 text-muted fw-semibold small">Category</th>
                            <th class="border-0 text-muted fw-semibold small">Status</th>
                            <th class="border-0 text-muted fw-semibold small">Total Bookings</th>
                            <th class="border-0 text-muted fw-semibold small">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $highestListings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $listing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td class="ps-4 border-0 py-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($i === 0): ?>
                                    <span class="badge bg-warning text-dark rounded-circle p-2"><i class="bi bi-trophy-fill" style="font-size:12px;"></i></span>
                                <?php elseif($i === 1): ?>
                                    <span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-award-fill" style="font-size:12px;"></i></span>
                                <?php elseif($i === 2): ?>
                                    <span class="badge" style="background:#cd7f32; border-radius:50%; padding:6px 8px;"><i class="bi bi-award" style="font-size:12px;"></i></span>
                                <?php else: ?>
                                    <span class="text-muted fw-bold"><?php echo e($i + 1); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="border-0 py-3">
                                <div class="fw-semibold text-dark"><?php echo e($listing->title); ?></div>
                                <small class="text-muted"><?php echo e($listing->city); ?>, <?php echo e($listing->state); ?></small>
                            </td>
                            <td class="border-0 py-3">
                                <span class="badge bg-light text-dark border"><?php echo e($listing->category->name ?? '—'); ?></span>
                            </td>
                            <td class="border-0 py-3">
                                <?php
                                    $sc = match($listing->status) {
                                        'approved' => 'success',
                                        'pending'  => 'warning',
                                        'draft'    => 'secondary',
                                        'rejected' => 'danger',
                                        default    => 'secondary',
                                    };
                                ?>
                                <span class="badge bg-<?php echo e($sc); ?> bg-opacity-10 text-<?php echo e($sc); ?> border border-<?php echo e($sc); ?> border-opacity-25 rounded-pill text-capitalize px-2 py-1" style="font-size:11px;"><?php echo e($listing->status); ?></span>
                            </td>
                            <td class="border-0 py-3">
                                <span class="fw-bold"><?php echo e(number_format($listing->booking_count)); ?></span>
                            </td>
                            <td class="border-0 py-3">
                                <span class="fw-bold text-success">₹<?php echo e(number_format($listing->total_revenue ?? 0)); ?></span>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasHrms && (auth()->user()->canAccess('staff_viewAny') || auth()->user()->canAccess('attendance_viewAny') || auth()->user()->canAccess('department_viewTeam') || auth()->user()->role === 'partner')): ?>
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-5" style="font-size: 12px; letter-spacing: 1px;">HRMS Module Overview</h6>

    <!-- HRMS Quick Stats -->
    <div class="row g-3 mb-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('staff_viewAny') || auth()->user()->canAccess('attendance_viewAny')): ?>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.hrms.staff')); ?>'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid #6366f1;">
                <div class="stat-icon" style="background:rgba(99,102,241,.12);"><i class="bi bi-people" style="color:#6366f1;"></i></div>
                <div>
                    <div class="stat-label">Total Company Staff</div>
                    <div class="stat-value" style="color:#6366f1;"><?php echo e(number_format($stats['hrmsStaff'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.hrms.attendance.manage')); ?>'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid #10b981;">
                <div class="stat-icon" style="background:rgba(16,185,129,.12);"><i class="bi bi-calendar-check" style="color:#10b981;"></i></div>
                <div>
                    <div class="stat-label">Attendance Today</div>
                    <div class="stat-value" style="color:#10b981;"><?php echo e(number_format($stats['hrmsAttendanceToday'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.hrms.departments')); ?>'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid #f59e0b;">
                <div class="stat-icon" style="background:rgba(245,158,11,.12);"><i class="bi bi-diagram-3" style="color:#f59e0b;"></i></div>
                <div>
                    <div class="stat-label">Departments</div>
                    <div class="stat-value" style="color:#f59e0b;"><?php echo e(number_format($stats['hrmsDepts'] ?? 0)); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($stats['teamTotalTarget'])): ?>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.hrms.my-targets')); ?>'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid #8b5cf6;">
                <div class="stat-icon" style="background:rgba(139,92,246,.12);"><i class="bi bi-bullseye" style="color:#8b5cf6;"></i></div>
                <div>
                    <div class="stat-label">Team Target (Month)</div>
                    <div class="stat-value" style="color:#8b5cf6;">₹<?php echo e(number_format($stats['teamTotalTarget'] ?? 0, 0)); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($stats['teamTotalBusiness'])): ?>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card hover-lift" onclick="window.location.href='<?php echo e(route('partner.hrms.my-targets')); ?>'" style="cursor: pointer; transition: transform 0.2s; border-left: 4px solid #14b8a6;">
                <div class="stat-icon" style="background:rgba(20,184,166,.12);"><i class="bi bi-graph-up-arrow" style="color:#14b8a6;"></i></div>
                <div>
                    <div class="stat-label">Team Business (Month)</div>
                    <div class="stat-value" style="color:#14b8a6;">₹<?php echo e(number_format($stats['teamTotalBusiness'] ?? 0, 0)); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('task_viewAny') || auth()->user()->canAccess('task_viewTeam') || auth()->user()->role === 'partner'): ?>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <a href="<?php echo e(route('partner.hrms.tasks')); ?>" class="text-decoration-none">
                <div class="stat-card" style="border-left: 4px solid #ec4899; cursor:pointer; transition:.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    <div class="stat-icon" style="background:rgba(236,72,153,.12);"><i class="bi bi-list-task" style="color:#ec4899;"></i></div>
                    <div>
                        <div class="stat-label">Pending Tasks</div>
                        <div class="stat-value" style="color:#ec4899;"><?php echo e(number_format(isset($stats['recentTasks']) ? $stats['recentTasks']->where('status','pending')->count() : 0)); ?></div>
                    </div>
                </div>
            </a>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- HRMS Quick Action Modules -->
    <div class="row g-3 mb-4">
        <?php
        $hrmsModules = [
            ['label'=>'Staff Management','icon'=>'bi-people-fill','color'=>'#6366f1','bg'=>'rgba(99,102,241,.1)','route'=>'partner.hrms.staff','perm'=>'staff_viewAny'],
            ['label'=>'Attendance','icon'=>'bi-calendar-check-fill','color'=>'#10b981','bg'=>'rgba(16,185,129,.1)','route'=>'partner.hrms.attendance.manage','perm'=>'attendance_viewAny'],
            ['label'=>'Departments','icon'=>'bi-diagram-3-fill','color'=>'#f59e0b','bg'=>'rgba(245,158,11,.1)','route'=>'partner.hrms.departments','perm'=>'department_viewTeam'],
            ['label'=>'Tasks','icon'=>'bi-list-task','color'=>'#ec4899','bg'=>'rgba(236,72,153,.1)','route'=>'partner.hrms.tasks','perm'=>'task_viewAny'],
            ['label'=>'Leaves','icon'=>'bi-umbrella-fill','color'=>'#06b6d4','bg'=>'rgba(6,182,212,.1)','route'=>'partner.hrms.leaves.my-requests','perm'=>'leave_viewAny'],
            ['label'=>'Payroll','icon'=>'bi-cash-coin','color'=>'#8b5cf6','bg'=>'rgba(139,92,246,.1)','route'=>'partner.hrms.payroll.my','perm'=>'payroll_viewAny'],
            ['label'=>'Expenses','icon'=>'bi-receipt','color'=>'#ef4444','bg'=>'rgba(239,68,68,.1)','route'=>'partner.hrms.expenses','perm'=>'expense_viewAny'],
            ['label'=>'Notices','icon'=>'bi-megaphone-fill','color'=>'#f97316','bg'=>'rgba(249,115,22,.1)','route'=>'partner.hrms.notices','perm'=>null],
            ['label'=>'Employee Targets','icon'=>'bi-bullseye','color'=>'#0ea5e9','bg'=>'rgba(14,165,233,.1)','route'=>'partner.hrms.my-targets','perm'=>'staff_viewAny'],
            ['label'=>'Roles & Permissions','icon'=>'bi-shield-check','color'=>'#475569','bg'=>'rgba(71,85,105,.1)','route'=>'partner.hrms.roles','perm'=>'role_viewAny'],
        ];
        ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $hrmsModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$mod['perm'] || auth()->user()->canAccess($mod['perm']) || auth()->user()->role === 'partner'): ?>
            <div class="col-xl-2 col-md-3 col-sm-4 col-6">
                <a href="<?php echo e(route($mod['route'])); ?>" class="text-decoration-none">
                    <div class="card border-0 shadow-sm rounded-4 h-100 text-center p-3" style="transition:.25s; cursor:pointer;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 25px rgba(0,0,0,.12)'" onmouseout="this.style.transform='none';this.style.boxShadow=''">
                        <div class="d-flex align-items-center justify-content-center mx-auto mb-2 rounded-3" style="width:52px;height:52px;background:<?php echo e($mod['bg']); ?>;">
                            <i class="bi <?php echo e($mod['icon']); ?> fs-4" style="color:<?php echo e($mod['color']); ?>;"></i>
                        </div>
                        <div class="fw-semibold text-dark" style="font-size:12px;"><?php echo e($mod['label']); ?></div>
                    </div>
                </a>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($stats['teamAchievers'])): ?>
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3 mt-4">
        <h6 class="fw-bold text-secondary mb-0 text-uppercase" style="font-size:0.85rem; letter-spacing:0.5px;">Top 10 Team Performance (All)</h6>
    </div>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-flex align-items-center justify-content-center rounded-3 bg-primary bg-opacity-10 text-primary" style="width:34px;height:34px;"><i class="bi bi-funnel"></i></span>
                    <div><div class="fw-bold text-dark">Performance Filters</div><small class="text-muted">Filter the top performers by team details</small></div>
                </div>
                <button type="button" class="btn btn-sm btn-light border" wire:click="$set('performanceBranch', '') ; $set('performanceDepartment', '') ; $set('performanceEmployee', '')" title="Clear filters"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
            </div>
            <div class="row g-3">
                <div class="col-xl-4 col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-building me-1"></i>Branch</label>
                    <select wire:model.live="performanceBranch" class="form-select">
                        <option value="">All Branches</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats['performanceBranches'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="col-xl-4 col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-diagram-3 me-1"></i>Department</label>
                    <select wire:model.live="performanceDepartment" class="form-select">
                        <option value="">All Departments</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats['performanceDepartments'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $department): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($department->id); ?>"><?php echo e($department->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
                <div class="col-xl-4 col-md-12">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-person me-1"></i>Employee</label>
                    <select wire:model.live="performanceEmployee" class="form-select">
                        <option value="">All Employees</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats['performanceEmployees'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($employee->id); ?>"><?php echo e($employee->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-y-auto">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table report-table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-4 border-top-0">Employee</th>
                                    <th class="border-top-0">Department</th>
                                    <!-- <th class="text-center border-top-0">Attendance <small class="d-block text-muted">25 pts</small></th>
                                    <th class="text-center border-top-0">Tasks <small class="d-block text-muted">25 pts</small></th>
                                    <th class="text-center border-top-0">Merchant Target <small class="d-block text-muted">25 pts</small></th>
                                    <th class="text-center border-top-0">Monthly Target <small class="d-block text-muted">25 pts</small></th> -->
                                    <th class="text-center border-top-0">Total Score <small class="d-block text-muted">100 pts</small></th>
                                    <th class="text-center border-top-0">Grade</th>
                                    <th class="text-center border-top-0">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stats['teamAchievers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold"><?php echo e($row['name']); ?></div><small class="text-muted"><?php echo e($row['employee_code'] ?: 'No employee code'); ?></small>
                                    </td>
                                    <td class="text-muted"><?php echo e($row['department']); ?></td>
                                    <!-- <td class="text-center" style="min-width:130px;">
                                        <div class="fw-bold fs-5 text-dark"><?php echo e($row['attendance_score']); ?></div><small class="text-muted"><?php echo e($row['attendance']); ?>/<?php echo e($row['days_in_month']); ?> days</small>
                                        <div class="progress mx-auto mt-1" style="height:4px;max-width:130px;">
                                            <div class="progress-bar bg-success" style="width:<?php echo e(($row['attendance_score'] / 25) * 100); ?>%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center" style="min-width:130px;">
                                        <div class="fw-bold fs-5 text-dark"><?php echo e($row['task_score']); ?></div><small class="text-muted"><?php echo e($row['completed_tasks']); ?>/<?php echo e(max(1, $row['tasks'])); ?> tasks</small>
                                        <div class="progress mx-auto mt-1" style="height:4px;max-width:130px;">
                                            <div class="progress-bar bg-primary" style="width:<?php echo e(($row['task_score'] / 25) * 100); ?>%;"></div>
                                        </div>
                                    </td>
                                
                                    <td class="text-center" style="min-width:130px;">
                                        <?php $merchantPercentage = $row['merchant_target'] > 0 ? min(100, ($row['merchants'] / $row['merchant_target']) * 100) : 0; ?>
                                        <div class="fw-bold fs-5 text-dark"><?php echo e(number_format($row['merchants'], 0)); ?>/<?php echo e(number_format($row['merchant_target'], 0)); ?></div>
                                        <small class="text-muted"><?php echo e(number_format($merchantPercentage, 1)); ?>% · <?php echo e($row['merchant_score']); ?>/25</small>
                                        <div class="progress mx-auto mt-1" style="height:4px;max-width:130px;"><div class="progress-bar bg-info" style="width:<?php echo e($merchantPercentage); ?>%;"></div></div>
                                    </td>
                                    <td class="text-center" style="min-width:155px;">
                                        <div class="fw-bold">₹<?php echo e(number_format($row['monthly_achieved'], 2)); ?> / ₹<?php echo e(number_format($row['monthly_target'], 2)); ?></div>
                                        <small class="text-muted"><?php echo e(number_format($row['monthly_percentage'], 1)); ?>% · <?php echo e($row['monthly_target_score']); ?>/25</small>
                                        <div class="progress mx-auto mt-1" style="height:4px;max-width:150px;"><div class="progress-bar bg-warning" style="width:<?php echo e($row['monthly_percentage']); ?>%;"></div></div>
                                    </td> -->
                                    <td class="text-center" style="min-width:100px;">
                                        <div class="fw-bold fs-5 text-<?php echo e($row['total_score'] >= 70 ? 'primary' : ($row['total_score'] >= 60 ? 'warning' : 'danger')); ?>"><?php echo e($row['total_score']); ?></div>
                                        <div class="progress mx-auto mt-1" style="height:5px;max-width:60px;">
                                            <div class="progress-bar bg-<?php echo e($row['total_score'] >= 70 ? 'primary' : ($row['total_score'] >= 60 ? 'warning' : 'danger')); ?>" style="width:<?php echo e(min(100, $row['total_score'])); ?>%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-<?php echo e($row['grade'] === 'A+' ? 'success' : ($row['grade'] === 'A' ? 'primary' : ($row['grade'] === 'B+' ? 'info' : 'warning'))); ?>"><?php echo e($row['grade']); ?></span></td>
                                
                                    <td class="text-center"><a href="<?php echo e(route('partner.hrms.report.performance-profile', ['id' => $row['employee_id']])); ?>" class="btn btn-sm btn-outline-primary" title="View employee profile"><i class="bi bi-eye"></i><span class="visually-hidden">View</span></a></td>
                                
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">No performance data found.</td>
                                </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <!-- Department Performance -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($stats['departmentPerformance'])): ?>
    <h6 class="fw-bold text-secondary mb-3 mt-4 text-uppercase" style="font-size:0.85rem; letter-spacing:0.5px;">Department Overview</h6>
    <div class="row g-4 mb-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stats['departmentPerformance']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="col-xl-6 col-md-6">
                <div class="card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-diagram-3 fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold"><?php echo e($dept['name']); ?></h6>
                                    <small class="text-muted">Head: <?php echo e($dept['head']); ?></small>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border"><i class="bi bi-people me-1"></i> <?php echo e($dept['staffCount']); ?> Staff</span>
                        </div>
                        <?php
                            $attPct = $dept['staffCount'] > 0 ? round(($dept['attendanceToday'] / $dept['staffCount']) * 100) : 0;
                        ?>
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small mb-1">Today's Attendance</div>
                            <div class="fw-bold text-dark fs-5"><?php echo e($dept['attendanceToday']); ?> <span class="fs-6 text-muted fw-normal">/ <?php echo e($dept['staffCount']); ?></span></div>
                            <div class="progress mt-2" style="height:6px;">
                                <div class="progress-bar bg-success" style="width:<?php echo e($attPct); ?>%;"></div>
                            </div>
                            <div class="text-end text-muted small mt-1"><?php echo e($attPct); ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="col-12">
                <div class="p-4 text-center bg-light rounded-4 border text-muted small">
                    <i class="bi bi-diagram-3 fs-3 d-block mb-2 text-secondary opacity-50"></i>
                    No department data available.
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($stats['branchAchievers']) && $stats['branchAchievers']->count() > 0): ?>
    <h6 class="fw-bold text-secondary mb-3 mt-4 text-uppercase" style="font-size:0.85rem; letter-spacing:0.5px;">Top Team Achievers (By Branch)</h6>
    <div class="row g-4 mb-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats['branchAchievers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branchName => $achievers): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="col-xl-4 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-bottom py-3 rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-building text-primary me-2"></i><?php echo e($branchName); ?></h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-bottom-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $achievers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $achiever): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="list-group-item p-3 border-bottom-0 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                            <div class="d-flex align-items-center gap-3">
                                <div class="position-relative">
                                    <img src="<?php echo e($achiever['user']->avatar_url); ?>" alt="<?php echo e($achiever['user']->name); ?>" class="rounded-circle object-fit-cover shadow-sm" width="45" height="45">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($index === 0): ?>
                                    <div class="position-absolute bottom-0 end-0 bg-white rounded-circle shadow-sm" style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;transform:translate(25%, 25%);">
                                        <i class="bi bi-trophy-fill" style="color: #fbbf24; font-size: 12px;"></i>
                                    </div>
                                    <?php elseif($index === 1): ?>
                                    <div class="position-absolute bottom-0 end-0 bg-white rounded-circle shadow-sm" style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;transform:translate(25%, 25%);">
                                        <i class="bi bi-trophy-fill" style="color: #9ca3af; font-size: 12px;"></i>
                                    </div>
                                    <?php elseif($index === 2): ?>
                                    <div class="position-absolute bottom-0 end-0 bg-white rounded-circle shadow-sm" style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;transform:translate(25%, 25%);">
                                        <i class="bi bi-trophy-fill" style="color: #b45309; font-size: 12px;"></i>
                                    </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <h6 class="fw-bold mb-0 text-truncate text-dark" style="font-size:14px;"><?php echo e($achiever['user']->name); ?></h6>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <div class="d-flex flex-column">
                                            <div class="text-success fw-bold small" style="font-size:13px;">₹<?php echo e(number_format($achiever['business'], 0)); ?> <span class="text-muted fw-normal" style="font-size:11px;">Achieved</span></div>
                                            <div class="text-muted fw-medium" style="font-size:11px;">₹<?php echo e(number_format($achiever['target'], 0)); ?> Target</div>
                                        </div>
                                        <span class="badge bg-light text-secondary border small"><?php echo e(number_format($achiever['pct'], 1)); ?>%</span>
                                    </div>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar <?php echo e($achiever['pct'] >= 100 ? 'bg-success' : 'bg-primary'); ?>" role="progressbar" style="width: <?php echo e(min(100, $achiever['pct'])); ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="p-4 text-center text-muted small">No achievers found yet.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Recent Tasks from HRMS -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($stats['recentTasks']) && $stats['recentTasks']->count() > 0): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-list-task me-2 text-primary"></i>Recent Tasks (HRMS)</h6>
            <a href="<?php echo e(route('partner.hrms.tasks')); ?>" class="btn btn-sm btn-light rounded-pill px-3">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 border-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 border-0 text-muted fw-semibold small">Task</th>
                            <th class="border-0 text-muted fw-semibold small">Assigned To</th>
                            <th class="border-0 text-muted fw-semibold small">Due Date</th>
                            <th class="border-0 text-muted fw-semibold small">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats['recentTasks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td class="ps-4 border-0 py-3">
                                <div class="fw-medium text-dark"><?php echo e($task->title); ?></div>
                            </td>
                            <td class="border-0 py-3">
                                <span class="text-muted small"><?php echo e($task->employee?->name ?? '—'); ?></span>
                            </td>
                            <td class="border-0 py-3">
                                <span class="text-muted small"><i class="bi bi-calendar-event me-1"></i><?php echo e(\Carbon\Carbon::parse($task->due_date)->format('M d, Y')); ?></span>
                            </td>
                            <td class="border-0 py-3">
                                <?php
                                    $bc = match($task->status) {
                                        'completed'   => 'success',
                                        'in_progress' => 'primary',
                                        'pending'     => 'warning',
                                        default       => 'secondary'
                                    };
                                ?>
                                <span class="badge bg-<?php echo e($bc); ?> bg-opacity-10 text-<?php echo e($bc); ?> border border-<?php echo e($bc); ?> border-opacity-25 rounded-pill text-capitalize px-2 py-1" style="font-size:11px;">
                                    <?php echo e(str_replace('_', ' ', $task->status)); ?>

                                </span>
                            </td>
                        </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($myTarget) && $myTarget): ?>
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-5" style="font-size: 12px; letter-spacing: 1px;">My Targets (<?php echo e(now()->format('F Y')); ?>)</h6>
    <div class="row g-4 mb-4">
        <div class="col-xl-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 text-success"><i class="bi bi-currency-dollar me-2"></i>Monthly Business Target</h5>
                    <?php
                        $busPercentage = $myTarget->business_target > 0 ? ($myTarget->business_achieved / $myTarget->business_target) * 100 : 0;
                        $busPercentage = min(100, max(0, $busPercentage));
                    ?>
                    <div class="d-flex justify-content-between text-muted fw-medium mb-2">
                        <span>Achieved: ₹<?php echo e(number_format($myTarget->business_achieved, 2)); ?></span>
                        <span>Target: ₹<?php echo e(number_format($myTarget->business_target, 2)); ?></span>
                    </div>
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo e($busPercentage); ?>%" aria-valuenow="<?php echo e($busPercentage); ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="text-end text-muted small fw-medium"><?php echo e(number_format($busPercentage, 1)); ?>% Completed</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'partner' || auth()->user()->canAccess('subscription_viewAny')): ?>
    <h5 class="fw-bold text-dark mb-3 mt-5">Subscriptions & Transactions</h5>
    <div class="row g-4 mt-1 mb-4">
        <div class="col-xl-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Recent Bookings</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0 text-muted fw-semibold small">Customer</th>
                                    <th class="border-0 text-muted fw-semibold small">Package</th>
                                    <th class="border-0 text-muted fw-semibold small">Amount</th>
                                    <th class="border-0 text-muted fw-semibold small">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stats['recentBookings'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td class="ps-4 border-0 py-3">
                                        <div class="fw-medium text-dark"><?php echo e($booking->customer->name ?? 'Unknown'); ?></div>
                                        <span class="text-muted small"><?php echo e(\Carbon\Carbon::parse($booking->created_at)->format('M d, Y')); ?></span>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="text-dark small"><?php echo e($booking->package->name ?? '-'); ?></div>
                                    </td>
                                    <td class="border-0 py-3"><span class="fw-bold text-success">₹<?php echo e(number_format($booking->final_amount)); ?></span></td>
                                    <td class="border-0 py-3">
                                        <?php
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
                                        ?>
                                        <span class="badge bg-<?php echo e($color); ?>-soft text-<?php echo e($color); ?> rounded-pill px-2 py-1" style="font-size: 11px;"><?php echo e(ucwords(str_replace('_', ' ', $booking->status))); ?></span>
                                    </td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted small">No recent bookings.</td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Recent Wallet Transactions</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0 text-muted fw-semibold small">Description</th>
                                    <th class="border-0 text-muted fw-semibold small">Amount</th>
                                    <th class="border-0 text-muted fw-semibold small">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stats['recentTransactions'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td class="ps-4 border-0 py-3">
                                        <div class="text-dark small" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;"><?php echo e($tx->description); ?></div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tx->type === 'credit'): ?>
                                            <span class="fw-bold text-success">+₹<?php echo e(number_format($tx->amount)); ?></span>
                                        <?php else: ?>
                                            <span class="fw-bold text-danger">-₹<?php echo e(number_format($tx->amount)); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3"><span class="text-muted small"><?php echo e(\Carbon\Carbon::parse($tx->created_at)->format('M d, g:i A')); ?></span></td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="3" class="text-center py-5 text-muted small">No recent transactions.</td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'partner' || auth()->user()->canAccess('subscription_viewAny')): ?>
    <div class="row g-4 mt-1 mb-4">
        <div class="col-12">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Expiring & Expired Subscriptions</h6>
                    <div class="d-flex align-items-center gap-2">
                        <select class="form-select form-select-sm shadow-sm" wire:model.live="expiringFilter" style="width: 140px;">
                            <option value="0">Today</option>
                            <option value="3">Next 3 Days</option>
                            <option value="7">Next 1 Week</option>
                            <option value="expired">Expired</option>
                        </select>
                        <a href="<?php echo e(route('partner.subscriptions')); ?>?expireFilter=expired" class="btn btn-sm btn-light rounded-pill px-3 d-none d-sm-inline-block">View All</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0 text-muted fw-semibold small">Customer</th>
                                    <th class="border-0 text-muted fw-semibold small">Package</th>
                                    <th class="border-0 text-muted fw-semibold small">Status</th>
                                    <th class="border-0 text-muted fw-semibold small text-end pe-4">Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stats['expiringSubscriptionsList'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td class="ps-4 border-0 py-3">
                                        <div class="fw-medium text-dark"><?php echo e($sub->customer->name ?? 'Unknown'); ?></div>
                                        <div class="text-muted small"><?php echo e($sub->customer->mobile ?? ''); ?></div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <div class="text-dark small"><?php echo e($sub->package->name ?? '-'); ?></div>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sub->status === 'expired'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1" style="font-size:11px;">Expired</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1" style="font-size:11px; color:#b45309 !important;">Expiring Soon</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3 text-end pe-4">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($sub->status === 'expired'): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill"><?php echo e(\Carbon\Carbon::parse($sub->expires_at)->diffForHumans()); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill"><?php echo e(\Carbon\Carbon::parse($sub->expires_at)->diffForHumans()); ?></span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="4" class="text-center py-5 text-muted small">No expiring or expired subscriptions found.</td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('task_viewAny') || auth()->user()->canAccess('task_viewTeam') || auth()->user()->canAccess('task_viewOwn') || auth()->user()->role === 'partner'): ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$hasHrms): ?>
    <h5 class="fw-bold text-dark mb-3 mt-5">Recent Activity</h5>
    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Recent Tasks</h6>
                    <a href="<?php echo e(route('partner.hrms.tasks')); ?>" class="btn btn-sm btn-light rounded-pill px-3">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0 text-muted fw-semibold small">Task</th>
                                    <th class="border-0 text-muted fw-semibold small">Due Date</th>
                                    <th class="border-0 text-muted fw-semibold small">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $stats['recentTasks'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td class="ps-4 border-0 py-3">
                                        <div class="fw-medium text-dark"><?php echo e($task->title); ?></div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($task->employee): ?>
                                            <div class="text-muted small"><?php echo e($task->employee->name); ?></div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="border-0 py-3">
                                        <span class="text-muted small"><i class="bi bi-calendar-event me-1"></i><?php echo e(\Carbon\Carbon::parse($task->due_date)->format('M d, Y')); ?></span>
                                    </td>
                                    <td class="border-0 py-3">
                                        <?php
                                            $badgeColor = match($task->status) {
                                                'completed'   => 'success',
                                                'in_progress' => 'primary',
                                                'pending'     => 'warning',
                                                default       => 'secondary'
                                            };
                                        ?>
                                        <span class="badge bg-<?php echo e($badgeColor); ?> bg-opacity-10 text-<?php echo e($badgeColor); ?> border border-<?php echo e($badgeColor); ?> border-opacity-25 rounded-pill text-capitalize px-2 py-1" style="font-size: 11px;">
                                            <?php echo e(str_replace('_', ' ', $task->status)); ?>

                                        </span>
                                    </td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="3" class="text-center py-5 text-muted">No recent tasks found.</td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($activeNotices) && $activeNotices->count() > 0): ?>
        <div class="modal fade" id="activeNoticesModal" tabindex="-1" aria-labelledby="activeNoticesModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header text-white border-0 py-3 px-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px; height:40px; background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3);">
                                <i class="bi bi-megaphone-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0 text-white" id="activeNoticesModalLabel">Notice Board</h5>
                                <span class="text-white-50 extra-small" style="font-size:0.75rem;">Company Announcements & Alerts</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3 p-md-4" style="max-height: 70vh; overflow-y: auto; background-color: #f1f5f9;">
                        <div class="row g-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $activeNotices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="col-12">
                                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background:#ffffff; border-left: 4px solid <?php echo e($notice->type === 'global' ? '#2563eb' : '#6366f1'); ?> !important;">
                                    <div class="card-body p-3.5 p-md-4">
                                        <!-- Title & Header Meta Row -->
                                        <div class="d-flex w-100 justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h5 class="mb-0 fw-bold text-dark text-break" style="font-size:1.05rem; letter-spacing:-0.01em;"><?php echo e($notice->title); ?></h5>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notice->created_at->diffInHours(now()) < 48): ?>
                                                    <span class="badge bg-danger rounded-pill px-2 py-0.5 text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.03em;"><i class="bi bi-lightning-fill me-0.5"></i>NEW</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notice->type === 'global'): ?>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                                                        <i class="bi bi-globe me-1"></i> Global Notice
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-indigo bg-opacity-10 text-indigo border border-indigo border-opacity-25 rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.72rem; color:#4f46e5; background-color:#eef2ff; border-color:#c7d2fe;">
                                                        <i class="bi bi-person-fill-lock me-1"></i> Personal Notice
                                                    </span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <span class="text-muted extra-small fw-medium bg-light px-2.5 py-1 rounded-pill"><i class="bi bi-clock me-1 text-primary"></i><?php echo e($notice->created_at->diffForHumans()); ?></span>
                                        </div>

                                        <!-- Content Body -->
                                        <p class="my-3 text-secondary text-break" style="white-space:pre-line; line-height:1.6; font-size:0.92rem;"><?php echo e($notice->content); ?></p>

                                        <!-- Validity Date Badge (if present) -->
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notice->start_date && $notice->end_date): ?>
                                        <div class="d-inline-flex align-items-center gap-2 bg-light border rounded-pill px-3 py-1.5 mb-3 text-muted small font-monospace" style="font-size:0.8rem;">
                                            <i class="bi bi-calendar-check text-primary"></i>
                                            <span>Validity: <strong class="text-dark"><?php echo e(Carbon\Carbon::parse($notice->start_date)->format('M d, Y')); ?></strong> &rarr; <strong class="text-dark"><?php echo e(Carbon\Carbon::parse($notice->end_date)->format('M d, Y')); ?></strong></span>
                                        </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <!-- Action Link CTA Bar (if present) -->
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notice->action_link): ?>
                                        <div class="mt-2 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                            <div class="d-flex align-items-center text-muted small text-truncate pe-2">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 me-2 flex-shrink-0">
                                                    <i class="bi bi-link-45deg me-1"></i> Action Link
                                                </span>
                                                <span class="text-truncate extra-small text-secondary" style="max-width: 260px;"><?php echo e($notice->action_link); ?></span>
                                            </div>
                                            <a href="<?php echo e($notice->action_link); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-bold btn-sm ms-auto d-inline-flex align-items-center gap-1.5">
                                                <?php echo e($notice->action_text ?: 'Join Now'); ?> <i class="bi bi-box-arrow-up-right"></i>
                                            </a>
                                        </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer border-0 bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="text-muted extra-small"><i class="bi bi-info-circle me-1"></i>Review notice details anytime under HRMS Notices.</span>
                        <button type="button" class="btn-dark rounded-pill px-4" data-bs-dismiss="modal">Close Notice Board</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var noticesModal = new bootstrap.Modal(document.getElementById('activeNoticesModal'));
                noticesModal.show();
            });
        </script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const chartData = <?php echo json_encode($chartData ?? [], 15, 512) ?>;

        // ── Monthly Sales Chart ───────────────────────────────────────────
        const salesLabels  = <?php echo json_encode($salesLabels, 15, 512) ?>;
        const salesAmounts = <?php echo json_encode($salesAmounts, 15, 512) ?>;
        const salesCounts  = <?php echo json_encode($salesCounts, 15, 512) ?>;

        const salesCtx = document.getElementById('monthlySalesChart');
        if (salesCtx) {
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
                        data: [<?php echo e($approvedListings); ?>, <?php echo e($pendingListings); ?>, <?php echo e($draftListings); ?>, <?php echo e($rejectedListings); ?>],
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
        <?php if($highestListings->isNotEmpty()): ?>
        const hlCtx = document.getElementById('highestListingsChart');
        if (hlCtx) {
            const hlLabels = <?php echo json_encode($highestListings->pluck('title')->map(fn($t) => strlen($t) > 20 ? substr($t, 0, 20).'…' : $t)->values()) ?>;
            const hlBookings = <?php echo json_encode($highestListings->pluck('booking_count')->values(), 15, 512) ?>;
            const hlRevenue  = <?php echo json_encode($highestListings->pluck('total_revenue')->values(), 15, 512) ?>;
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
                            yAxisID: 'y',
                        },
                        {
                            label: 'Revenue (₹)',
                            data: hlRevenue,
                            type: 'line',
                            borderColor: '#f59e0b',
                            borderWidth: 2.5,
                            pointBackgroundColor: '#f59e0b',
                            pointRadius: 5,
                            tension: 0.3,
                            fill: false,
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, font: { size: 12 } } },
                        tooltip: {
                            callbacks: {
                                label: ctx => ctx.dataset.yAxisID === 'y1'
                                    ? ' Revenue: ₹' + ctx.parsed.y.toLocaleString()
                                    : ' Bookings: ' + ctx.parsed.y
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            position: 'left',
                            grid: { color: 'rgba(0,0,0,0.05)' },
                            ticks: { stepSize: 1, font: { size: 11 } }
                        },
                        y1: {
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: {
                                callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'K' : v),
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        }
        <?php endif; ?>

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
    <?php $__env->stopPush(); ?>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/dashboard.blade.php ENDPATH**/ ?>