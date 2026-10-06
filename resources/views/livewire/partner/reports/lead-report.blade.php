<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3"><h4 class="fw-bold mb-0">Lead Report</h4><p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr></div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div><h4 class="mb-1 fw-bold text-dark">Lead Report</h4><p class="text-muted mb-0 small">Track all lead activities and conversion rates</p></div>
        <div class="report-actions d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="exportCsv"><i class="bi bi-filetype-csv me-1"></i> Export CSV</button>
            <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
        </div>
    </div>

    <div class="report-filter-card card mb-4 d-print-none">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3 gap-2"><i class="bi bi-funnel text-primary"></i><span class="fw-bold text-dark" style="font-size:0.9rem;">Filters</span></div>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="filter-label">Assigned Employee</div>
                    <select class="form-select" wire:model.live="employeeId">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="filter-label">Lead Status</div>
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="qualified">Qualified</option>
                        <option value="proposal">Proposal</option>
                        <option value="won">Won</option>
                        <option value="lost">Lost</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="filter-label">From Date</div>
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>
                <div class="col-md-3">
                    <div class="filter-label">To Date</div>
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Total Leads</div><div class="fw-bold fs-4 text-primary">{{ $reportData->total() }}</div></div></div>
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Won</div><div class="fw-bold fs-4 text-success">{{ $reportData->where('status','won')->count() }}</div></div></div>
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Lost</div><div class="fw-bold fs-4 text-danger">{{ $reportData->where('status','lost')->count() }}</div></div></div>
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Open</div><div class="fw-bold fs-4 text-warning">{{ $reportData->whereNotIn('status',['won','lost'])->count() }}</div></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0">
                <thead><tr>
                    <th class="ps-4">Lead Name</th><th>Mobile</th><th>Assigned To</th><th>Status</th><th>Date</th>
                </tr></thead>
                <tbody>
                    @forelse($reportData as $row)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $row->customer_name ?? '-' }}</td>
                        <td class="text-muted">{{ $row->customer_mobile ?? '-' }}</td>
                        <td>{{ optional($row->assignedTo)->name ?? 'Unassigned' }}</td>
                        <td>
                            @php $sc = match($row->status){ 'won'=>'success','lost'=>'danger','new'=>'info','qualified'=>'primary',default=>'secondary' }; @endphp
                            <span class="badge bg-{{ $sc }} bg-opacity-10 text-{{ $sc }} rounded-pill px-3">{{ ucfirst($row->status) }}</span>
                        </td>
                        <td class="text-muted">{{ $row->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">No leads found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())<div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $reportData->links() }}</div>@endif
    </div>
</div>
</div>
