<div class="nav-section-label">Main</div>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_dashboard')): ?>
<a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
    <i class="bi bi-grid-1x2"></i> Dashboard
</a>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_system_modules')): ?>
<a href="<?php echo e(route('admin.system-modules')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.system-modules') ? 'active' : ''); ?>">
    <i class="bi bi-gear"></i> System Modules
</a>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_system_modules')): ?>
<li class="nav-item mt-2">
    <a class="nav-link <?php echo e(request()->routeIs('admin.job-categories') || request()->routeIs('admin.job-templates') || request()->routeIs('admin.job-plans') || request()->routeIs('admin.job-posts') || request()->routeIs('admin.job-attributes') ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" href="#jobSettingsSubmenu" aria-expanded="<?php echo e(request()->routeIs('admin.job-categories') || request()->routeIs('admin.job-templates') || request()->routeIs('admin.job-plans') || request()->routeIs('admin.job-posts') || request()->routeIs('admin.job-attributes') ? 'true' : 'false'); ?>">
        <i class="bi bi-briefcase"></i>
        <span>Job Settings</span>
        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
    </a>
    <div class="collapse <?php echo e(request()->routeIs('admin.job-categories') || request()->routeIs('admin.job-templates') || request()->routeIs('admin.job-plans') || request()->routeIs('admin.job-posts') || request()->routeIs('admin.job-attributes') ? 'show' : ''); ?>" id="jobSettingsSubmenu" data-bs-parent="#sidebar-nav">
        <ul class="nav flex-column ms-4" style="list-style: none; padding-left: 0; margin-top: 0.2rem; border-left: 1px solid rgba(255,255,255,0.1);">
            <li class="nav-item">
                <a href="<?php echo e(route('admin.job-categories')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.job-categories') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Job Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.job-templates')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.job-templates') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Job Templates
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.job-plans')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.job-plans') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Job Plans
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.job-attributes')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.job-attributes') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Job Attributes
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.job-posts')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.job-posts') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    All Job Posts
                </a>
            </li>
        </ul>
    </div>
</li>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_system_settings')): ?>
<a href="<?php echo e(route('admin.system-settings')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.system-settings') ? 'active' : ''); ?>">
    <i class="bi bi-gear-fill"></i> System Settings
</a>
<a href="<?php echo e(route('admin.payment-gateway')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.payment-gateway') ? 'active' : ''); ?>">
    <i class="bi bi-credit-card"></i> Payment Gateway
</a>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<a href="<?php echo e(route('admin.profile')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.profile') ? 'active' : ''); ?>">
    <i class="bi bi-person-circle"></i> Profile
</a>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_user_management')): ?>
<li class="nav-item">
    <a class="nav-link <?php echo e(request()->routeIs('admin.partners') || request()->routeIs('admin.customers') || request()->routeIs('admin.custom-notifications') ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" href="#userManagementSubmenu" aria-expanded="<?php echo e(request()->routeIs('admin.partners') || request()->routeIs('admin.customers') || request()->routeIs('admin.custom-notifications') ? 'true' : 'false'); ?>">
        <i class="bi bi-people"></i>
        <span>User Management</span>
        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
    </a>
    <div class="collapse <?php echo e(request()->routeIs('admin.partners') || request()->routeIs('admin.customers') || request()->routeIs('admin.custom-notifications') ? 'show' : ''); ?>" id="userManagementSubmenu" data-bs-parent="#sidebar-nav">
        <ul class="nav flex-column ms-4" style="list-style: none; padding-left: 0; margin-top: 0.2rem; border-left: 1px solid rgba(255,255,255,0.1);">
            <li class="nav-item">
                <a href="<?php echo e(route('admin.partners')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.partners') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Partners
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.customers')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.customers') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Customers
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.custom-notifications')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.custom-notifications') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Send Notifications
                </a>
            </li>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_staff_manage')): ?>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.admin-staff')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.admin-staff') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Admin Staff
                </a>
            </li>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </ul>
    </div>
