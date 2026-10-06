<div>
@include('partials.report-styles')

<div id="print-area">

    {{-- Print Header --}}
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Product Category Report</h4>
        <p class="text-muted small mb-0">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        <hr>
    </div>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Product Category Report</h4>
            <p class="text-muted mb-0 small">View and filter product category catalogue by name and status</p>
        </div>
        <div class="report-actions d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="exportCsv" wire:loading.attr="disabled">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="report-filter-card card mb-4 d-print-none shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel text-primary fs-5"></i>
                    <span class="fw-bold text-dark" style="font-size:0.95rem;">Filters</span>
                </div>
                @if($search || $status)
                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                @endif
            </div>
            <div class="row g-3">
                {{-- 1. Search Category Name --}}
                <div class="col-md-6 col-sm-12">
                    <label class="filter-label">Search Category Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" wire:model.live.debounce.300ms="search" placeholder="Enter category name…">
                    </div>
                </div>

                {{-- 2. Status --}}
                <div class="col-md-6 col-sm-12">
                    <label class="filter-label">Status</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-12">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Categories</div>
                <div class="fw-bold fs-4 text-primary">{{ $reportData->total() }}</div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Active Categories</div>
                <div class="fw-bold fs-4 text-success">{{ $reportData->where('status', 'active')->count() }}</div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Inactive Categories</div>
                <div class="fw-bold fs-4 text-danger">{{ $reportData->where('status', 'inactive')->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Category Name</th>
                        <th class="text-center">Total Products</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $i => $row)
                    <tr>
                        <td class="ps-3 text-muted small fw-bold">#{{ $row->id }}</td>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $row->name }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-primary border px-3 py-2 rounded-pill">
                                <i class="bi bi-box-seam me-1"></i> {{ $row->products_count ?? 0 }} Products
                            </span>
                        </td>
                        <td class="text-center">
                            @if($row->status == 'active')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                                    <i class="bi bi-check-circle me-1"></i> Active
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">
                                    <i class="bi bi-x-circle me-1"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3 text-muted small">
                            {{ $row->created_at ? $row->created_at->format('d M Y, h:i A') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            No product categories found matching the filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())
        <div class="card-footer border-top bg-transparent p-4 d-print-none">
            {{ $reportData->links() }}
        </div>
        @endif
    </div>
</div>
</div>
