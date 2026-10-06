<div>
@include('partials.report-styles')

<div id="print-area">

    {{-- Print Header --}}
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Payroll Report</h4>
        <p class="text-muted small mb-0">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        <hr>
    </div>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Payroll Report</h4>
            <p class="text-muted mb-0 small">View and export your team's payroll details, branch, and team hierarchy</p>
        </div>
        <div class="report-actions d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="exportCsv" wire:loading.attr="disabled">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="report-filter-card card mb-4 d-print-none shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel text-primary fs-5"></i>
                    <span class="fw-bold text-dark" style="font-size:0.95rem;">Filter Payroll</span>
                </div>
                @if($branchId || $teamId || $departmentId || $roleName || $status || $search)
                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                @endif
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
                    <select class="form-select" wire:model.live="departmentId">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Role --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Role</label>
                    <select class="form-select" wire:model.live="roleName">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            @php $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id; @endphp
                            <option value="{{ $role->name }}">{{ str_replace([$partnerId . '_', '_' . $partnerId], '', $role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 5. Status --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Status</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                {{-- 6. Search --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Search Staff</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" wire:model.live.debounce.300ms="search" placeholder="Name, code, or email...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Payrolls</div>
                <div class="fw-bold fs-4 text-primary">{{ $reportData->total() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Paid</div>
                <div class="fw-bold fs-4 text-success">{{ $reportData->where('status', 'paid')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Pending</div>
                <div class="fw-bold fs-4 text-warning">{{ $reportData->where('status', 'pending')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Net Pay</div>
                <div class="fw-bold fs-4 text-dark">₹{{ number_format($reportData->sum('net_pay'), 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Employee Name</th>
                        <th>Branch</th>
                        <th>Team / Reporting To</th>
                        <th>Department</th>
                        <th>Month/Year</th>
                        <th>Presents</th>
                        <th>Absents</th>
                        <th>Leaves</th>
                        <th>Expenses</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $i => $row)
                    <tr>
                        @php $stats = $this->getStats($row->employee_id, $row->month, $row->year); @endphp
                        <td class="ps-4 text-muted small">{{ $row->id }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ optional($row->employee)->name ?? "-" }}</div>
                            @if(optional($row->employee)->employee_code)
                                <div class="small text-muted">{{ $row->employee->employee_code }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                <i class="bi bi-geo-alt me-1"></i>{{ optional(optional($row->employee)->branch)->name ?? 'Main Branch' }}
                            </span>
                        </td>
                        <td>
                            @if(optional($row->employee)->reportingTo)
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-person-badge text-info"></i>
                                    <span class="fw-semibold text-dark">{{ $row->employee->reportingTo->name }}</span>
                                </div>
                            @else
                                <span class="text-muted small fst-italic">Direct / None</span>
                            @endif
                        </td>
                        <td>
                            @if(optional($row->employee)->department)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">{{ $row->employee->department->name }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>{{ $row->month }} / {{ $row->year }}</td>
                        <td class="text-success fw-bold">{{ $stats['presents'] }} <span class="text-muted fw-normal small">days</span></td>
                        <td class="text-danger fw-bold">{{ $stats['absents'] }} <span class="text-muted fw-normal small">days</span></td>
                        <td class="text-warning fw-bold">{{ $stats['leaves'] }} <span class="text-muted fw-normal small">days</span></td>
                        <td>₹{{ number_format($stats['expenses'], 2) }}</td>
                        <td class="fw-bold text-dark">₹{{ number_format($row->net_pay, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $row->status=='paid'?'success':'warning' }} bg-opacity-10 text-{{ $row->status=='paid'?'success':'warning' }} rounded-pill px-3">
                                {{ ucfirst($row->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="12" class="text-center py-5 text-muted">No payroll records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())
        <div class="card-footer border-top bg-transparent p-4 d-print-none">
            {{ $reportData->links() }}
        </div>
        @endif
    </div>
</div>
</div>

