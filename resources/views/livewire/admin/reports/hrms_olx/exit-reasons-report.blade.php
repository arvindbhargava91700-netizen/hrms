<div>
    {{-- Header --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">HRMS Exit Reasons Catalog Report</h4>
            <p class="text-muted small mb-0">Configured exit reason categories, type distribution (Voluntary / Involuntary), and usage metrics.</p>
        </div>
        <div class="d-flex align-items-center gap-2 d-print-none">
            <button class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
                <i class="bi bi-printer"></i> Print Report
            </button>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 d-print-none">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Exit Type Filter</label>
                    <select wire:model.live="filterType" class="form-select form-select-sm">
                        <option value="all">All Types</option>
                        <option value="voluntary">Voluntary</option>
                        <option value="involuntary">Involuntary</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold text-muted mb-1">Search Exit Reason</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Search exit reason title...">
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-primary text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Total Exit Reasons</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($totalReasons) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-warning text-dark h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-dark-50 text-uppercase fw-semibold">Voluntary Reasons</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($voluntaryCount) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 bg-secondary text-white h-100">
                <div class="card-body p-3 text-center">
                    <div class="small text-white-50 text-uppercase fw-semibold">Involuntary Reasons</div>
                    <div class="fs-3 fw-bold mt-1">{{ number_format($involuntaryCount) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-box-arrow-right text-warning me-2"></i>Exit Reasons Catalog
            </h6>
            <div class="d-flex align-items-center gap-2 d-print-none">
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 75px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Reason Name</th>
                        <th>Type</th>
                        <th>Scope</th>
                        <th class="pe-3 text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedReasons as $reason)
                        <tr>
                            <td class="ps-3 fw-bold text-dark">{{ $reason->name }}</td>
                            <td>
                                @if($reason->type === 'voluntary')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Voluntary</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Involuntary</span>
                                @endif
                            </td>
                            <td>
                                @if($reason->partner_id)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Custom (Partner)</span>
                                @else
                                    <span class="badge bg-light text-dark border">Global System</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                @if($reason->is_active)
                                    <span class="badge bg-success text-white">ACTIVE</span>
                                @else
                                    <span class="badge bg-secondary text-white">INACTIVE</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No exit reasons found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedReasons->hasPages())
            <div class="card-footer bg-white py-3 border-0 d-print-none">
                {{ $paginatedReasons->links() }}
            </div>
        @endif
    </div>
</div>
