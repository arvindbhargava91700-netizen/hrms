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
                <i class="bi bi-globe text-primary me-2"></i> Global Transactions
            </h5>
            <div class="d-flex flex-wrap gap-2">
                <div class="input-group input-group-sm rounded-3 shadow-sm" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Search Txn ID or User...">
                </div>
                <div class="input-group input-group-sm rounded-3 shadow-sm" style="max-width: 150px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-filter"></i></span>
                    <select wire:model.live="type" class="form-select form-select-sm border-start-0 ps-0 bg-light">
                        <option value="">All Types</option>
                        <option value="booking">Bookings</option>
                        <option value="recharge">Wallet Recharge</option>
                        <option value="withdrawal">Withdrawals</option>
                        <option value="subscription">Subscriptions</option>
                    </select>
                </div>
                <div class="input-group input-group-sm rounded-3 shadow-sm" style="max-width: 150px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-funnel"></i></span>
                    <select wire:model.live="status" class="form-select form-select-sm border-start-0 ps-0 bg-light">
                        <option value="">All Statuses</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="failed">Failed</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="input-group input-group-sm rounded-3 shadow-sm" style="max-width: 150px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar"></i></span>
                    <input type="date" wire:model.live="startDate" class="form-control form-control-sm border-start-0 ps-0 bg-light" title="Start Date">
                </div>
                <div class="input-group input-group-sm rounded-3 shadow-sm" style="max-width: 150px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar"></i></span>
                    <input type="date" wire:model.live="endDate" class="form-control form-control-sm border-start-0 ps-0 bg-light" title="End Date">
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-secondary fw-semibold">User Details</th>
                        <th class="text-secondary fw-semibold">Txn ID / Date</th>
                        <th class="text-secondary fw-semibold">Type</th>
                        <th class="text-end text-secondary fw-semibold">Amount</th>
                        <th class="text-center text-secondary fw-semibold pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $txn)
                        <tr style="cursor: pointer;" wire:click="viewDetails({{ $txn->id }})">
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 42px; height: 42px; font-size: 1.1rem;">
                                        {{ strtoupper(substr($txn->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <span class="d-block fw-bold text-dark">{{ $txn->user->name ?? 'Unknown' }}</span>
                                        <span class="text-muted d-flex align-items-center gap-1" style="font-size: 0.8rem;">
                                            <i class="bi bi-telephone"></i> {{ $txn->user->mobile ?? 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
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
                                    @if($txn->type === 'booking')
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2">Booking</span>
                                    @elseif($txn->type === 'recharge')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-2">Recharge</span>
                                    @elseif($txn->type === 'withdrawal')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-3 py-2">Withdrawal</span>
                                    @elseif($txn->type === 'subscription')
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill px-3 py-2">Subscription</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">{{ ucfirst($txn->type) }}</span>
                                    @endif

                                    @if($txn->status === 'completed')
                                        <i class="bi bi-check-circle-fill text-success fs-6" title="Completed"></i>
                                    @elseif($txn->status === 'pending')
                                        <i class="bi bi-hourglass-split text-warning fs-6" title="Pending"></i>
                                    @elseif($txn->status === 'failed' || $txn->status === 'rejected')
                                        <i class="bi bi-x-circle-fill text-danger fs-6" title="{{ ucfirst($txn->status) }}"></i>
                                    @endif
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
                                <button class="btn btn-sm btn-light rounded-circle text-primary shadow-sm" wire:click.stop="viewDetails({{ $txn->id }})">
                                    <i class="bi bi-chevron-right"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted d-flex flex-column align-items-center">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                                        <i class="bi bi-search display-5 text-secondary" style="opacity: 0.5"></i>
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

                        <!-- User Profile Mini -->
                        <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3 mb-4">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 45px; height: 45px; font-size: 1.2rem;">
                                {{ strtoupper(substr($selectedTransaction->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <span class="d-block fw-bold text-dark">{{ $selectedTransaction->user->name ?? 'Unknown' }}</span>
                                <div class="text-muted d-flex align-items-center gap-3 mt-1" style="font-size: 0.8rem;">
                                    <span><i class="bi bi-telephone text-primary"></i> {{ $selectedTransaction->user->mobile ?? 'N/A' }}</span>
                                    <span><i class="bi bi-envelope text-primary"></i> {{ $selectedTransaction->user->email ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

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
                        <div class="card bg-light border-0 rounded-4 mb-4">
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
                        <div class="p-3 border rounded-3 bg-white shadow-sm">
                            <span class="d-block text-muted small mb-1 fw-semibold text-uppercase" style="letter-spacing: 0.5px;">Description</span>
                            <p class="mb-0 text-dark">{{ $selectedTransaction->description }}</p>
                        </div>

                    </div>
                    <div class="modal-footer border-top-0 bg-light p-3">
                        <button type="button" class="btn btn-secondary w-100 rounded-3 py-2 fw-semibold shadow-sm" wire:click="closeModal">Close Details</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
