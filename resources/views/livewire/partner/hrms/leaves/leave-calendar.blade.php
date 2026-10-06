<div>
    <!-- Page Header & Month Selector -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-calendar-range text-primary me-2"></i>Leave Calendar
                </h4>
                <p class="text-muted mb-0 small">View and track employee leaves, holidays, and team availability across the organization.</p>
            </div>
            
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button wire:click="goToToday" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold">
                    <i class="bi bi-calendar-event me-1"></i> Today
                </button>
                <div class="btn-group shadow-sm rounded-pill overflow-hidden border">
                    <button wire:click="previousMonth" class="btn btn-white border-0 px-3 hover-bg-light" title="Previous Month">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn btn-white border-0 fw-bold px-4 text-dark fs-6" style="min-width: 180px;" disabled>
                        {{ $monthName }} {{ $currentYear }}
                    </button>
                    <button wire:click="nextMonth" class="btn btn-white border-0 px-3 hover-bg-light" title="Next Month">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary KPI Stats -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-calendar2-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-bold text-uppercase">Total Leaves (This Month)</div>
                        <div class="fs-4 fw-bold text-dark">{{ $totalApproved + $totalPending }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-bold text-uppercase">Approved Leaves</div>
                        <div class="fs-4 fw-bold text-success">{{ $totalApproved }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-bold text-uppercase">Pending Approvals</div>
                        <div class="fs-4 fw-bold text-warning">{{ $totalPending }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="rounded-3 p-3 bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted extra-small fw-bold text-uppercase">Staff On Leave</div>
                        <div class="fs-4 fw-bold text-dark">{{ $uniqueEmployees }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-lg-3 col-md-6">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control bg-light border-0" wire:model.live.debounce.300ms="filterEmployeeSearch" placeholder="Search employee name/code...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select class="form-select form-select-sm bg-light border-0" wire:model.live="filterDepartmentId">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <select class="form-select form-select-sm bg-light border-0" wire:model.live="filterBranchId">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4">
                    <select class="form-select form-select-sm bg-light border-0" wire:model.live="statusFilter">
                        <option value="">Approved & Pending</option>
                        <option value="approved">Approved Only</option>
                        <option value="pending">Pending Only</option>
                        <option value="rejected">Rejected Only</option>
                    </select>
                </div>
                <div class="col-lg-1 col-md-2 text-end">
                    @if($filterBranchId || $filterDepartmentId || $filterEmployeeSearch || $statusFilter)
                        <button class="btn btn-outline-danger btn-sm rounded-pill w-100" wire:click="clearFilters" title="Reset Filters">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Grid Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-body p-0">
            <div class="calendar-wrapper" style="overflow-x: auto;">
                <div style="min-width: 950px;" class="border-top">
                    <!-- Days of Week Header -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr);" class="bg-light bg-opacity-75 border-bottom text-uppercase text-muted fw-bold text-center small py-2.5">
                        <div class="text-danger py-1">Sun</div>
                        <div class="py-1">Mon</div>
                        <div class="py-1">Tue</div>
                        <div class="py-1">Wed</div>
                        <div class="py-1">Thu</div>
                        <div class="py-1">Fri</div>
                        <div class="py-1">Sat</div>
                    </div>

                    <!-- Days Grid -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr);">
                        <!-- Empty Padding for Start of Month -->
                        @for($i = 0; $i < $startDayOfWeek; $i++)
                            <div class="border-end border-bottom bg-light bg-opacity-25" style="min-height: 130px;"></div>
                        @endfor

                        <!-- Actual Month Days -->
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dateObj = \Carbon\Carbon::create($currentYear, $currentMonth, $day);
                                $isToday = $dateObj->isToday();
                                $isSunday = $dateObj->isSunday();
                                $leavesOnDay = $leavesByDay[$day] ?? collect();
                                $holiday = $holidays[$day] ?? null;
                            @endphp

                            <div class="border-end border-bottom p-2 position-relative d-flex flex-column {{ $isToday ? 'bg-primary bg-opacity-10' : ($isSunday ? 'bg-light bg-opacity-40' : 'bg-white') }}" 
                                 style="min-height: 130px; transition: background 0.15s ease;">
                                
                                <!-- Top Row: Date Pill & Badges -->
                                <div class="d-flex justify-content-between align-items-center mb-1.5 flex-wrap gap-1">
                                    <span class="badge {{ $isToday ? 'bg-primary text-white shadow-sm' : ($isSunday ? 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' : 'bg-light text-dark border') }} rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.8rem;">
                                        {{ $day }}
                                    </span>

                                    <div class="d-flex gap-1 align-items-center">
                                        @if($holiday)
                                            <span class="badge rounded-pill bg-purple text-white shadow-sm" style="background-color: #8b5cf6; font-size: 0.65rem;" title="Holiday: {{ $holiday->name }}">
                                                <i class="bi bi-gift me-0.5"></i>{{ \Illuminate\Support\Str::limit($holiday->name, 10) }}
                                            </span>
                                        @endif

                                        @if($leavesOnDay->count() > 0)
                                            <button wire:click="viewDayLeaves({{ $day }})" class="btn btn-xs p-0 border-0 badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;" title="Click to view all leaves for this day">
                                                {{ $leavesOnDay->count() }} On Leave
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Leaves List Container -->
                                <div class="leaves-container flex-grow-1" style="max-height: 95px; overflow-y: auto;">
                                    @foreach($leavesOnDay->take(2) as $leave)
                                        @php
                                            $empName = $leave->employee->name ?? 'Staff';
                                            $catName = $leave->leaveCategory->name ?? ucfirst($leave->type ?? 'Leave');
                                            $isApproved = $leave->status === 'approved';
                                            $isPending = $leave->status === 'pending';
                                            
                                            $cardClass = $isApproved 
                                                ? 'bg-success bg-opacity-10 border-success text-dark' 
                                                : ($isPending 
                                                    ? 'bg-warning bg-opacity-15 border-warning text-dark' 
                                                    : 'bg-danger bg-opacity-10 border-danger text-dark');
                                        @endphp
                                        <div wire:click="viewLeaveDetails({{ $leave->id }})" 
                                             class="p-1 px-1.5 mb-1 rounded-2 border-start border-3 {{ $cardClass }} shadow-2xs" 
                                             style="cursor: pointer; font-size: 0.72rem;" 
                                             title="{{ $empName }} - {{ $catName }} ({{ ucfirst($leave->status) }})">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="fw-bold text-truncate" style="max-width: 95px;">
                                                    <i class="bi bi-person-fill me-0.5 opacity-75"></i>{{ $empName }}
                                                </div>
                                                <span class="badge {{ $isApproved ? 'bg-success' : ($isPending ? 'bg-warning text-dark' : 'bg-danger') }} rounded-circle p-0" style="width: 6px; height: 6px;" title="{{ ucfirst($leave->status) }}"></span>
                                            </div>
                                            <div class="text-muted extra-small text-truncate" style="font-size: 0.65rem;">
                                                {{ $catName }}
                                            </div>
                                        </div>
                                    @endforeach

                                    @if($leavesOnDay->count() > 2)
                                        <button wire:click="viewDayLeaves({{ $day }})" class="btn btn-link btn-xs p-0 text-primary text-decoration-none fw-bold" style="font-size: 0.68rem;">
                                            +{{ $leavesOnDay->count() - 2 }} more...
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endfor

                        <!-- Empty Padding for End of Month -->
                        @php $remainingDays = 7 - (($daysInMonth + $startDayOfWeek) % 7); @endphp
                        @if($remainingDays < 7)
                            @for($i = 0; $i < $remainingDays; $i++)
                                <div class="border-end border-bottom bg-light bg-opacity-25" style="min-height: 130px;"></div>
                            @endfor
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Leave Details Modal -->
    <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white">
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center" id="leaveModalLabel">
                        <i class="bi bi-info-circle-fill text-primary me-2"></i>Leave Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    @if($selectedLeave)
                        <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-3">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold me-3" style="width: 46px; height: 46px; font-size: 1.1rem;">
                                {{ strtoupper(substr($selectedLeave->employee->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">{{ $selectedLeave->employee->name ?? 'Unknown Employee' }}</h6>
                                <div class="text-muted small">
                                    {{ $selectedLeave->employee->employee_code ?? '' }}
                                    @if($selectedLeave->employee?->department)
                                        • {{ $selectedLeave->employee->department->name }}
                                    @endif
                                </div>
                            </div>
                            <div class="ms-auto">
                                @if($selectedLeave->status === 'approved')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                        <i class="bi bi-check-circle me-1"></i>Approved
                                    </span>
                                @elseif($selectedLeave->status === 'pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                        <i class="bi bi-hourglass me-1"></i>Pending
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                                        {{ ucfirst($selectedLeave->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="text-muted small fw-bold text-uppercase">Leave Category</label>
                                <div class="fw-bold text-dark">{{ $selectedLeave->leaveCategory->name ?? ucfirst($selectedLeave->type ?? 'General') }}</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold text-uppercase">Duration</label>
                                @php
                                    $start = \Carbon\Carbon::parse($selectedLeave->start_date);
                                    $end = \Carbon\Carbon::parse($selectedLeave->end_date);
                                    $totalDays = $start->diffInDays($end) + 1;
                                @endphp
                                <div class="fw-bold text-dark">{{ $totalDays }} Day{{ $totalDays > 1 ? 's' : '' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold text-uppercase">Start Date</label>
                                <div class="fw-semibold text-dark"><i class="bi bi-calendar-event me-1 text-primary"></i>{{ $start->format('d M Y') }}</div>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small fw-bold text-uppercase">End Date</label>
                                <div class="fw-semibold text-dark"><i class="bi bi-calendar-check me-1 text-primary"></i>{{ $end->format('d M Y') }}</div>
                            </div>
                        </div>

                        @if($selectedLeave->reason)
                            <div class="mb-3">
                                <label class="text-muted small fw-bold text-uppercase">Reason</label>
                                <div class="p-2.5 bg-light rounded-3 text-dark small border">{{ $selectedLeave->reason }}</div>
                            </div>
                        @endif

                        @if($selectedLeave->remarks)
                            <div class="mb-0">
                                <label class="text-muted small fw-bold text-uppercase">Admin Remarks</label>
                                <div class="p-2.5 bg-light rounded-3 text-muted small border">{{ $selectedLeave->remarks }}</div>
                            </div>
                        @endif
                    @endif
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-white">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Day Leaves List Modal -->
    <div class="modal fade" id="dayModal" tabindex="-1" aria-labelledby="dayModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white">
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0 d-flex align-items-center" id="dayModalLabel">
                            <i class="bi bi-calendar-day text-primary me-2"></i>Staff On Leave
                        </h5>
                        <p class="text-muted small mb-0 mt-0.5">{{ $selectedDayDate }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                                <tr>
                                    <th class="ps-4">Employee</th>
                                    <th>Category</th>
                                    <th>Leave Period</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($selectedDayLeaves as $leave)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $leave->employee->name ?? 'Unknown' }}</div>
                                            <div class="text-muted extra-small">{{ $leave->employee->department->name ?? 'General' }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">
                                                {{ $leave->leaveCategory->name ?? ucfirst($leave->type ?? 'Leave') }}
                                            </span>
                                        </td>
                                        <td class="small">
                                            {{ \Carbon\Carbon::parse($leave->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                        </td>
                                        <td>
                                            @if($leave->status === 'approved')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1">Approved</span>
                                            @elseif($leave->status === 'pending')
                                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-2.5 py-1">Pending</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1">{{ ucfirst($leave->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="small text-muted text-truncate" style="max-width: 200px;">
                                            {{ $leave->reason ?: 'No reason specified' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No leaves found for this day.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-white">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('open-leave-modal', () => {
            let el = document.getElementById('leaveModal');
            if (el) {
                let modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
                modal.show();
            }
        });

        $wire.on('open-day-modal', () => {
            let el = document.getElementById('dayModal');
            if (el) {
                let modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
                modal.show();
            }
        });
    </script>
    @endscript
</div>
