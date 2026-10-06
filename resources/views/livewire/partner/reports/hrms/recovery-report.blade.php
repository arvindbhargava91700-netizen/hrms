<div>
@include('partials.report-styles')

<div id="print-area">

    {{-- Print Header --}}
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Recovery Report</h4>
        <p class="text-muted small mb-0">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        <hr>
    </div>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Recovery Report</h4>
            <p class="text-muted mb-0 small">View and export your team's recovery details, branch, and team hierarchy</p>
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
                    <span class="fw-bold text-dark" style="font-size:0.95rem;">Filters</span>
                </div>
                @if($branchId || $teamId || $employeeId || $departmentId || $roleName || $paymentStatus || $search)
                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                @endif
            </div>
            <div class="row g-3">
                {{-- 1. Branch --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Branch</label>
                    <select class="form-select border-primary-subtle" wire:model.live="branchId">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Team / Reporting Manager --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Team / Reporting Manager</label>
                    <select class="form-select border-info-subtle" wire:model.live="teamId">
                        <option value="">All Teams / Reporting Managers</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }} {{ $team->employee_code ? '('.$team->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Employee --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Employee</label>
                    <select class="form-select" wire:model.live="employeeId">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} {{ $emp->employee_code ? '('.$emp->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Search --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Search</label>
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Lead name, mobile, employee…">
                </div>

                {{-- 5. Department --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Department</label>
                    <select class="form-select" wire:model.live="departmentId">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 6. Role --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Role</label>
                    <select class="form-select" wire:model.live="roleName">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            @php $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id; @endphp
                            <option value="{{ $role->name }}">{{ str_replace($partnerId . '_', '', $role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 7. Payment Status --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Payment Status</label>
                    <select class="form-select" wire:model.live="paymentStatus">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Orders</div>
                <div class="fw-bold fs-4 text-primary">{{ $reportData->total() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Amount</div>
                <div class="fw-bold fs-4 text-dark">₹{{ number_format($reportData->sum('total_amount'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Paid</div>
                <div class="fw-bold fs-4 text-success">₹{{ number_format($reportData->sum('paid_amount'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Remaining</div>
                <div class="fw-bold fs-4 text-danger">₹{{ number_format($reportData->sum('remaining_balance'), 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">Order ID</th>
                        <th>Lead / Customer</th>
                        <th>Assigned Employee</th>
                        <th>Branch</th>
                        <th>Team / Manager</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-end">Remaining Balance</th>
                        <th class="text-center">Payment Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $i => $row)
                    @php
                        $emp = $row->employee ?? optional($row->lead)->assignedTo;
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted small fw-bold">#{{ $row->id ? substr($row->id, 0, 8) : '-' }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ optional($row->lead)->customer_name ?? "-" }}</div>
                            @if(optional($row->lead)->customer_mobile)
                                <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $row->lead->customer_mobile }}</div>
                            @endif
                        </td>
                        <td>
                            @if($emp)
                                <div class="fw-semibold text-dark">{{ $emp->name }}</div>
                                <div class="text-muted small">{{ $emp->employee_code ?? '' }}</div>
                            @else
                                <span class="text-muted fst-italic">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-geo-alt text-danger me-1"></i>
                                {{ optional(optional($emp)->branch)->name ?: ($emp ? 'Main Branch' : '-') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-people text-primary me-1"></i>
                                {{ optional(optional($emp)->reportingTo)->name ?: ($emp ? 'Direct / None' : '-') }}
                            </span>
                        </td>
                        <td class="text-end fw-semibold">₹{{ number_format($row->total_amount, 2) }}</td>
                        <td class="text-end text-success fw-semibold">₹{{ number_format($row->paid_amount, 2) }}</td>
                        <td class="text-end text-danger fw-bold">₹{{ number_format($row->remaining_balance, 2) }}</td>
                        <td class="text-center">
                            @php
                                $statusBadgeClass = match($row->payment_status) {
                                    'paid' => 'bg-success text-success',
                                    'partial' => 'bg-warning text-warning',
                                    default => 'bg-danger text-danger',
                                };
                            @endphp
                            <span class="badge {{ $statusBadgeClass }} bg-opacity-10 rounded-pill px-3">
                                {{ ucfirst($row->payment_status ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            No recovery records found matching the filters.
                        </td>
                    </tr>
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
