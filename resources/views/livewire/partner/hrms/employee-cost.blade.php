<div>
    {{-- Data attributes for Chart.js (updated by Livewire on each render) --}}
    <div id="monthly-trends-data" style="display:none;">{{ json_encode($monthlyTrends) }}</div>
    <div id="dept-breakdown-data" style="display:none;">{{ json_encode($deptCostBreakdown) }}</div>
    <div id="branch-breakdown-data" style="display:none;">{{ json_encode($branchCostBreakdown) }}</div>
    <div id="total-ot-data" style="display:none;">{{ $totalOvertimeCost }}</div>
    <div id="total-inc-data" style="display:none;">{{ $totalIncentiveCost }}</div>

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
                <i class="bi bi-cash-stack text-primary me-2"></i>Employee Cost Analytics & Reporting
            </h4>
            <p class="text-muted small mb-0">Analyze total employment costs across salary, overtime, and incentives along with annual CTC metrics.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button> -->
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
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
                    <label class="form-label text-muted small fw-bold mb-1">Employee</label>
                    <select wire:model.live="filterEmployeeId" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        @foreach($allEmployeesList as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Search name/code...">
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
                            <div class="text-muted small fw-semibold">Total Employees</div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($totalEmployees) }}</div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #8b5cf6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Total Annual CTC</div>
                            <div class="fs-5 fw-bold text-purple mt-1" style="color: #7c3aed;">
                                ₹{{ number_format($totalCtcCost, 0) }}
                            </div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                            <i class="bi bi-award-fill fs-5"></i>
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
                            <div class="text-muted small fw-semibold">Salary Cost</div>
                            <div class="fs-5 fw-bold text-success mt-1">
                                ₹{{ number_format($totalSalaryCost, 0) }}
                            </div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="bi bi-currency-rupee fs-5"></i>
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
                            <div class="text-muted small fw-semibold">Overtime Cost</div>
                            <div class="fs-5 fw-bold text-warning mt-1">
                                ₹{{ number_format($totalOvertimeCost, 0) }}
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">({{ $totalOvertimeHours }} hrs)</small>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                            <i class="bi bi-clock-history fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #ec4899 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Incentive Cost</div>
                            <div class="fs-5 fw-bold text-pink mt-1" style="color: #ec4899;">
                                ₹{{ number_format($totalIncentiveCost, 0) }}
                            </div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;">
                            <i class="bi bi-trophy-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #ef4444 !important; background: linear-gradient(135deg, #ffffff 0%, #fef2f2 100%);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Total Employee Cost</div>
                            <div class="fs-5 fw-extrabold text-danger mt-1">
                                ₹{{ number_format($totalEmployeeCost, 0) }}
                            </div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i class="bi bi-wallet2 fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-3 mb-4">
        {{-- Chart 1: Monthly Cost Trend --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-bar-chart-line-fill text-primary me-2"></i>Monthly Employee Cost Breakdown ({{ $filterYear }})
                    </h6>
                    <span class="badge bg-light text-dark border">Salary + OT + Incentive</span>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="monthlyCostChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 2: Department Salary Cost Distribution --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-pie-chart-fill text-success me-2"></i>Department Salary Cost Distribution
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="deptCostChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Chart 3: Branch Cost Breakdown --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-building text-warning me-2"></i>Branch Employee Cost Comparison
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 240px;">
                        <canvas id="branchCostChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 4: Overtime vs Incentive Cost --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-diagram-3-fill text-info me-2"></i>Overtime vs Incentive Cost Comparison
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 240px;">
                        <canvas id="otVsIncentiveChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Breakdown Tables Section --}}
    <div class="row g-3 mb-4">
        {{-- Department Salary Cost Table --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-diagram-2-fill text-primary me-2"></i>Department Salary Cost & Share (%)
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted uppercase">
                            <tr>
                                <th class="ps-3">Department</th>
                                <th>Employees</th>
                                <th>Salary Cost</th>
                                <th>Cost Share (%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deptCostBreakdown as $dept)
                            <tr>
                                <td class="ps-3 fw-bold text-dark">{{ $dept['department'] }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $dept['employees'] }}</span></td>
                                <td class="fw-bold text-success">₹{{ number_format($dept['salary_cost'], 0) }}</td>
                                <td style="width: 35%;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-fill" style="height: 8px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min($dept['percentage'], 100) }}%"></div>
                                        </div>
                                        <span class="fw-semibold text-muted" style="font-size: 0.75rem;">{{ $dept['percentage'] }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3 text-muted">No department cost data recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Branch Cost Table --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-buildings-fill text-warning me-2"></i>Branch Employee Total Cost
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="bg-light text-muted uppercase">
                            <tr>
                                <th class="ps-3">Branch</th>
                                <th>Employees</th>
                                <th>Salary</th>
                                <th>OT</th>
                                <th>Incentive</th>
                                <th class="pe-3 text-end">Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branchCostBreakdown as $branch)
                            <tr>
                                <td class="ps-3 fw-bold text-dark">{{ $branch['branch'] }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $branch['employees'] }}</span></td>
                                <td>₹{{ number_format($branch['salary_cost'], 0) }}</td>
                                <td class="text-warning fw-semibold">₹{{ number_format($branch['ot_cost'], 0) }}</td>
                                <td class="text-pink fw-semibold" style="color: #ec4899;">₹{{ number_format($branch['incentive_cost'], 0) }}</td>
                                <td class="pe-3 text-end fw-bold text-danger">₹{{ number_format($branch['total_cost'], 0) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">No branch cost data recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Employee Cost Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-person-lines-fill me-2 text-danger"></i>Employee-wise CTC & Cost Details
            </h6>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show</span>
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 70px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Branch</th>
                        <th>Department / Designation</th>
                        <th>Monthly Salary</th>
                        <th>Annual CTC</th>
                        <th>OT Hours</th>
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
                        <td>
                            <div>{{ $emp->department?->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $emp->designation?->name ?? '' }}</small>
                        </td>
                        <td class="fw-semibold text-dark">₹{{ number_format($m['monthly_salary'], 0) }}</td>
                        <td class="fw-bold text-purple" style="color: #7c3aed;">₹{{ number_format($m['annual_ctc'], 0) }}</td>
                        <td>{{ $m['ot_hours'] }} hrs</td>
                        <td class="text-warning fw-semibold">₹{{ number_format($m['ot_cost'], 0) }}</td>
                        <td class="text-pink fw-semibold" style="color: #ec4899;">₹{{ number_format($m['incentive_cost'], 0) }}</td>
                        <td class="pe-3 text-end fw-extrabold text-danger fs-6">₹{{ number_format($m['total_cost'], 0) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No employee records found matching your filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedEmployeeIds->hasPages())
        <div class="card-footer bg-white py-3 border-0">
            {{ $paginatedEmployeeIds->links() }}
        </div>
        @endif
    </div>

    {{-- Chart.js Script --}}
    <script>
        (function() {
            let monthlyChartInstance = null;
            let deptChartInstance = null;
            let branchChartInstance = null;
            let otVsIncentiveChartInstance = null;

            function initEmployeeCostCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(initEmployeeCostCharts, 100);
                    return;
                }

                // Read fresh data from DOM data attributes (updated by Livewire)
                const monthlyTrendsEl = document.getElementById('monthly-trends-data');
                const deptBreakdownEl = document.getElementById('dept-breakdown-data');
                const branchBreakdownEl = document.getElementById('branch-breakdown-data');
                const totalOTEl = document.getElementById('total-ot-data');
                const totalIncEl = document.getElementById('total-inc-data');

                if (!monthlyTrendsEl || !deptBreakdownEl || !branchBreakdownEl) {
                    return; // Elements not ready
                }

                const monthlyTrends = JSON.parse(monthlyTrendsEl.textContent || '[]');
                const deptBreakdown = JSON.parse(deptBreakdownEl.textContent || '[]');
                const branchBreakdown = JSON.parse(branchBreakdownEl.textContent || '[]');
                const totalOT = parseFloat(totalOTEl?.textContent || '0');
                const totalInc = parseFloat(totalIncEl?.textContent || '0');

                // Chart 1: Monthly Cost Trend
                const monthlyCanvas = document.getElementById('monthlyCostChart');
                if (monthlyCanvas) {
                    if (monthlyChartInstance) {
                        monthlyChartInstance.destroy();
                        monthlyChartInstance = null;
                    }
                    monthlyChartInstance = new Chart(monthlyCanvas, {
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

                // Chart 2: Department Salary Cost Distribution
                const deptCanvas = document.getElementById('deptCostChart');
                if (deptCanvas) {
                    if (deptChartInstance) {
                        deptChartInstance.destroy();
                        deptChartInstance = null;
                    }
                    deptChartInstance = new Chart(deptCanvas, {
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

                // Chart 3: Branch Cost Breakdown
                const branchCanvas = document.getElementById('branchCostChart');
                if (branchCanvas) {
                    if (branchChartInstance) {
                        branchChartInstance.destroy();
                        branchChartInstance = null;
                    }
                    branchChartInstance = new Chart(branchCanvas, {
                        type: 'bar',
                        data: {
                            labels: branchBreakdown.map(b => b.branch),
                            datasets: [{
                                label: 'Total Cost (₹)',
                                data: branchBreakdown.map(b => b.total_cost),
                                backgroundColor: '#3b82f6'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }

                // Chart 4: Overtime vs Incentive Comparison
                const otVsIncCanvas = document.getElementById('otVsIncentiveChart');
                if (otVsIncCanvas) {
                    if (otVsIncentiveChartInstance) {
                        otVsIncentiveChartInstance.destroy();
                        otVsIncentiveChartInstance = null;
                    }
                    otVsIncentiveChartInstance = new Chart(otVsIncCanvas, {
                        type: 'bar',
                        data: {
                            labels: ['Overtime Cost', 'Incentive Cost'],
                            datasets: [{
                                label: 'Cost (₹)',
                                data: [totalOT, totalInc],
                                backgroundColor: ['#f59e0b', '#ec4899']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }
            }

            // Initialize on load
            document.addEventListener('DOMContentLoaded', initEmployeeCostCharts);
            
            // Re-initialize on Livewire updates
            document.addEventListener('livewire:navigated', initEmployeeCostCharts);
            document.addEventListener('livewire:load', initEmployeeCostCharts);
            
            // Also listen for Livewire morph updates (for partial updates)
            document.addEventListener('livewire:message.received', function() {
                setTimeout(initEmployeeCostCharts, 50);
            });
        })();
    </script>
</div>