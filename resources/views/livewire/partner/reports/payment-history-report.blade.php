<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3"><h4 class="fw-bold mb-0">Payment History Report</h4><p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr></div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div><h4 class="mb-1 fw-bold text-dark">Payment History Report</h4><p class="text-muted mb-0 small">View all received payments</p></div>
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
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Search by transaction ID or customer...">
                </div>
                <div class="col-md-4">
                    <div class="filter-label">Start Date</div>
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>
                <div class="col-md-4">
                    <div class="filter-label">End Date</div>
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0">
                <thead><tr>
                    <th class="ps-4">ID</th><th>Transaction ID</th><th>Customer Name</th><th>Gross Amount</th><th>Platform Fee</th><th>Net Amount</th><th>Method</th><th>Status</th><th>Date</th>
                </tr></thead>
                <tbody>
                    @forelse($reportData as $row)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ substr($row->id, 0, 8) }}</td>
                        <td>{{ $row->receipt_number ?? $row->gateway_ref ?? '-' }}</td>
                        <td class="fw-bold text-dark">
                            @if($row->booking && $row->booking->customer)
                                {{ $row->booking->customer->name }}
                            @elseif($row->subscription && $row->subscription->customer)
                                {{ $row->subscription->customer->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="fw-bold text-muted">₹{{ number_format($row->amount, 2) }}</td>
                        @php
                            $netAmt = $this->getNetAmount($row->booking_id ?? $row->subscription_id);
                            $platFee = $row->amount - $netAmt;
                        @endphp
                        <td class="fw-bold text-danger">-₹{{ number_format($platFee, 2) }}</td>
                        <td class="fw-bold text-success">₹{{ number_format($netAmt, 2) }}</td>
                        <td>{{ ucfirst($row->gateway ?? '-') }}</td>
                        <td>
                            @php $st = match($row->status){ 'completed'=>'success','pending'=>'warning',default=>'danger' }; @endphp
                            <span class="badge bg-{{ $st }} bg-opacity-10 text-{{ $st }} rounded-pill px-3">{{ ucfirst($row->status) }}</span>
                        </td>
                        <td>{{ $row->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())<div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $reportData->links() }}</div>@endif
    </div>
</div>
</div>
