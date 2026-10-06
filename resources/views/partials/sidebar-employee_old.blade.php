<div class="nav-section-label">Main</div>

@if (auth()->user()?->hasApprovedKyc())
    <a href="{{ route('partner.hrms.dashboard') }}"
        class="nav-link {{ request()->routeIs('partner.hrms.dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i> <span>Dashboard</span>
    </a>
    <a href="{{ route('partner.profile') }}" class="nav-link {{ request()->routeIs('partner.profile') ? 'active' : '' }}">
        <i class="bi bi-person-circle"></i> <span>Profile</span>
    </a>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('partner.hrms.process-notes') ? 'active' : '' }}"
            href="{{ route('partner.hrms.process-notes') }}">
            <i class="bi bi-journal-text"></i> <span>Process Notes</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('partner.hrms.company-documents') ? 'active' : '' }}"
            href="{{ route('partner.hrms.company-documents') }}">
            <i class="bi bi-folder2-open"></i> <span>Company Documents</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('partner.hrms.my-daily-reports') ? 'active' : '' }}"
            href="{{ route('partner.hrms.my-daily-reports') }}">
            <i class="bi bi-journal-plus"></i> <span>My Daily Reports</span>
        </a>
    </li>
    
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('partner.hrms.daily-reports-history') ? 'active' : '' }}"
            href="{{ route('partner.hrms.daily-reports-history') }}">
            <i class="bi bi-clock-history"></i> <span>Daily Report History</span>
        </a>
    </li>
    
    @if(auth()->user()->reportees()->count() > 0)
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('partner.hrms.team-reports') ? 'active' : '' }}"
            href="{{ route('partner.hrms.team-reports') }}">
            <i class="bi bi-people"></i> <span>Team Work Reports</span>
        </a>
    </li>
    @endif

    <div class="nav-section-label mt-3">My Workspace</div> 

    @if (auth()->user()->canAccess('attendance_viewAny') ||
            auth()->user()->canAccess('attendance_viewOwn') ||
            auth()->user()->canAccess('attendance_viewteam'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.attendance*') ? '' : 'collapsed' }}"
                data-bs-toggle="collapse" href="#attendanceSubmenu"
                aria-expanded="{{ request()->routeIs('partner.hrms.attendance*') ? 'true' : 'false' }}">
                <i class="bi bi-calendar-check"></i>
                <span>Attendance</span>
                <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
            </a>
            <div class="collapse {{ request()->routeIs('partner.hrms.attendance*') ? 'show' : '' }}"
                id="attendanceSubmenu">
                <ul class="nav flex-column ms-3"
                    style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                    @if (auth()->user()->canAccess('attendance_viewOwn') ||
                            auth()->user()->canAccess('attendance_viewAny') ||
                            auth()->user()->canAccess('attendance_viewteam'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.attendance.mark') ? 'active' : '' }}"
                                href="{{ route('partner.hrms.attendance.mark') }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                Mark Attendance
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->canAccess('attendance_manualoverride'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.attendance.override') ? 'active' : '' }}"
                                href="{{ route('partner.hrms.attendance.override') }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                Manual Override
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('partner.hrms.attendance.manage') ? 'active' : '' }}"
                            href="{{ route('partner.hrms.attendance.manage') }}"
                            style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                            {{ auth()->user()->canAccess('attendance_viewAny') || auth()->user()->canAccess('attendance_viewteam') ? 'Employee Attendance' : 'My History' }}
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('partner.hrms.attendance.calendar') ? 'active' : '' }}"
                            href="{{ route('partner.hrms.attendance.calendar') }}"
                            style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                            {{ auth()->user()->canAccess('attendance_viewAny') || auth()->user()->canAccess('attendance_viewteam') ? 'Attendance Calendar' : 'My Calendar' }}
                        </a>
                    </li>

                </ul>
            </div>
        </li>
    @endif

    @if (auth()->user()->canAccess('leave_viewAny') ||
            auth()->user()->canAccess('leave_viewOwn') ||
            auth()->user()->canAccess('leave_viewteam'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.leaves*') ? '' : 'collapsed' }}"
                data-bs-toggle="collapse" href="#leavesSubmenu"
                aria-expanded="{{ request()->routeIs('partner.hrms.leaves*') ? 'true' : 'false' }}">
                <i class="bi bi-umbrella"></i>
                <span>Leaves</span>
                <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
            </a>
            <div class="collapse {{ request()->routeIs('partner.hrms.leaves*') ? 'show' : '' }}" id="leavesSubmenu">
                <ul class="nav flex-column ms-3"
                    style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                    @if (auth()->user()->canAccess('leave_viewOwn') || auth()->user()->canAccess('leave_viewteam'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.leaves.apply') ? 'active' : '' }}"
                                href="{{ route('partner.hrms.leaves.apply') }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                Apply for Leave
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.leaves.my-requests') ? 'active' : '' }}"
                                href="{{ route('partner.hrms.leaves.my-requests') }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                Leave Requests
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->canAccess('leave_viewAny') || auth()->user()->canAccess('leave_viewteam'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.leaves.approvals') ? 'active' : '' }}"
                                href="{{ route('partner.hrms.leaves.approvals') }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                Leave Approvals
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->canAccess('leave_viewOwn'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.leaves.balance') ? 'active' : '' }}"
                                href="{{ route('partner.hrms.leaves.balance') }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                Leave Balance
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->canAccess('leave_viewAny') || auth()->user()->canAccess('leave_viewteam'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.leaves.calendar') ? 'active' : '' }}"
                                href="{{ route('partner.hrms.leaves.calendar') }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                Leave Calendar
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
    @endif

    @if (auth()->user()->canAccess('salary_viewAny') ||
            auth()->user()->canAccess('salary_viewteam') ||
            auth()->user()->canAccess('salary_viewOwn'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.payroll.salary') ? 'active' : '' }}"
                href="{{ route('partner.hrms.payroll.salary') }}">
                <i class="bi bi-wallet2"></i>
                <span>Salary Management</span>
            </a>
        </li>
    @endif

    @if (auth()->user()->canAccess('payroll_viewAny') || auth()->user()->canAccess('payroll_viewteam'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.payroll*') ? '' : 'collapsed' }}"
                data-bs-toggle="collapse" href="#payrollSubmenu"
                aria-expanded="{{ request()->routeIs('partner.hrms.payroll*') ? 'true' : 'false' }}">
                <i class="bi bi-cash-stack"></i>
                <span>Payroll</span>
                <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
            </a>
            <div class="collapse {{ request()->routeIs('partner.hrms.payroll*') ? 'show' : '' }}" id="payrollSubmenu">
                <ul class="nav flex-column ms-3"
                    style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                    @if (auth()->user()->canAccess('payroll_viewOwn'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.payroll.my') ? 'active' : '' }}"
                                href="{{ route('partner.hrms.payroll.my') }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                My Payroll
                            </a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('partner.hrms.payroll.process') ? 'active' : '' }}"
                            href="{{ route('partner.hrms.payroll.process') }}"
                            style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                            Payroll Process
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('partner.hrms.payroll.drafts') ? 'active' : '' }}"
                            href="{{ route('partner.hrms.payroll.drafts') }}"
                            style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                            Payroll Drafts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('partner.hrms.payroll.approval') ? 'active' : '' }}"
                            href="{{ route('partner.hrms.payroll.approval') }}"
                            style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                            Payroll Approval
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('partner.hrms.advance-payments') ? 'active' : '' }}"
                            href="{{ route('partner.hrms.advance-payments') }}"
                            style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                            Advance Payments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('partner.hrms.payroll.reports') ? 'active' : '' }}"
                            href="{{ route('partner.hrms.payroll.reports') }}"
                            style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                            Payroll Reports
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @elseif(auth()->user()->canAccess('payroll_viewOwn'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.payroll.my') ? 'active' : '' }}"
                href="{{ route('partner.hrms.payroll.my') }}">
                <i class="bi bi-cash-stack"></i> <span>My Payroll</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.advance-payments') ? 'active' : '' }}"
                href="{{ route('partner.hrms.advance-payments') }}">
                <i class="bi bi-cash-coin"></i> <span>Advance Payments</span>
            </a>
        </li>
    @endif

    @if (auth()->user()->canAccess('performance_viewAny') || auth()->user()->canAccess('performance_viewTeam') || auth()->user()->canAccess('performance_viewBranch') || auth()->user()->canAccess('performance_viewOwn'))
        <li class="nav-item">
            <a href="{{ route('partner.hrms.report.performance') }}"
                class="nav-link {{ request()->routeIs('partner.hrms.report.performance') ? 'active' : '' }}"
                style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                 <i class="bi bi-trophy-fill"></i>
                <span>
                    Partner Performance
                </span>
            </a>
        </li>
    @endif

    @if (auth()->user()->canAccess('wallet_viewAny') || auth()->user()->canAccess('wallet_viewOwn'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.wallet') ? 'active' : '' }}"
                href="{{ route('partner.wallet') }}">
                <i class="bi bi-wallet2"></i> <span>My Wallet</span>
            </a>
        </li>
    @endif
    @if (auth()->user()->canAccess('task_viewAny') || auth()->user()->canAccess('task_viewOwn'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.tasks*') ? '' : 'collapsed' }}"
                data-bs-toggle="collapse" href="#tasksSubmenu"
                aria-expanded="{{ request()->routeIs('partner.hrms.tasks*') ? 'true' : 'false' }}">
                <i class="bi bi-list-task"></i>
                <span>Tasks</span>
                <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
            </a>
            <div class="collapse {{ request()->routeIs('partner.hrms.tasks*') ? 'show' : '' }}" id="tasksSubmenu">
                <ul class="nav flex-column ms-3"
                    style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                    @if (auth()->user()->canAccess('task_viewOwn') || auth()->user()->canAccess('task_viewAny'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.tasks') && request('scope') === 'me' ? 'active' : (request()->routeIs('partner.hrms.tasks') && !request('scope') && !auth()->user()->canAccess('task_viewAny') ? 'active' : '') }}"
                                href="{{ route('partner.hrms.tasks', ['scope' => 'me']) }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                My Tasks
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->canAccess('task_viewteam') || auth()->user()->canAccess('task_viewAny'))
                        <li class="nav-item">
                            <a class="nav-link {{ request('scope') === 'team' ? 'active' : '' }}"
                                href="{{ route('partner.hrms.tasks', ['scope' => 'team']) }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                Team Tasks
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->canAccess('task_viewAny'))
                        <li class="nav-item">
                            <a class="nav-link {{ request('scope') === 'all' || (request()->routeIs('partner.hrms.tasks') && !request('scope') && auth()->user()->canAccess('task_viewAny')) ? 'active' : '' }}"
                                href="{{ route('partner.hrms.tasks', ['scope' => 'all']) }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                All Tasks
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
    @endif


    @if (auth()->user()->canAccess('expense_viewAny') || auth()->user()->canAccess('expense_viewOwn'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.expenses*') ? '' : 'collapsed' }}"
                data-bs-toggle="collapse" href="#expensesSubmenu"
                aria-expanded="{{ request()->routeIs('partner.hrms.expenses*') ? 'true' : 'false' }}">
                <i class="bi bi-receipt"></i>
                <span>Expenses</span>
                <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
            </a>
            <div class="collapse {{ request()->routeIs('partner.hrms.expenses*') ? 'show' : '' }}"
                id="expensesSubmenu">
                <ul class="nav flex-column ms-3"
                    style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                    @if (auth()->user()->canAccess('expense_viewOwn') || auth()->user()->canAccess('expense_viewAny'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.expenses') && request('scope') === 'me' ? 'active' : (request()->routeIs('partner.hrms.expenses') && !request('scope') && !auth()->user()->canAccess('expense_viewAny') ? 'active' : '') }}"
                                href="{{ route('partner.hrms.expenses', ['scope' => 'me']) }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                My Expenses
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->canAccess('expense_viewteam') || auth()->user()->canAccess('expense_viewAny'))
                        <li class="nav-item">
                            <a class="nav-link {{ request('scope') === 'team' ? 'active' : '' }}"
                                href="{{ route('partner.hrms.expenses', ['scope' => 'team']) }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                Team Expenses
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->canAccess('expense_viewAny'))
                        <li class="nav-item">
                            <a class="nav-link {{ request('scope') === 'all' || (request()->routeIs('partner.hrms.expenses') && !request('scope') && auth()->user()->canAccess('expense_viewAny')) ? 'active' : '' }}"
                                href="{{ route('partner.hrms.expenses', ['scope' => 'all']) }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                All Expenses
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </li>
    @endif

    @if (auth()->user()->canAccess('notice_viewAny') ||
            auth()->user()->canAccess('notice_viewOwn') ||
            auth()->user()->canAccess('notice_viewteam'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.notices') ? 'active' : '' }}"
                href="{{ route('partner.hrms.notices') }}">
                <i class="bi bi-megaphone"></i> <span>Notices</span>
            </a>
        </li>
    @endif

    @if (auth()->user()->canAccess('employeecost_view') ||
            auth()->user()->canAccess('employeecost_viewany') ||
            auth()->user()->canAccess('employeecost_viewteam') ||
            auth()->user()->canAccess('employeecost_viewown'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.employee-cost') ? 'active' : '' }}"
                href="{{ route('partner.hrms.employee-cost') }}">
                <i class="bi bi-calculator"></i> <span>Employee Cost</span>
            </a>
        </li>
    @endif

    @if (auth()->user()->canAccess('training_view') ||
            auth()->user()->canAccess('training_viewany') ||
            auth()->user()->canAccess('training_viewteam') ||
            auth()->user()->canAccess('training_viewown'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.training') ? 'active' : '' }}"
                href="{{ route('partner.hrms.training') }}">
                <i class="bi bi-journal-bookmark-fill"></i> <span>Training Management</span>
            </a>
        </li>
    @endif

    @if (auth()->user()->canAccess('asset_view') ||
            auth()->user()->canAccess('asset_viewany') ||
            auth()->user()->canAccess('asset_viewteam') ||
            auth()->user()->canAccess('asset_viewown'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.assets') ? 'active' : '' }}"
                href="{{ route('partner.hrms.assets') }}">
                <i class="bi bi-laptop"></i> <span>Asset Management</span>
            </a>
        </li>
    @endif

    @if (auth()->user()->canAccess('attrition_view') || auth()->user()->canAccess('attrition_viewany'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.attrition') ? 'active' : '' }}"
                href="{{ route('partner.hrms.attrition') }}">
                <i class="bi bi-graph-down-arrow"></i> <span>Attrition Analytics</span>
            </a>
        </li>
    @endif

    @if (auth()->user()->canAccess('exitreason_view') || auth()->user()->canAccess('exitreason_viewany'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.exit-reasons') ? 'active' : '' }}"
                href="{{ route('partner.hrms.exit-reasons') }}">
                <i class="bi bi-box-arrow-right"></i> <span>Exit Reasons</span>
            </a>
        </li>
    @endif

    @if (auth()->user()->canAccess('department_viewAny') ||
            auth()->user()->canAccess('department_viewOwn') ||
            auth()->user()->canAccess('staff_viewAny') ||
            auth()->user()->canAccess('staff_viewOwn') ||
            auth()->user()->canAccess('staff_viewteam') ||
            auth()->user()->canAccess('role_viewAny') ||
            auth()->user()->canAccess('role_viewOwn') ||
            auth()->user()->canAccess('branch_viewAny') ||
            auth()->user()->canAccess('branch_viewOwn') ||
            auth()->user()->canAccess('shift_viewAny'))
        <div class="nav-section-label mt-3">HR Settings</div>
        @if (auth()->user()->canAccess('staff_viewAny') ||
                auth()->user()->canAccess('staff_viewOwn') ||
                auth()->user()->canAccess('staff_viewteam'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.hrms.staff') ? 'active' : '' }}"
                    href="{{ route('partner.hrms.staff') }}">
                    <i class="bi bi-people"></i> <span>Staff</span>
                </a>
            </li>
        @endif
        @if (auth()->user()->canAccess('department_viewAny') || auth()->user()->canAccess('department_viewOwn'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.hrms.departments') ? 'active' : '' }}"
                    href="{{ route('partner.hrms.departments') }}">
                    <i class="bi bi-diagram-3"></i> <span>Departments</span>
                </a>
            </li>
        @endif
        @if (auth()->user()->canAccess('role_viewAny') || auth()->user()->canAccess('role_viewOwn'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.hrms.roles') ? 'active' : '' }}"
                    href="{{ route('partner.hrms.roles') }}">
                    <i class="bi bi-person-badge"></i> <span>Roles</span>
                </a>
            </li>
        @endif
        @if (auth()->user()->canAccess('branch_viewAny') || auth()->user()->canAccess('branch_viewOwn'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.hrms.branches') ? 'active' : '' }}"
                    href="{{ route('partner.hrms.branches') }}">
                    <i class="bi bi-building"></i> <span>Branches</span>
                </a>
            </li>
        @endif
        @if (auth()->user()->canAccess('shift_viewAny'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.hrms.work-shifts') ? 'active' : '' }}"
                    href="{{ route('partner.hrms.work-shifts') }}">
                    <i class="bi bi-clock"></i> <span>Work Shifts</span>
                </a>
            </li>
        @endif
    @endif

    <div class="nav-section-label mt-3">Performance</div>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('partner.hrms.my-targets') ? 'active' : '' }}"
            href="{{ route('partner.hrms.my-targets') }}">
            <i class="bi bi-bullseye"></i> <span>My Targets</span>
        </a>
    </li>
    @if (auth()->user()->canAccess('pip_viewAny') ||
            auth()->user()->canAccess('pip_viewOwn') ||
            auth()->user()->canAccess('pip_viewTeam') ||
            auth()->user()->canAccess('pip_viewteam'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.pip') ? 'active' : '' }}"
                href="{{ route('partner.hrms.pip') }}">
                <i class="bi bi-speedometer2"></i> <span>PIP (Performance Plan)</span>
            </a>
        </li>
    @endif
    @if (auth()->user()->canAccess('recruitment_viewAny') ||
            auth()->user()->canAccess('recruitment_viewOwn') ||
            auth()->user()->canAccess('recruitment_viewTeam') ||
            auth()->user()->canAccess('recruitment_viewteam'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.recruitment') ? 'active' : '' }}"
                href="{{ route('partner.hrms.recruitment') }}">
                <i class="bi bi-person-plus"></i> <span>Recruitment</span>
            </a>
        </li>
    @endif
    @if (auth()->user()->canAccess('probation_viewAny') ||
            auth()->user()->canAccess('probation_viewOwn') ||
            auth()->user()->canAccess('probation_viewTeam') ||
            auth()->user()->canAccess('probation_viewteam'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.probation') ? 'active' : '' }}"
                href="{{ route('partner.hrms.probation') }}">
                <i class="bi bi-person-bounding-box"></i> <span>Probation</span>
            </a>
        </li>
    @endif
    @if (auth()->user()->canAccess('resignationexit_viewAny') ||
            auth()->user()->canAccess('resignationexit_viewOwn') ||
            auth()->user()->canAccess('resignationexit_viewTeam') ||
            auth()->user()->canAccess('resignationexit_viewteam'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.resignation-exit') ? 'active' : '' }}"
                href="{{ route('partner.hrms.resignation-exit') }}">
                <i class="bi bi-box-arrow-right"></i> <span>Resignation & Exit</span>
            </a>
        </li>
    @endif

    @if (auth()->user()->isPartner() ||
            auth()->user()->canAccess('document_viewAny') ||
            auth()->user()->canAccess('document_viewOwn') ||
            auth()->user()->canAccess('document_viewTeam') ||
            auth()->user()->canAccess('document_viewteam'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.documents') ? 'active' : '' }}"
                href="{{ route('partner.hrms.documents') }}" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                Documents & KYC
            </a>
        </li>
    @endif
    @if (auth()->user()->isPartner() ||
            auth()->user()->canAccess('grievance_viewAny') ||
            auth()->user()->canAccess('grievance_viewOwn') ||
            auth()->user()->canAccess('grievance_viewTeam') ||
            auth()->user()->canAccess('grievance_viewteam'))
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.grievances') ? 'active' : '' }}"
                href="{{ route('partner.hrms.grievances') }}" style="padding: 0.4rem 1rem; font-size: 0.85rem;">
                Grievance & Discipline
            </a>
        </li>
    @endif

    @if (auth()->user()->canAccess('lead_viewAny') ||
            auth()->user()->canAccess('lead_viewOwn') ||
            auth()->user()->canAccess('lead_viewteam'))
        <div class="nav-section-label mt-3">CRM & Sales</div>
        @if (auth()->user()->canAccess('lead_viewAny') ||
                auth()->user()->canAccess('lead_viewOwn') ||
                auth()->user()->canAccess('lead_viewteam'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('partner.hrms.leads*') ? '' : 'collapsed' }}"
                    data-bs-toggle="collapse" href="#crmSubmenu"
                    aria-expanded="{{ request()->routeIs('partner.hrms.leads*') ? 'true' : 'false' }}">
                    <i class="bi bi-person-lines-fill"></i>
                    <span>Lead Management</span>
                    <i class="bi bi-chevron-down ms-auto" style="font-size: 0.8rem; transition: transform 0.2s;"></i>
                </a>
                <div class="collapse {{ request()->routeIs('partner.hrms.leads*') ? 'show' : '' }}" id="crmSubmenu">
                    <ul class="nav flex-column ms-3"
                        style="list-style: none; padding-left: 0; margin-top: 0.1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('partner.hrms.leads.index') ? 'active' : '' }}"
                                href="{{ route('partner.hrms.leads.index') }}"
                                style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                Leads
                            </a>
                        </li>
                        @if (auth()->user()->canAccess('customervisit_viewAny') ||
                                auth()->user()->canAccess('customervisit_viewOwn') ||
                                auth()->user()->canAccess('customervisit_viewteam'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('partner.hrms.customer-visits') ? 'active' : '' }}"
                                    href="{{ route('partner.hrms.customer-visits') }}"
                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                    Customer Visits
                                </a>
                            </li>
                        @endif
                        @if (auth()->user()->canAccess('leadorder_viewAny') ||
                                auth()->user()->canAccess('leadorder_viewOwn') ||
                                auth()->user()->canAccess('leadorder_viewteam'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('partner.hrms.lead-orders') ? 'active' : '' }}"
                                    href="{{ route('partner.hrms.lead-orders') }}"
                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                    Orders Pipeline
                                </a>
                            </li>
                        @endif
                        @if (auth()->user()->canAccess('recovery_viewAny') ||
                                auth()->user()->canAccess('recovery_viewOwn') ||
                                auth()->user()->canAccess('recovery_viewTeam'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('partner.hrms.lead-orders.recovery') ? 'active' : '' }}"
                                    href="{{ route('partner.hrms.lead-orders.recovery') }}"
                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                    Recovery Amount
                                </a>
                            </li>
                        @endif
                         @if (auth()->user()->canAccess('recoveryhistory_viewAny') ||
                                auth()->user()->canAccess('recoveryhistory_viewOwn') ||
                                auth()->user()->canAccess('recoveryhistory_viewBranch') ||
                                auth()->user()->canAccess('recoveryhistory_viewTeam'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('partner.hrms.lead-orders.recovery-history') ? 'active' : '' }}"
                                    href="{{ route('partner.hrms.lead-orders.recovery-history') }}"
                                    style="padding: 0.3rem 1rem; font-size: 0.8rem;">
                                    Recovery History
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </li>
        @endif

    @endif

    @if (auth()->user()->canAccess('hrmssetting_manage') || auth()->user()->canAccess('attendance_manage'))
        <div class="nav-section-label mt-3">Settings & Config</div>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('partner.hrms.settings') ? 'active' : '' }}"
                href="{{ route('partner.hrms.settings') }}">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </li>
    @endif

@endif
