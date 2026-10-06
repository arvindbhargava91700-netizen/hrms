<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">HRMS Employee Cost & CTC Report</h4>
            <p class="text-muted small mb-0">Total employment costs across salary, overtime, incentives, and annual CTC metrics.</p>
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
                    <label class="form-label small fw-bold text-muted mb-1">Financial Year</label>
                    <select wire:model.live="filterYear" class="form-select form-select-sm">
                        @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Branch</label>
                    <select wire:model.live="filterBranchId" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Department</label>
                    <select wire:model.live="filterDepartmentId" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Search Employee</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Search name or code...">
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Total Employee Cost</div>
                    <div class="fs-4 fw-bold mt-1">₹{{ number_format($totalEmployeeCost > 0 ? $totalEmployeeCost : $totalAnnualCTC, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-3 bg-success text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Salary Cost</div>
                    <div class="fs-4 fw-bold mt-1">₹{{ number_format($totalSalaryCost, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-3 bg-warning text-dark h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-dark-50 text-uppercase fw-semibold">Overtime Cost</div>
                    <div class="fs-4 fw-bold mt-1">₹{{ number_format($totalOvertimeCost, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-3 bg-info text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Incentive Cost</div>
                    <div class="fs-4 fw-bold mt-1">₹{{ number_format($totalIncentiveCost, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-3 bg-purple text-white h-100" style="background-color: #7c3aed;">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Total Annual CTC</div>
                    <div class="fs-4 fw-bold mt-1">₹{{ number_format($totalAnnualCTC, 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-3 bg-dark text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Avg Employee CTC</div>
                    <div class="fs-4 fw-bold mt-1">₹{{ number_format($averageCTC, 0) }}</div>
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
                        <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Monthly Employee Cost Breakdown ({{ $filterYear }})
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="monthlyCostChartPartner"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-pie-chart-fill text-success me-2"></i>Department Salary Cost Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="deptCostChartPartner"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-person-lines-fill me-2 text-danger"></i>Employee CTC & Cost Details
            </h6>
            <div class="d-flex align-items-center gap-2 d-print-none">
                <span class="text-muted small">Show</span>
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 70px;">
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
                        <th>Monthly Salary</th>
                        <th>Annual CTC</th>
                        <th>OT Cost</th>
                        <th>Incentive Cost</th>
                        <th class="pe-3 text-end">Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedMetrics as $m)
                        @php $emp = $m['employee']; @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $emp->avatar_url }}" class="rounded-circle" width="32" height="32">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $emp->name }}</div>
                                        <div class="text-muted small">{{ $emp->employee_code ?? $emp->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $emp->branch?->name ?? 'Main Branch' }}</td>
                            <td>{{ $emp->department?->name ?? 'N/A' }}</td>
                            <td class="fw-semibold text-dark">₹{{ number_format($m['monthly_salary'], 0) }}</td>
                            <td class="fw-bold text-purple" style="color: #7c3aed;">₹{{ number_format($m['annual_ctc'], 0) }}</td>
                            <td class="text-warning fw-semibold">₹{{ number_format($m['ot_cost'], 0) }}</td>
                            <td class="text-pink fw-semibold" style="color: #ec4899;">₹{{ number_format($m['incentive_cost'], 0) }}</td>
                            <td class="pe-3 text-end fw-extrabold text-danger fs-6">₹{{ number_format($m['total_cost'], 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No employee records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedEmployees->hasPages())
            <div class="card-footer bg-white py-3 border-0 d-print-none">
                {{ $paginatedEmployees->links() }}
            </div>
        @endif
    </div>

    {{-- Chart.js Script --}}
    <script>
        (function() {
            let partnerMonthlyChartInstance = null;
            let partnerDeptChartInstance = null;

            function initPartnerEmployeeCostCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(initPartnerEmployeeCostCharts, 100);
                    return;
                }

                const monthlyTrends = @json($monthlyTrends);
                const deptBreakdown = @json($deptCostBreakdown);

                const monthlyCanvas = document.getElementById('monthlyCostChartPartner');
                if (monthlyCanvas) {
                    if (partnerMonthlyChartInstance) {
                        partnerMonthlyChartInstance.destroy();
                        partnerMonthlyChartInstance = null;
                    }
                    partnerMonthlyChartInstance = new Chart(monthlyCanvas, {
                        type: 'bar',
                        data: {
                            labels: monthlyTrends.map(m => m.month),
                            datasets: [{
                                    label: 'Salary Cost',
                                    data: monthlyTrends.map(m => m.salary_cost),
                                    backgroundColor: '#10b981',
                                },
                                {
                                    label: 'Overtime Cost',
                                    data: monthlyTrends.map(m => m.ot_cost),
                                    backgroundColor: '#f59e0b',
                                },
                                {
                                    label: 'Incentive Cost',
                                    data: monthlyTrends.map(m => m.incentive_cost),
                                    backgroundColor: '#ec4899',
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: { stacked: true },
                                y: { stacked: true, beginAtZero: true }
                            }
                        }
                    });
                }

                const deptCanvas = document.getElementById('deptCostChartPartner');
                if (deptCanvas) {
                    if (partnerDeptChartInstance) {
                        partnerDeptChartInstance.destroy();
                        partnerDeptChartInstance = null;
                    }
                    partnerDeptChartInstance = new Chart(deptCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: deptBreakdown.map(d => d.department),
                            datasets: [{
                                data: deptBreakdown.map(d => d.salary_cost),
                                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#06b6d4', '#64748b']
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

            setTimeout(initPartnerEmployeeCostCharts, 50);

            document.addEventListener('DOMContentLoaded', initPartnerEmployeeCostCharts);
            document.addEventListener('livewire:navigated', initPartnerEmployeeCostCharts);

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                    succeed(() => {
                        setTimeout(initPartnerEmployeeCostCharts, 100);
                    });
                });
            }
        })();
    </script>
</div>
