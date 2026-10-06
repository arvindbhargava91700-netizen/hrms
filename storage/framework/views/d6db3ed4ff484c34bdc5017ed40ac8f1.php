    <div class="nav-section-label">Main</div>
    <?php
        $dashboardRoute =
            auth()->user()->role === 'employee' ? route('partner.hrms.dashboard') : route('partner.dashboard');
        $isDashboardActive = request()->routeIs('partner.dashboard') || request()->routeIs('partner.hrms.dashboard');
    ?>
    <a href="<?php echo e($dashboardRoute); ?>" class="nav-link <?php echo e($isDashboardActive ? 'active' : ''); ?>">
                                    <i class="bi bi-speedometer me-2"></i> Dashboard</a>

    <a href="<?php echo e(route('partner.profile')); ?>" class="nav-link <?php echo e(request()->routeIs('partner.profile') ? 'active' : ''); ?>">
        <i class="bi bi-person-circle"></i> Profile
    </a>

    <?php
        $partnerId = auth()->id();
        $hasHrms = true;
        $hasKyc = true; // KYC is always accessible
    ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasHrms): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('role_viewAny') ||
                auth()->user()->canAccess('role_viewOwn') ||
                auth()->user()->canAccess('department_viewAny') ||
                auth()->user()->canAccess('department_viewOwn') ||
                auth()->user()->canAccess('staff_viewAny') ||
                auth()->user()->canAccess('staff_viewOwn') ||
                auth()->user()->canAccess('attendance_viewAny') ||
                auth()->user()->canAccess('attendance_viewOwn') ||
                auth()->user()->canAccess('leave_viewAny') ||
                auth()->user()->canAccess('leave_viewOwn') ||
                auth()->user()->canAccess('payroll_viewAny') ||
                auth()->user()->canAccess('payroll_viewOwn') ||
                auth()->user()->canAccess('task_viewAny') ||
                auth()->user()->canAccess('task_viewOwn') ||
                auth()->user()->canAccess('expense_viewAny') ||
                auth()->user()->canAccess('expense_viewOwn') ||
                auth()->user()->canAccess('notice_viewAny') ||
                auth()->user()->canAccess('notice_viewOwn')): ?>

            <li class="nav-heading mt-4 mb-2" style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.6rem 1rem; color: #fff; background: linear-gradient(90deg, rgba(13, 110, 253, 0.2), transparent); border-left: 4px solid #0d6efd; border-radius: 0 4px 4px 0;">
                <i class="bi bi-building me-1 text-primary" style="font-size: 1rem;"></i> HRMS MODULE
            </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('role_viewAny') || auth()->user()->canAccess('role_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.roles') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.roles')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-person-badge me-2"></i> Roles
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('department_viewAny') || auth()->user()->canAccess('department_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.departments') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.departments')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-diagram-3 me-2"></i> Departments
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('branch_viewAny') ||
                                auth()->user()->canAccess('branch_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.branches') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.branches')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-buildings me-2"></i> Branches
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('shift_viewAny')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.work-shifts') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.work-shifts')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-clock me-2"></i> Work Shifts
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('staff_viewAny') || auth()->user()->canAccess('staff_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.staff') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.staff')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-people me-2"></i> Staff
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('attrition_viewAny') ||
                                auth()->user()->canAccess('staff_viewAny')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.attrition') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.attrition')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-graph-down me-2"></i> Attrition Analytics
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('exit_reason_view') ||
                                auth()->user()->canAccess('exitreason_viewany') ||
                                auth()->user()->canAccess('attrition_viewAny')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.exit-reasons') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.exit-reasons')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-door-open me-2"></i> Exit Reasons
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('employee_cost_view') ||
                                auth()->user()->canAccess('employeecost_viewany') ||
                                auth()->user()->canAccess('salary_viewAny')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.employee-cost') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.employee-cost')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-cash-coin me-2"></i> Employee Cost
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('training_view') ||
                                auth()->user()->canAccess('training_viewany')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.training') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.training')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-mortarboard me-2"></i> Training Management
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('asset_view') ||
                                auth()->user()->canAccess('asset_viewany')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.assets') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.assets')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-laptop me-2"></i> Asset Management
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('pip_viewAny') ||
                                auth()->user()->canAccess('pip_viewOwn') ||
                                auth()->user()->canAccess('pip_viewTeam') ||
                                auth()->user()->canAccess('pip_viewteam')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.pip') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.pip')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-speedometer2 me-2"></i> PIP (Performance Plan)
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('recruitment_viewAny') ||
                                auth()->user()->canAccess('recruitment_viewOwn') ||
                                auth()->user()->canAccess('recruitment_viewTeam') ||
                                auth()->user()->canAccess('recruitment_viewteam')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.recruitment') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.recruitment')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-person-plus me-2"></i> Recruitment
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('jobpost_viewAny') ||
                                auth()->user()->canAccess('jobpost_viewBranch') ||
                                auth()->user()->canAccess('jobpost_viewTeam') ||
                                auth()->user()->canAccess('jobpost_viewOwn')): ?>
                            <?php
                                $isJobPostRoute = request()->routeIs('partner.hrms.job-posts*') || request()->routeIs('partner.hrms.job-post.*');
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e($isJobPostRoute ? '' : 'collapsed'); ?>"
                                    data-bs-toggle="collapse" href="#jobPostingsSubmenu"
                                    aria-expanded="<?php echo e($isJobPostRoute ? 'true' : 'false'); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                  <i class="bi bi-receipt me-2"></i>  Job Postings
                                    <i class="bi bi-chevron-down ms-auto"
                                        style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                                </a>
                                <div class="collapse <?php echo e($isJobPostRoute ? 'show' : ''); ?>"
                                    id="jobPostingsSubmenu">
                                    <ul class="nav flex-column ms-3"
                                        style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.job-posts') || request()->routeIs('partner.hrms.job-posts.edit') || request()->routeIs('partner.hrms.job-post.payment.callback') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.job-posts')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                All Jobs
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.job-posts.create') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.job-posts.create')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                Post a Job
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.job-posts.billing') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.job-posts.billing')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                     Billing History
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('probation_viewAny') ||
                                auth()->user()->canAccess('probation_viewOwn') ||
                                auth()->user()->canAccess('probation_viewTeam') ||
                                auth()->user()->canAccess('probation_viewteam')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.probation') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.probation')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-shield-check me-2"></i> Probation & Assets
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('resignationexit_viewAny') ||
                                auth()->user()->canAccess('resignationexit_viewOwn') ||
                                auth()->user()->canAccess('resignationexit_viewTeam') ||
                                auth()->user()->canAccess('resignationexit_viewteam')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.resignation-exit') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.resignation-exit')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-box-arrow-right me-2"></i> Resignation & Exit
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('document_viewAny') ||
                                auth()->user()->canAccess('document_viewOwn') ||
                                auth()->user()->canAccess('document_viewTeam') ||
                                auth()->user()->canAccess('document_viewteam')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.documents') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.documents')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-file-earmark-text me-2"></i> Documents & KYC
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('grievance_viewAny') ||
                                auth()->user()->canAccess('grievance_viewOwn') ||
                                auth()->user()->canAccess('grievance_viewTeam') ||
                                auth()->user()->canAccess('grievance_viewteam')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.grievances') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.grievances')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-exclamation-triangle me-2"></i> Grievance & Discipline
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('lead_viewAny') ||
                                auth()->user()->canAccess('lead_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.leads*') ? 'active' : ''); ?>"
                                    data-bs-toggle="collapse" href="#crmSubmenu"
                                    aria-expanded="<?php echo e(request()->routeIs('partner.hrms.leads*') ? 'true' : 'false'); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                     <i class="bi bi-funnel me-2"></i> Lead Management
                                    <i class="bi bi-chevron-down ms-auto"
                                        style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                                </a>
                                <div class="collapse <?php echo e(request()->routeIs('partner.hrms.leads*') ? 'show' : ''); ?>"
                                    id="crmSubmenu">
                                    <ul class="nav flex-column ms-3"
                                        style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.leads.index') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.leads.index')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                    Leads
                                            </a>
                                        </li>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                                auth()->user()->canAccess('customervisit_viewAny') ||
                                                auth()->user()->canAccess('customervisit_viewOwn')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.customer-visits') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.customer-visits')); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    Customer Visits
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                                auth()->user()->canAccess('leadorder_viewAny') ||
                                                auth()->user()->canAccess('leadorder_viewOwn')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.lead-orders') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.lead-orders')); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    Orders Pipeline
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                         <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                                auth()->user()->canAccess('recoveryhistory_viewAny') ||
                                                auth()->user()->canAccess('recoveryhistory_viewOwn') ||
                                                auth()->user()->canAccess('recoveryhistory_viewBranch') ||
                                                auth()->user()->canAccess('recoveryhistory_viewTeam')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.lead-orders.recovery-history') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.lead-orders.recovery-history')); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                      Recovery History
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('task_viewAny') || auth()->user()->canAccess('task_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.tasks*') ? '' : 'collapsed'); ?>"
                                    data-bs-toggle="collapse" href="#tasksSubmenu"
                                    aria-expanded="<?php echo e(request()->routeIs('partner.hrms.tasks*') ? 'true' : 'false'); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                 <i class="bi bi-list-check me-2"></i> Tasks
                                    <i class="bi bi-chevron-down ms-auto"
                                        style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                                </a>
                                <div class="collapse <?php echo e(request()->routeIs('partner.hrms.tasks*') ? 'show' : ''); ?>"
                                    id="tasksSubmenu">
                                    <ul class="nav flex-column ms-3"
                                        style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('task_viewOwn') || auth()->user()->canAccess('task_viewAny')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.tasks') && request('scope') === 'me' ? 'active' : (request()->routeIs('partner.hrms.tasks') && !request('scope') && !auth()->user()->canAccess('task_viewAny') ? 'active' : '')); ?>"
                                                    href="<?php echo e(route('partner.hrms.tasks', ['scope' => 'me'])); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    My Tasks
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                                auth()->user()->canAccess('task_viewteam') ||
                                                auth()->user()->canAccess('task_viewAny') ||
                                                (auth()->user()->role === 'employee' && count(auth()->user()->getTeamIds()) > 1)): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request('scope') === 'team' ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.tasks', ['scope' => 'team'])); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    Team Tasks
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('task_viewAny') || auth()->user()->isPartner()): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request('scope') === 'all' || (request()->routeIs('partner.hrms.tasks') && !request('scope') && (auth()->user()->canAccess('task_viewAny') || auth()->user()->isPartner())) ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.tasks', ['scope' => 'all'])); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    All Tasks
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('expense_viewAny') || auth()->user()->canAccess('expense_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.expenses*') ? '' : 'collapsed'); ?>"
                                    data-bs-toggle="collapse" href="#expensesSubmenu"
                                    aria-expanded="<?php echo e(request()->routeIs('partner.hrms.expenses*') ? 'true' : 'false'); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                              <i class="bi bi-cash-stack me-2"></i> Expenses
                                    <i class="bi bi-chevron-down ms-auto"
                                        style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                                </a>
                                <div class="collapse <?php echo e(request()->routeIs('partner.hrms.expenses*') ? 'show' : ''); ?>"
                                    id="expensesSubmenu">
                                    <ul class="nav flex-column ms-3"
                                        style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('expense_viewOwn') || auth()->user()->canAccess('expense_viewAny')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.expenses') && request('scope') === 'me' ? 'active' : (request()->routeIs('partner.hrms.expenses') && !request('scope') && !auth()->user()->canAccess('expense_viewAny') ? 'active' : '')); ?>"
                                                    href="<?php echo e(route('partner.hrms.expenses', ['scope' => 'me'])); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    My Expenses
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                                auth()->user()->canAccess('expense_viewteam') ||
                                                auth()->user()->canAccess('expense_viewAny') ||
                                                (auth()->user()->role === 'employee' && count(auth()->user()->getTeamIds()) > 1)): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request('scope') === 'team' ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.expenses', ['scope' => 'team'])); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    Team Expenses
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('expense_viewAny') || auth()->user()->isPartner()): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request('scope') === 'all' || (request()->routeIs('partner.hrms.expenses') && !request('scope') && (auth()->user()->canAccess('expense_viewAny') || auth()->user()->isPartner())) ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.expenses', ['scope' => 'all'])); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    All Expenses
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('notice_viewAny') || auth()->user()->canAccess('notice_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.notices') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.notices')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-megaphone me-2"></i> Notices
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('product_viewAny')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.product-categories') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.product-categories')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-tags me-2"></i> Product Categories
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.products') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.products')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-box-seam me-2"></i> Products
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                                auth()->user()->canAccess('hrmssetting_manage') ||
                                auth()->user()->canAccess('attendance_manage')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.settings') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.settings')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-gear me-2"></i> Settings
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('attendance_viewAny') || auth()->user()->canAccess('attendance_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.attendance*') ? '' : 'collapsed'); ?>"
                                    data-bs-toggle="collapse" href="#attendanceSubmenu"
                                    aria-expanded="<?php echo e(request()->routeIs('partner.hrms.attendance*') ? 'true' : 'false'); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                  <i class="bi bi-calendar3 me-2"></i>  Attendance
                                    <i class="bi bi-chevron-down ms-auto"
                                        style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                                </a>
                                <div class="collapse <?php echo e(request()->routeIs('partner.hrms.attendance*') ? 'show' : ''); ?>"
                                    id="attendanceSubmenu">
                                    <ul class="nav flex-column ms-3"
                                        style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('attendance_viewOwn') || auth()->user()->canAccess('attendance_viewAny')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.attendance.mark') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.attendance.mark')); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    Mark Attendance
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.attendance.override') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.attendance.override')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                Manual Override
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.attendance.manage') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.attendance.manage')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                <?php echo e(auth()->user()->canAccess('attendance_viewAny') ? 'Employee Attendance' : 'My History'); ?>

                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.attendance.calendar') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.attendance.calendar')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                     <?php echo e(auth()->user()->canAccess('attendance_viewAny') ? 'Attendance Calendar' : 'My Calendar'); ?>

                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('leave_viewAny') || auth()->user()->canAccess('leave_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.leaves*') ? '' : 'collapsed'); ?>"
                                    data-bs-toggle="collapse" href="#leavesSubmenu"
                                    aria-expanded="<?php echo e(request()->routeIs('partner.hrms.leaves*') ? 'true' : 'false'); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-calendar-week me-2"></i> Leaves
                                    <i class="bi bi-chevron-down ms-auto"
                                        style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                                </a>
                                <div class="collapse <?php echo e(request()->routeIs('partner.hrms.leaves*') ? 'show' : ''); ?>"
                                    id="leavesSubmenu">
                                    <ul class="nav flex-column ms-3"
                                        style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('leave_viewOwn')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.leaves.apply') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.leaves.apply')); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    Apply for Leave
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.leaves.my-requests') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.leaves.my-requests')); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    Leave Requests
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('leave_viewAny')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.leaves.approvals') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.leaves.approvals')); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                     Leave Approvals
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('leave_viewOwn')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.leaves.balance') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.leaves.balance')); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                    <i class="bi bi-wallet me-2"></i> Leave Balance
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('leave_viewAny')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.leaves.calendar') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.leaves.calendar')); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                    Leave Calendar
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('payroll_viewAny')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.commission*') ? '' : 'collapsed'); ?>"
                                    data-bs-toggle="collapse" href="#commissionSubmenu"
                                    aria-expanded="<?php echo e(request()->routeIs('partner.hrms.commission*') ? 'true' : 'false'); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                   <i class="bi bi-percent me-2"></i> Commissions
                                    <i class="bi bi-chevron-down ms-auto"
                                        style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                                </a>
                                <div class="collapse <?php echo e(request()->routeIs('partner.hrms.commission*') ? 'show' : ''); ?>"
                                    id="commissionSubmenu">
                                    <ul class="nav flex-column ms-3"
                                        style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.commission.process') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.commission.process')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                Process Commissions
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.commission.history') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.commission.history')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                        Commission History
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('salary_viewAny') ||
                                auth()->user()->canAccess('salary_viewTeam') ||
                                auth()->user()->canAccess('salary_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.payroll.salary') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.payroll.salary')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-currency-exchange me-2"></i> Salary Management
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('payroll_viewAny')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.payroll*') ? '' : 'collapsed'); ?>"
                                    data-bs-toggle="collapse" href="#payrollSubmenu"
                                    aria-expanded="<?php echo e(request()->routeIs('partner.hrms.payroll*') ? 'true' : 'false'); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-cash me-2"></i>  Payroll
                                    <i class="bi bi-chevron-down ms-auto"
                                        style="font-size: 0.7rem; transition: transform 0.2s;"></i>
                                </a>
                                <div class="collapse <?php echo e(request()->routeIs('partner.hrms.payroll*') ? 'show' : ''); ?>"
                                    id="payrollSubmenu">
                                    <ul class="nav flex-column ms-3"
                                        style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('payroll_viewOwn')): ?>
                                            <li class="nav-item">
                                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.payroll.my') ? 'active' : ''); ?>"
                                                    href="<?php echo e(route('partner.hrms.payroll.my')); ?>"
                                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                    My Payroll
                                                </a>
                                            </li>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.payroll.process') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.payroll.process')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                Payroll Process
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.payroll.drafts') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.payroll.drafts')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                Payroll Drafts
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.payroll.approval') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.payroll.approval')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                Payroll Approval
                                            </a>
                                        </li>

                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.advance-payments') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.advance-payments')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                 Advance Payments
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.payroll.reports') ? 'active' : ''); ?>"
                                                href="<?php echo e(route('partner.hrms.payroll.reports')); ?>"
                                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                                  Payroll Reports
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        <?php elseif(auth()->user()->canAccess('payroll_viewOwn')): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('partner.hrms.payroll.my') ? 'active' : ''); ?>"
                                    href="<?php echo e(route('partner.hrms.payroll.my')); ?>"
                                    style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    My Payroll
                                </a>
                            </li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                </div>
            </li>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(
        ($hasHrms && auth()->user()->isPartner()) ||
            auth()->user()->canAccess('staff_viewAny') ||
            auth()->user()->canAccess('attendance_viewAny') ||
            auth()->user()->canAccess('leave_viewAny') ||
            auth()->user()->canAccess('expense_viewAny') ||
            auth()->user()->canAccess('payroll_viewAny') ||
            auth()->user()->canAccess('recovery_viewAny') ||
            auth()->user()->canAccess('notice_viewAny')): ?>
            <li class="nav-heading mt-4 mb-2" style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.6rem 1rem; color: #fff; background: linear-gradient(90deg, rgba(13, 110, 253, 0.2), transparent); border-left: 4px solid #0d6efd; border-radius: 0 4px 4px 0;">
                <i class="bi bi-file-person me-1 text-primary" style="font-size: 1rem;"></i> HRMS REPORTS
            </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('staff_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.staff')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.staff') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-people me-2"></i> Staff</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('attendance_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.attendance')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.attendance') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-calendar-check me-2"></i> Attendances</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                      <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.performance')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.performance') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-speedometer me-2"></i> Performance</a>
                        </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('leave_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.leaves')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.leaves') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-calendar-minus me-2"></i> Leaves</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('expense_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.expenses')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.expenses') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-currency-dollar me-2"></i> Expenses</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('task_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.tasks')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.tasks') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-list-check me-2"></i> Tasks</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('lead_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.leads')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.leads') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-funnel me-2"></i> Leads</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('order_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.orders')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.orders') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                <i class="bi bi-cart-check me-2"></i> Orders</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('payroll_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.payroll')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.payroll') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="bi bi-cash-stack me-2"></i> Payrolls</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('recovery_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.recovery')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.recovery') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="bi bi-arrow-counterclockwise me-2"></i> Recoveries</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('notice_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.notice')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.notice') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-megaphone me-2"></i> Notices</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('product_category_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.product-category')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.product-category') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-tags me-2"></i> Product Categories</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('product_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.product')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.product') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-box-seam me-2"></i> Products</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('salary_management_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.salary-management')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.salary-management') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="bi bi-list-ul me-2"></i> Salary List</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('commission_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.commissions')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.commissions') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-percent me-2"></i> Commissions</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('pip_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.pip')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.pip') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="bi bi-file-earmark-bar-graph me-2"></i> PIP Report</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('recruitment_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.recruitment')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.recruitment') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="bi bi-person-lines-fill me-2"></i> Recruitment Report</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('probation_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.probation')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.probation') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="bi bi-person-check me-2"></i> Probation Report</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('resignationexit_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.resignation-exit')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.resignation-exit') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="bi bi-box-arrow-right me-2"></i> Resignation & Exit Report</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('document_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.documents')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.documents') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="bi bi-file-earmark-person me-2"></i> Documents & KYC Report</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('grievance_viewAny')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.grievances')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.grievances') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="bi bi-exclamation-triangle me-2"></i> Grievance & Discipline Report</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>



                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                            auth()->user()->canAccess('attrition_viewAny') ||
                            auth()->user()->canAccess('attrition_view')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.attrition')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.attrition') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-graph-down me-2"></i> Attrition Analytics</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                            auth()->user()->canAccess('exitreason_viewAny') ||
                            auth()->user()->canAccess('exitreason_view')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.exit-reasons')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.exit-reasons') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-door-open me-2"></i> Exit Reasons</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                            auth()->user()->canAccess('employeecost_viewAny') ||
                            auth()->user()->canAccess('employeecost_view')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.employee-cost')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.employee-cost') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-cash-coin me-2"></i> Employee Cost</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                            auth()->user()->canAccess('training_viewAny') ||
                            auth()->user()->canAccess('training_view')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.training')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.training') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;"><i class="bi bi-mortarboard me-2"></i> Training Report</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() ||
                            auth()->user()->canAccess('asset_viewAny') ||
                            auth()->user()->canAccess('asset_view')): ?>
                        <li class="nav-item">
                            <a href="<?php echo e(route('partner.hrms.report.assets')); ?>"
                                class="nav-link <?php echo e(request()->routeIs('partner.hrms.report.assets') ? 'active' : ''); ?>"
                                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                                    <i class="bi bi-laptop me-2"></i> Asset Report</a>
                        </li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


   



<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/partials/sidebar-partner.blade.php ENDPATH**/ ?>