</li>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_operations_kyc')): ?>
<li class="nav-item mt-2">
    <a class="nav-link <?php echo e(request()->routeIs('admin.kyc') || request()->routeIs('admin.customer-kyc') || request()->routeIs('admin.kyc.fields') || request()->routeIs('admin.visits') || request()->routeIs('admin.attendance') || request()->routeIs('admin.bookings') ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" href="#operationsKycSubmenu" aria-expanded="<?php echo e(request()->routeIs('admin.kyc') || request()->routeIs('admin.customer-kyc') || request()->routeIs('admin.kyc.fields') || request()->routeIs('admin.visits') || request()->routeIs('admin.attendance') || request()->routeIs('admin.bookings') ? 'true' : 'false'); ?>">
        <i class="bi bi-shield-check"></i>
        <span>Operations & KYC</span>
        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
    </a>
    <div class="collapse <?php echo e(request()->routeIs('admin.kyc') || request()->routeIs('admin.customer-kyc') || request()->routeIs('admin.kyc.fields') || request()->routeIs('admin.visits') || request()->routeIs('admin.attendance') || request()->routeIs('admin.bookings') ? 'show' : ''); ?>" id="operationsKycSubmenu" data-bs-parent="#sidebar-nav">
        <ul class="nav flex-column ms-4" style="list-style: none; padding-left: 0; margin-top: 0.2rem; border-left: 1px solid rgba(255,255,255,0.1);">
            <li class="nav-item">
                <a href="<?php echo e(route('admin.bookings')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.bookings') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Bookings
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.kyc')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.kyc') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Partner KYC
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.customer-kyc')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.customer-kyc') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Customer KYC
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.kyc.fields')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.kyc.fields') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    KYC Fields
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.visits')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.visits') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Visit Requests
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.attendance')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.attendance') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Attendance
                </a>
            </li>
        </ul>
    </div>
</li>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_platform_content')): ?>
<li class="nav-item mt-2">
    <a class="nav-link <?php echo e(request()->routeIs('admin.categories') || request()->routeIs('admin.banners') || request()->routeIs('admin.coupons') || request()->routeIs('admin.listings') || request()->routeIs('admin.listing.*') || request()->routeIs('admin.reviews') ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" href="#platformContentSubmenu" aria-expanded="<?php echo e(request()->routeIs('admin.categories') || request()->routeIs('admin.banners') || request()->routeIs('admin.coupons') || request()->routeIs('admin.listings') || request()->routeIs('admin.listing.*') || request()->routeIs('admin.reviews') ? 'true' : 'false'); ?>">
        <i class="bi bi-collection"></i>
        <span>Platform Content</span>
        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
    </a>
    <div class="collapse <?php echo e(request()->routeIs('admin.categories') || request()->routeIs('admin.banners') || request()->routeIs('admin.coupons') || request()->routeIs('admin.listings') || request()->routeIs('admin.listing.*') || request()->routeIs('admin.reviews') ? 'show' : ''); ?>" id="platformContentSubmenu" data-bs-parent="#sidebar-nav">
        <ul class="nav flex-column ms-4" style="list-style: none; padding-left: 0; margin-top: 0.2rem; border-left: 1px solid rgba(255,255,255,0.1);">
            <li class="nav-item">
                <a href="<?php echo e(route('admin.categories')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.categories') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.banners')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.banners') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    App Banners
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.coupons')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.coupons') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Coupons
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.listings')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.listings') || request()->routeIs('admin.listing.*') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Listings
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reviews')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reviews') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Reviews
                </a>
            </li>
        </ul>
    </div>
</li>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_finance_earnings')): ?>
<li class="nav-item mt-2">
    <a class="nav-link <?php echo e(request()->routeIs('admin.partner-packages') || request()->routeIs('admin.wallet-recharges') || request()->routeIs('admin.withdrawals') || request()->routeIs('admin.bookings') || request()->routeIs('admin.subscriptions') || request()->routeIs('admin.payments') || request()->routeIs('admin.job-post-billing-history') || request()->routeIs('admin.invoices') || request()->routeIs('admin.reports') || request()->routeIs('admin.reserve-histories') || request()->routeIs('admin.transaction-history') ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" href="#financeAdminSubmenu" aria-expanded="<?php echo e(request()->routeIs('admin.partner-packages') || request()->routeIs('admin.wallet-recharges') || request()->routeIs('admin.withdrawals') || request()->routeIs('admin.bookings') || request()->routeIs('admin.subscriptions') || request()->routeIs('admin.payments') || request()->routeIs('admin.job-post-billing-history') || request()->routeIs('admin.invoices') || request()->routeIs('admin.reports') || request()->routeIs('admin.reserve-histories') || request()->routeIs('admin.transaction-history') ? 'true' : 'false'); ?>">
        <i class="bi bi-graph-up-arrow"></i>
        <span>Finance & Earnings</span>
        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
    </a>
    <div class="collapse <?php echo e(request()->routeIs('admin.partner-packages') || request()->routeIs('admin.wallet-recharges') || request()->routeIs('admin.withdrawals') || request()->routeIs('admin.bookings') || request()->routeIs('admin.subscriptions') || request()->routeIs('admin.payments') || request()->routeIs('admin.job-post-billing-history') || request()->routeIs('admin.invoices') || request()->routeIs('admin.reports') || request()->routeIs('admin.reserve-histories') || request()->routeIs('admin.transaction-history') ? 'show' : ''); ?>" id="financeAdminSubmenu" data-bs-parent="#sidebar-nav">
        <ul class="nav flex-column ms-4" style="list-style: none; padding-left: 0; margin-top: 0.2rem; border-left: 1px solid rgba(255,255,255,0.1);">
            <li class="nav-item">
                <a href="<?php echo e(route('admin.transaction-history')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.transaction-history') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Transaction History
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.subscriptions')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.subscriptions') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Subscriptions
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.payments')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.payments') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Payments
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.job-post-billing-history')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.job-post-billing-history') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Job Post Billing History
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.invoices')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.invoices') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Invoices
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.partner-packages')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.partner-packages') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Partner Packages
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.partner-subscriptions')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.partner-subscriptions') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Partner Subscriptions
                </a>
            </li>


            <li class="nav-item">
                <a href="<?php echo e(route('admin.wallet-recharges')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.wallet-recharges') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Wallet Recharges
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reserve-histories')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reserve-histories') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Reserve History
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.withdrawals')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.withdrawals') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Withdrawal Requests
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Revenue Analytics
                </a>
            </li>
        </ul>
    </div>
