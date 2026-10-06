<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-1 text-dark fw-bold">Visit Request Report</h5>
            <p class="text-muted small mb-0">View all customer visit requests for your listings</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="exportCsv" class="btn btn-outline-success btn-sm px-3 rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </button>
            <button onclick="window.print()" class="btn btn-outline-secondary btn-sm px-3 rounded-3 shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h6 class="text-muted fw-bold mb-3 small">
                <i class="bi bi-funnel text-primary me-2"></i>Filters
            </h6>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm rounded-3 py-2" placeholder="Listing or customer name...">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Status</label>
                    <select wire:model.live="status" class="form-select form-select-sm rounded-3 py-2">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">From Visit Date</label>
                    <input type="date" wire:model.live="startDate" class="form-control form-control-sm rounded-3 py-2">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px;">To Visit Date</label>
                    <input type="date" wire:model.live="endDate" class="form-control form-control-sm rounded-3 py-2">
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Total Visits</h6>
                <h3 class="text-primary fw-bold mb-0">{{ number_format($stats['total_visits']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Pending</h6>
                <h3 class="text-warning fw-bold mb-0">{{ number_format($stats['pending']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Completed</h6>
                <h3 class="text-success fw-bold mb-0">{{ number_format($stats['completed']) }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
                <h6 class="text-muted small fw-bold text-uppercase mb-2">Cancelled</h6>
                <h3 class="text-danger fw-bold mb-0">{{ number_format($stats['cancelled']) }}</h3>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 text-muted small fw-bold text-uppercase px-4 py-3" style="letter-spacing: 0.5px;">ID</th>
                            <th class="border-0 text-muted small fw-bold text-uppercase px-4 py-3" style="letter-spacing: 0.5px;">Listing</th>
                            <th class="border-0 text-muted small fw-bold text-uppercase px-4 py-3" style="letter-spacing: 0.5px;">Customer</th>
                            <th class="border-0 text-muted small fw-bold text-uppercase px-4 py-3" style="letter-spacing: 0.5px;">Visit Date & Time</th>
                            <th class="border-0 text-muted small fw-bold text-uppercase px-4 py-3" style="letter-spacing: 0.5px;">Status</th>
                            <th class="border-0 text-muted small fw-bold text-uppercase px-4 py-3" style="letter-spacing: 0.5px;">Note</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($reportData as $row)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="fw-semibold text-dark">#{{ substr($row->id, 0, 8) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="fw-semibold text-dark">{{ optional($row->listing)->title ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="fw-semibold text-dark">{{ optional($row->customer)->name ?? '-' }}</div>
                                    <div class="small text-muted">{{ optional($row->customer)->phone ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($row->visit_date)->format('d M Y') }}</div>
                                    <div class="small text-muted">{{ $row->visit_time }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $badgeClass = match($row->status) {
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'approved' => 'primary',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }} bg-opacity-10 text-{{ $badgeClass }} rounded-pill px-3">{{ ucfirst($row->status) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-muted small text-truncate d-inline-block" style="max-width: 200px;" title="{{ $row->note }}">
                                        {{ $row->note ?: '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox fs-2 text-light mb-3 d-block"></i>
                                    No visit requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($reportData->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $reportData->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
