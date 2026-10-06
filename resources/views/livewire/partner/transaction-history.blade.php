<div>
    <!-- Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4" style="background-color: #e0f2fe;">
                <div class="card-body py-3 d-flex align-items-center">
                    <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                    <div>
                        <div class="text-primary small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">Total Amount</div>
                        <div class="fs-4 fw-bold text-dark">₹{{ number_format($totalAmount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4" style="background-color: #fef3c7;">
                <div class="card-body py-3 d-flex align-items-center">
                    <div class="bg-white text-warning rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px;">
                        <i class="bi bi-percent fs-4"></i>
                    </div>
                    <div>
                        <div class="text-warning small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">Platform Fee</div>
                        <div class="fs-4 fw-bold text-dark">₹{{ number_format($platformFee, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4" style="background-color: #d1e7dd;">
                <div class="card-body py-3 d-flex align-items-center">
                    <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 48px; height: 48px;">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-success small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px;">Security Deposit</div>
                        <div class="fs-4 fw-bold text-dark">₹{{ number_format($securityAmount, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 rounded-top-4">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="bi bi-clock-history text-primary me-2"></i> All Transactions
            </h5>
            <div class="d-flex flex-wrap gap-2">
                <div class="input-group input-group-sm rounded-3" style="max-width: 200px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-filter"></i></span>
                    <select wire:model.live="type" class="form-select form-select-sm border-start-0 ps-0 bg-light">
                        <option value="">All Types</option>
                        <option value="booking">Bookings</option>
                        <option value="recharge">Wallet Recharge</option>
                        <option value="withdrawal">Withdrawals</option>
                        <option value="subscription">Subscriptions</option>
                    </select>
                </div>
                <div class="input-group input-group-sm rounded-3" style="max-width: 200px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-funnel"></i></span>
                    <select wire:model.live="status" class="form-select form-select-sm border-start-0 ps-0 bg-light">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-secondary fw-semibold">Txn ID / Date</th>
                        <th class="text-secondary fw-semibold">Type</th>
                        <th class="text-secondary fw-semibold">Description</th>
                        <th class="text-end text-secondary fw-semibold">Amount</th>
                        <th class="text-center text-secondary fw-semibold pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr style="cursor: pointer;" wire:click="viewDetails({{ $txn->id }})">
                            <td class="ps-4">
                                <div class="d-flex flex-column">
                                    <span class="text-primary fw-medium" style="font-size: 0.85rem; font-family: monospace;">
                                        #{{ strtoupper(substr($txn->transaction_id, 0, 8)) }}...
                                    </span>
                                    <small class="text-muted d-flex align-items-center mt-1">
                                        <i class="bi bi-calendar-event me-1"></i> {{ $txn->created_at->format('d M, Y h:i A') }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @php
                                        $badgeClass = 'bg-secondary text-secondary border-secondary-subtle';
                                        $iconHtml = '';
                                        
                                        if($txn->type === 'booking') {
                                            $badgeClass = 'bg-primary text-primary border-primary-subtle';
                                        } elseif($txn->type === 'recharge') {
                                            $badgeClass = 'bg-success text-success border-success-subtle';
                                        } elseif($txn->type === 'withdrawal') {
                                            $badgeClass = 'bg-warning text-warning border-warning-subtle';
                                        } elseif($txn->type === 'subscription') {
                                            $badgeClass = 'bg-info text-info border-info-subtle';
                                        }

                                        if($txn->status === 'completed') {
                                            $iconHtml = '<i class="bi bi-check-circle-fill text-success ms-2 fs-6"></i>';
                                        } elseif($txn->status === 'pending') {
                                            $iconHtml = '<i class="bi bi-hourglass-split text-warning ms-2 fs-6"></i>';
                                        } elseif($txn->status === 'failed' || $txn->status === 'rejected') {
                                            $iconHtml = '<i class="bi bi-x-circle-fill text-danger ms-2 fs-6"></i>';
                                        }
                                    @endphp
                                    <div class="d-inline-flex align-items-center bg-opacity-10 border rounded-pill px-3 py-1 {{ $badgeClass }}" style="background-color: currentColor;">
                                        <span class="fw-semibold" style="font-size: 0.8rem; letter-spacing: 0.3px;">{{ ucfirst($txn->type) }}</span>
                                        {!! $iconHtml !!}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-dark" style="font-size: 0.9rem; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $txn->description }}
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex flex-column align-items-end">
                                    @if($txn->type === 'withdrawal' || $txn->type === 'subscription')
                                        <span class="text-danger fw-bold fs-6">-₹{{ number_format($txn->total_amount, 2) }}</span>
                                    @else
                                        <span class="text-success fw-bold fs-6">+₹{{ number_format($txn->type === 'booking' ? $txn->net_amount : $txn->total_amount, 2) }}</span>
                                    @endif
                                    @if($txn->platform_fee > 0)
                                        <small class="text-muted" style="font-size: 0.75rem;">Fee: ₹{{ number_format($txn->platform_fee, 2) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center pe-4">
                                <button class="btn btn-sm btn-light rounded-circle text-primary" wire:click.stop="viewDetails({{ $txn->id }})">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted d-flex flex-column align-items-center">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                                        <i class="bi bi-receipt display-5 text-secondary" style="opacity: 0.5"></i>
                                    </div>
                                    <h5 class="fw-semibold text-dark">No Transactions Found</h5>
                                    <p class="mb-0">There are no transactions matching your current filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($transactions->hasPages())
            <div class="card-footer bg-white border-top py-3 rounded-bottom-4">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

    <!-- Transaction Details Modal -->
    @if($showModal && $selectedTransaction)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="modal-header border-bottom-0 pb-0 position-relative z-1" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                        <div class="d-flex flex-column w-100 pb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
                                    <i class="bi bi-tag-fill text-primary me-1"></i> {{ ucfirst($selectedTransaction->type) }}
                                </span>
                                <button type="button" class="btn-close" wire:click="closeModal"></button>
                            </div>
                            <h4 class="modal-title fw-bold text-dark mt-2">Transaction Details</h4>
                            <p class="text-muted mb-0 small">ID: <span class="font-monospace text-dark">{{ $selectedTransaction->transaction_id }}</span></p>
                        </div>
                    </div>
                    <div class="modal-body p-4 bg-white position-relative">
                        <!-- Amount Header -->
                        <div class="text-center mb-4 pb-4 border-bottom">
                            <span class="d-block text-muted text-uppercase fw-semibold" style="letter-spacing: 1px; font-size: 0.75rem;">Total Value</span>
                            @if($selectedTransaction->type === 'withdrawal' || $selectedTransaction->type === 'subscription')
                                <h1 class="display-5 fw-bold text-danger mb-0">-₹{{ number_format($selectedTransaction->total_amount, 2) }}</h1>
                            @else
                                <h1 class="display-5 fw-bold text-success mb-0">+₹{{ number_format($selectedTransaction->type === 'booking' ? $selectedTransaction->net_amount : $selectedTransaction->total_amount, 2) }}</h1>
                            @endif
                            <div class="mt-2">
                                @if($selectedTransaction->status === 'completed')
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3"><i class="bi bi-check-circle-fill me-1"></i> Completed</span>
                                @elseif($selectedTransaction->status === 'pending')
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3"><i class="bi bi-x-circle-fill me-1"></i> {{ ucfirst($selectedTransaction->status) }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <span class="d-block text-muted small mb-1">Date & Time</span>
                                    <span class="fw-semibold text-dark">{{ $selectedTransaction->created_at->format('d M, Y') }}</span><br>
                                    <small class="text-muted">{{ $selectedTransaction->created_at->format('h:i A') }}</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-light rounded-3 h-100">
                                    <span class="d-block text-muted small mb-1">Gateway Ref ID</span>
                                    <span class="fw-medium text-dark font-monospace" style="word-break: break-all; font-size: 0.85rem;">
                                        {{ $selectedTransaction->payment_gateway_id ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Breakdown -->
                        <div class="card bg-light border-0 rounded-4">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3 text-dark">Financial Breakdown</h6>
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary">Total Amount</span>
                                    <span class="fw-medium">₹{{ number_format($selectedTransaction->total_amount, 2) }}</span>
                                </div>

                                @if($selectedTransaction->wallet_deducted > 0)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary">Wallet Deducted</span>
                                    <span class="fw-medium text-danger">-₹{{ number_format($selectedTransaction->wallet_deducted, 2) }}</span>
                                </div>
                                @endif

                                @if($selectedTransaction->online_payable > 0)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary">Paid Online</span>
                                    <span class="fw-medium">₹{{ number_format($selectedTransaction->online_payable, 2) }}</span>
                                </div>
                                @endif

                                @if($selectedTransaction->platform_fee > 0)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary">Platform Fee</span>
                                    <span class="fw-medium text-danger">-₹{{ number_format($selectedTransaction->platform_fee, 2) }}</span>
                                </div>
                                @endif

                                <hr class="border-secondary border-opacity-25 my-3">
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-dark">Net Amount</span>
                                    <span class="fw-bold text-dark fs-5">₹{{ number_format($selectedTransaction->net_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mt-4 p-3 border rounded-3 bg-white">
                            <span class="d-block text-muted small mb-1 fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Description</span>
                            <p class="mb-0 text-dark">{{ $selectedTransaction->description }}</p>
                        </div>

                    </div>
                    <div class="modal-footer border-top-0 bg-light p-3">
                        <button type="button" class="btn btn-secondary w-100 rounded-3 py-2 fw-semibold" wire:click="closeModal">Close Details</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
