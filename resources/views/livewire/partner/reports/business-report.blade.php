<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3"><h4 class="fw-bold mb-0">Business Report</h4><p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr></div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div><h4 class="mb-1 fw-bold text-dark">Business Report</h4><p class="text-muted mb-0 small">View business and revenue by listing</p></div>
        <div class="report-actions d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="exportCsv"><i class="bi bi-filetype-csv me-1"></i> Export CSV</button>
            <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
        </div>
    </div>

    <div class="report-filter-card card mb-4 d-print-none">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3 gap-2"><i class="bi bi-funnel text-primary"></i><span class="fw-bold text-dark" style="font-size:0.9rem;">Filters</span></div>
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="filter-label">Search</div>
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Search listing or city...">
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4 d-print-none">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Listings</h6>
                <h3 class="text-primary fw-bold mb-0">{{ $records->total() }}</h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Bookings</h6>
                <h3 class="text-success fw-bold mb-0">{{ number_format($stats['totalBookings']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Active Subscriptions</h6>
                <h3 class="text-info fw-bold mb-0">{{ number_format($stats['activeSubscriptions']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Revenue (Gross)</h6>
                <h3 class="text-warning fw-bold mb-0">₹{{ number_format($stats['totalRevenue'], 2) }}</h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Net Earn</h6>
                <h3 class="text-success fw-bold mb-0">₹{{ number_format($stats['netEarn'], 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0">
                <thead><tr>
                    <th class="ps-4">Listing</th><th class="text-center">Total Bookings</th><th class="text-center">Active Subs</th><th class="text-center">Total Revenue (Gross)</th><th class="text-center">Net Earn</th>
                </tr></thead>
                <tbody>
                    @forelse($records as $row)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">{{ $row->title }}</div>
                            <small class="text-muted">{{ $row->category->name ?? 'N/A' }} &bull; {{ $row->city ?? 'N/A' }}</small>
                        </td>
                        <td class="text-center fw-semibold text-success">{{ number_format($row->total_bookings) }}</td>
                        <td class="text-center fw-semibold text-info">{{ number_format($row->active_subscriptions) }}</td>
                        <td class="text-center fw-bold text-warning">₹{{ number_format($row->total_revenue, 2) }}</td>
                        <td class="text-center fw-bold text-success">₹{{ number_format($row->net_earn, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-5 text-muted">No business data found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())<div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $records->links() }}</div>@endif
    </div>
</div>
</div>
