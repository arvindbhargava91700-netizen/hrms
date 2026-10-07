<div>
    {{-- CSS for Premium Design --}}
    <style>
        .premium-dashboard {
            background: #f4f7fe;
            font-family: 'Inter', sans-serif;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 20px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.1);
        }

        .gradient-text {
            background: linear-gradient(135deg, #4F46E5 0%, #EC4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .icon-blue { background: linear-gradient(135deg, rgba(79,70,229,0.1), rgba(79,70,229,0.2)); color: #4F46E5; }
        .icon-green { background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(16,185,129,0.2)); color: #10B981; }
        .icon-orange { background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(245,158,11,0.2)); color: #F59E0B; }
        .icon-pink { background: linear-gradient(135deg, rgba(236,72,153,0.1), rgba(236,72,153,0.2)); color: #EC4899; }
        .icon-purple { background: linear-gradient(135deg, rgba(139,92,246,0.1), rgba(139,92,246,0.2)); color: #8B5CF6; }
        .icon-red { background: linear-gradient(135deg, rgba(239,68,68,0.1), rgba(239,68,68,0.2)); color: #EF4444; }
        .icon-cyan { background: linear-gradient(135deg, rgba(6,182,212,0.1), rgba(6,182,212,0.2)); color: #06B6D4; }
        .icon-indigo { background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(99,102,241,0.2)); color: #6366F1; }
        .icon-teal { background: linear-gradient(135deg, rgba(20,184,166,0.1), rgba(20,184,166,0.2)); color: #14B8A6; }
        .icon-amber { background: linear-gradient(135deg, rgba(217,119,6,0.1), rgba(217,119,6,0.2)); color: #D97706; }
        .icon-sky { background: linear-gradient(135deg, rgba(14,165,233,0.1), rgba(14,165,233,0.2)); color: #0EA5E9; }
        
        .welcome-banner {
            background: linear-gradient(135deg, #4F46E5 0%, #8B5CF6 100%);
            border-radius: 24px;
            padding: 30px 40px;
            color: white;
            box-shadow: 0 15px 30px rgba(79,70,229,0.3);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::after {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .table-custom th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: #64748B;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 15px;
        }
        
        .table-custom td {
            vertical-align: middle;
            border-bottom: 1px dashed #E2E8F0;
            padding: 15px 10px;
        }
        
        .badge-custom {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
        }
    </style>

    <div class="premium-dashboard p-2">
        
        <!-- Welcome Banner -->
        <div class="welcome-banner mb-5 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-2">Welcome back, {{ auth()->user()->name }}!</h2>
                <p class="mb-0 text-white-50 fs-5">Here is what's happening in your organization today, {{ now()->format('l, jS F Y') }}.</p>
            </div>
            <div style="z-index: 10;">
                <div class="bg-white bg-opacity-25 rounded-4 p-3 text-center backdrop-blur">
                    <div class="fs-6 fw-semibold text-white">Current Time</div>
                    <div class="fs-3 fw-bold font-monospace" id="realTimeClock">{{ now()->format('h:i:s A') }}</div>
                </div>
            </div>
        </div>

        <!-- Quick Action Shortcuts -->
        <div class="glass-card p-3 mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <span class="fw-bold text-dark">Quick Actions:</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('partner.hrms.staff') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-person-plus me-1"></i> Staff Directory
                </a>
                <a href="{{ route('partner.hrms.attendance.manage') }}" class="btn btn-sm btn-outline-success rounded-pill px-3">
                    <i class="bi bi-calendar-check me-1"></i> Attendance
                </a>
                <a href="{{ route('partner.hrms.leaves.approvals') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                    <i class="bi bi-umbrella me-1"></i> Leave Approvals
                </a>
                <a href="{{ route('partner.hrms.tasks') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-plus-circle me-1"></i> Assign Task
                </a>
                <a href="{{ route('partner.hrms.job-posts') }}" class="btn btn-sm btn-outline-info rounded-pill px-3">
                    <i class="bi bi-briefcase me-1"></i> Job Posts
                </a>
                <a href="{{ route('partner.hrms.expenses') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-receipt me-1"></i> Expenses
                </a>
            </div>
        </div>

        <!-- ── Section 1: Workforce & Today's Attendance ──────────────── -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-bold mb-0 text-dark text-uppercase letter-spacing-1 d-flex align-items-center" style="letter-spacing: 0.5px;">
                <i class="bi bi-people me-2 text-primary"></i> Workforce & Attendance Today
            </h6>
            <a href="{{ route('partner.hrms.attendance.manage') }}" class="text-primary text-decoration-none small fw-semibold">
                Manage Attendance <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-4 mb-4">
            <!-- 1. Total Staff -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-blue">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Total Staff</div>
                    <div class="fs-1 fw-bold text-dark">{{ number_format($stats['totalEmployees']) }}</div>
                    <a href="{{ route('partner.hrms.staff') }}" class="mt-2 d-inline-block text-primary fw-semibold text-decoration-none" style="font-size:0.85rem;">
                        View Directory <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(79,70,229,0.03);">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>

            <!-- 2. Present Today -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-green">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Present Today</div>
                    <div class="fs-1 fw-bold text-dark">{{ number_format($stats['attendanceToday']) }}</div>
                    <div class="mt-2 text-success fw-semibold" style="font-size:0.85rem;">
                        <i class="bi bi-dot" style="font-size: 1.5rem; vertical-align: middle;"></i> Active Workforce
                    </div>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(16,185,129,0.03);">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
            </div>

            <!-- 3. On Leave Today -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-sky">
                        <i class="bi bi-person-slash"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">On Leave Today</div>
                    <div class="fs-1 fw-bold text-dark">{{ number_format($stats['onLeaveToday']) }}</div>
                    <a href="{{ route('partner.hrms.leaves.approvals') }}" class="mt-2 d-inline-block text-info fw-semibold text-decoration-none" style="font-size:0.85rem;">
                        Approved Leaves <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(14,165,233,0.03);">
                        <i class="bi bi-person-slash"></i>
                    </div>
                </div>
            </div>

            <!-- 4. Absent Today -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-red">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Absent Today</div>
                    <div class="fs-1 fw-bold text-dark">{{ number_format($stats['absentToday']) }}</div>
                    <a href="{{ route('partner.hrms.attendance.manage') }}" class="mt-2 d-inline-block text-danger fw-semibold text-decoration-none" style="font-size:0.85rem;">
                        View Attendance <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(239,68,68,0.03);">
                        <i class="bi bi-person-x-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Section 2: Operations & Productivity ───────────────────── -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-bold mb-0 text-dark text-uppercase letter-spacing-1 d-flex align-items-center" style="letter-spacing: 0.5px;">
                <i class="bi bi-lightning-fill me-2 text-warning"></i> Operations & Productivity
            </h6>
            <span class="text-muted small">Live workflow tracking</span>
        </div>
        <div class="row g-4 mb-4">
            <!-- 5. Pending Leaves -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-orange">
                        <i class="bi bi-umbrella-fill"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Pending Leaves</div>
                    <div class="fs-1 fw-bold text-dark">{{ number_format($stats['pendingLeaves']) }}</div>
                    <a href="{{ route('partner.hrms.leaves.approvals') }}" class="mt-2 d-inline-block text-warning fw-semibold text-decoration-none" style="font-size:0.85rem;">
                        Review Requests <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(245,158,11,0.03);">
                        <i class="bi bi-umbrella-fill"></i>
                    </div>
                </div>
            </div>

            <!-- 6. Open Tasks -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-pink">
                        <i class="bi bi-list-task"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Open Tasks</div>
                    <div class="fs-1 fw-bold text-dark">{{ number_format($stats['pendingTasks']) }}</div>
                    <a href="{{ route('partner.hrms.tasks') }}" class="mt-2 d-inline-block text-secondary fw-semibold text-decoration-none" style="font-size:0.85rem;">
                        {{ number_format($stats['completedTasksMonth']) }} done this month <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(236,72,153,0.03);">
                        <i class="bi bi-list-task"></i>
                    </div>
                </div>
            </div>

            <!-- 7. Daily Work Reports (Today) -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-indigo">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Work Reports (DWR)</div>
                    <div class="fs-1 fw-bold text-dark">{{ number_format($stats['dwrToday']) }}</div>
                    <a href="{{ route('partner.hrms.team-reports') }}" class="mt-2 d-inline-block text-primary fw-semibold text-decoration-none" style="font-size:0.85rem;">
                        Today's Submissions <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(99,102,241,0.03);">
                        <i class="bi bi-journal-check"></i>
                    </div>
                </div>
            </div>

            <!-- 8. Organization Structure -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-cyan">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Departments</div>
                    <div class="fs-1 fw-bold text-dark">
                        {{ number_format($stats['totalDepartments']) }}
                        <span class="fs-5 text-muted fw-normal">/ {{ number_format($stats['totalBranches']) }} branches</span>
                    </div>
                    <a href="{{ route('partner.hrms.departments') }}" class="mt-2 d-inline-block text-info fw-semibold text-decoration-none" style="font-size:0.85rem;">
                        Manage Structure <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(6,182,212,0.03);">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Section 3: Recruitment, Assets & Business ──────────────── -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-bold mb-0 text-dark text-uppercase letter-spacing-1 d-flex align-items-center" style="letter-spacing: 0.5px;">
                <i class="bi bi-briefcase-fill me-2 text-success"></i> Recruitment, Assets & Finance
            </h6>
            <span class="text-muted small">Overview & Growth</span>
        </div>
        <div class="row g-4 mb-5">
            <!-- 9. Active Job Openings -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-purple">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Active Job Posts</div>
                    <div class="fs-1 fw-bold text-dark">{{ number_format($stats['activeJobPosts']) }}</div>
                    <a href="{{ route('partner.hrms.job-posts') }}" class="mt-2 d-inline-block text-purple fw-semibold text-decoration-none" style="font-size:0.85rem; color: #8B5CF6;">
                        {{ number_format($stats['totalCandidates']) }} Candidates <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(139,92,246,0.03);">
                        <i class="bi bi-briefcase-fill"></i>
                    </div>
                </div>
            </div>

            <!-- 10. Monthly Expenses -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-amber">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Month Expenses</div>
                    <div class="fs-2 fw-bold text-dark">₹{{ number_format($stats['monthlyExpenses'], 2) }}</div>
                    <a href="{{ route('partner.hrms.expenses') }}" class="mt-2 d-inline-block text-warning fw-semibold text-decoration-none" style="font-size:0.85rem;">
                        {{ $stats['pendingAdvances'] }} Pending Advances <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(217,119,6,0.03);">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
            </div>

            <!-- 11. Company Assets -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-teal">
                        <i class="bi bi-laptop-fill"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Allocated Assets</div>
                    <div class="fs-1 fw-bold text-dark">
                        {{ number_format($stats['allocatedAssets']) }}
                        <span class="fs-5 text-muted fw-normal">/ {{ number_format($stats['totalAssets']) }}</span>
                    </div>
                    <a href="{{ route('partner.hrms.assets') }}" class="mt-2 d-inline-block text-teal fw-semibold text-decoration-none" style="font-size:0.85rem; color: #14B8A6;">
                        Manage Inventory <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(20,184,166,0.03);">
                        <i class="bi bi-laptop-fill"></i>
                    </div>
                </div>
            </div>

            <!-- 12. Active CRM Leads -->
            <div class="col-xl-3 col-md-6">
                <div class="glass-card p-4 h-100 position-relative overflow-hidden">
                    <div class="stat-icon-wrapper icon-blue">
                        <i class="bi bi-funnel-fill"></i>
                    </div>
                    <div class="text-muted fw-semibold mb-1 text-uppercase" style="font-size: 0.78rem; letter-spacing:1px;">Active CRM Leads</div>
                    <div class="fs-1 fw-bold text-dark">{{ number_format($stats['activeLeads']) }}</div>
                    <a href="{{ route('partner.hrms.leads.index') }}" class="mt-2 d-inline-block text-primary fw-semibold text-decoration-none" style="font-size:0.85rem;">
                        View Pipeline <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <div class="position-absolute" style="bottom: -20px; right: -10px; font-size: 100px; color: rgba(79,70,229,0.03);">
                        <i class="bi bi-funnel-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="row g-4 mb-5">
            <div class="col-xl-8">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold mb-4 text-dark d-flex align-items-center">
                        <i class="bi bi-graph-up-arrow me-2 text-primary"></i> Weekly Attendance Trends
                    </h5>
                    <div style="height: 300px; width: 100%;" wire:ignore>
                        <canvas id="mainAttendanceChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-4">
                <div class="glass-card p-4 h-100">
                    <h5 class="fw-bold mb-4 text-dark d-flex align-items-center">
                        <i class="bi bi-pie-chart-fill me-2 text-primary"></i> Task Distribution
                    </h5>
                    <div style="height: 250px; width: 100%; display: flex; align-items: center; justify-content: center;" wire:ignore>
                        <canvas id="taskDistributionChart"></canvas>
                    </div>
                    <div class="mt-4 text-center">
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2 me-2">Completed: {{ $chartData['taskStatusData'][0] }}</span>
                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-2">Pending: {{ $chartData['taskStatusData'][1] + $chartData['taskStatusData'][2] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row g-4">
            <div class="col-xl-6">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 text-dark">Recent Leave Requests</h5>
                        <a href="{{ route('partner.hrms.leaves.approvals') }}" class="btn btn-sm btn-light border rounded-pill px-3">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLeaves as $leave)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width:40px;height:40px;">
                                                {{ substr($leave->employee?->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $leave->employee?->name ?? 'Staff Member' }}</div>
                                                <div class="text-muted small">{{ ucfirst($leave->type ?? 'Leave') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M') }}</div>
                                    </td>
                                    <td>
                                        @if($leave->status === 'pending')
                                            <span class="badge badge-custom bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Pending</span>
                                        @elseif($leave->status === 'approved')
                                            <span class="badge badge-custom bg-success bg-opacity-10 text-success border border-success border-opacity-25">Approved</span>
                                        @else
                                            <span class="badge badge-custom bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No recent leave requests.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0 text-dark">Latest Task Updates</h5>
                        <a href="{{ route('partner.hrms.tasks') }}" class="btn btn-sm btn-light border rounded-pill px-3">View All</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th>Task Name</th>
                                    <th>Assigned To</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTasks as $task)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 200px;">{{ $task->title }}</div>
                                        <div class="text-muted small">Due: {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width:28px;height:28px;font-size:10px;">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <span class="fw-semibold">{{ $task->employee?->name ?? 'Unassigned' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($task->status === 'completed')
                                            <span class="badge badge-custom bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle me-1"></i> Completed</span>
                                        @elseif($task->status === 'in_progress')
                                            <span class="badge badge-custom bg-primary bg-opacity-10 text-primary"><i class="bi bi-play-circle me-1"></i> In Progress</span>
                                        @else
                                            <span class="badge badge-custom bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-circle me-1"></i> Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">No recent tasks.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        // Realtime Clock with seconds
        function updateClock() {
            const clockEl = document.getElementById('realTimeClock');
            if (!clockEl) return;
            const now = new Date();
            let hours = now.getHours();
            let minutes = now.getMinutes();
            let seconds = now.getSeconds();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; 
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            clockEl.innerText = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Chart.js init
        document.addEventListener('livewire:initialized', () => {
            const ctxAtt = document.getElementById('mainAttendanceChart');
            if (ctxAtt) {
                new Chart(ctxAtt, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($chartData['attendanceLabels']) !!},
                        datasets: [{
                            label: 'Staff Present',
                            data: {!! json_encode($chartData['attendanceData']) !!},
                            borderColor: '#4F46E5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#4F46E5',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            const ctxTask = document.getElementById('taskDistributionChart');
            if (ctxTask) {
                new Chart(ctxTask, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'In Progress', 'Pending'],
                        datasets: [{
                            data: {!! json_encode($chartData['taskStatusData']) !!},
                            backgroundColor: ['#10B981', '#3B82F6', '#F59E0B'],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                        }
                    }
                });
            }
        });
    </script>
    @endscript
</div>
