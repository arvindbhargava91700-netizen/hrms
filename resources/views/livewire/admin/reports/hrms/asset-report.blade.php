<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">HRMS Asset Inventory Report</h4>
            <p class="text-muted small mb-0">Overview of registered company assets, status distribution, and category metrics.</p>
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
                    <label class="form-label small fw-bold text-muted mb-1">Category</label>
                    <select wire:model.live="filterCategory" class="form-select form-select-sm">
                        <option value="all">All Categories</option>
                        <option value="laptop">Laptop</option>
                        <option value="mobile">Mobile</option>
                        <option value="sim">SIM Card</option>
                        <option value="id_card">ID Card</option>
                        <option value="other">Other Asset</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select wire:model.live="filterStatus" class="form-select form-select-sm">
                        <option value="all">All Statuses</option>
                        <option value="available">Available</option>
                        <option value="issued">Issued</option>
                        <option value="damaged">Damaged</option>
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
                    <label class="form-label small fw-bold text-muted mb-1">Team / Reporting Manager</label>
                    <select wire:model.live="teamId" class="form-select form-select-sm">
                        <option value="">All Teams / Managers</option>
                        @foreach($teams as $t)
                            <option value="{{ $t->id }}">{{ $t->name }} {{ $t->employee_code ? '('.$t->employee_code.')' : '' }}</option>
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
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Total Assets</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($totalAssetsCount) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-success text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Available</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($availableCount) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-danger text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Damaged</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($damagedCount) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-purple text-white h-100" style="background-color: #8b5cf6 !important;">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Total Cost Value</div>
                    <div class="fs-3 fw-bold mt-1">₹{{ number_format($totalCost, 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-pie-chart-fill text-primary me-2"></i>Asset Category Breakdown
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="assetReportCategoryChartPartner"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-bar-chart-fill text-success me-2"></i>Status Distribution by Category
                    </h6>
                </div>
                <div class="card-body">
                    <div style="height: 250px;">
                        <canvas id="assetReportStatusChartPartner"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Asset Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-journal-text text-primary me-2"></i>Asset Inventory Catalog
            </h6>
            <div class="d-flex align-items-center gap-2 d-print-none">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Search code, title, serial..." style="width: 220px;">
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
                        <th class="ps-3">Asset Code</th>
                        <th>Category</th>
                        <th>Title / Model</th>
                        <th>Identifier (Serial/IMEI/SIM)</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Condition</th>
                        <th>Cost (₹)</th>
                        <th class="pe-3 text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedAssets as $asset)
                        <tr>
                            <td class="ps-3 fw-bold text-dark">{{ $asset->asset_code }}</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    {{ strtoupper(str_replace('_', ' ', $asset->category)) }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $asset->name }}</div>
                                @if($asset->brand || $asset->model)
                                    <small class="text-muted">{{ $asset->brand }} {{ $asset->model }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $asset->serial_number ?: ($asset->imei_number ?: ($asset->mobile_number ?: ($asset->card_number ?: '-'))) }}
                            </td>
                            <td>{{ $asset->branch?->name ?? 'Main Branch' }}</td>
                            <td>{{ $asset->department?->name ?? 'N/A' }}</td>
                            <td>
                                @if($asset->condition === 'new')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">New</span>
                                @elseif($asset->condition === 'good')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Good</span>
                                @elseif($asset->condition === 'fair')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Fair</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Damaged</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-dark">
                                {{ $asset->purchase_cost ? '₹' . number_format($asset->purchase_cost, 2) : '-' }}
                            </td>
                            <td class="pe-3 text-end">
                                @if($asset->status === 'available')
                                    <span class="badge bg-success text-white">AVAILABLE</span>
                                @elseif($asset->status === 'issued')
                                    <span class="badge bg-info text-white">ISSUED</span>
                                @else
                                    <span class="badge bg-danger text-white">DAMAGED</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bi bi-box-seam fs-3 d-block mb-2"></i>
                                No assets found matching your filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedAssets->hasPages())
            <div class="card-footer bg-white py-3 border-0 d-print-none">
                {{ $paginatedAssets->links() }}
            </div>
        @endif
    </div>

    {{-- Chart.js Script --}}
    <script>
        (function() {
            let assetReportCategoryChartInstance = null;
            let assetReportStatusChartInstance = null;

            function initPartnerAssetReportCharts() {
                if (typeof Chart === 'undefined') {
                    setTimeout(initPartnerAssetReportCharts, 100);
                    return;
                }

                const categoryBreakdown = @json($categoryBreakdown);

                const canvas1 = document.getElementById('assetReportCategoryChartPartner');
                if (canvas1) {
                    if (assetReportCategoryChartInstance) {
                        assetReportCategoryChartInstance.destroy();
                        assetReportCategoryChartInstance = null;
                    }
                    assetReportCategoryChartInstance = new Chart(canvas1, {
                        type: 'doughnut',
                        data: {
                            labels: categoryBreakdown.map(c => c.label),
                            datasets: [{
                                data: categoryBreakdown.map(c => c.total),
                                backgroundColor: ['#3b82f6', '#10b981', '#06b6d4', '#8b5cf6', '#64748b']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'right' } }
                        }
                    });
                }

                const canvas2 = document.getElementById('assetReportStatusChartPartner');
                if (canvas2) {
                    if (assetReportStatusChartInstance) {
                        assetReportStatusChartInstance.destroy();
                        assetReportStatusChartInstance = null;
                    }
                    assetReportStatusChartInstance = new Chart(canvas2, {
                        type: 'bar',
                        data: {
                            labels: categoryBreakdown.map(c => c.label),
                            datasets: [
                                {
                                    label: 'Available',
                                    data: categoryBreakdown.map(c => c.available),
                                    backgroundColor: '#10b981',
                                },
                                {
                                    label: 'Issued',
                                    data: categoryBreakdown.map(c => c.issued),
                                    backgroundColor: '#3b82f6',
                                },
                                {
                                    label: 'Damaged',
                                    data: categoryBreakdown.map(c => c.damaged),
                                    backgroundColor: '#ef4444',
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
            }

            setTimeout(initPartnerAssetReportCharts, 50);

            document.addEventListener('DOMContentLoaded', initPartnerAssetReportCharts);
            document.addEventListener('livewire:navigated', initPartnerAssetReportCharts);

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
                    succeed(() => {
                        setTimeout(initPartnerAssetReportCharts, 100);
                    });
                });
            }
        })();
    </script>
</div>
