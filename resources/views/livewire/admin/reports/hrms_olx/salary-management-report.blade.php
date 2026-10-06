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
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Salary Management Report</h4>
            <p class="text-muted mb-0 small">View and export your team's salary-management details</p>
        </div>
        <div class="report-actions d-flex gap-2">
            
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="report-filter-card card mb-4 d-print-none">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3 gap-2">
                <i class="bi bi-funnel text-primary"></i>
                <span class="fw-bold text-dark" style="font-size:0.9rem;">Filters</span>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="filter-label">Search</div>
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Name or email…">
                </div>
                <div class="col-md-3">
                    <div class="filter-label">Partner</div>
                    <select class="form-select" wire:model.live="partnerFilter">
                        <option value="">All Partners</option>
                        @foreach(\App\Models\User::where('role', 'partner')->get() as $partner)
                            <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="filter-label">Department</div>
                    <select class="form-select" wire:model.live="departmentId">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="filter-label">Role</div>
                    <select class="form-select" wire:model.live="roleName">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            
                            <option value="{{ $role->name }}">{{ preg_replace("/^[0-9]+_/", "", $role->name) }}</option>
                        @endforeach
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
            <table class="table report-table mb-0">
                <thead>
                    <tr>
                        <th>Partner</th>
                                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>Month/Year</th>
                        <th>Basic Salary</th>
                        <th>Allowances</th>
                        <th>Bonuses</th>
                        <th>Commissions</th>
                        <th>Gross Pay</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $i => $row)
                    <tr>
                        <td class="ps-4 text-muted small">{{ $row->id }}</td>
                        <td class="fw-bold">{{ optional($row->employee)->name ?? "-" }}</td>
                        <td>{{ $row->month }} / {{ $row->year }}</td>
                        <td>₹{{ number_format($row->basic_salary, 2) }}</td>
                        <td>₹{{ number_format($this->getAllowances($row->allowances_breakdown), 2) }}</td>
                        <td>₹{{ number_format($row->bonuses, 2) }}</td>
                        <td>₹{{ number_format($row->commissions, 2) }}</td>
                        <td class="fw-bold">₹{{ number_format($row->gross_pay, 2) }}</td>
                        <td class="text-danger">₹{{ number_format($row->deductions, 2) }}</td>
                        <td class="fw-bold text-success">₹{{ number_format($row->net_pay, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $row->status=='paid'?'success':'warning' }} bg-opacity-10 text-{{ $row->status=='paid'?'success':'warning' }} rounded-pill px-3">
                                {{ ucfirst($row->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">No salary-management records found.</td></tr>
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

