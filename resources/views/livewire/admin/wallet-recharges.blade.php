<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Wallet Recharges Report</h4>
        <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">Wallet Recharge Requests</h5>
            <div class="d-flex gap-2 flex-wrap">
                <input type="text" class="form-control form-control-sm w-auto" wire:model.live="search" placeholder="Search Partner...">
                <select class="form-select form-select-sm w-auto" wire:model.live="status">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="p-3 d-flex justify-content-end d-print-none">
                <div class="ms-sm-auto d-flex gap-2">
                    <button wire:click="exportCsv" class="btn btn-outline-success">
                        <i class="bi bi-download me-1"></i> Export CSV
                    </button>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="bi bi-printer me-1"></i> Print
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-feetrack mb-0">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th>Amount</th>
                            <th>Transaction ID</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recharges as $req)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $req->user->name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ \App\Helpers\AdminHelper::maskContact('email', $req->user->email ?? '' ) }}</small>
                                </td>
                                <td class="fw-bold text-success">+₹{{ $req->amount }}</td>
                                <td>{{ $req->transaction_id }}</td>
                                <td>{{ $req->notes ?: '-' }}</td>
                                <td>
                                    @if($req->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($req->status === 'approved')
                                        <span class="badge bg-success">Approved</span>
                                        <div class="small text-muted">{{ $req->approved_at?->format('d M, Y') }}</div>
                                    @else
                                        <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    @if($req->status === 'pending')
                                        <button class="btn btn-sm btn-success" wire:click="updateStatus('{{ $req->id }}', 'approved')" onclick="confirm('Are you sure you want to approve this recharge and add funds to the partner wallet?') || event.stopImmediatePropagation()">Approve</button>
                                        <button class="btn btn-sm btn-danger" wire:click="updateStatus('{{ $req->id }}', 'rejected')" onclick="confirm('Are you sure you want to reject this request?') || event.stopImmediatePropagation()">Reject</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">No recharge requests found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($recharges->hasPages())
            <div class="card-footer bg-transparent border-top p-3 d-print-none">
                {{ $recharges->links() }}
            </div>
        @endif
    </div>
</div>
</div>
