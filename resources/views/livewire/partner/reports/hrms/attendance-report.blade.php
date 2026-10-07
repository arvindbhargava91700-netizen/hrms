<div>
@include('partials.report-styles')
<div id="print-area">

    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Attendance Report</h4>
        <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Attendance Report</h4>
            <p class="text-muted mb-0 small">Track employee check-in/out, branch, team hierarchy, and working hours</p>
        </div>
        <div class="report-actions d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="exportCsv" wire:loading.attr="disabled">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="report-filter-card card mb-4 d-print-none shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel text-primary fs-5"></i>
                    <span class="fw-bold text-dark" style="font-size:0.95rem;">Filter Attendance</span>
                </div>
                @if($branchId || $teamId || $employeeId || $statusFilter || $selectedWorkingMode)
                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                @endif
            </div>
            <div class="row g-3">
                {{-- 1. Branch Filter --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Branch</label>
                    <select class="form-select border-primary-subtle" wire:model.live="branchId">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Team / Reporting Manager Filter --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Team / Reporting Manager</label>
                    <select class="form-select border-info-subtle" wire:model.live="teamId">
                        <option value="">All Teams / Reporting Managers</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }} {{ $team->employee_code ? '('.$team->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Employee Filter --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Employee</label>
                    <select class="form-select" wire:model.live="employeeId">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} {{ $emp->employee_code ? '('.$emp->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Status Filter --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Status</label>
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="present">Present (All)</option>
                        <option value="punch_out">Present (Punch Out)</option>
                        <option value="punch_in">In Office (Punch In)</option>
                        <option value="half_day">Half Day</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="leave">On Leave</option>
                    </select>
                </div>

                {{-- 5. Working Mode Filter --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Working Mode</label>
                    <select class="form-select" wire:model.live="selectedWorkingMode">
                        <option value="">All Modes</option>
                        <option value="office">Office</option>
                        <option value="remote">Remote (WFH)</option>
                        <option value="field">Field Work</option>
                    </select>
                </div>

                {{-- 6. From Date --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">From Date</label>
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>

                {{-- 7. To Date --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">To Date</label>
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
            </div>
        </div>
    </div>

    {{-- Summary KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Records</div>
                <div class="fw-bold fs-4 text-primary">{{ $reportSummary['totalRecords'] }}</div>
                <div class="text-muted" style="font-size: 0.72rem;">{{ $reportSummary['totalHours'] }} worked</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Present</div>
                <div class="fw-bold fs-4 text-success">{{ $reportSummary['presentCount'] }}</div>
                <div class="text-muted" style="font-size: 0.72rem;">Avg: {{ $reportSummary['avgWorkingMins'] }}m</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Half Day</div>
                <div class="fw-bold fs-4 text-warning">{{ $reportSummary['halfDayCount'] }}</div>
                <div class="text-muted" style="font-size: 0.72rem;">Partial work</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Absent</div>
                <div class="fw-bold fs-4 text-danger">{{ $reportSummary['absentCount'] }}</div>
                <div class="text-muted" style="font-size: 0.72rem;">Under minimum</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">On Leave</div>
                <div class="fw-bold fs-4 text-info">{{ $reportSummary['leaveCount'] }}</div>
                <div class="text-muted" style="font-size: 0.72rem;">Approved leave</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Late Records</div>
                <div class="fw-bold fs-4 text-danger">{{ $reportSummary['lateCount'] }}</div>
                <div class="text-muted" style="font-size: 0.72rem;">{{ $reportSummary['missedPunch'] }} missed punch</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Employee</th>
                        <th>Branch</th>
                        <th>Team / Reporting To</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Working</th>
                        <th>Late (Mins)</th>
                        <th>Status</th>
                        <th>Total Hours</th>
                        <th>Productive Hours</th>
                        <th>Overtime</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $row)
                    <tr>
                        <td class="ps-4 fw-bold">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ optional($row->employee)->name }}</div>
                            <div class="small text-muted">{{ optional($row->employee)->employee_code ?? substr(optional($row->employee)->id,0,8) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                <i class="bi bi-geo-alt me-1"></i>{{ optional(optional($row->employee)->branch)->name ?? 'Main Branch' }}
                            </span>
                        </td>
                        <td>
                            @if(optional($row->employee)->reportingTo)
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-person-badge text-info"></i>
                                    <span class="fw-semibold text-dark">{{ $row->employee->reportingTo->name }}</span>
                                </div>
                            @else
                                <span class="text-muted small fst-italic">Direct / None</span>
                            @endif
                        </td>
                        <td>{{ $row->check_in ? \Carbon\Carbon::parse($row->check_in)->format('h:i A') : '-' }}</td>
                        <td>{{ $row->check_out ? \Carbon\Carbon::parse($row->check_out)->format('h:i A') : '-' }}</td>
                        <td class="fw-bold">{{ $row->working_minutes ?? 0 }} min</td>
                        <td class="{{ $row->late_minutes > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $row->late_minutes ?? 0 }}</td>
                        <td>
                            @php
                                $st = strtolower($row->status ?? '');
                                $badgeClass = match($st) {
                                    'punch_out', 'present' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                    'punch_in'  => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                    'half_day'  => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
                                    'absent'    => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                    'leave'     => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
                                    'late'      => 'bg-danger bg-opacity-10 text-danger',
                                    default     => 'bg-secondary bg-opacity-10 text-secondary',
                                };
                                $statusLabel = match($st) {
                                    'punch_out' => 'Present',
                                    'punch_in'  => 'In Office',
                                    'half_day'  => 'Half Day',
                                    'absent'    => 'Absent',
                                    'leave'     => 'On Leave',
                                    'late'      => 'Late',
                                    default     => ucfirst(str_replace('_', ' ', $st ?: 'Unknown')),
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill px-3 py-1 fw-bold">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        @php
                            $worked = (int) ($row->working_minutes ?? 0);
                            $rowShift = optional($row->employee)->shift ?? null;
                            $req = ($rowShift && $rowShift->min_present_mins) ? (int) $rowShift->min_present_mins : $reportSummary['defaultRequiredMins'];
                            $prod = min($worked, $req);
                            $ov = max(0, $worked - $req);
                        @endphp
                        <td class="fw-bold">{{ intdiv($worked, 60) }}h {{ $worked % 60 }}m</td>
                        <td class="fw-bold text-secondary">{{ intdiv($prod, 60) }}h {{ $prod % 60 }}m</td>
                        <td class="fw-bold text-warning">{{ $ov > 0 ? (intdiv($ov, 60) . 'h ' . ($ov % 60) . 'm') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="text-center py-5 text-muted">No attendance records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())
        <div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $reportData->links() }}</div>
        @endif
    </div>
</div>
</div>
