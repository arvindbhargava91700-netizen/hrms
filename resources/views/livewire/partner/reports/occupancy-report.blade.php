<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3"><h4 class="fw-bold mb-0">Occupancy Report</h4><p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr></div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div><h4 class="mb-1 fw-bold text-dark">Occupancy Report</h4><p class="text-muted mb-0 small">View occupancy and fillup for your listings</p></div>
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
                <div class="col-md-6">
                    <div class="filter-label">Category</div>
                    <select class="form-select" wire:model.live="categoryId">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4 d-print-none">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Capacity</h6>
                <h3 class="text-primary fw-bold mb-0">{{ number_format($stats['total_capacity']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Occupied Beds / Seats</h6>
                <h3 class="text-success fw-bold mb-0">{{ number_format($stats['total_occupied']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Available Beds / Seats</h6>
                <h3 class="text-info fw-bold mb-0">{{ number_format($stats['total_available']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Occupancy Rate</h6>
                <h3 class="text-warning fw-bold mb-0">{{ $stats['occupancy_rate'] }}%</h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0">
                <thead><tr>
                    <th class="ps-4">Listing</th><th>Category</th><th class="text-center">Total Capacity</th><th class="text-center">Occupied Beds / Seats</th><th class="text-center">Available Beds</th><th class="text-center">Occupancy (%)</th>
                </tr></thead>
                <tbody>
                    @forelse($records as $row)
                    @php
                        $roomCapacity = (int) $row->rooms_sum_capacity;
                        $roomAvailable = (int) $row->rooms_sum_available_beds;
                        $roomOccupied = $roomCapacity - $roomAvailable;
                        
                        $shiftCapacity = (int) $row->shifts_sum_max_members;
                        $shiftOccupied = (int) $row->shifts_enrolled_sum;
                        $shiftAvailable = max(0, $shiftCapacity - $shiftOccupied);
                        
                        $capacity = $roomCapacity + $shiftCapacity;
                        $occupied = $roomOccupied + $shiftOccupied;
                        $available = $roomAvailable + $shiftAvailable;
                        $occupancyPercentage = $capacity > 0 ? round(($occupied / $capacity) * 100, 2) : 0;
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold text-dark">{{ $row->title }}<br><small class="text-muted">{{ $row->city ?? '-' }}</small></td>
                        <td>{{ optional($row->category)->name ?? '-' }}</td>
                        <td class="text-center fw-semibold text-primary">{{ $capacity }}</td>
                        <td class="text-center fw-semibold text-success">{{ $occupied }}</td>
                        <td class="text-center fw-semibold text-info">{{ $available }}</td>
                        <td class="text-center fw-bold">
                            <span class="badge bg-{{ $occupancyPercentage >= 80 ? 'success' : ($occupancyPercentage >= 50 ? 'warning' : 'danger') }} bg-opacity-10 text-{{ $occupancyPercentage >= 80 ? 'success' : ($occupancyPercentage >= 50 ? 'warning' : 'danger') }} rounded-pill px-3">
                                {{ $occupancyPercentage }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-5 text-muted">No listings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())<div class="card-footer border-top bg-transparent p-4 d-print-none">{{ $records->links() }}</div>@endif
    </div>
</div>
</div>
