<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3"><h4 class="fw-bold mb-0">Task Report</h4><p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr></div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div><h4 class="mb-1 fw-bold text-dark">Task Report</h4><p class="text-muted mb-0 small">Track staff tasks and completion status</p></div>
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
                    <div class="filter-label">Employee</div>
                    <select class="form-select" wire:model.live="employeeId">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="filter-label">Status</div>
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
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
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Total Tasks</div><div class="fw-bold fs-4 text-primary">{{ $reportData->total() }}</div></div></div>
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Completed</div><div class="fw-bold fs-4 text-success">{{ $reportData->where('status','completed')->count() }}</div></div></div>
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">In Progress</div><div class="fw-bold fs-4 text-info">{{ $reportData->where('status','in_progress')->count() }}</div></div></div>
        <div class="col-md-3 col-6"><div class="report-stat-card card p-3 text-center"><div class="text-muted small fw-bold text-uppercase mb-1">Pending</div><div class="fw-bold fs-4 text-warning">{{ $reportData->where('status','pending')->count() }}</div></div></div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0">
                <thead><tr>
                    <th class="ps-4">Task</th><th>Employee</th><th>Priority</th><th>Due Date</th><th>Status</th>
                </tr></thead>
                <tbody>
                    @forelse($reportData as $row)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $row->title }}</div>
                            <div class="small text-muted text-truncate" style="max-width:250px;">{{ $row->description }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ optional($row->employee)->name }}</div>
                            <div class="small text-muted">{{ optional($row->employee)->employee_code ?? substr(optional($row->employee)->id,0,8) }}</div>
                        </td>
                        <td>
                            @php $p = match($row->priority){ 'high'=>'danger','medium'=>'warning',default=>'info' }; @endphp
                            <span class="badge bg-{{ $p }} bg-opacity-10 text-{{ $p }} px-2 py-1 rounded">{{ ucfirst($row->priority) }}</span>
                        </td>
                        <td class="text-muted">{{ $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d M Y') : '-' }}</td>
                        <td>
                            @php $sc = match($row->status){ 'completed'=>'success','cancelled'=>'danger','in_progress'=>'info',default=>'warning' }; @endphp
                            <span class="badge bg-{{ $sc }} bg-opacity-10 text-{{ $sc }} rounded-pill px-3">{{ ucfirst(str_replace('_',' ',$row->status)) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">No tasks found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())<div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $reportData->links() }}</div>@endif
    </div>
</div>
</div>
