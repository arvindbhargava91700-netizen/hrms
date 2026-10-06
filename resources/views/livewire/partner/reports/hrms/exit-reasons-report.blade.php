<div>
    @include('partials.report-styles')
    <div id="print-area">
        <div class="report-print-header mb-3">
            <h4 class="fw-bold mb-0">Exit Reasons Report</h4>
            <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p>
            <hr>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
            <div>
                <h4 class="mb-1 fw-bold text-dark">Exit Reasons Report</h4>
                <p class="text-muted mb-0 small">Employee exits grouped by exit reason with branch & team filters</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="report-filter-card card mb-4 d-print-none shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-funnel text-primary fs-5"></i>
                        <span class="fw-bold text-dark" style="font-size:0.95rem;">Filter Exit Reasons</span>
                    </div>
                    @if($branchId || $teamId || $filterType !== 'all' || $search)
                        <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </button>
                    @endif
                </div>
                <div class="row g-3">
                    {{-- Branch --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">Branch</label>
                        <select class="form-select border-primary-subtle" wire:model.live="branchId">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Team / Reporting Manager --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">Team / Reporting Manager</label>
                        <select class="form-select border-info-subtle" wire:model.live="teamId">
                            <option value="">All Teams / Managers</option>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }} {{ $team->employee_code ? '('.$team->employee_code.')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Exit Type --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">Exit Type</label>
                        <select class="form-select" wire:model.live="filterType">
                            <option value="all">All Types</option>
                            <option value="voluntary">Voluntary</option>
                            <option value="involuntary">Involuntary</option>
                        </select>
                    </div>

                    {{-- Search --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="filter-label">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search reason or employee name...">
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="report-stat-card card p-3 text-center">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Total Exits</div>
                    <div class="fw-bold fs-4 text-primary">{{ number_format($totalExits) }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="report-stat-card card p-3 text-center">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Voluntary</div>
                    <div class="fw-bold fs-4 text-warning">{{ number_format($voluntaryCount) }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="report-stat-card card p-3 text-center">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Involuntary</div>
                    <div class="fw-bold fs-4 text-secondary">{{ number_format($involuntaryCount) }}</div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="report-stat-card card p-3 text-center">
                    <div class="text-muted small fw-bold text-uppercase mb-1">Unique Reasons</div>
                    <div class="fw-bold fs-4 text-info">{{ count($exitReasonsBreakdown) }}</div>
                </div>
            </div>
        </div>

        {{-- Exit Reasons Breakdown --}}
        @if(count($exitReasonsBreakdown) > 0)
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 border-0">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Reasons Breakdown</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($exitReasonsBreakdown as $item)
                        <div class="col-md-4 col-sm-6">
                            <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light">
                                <div>
                                    <div class="fw-bold text-dark small">{{ $item['reason'] ?: 'Not Specified' }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $item['count'] }} exit(s)</div>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary">{{ $item['percentage'] }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-box-arrow-right text-warning me-2"></i>Employee Exit Records
                </h6>
                <div class="d-flex align-items-center gap-2 d-print-none">
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 75px;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table report-table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-4">Employee</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Exit Reason</th>
                            <th>Type</th>
                            <th>Exit Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedExits as $exit)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ optional($exit->employee)->name ?? '-' }}</div>
                                    @if(optional($exit->employee)->employee_code)
                                        <div class="small text-muted">{{ $exit->employee->employee_code }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if($exit->branch)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-geo-alt me-1"></i>{{ $exit->branch->name }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($exit->department)
                                        <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">{{ $exit->department->name }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="fw-semibold text-dark">{{ $exit->exit_reason ?: '-' }}</td>
                                <td>
                                    @if($exit->exit_type === 'voluntary')
                                        <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3">Voluntary</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-3">Involuntary</span>
                                    @endif
                                </td>
                                <td>{{ $exit->exit_date ? $exit->exit_date->format('d M Y') : '-' }}</td>
                                <td>
                                    @php $cs = match($exit->status){ 'completed'=>'success','active'=>'primary','pending'=>'warning',default=>'secondary' }; @endphp
                                    <span class="badge bg-{{ $cs }} bg-opacity-10 text-{{ $cs }} rounded-pill px-3">{{ ucfirst($exit->status) }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No exit records found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($paginatedExits->hasPages())
                <div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $paginatedExits->links() }}</div>
            @endif
        </div>
    </div>
</div>
