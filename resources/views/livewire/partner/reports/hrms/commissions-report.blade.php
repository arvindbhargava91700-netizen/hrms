<div>
@include('partials.report-styles')

<div id="print-area">

    {{-- Print Header --}}
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Commissions Report</h4>
        <p class="text-muted small mb-0">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        <hr>
    </div>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Commissions Report</h4>
            <p class="text-muted mb-0 small">View and export your team's commissions details</p>
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
                    <div class="filter-label">Branch</div>
                    <select class="form-select" wire:model.live="branchId">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="filter-label">Team / Reporting Manager</div>
                    <select class="form-select" wire:model.live="teamId">
                        <option value="">All Teams / Managers</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
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
                            @php $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id; @endphp
                            <option value="{{ $role->name }}">{{ str_replace($partnerId . '_', '', $role->name) }}</option>
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
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Target</div>
                <div class="fw-bold fs-4 text-dark">₹{{ number_format($reportData->sum('target_amount'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Commission</div>
                <div class="fw-bold fs-4 text-success">₹{{ number_format($reportData->sum('commission_earned') + $reportData->sum('recovery_earned'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Net Payout</div>
                <div class="fw-bold fs-4 text-primary">₹{{ number_format($reportData->sum('net_payout'), 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee Name</th>
                        <th>Month/Year</th>
                        <th>Target</th>
                        <th>Commission Earned</th>
                        <th>Recovery Earned</th>
                        <th>Net Payout</th>
                        <th>Status</th>
                        
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $i => $row)
                    <tr>
                        
                        <td class="ps-4 text-muted small">{{ $row->id }}</td>
                        <td class="fw-bold">{{ optional($row->employee)->name ?? "-" }}</td>
                        <td>{{ $row->month }} / {{ $row->year }}</td>
                        <td>₹{{ number_format($row->target_amount, 2) }}</td>
                        <td class="text-success">₹{{ number_format($row->commission_earned, 2) }}</td>
                        <td class="text-success">₹{{ number_format($row->recovery_earned, 2) }}</td>
                        <td class="fw-bold">₹{{ number_format($row->net_payout, 2) }}</td>
                        <td>
                            <span class="badge bg-{{ $row->status=='paid'?'success':'warning' }} bg-opacity-10 text-{{ $row->status=='paid'?'success':'warning' }} rounded-pill px-3">
                                {{ ucfirst($row->status) }}
                            </span>
                        </td>
    
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">No commissions records found.</td></tr>
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

