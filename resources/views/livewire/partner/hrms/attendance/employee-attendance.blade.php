<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        @php
                            $_hasViewAny   = auth()->user()->canAccess('attendance_viewAny');
                            $_hasViewTeam  = auth()->user()->canAccess('attendance_viewBranch') || auth()->user()->canAccess('attendance_viewTeam');
                        @endphp
                        <h4 class="mb-1 text-dark fw-bold">{{ $_hasViewAny ? 'Manage Employee Attendance' : ($_hasViewTeam ? 'Team Attendance' : 'My Attendance History') }}</h4>
                        <p class="text-muted mb-0">{{ $_hasViewAny ? 'View check-ins, selfies, and GPS locations of all employees.' : ($_hasViewTeam ? 'View attendance records of your team members.' : 'View your attendance history.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
   @if(auth()->user()->canAccess('attendance_viewTeam') || auth()->user()->canAccess('attendance_viewAny') )
    @include('partials.hrms-filters', ['viewAnyPermission' => 'attendance_viewAny'])
  @endif
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white h-100">
                <div class="card-body p-3">
                    <label class="form-label fw-bold small text-muted"><i class="bi bi-calendar-month me-1"></i>Select Month</label>
                    <input type="month" wire:model.live="selectedMonth" class="form-control form-control-sm bg-light border-0">
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white h-100">
                <div class="card-body p-3">
                    <label class="form-label fw-bold small text-muted"><i class="bi bi-calendar-day me-1"></i>Select Date</label>
                    <input type="date" wire:model.live="selectedDate" class="form-control form-control-sm bg-light border-0">
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white h-100">
                <div class="card-body p-3">
                    <label class="form-label fw-bold small text-muted"><i class="bi bi-check2-circle me-1"></i>Select Status</label>
                                    <select wire:model.live="selectedStatus" class="form-select form-select-sm bg-light border-0">
                                        <option value="">All Statuses</option>
                                        <option value="punch_out">Punch Out</option>
                                        <option value="absent">Absent</option>
                                        <option value="half_day">Half Day</option>
                                        <option value="punch_in">Punch In</option>
                                        <option value="leave">Leave</option>
                                        <option value="holiday">Holiday</option>
                                        <option value="weekOff">Week Off</option>
                                        <option value="late">Late</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 bg-white h-100">
                                <div class="card-body p-3">
                                    <label class="form-label fw-bold small text-muted"><i class="bi bi-laptop me-1"></i>Select Working Mode</label>
                                    <select wire:model.live="selectedWorkingMode" class="form-select form-select-sm bg-light border-0">
                                        <option value="">All Modes</option>
                                        <option value="office">Office</option>
                                        <option value="remote">Remote (WFH)</option>
                                        <option value="field">Field Work</option>
                                    </select>
                                </div>
                            </div>
                        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body p-3">
                    <div class="row g-2 text-center">
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-primary mb-1 fw-bold" style="font-size: 0.8rem;">Punch In</h6>
                                <h5 class="mb-0 text-dark">{{ $summary['punch_in'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-success mb-1 fw-bold" style="font-size: 0.8rem;">Punch Out</h6>
                                <h5 class="mb-0 text-dark">{{ $summary['punch_out'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-danger mb-1 fw-bold" style="font-size: 0.8rem;">Absent</h6>
                                <h5 class="mb-0 text-dark">{{ $summary['absent'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-warning mb-1 fw-bold" style="font-size: 0.8rem;">Half Day</h6>
                                <h5 class="mb-0 text-dark">{{ $summary['half_day'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-info mb-1 fw-bold" style="font-size: 0.8rem;">Leave</h6>
                                <h5 class="mb-0 text-dark">{{ $summary['leave'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-secondary mb-1 fw-bold" style="font-size: 0.8rem;">Week Off/Holiday</h6>
                                <h5 class="mb-0 text-dark">{{ ($summary['weekOff'] ?? 0) + ($summary['holiday'] ?? 0) }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-danger mb-1 fw-bold" style="font-size: 0.8rem;">Late</h6>
                                <h5 class="mb-0 text-dark">{{ $summary['late'] ?? 0 }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-dark mb-1 fw-bold" style="font-size: 0.8rem;">Missed Punch</h6>
                                <h5 class="mb-0 text-dark">{{ $summary['missedPunch'] ?? 0 }}</h5>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 text-center mt-2 pt-2 border-top">
                        <div class="col-md-4 col-12">
                            <div class="p-2 border rounded border-light bg-light">
                                <span class="text-muted small fw-bold"><i class="bi bi-clock me-1"></i>Total Hours:</span>
                                <span class="fw-bold text-dark ms-1">{{ $summary['total_hours'] ?? '0h 0m' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="p-2 border rounded border-light bg-light">
                                <span class="text-success small fw-bold"><i class="bi bi-check-circle me-1"></i>Productive Hours:</span>
                                <span class="fw-bold text-success ms-1">{{ $summary['productive_hours'] ?? '0h 0m' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="p-2 border rounded border-light bg-light">
                                <span class="text-warning small fw-bold"><i class="bi bi-hourglass-split me-1"></i>Overtime:</span>
                                <span class="fw-bold text-dark ms-1">{{ $summary['overtime_hours'] ?? '0h 0m' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 bg-white">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                        <tr>
                            <th class="py-3.5 ps-3" style="width: 44px;">
                                <input type="checkbox" class="form-check-input" wire:click="toggleSelectAll"
                                    @if(count($pageEmployeeIds) > 0 && collect($pageEmployeeIds)->every(fn($id) => in_array($id, $selectedUsers))) checked @endif>
                            </th>
                            <th class="ps-4 py-3.5">Employee</th>
                            <th class="py-3.5 px-3">Date</th>
                            <th class="py-3.5 px-3">Working Mode</th>
                            <th class="py-3.5 px-3">Status</th>
                            <th class="py-3.5 px-3">Check In</th>
                            <th class="py-3.5 px-3">Check Out</th>
                            <th class="py-3.5 px-3 text-center">Total Hours</th>
                            <th class="py-3.5 px-3 text-center">Productive Hours</th>
                            <th class="py-3.5 px-3 text-center">Overtime</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $attendance)
                            @php
                                $rowKey = $attendance->id
                                    ? $attendance->id
                                    : 'v_' . $attendance->employee_id . '_' . $attendance->date;

                                if (!empty($attendance->working_minutes)) {
                                    $worked = (int) $attendance->working_minutes;
                                } elseif ($attendance->check_in && $attendance->check_out) {
                                    $dateStr = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
                                    $inTime = \Carbon\Carbon::parse($dateStr . ' ' . $attendance->check_in);
                                    $outTime = \Carbon\Carbon::parse($dateStr . ' ' . $attendance->check_out);
                                    $worked = (int) abs($outTime->diffInMinutes($inTime));
                                } elseif ($attendance->check_in && \Carbon\Carbon::parse($attendance->date)->isToday()) {
                                    $inTime = \Carbon\Carbon::parse(\Carbon\Carbon::today()->format('Y-m-d') . ' ' . $attendance->check_in);
                                    $worked = (int) max(0, \Carbon\Carbon::now()->diffInMinutes($inTime));
                                } else {
                                    $worked = 0;
                                }

                                $rowShift = $attendance->employee->shift ?? null;
                                $required = ($rowShift && $rowShift->min_present_mins)
                                    ? (int) $rowShift->min_present_mins
                                    : ($defaultRequiredMins ?? 480);

                                $productive = min($worked, $required);
                                $overtime = max(0, $worked - $required);
                            @endphp
                            <tr wire:click="toggleDetails('{{ $rowKey }}')" style="cursor: pointer;" class="hover-shadow-sm transition-all @if($expandedKey === $rowKey) table-active @endif">
                                <td class="ps-3 py-3.5 align-middle" @click.stop>
                                    <input type="checkbox" class="form-check-input" wire:model.live="selectedUsers" value="{{ $attendance->employee_id }}">
                                </td>
                                <td class="ps-4 py-3.5 align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($attendance->employee->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark">{{ $attendance->employee->name }}</h6>
                                            <small class="text-muted">{{ $attendance->employee->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 align-middle text-secondary fw-semibold">{{ \Carbon\Carbon::parse($attendance->date)->format('M d, Y') }}</td>
                                <td class="py-3.5 px-3 align-middle">
                                    <span class="badge bg-dark bg-gradient rounded-pill px-3 py-1.5 fs-7 fw-semibold">
                                        <i class="bi bi-laptop me-1"></i>{{ ucfirst($attendance->working_mode ?: 'office') }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-3 align-middle">
                                    @if($attendance->status === 'punch_out')
                                        <span class="badge bg-success rounded-pill px-3 py-1.5">Punch Out</span>
                                    @elseif($attendance->status === 'absent')
                                        <span class="badge bg-danger rounded-pill px-3 py-1.5">Absent</span>
                                    @elseif($attendance->status === 'half_day')
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5">Half Day</span>
                                    @elseif($attendance->status === 'short_leave')
                                        <span class="badge bg-info rounded-pill px-3 py-1.5">Short Leave</span>
                                    @elseif($attendance->status === 'punch_in' || ($attendance->check_in && !$attendance->check_out))
                                        <span class="badge bg-primary rounded-pill px-3 py-1.5">Punch In</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3 py-1.5">{{ ucfirst($attendance->status ?: 'Punch In') }}</span>
                                    @endif

                                    @if($attendance->late_minutes > 0)
                                        <div class="mt-1">
                                             <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-clock-history me-1"></i>Late by {{ $attendance->late_minutes }} mins
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 align-middle">
                                    @if($attendance->check_in)
                                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') }}</div>
                                        <div class="mt-1">
                                             @if($attendance->check_in_selfie) <i class="bi bi-camera text-primary me-1" title="Selfie attached"></i> @endif
                                            @if($attendance->check_in_lat) <i class="bi bi-geo-alt text-danger me-1" title="GPS attached"></i> @endif
                                            @if($attendance->checklist_responses && count($attendance->checklist_responses) > 0) <i class="bi bi-card-checklist text-success" title="Checklist attached"></i> @endif
                                        </div>
                                    @else
                                        <span class="text-muted">--:--</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 align-middle">
                                    @if($attendance->check_out)
                                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') }}</div>
                                        <div class="mt-1">
                                             @if($attendance->check_out_selfie) <i class="bi bi-camera text-primary me-1" title="Selfie attached"></i> @endif
                                            @if($attendance->check_out_lat) <i class="bi bi-geo-alt text-danger me-1" title="GPS attached"></i> @endif
                                            @if($attendance->check_out_checklist_responses && count($attendance->check_out_checklist_responses) > 0) <i class="bi bi-card-checklist text-success" title="Checklist attached"></i> @endif
                                        </div>
                                    @else
                                        <span class="text-muted">--:--</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-3 align-middle text-center fw-semibold text-dark">{{ intdiv($worked, 60) }}h {{ $worked % 60 }}m</td>
                                <td class="py-3.5 px-3 align-middle text-center fw-semibold text-success">{{ intdiv($productive, 60) }}h {{ $productive % 60 }}m</td>
                                <td class="py-3.5 px-3 align-middle text-center fw-semibold text-warning">{{ intdiv($overtime, 60) }}h {{ $overtime % 60 }}m</td>
                            </tr>

                            @if($expandedKey === $rowKey)
                                <tr class="table-light">
                                    <td colspan="10" class="px-4 py-4">
                                        <div class="row g-4">
                                            <!-- Check In Details -->
                                            <div class="col-md-6">
                                                <div class="card h-100 border border-light bg-light rounded-3 shadow-sm">
                                                    <div class="card-body">
                                                        <h6 class="fw-bold text-primary border-bottom border-light pb-2 mb-3"><i class="bi bi-box-arrow-in-right me-2"></i>Check In</h6>

                                                        <div class="mb-3">
                                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Time</small>
                                                            <div class="fs-5 text-dark">
                                                                @if($attendance->check_in)
                                                                    <i class="bi bi-clock me-1 text-primary"></i> {{ \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') }}
                                                                @else
                                                                    <span class="text-muted">Not recorded</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Location</small>
                                                            <div class="text-dark">
                                                                @if($attendance->check_in_lat && $attendance->check_in_lng)
                                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                                    <a href="https://maps.google.com/?q={{ $attendance->check_in_lat }},{{ $attendance->check_in_lng }}" target="_blank" class="text-decoration-none">
                                                                        View on Map <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted"><i class="bi bi-geo-alt me-1"></i> Location not captured</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Capture Image</small>
                                                            <div class="mt-2 text-center bg-white rounded border border-light p-2" style="min-height: 150px; display: flex; align-items: center; justify-content: center;">
                                                                @if($attendance->check_in_selfie)
                                                                    <img src="{{ $attendance->check_in_selfie }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check In Selfie">
                                                                @elseif($attendance->check_in_photo)
                                                                    <img src="{{ $attendance->check_in_photo }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check In Photo">
                                                                @else
                                                                    <div class="text-muted">
                                                                        <i class="bi bi-camera-fill fs-1 text-light d-block mb-1"></i>
                                                                        <small>No image captured</small>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Check Out Details -->
                                            <div class="col-md-6">
                                                <div class="card h-100 border border-light bg-light rounded-3 shadow-sm">
                                                    <div class="card-body">
                                                        <h6 class="fw-bold text-secondary border-bottom border-light pb-2 mb-3"><i class="bi bi-box-arrow-left me-2"></i>Check Out</h6>

                                                        <div class="mb-3">
                                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Time</small>
                                                            <div class="fs-5 text-dark">
                                                                @if($attendance->check_out)
                                                                    <i class="bi bi-clock me-1 text-secondary"></i> {{ \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') }}
                                                                @else
                                                                    <span class="text-muted">Not recorded</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Location</small>
                                                            <div class="text-dark">
                                                                @if($attendance->check_out_lat && $attendance->check_out_lng)
                                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                                    <a href="https://maps.google.com/?q={{ $attendance->check_out_lat }},{{ $attendance->check_out_lng }}" target="_blank" class="text-decoration-none">
                                                                        View on Map <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted"><i class="bi bi-geo-alt me-1"></i> Location not captured</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Capture Image</small>
                                                            <div class="mt-2 text-center bg-white rounded border border-light p-2" style="min-height: 150px; display: flex; align-items: center; justify-content: center;">
                                                                @if($attendance->check_out_selfie)
                                                                    <img src="{{ $attendance->check_out_selfie }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check Out Selfie">
                                                                @elseif($attendance->check_out_photo)
                                                                    <img src="{{ $attendance->check_out_photo }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check Out Photo">
                                                                @else
                                                                    <div class="text-muted">
                                                                        <i class="bi bi-camera-fill fs-1 text-light d-block mb-1"></i>
                                                                        <small>No image captured</small>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Checklist Details -->
                                            @if($attendance->checklist_responses && count($attendance->checklist_responses) > 0)
                                                <div class="col-12 mt-2">
                                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-2 text-success"></i>Pre-Check-in Checklist</h6>
                                                    <div class="card border-0 shadow-sm rounded-3">
                                                        <div class="table-responsive rounded-3">
                                                            <table class="table table-hover table-borderless align-middle mb-0">
                                                                <thead class="bg-light">
                                                                    <tr>
                                                                        <th class="ps-4 fw-600 text-muted">Question</th>
                                                                        <th class="text-end pe-4 fw-600 text-muted">Response</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($attendance->checklist_responses as $response)
                                                                        <tr class="border-bottom border-light">
                                                                            <td class="ps-4 fw-600 py-3">{{ $response['question'] }}</td>
                                                                            <td class="text-end pe-4 py-3">
                                                                                @if($response['answer'] == 'Yes')
                                                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                                                                @else
                                                                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i> No</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($attendance->check_out_checklist_responses && count($attendance->check_out_checklist_responses) > 0)
                                                <div class="col-12 mt-2">
                                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-2 text-success"></i>Pre-Check-out Checklist</h6>
                                                    <div class="card border-0 shadow-sm rounded-3">
                                                        <div class="table-responsive rounded-3">
                                                            <table class="table table-hover table-borderless align-middle mb-0">
                                                                <thead class="bg-light">
                                                                    <tr>
                                                                        <th class="ps-4 fw-600 text-muted">Question</th>
                                                                        <th class="text-end pe-4 fw-600 text-muted">Response</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($attendance->check_out_checklist_responses as $response)
                                                                        <tr class="border-bottom border-light">
                                                                            <td class="ps-4 fw-600 py-3">{{ $response['question'] }}</td>
                                                                            <td class="text-end pe-4 py-3">
                                                                                @if($response['answer'] == 'Yes')
                                                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                                                                @else
                                                                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i> No</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                    No attendance records found for this date.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($attendances->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
    
    <!-- Attendance Details Modal (disabled: details now shown inline) -->
    @if(false)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-0">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem;">
                            {{ strtoupper(substr($selectedAttendance->employee->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="modal-title fw-bold text-dark mb-0">{{ $selectedAttendance->employee->name }}</h5>
                                <span class="badge bg-dark bg-gradient rounded-pill px-3 py-1 fs-7">
                                    <i class="bi bi-laptop me-1"></i>{{ ucfirst($selectedAttendance->working_mode ?: 'office') }} Mode
                                </span>
                            </div>
                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> {{ \Carbon\Carbon::parse($selectedAttendance->date)->format('l, F j, Y') }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" wire:click="closeDetailsModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Check In Details -->
                        <div class="col-md-6">
                            <div class="card h-100 border border-light bg-light rounded-3 shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary border-bottom border-light pb-2 mb-3"><i class="bi bi-box-arrow-in-right me-2"></i>Check In</h6>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Time</small>
                                        <div class="fs-5 text-dark">
                                            @if($selectedAttendance->check_in)
                                                <i class="bi bi-clock me-1 text-primary"></i> {{ \Carbon\Carbon::parse($selectedAttendance->check_in)->format('h:i A') }}
                                            @else
                                                <span class="text-muted">Not recorded</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Location</small>
                                        <div class="text-dark">
                                            @if($selectedAttendance->check_in_lat && $selectedAttendance->check_in_lng)
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> 
                                                <a href="https://maps.google.com/?q={{ $selectedAttendance->check_in_lat }},{{ $selectedAttendance->check_in_lng }}" target="_blank" class="text-decoration-none">
                                                    View on Map <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                </a>
                                            @else
                                                <span class="text-muted"><i class="bi bi-geo-alt me-1"></i> Location not captured</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Capture Image</small>
                                        <div class="mt-2 text-center bg-white rounded border border-light p-2" style="min-height: 150px; display: flex; align-items: center; justify-content: center;">
                                            @if($selectedAttendance->check_in_selfie)
                                                <img src="{{ $selectedAttendance->check_in_selfie }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check In Selfie">
                                            @elseif($selectedAttendance->check_in_photo)
                                                <img src="{{ $selectedAttendance->check_in_photo }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check In Photo">
                                            @else
                                                <div class="text-muted">
                                                    <i class="bi bi-camera-fill fs-1 text-light d-block mb-1"></i>
                                                    <small>No image captured</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Check Out Details -->
                        <div class="col-md-6">
                            <div class="card h-100 border border-light bg-light rounded-3 shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold text-secondary border-bottom border-light pb-2 mb-3"><i class="bi bi-box-arrow-left me-2"></i>Check Out</h6>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Time</small>
                                        <div class="fs-5 text-dark">
                                            @if($selectedAttendance->check_out)
                                                <i class="bi bi-clock me-1 text-secondary"></i> {{ \Carbon\Carbon::parse($selectedAttendance->check_out)->format('h:i A') }}
                                            @else
                                                <span class="text-muted">Not recorded</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Location</small>
                                        <div class="text-dark">
                                            @if($selectedAttendance->check_out_lat && $selectedAttendance->check_out_lng)
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> 
                                                <a href="https://maps.google.com/?q={{ $selectedAttendance->check_out_lat }},{{ $selectedAttendance->check_out_lng }}" target="_blank" class="text-decoration-none">
                                                    View on Map <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                </a>
                                            @else
                                                <span class="text-muted"><i class="bi bi-geo-alt me-1"></i> Location not captured</span>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Capture Image</small>
                                        <div class="mt-2 text-center bg-white rounded border border-light p-2" style="min-height: 150px; display: flex; align-items: center; justify-content: center;">
                                            @if($selectedAttendance->check_out_selfie)
                                                <img src="{{ $selectedAttendance->check_out_selfie }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check Out Selfie">
                                            @elseif($selectedAttendance->check_out_photo)
                                                <img src="{{ $selectedAttendance->check_out_photo }}" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check Out Photo">
                                            @else
                                                <div class="text-muted">
                                                    <i class="bi bi-camera-fill fs-1 text-light d-block mb-1"></i>
                                                    <small>No image captured</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Checklist Details -->
                        @if($selectedAttendance->checklist_responses && count($selectedAttendance->checklist_responses) > 0)
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-2 text-success"></i>Pre-Check-in Checklist</h6>
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="table-responsive rounded-3">
                                    <table class="table table-hover table-borderless align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 fw-600 text-muted">Question</th>
                                                <th class="text-end pe-4 fw-600 text-muted">Response</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($selectedAttendance->checklist_responses as $response)
                                                <tr class="border-bottom border-light">
                                                    <td class="ps-4 fw-600 py-3">{{ $response['question'] }}</td>
                                                    <td class="text-end pe-4 py-3">
                                                        @if($response['answer'] == 'Yes')
                                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                                        @else
                                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i> No</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($selectedAttendance->check_out_checklist_responses && count($selectedAttendance->check_out_checklist_responses) > 0)
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-2 text-success"></i>Pre-Check-out Checklist</h6>
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="table-responsive rounded-3">
                                    <table class="table table-hover table-borderless align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 fw-600 text-muted">Question</th>
                                                <th class="text-end pe-4 fw-600 text-muted">Response</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($selectedAttendance->check_out_checklist_responses as $response)
                                                <tr class="border-bottom border-light">
                                                    <td class="ps-4 fw-600 py-3">{{ $response['question'] }}</td>
                                                    <td class="text-end pe-4 py-3">
                                                        @if($response['answer'] == 'Yes')
                                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                                        @else
                                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i> No</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
