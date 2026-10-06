<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3"><h4 class="fw-bold mb-0">Booking Report</h4><p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr></div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div><h4 class="mb-1 fw-bold text-dark">Booking Report</h4><p class="text-muted mb-0 small">View booking performance</p></div>
        <div class="report-actions d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="exportCsv"><i class="bi bi-filetype-csv me-1"></i> Export CSV</button>
            <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
        </div>
    </div>

    <div class="report-filter-card card mb-4 d-print-none">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3 gap-2"><i class="bi bi-funnel text-primary"></i><span class="fw-bold text-dark" style="font-size:0.9rem;">Filters</span></div>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="filter-label">Search</div>
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Search by booking ID or customer...">
                </div>
                <div class="col-md-4">
                    <div class="filter-label">Start Date</div>
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>
                <div class="col-md-4">
                    <div class="filter-label">End Date</div>
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
                <div class="col-md-4">
                    <div class="filter-label">Status</div>
                    <select class="form-select" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0">
                <thead><tr>
                    <th class="ps-4">Booking ID</th><th>Customer</th><th>Listing & Room</th><th>Package / Shift</th><th>Amount</th><th>Status</th><th>Date</th>
                </tr></thead>
                <tbody>
                    @forelse($reportData as $row)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ $row->booking_id ?? substr($row->id, 0, 8) }}</td>
                        <td class="fw-bold text-dark">{{ optional($row->customer)->name ?? '-' }}<br><small class="text-muted">{{ optional($row->customer)->mobile ?? '' }}</small></td>
                        <td>
                            <span class="fw-bold">{{ optional($row->listing)->title ?? '-' }}</span><br>
                            @if($row->room)<small class="text-muted">Room: {{ $row->room->room_number }}</small>@endif
                        </td>
                        <td>
                            @if($row->package) <span class="badge bg-light text-dark border">Pkg: {{ $row->package->name }}</span><br> @endif
                            @if($row->shift) <span class="badge bg-light text-dark border">Shift: {{ $row->shift->name }}</span><br> @endif
                            <small class="text-muted">{{ ucfirst($row->occupancy_type ?? '') }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-success" title="Gross Amount">₹{{ number_format($row->final_amount ?? $row->amount,2) }}</div>
                            <div class="small fw-semibold text-primary mt-1" title="Net Earning">Net: ₹{{ number_format($row->net_earn, 2) }}</div>
                        </td>
                        <td>
                            @php $st = match($row->status){ 'confirmed'=>'success','completed'=>'primary','pending'=>'warning',default=>'danger' }; @endphp
                            <span class="badge bg-{{ $st }} bg-opacity-10 text-{{ $st }} rounded-pill px-3">{{ ucfirst($row->status) }}</span>
                        </td>
                        <td>{{ $row->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())<div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $reportData->links() }}</div>@endif
    </div>
</div>
</div>
