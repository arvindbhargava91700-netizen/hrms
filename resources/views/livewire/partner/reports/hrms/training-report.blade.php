<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">HRMS Training & Assessment Report</h4>
            <p class="text-muted small mb-0">Employee training assignments, attendance tracking, assessment scores, and program completion progress.</p>
        </div>
        <div class="d-flex align-items-center gap-2 d-print-none">
            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 d-print-none">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Training Program</label>
                    <select wire:model.live="filterProgramId" class="form-select form-select-sm">
                        <option value="">All Programs</option>
                        @foreach($trainingPrograms as $tp)
                            <option value="{{ $tp->id }}">{{ $tp->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select wire:model.live="filterStatus" class="form-select form-select-sm">
                        <option value="all">All Statuses</option>
                        <option value="assigned">Assigned</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Branch</label>
                    <select wire:model.live="filterBranchId" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Team / Reporting Manager</label>
                    <select wire:model.live="teamId" class="form-select form-select-sm">
                        <option value="">All Teams / Managers</option>
                        @foreach($teams as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} {{ $t->employee_code ? '('.$t->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Department</label>
                    <select wire:model.live="filterDepartmentId" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Result</label>
                    <select wire:model.live="filterResult" class="form-select form-select-sm">
                        <option value="all">All Results</option>
                        <option value="passed">Passed</option>
                        <option value="failed">Failed</option>
                        <option value="pending">Pending Score</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-20 col-sm-6" style="width: 20%;">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Trainings Assigned</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($totalAssigned) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-20 col-sm-6" style="width: 20%;">
            <div class="card border-0 shadow-sm rounded-3 bg-success text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Trainings Completed</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($totalCompleted) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-20 col-sm-6" style="width: 20%;">
            <div class="card border-0 shadow-sm rounded-3 bg-warning text-dark h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-dark-50 text-uppercase fw-semibold">Pending Training</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($totalPending) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-20 col-sm-6" style="width: 20%;">
            <div class="card border-0 shadow-sm rounded-3 bg-info text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Avg Attendance %</div>
                    <div class="fs-3 fw-bold mt-1">{{ $avgAttendance }}%</div>
                </div>
            </div>
        </div>
        <div class="col-md-20 col-sm-6" style="width: 20%;">
            <div class="card border-0 shadow-sm rounded-3 bg-purple text-white h-100" style="background-color: #8b5cf6;">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Avg Assessment %</div>
                    <div class="fs-3 fw-bold mt-1">{{ $avgAssessmentScore }}%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Monthly Assigned vs Completed Trend ({{ date('Y') }})
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="assignedVsCompletedChartPartner"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-pie-chart-fill text-success me-2"></i>Department Completion Rate
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="deptCompletionChartPartner"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Assignments Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-person-check-fill text-primary me-2"></i>Employee Training Assignment Details
            </h6>
            <div class="d-flex align-items-center gap-2 d-print-none">
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
                        <th>Department</th>
                        <th>Branch</th>
                        <th>Training Program</th>
                        <th>Assigned Date</th>
                        <th>Status</th>
                        <th>Attendance %</th>
                        <th>Assessment Score</th>
                        <th class="pe-3 text-end">Result</th>
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
                            <td>{{ $item->department?->name ?? 'N/A' }}</td>
                            <td>{{ $item->branch?->name ?? 'Main Branch' }}</td>
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
                            <td class="pe-3 text-end">
                                @if($item->result === 'passed')
                                    <span class="badge bg-success text-white">PASSED</span>
                                @elseif($item->result === 'failed')
                                    <span class="badge bg-danger text-white">FAILED</span>
                                @else
                                    <span class="badge bg-light text-dark border">PENDING</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                                No employee training assignments found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedAssignments->hasPages())
            <div class="card-footer bg-white py-3 border-0 d-print-none">
                {{ $paginatedAssignments->links() }}
            </div>
        @endif
    </div>

    {{-- Chart.js Script --}}
    <script>
        (function() {
            let partnerAssignedVsCompletedChartInstance = null;
            let partnerDeptCompletionChartInstance = null;

            function initPartnerTrainingCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(initPartnerTrainingCharts, 100);
                    return;
                }

                const monthlyTrends = @json($monthlyTrends);
                const deptBreakdown = @json($deptBreakdown);

                const canvas1 = document.getElementById('assignedVsCompletedChartPartner');
                if (canvas1) {
                    if (partnerAssignedVsCompletedChartInstance) {
                        partnerAssignedVsCompletedChartInstance.destroy();
                        partnerAssignedVsCompletedChartInstance = null;
                    }
                    partnerAssignedVsCompletedChartInstance = new Chart(canvas1, {
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

                const canvas2 = document.getElementById('deptCompletionChartPartner');
                if (canvas2) {
                    if (partnerDeptCompletionChartInstance) {
                        partnerDeptCompletionChartInstance.destroy();
                        partnerDeptCompletionChartInstance = null;
                    }
                    partnerDeptCompletionChartInstance = new Chart(canvas2, {
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
            }

            setTimeout(initPartnerTrainingCharts, 50);

            document.addEventListener('DOMContentLoaded', initPartnerTrainingCharts);
            document.addEventListener('livewire:navigated', initPartnerTrainingCharts);

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                    succeed(() => {
                        setTimeout(initPartnerTrainingCharts, 100);
                    });
                });
            }
        })();
    </script>
</div>