</li>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_reports')): ?>
<li class="nav-item mt-2">
    <a class="nav-link <?php echo e(request()->routeIs('admin.reports.*') ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" href="#adminReportsSubmenu" aria-expanded="<?php echo e(request()->routeIs('admin.reports.*') ? 'true' : 'false'); ?>">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span>Reports</span>
        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
    </a>
    <div class="collapse <?php echo e(request()->routeIs('admin.reports.*') ? 'show' : ''); ?>" id="adminReportsSubmenu" data-bs-parent="#sidebar-nav">
        <ul class="nav flex-column ms-4" style="list-style: none; padding-left: 0; margin-top: 0.2rem; border-left: 1px solid rgba(255,255,255,0.1);">
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.partners')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.partners') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Partners
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.customers')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.customers') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Customers
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.listings')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.listings') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Listings
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.occupancy')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.occupancy') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Occupancy
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.business')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.business') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Business
                </a>
            </li>
           
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.payments')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.payments') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Payment Histories
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.collections')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.collections') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Collection Report
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.subscriptions')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.subscriptions') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Customer Subscription
                </a>
            </li>
            
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.partner-packages')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.partner-packages') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Partner Subscription
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.transaction-ledger')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.transaction-ledger') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Transaction Ledgers
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.reviews')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.reviews') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Reviews
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.reports.visit-requests')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.visit-requests') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Visit Requests
                </a>
            </li>
        </ul>
    </div>
</li>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->can('admin_hrms_reports')): ?>
<li class="nav-item mt-2">
    <a class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.*') ? '' : 'collapsed'); ?>" data-bs-toggle="collapse" href="#adminHrmsReportsSubmenu" aria-expanded="<?php echo e(request()->routeIs('admin.hrms-reports.*') ? 'true' : 'false'); ?>">
        <i class="bi bi-person-lines-fill"></i>
        <span>HRMS Reports</span>
        <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
    </a>
    <div class="collapse <?php echo e(request()->routeIs('admin.hrms-reports.*') ? 'show' : ''); ?>" id="adminHrmsReportsSubmenu" data-bs-parent="#sidebar-nav">
        <ul class="nav flex-column ms-4" style="list-style: none; padding-left: 0; margin-top: 0.2rem; border-left: 1px solid rgba(255,255,255,0.1);">
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.staff')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.staff') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Staffs
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.attendance')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.attendance') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Attendances
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.leaves')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.leaves') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Leaves
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.expenses')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.expenses') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Expenses
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.tasks')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.tasks') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Tasks
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.leads')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.leads') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Leads
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.orders')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.orders') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Orders
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.payroll')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.payroll') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Payrolls
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.recovery')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.recovery') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Recovery
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.notice')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.notice') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Notices
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.product-category')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.product-category') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Product Categories
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.product')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.product') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Products
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.salary-management')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.salary-management') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Salary Managements
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.commissions')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.commissions') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Commissions
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.pip-report')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.pip-report') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    PIP Report
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.recruitment-report')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.recruitment-report') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Recruitment Report
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.probation-report')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.probation-report') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Probation Report
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.resignation-exit-report')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.resignation-exit-report') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Resignation & Exit Report
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.documents-kyc-report')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.documents-kyc-report') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Documents & KYC Report
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.grievance-discipline-report')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.grievance-discipline-report') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Grievance & Discipline Report
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.attrition-analytics')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.attrition-analytics') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Attrition Analytics
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.exit-reasons')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.exit-reasons') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Exit Reasons
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.employee-cost')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.employee-cost') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Employee Cost
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.training-report')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.training-report') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Training Report
                </a>
            </li>
            <li class="nav-item">
                <a href="<?php echo e(route('admin.hrms-reports.asset-report')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.hrms-reports.asset-report') ? 'active' : ''); ?>" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                    Asset Report
                </a>
            </li>
        </ul>
    </div>
</li>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/partials/sidebar-admin.blade.php ENDPATH**/ ?>