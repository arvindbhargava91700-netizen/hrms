<div>
    @include('partials.report-styles')

    <div class="report-filter-card card mb-4 d-print-none shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel text-primary fs-5"></i>
                    <span class="fw-bold text-dark" style="font-size:0.95rem;">Filter Performance</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($branchId || $teamId || $filterDept || $search || $sortBy !== 'score_desc')
                        <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </button>
                    @endif
                    <span class="text-muted small"><i class="bi bi-people me-1"></i>{{ $rows->count() }} staff</span>
                </div>
            </div>
            <div class="row g-3">
                {{-- 1. Branch --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label"> Branch</label>
                    <select class="form-select border-primary-subtle" wire:model.live="branchId">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Team / Reporting Manager --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label"> Team / Reporting Manager</label>
                    <select class="form-select border-info-subtle" wire:model.live="teamId">
                        <option value="">All Teams / Reporting Managers</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }} {{ $team->employee_code ? '('.$team->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Department --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Department</label>
                    <select wire:model.live="filterDept" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Month --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Month</label>
                    <input type="month" wire:model.live="filterMonth" class="form-control">
                </div>

                {{-- 5. Sort By --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Sort By</label>
                    <select wire:model.live="sortBy" class="form-select">
                        <option value="score_desc">Score: High to Low</option>
                        <option value="score_asc">Score: Low to High</option>
                        <option value="name_asc">Name A-Z</option>
                    </select>
                </div>

                {{-- 6. Search --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Search Staff</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name, code, email..." class="form-control border-start-0 ps-0">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
        ['Total Tasks', $totals['tasks'], 'bi-list-task', 'primary'],
        ['Attendance Days', $totals['attendance'], 'bi-calendar-check', 'success'],
        ['Merchants Achieved', $totals['merchants'], 'bi-shop', 'info'],
        ['Merchant Target', $totals['merchant_target'], 'bi-bullseye', 'warning'],
        ] as [$label, $value, $icon, $color])
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-{{ $color }} bg-opacity-10"><i class="bi {{ $icon }} text-{{ $color }}"></i></div>
                <div>
                    <div class="stat-label">{{ $label }}</div>
                    <div class="stat-value">{{ number_format($value, 0) }}</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Department & Branch</th>
                        <th>Team / Reporting To</th>
                        <th class="text-center">Attendance</th>
                        <th class="text-center">Tasks</th>
                        <th class="text-center">Merchant Target</th>
                        <th class="text-center">Monthly Target</th>
                        <th class="text-center">Total Score <small class="d-block text-muted">100 pts</small></th>
                        <th class="text-center">Grade</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $row['name'] }}</div>
                            <div class="text-muted extra-small d-flex align-items-center gap-1.5 mt-0.5">
                                <span>{{ $row['employee_code'] ?: 'No code' }}</span>
                                @if($row['is_custom'])
                                    <span class="badge bg-warning-subtle text-dark border border-warning px-1.5 py-0.5" style="font-size:0.65rem;" title="Custom weights: Att {{ $row['att_weight'] }} / Task {{ $row['task_weight'] }} / Merch {{ $row['merch_weight'] }} / Month {{ $row['month_weight'] }}">
                                        <i class="bi bi-sliders me-0.5"></i>Custom
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="text-muted">
                            <div class="fw-semibold text-dark">{{ $row['department'] }}</div>
                            <div class="small text-muted">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-0.5 rounded-pill" style="font-size:0.75rem;">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $row['branch'] }}
                                </span>
                            </div>
                        </td>
                        <td>
                            @if($row['reporting_to'] && $row['reporting_to'] !== 'Direct / None')
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-person-badge text-info"></i>
                                    <span class="fw-semibold text-dark small">{{ $row['reporting_to'] }}</span>
                                </div>
                            @else
                                <span class="text-muted small fst-italic">Direct / None</span>
                            @endif
                        </td>
                        <td class="text-center" style="min-width:130px;">
                            <div class="fw-bold fs-5 text-dark">
                                {{ $row['attendance_score'] }} 
                                <span class="text-muted fw-normal" style="font-size:0.75rem;">/ {{ $row['att_weight'] }} pts</span>
                            </div>
                            <small class="text-muted">{{ $row['attendance'] }}/{{ $row['days_in_month'] }} days</small>
                            <div class="progress mx-auto mt-1" style="height:4px;max-width:130px;">
                                <div class="progress-bar bg-success" style="width:{{ min(100, ($row['attendance_score'] / max(1, $row['att_weight'])) * 100) }}%;"></div>
                            </div>
                        </td>
                        <td class="text-center" style="min-width:130px;">
                            <div class="fw-bold fs-5 text-dark">
                                {{ $row['task_score'] }}
                                <span class="text-muted fw-normal" style="font-size:0.75rem;">/ {{ $row['task_weight'] }} pts</span>
                            </div>
                            <small class="text-muted">{{ $row['completed_tasks'] }}/{{ max(1, $row['tasks']) }} tasks</small>
                            <div class="progress mx-auto mt-1" style="height:4px;max-width:130px;">
                                <div class="progress-bar bg-primary" style="width:{{ min(100, ($row['task_score'] / max(1, $row['task_weight'])) * 100) }}%;"></div>
                            </div>
                        </td>
                       
                        <td class="text-center" style="min-width:130px;">
                            @php $merchantPercentage = $row['merchant_target'] > 0 ? min(100, ($row['merchants'] / $row['merchant_target']) * 100) : 0; @endphp
                            <div class="fw-bold fs-5 text-dark">
                                {{ $row['merchant_score'] }}
                                <span class="text-muted fw-normal" style="font-size:0.75rem;">/ {{ $row['merch_weight'] }} pts</span>
                            </div>
                            <small class="text-muted">{{ number_format($row['merchants'], 0) }}/{{ number_format($row['merchant_target'], 0) }} ({{ number_format($merchantPercentage, 1) }}%)</small>
                            <div class="progress mx-auto mt-1" style="height:4px;max-width:130px;">
                                <div class="progress-bar bg-info" style="width:{{ min(100, ($row['merchant_score'] / max(1, $row['merch_weight'])) * 100) }}%;"></div>
                            </div>
                        </td>
                        <td class="text-center" style="min-width:155px;">
                            <div class="fw-bold fs-5 text-dark">
                                {{ $row['monthly_target_score'] }}
                                <span class="text-muted fw-normal" style="font-size:0.75rem;">/ {{ $row['month_weight'] }} pts</span>
                            </div>
                            <small class="text-muted">₹{{ number_format($row['monthly_achieved'], 0) }} / ₹{{ number_format($row['monthly_target'], 0) }} ({{ number_format($row['monthly_percentage'], 1) }}%)</small>
                            <div class="progress mx-auto mt-1" style="height:4px;max-width:150px;">
                                <div class="progress-bar bg-warning" style="width:{{ min(100, ($row['monthly_target_score'] / max(1, $row['month_weight'])) * 100) }}%;"></div>
                            </div>
                        </td>
                        <td class="text-center" style="min-width:100px;">
                            <div class="fw-bold fs-5 text-{{ $row['total_score'] >= 70 ? 'primary' : ($row['total_score'] >= 60 ? 'warning' : 'danger') }}">
                                {{ $row['total_score'] }}
                                <span class="text-muted fw-normal" style="font-size:0.75rem;">/ 100</span>
                            </div>
                            <div class="progress mx-auto mt-1" style="height:5px;max-width:60px;">
                                <div class="progress-bar bg-{{ $row['total_score'] >= 70 ? 'primary' : ($row['total_score'] >= 60 ? 'warning' : 'danger') }}" style="width:{{ min(100, $row['total_score']) }}%;"></div>
                            </div>
                        </td>
                        <td class="text-center"><span class="badge bg-{{ $row['grade'] === 'A+' ? 'success' : ($row['grade'] === 'A' ? 'primary' : ($row['grade'] === 'B+' ? 'info' : 'warning')) }}">{{ $row['grade'] }}</span></td>
                       
                        <td class="text-center"><a href="{{ route('partner.hrms.report.performance-profile', ['id' => $row['employee_id']]) }}" class="btn btn-sm btn-outline-primary" title="View employee profile"><i class="bi bi-eye"></i><span class="visually-hidden">View</span></a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">No performance data found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>