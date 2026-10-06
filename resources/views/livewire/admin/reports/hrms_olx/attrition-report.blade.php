<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">HRMS Attrition Analytics Report</h4>
            <p class="text-muted small mb-0">Employee attrition trends, exit reasons, voluntary vs involuntary breakdown, and retention metrics.</p>
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
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Year</label>
                    <select wire:model.live="filterYear" class="form-select form-select-sm">
                        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Month</label>
                    <select wire:model.live="filterMonth" class="form-select form-select-sm">
                        <option value="all">All Months</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endfor
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
                    <label class="form-label small fw-bold text-muted mb-1">Department</label>
                    <select wire:model.live="filterDepartmentId" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Exit Type</label>
                    <select wire:model.live="filterExitType" class="form-select form-select-sm">
                        <option value="all">All Types</option>
                        <option value="voluntary">Voluntary</option>
                        <option value="involuntary">Involuntary</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Exit Reason</label>
                    <select wire:model.live="filterExitReason" class="form-select form-select-sm">
                        <option value="all">All Reasons</option>
                        @foreach($exitReasonsList as $reason)
                            <option value="{{ $reason }}">{{ $reason }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-danger text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Attrition Rate</div>
                    <div class="fs-3 fw-bold mt-1">{{ $attritionRate }}%</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Total Exits</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($totalExits) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-warning text-dark h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-dark-50 text-uppercase fw-semibold">Voluntary Exits</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($voluntaryExits) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-secondary text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Involuntary Exits</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($involuntaryExits) }}</div>
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
                        <i class="bi bi-graph-down-arrow text-danger me-2"></i>Monthly Exit Trends ({{ $filterYear }})
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="monthlyExitChartPartner"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-pie-chart-fill text-warning me-2"></i>Exit Reasons Breakdown
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="exitReasonChartPartner"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-box-arrow-right text-danger me-2"></i>Employee Exit Logs & Details
            </h6>
            <div class="d-flex align-items-center gap-2 d-print-none">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Search employee..." style="width: 220px;">
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
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Exit Date</th>
                        <th>Last Working Date</th>
                        <th>Exit Type</th>
                        <th>Reason</th>
                        <th class="pe-3 text-end">Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedExits as $exit)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $exit->employee?->avatar_url }}" class="rounded-circle" width="32" height="32">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $exit->employee?->name }}</div>
                                        <div class="text-muted small">{{ $exit->employee?->employee_code ?? $exit->employee?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $exit->branch?->name ?? 'Main Branch' }}</td>
                            <td>{{ $exit->department?->name ?? 'N/A' }}</td>
                            <td>{{ $exit->exit_date ? $exit->exit_date->format('d M Y') : 'N/A' }}</td>
                            <td>{{ $exit->last_working_date ? $exit->last_working_date->format('d M Y') : 'N/A' }}</td>
                            <td>
                                @if($exit->exit_type === 'voluntary')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Voluntary</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Involuntary</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $exit->exit_reason }}</span></td>
                            <td class="pe-3 text-end text-muted small">{{ Str::limit($exit->remarks, 30) ?: 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-box-arrow-right fs-3 d-block mb-2"></i>
                                No employee exit records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedExits->hasPages())
            <div class="card-footer bg-white py-3 border-0 d-print-none">
                {{ $paginatedExits->links() }}
            </div>
        @endif
    </div>

    {{-- Chart.js Script --}}
    <script>
        (function() {
            let partnerMonthlyExitChartInstance = null;
            let partnerExitReasonChartInstance = null;

            function initPartnerAttritionCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(initPartnerAttritionCharts, 100);
                    return;
                }

                const monthlyTrends = @json($monthlyTrends);
                const exitReasonsBreakdown = @json($exitReasonsBreakdown);

                const canvas1 = document.getElementById('monthlyExitChartPartner');
                if (canvas1) {
                    if (partnerMonthlyExitChartInstance) {
                        partnerMonthlyExitChartInstance.destroy();
                        partnerMonthlyExitChartInstance = null;
                    }
                    partnerMonthlyExitChartInstance = new Chart(canvas1, {
                        type: 'line',
                        data: {
                            labels: monthlyTrends.map(m => m.month),
                            datasets: [{
                                label: 'Exits Count',
                                data: monthlyTrends.map(m => m.exits),
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
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

                const canvas2 = document.getElementById('exitReasonChartPartner');
                if (canvas2) {
                    if (partnerExitReasonChartInstance) {
                        partnerExitReasonChartInstance.destroy();
                        partnerExitReasonChartInstance = null;
                    }
                    partnerExitReasonChartInstance = new Chart(canvas2, {
                        type: 'doughnut',
                        data: {
                            labels: exitReasonsBreakdown.map(r => r.reason),
                            datasets: [{
                                data: exitReasonsBreakdown.map(r => r.count),
                                backgroundColor: ['#f59e0b', '#ec4899', '#3b82f6', '#10b981', '#8b5cf6', '#64748b']
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

            setTimeout(initPartnerAttritionCharts, 50);

            document.addEventListener('DOMContentLoaded', initPartnerAttritionCharts);
            document.addEventListener('livewire:navigated', initPartnerAttritionCharts);

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                    succeed(() => {
                        setTimeout(initPartnerAttritionCharts, 100);
                    });
                });
            }
        })();
    </script>
</div>
