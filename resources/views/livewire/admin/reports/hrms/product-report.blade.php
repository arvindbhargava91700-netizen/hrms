<div>
@include('partials.report-styles')

<div id="print-area">

    {{-- Print Header --}}
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Product Report</h4>
        <p class="text-muted small mb-0">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        <hr>
    </div>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Product Report</h4>
            <p class="text-muted mb-0 small">View and filter products by category, product, and status</p>
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
                @if($categoryId || $productId || $status || $search)
                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                @endif
            </div>
            <div class="row g-3">
                {{-- 1. Product Category --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Product Category</label>
                    <select class="form-select border-primary-subtle" wire:model.live="categoryId">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Product (Cascading based on selected Category) --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Product</label>
                    <select class="form-select border-info-subtle" wire:model.live="productId">
                        <option value="">All Products {{ $categoryId ? '(in Selected Category)' : '' }}</option>
                        @foreach($productsList as $prod)
                            <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Status --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Status</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                {{-- 4. Search --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" wire:model.live.debounce.300ms="search" placeholder="Product or category…">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Products</div>
                <div class="fw-bold fs-4 text-primary">{{ $reportData->total() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Active Products</div>
                <div class="fw-bold fs-4 text-success">{{ $reportData->where('status', 'active')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Inactive Products</div>
                <div class="fw-bold fs-4 text-danger">{{ $reportData->where('status', 'inactive')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Base Value</div>
                <div class="fw-bold fs-4 text-dark">₹{{ number_format($reportData->sum('amount'), 2) }}</div>
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
                        <th>Product Name</th>
                        <th>Category</th>
                        <th class="text-end">Base Amount</th>
                        <th class="text-center">GST</th>
                        <th class="text-end">Total Price</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $i => $row)
                    @php
                        $isGstIncluded = $row->gst_type === 'include' && (float) $row->gst_percent > 0;
                        $gstRate = $isGstIncluded ? (float) $row->gst_percent : 0;
                        $gstAmount = $isGstIncluded ? ((float) $row->amount * $gstRate / 100) : 0;
                        $totalPrice = (float) $row->amount + $gstAmount;
                    @endphp
                    <tr>
                        <td class="ps-3 text-muted small fw-bold">#{{ $row->id }}</td>
                        <td>
                            <div class="fw-bold text-dark fs-6">{{ $row->name }}</div>
                        </td>
                        <td>
                            @if(optional($row->category)->name)
                                <span class="badge bg-light text-primary border px-2.5 py-1">
                                    <i class="bi bi-tag me-1"></i>{{ $row->category->name }}
                                </span>
                            @else
                                <span class="text-muted fst-italic">Uncategorized</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">₹{{ number_format($row->amount, 2) }}</td>
                        <td class="text-center">
                            @if($isGstIncluded)
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 fw-bold">
                                    {{ number_format($gstRate, 1) }}% Included
                                </span>
                            @else
                                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 extra-small">
                                    0% (Not Included)
                                </span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-primary">₹{{ number_format($totalPrice, 2) }}</td>
                        <td class="text-center">
                            @if($row->status == 'active')
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">
                                    <i class="bi bi-check-circle me-1"></i> Active
                                </span>
                            @else
                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1">
                                    <i class="bi bi-x-circle me-1"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-end pe-3 text-muted small">
                            {{ $row->created_at ? $row->created_at->format('d M Y') : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            No products found matching the selected category/status filters.
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
