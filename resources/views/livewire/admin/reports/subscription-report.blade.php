<div>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold mb-4" style="color: var(--primary-color);">Customer Subscription Report</h5>

            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Search..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.live="startDate" title="Start Date">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.live="endDate" title="End Date">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="expiresFilter">
                        <option value="">Any Expiration</option>
                        <option value="today">Expiring Today</option>
                        <option value="3days">Expiring Next 3 Days</option>
                        <option value="week">Expiring Next 1 Week</option>
                        <option value="expired">Expired</option>
                    </select>
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
                            <th>Customer</th>
                            <th>Listing / Package</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Valid Dates</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $sub)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $sub->customer->name ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ \App\Helpers\AdminHelper::maskContact('email', $sub->customer->email ?? '' ) }}</div>
                            </td>
                            <td>
                                <div>{{ $sub->package->listing->title ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ $sub->package->name ?? 'N/A' }}</div>
                            </td>
                            <td>{{ number_format($sub->package->price ?? 0, 2) }}</td>
                            <td>
                                @if($sub->status === 'active' && $sub->expires_at && $sub->expires_at < now())
                                    <span class="badge bg-danger">Expired</span>
                                @elseif($sub->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($sub->status === 'expired')
                                    <span class="badge bg-danger">Expired</span>
                                @elseif($sub->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($sub->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="small">Start: {{ $sub->starts_at ? $sub->starts_at->format('M d, Y') : 'N/A' }}</div>
                                <div class="small">End: {{ $sub->expires_at ? $sub->expires_at->format('M d, Y') : 'N/A' }}</div>
                            </td>
                            <td>{{ $sub->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No subscriptions found for the selected criteria.
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
