<div>
    @include('partials.report-styles')

    <div class="mb-3">
        <a href="{{ route('partner.hrms.report.performance') }}" class="btn btn-sm btn-outline-secondary border-0 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Report
        </a>
    </div>

    <!-- Profile Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 text-primary bg-primary bg-opacity-10" style="width: 65px; height: 65px;">
                    {{ strtoupper(substr($employee->name, 0, 2)) }}
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-1 text-uppercase">{{ $employee->name }}</h5>
                    <p class="text-muted mb-2 small">{{ $employee->designation->name ?? 'Employee' }}</p>
                    <div class="d-flex flex-wrap gap-2 align-items-center small text-muted">
                        <span><i class="bi bi-person-badge text-primary"></i> {{ $employee->employee_code ?? 'N/A' }}</span>
                        <span><i class="bi bi-building text-success"></i> {{ $employee->department->name ?? 'N/A' }}</span>
                        @if($isCustom ?? false)
                            <span class="badge bg-warning-subtle text-dark border border-warning px-2 py-0.5 rounded-pill" style="font-size:0.75rem;">
                                <i class="bi bi-sliders me-1 text-warning"></i>Custom Config: Att {{ $weights['attendance'] ?? 25 }} / Task {{ $weights['tasks'] ?? 25 }} / Merch {{ $weights['merchant'] ?? 25 }} / Month {{ $weights['monthly'] ?? 25 }} pts
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="text-end">
                <div class="fw-bold fs-4 {{ $avgScore >= 70 ? 'text-primary' : ($avgScore >= 50 ? 'text-warning' : 'text-danger') }}">{{ $avgScore }}</div>
                <div class="text-muted small mb-1">Avg. Score / 100</div>
                <span class="badge bg-{{ $overallGrade === 'A+' ? 'success' : ($overallGrade === 'A' ? 'primary' : ($overallGrade === 'B+' ? 'info' : 'warning')) }} rounded-pill px-3 py-1">
                    {{ $overallGrade }} Grade
                </span>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-3">
                    <div class="text-success mb-2 fs-5"><i class="bi bi-calendar-check-fill"></i></div>
                    <h4 class="fw-bold mb-1">{{ $stats['total_present'] }}</h4>
                    <div class="text-muted small fw-semibold">Total Present</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-3">
                    <div class="text-warning mb-2 fs-5"><i class="bi bi-clock-fill"></i></div>
                    <h4 class="fw-bold mb-1">{{ $stats['late_marks'] }}</h4>
                    <div class="text-muted small fw-semibold">Late Marks</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-3">
                    <div class="text-primary mb-2 fs-5"><i class="bi bi-check-circle-fill"></i></div>
                    <h4 class="fw-bold mb-1">{{ $stats['tasks_completed'] }}</h4>
                    <div class="text-muted small fw-semibold">Tasks Completed</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-3">
                    <div class="text-info mb-2 fs-5"><i class="bi bi-list-task"></i></div>
                    <h4 class="fw-bold mb-1">{{ $stats['total_tasks'] }}</h4>
                    <div class="text-muted small fw-semibold">Total Tasks</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body text-center p-3">
                    <div class="text-danger mb-2 fs-5"><i class="bi bi-calendar-x-fill"></i></div>
                    <h4 class="fw-bold mb-1">{{ $stats['leaves_taken'] }}</h4>
                    <div class="text-muted small fw-semibold">Leaves Taken</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Performance Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Monthly Performance — {{ $year }}</h6>
            <select wire:model.live="year" class="form-select form-select-sm w-auto">
                @for($y = date('Y'); $y >= 2023; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0 text-center table-hover">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 text-start text-muted fw-bold small text-uppercase">Month</th>
                        <th class="text-muted fw-bold small text-uppercase">Attendance <span class="d-block text-black-50" style="font-size: 10px;">({{ $weights['attendance'] ?? 25 }} pts)</span></th>
                        <th class="text-muted fw-bold small text-uppercase">Tasks <span class="d-block text-black-50" style="font-size: 10px;">({{ $weights['tasks'] ?? 25 }} pts)</span></th>
                        <th class="text-muted fw-bold small text-uppercase">Merchant Target <span class="d-block text-black-50" style="font-size: 10px;">({{ $weights['merchant'] ?? 25 }} pts)</span></th>
                        <th class="text-muted fw-bold small text-uppercase">Monthly Target <span class="d-block text-black-50" style="font-size: 10px;">({{ $weights['monthly'] ?? 25 }} pts)</span></th>
                        <th class="text-muted fw-bold small text-uppercase">Total Score <span class="d-block text-black-50" style="font-size: 10px;">(100 pts)</span></th>
                        <th class="text-muted fw-bold small text-uppercase pe-4">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthly as $data)
                    <tr>
                        <td class="ps-4 text-start fw-bold text-dark">{{ $data['month_name'] }}</td>
                        <td>
                            @if($data['attendance_score'] !== '-')
                                <div class="fw-bold text-success">{{ $data['attendance_score'] }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $data['attendance_days'] }}</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($data['task_score'] !== '-')
                                <div class="fw-bold text-primary">{{ $data['task_score'] }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $data['task_completed'] }}</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($data['merchant_score'] !== '-')
                                <div class="fw-bold text-info">{{ $data['merchant_score'] }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $data['merchant_details'] }}</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($data['monthly_score'] !== '-')
                                <div class="fw-bold text-warning">{{ $data['monthly_score'] }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ $data['monthly_details'] }}</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($data['rate_score'] !== '-')
                                <div class="fw-bold text-{{ $data['rate_score'] >= 70 ? 'primary' : ($data['rate_score'] >= 50 ? 'warning' : 'danger') }}">{{ $data['rate_score'] }}</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="pe-4">
                            @if($data['grade'] !== '-')
                                <span class="badge bg-{{ $data['grade'] === 'A+' ? 'success' : ($data['grade'] === 'A' ? 'primary' : ($data['grade'] === 'B+' ? 'info' : 'warning')) }}">{{ $data['grade'] }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    <!-- Year Average Row -->
                    <tr class="bg-light">
                        <td class="ps-4 text-start fw-bold text-primary">Year Average</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td>-</td>
                        <td class="fw-bold text-primary">{{ $avgScore }}</td>
                        <td class="pe-4">
                            <span class="badge bg-{{ $overallGrade === 'A+' ? 'success' : ($overallGrade === 'A' ? 'primary' : ($overallGrade === 'B+' ? 'info' : 'warning')) }}">{{ $overallGrade }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
