<div>
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-dark">Welcome back, {{ auth()->user()->name }}!</h4>
            <p class="text-muted mb-0">{{ now()->format('l, jS F Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            @if(auth()->user()->canAccess('attendance_create') || auth()->user()->canAccess('attendance_update'))
                @if(isset($stats['myAttendanceToday']) && $stats['myAttendanceToday']?->status === 'punch_out')
                    {{-- Punched Out --}}
                @elseif(isset($stats['myAttendanceToday']) && $stats['myAttendanceToday'])
                    <a href="{{ route('partner.hrms.attendance.mark') }}" class="btn btn-warning shadow-sm"><i class="bi bi-box-arrow-right"></i> Punch Out</a>
                @else
                    <a href="{{ route('partner.hrms.attendance.mark') }}" class="btn btn-primary shadow-sm"><i class="bi bi-box-arrow-in-right"></i> Punch In</a>
                @endif
            @endif
            @if(auth()->user()->canAccess('leave_create'))
                <a href="{{ route('partner.hrms.leaves.apply') }}" class="btn btn-light shadow-sm border"><i class="bi bi-umbrella me-1"></i> Apply Leave</a>
            @endif
            @if(auth()->user()->canAccess('task_create'))
                <a href="{{ route('partner.hrms.tasks') }}" class="btn btn-light shadow-sm border"><i class="bi bi-list-task me-1"></i> New Task</a>
            @endif
        </div>
    </div>

    {{-- ────────────────────────────────────────────────
         TODAY'S ATTENDANCE STATUS BANNER
    ──────────────────────────────────────────────── --}}
    @if(auth()->user()->canAccess('attendance_viewOwn') || auth()->user()->canAccess('attendance_create') || auth()->user()->canAccess('attendance_viewAny'))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-body p-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        @if($stats['isHoliday'])
                            <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-gift fs-4"></i></div>
                            <div><h5 class="fw-bold mb-1 text-info">Holiday: {{ $stats['holidayName'] }}</h5><p class="text-muted mb-0 small">Enjoy your holiday!</p></div>
                        @elseif($stats['isWeekOff'])
                            <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-cup-hot fs-4"></i></div>
                            <div><h5 class="fw-bold mb-1 text-secondary">Week Off</h5><p class="text-muted mb-0 small">Enjoy your weekend!</p></div>
                        @elseif($stats['isFullLeave'])
                            <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-umbrella fs-4"></i></div>
                            <div><h5 class="fw-bold mb-1 text-warning">On Leave</h5><p class="text-muted mb-0 small">Take rest and enjoy your day off!</p></div>
                        @elseif($stats['isHalfLeave'])
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-clock-half fs-4"></i></div>
                            <div><h5 class="fw-bold mb-1 text-primary">Half-Day Leave</h5><p class="text-muted mb-0 small">Working partial hours today.</p></div>
                        @elseif($stats['isAbsent'])
                            <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-x-circle fs-4"></i></div>
                            <div><h5 class="fw-bold mb-1 text-danger">Marked Absent</h5><p class="text-muted mb-0 small">You are marked absent for today.</p></div>
                        @elseif(isset($stats['myAttendanceToday']) && $stats['myAttendanceToday'])
                            @if($stats['myAttendanceToday']->status === 'punch_out')
                                <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-check-circle fs-4"></i></div>
                                <div><h5 class="fw-bold mb-1 text-success">Punched Out</h5><p class="text-muted mb-0 small">In {{ \Carbon\Carbon::parse($stats['myAttendanceToday']->check_in)->format('h:i A') }} · Out {{ \Carbon\Carbon::parse($stats['myAttendanceToday']->check_out)->format('h:i A') }}</p></div>
                            @elseif($stats['myAttendanceToday']->status === 'half_day')
                                <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-clock-history fs-4"></i></div>
                                <div><h5 class="fw-bold mb-1 text-warning">Half Day Logged</h5><p class="text-muted mb-0 small">In {{ \Carbon\Carbon::parse($stats['myAttendanceToday']->check_in)->format('h:i A') }} · Out {{ \Carbon\Carbon::parse($stats['myAttendanceToday']->check_out)->format('h:i A') }}</p></div>
                            @else
                                <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-briefcase fs-4"></i></div>
                                <div><h5 class="fw-bold mb-1 text-primary">Punched In</h5><p class="text-muted mb-0 small">Since {{ \Carbon\Carbon::parse($stats['myAttendanceToday']->check_in)->format('h:i A') }}</p></div>
                            @endif
                        @else
                            <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-exclamation-circle fs-4"></i></div>
                            <div><h5 class="fw-bold mb-1 text-danger">Not Punched In</h5><p class="text-muted mb-0 small">Please mark your attendance to start your day.</p></div>
                        @endif
                    </div>
                    
                    @if(auth()->user()->canAccess('attendance_create') || auth()->user()->canAccess('attendance_update'))
                        @if(!$stats['isHoliday'] && !$stats['isWeekOff'] && !$stats['isFullLeave'] && !$stats['isAbsent'])
                            @if(isset($stats['myAttendanceToday']) && in_array($stats['myAttendanceToday']?->status, ['punch_out', 'half_day']))
                                {{-- Don't show punch button --}}
                            @elseif(isset($stats['myAttendanceToday']) && $stats['myAttendanceToday'])
                                <a href="{{ route('partner.hrms.attendance.mark') }}" class="btn btn-warning shadow-sm rounded-pill px-4 fw-bold"><i class="bi bi-box-arrow-right me-1"></i> Punch Out</a>
                            @else
                                <a href="{{ route('partner.hrms.attendance.mark') }}" class="btn btn-primary shadow-sm rounded-pill px-4 fw-bold"><i class="bi bi-box-arrow-in-right me-1"></i> Punch In</a>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ────────────────────────────────────────────────
         MY SUMMARY CARDS
    ──────────────────────────────────────────────── --}}
    <h6 class="mb-3 text-uppercase text-muted fw-bold" style="font-size:12px;letter-spacing:1px;">My Summary</h6>
    <div class="row g-3 mb-4">
        @if(auth()->user()->canAccess('attendance_viewOwn') || auth()->user()->canAccess('attendance_viewAny') || auth()->user()->canAccess('attendance_viewTeam'))
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-success-soft"><i class="bi bi-calendar-check text-success"></i></div>
                <div>
                    <div class="stat-label">My Attendance (Month)</div>
                    <div class="stat-value">{{ $stats['myAttendanceThisMonth'] ?? 0 }} <span class="fs-14 fw-normal text-muted">Days</span></div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->canAccess('leave_viewOwn') || auth()->user()->canAccess('leave_viewAny') || auth()->user()->canAccess('leave_viewTeam'))
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="{{ ($stats['myPendingLeaves'] ?? 0) > 0 ? 'border-left:4px solid var(--bs-warning);' : '' }}">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-umbrella text-warning"></i></div>
                <div>
                    <div class="stat-label">My Pending Leaves</div>
                    <div class="stat-value">{{ $stats['myPendingLeaves'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->canAccess('task_viewAny') || auth()->user()->canAccess('task_viewTeam') || auth()->user()->canAccess('task_viewOwn'))
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="{{ ($stats['myPendingTasks'] ?? 0) > 0 ? 'border-left:4px solid var(--bs-danger);' : '' }}">
                <div class="stat-icon bg-danger bg-opacity-10"><i class="bi bi-list-task text-danger"></i></div>
                <div>
                    <div class="stat-label">{{ $stats['taskLabel'] ?? 'Tasks' }}</div>
                    <div class="stat-value">{{ $stats['myPendingTasks'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->canAccess('expense_viewAny') || auth()->user()->canAccess('expense_viewTeam') || auth()->user()->canAccess('expense_viewOwn'))
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-receipt text-warning"></i></div>
                <div>
                    <div class="stat-label">{{ $stats['expenseLabel'] ?? 'Expenses' }}</div>
                    <div class="stat-value">₹{{ number_format($stats['myExpensesAmount'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ────────────────────────────────────────────────
         PERFORMANCE & MY TARGET / ATTENDANCE CHART
    ──────────────────────────────────────────────── --}}
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size:12px;letter-spacing:1px;">Performance & Attendance</h6>
    <div class="row g-4 mb-4">
        @if(auth()->user()->canAccess('attendance_viewOwn') || auth()->user()->canAccess('attendance_viewAny'))
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4 text-dark">My Weekly Attendance (Hours)</h6>
                    <div style="position: relative; height:250px; width:100%">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->canAccess('lead_viewOwn') || auth()->user()->canAccess('lead_viewAny') || auth()->user()->canAccess('lead_viewTeam'))
<div class="col-xl-6">
    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">

        <div class="card-body p-4">

            <!-- Header -->
            <div class="d-flex justify-content-end align-items-center mb-4">
                <a href="{{ route('partner.hrms.my-targets') }}"
                   class="btn btn-sm btn-outline-primary rounded-pill px-3"
                   style="font-size:12px;">
                    <i class="bi bi-bullseye me-1"></i> Full Details
                </a>
            </div>

            <!-- Two Cards -->
            <div class="row g-3">

                <!-- ================= TARGET OVERVIEW ================= -->
                <div class="col-12 col-md-6">

                    <div class="card border shadow-sm rounded-3 h-100 mb-0">

                        <div class="card-body p-3">

                            <h6 class="fw-bold mb-3 text-dark">
                                Target Overview
                            </h6>

                            @if(isset($stats['myTarget']) && $stats['myTarget'])

                                @php
                                    $mt = $stats['myTarget'];

                                    $pct = number_format($mt['target_pct'], 1);

                                    $barColor = $mt['target_achieved']
                                        ? 'bg-success'
                                        : ($mt['target_pct'] >= 50
                                            ? 'bg-primary'
                                            : 'bg-warning');

                                    $svgColor = $mt['target_achieved']
                                        ? '#198754'
                                        : ($mt['target_pct'] >= 50
                                            ? '#0d6efd'
                                            : '#ffc107');

                                    $targetGraphPct = min(
                                        max((float) $mt['target_pct'], 0),
                                        100
                                    );
                                @endphp

                                <!-- Circle + Information -->
                                <div class="d-flex align-items-center gap-3 mb-3">

                                    <div style="
                                        position:relative;
                                        width:75px;
                                        height:75px;
                                        flex-shrink:0;
                                    ">

                                        <svg viewBox="0 0 36 36"
                                             style="
                                                width:75px;
                                                height:75px;
                                                transform:rotate(-90deg);
                                             ">

                                            <circle
                                                cx="18"
                                                cy="18"
                                                r="15.9"
                                                fill="none"
                                                stroke="#e9ecef"
                                                stroke-width="3"
                                            />

                                            <circle
                                                cx="18"
                                                cy="18"
                                                r="15.9"
                                                fill="none"
                                                stroke="{{ $svgColor }}"
                                                stroke-width="3"
                                                stroke-dasharray="{{ $targetGraphPct }} {{ 100 - $targetGraphPct }}"
                                                stroke-linecap="round"
                                            />

                                        </svg>

                                        <div style="
                                            position:absolute;
                                            inset:0;
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                        ">
                                            <span class="fw-bold"
                                                  style="font-size:13px;">
                                                {{ $pct }}%
                                            </span>
                                        </div>

                                    </div>

                                    <div class="flex-grow-1">

                                        <p class="text-muted mb-1 fw-semibold text-uppercase"
                                           style="font-size:10px;">
                                            {{ now()->format('F Y') }}
                                        </p>

                                        <div class="fw-bold text-dark"
                                             style="font-size:15px;">
                                            ₹{{ number_format($mt['business_achieved'], 2) }}
                                        </div>

                                        <div class="text-muted"
                                             style="font-size:11px;">
                                            of ₹{{ number_format($mt['monthly_target'], 2) }} target
                                        </div>

                                        @if($mt['target_achieved'])

                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill mt-1"
                                                  style="font-size:9px;">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Target Achieved!
                                            </span>

                                        @else

                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill mt-1"
                                                  style="font-size:9px;">
                                                <i class="bi bi-hourglass-split me-1"></i>
                                                In Progress
                                            </span>

                                        @endif

                                    </div>

                                </div>

                                <!-- Progress -->
                                <div class="progress mb-3"
                                     style="height:7px;border-radius:4px;">

                                    <div class="progress-bar {{ $barColor }}"
                                         role="progressbar"
                                         style="width:{{ $targetGraphPct }}%;"
                                         aria-valuenow="{{ $mt['target_pct'] }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>

                                </div>

                                <!-- Bottom Stats -->
                                <div class="row g-2">

                                    <div class="col-6">

                                        <div class="p-2 rounded-3 bg-success bg-opacity-10 text-center">

                                            <div class="text-muted"
                                                 style="
                                                    font-size:9px;
                                                    font-weight:600;
                                                    text-transform:uppercase;
                                                 ">
                                                Commission Earned
                                            </div>

                                            <div class="fw-bold text-success"
                                                 style="font-size:12px;">
                                                ₹{{ number_format($mt['est_commission'], 0) }}
                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-6">

                                        <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-center">

                                            <div class="text-muted"
                                                 style="
                                                    font-size:9px;
                                                    font-weight:600;
                                                    text-transform:uppercase;
                                                 ">
                                                Target <br>Progress
                                            </div>

                                            <div class="fw-bold text-primary"
                                                 style="font-size:12px;">
                                                {{ $pct }}%
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @else

                                <div class="text-center text-muted py-4">

                                    <i class="bi bi-bullseye text-light"
                                       style="font-size:3.5rem;"></i>

                                    <p class="mt-3 mb-1"
                                       style="font-size:13px;">
                                        No target set for this month.
                                    </p>

                                    <small style="font-size:10px;">
                                        Contact your manager to set a monthly target.
                                    </small>

                                </div>

                            @endif

                        </div>
                    </div>

                </div>


                <!-- ================= MERCHANT PROGRESS ================= -->
                <div class="col-12 col-md-6">

                    <div class="card border shadow-sm rounded-3 h-100 mb-0">

                        <div class="card-body p-3">

                            <h6 class="fw-bold mb-3 text-dark">
                                Merchant Progress
                            </h6>

                            @php
                                $merchantTarget = (int) ($stats['target'] ?? 0);
                                $wonLeads = (int) ($stats['won_leads'] ?? 0);
                                $merchantPct = max(
                                    0,
                                    (float) ($stats['percentage'] ?? 0)
                                );

                                $graphPct = min($merchantPct, 100);

                                $merchantColor = $merchantPct >= 100
                                    ? '#198754'
                                    : ($merchantPct >= 50
                                        ? '#0d6efd'
                                        : '#2acf0d');
                            @endphp

                            @if($merchantTarget > 0)

                                <!-- Circle + Information -->
                                <div class="d-flex align-items-center gap-3 mb-3">

                                    <div style="
                                        position:relative;
                                        width:75px;
                                        height:75px;
                                        flex-shrink:0;
                                    ">

                                        <svg viewBox="0 0 36 36"
                                             style="
                                                width:75px;
                                                height:75px;
                                                transform:rotate(-90deg);
                                             ">

                                            <circle
                                                cx="18"
                                                cy="18"
                                                r="15.9"
                                                fill="none"
                                                stroke="#e9ecef"
                                                stroke-width="3"
                                            />

                                            <circle
                                                cx="18"
                                                cy="18"
                                                r="15.9"
                                                fill="none"
                                                stroke="{{ $merchantColor }}"
                                                stroke-width="3"
                                                stroke-dasharray="{{ $graphPct }} {{ 100 - $graphPct }}"
                                                stroke-linecap="round"
                                            />

                                        </svg>

                                        <div style="
                                            position:absolute;
                                            inset:0;
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                        ">
                                            <span class="fw-bold"
                                                  style="font-size:13px;">
                                                {{ number_format($merchantPct, 1) }}%
                                            </span>
                                        </div>

                                    </div>

                                    <div class="flex-grow-1">

                                        <p class="text-muted mb-1 fw-semibold text-uppercase"
                                           style="font-size:10px;">
                                            {{ now()->format('F Y') }}
                                        </p>

                                        <div class="fw-bold text-dark"
                                             style="font-size:15px;">
                                            {{ $wonLeads }} Won Leads
                                        </div>

                                        <div class="text-muted"
                                             style="font-size:11px;">
                                            of {{ $merchantTarget }} merchant target
                                        </div>

                                        @if($merchantPct >= 100)

                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill mt-1"
                                                  style="font-size:9px;">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Target Achieved!
                                            </span>

                                        @else

                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill mt-1"
                                                  style="font-size:9px;">
                                                <i class="bi bi-hourglass-split me-1"></i>
                                                In Progress
                                            </span>

                                        @endif

                                    </div>

                                </div>

                                <!-- Progress -->
                                <div class="progress mb-3"
                                     style="height:7px;border-radius:4px;">

                                    <div class="progress-bar"
                                         role="progressbar"
                                         style="
                                            width:{{ $graphPct }}%;
                                            background-color:{{ $merchantColor }};
                                         "
                                         aria-valuenow="{{ $merchantPct }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>

                                </div>

                                <!-- Bottom Stats -->
                                <div class="row g-2">

                                    <div class="col-6">

                                        <div class="p-2 rounded-3 bg-success bg-opacity-10 text-center">

                                            <div class="text-muted"
                                                 style="
                                                    font-size:9px;
                                                    font-weight:600;
                                                    text-transform:uppercase;
                                                 ">
                                                Won <br> Leads
                                            </div>

                                            <div class="fw-bold text-success"
                                                 style="font-size:12px;">
                                                {{ $wonLeads }}
                                            </div>

                                        </div>

                                    </div>

                                    <div class="col-6">

                                        <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-center">

                                            <div class="text-muted"
                                                 style="
                                                    font-size:9px;
                                                    font-weight:600;
                                                    text-transform:uppercase;
                                                 ">
                                               Merchant <br> Progress
                                            </div>

                                            <div class="fw-bold text-primary"
                                                 style="font-size:12px;">
                                                {{ number_format($merchantPct, 1) }}%
                                            </div>

                                        </div>

                                    </div>

                                </div>

                            @else

                                <div class="text-center text-muted py-4">

                                    <i class="bi bi-shop text-light"
                                       style="font-size:3.5rem;"></i>

                                    <p class="mt-3 mb-1"
                                       style="font-size:13px;">
                                        No merchant target set.
                                    </p>

                                    <small style="font-size:10px;">
                                        Contact your manager to set a merchant target.
                                    </small>

                                </div>

                            @endif

                        </div>
                    </div>

                </div>

            </div>

        </div>
    </div>
</div>
        @endif
    </div>

    {{-- ────────────────────────────────────────────────
         ANALYTICS & CHARTS
    ──────────────────────────────────────────────── --}}
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size:12px;letter-spacing:1px;">Analytics & Charts</h6>
    <div class="row g-4 mb-4">
        {{-- Task Performance Trend --}}
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4" wire:ignore>
                    <h6 class="fw-bold mb-4 text-dark">Monthly Task Completion</h6>
                    <div style="position: relative; height:300px; width:100%">
                        <canvas id="monthlyTaskChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Task Status Distribution --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4" wire:ignore>
                    <h6 class="fw-bold mb-4 text-dark">Task Status Distribution</h6>
                    <div style="position: relative; height:300px; width:100%;">
                        <canvas id="taskStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ────────────────────────────────────────────────
         HIGHEST PERFORMING
    ──────────────────────────────────────────────── --}}
    @if(auth()->user()->canAccess('task_viewAny') || auth()->user()->canAccess('task_viewTeam') || auth()->user()->isPartner())
    @if(!empty($chartData['topEmployeesLabels']))
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size:12px;letter-spacing:1px;">Highest Performing Employees</h6>
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4" wire:ignore>
                    <h6 class="fw-bold mb-4 text-dark">Top 5 by Tasks Completed</h6>
                    <div style="position: relative; height:300px; width:100%">
                        <canvas id="topEmployeesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- ────────────────────────────────────────────────
         TEAM OVERVIEW (managers / team leads)
    ──────────────────────────────────────────────── --}}
    @if(isset($stats['hrmsStaff']) && (auth()->user()->canAccess('staff_viewAny') || auth()->user()->canAccess('attendance_viewAny') || auth()->user()->canAccess('attendance_viewTeam') || auth()->user()->canAccess('staff_viewTeam') || auth()->user()->canAccess('task_viewTeam')))
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size:12px;letter-spacing:1px;">
        {{ auth()->user()->canAccess('staff_viewAny') ? 'Company' : 'My Team' }} Overview
    </h6>
    <div class="row g-3 mb-4">
        {{-- Team / Staff count --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="border-left:4px solid #6366f1;">
                <div class="stat-icon" style="background:#6366f115;"><i class="bi bi-people" style="color:#6366f1;"></i></div>
                <div>
                    <div class="stat-label">{{ auth()->user()->canAccess('staff_viewAny') ? 'Total Staff' : 'Team Members' }}</div>
                    <div class="stat-value">{{ number_format($stats['hrmsStaff'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        {{-- Team attendance today --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="border-left:4px solid #0ea5e9;">
                <div class="stat-icon" style="background:#0ea5e915;"><i class="bi bi-calendar-check" style="color:#0ea5e9;"></i></div>
                <div>
                    <div class="stat-label">Present Today</div>
                    <div class="stat-value">{{ number_format($stats['hrmsAttendanceToday'] ?? 0) }}
                        @if(($stats['hrmsStaff'] ?? 0) > 0)
                            <span class="fs-14 fw-normal text-muted">/ {{ $stats['hrmsStaff'] }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending team leaves --}}
        @if(isset($stats['hrmsPendingLeaves']))
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="{{ ($stats['hrmsPendingLeaves'] ?? 0) > 0 ? 'border-left:4px solid #f59e0b;' : '' }}">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-umbrella text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending Leave Requests</div>
                    <div class="stat-value {{ ($stats['hrmsPendingLeaves'] ?? 0) > 0 ? 'text-warning' : '' }}">{{ number_format($stats['hrmsPendingLeaves'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Pending team tasks --}}
        @if(isset($stats['hrmsPendingTasks']))
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="{{ ($stats['hrmsPendingTasks'] ?? 0) > 0 ? 'border-left:4px solid #ef4444;' : '' }}">
                <div class="stat-icon bg-danger bg-opacity-10"><i class="bi bi-list-task text-danger"></i></div>
                <div>
                    <div class="stat-label">Open Tasks</div>
                    <div class="stat-value">{{ number_format($stats['hrmsPendingTasks'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Team total business & commission --}}
        @if(isset($stats['teamTotalTarget']))
          <div class="col-xl-3 col-md-4 col-sm-6">
              <div class="stat-card" style="border-left:4px solid #6366f1;">
                  <div class="stat-icon" style="background:#6366f115;"><i class="bi bi-bullseye" style="color:#6366f1;"></i></div>
                  <div>
                      <div class="stat-label">Team Target (Month)</div>
                      <div class="stat-value" style="color:#6366f1;">₹{{ number_format($stats['teamTotalTarget'] ?? 0, 0) }}</div>
                  </div>
              </div>
          </div>
          @endif
          @if(isset($stats['teamTotalBusiness']))
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="border-left:4px solid #10b981;">
                <div class="stat-icon" style="background:#10b98115;"><i class="bi bi-graph-up-arrow" style="color:#10b981;"></i></div>
                <div>
                    <div class="stat-label">Team Business (Month)</div>
                    <div class="stat-value" style="color:#10b981;">₹{{ number_format($stats['teamTotalBusiness'] ?? 0, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="border-left:4px solid #059669;">
                <div class="stat-icon" style="background:#05966915;"><i class="bi bi-cash-coin" style="color:#059669;"></i></div>
                <div>
                    <div class="stat-label">{{ auth()->user()->canAccess('staff_viewAny') ? 'Company' : 'Team' }} Commission (Month)</div>
                    <div class="stat-value" style="color:#059669;">₹{{ number_format($stats['teamTotalCommission'] ?? 0, 0) }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Active / Inactive --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="border-left:4px solid #22c55e;">
                <div class="stat-icon" style="background:#22c55e15;"><i class="bi bi-people-fill" style="color:#22c55e;"></i></div>
                <div>
                    <div class="stat-label">Active / Inactive</div>
                    <div class="stat-value">{{ number_format($stats['hrmsActive'] ?? 0) }} <span class="fs-14 fw-normal text-muted">/ {{ number_format($stats['hrmsInactive'] ?? 0) }}</span></div>
                </div>
            </div>
        </div>

        {{-- Department-wise --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="border-left:4px solid #8b5cf6;">
                <div class="stat-icon" style="background:#8b5cf615;"><i class="bi bi-diagram-3" style="color:#8b5cf6;"></i></div>
                <div>
                    <div class="stat-label">{{ auth()->user()->canAccess('staff_viewAny') ? 'Departments' : 'Departments (Team)' }}</div>
                    <div class="stat-value">{{ number_format($stats['hrmsDepartments'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        {{-- Branch-wise --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="border-left:4px solid #0ea5e9;">
                <div class="stat-icon" style="background:#0ea5e915;"><i class="bi bi-geo-alt" style="color:#0ea5e9;"></i></div>
                <div>
                    <div class="stat-label">{{ auth()->user()->canAccess('staff_viewAny') ? 'Branches' : 'Branches (Team)' }}</div>
                    <div class="stat-value">{{ number_format($stats['hrmsBranches'] ?? 0) }}</div>
                </div>
            </div>
        </div>

        {{-- New Joiners --}}
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card" style="border-left:4px solid #f59e0b;">
                <div class="stat-icon" style="background:#f59e0b15;"><i class="bi bi-person-plus" style="color:#f59e0b;"></i></div>
                <div>
                    <div class="stat-label">New Joiners (Month)</div>
                    <div class="stat-value">{{ number_format($stats['hrmsNewJoiners'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif
 @if (auth()->user()->canAccess('performance_viewAny') || auth()->user()->canAccess('performance_viewTeam') || auth()->user()->canAccess('performance_viewBranch'))
     {{-- Team Wise Top Achievers (HRMS) --}}
    @if(isset($stats['teamAchievers']))
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
                        @foreach($stats['performanceBranches'] ?? [] as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-4 col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-diagram-3 me-1"></i>Department</label>
                    <select wire:model.live="performanceDepartment" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($stats['performanceDepartments'] ?? [] as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-4 col-md-12">
                    <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-person me-1"></i>Employee</label>
                    <select wire:model.live="performanceEmployee" class="form-select">
                        <option value="">All Employees</option>
                        @foreach($stats['performanceEmployees'] ?? [] as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm rounded-4 h-100">
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
                                @forelse($stats['teamAchievers'] as $row)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold">{{ $row['name'] }}</div><small class="text-muted">{{ $row['employee_code'] ?: 'No employee code' }}</small>
                                    </td>
                                    <td class="text-muted">{{ $row['department'] }}</td>
                                    <!-- <td class="text-center" style="min-width:130px;">
                                        <div class="fw-bold fs-5 text-dark">{{ $row['attendance_score'] }}</div><small class="text-muted">{{ $row['attendance'] }}/{{ $row['days_in_month'] }} days</small>
                                        <div class="progress mx-auto mt-1" style="height:4px;max-width:130px;">
                                            <div class="progress-bar bg-success" style="width:{{ ($row['attendance_score'] / 25) * 100 }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center" style="min-width:130px;">
                                        <div class="fw-bold fs-5 text-dark">{{ $row['task_score'] }}</div><small class="text-muted">{{ $row['completed_tasks'] }}/{{ max(1, $row['tasks']) }} tasks</small>
                                        <div class="progress mx-auto mt-1" style="height:4px;max-width:130px;">
                                            <div class="progress-bar bg-primary" style="width:{{ ($row['task_score'] / 25) * 100 }}%;"></div>
                                        </div>
                                    </td>
                                
                                    <td class="text-center" style="min-width:130px;">
                                        @php $merchantPercentage = $row['merchant_target'] > 0 ? min(100, ($row['merchants'] / $row['merchant_target']) * 100) : 0; @endphp
                                        <div class="fw-bold fs-5 text-dark">{{ number_format($row['merchants'], 0) }}/{{ number_format($row['merchant_target'], 0) }}</div>
                                        <small class="text-muted">{{ number_format($merchantPercentage, 1) }}% · {{ $row['merchant_score'] }}/25</small>
                                        <div class="progress mx-auto mt-1" style="height:4px;max-width:130px;"><div class="progress-bar bg-info" style="width:{{ $merchantPercentage }}%;"></div></div>
                                    </td>
                                    <td class="text-center" style="min-width:155px;">
                                        <div class="fw-bold">₹{{ number_format($row['monthly_achieved'], 2) }} / ₹{{ number_format($row['monthly_target'], 2) }}</div>
                                        <small class="text-muted">{{ number_format($row['monthly_percentage'], 1) }}% · {{ $row['monthly_target_score'] }}/25</small>
                                        <div class="progress mx-auto mt-1" style="height:4px;max-width:150px;"><div class="progress-bar bg-warning" style="width:{{ $row['monthly_percentage'] }}%;"></div></div>
                                    </td> -->
                                    <td class="text-center" style="min-width:100px;">
                                        <div class="fw-bold fs-5 text-{{ $row['total_score'] >= 70 ? 'primary' : ($row['total_score'] >= 60 ? 'warning' : 'danger') }}">{{ $row['total_score'] }}</div>
                                        <div class="progress mx-auto mt-1" style="height:5px;max-width:60px;">
                                            <div class="progress-bar bg-{{ $row['total_score'] >= 70 ? 'primary' : ($row['total_score'] >= 60 ? 'warning' : 'danger') }}" style="width:{{ min(100, $row['total_score']) }}%;"></div>
                                        </div>
                                    </td>
                                    <td class="text-center"><span class="badge bg-{{ $row['grade'] === 'A+' ? 'success' : ($row['grade'] === 'A' ? 'primary' : ($row['grade'] === 'B+' ? 'info' : 'warning')) }}">{{ $row['grade'] }}</span></td>
                                
                                    <td class="text-center"><a href="{{ route('partner.hrms.report.performance-profile', ['id' => $row['employee_id']]) }}" class="btn btn-sm btn-outline-primary" title="View employee profile"><i class="bi bi-eye"></i><span class="visually-hidden">View</span></a></td>
                                
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">No performance data found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
      @endif
    {{-- ────────────────────────────────────────────────
         DEPARTMENT PERFORMANCE
    ──────────────────────────────────────────────── --}}
    @if(isset($stats['departmentPerformance']) && count($stats['departmentPerformance']) > 0)
    <h6 class="fw-bold text-secondary mb-3 mt-4 text-uppercase" style="font-size:0.85rem;letter-spacing:0.5px;">Department Performance</h6>
    <div class="row g-4 mb-4">
        @foreach($stats['departmentPerformance'] as $dept)
        <div class="col-xl-6 col-md-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="bi bi-diagram-3 fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $dept['name'] }}</h6>
                                <small class="text-muted">Head: {{ $dept['head'] }}</small>
                            </div>
                        </div>
                        <span class="badge bg-light text-dark border"><i class="bi bi-people me-1"></i>{{ $dept['staffCount'] }} Staff</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="p-2 bg-success bg-opacity-10 rounded-3 text-center">
                                <div class="text-muted small mb-1" style="font-size:10px;">Attendance Today</div>
                                <div class="fw-bold text-success">{{ $dept['attendanceToday'] }}<span class="text-muted fw-normal small"> / {{ $dept['staffCount'] }}</span></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-warning bg-opacity-10 rounded-3 text-center">
                                <div class="text-muted small mb-1" style="font-size:10px;">Pending Leaves</div>
                                <div class="fw-bold text-warning">{{ $dept['pendingLeaves'] }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-danger bg-opacity-10 rounded-3 text-center">
                                <div class="text-muted small mb-1" style="font-size:10px;">Open Tasks</div>
                                <div class="fw-bold text-danger">{{ $dept['pendingTasks'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ────────────────────────────────────────────────
         CRM & SALES STATS
    ──────────────────────────────────────────────── --}}
    @if(auth()->user()->canAccess('lead_viewAny') || auth()->user()->canAccess('lead_viewOwn') || auth()->user()->isPartner() || auth()->user()->canAccess('lead_viewTeam'))
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size:12px;letter-spacing:1px;">CRM & Sales</h6>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-info bg-opacity-10"><i class="bi bi-funnel text-info"></i></div>
                <div>
                    <div class="stat-label">Total Leads</div>
                    <div class="stat-value">{{ number_format($stats['totalLeads'] ?? 0) }} <span class="fs-14 fw-normal text-muted">({{ number_format($stats['wonLeads'] ?? 0) }} Won)</span></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-success bg-opacity-10"><i class="bi bi-bag-check text-success"></i></div>
                <div>
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value">{{ number_format($stats['totalOrders'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary bg-opacity-10"><i class="bi bi-currency-rupee text-primary"></i></div>
                <div>
                    <div class="stat-label">Pipeline Value</div>
                    <div class="stat-value">₹{{ number_format($stats['totalPipelineValue'] ?? 0, 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ────────────────────────────────────────────────
         APPROVAL QUEUE
    ──────────────────────────────────────────────── --}}
    @if(isset($stats['pipelineQueues']) && count($stats['pipelineQueues']) > 0)
    <h6 class="mb-3 text-uppercase text-muted fw-bold mt-4" style="font-size:12px;letter-spacing:1px;">My Approval Queue</h6>
    <div class="row g-3 mb-4">
        @foreach($stats['pipelineQueues'] as $queue)
        <div class="col-xl-3 col-md-4 col-sm-6">
            <a href="{{ route('partner.hrms.lead-orders') }}" class="text-decoration-none">
            <div class="stat-card h-100 border border-{{ $queue['color'] }} border-opacity-25 shadow-sm">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-{{ $queue['color'] }} bg-opacity-10 me-3"><i class="bi {{ $queue['icon'] }} text-{{ $queue['color'] }}"></i></div>
                    <div>
                        <div class="stat-label text-dark">{{ $queue['label'] }}</div>
                        <div class="stat-value text-{{ $queue['color'] }}">{{ number_format($queue['count']) }} <span class="fs-14 fw-normal text-muted">Orders</span></div>
                    </div>
                </div>
            </div>
            </a>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ────────────────────────────────────────────────
         RECENT ACTIVITY
    ──────────────────────────────────────────────── --}}
    <h5 class="fw-bold text-dark mb-3 mt-4">Recent Activity</h5>
    <div class="row g-4 mb-4">
        {{-- Recent Tasks --}}
        @if(auth()->user()->canAccess('task_viewAny') || auth()->user()->canAccess('task_viewTeam') || auth()->user()->canAccess('task_viewOwn') || auth()->user()->role === 'partner')
        <div class="col-xl-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Recent Tasks</h6>
                    <a href="{{ route('partner.hrms.tasks') }}" class="btn btn-sm btn-light rounded-pill px-3">View All</a>
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
                                @forelse($stats['recentTasks'] ?? [] as $task)
                                <tr>
                                    <td class="ps-4 border-0 py-3">
                                        <div class="fw-medium text-dark">{{ $task->title }}</div>
                                        @if($task->employee)
                                            <div class="text-muted small">{{ $task->employee->name }}</div>
                                        @endif
                                    </td>
                                    <td class="border-0 py-3">
                                        <span class="text-muted small"><i class="bi bi-calendar-event me-1"></i>{{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</span>
                                    </td>
                                    <td class="border-0 py-3">
                                        @php $badgeColor = match($task->status) { 'completed' => 'success', 'in_progress' => 'primary', 'pending' => 'warning', default => 'secondary' }; @endphp
                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} border border-{{ $badgeColor }} border-opacity-25 rounded-pill text-capitalize px-2 py-1" style="font-size:11px;">
                                            {{ str_replace('_', ' ', $task->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-5 text-muted">No recent tasks found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Recent Leads --}}
        @if(auth()->user()->canAccess('lead_viewAny') || auth()->user()->canAccess('lead_viewTeam') || auth()->user()->canAccess('lead_viewOwn'))
        <div class="col-xl-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Recent Leads</h6>
                    <a href="{{ route('partner.hrms.leads.index') }}" class="btn btn-sm btn-light rounded-pill px-3">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0 text-muted fw-semibold small">Customer</th>
                                    <th class="border-0 text-muted fw-semibold small">Phone</th>
                                    <th class="border-0 text-muted fw-semibold small">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recentLeads'] ?? [] as $lead)
                                <tr>
                                    <td class="ps-4 border-0 py-3"><div class="fw-medium text-dark">{{ $lead->customer_name }}</div></td>
                                    <td class="border-0 py-3"><span class="text-muted small">{{ $lead->customer_mobile ?? 'N/A' }}</span></td>
                                    <td class="border-0 py-3">
                                        @php $badgeColor = match($lead->status) { 'won' => 'success', 'lost' => 'danger', 'follow_up' => 'primary', 'new' => 'info', default => 'secondary' }; @endphp
                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} border border-{{ $badgeColor }} border-opacity-25 rounded-pill text-capitalize px-2 py-1" style="font-size:11px;">
                                            {{ str_replace('_', ' ', $lead->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-5 text-muted">No recent leads.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Orders --}}
        <div class="col-xl-6">
            <div class="card h-100 border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
                    <h6 class="mb-0 fw-bold text-dark">Recent Orders</h6>
                    <a href="{{ route('partner.hrms.lead-orders') }}" class="btn btn-sm btn-light rounded-pill px-3">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0 text-muted fw-semibold small">Customer</th>
                                    <th class="border-0 text-muted fw-semibold small">Amount</th>
                                    <th class="border-0 text-muted fw-semibold small">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['recentOrders'] ?? [] as $order)
                                <tr>
                                    <td class="ps-4 border-0 py-3">
                                        <div class="fw-medium text-dark">{{ $order->lead?->customer_name ?? '—' }}</div>
                                    </td>
                                    <td class="border-0 py-3"><span class="fw-bold text-dark">₹{{ number_format($order->total_amount, 0) }}</span></td>
                                    <td class="border-0 py-3">
                                        @php $badgeColor = match($order->approval_status) { 'completed' => 'success', 'rejected' => 'danger', 'pending' => 'warning', default => 'info' }; @endphp
                                        <span class="badge bg-{{ $badgeColor }} bg-opacity-10 text-{{ $badgeColor }} border border-{{ $badgeColor }} border-opacity-25 rounded-pill text-capitalize px-2 py-1" style="font-size:11px;">
                                            {{ str_replace('_', ' ', $order->approval_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-5 text-muted">No recent orders.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ────────────────────────────────────────────────
         ACTIVE NOTICES MODAL
    ──────────────────────────────────────────────── --}}
    {{-- ────────────────────────────────────────────────
         ACTIVE NOTICES MODAL (PRO & MOBILE FRIENDLY)
    ──────────────────────────────────────────────── --}}
    @if(isset($activeNotices) && $activeNotices->count() > 0)
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
                <div class="modal-body p-3 p-md-4" style="max-height:70vh; overflow-y:auto; background:#f1f5f9;">
                    <div class="row g-3">
                        @foreach($activeNotices as $notice)
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background:#ffffff; border-left: 4px solid {{ $notice->type === 'global' ? '#2563eb' : '#6366f1' }} !important;">
                                <div class="card-body p-3.5 p-md-4">
                                    <!-- Title & Header Meta Row -->
                                    <div class="d-flex w-100 justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <h5 class="mb-0 fw-bold text-dark text-break" style="font-size:1.05rem; letter-spacing:-0.01em;">{{ $notice->title }}</h5>
                                            @if($notice->created_at->diffInHours(now()) < 48)
                                                <span class="badge bg-danger rounded-pill px-2 py-0.5 text-uppercase" style="font-size: 0.68rem; letter-spacing: 0.03em;"><i class="bi bi-lightning-fill me-0.5"></i>NEW</span>
                                            @endif
                                            @if($notice->type === 'global')
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.72rem;">
                                                    <i class="bi bi-globe me-1"></i> Global Notice
                                                </span>
                                            @else
                                                <span class="badge bg-indigo bg-opacity-10 text-indigo border border-indigo border-opacity-25 rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.72rem; color:#4f46e5; background-color:#eef2ff; border-color:#c7d2fe;">
                                                    <i class="bi bi-person-fill-lock me-1"></i> Personal Notice
                                                </span>
                                            @endif
                                        </div>
                                        <span class="text-muted extra-small fw-medium bg-light px-2.5 py-1 rounded-pill"><i class="bi bi-clock me-1 text-primary"></i>{{ $notice->created_at->diffForHumans() }}</span>
                                    </div>

                                    <!-- Content Body -->
                                    <p class="my-3 text-secondary text-break" style="white-space:pre-line; line-height:1.6; font-size:0.92rem;">{{ $notice->content }}</p>

                                    <!-- Validity Date Badge (if present) -->
                                    @if($notice->start_date && $notice->end_date)
                                    <div class="d-inline-flex align-items-center gap-2 bg-light border rounded-pill px-3 py-1.5 mb-3 text-muted small font-monospace" style="font-size:0.8rem;">
                                        <i class="bi bi-calendar-check text-primary"></i>
                                        <span>Validity: <strong class="text-dark">{{ Carbon\Carbon::parse($notice->start_date)->format('M d, Y') }}</strong> &rarr; <strong class="text-dark">{{ Carbon\Carbon::parse($notice->end_date)->format('M d, Y') }}</strong></span>
                                    </div>
                                    @endif

                                    <!-- Action Link CTA Bar (if present) -->
                                    @if($notice->action_link)
                                    <div class="mt-2 pt-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div class="d-flex align-items-center text-muted small text-truncate pe-2">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 me-2 flex-shrink-0">
                                                <i class="bi bi-link-45deg me-1"></i> Action Link
                                            </span>
                                            <span class="text-truncate extra-small text-secondary" style="max-width: 260px;">{{ $notice->action_link }}</span>
                                        </div>
                                        <a href="{{ $notice->action_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-primary rounded-pill px-4 py-2 shadow-sm fw-bold btn-sm ms-auto d-inline-flex align-items-center gap-1.5">
                                            {{ $notice->action_text ?: 'Join Now' }} <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-muted extra-small"><i class="bi bi-info-circle me-1"></i>Review notice details anytime under HRMS Notices.</span>
                    <button type="button" class="btn btn-dark rounded-pill px-4" data-bs-dismiss="modal">Close Notice Board</button>
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
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Reusable Chart Theme Settings
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            font: { family: "'Inter', sans-serif", size: 12 },
                            color: '#64748b'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { family: "'Inter', sans-serif", size: 13 },
                        bodyFont: { family: "'Inter', sans-serif", size: 13 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true
                    }
                }
            };

            // 0. Attendance Chart (Bar)
            @if(auth()->user()->canAccess('attendance_viewOwn') || auth()->user()->canAccess('attendance_viewAny'))
            @if(isset($stats['weeklyAttendance']) && count($stats['weeklyAttendance']) > 0)
            const attendanceCtx = document.getElementById('attendanceChart');
            if (attendanceCtx) {
                const attendanceData = @json($stats['weeklyAttendance']);
                new Chart(attendanceCtx, {
                    type: 'bar',
                    data: {
                        labels: attendanceData.map(i => i.date),
                        datasets: [{
                            label: 'Hours Worked',
                            data: attendanceData.map(i => i.hours),
                            backgroundColor: 'rgba(13, 110, 253, 0.85)',
                            hoverBackgroundColor: 'rgba(13, 110, 253, 1)',
                            borderRadius: 4,
                            barPercentage: 0.5
                        }]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Hours', font: { family: "'Inter', sans-serif" } },
                                grid: { color: '#f1f5f9', drawBorder: false },
                            },
                            x: {
                                grid: { display: false, drawBorder: false }
                            }
                        }
                    }
                });
            }
            @endif
            @endif

            // 1. Monthly Task Completion (Bar Chart)
            const monthlyCtx = document.getElementById('monthlyTaskChart');
            if (monthlyCtx) {
                new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartData['monthlyLabels'] ?? []),
                        datasets: [
                            {
                                label: 'Tasks Completed',
                                data: @json($chartData['monthlyTasks'] ?? []),
                                backgroundColor: 'rgba(99, 102, 241, 0.85)',
                                hoverBackgroundColor: 'rgba(99, 102, 241, 1)',
                                borderRadius: 6,
                                barPercentage: 0.6
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9', drawBorder: false },
                                ticks: { font: { family: "'Inter', sans-serif" }, color: '#94a3b8', stepSize: 1 }
                            },
                            x: {
                                grid: { display: false, drawBorder: false },
                                ticks: { font: { family: "'Inter', sans-serif" }, color: '#94a3b8' }
                            }
                        }
                    }
                });
            }

            // 2. Task Status Distribution (Donut Chart)
            const statusCtx = document.getElementById('taskStatusChart');
            if (statusCtx) {
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'Pending', 'In Progress'],
                        datasets: [{
                            data: @json($chartData['taskStatus'] ?? [0,0,0]),
                            backgroundColor: [
                                '#10b981', // Success (Completed)
                                '#f59e0b', // Warning (Pending)
                                '#3b82f6'  // Primary (In Progress)
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        ...commonOptions,
                        cutout: '75%',
                        plugins: {
                            ...commonOptions.plugins,
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 20,
                                    font: { family: "'Inter', sans-serif", size: 12 }
                                }
                            }
                        }
                    }
                });
            }

            // 3. Top Employees (Bar Chart)
            const topEmpCtx = document.getElementById('topEmployeesChart');
            if (topEmpCtx) {
                new Chart(topEmpCtx, {
                    type: 'bar',
                    data: {
                        labels: @json($chartData['topEmployeesLabels'] ?? []),
                        datasets: [{
                            label: 'Tasks Completed',
                            data: @json($chartData['topEmployeesTasks'] ?? []),
                            backgroundColor: 'rgba(16, 185, 129, 0.85)',
                            hoverBackgroundColor: 'rgba(16, 185, 129, 1)',
                            borderRadius: 6,
                            barPercentage: 0.5
                        }]
                    },
                    options: {
                        ...commonOptions,
                        indexAxis: 'y',
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9', drawBorder: false },
                                ticks: { font: { family: "'Inter', sans-serif" }, color: '#94a3b8', stepSize: 1 }
                            },
                            y: {
                                grid: { display: false, drawBorder: false },
                                ticks: { font: { family: "'Inter', sans-serif" }, color: '#64748b', font: { weight: 'bold' } }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</div>
