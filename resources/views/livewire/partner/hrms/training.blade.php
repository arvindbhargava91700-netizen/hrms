<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Training Management & Analytics
            </h4>
            <p class="text-muted small mb-0">Assign, track attendance, evaluate assessment scores, and monitor employee training completion.</p>
        </div>
        <div class="d-flex align-items-center gap-2 d-print-none">
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm" wire:click="openTrainingModal()">
                <i class="bi bi-plus-lg"></i> Create Program
            </button>
            <button class="btn btn-success btn-sm d-flex align-items-center gap-1 shadow-sm" wire:click="openAssignModal()">
                <i class="bi bi-person-plus-fill"></i> Assign Training
            </button>
            <!-- <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button> -->
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 d-print-none">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Year</label>
                    <select wire:model.live="filterYear" class="form-select form-select-sm">
                        @foreach(range(date('Y'), date('Y') - 3) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Month</label>
                    <select wire:model.live="filterMonth" class="form-select form-select-sm">
                        <option value="all">Full Year (Jan-Dec)</option>
                        @foreach(range(1, 12) as $m)
                            <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Branch</label>
                    <select wire:model.live="filterBranchId" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Department</label>
                    <select wire:model.live="filterDepartmentId" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Training Program</label>
                    <select wire:model.live="filterTrainingId" class="form-select form-select-sm">
                        <option value="">All Programs</option>
                        @foreach($trainingProgramsList as $t)
                            <option value="{{ $t->id }}">{{ $t->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Status</label>
                    <select wire:model.live="filterStatus" class="form-select form-select-sm">
                        <option value="all">All Statuses</option>
                        <option value="assigned">Assigned</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Training Assigned</div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($totalAssigned) }}</div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                            <i class="bi bi-journal-check fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Training Completed</div>
                            <div class="fs-4 fw-bold text-success mt-1">{{ number_format($totalCompleted) }}</div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #06b6d4 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Average Attendance</div>
                            <div class="fs-4 fw-bold text-info mt-1">{{ $avgAttendance }}%</div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;">
                            <i class="bi bi-person-check-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #8b5cf6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Avg Assessment Score</div>
                            <div class="fs-4 fw-bold text-purple mt-1" style="color: #7c3aed;">{{ $avgAssessmentScore }}%</div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                            <i class="bi bi-award-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Pending Training</div>
                            <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($pendingTraining) }}</div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                            <i class="bi bi-hourglass-split fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-3 mb-4">
        {{-- Chart 1: Training Assigned vs Completed --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-bar-chart-fill text-primary me-2"></i>Training Assigned vs Completed ({{ $filterYear }})
                    </h6>
                    <span class="badge bg-light text-dark border">Monthly Trend</span>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="assignedVsCompletedChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 2: Department Completion Rate --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-pie-chart-fill text-success me-2"></i>Department Training Completion Rate
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="deptCompletionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Chart 3: Pending Training Trend --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-graph-up-arrow text-warning me-2"></i>Pending Training Trend
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 240px;">
                        <canvas id="pendingTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 4: Assessment Score Distribution --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-award-fill text-purple me-2" style="color: #7c3aed;"></i>Average Assessment Score by Program
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 240px;">
                        <canvas id="scoreDistChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown Tables Section --}}
    <div class="row g-3 mb-4">
        {{-- Department Training Breakdown --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-diagram-2-fill text-primary me-2"></i>Department Training Analytics
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted uppercase">
                            <tr>
                                <th class="ps-3">Department</th>
                                <th>Assigned</th>
                                <th>Completed</th>
                                <th>Pending</th>
                                <th>Avg Att. %</th>
                                <th class="pe-3 text-end">Avg Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deptBreakdown as $dept)
                                <tr>
                                    <td class="ps-3 fw-bold text-dark">{{ $dept['department'] }}</td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $dept['assigned'] }}</span></td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle">{{ $dept['completed'] }}</span></td>
                                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $dept['pending'] }}</span></td>
                                    <td class="fw-semibold text-info">{{ $dept['attendance_pct'] }}%</td>
                                    <td class="pe-3 text-end fw-bold text-purple" style="color: #7c3aed;">{{ $dept['score_pct'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">No department training data recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Branch Training Breakdown --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-buildings-fill text-warning me-2"></i>Branch Training Analytics
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted uppercase">
                            <tr>
                                <th class="ps-3">Branch</th>
                                <th>Assigned</th>
                                <th>Completed</th>
                                <th>Pending</th>
                                <th>Avg Att. %</th>
                                <th class="pe-3 text-end">Avg Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchBreakdown as $branch)
                                <tr>
                                    <td class="ps-3 fw-bold text-dark">{{ $branch['branch'] }}</td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $branch['assigned'] }}</span></td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle">{{ $branch['completed'] }}</span></td>
                                    <td><span class="badge bg-warning-subtle text-warning border border-warning-subtle">{{ $branch['pending'] }}</span></td>
                                    <td class="fw-semibold text-info">{{ $branch['attendance_pct'] }}%</td>
                                    <td class="pe-3 text-end fw-bold text-purple" style="color: #7c3aed;">{{ $branch['score_pct'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">No branch training data recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Employee Training Assignments Table --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-person-workspace me-2 text-danger"></i>Employee Training Assignments
            </h6>
            <div class="d-flex align-items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Search employee / program..." style="width: 220px;">
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 75px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Department / Branch</th>
                        <th>Training Program</th>
                        <th>Assigned Date</th>
                        <th>Status</th>
                        <th>Attendance %</th>
                        <th>Assessment</th>
                        <th>Result</th>
                        <th class="pe-3 text-end d-print-none">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedAssignments as $item)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $item->employee?->avatar_url }}" class="rounded-circle" width="32" height="32">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $item->employee?->name }}</div>
                                        <div class="text-muted small">{{ $item->employee?->employee_code ?? $item->employee?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $item->department?->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $item->branch?->name ?? 'Main Branch' }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-primary">{{ $item->training?->title }}</div>
                                <small class="text-muted">Trainer: {{ $item->training?->trainer ?? 'Internal' }}</small>
                            </td>
                            <td>{{ $item->assigned_at ? $item->assigned_at->format('d M Y') : 'N/A' }}</td>
                            <td>
                                @if($item->status === 'completed')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Completed</span>
                                @elseif($item->status === 'in_progress')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">In Progress</span>
                                @elseif($item->status === 'cancelled')
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Cancelled</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Assigned</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-fill" style="height: 6px;">
                                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ min($item->attendance_percentage, 100) }}%"></div>
                                    </div>
                                    <span class="fw-semibold text-muted" style="font-size: 0.75rem;">{{ $item->attendance_percentage }}%</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ number_format($item->obtained_score, 1) }} / {{ number_format($item->maximum_score, 1) }}</div>
                                <small class="text-muted">({{ $item->assessment_score }}%)</small>
                            </td>
                            <td>
                                @if($item->result === 'passed')
                                    <span class="badge bg-success text-white">PASSED</span>
                                @elseif($item->result === 'failed')
                                    <span class="badge bg-danger text-white">FAILED</span>
                                @else
                                    <span class="badge bg-light text-dark border">PENDING</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end d-print-none">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" title="Log Attendance" wire:click="openAttendanceModal({{ $item->id }})">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                    <button class="btn btn-outline-purple" style="color: #7c3aed; border-color: #7c3aed;" title="Log Assessment" wire:click="openAssessmentModal({{ $item->id }})">
                                        <i class="bi bi-award"></i>
                                    </button>
                                    @if($item->status !== 'completed')
                                        <button class="btn btn-outline-success" title="Mark Completed" wire:click="markCompleted({{ $item->id }})">
                                            <i class="bi bi-check2-circle"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                                No employee training assignments found. Click "Assign Training" to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedAssignments->hasPages())
            <div class="card-footer bg-white py-3 border-0">
                {{ $paginatedAssignments->links() }}
            </div>
        @endif
    </div>

    {{-- Master Training Programs Management Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-sliders me-2 text-primary"></i>Training Programs Master Catalog
            </h6>
            <button class="btn btn-primary btn-sm d-print-none" wire:click="openTrainingModal()">
                <i class="bi bi-plus-lg me-1"></i> Add Program
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Title</th>
                        <th>Type</th>
                        <th>Trainer</th>
                        <th>Start Date</th>
                        <th>Duration</th>
                        <th>Passing Score</th>
                        <th>Status</th>
                        <th class="pe-3 text-end d-print-none">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programBreakdown as $prog)
                        @php $tModel = $trainingProgramsList->firstWhere('id', $prog['id']); @endphp
                        <tr>
                            <td class="ps-3 fw-bold text-dark">{{ $prog['title'] }}</td>
                            <td><span class="badge bg-light text-dark border">{{ strtoupper($tModel?->training_type ?? 'classroom') }}</span></td>
                            <td>{{ $prog['trainer'] ?: 'Internal' }}</td>
                            <td>{{ $tModel?->start_date ? $tModel->start_date->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $tModel?->duration }} Day(s)</td>
                            <td class="fw-bold text-purple" style="color: #7c3aed;">{{ $tModel?->passing_score }}%</td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">{{ ucfirst($tModel?->status ?? 'scheduled') }}</span>
                            </td>
                            <td class="pe-3 text-end d-print-none">
                                <button class="btn btn-outline-primary btn-sm me-1" wire:click="openTrainingModal({{ $prog['id'] }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm" wire:click="deleteTraining({{ $prog['id'] }})" onclick="return confirm('Delete this training program?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-3 text-muted">No training programs created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Modals Section ────────────────────────────────────────────────── --}}

    {{-- Modal 1: Create/Edit Training Master --}}
    @if($showTrainingModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold modal-title">{{ $editingTrainingId ? 'Edit Training Program' : 'Create Training Program' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeTrainingModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Training Title *</label>
                            <input type="text" wire:model="title" class="form-control" placeholder="e.g., Leadership Development Program">
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Trainer Name</label>
                            <input type="text" wire:model="trainer" class="form-control" placeholder="Trainer or Agency name">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Training Type</label>
                                <select wire:model="training_type" class="form-select">
                                    <option value="classroom">Classroom</option>
                                    <option value="online">Online</option>
                                    <option value="on_job">On-The-Job</option>
                                    <option value="workshop">Workshop</option>
                                    <option value="certification">Certification</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Duration (Days/Hours)</label>
                                <input type="number" wire:model="duration" class="form-control" min="1">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Start Date *</label>
                                <input type="date" wire:model="start_date" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" wire:model="end_date" class="form-control">
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Passing Score %</label>
                                <input type="number" step="0.1" wire:model="passing_score" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Status</label>
                                <select wire:model="status" class="form-select">
                                    <option value="scheduled">Scheduled</option>
                                    <option value="active">Active</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description</label>
                            <textarea wire:model="description" class="form-control" rows="2" placeholder="Program overview..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" wire:click="closeTrainingModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveTraining()">Save Program</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal 2: Bulk Employee Assignment --}}
    @if($showAssignModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold modal-title">Assign Training Program to Employees</h5>
                        <button type="button" class="btn-close" wire:click="closeAssignModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold">Select Training Program *</label>
                                <select wire:model="selectedTrainingId" class="form-select">
                                    <option value="">-- Choose Program --</option>
                                    @foreach($trainingProgramsList as $t)
                                        <option value="{{ $t->id }}">{{ $t->title }} ({{ $t->duration }} days)</option>
                                    @endforeach
                                </select>
                                @error('selectedTrainingId') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Filter by Branch</label>
                                <select wire:model.live="assignBranchId" class="form-select form-select-sm">
                                    <option value="">All Branches</option>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Filter by Department</label>
                                <select wire:model.live="assignDepartmentId" class="form-select form-select-sm">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label small fw-bold mb-0">Select Employees *</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="selectAllCheck" wire:model.live="selectAllEmployees" wire:change="toggleSelectAllEmployees()">
                                    <label class="form-check-label small fw-semibold" for="selectAllCheck">Select All</label>
                                </div>
                            </div>
                            <div class="border rounded-3 p-2" style="max-height: 180px; overflow-y: auto;">
                                <div class="row g-2">
                                    @forelse($assignableEmployees as $emp)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" value="{{ $emp->id }}" id="emp_{{ $emp->id }}" wire:model="selectedEmployeeIds">
                                                <label class="form-check-label small text-dark" for="emp_{{ $emp->id }}">
                                                    <strong>{{ $emp->name }}</strong> ({{ $emp->department?->name ?? 'N/A' }})
                                                </label>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-muted small p-2">No employees match filters.</div>
                                    @endforelse
                                </div>
                            </div>
                            @error('selectedEmployeeIds') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Start Date *</label>
                                <input type="date" wire:model="assignStartDate" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">End Date</label>
                                <input type="date" wire:model="assignEndDate" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" wire:click="closeAssignModal()">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="assignEmployees()">Confirm Assignment</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal 3: Attendance Logging --}}
    @if($showAttendanceModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold modal-title">Log Training Attendance</h5>
                        <button type="button" class="btn-close" wire:click="closeAttendanceModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Session Date *</label>
                            <input type="date" wire:model="attDate" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Attendance Status</label>
                            <select wire:model="attStatus" class="form-select">
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Remarks</label>
                            <input type="text" wire:model="attRemarks" class="form-control" placeholder="Session notes...">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" wire:click="closeAttendanceModal()">Cancel</button>
                        <button type="button" class="btn btn-info text-white" wire:click="saveAttendance()">Save Attendance</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal 4: Assessment Scoring --}}
    @if($showAssessmentModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold modal-title">Record Assessment Score</h5>
                        <button type="button" class="btn-close" wire:click="closeAssessmentModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Maximum Score *</label>
                                <input type="number" step="0.1" wire:model="assessMaxScore" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Obtained Score *</label>
                                <input type="number" step="0.1" wire:model="assessObtainedScore" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Remarks / Feedback</label>
                            <textarea wire:model="assessRemarks" class="form-control" rows="2" placeholder="Trainer evaluation feedback..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" wire:click="closeAssessmentModal()">Cancel</button>
                        <button type="button" class="btn btn-purple text-white" style="background: #7c3aed;" wire:click="saveAssessment()">Save Score & Result</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Chart.js Script --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            let assignedVsCompletedChartInstance = null;
            let deptCompletionChartInstance = null;
            let pendingTrendChartInstance = null;
            let scoreDistChartInstance = null;

            function initTrainingCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(initTrainingCharts, 100);
                    return;
                }

                const monthlyTrends = @json($monthlyTrends);
                const deptBreakdown = @json($deptBreakdown);
                const programBreakdown = @json($programBreakdown);

                // Chart 1: Assigned vs Completed Bar Chart
                const canvas1 = document.getElementById('assignedVsCompletedChart');
                if (canvas1) {
                    if (assignedVsCompletedChartInstance) {
                        assignedVsCompletedChartInstance.destroy();
                        assignedVsCompletedChartInstance = null;
                    }
                    assignedVsCompletedChartInstance = new Chart(canvas1, {
                        type: 'bar',
                        data: {
                            labels: monthlyTrends.map(m => m.month),
                            datasets: [
                                {
                                    label: 'Assigned',
                                    data: monthlyTrends.map(m => m.assigned),
                                    backgroundColor: '#3b82f6',
                                },
                                {
                                    label: 'Completed',
                                    data: monthlyTrends.map(m => m.completed),
                                    backgroundColor: '#10b981',
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }

                // Chart 2: Department Completion Rate
                const canvas2 = document.getElementById('deptCompletionChart');
                if (canvas2) {
                    if (deptCompletionChartInstance) {
                        deptCompletionChartInstance.destroy();
                        deptCompletionChartInstance = null;
                    }
                    deptCompletionChartInstance = new Chart(canvas2, {
                        type: 'doughnut',
                        data: {
                            labels: deptBreakdown.map(d => d.department),
                            datasets: [{
                                data: deptBreakdown.map(d => d.completed),
                                backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4', '#64748b']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'right' } }
                        }
                    });
                }

                // Chart 3: Pending Training Trend
                const canvas3 = document.getElementById('pendingTrendChart');
                if (canvas3) {
                    if (pendingTrendChartInstance) {
                        pendingTrendChartInstance.destroy();
                        pendingTrendChartInstance = null;
                    }
                    pendingTrendChartInstance = new Chart(canvas3, {
                        type: 'line',
                        data: {
                            labels: monthlyTrends.map(m => m.month),
                            datasets: [{
                                label: 'Pending Training',
                                data: monthlyTrends.map(m => m.pending),
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }

                // Chart 4: Average Assessment Score by Program
                const canvas4 = document.getElementById('scoreDistChart');
                if (canvas4) {
                    if (scoreDistChartInstance) {
                        scoreDistChartInstance.destroy();
                        scoreDistChartInstance = null;
                    }
                    scoreDistChartInstance = new Chart(canvas4, {
                        type: 'bar',
                        data: {
                            labels: programBreakdown.map(p => p.title),
                            datasets: [{
                                label: 'Avg Score %',
                                data: programBreakdown.map(p => p.score_pct),
                                backgroundColor: '#8b5cf6'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true, max: 100 } }
                        }
                    });
                }
            }

            setTimeout(initTrainingCharts, 50);

            document.addEventListener('DOMContentLoaded', initTrainingCharts);
            document.addEventListener('livewire:navigated', initTrainingCharts);

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                    succeed(() => {
                        setTimeout(initTrainingCharts, 100);
                    });
                });
            }
        })();
    </script>
</div>
