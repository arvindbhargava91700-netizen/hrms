<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Expense Report</h4>
        <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p>
        <hr>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Expense Report</h4>
            <p class="text-muted mb-0 small">Track all employee expense claims, branch, team hierarchy, and reimbursements</p>
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
                    <span class="fw-bold text-dark" style="font-size:0.95rem;">Filter Expenses</span>
                </div>
                @if($branchId || $teamId || $employeeId || $statusFilter)
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

                {{-- 3. Employee --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label"> Employee</label>
                    <select class="form-select" wire:model.live="employeeId">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} {{ $emp->employee_code ? '('.$emp->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Status --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Status</label>
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                {{-- 5. From Date --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">From Date</label>
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>

                {{-- 6. To Date --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">To Date</label>
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Total Records</div><div class="fw-bold fs-4 text-primary">{{ $reportData->total() }}</div></div></div>
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Total Amount</div><div class="fw-bold fs-4 text-dark">₹{{ number_format($reportData->sum('amount'),0) }}</div></div></div>
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Approved</div><div class="fw-bold fs-4 text-success">{{ $reportData->where('status','approved')->count() }}</div></div></div>
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Pending</div><div class="fw-bold fs-4 text-warning">{{ $reportData->where('status','pending')->count() }}</div></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Employee</th>
                        <th>Branch</th>
                        <th>Team / Reporting To</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $row)
                    <tr>
                        <td class="ps-4 fw-bold">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ optional($row->employee)->name }}</div>
                            <div class="small text-muted">{{ optional($row->employee)->employee_code ?? substr(optional($row->employee)->id,0,8) }}</div>
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
                        <td><span class="badge bg-info bg-opacity-10 text-info rounded-pill px-3">{{ $row->category }}</span></td>
                        <td class="text-muted">{{ Str::limit($row->description,40) }}</td>
                        <td class="fw-bold text-dark">₹{{ number_format($row->amount,2) }}</td>
                        <td>
                            @php $sc = match($row->status){ 'approved'=>'success','rejected'=>'danger',default=>'warning' }; @endphp
                            <span class="badge bg-{{ $sc }} bg-opacity-10 text-{{ $sc }} rounded-pill px-3">{{ ucfirst($row->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">No expense records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())
        <div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $reportData->links() }}</div>
        @endif
    </div>
</div>
</div>
