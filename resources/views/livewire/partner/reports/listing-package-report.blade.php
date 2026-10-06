<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3"><h4 class="fw-bold mb-0">Listing Packages Report</h4><p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr></div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div><h4 class="mb-1 fw-bold text-dark">Listing Packages Report</h4><p class="text-muted mb-0 small">View performance of your listing packages</p></div>
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
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Search package name or room type...">
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

    <!-- Stats -->
    <div class="row g-3 mb-4 d-print-none">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Packages</h6>
                <h3 class="text-primary fw-bold mb-0">{{ number_format($stats['total_packages']) }}</h3>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Subscriptions (Bookings)</h6>
                <h3 class="text-info fw-bold mb-0">{{ number_format($stats['total_subscriptions']) }}</h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0">
                <thead><tr>
                    <th class="ps-4">ID</th><th>Package & Listing</th><th>Room & Details</th><th>Price</th><th>Type</th><th class="text-center">Bookings</th><th>Date</th>
                </tr></thead>
                <tbody>
                    @forelse($reportData as $row)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ substr($row->id, 0, 8) }}</td>
                        <td><span class="fw-bold text-dark">{{ $row->name }}</span><br><small class="text-muted">{{ optional($row->listing)->title ?? '-' }}</small></td>
                        <td>
                            @if($row->room)
                                <span class="fw-semibold text-dark">Room {{ $row->room->room_number }}</span> <small class="text-muted">({{ $row->room->room_type }})</small><br>
                            @elseif($row->room_type)
                                <span class="fw-semibold text-dark">{{ ucfirst(str_replace('_', ' ', $row->room_type)) }}</span><br>
                            @else
                                <span class="fw-semibold text-dark">Standard</span><br>
                            @endif
                            <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $row->occupancy_type)) }} - {{ $row->duration_days ? $row->duration_days . ' days' : 'Custom' }}</small>
                        </td>
                        <td class="fw-bold text-success">₹{{ number_format($row->price,2) }}</td>
                        <td>{{ ucfirst($row->type) }}</td>
                        <td class="text-center fw-semibold text-info">{{ $row->subscriptions_count }}</td>
                        <td>{{ $row->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">No listing packages found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())<div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $reportData->links() }}</div>@endif
    </div>
</div>
</div>
