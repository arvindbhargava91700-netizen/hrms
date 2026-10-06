<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">Subscription History</h4>
            <p class="text-muted mb-0 small">View your past and active platform plan requests</p>
        </div>
    </div>

    <!-- Subscription History -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0 fw-bold">All Subscriptions</h5>
            <div class="d-flex gap-2 flex-wrap">
                <input type="date" class="form-control form-control-sm w-auto" wire:model.live="startDate" placeholder="Start Date">
                <input type="date" class="form-control form-control-sm w-auto" wire:model.live="endDate" placeholder="End Date">
                <select class="form-select form-select-sm w-auto" wire:model.live="statusFilter">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="expired">Expired</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="rejected">Rejected</option>
                </select>
                <button class="btn btn-sm btn-outline-secondary" wire:click="exportHistory">
                    <i class="bi bi-download me-1"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-feetrack mb-0 align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Package</th>
                            <th>Requested On</th>
                            <th>Validity Dates</th>
                            <th class="pe-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $sub)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $sub->package->name ?? 'Unknown' }}</div>
                                    <div class="small text-muted">₹{{ number_format($sub->package->price ?? 0, 2) }}</div>
                                </td>
                                <td>{{ $sub->created_at->format('d M, Y') }}</td>
                                <td>
                                    @if($sub->starts_at)
                                        <div class="text-dark">{{ \Carbon\Carbon::parse($sub->starts_at)->format('d M, Y') }}</div>
                                        <div class="small text-muted">to {{ $sub->expires_at ? \Carbon\Carbon::parse($sub->expires_at)->format('d M, Y') : 'Lifetime' }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="pe-4">
                                    @if($sub->status === 'active')
                                        <span class="badge bg-success-soft text-success px-3 py-1 rounded-pill">Active</span>
                                    @elseif($sub->status === 'pending')
                                        <span class="badge bg-warning-soft text-warning px-3 py-1 rounded-pill">Pending</span>
                                    @elseif($sub->status === 'expired')
                                        <span class="badge bg-danger-soft text-danger px-3 py-1 rounded-pill">Expired</span>
                                    @elseif($sub->status === 'rejected')
                                        <span class="badge bg-danger px-3 py-1 rounded-pill">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary-soft text-secondary px-3 py-1 rounded-pill">Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                    No subscription history found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
