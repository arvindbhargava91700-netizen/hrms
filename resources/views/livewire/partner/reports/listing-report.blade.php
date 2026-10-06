<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3"><h4 class="fw-bold mb-0">Listing Report</h4><p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr></div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div><h4 class="mb-1 fw-bold text-dark">Listing Report</h4><p class="text-muted mb-0 small">View your listings performance</p></div>
        <div class="report-actions d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="exportCsv"><i class="bi bi-filetype-csv me-1"></i> Export CSV</button>
            <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-1"></i> Print</button>
        </div>
    </div>

    <div class="report-filter-card card mb-4 d-print-none">
        <div class="card-body">
            <div class="d-flex align-items-center mb-3 gap-2"><i class="bi bi-funnel text-primary"></i><span class="fw-bold text-dark" style="font-size:0.9rem;">Filters</span></div>
            <div class="row g-3">
                <div class="col-md-2">
                    <div class="filter-label">Search</div>
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Search title or city...">
                </div>
                <div class="col-md-3">
                    <div class="filter-label">Category</div>
                    <select class="form-select" wire:model.live="category_id">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="filter-label">Start Date</div>
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>
                <div class="col-md-2">
                    <div class="filter-label">End Date</div>
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
                <div class="col-md-3">
                    <div class="filter-label">Status</div>
                    <select class="form-select" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4 d-print-none">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Listings</h6>
                <h3 class="text-primary fw-bold mb-0">{{ number_format($stats['total_listings']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Active Listings</h6>
                <h3 class="text-success fw-bold mb-0">{{ number_format($stats['active_listings']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Bookings</h6>
                <h3 class="text-info fw-bold mb-0">{{ number_format($stats['total_bookings']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Visits</h6>
                <h3 class="text-warning fw-bold mb-0">{{ number_format($stats['total_visits']) }}</h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0">
                <thead><tr>
                    <th class="ps-4">ID</th><th>Title</th><th>Category</th><th>Location & Contact</th><th class="text-center">Bookings</th><th class="text-center">Visits</th><th class="text-center">Reviews</th><th>Status</th><th>Created At</th>
                </tr></thead>
                <tbody>
                    @forelse($reportData as $row)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ substr($row->id, 0, 8) }}</td>
                        <td class="fw-bold text-dark">{{ $row->title }}<br><small class="text-muted">{{ $row->gender_type ?? '' }}</small></td>
                        <td>{{ optional($row->category)->name ?? '-' }}</td>
                        <td>{{ $row->city ?? '-' }}<br><small class="text-muted">{{ $row->phone ?? '-' }}</small></td>
                        <td class="text-center fw-semibold text-info">{{ $row->subscriptions_count }}</td>
                        <td class="text-center fw-semibold text-warning">{{ $row->visit_bookings_count }}</td>
                        <td class="text-center fw-semibold text-secondary">{{ $row->reviews_count }}</td>
                        <td>
                            @php
                                $badgeClass = match(strtolower($row->status)) {
                                    'approved' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger',
                                    'inactive' => 'secondary',
                                    default => 'primary'
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeClass }} bg-opacity-10 text-{{ $badgeClass }} rounded-pill px-3">{{ ucfirst($row->status) }}</span>
                        </td>
                        <td>{{ $row->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">No listings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())<div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $reportData->links() }}</div>@endif
    </div>
</div>
</div>
