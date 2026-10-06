<div>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold mb-4" style="color: var(--primary-color);">Partner Subscription Report</h5>

            <!-- Status Cards -->
            <div class="row g-3 mb-4">
                <div class="col">
                    <div class="card bg-primary text-white h-100 shadow-sm border-0">
                        <div class="card-body py-3">
                            <h6 class="card-title text-white-50 mb-1">Total</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-warning text-dark h-100 shadow-sm border-0">
                        <div class="card-body py-3">
                            <h6 class="card-title text-dark-50 mb-1" style="opacity: 0.7;">Pending</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['pending'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-success text-white h-100 shadow-sm border-0">
                        <div class="card-body py-3">
                            <h6 class="card-title text-white-50 mb-1">Active</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['active'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-danger text-white h-100 shadow-sm border-0">
                        <div class="card-body py-3">
                            <h6 class="card-title text-white-50 mb-1">Expired</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['expired'] }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card bg-secondary text-white h-100 shadow-sm border-0">
                        <div class="card-body py-3">
                            <h6 class="card-title text-white-50 mb-1">Cancelled/Rejected</h6>
                            <h3 class="mb-0 fw-bold">{{ $stats['cancelled'] + $stats['rejected'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>

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
                            <th>Partner</th>
                            <th>Package</th>
                            <th>Amount Paid</th>
                            <th>Status</th>
                            <th>Valid Dates</th>
                            <th>Created On</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $sub)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $sub->partner->name ?? 'N/A' }}</div>
                                <div class="small text-muted">{{ \App\Helpers\AdminHelper::maskContact('email', $sub->partner->email ?? '' ) }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-primary">{{ $sub->package->name ?? 'N/A' }}</div>
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
                                No partner plans found for the selected criteria.
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
