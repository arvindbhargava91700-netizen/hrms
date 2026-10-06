<div>
@include('partials.report-styles')

<div id="print-area">

    {{-- Print Header --}}
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Salary Management Report</h4>
        <p class="text-muted small mb-0">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        <hr>
    </div>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Salary Management Report</h4>
            <p class="text-muted mb-0 small">View and export your staff salary breakdowns across branches and teams</p>
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
                @if($branchId || $teamId || $employeeId || $departmentId || $roleName || $status || $month || $year || $search)
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
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Name, code, email…">
                </div>

                {{-- 5. Department --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Department</label>
                    <select class="form-select" wire:model.live="departmentId">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 6. Role --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Role</label>
                    <select class="form-select" wire:model.live="roleName">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            @php $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id; @endphp
                            <option value="{{ $role->name }}">{{ str_replace($partnerId . '_', '', $role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 7. Month --}}
                <div class="col-md-2 col-sm-6">
                    <label class="filter-label">Month</label>
                    <select class="form-select" wire:model.live="month">
                        <option value="">All Months</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}</option>
                        @endfor
                    </select>
                </div>

                {{-- 8. Year --}}
                <div class="col-md-2 col-sm-6">
                    <label class="filter-label">Year</label>
                    <select class="form-select" wire:model.live="year">
                        <option value="">All Years</option>
                        @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                {{-- 9. Status --}}
                <div class="col-md-2 col-sm-6">
                    <label class="filter-label">Status</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Records</div>
                <div class="fw-bold fs-4 text-primary">{{ $reportData->total() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Gross Pay</div>
                <div class="fw-bold fs-4 text-dark">₹{{ number_format($reportData->sum('gross_pay'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Deductions</div>
                <div class="fw-bold fs-4 text-danger">₹{{ number_format($reportData->sum('deductions'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Net Pay</div>
                <div class="fw-bold fs-4 text-success">₹{{ number_format($reportData->sum('net_pay'), 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Employee</th>
                        <th>Branch</th>
                        <th>Team / Manager</th>
                        <th>Month / Year</th>
                        <th class="text-end">Basic Salary</th>
                        <th class="text-end">Allowances</th>
                        <th class="text-end">Bonuses</th>
                        <th class="text-end">Commissions</th>
                        <th class="text-end">Gross Pay</th>
                        <th class="text-end">Deductions</th>
                        <th class="text-end">Net Pay</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $i => $row)
                    @php
                        $emp = $row->employee;
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted small fw-bold">#{{ $row->id }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ optional($emp)->name ?? "-" }}</div>
                            <div class="text-muted small">{{ optional($emp)->employee_code ?? '' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-geo-alt text-danger me-1"></i>
                                {{ optional(optional($emp)->branch)->name ?: 'Main Branch' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-people text-primary me-1"></i>
                                {{ optional(optional($emp)->reportingTo)->name ?: 'Direct / None' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border">
                                {{ \Carbon\Carbon::create(null, $row->month, 1)->format('M') }} {{ $row->year }}
                            </span>
                        </td>
                        <td class="text-end">₹{{ number_format($row->basic_salary, 2) }}</td>
                        <td class="text-end text-muted">₹{{ number_format($this->getAllowances($row->allowances_breakdown), 2) }}</td>
                        <td class="text-end text-muted">₹{{ number_format($row->bonuses, 2) }}</td>
                        <td class="text-end text-muted">₹{{ number_format($row->commissions, 2) }}</td>
                        <td class="text-end fw-bold">₹{{ number_format($row->gross_pay, 2) }}</td>
                        <td class="text-end text-danger fw-semibold">₹{{ number_format($row->deductions, 2) }}</td>
                        <td class="text-end fw-bold text-success">₹{{ number_format($row->net_pay, 2) }}</td>
                        <td class="text-center">
                            @php
                                $statusBadge = match($row->status) {
                                    'paid' => 'bg-success text-success',
                                    'partial' => 'bg-warning text-warning',
                                    default => 'bg-danger text-danger',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} bg-opacity-10 rounded-pill px-3 py-1">
                                {{ ucfirst($row->status ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            No salary management records found matching the filters.
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
