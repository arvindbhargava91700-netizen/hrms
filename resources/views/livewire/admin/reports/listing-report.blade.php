<div>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold mb-4" style="color: var(--primary-color);">Listing Report</h5>

            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search by listing or partner name..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" wire:model.live="startDate" title="Start Date">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" wire:model.live="endDate" title="End Date">
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    <button class="btn btn-primary w-100" wire:click="export"><i class="bi bi-download"></i> Export</button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Listing</th>
                            <th>Partner</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Base Price</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $listing)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $listing->title }}</div>
                            </td>
                            <td>
                                <div>{{ $listing->partner->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div>{{ $listing->category->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                @if($listing->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($listing->status === 'inactive')
                                    <span class="badge bg-secondary">Inactive</span>
                                @elseif($listing->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ ucfirst($listing->status) }}</span>
                                @endif
                            </td>
                            <td>{{ number_format($listing->base_price, 2) }}</td>
                            <td>{{ $listing->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No listings found for the selected criteria.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $records->links() }}
            </div>
        </div>
    </div>
</div>
