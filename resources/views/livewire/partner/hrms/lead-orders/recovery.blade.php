<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Recovery Amount</h4>
            <p class="text-muted mb-0">Track and collect outstanding order payments</p>
        </div>
        <div class="text-end">
            <div class="fs-4 fw-bold text-danger">₹{{ number_format($totalOutstanding, 2) }}</div>
            <div class="text-muted small">Total Outstanding</div>
        </div>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success border-0 shadow-sm">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-3">
        @if(auth()->user()->canAccess('recovery_viewAny')  || auth()->user()->canAccess('recovery_viewTeam'))
            <div class="card-body p-3">
                @include('partials.hrms-filters')
            </div>
        @endif
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Search by customer name or mobile..." wire:model.live.debounce.500ms="search">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Order / Customer</th>
                        <th>Items</th>
                        <th>Financials</th>
                        <th>Payment Status</th>
                        <th>Recent Payments</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark">Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</div>
                            <div class="text-muted small"><i class="bi bi-person me-1"></i> {{ $order->lead?->customer_name ?? 'Unknown Lead' }}</div>
                            @if($order->lead?->customer_mobile)
                            <div class="text-muted small"><i class="bi bi-telephone me-1"></i> {{ $order->lead->customer_mobile }}</div>
                            @endif
                        </td>
                        <td class="py-3">
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-2">
                                {{ $order->items->count() }} Items
                            </span>
                        </td>
                        <td class="py-3">
                            <div class="text-dark">Total: <strong>₹{{ number_format($order->final_amount, 2) }}</strong></div>
                            <div class="text-success small fw-medium">Paid: ₹{{ number_format($order->paid_amount, 2) }}</div>
                            <div class="text-danger fw-medium">Balance: ₹{{ number_format($order->remaining_balance, 2) }}</div>
                        </td>
                        <td class="py-3">
                            @if($order->payment_status === 'paid')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3">
                                    PAID
                                </span>
                            @elseif($order->payment_status === 'partial')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning rounded-pill px-3">
                                    PARTIAL
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-3">
                                    {{ strtoupper($order->payment_status) }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3">
                            @php
                                $lastPayment = $order->payments->first();
                            @endphp
                            @if($lastPayment)
                                <div class="small">₹{{ number_format($lastPayment->amount, 2) }} on {{ \Carbon\Carbon::parse($lastPayment->payment_date)->format('d M Y') }}</div>
                                <div class="text-muted small">{{ ucfirst($lastPayment->payment_method) }}</div>
                            @else
                                <span class="text-muted small">No payments yet</span>
                            @endif
                        </td>
                        <td class="py-3 pe-4 text-end">
                            @if(auth()->user()->isPartner() || auth()->user()->canAccess('recovery_update'))
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3" wire:click="openPayModal('{{ $order->id }}')">
                                <i class="bi bi-cash-coin me-1"></i> Pay Amount
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-cash-stack fs-1 opacity-50"></i></div>
                            <div class="text-muted">No outstanding amounts. All orders are settled.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="card-footer border-top bg-transparent p-4">
            {{ $orders->links() }}
        </div>
        @endif
    </div>

    <!-- Pay Amount Modal -->
    <div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="payModalLabel">Record Recovery Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($payOrder)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Customer</h6>
                            <p class="fw-bold fs-5 mb-0">{{ $payOrder->lead?->customer_name ?? 'Unknown' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Order</h6>
                            <p class="fw-bold fs-5 mb-0">#{{ str_pad($payOrder->id, 5, '0', STR_PAD_LEFT) }}</p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted small mb-1">Total</h6>
                            <p class="fw-bold mb-0">₹{{ number_format($payOrder->final_amount, 2) }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted small mb-1">Already Paid</h6>
                            <p class="fw-bold text-success mb-0">₹{{ number_format($payOrder->paid_amount, 2) }}</p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted small mb-1">Remaining Balance</h6>
                            <p class="fw-bold text-danger mb-0">₹{{ number_format($payOrder->remaining_balance, 2) }}</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount Received <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" wire:model="payAmount" min="0.01">
                            @error('payAmount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" wire:model="payMethod">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="card">Card</option>
                                <option value="online">Online</option>
                                <option value="other">Other</option>
                            </select>
                            @error('payMethod') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" wire:model="payDate">
                            @error('payDate') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference / UTR No.</label>
                            <input type="text" class="form-control" wire:model="payReference" placeholder="Optional">
                            @error('payReference') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" rows="2" wire:model="payNotes" placeholder="Optional notes..."></textarea>
                            @error('payNotes') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if($payOrder->payments->isNotEmpty())
                    <div class="mt-4">
                        <h6 class="fw-bold mb-2 border-bottom pb-2">Payment History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payOrder->payments as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                        <td>{{ ucfirst($payment->payment_method) }}</td>
                                        <td>{{ $payment->reference ?: '-' }}</td>
                                        <td class="text-end fw-bold">₹{{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                    @else
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success px-4" wire:click="recordPayment" wire:loading.attr="disabled">
                        <i class="bi bi-check2-circle me-1"></i> Save Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('open-pay-modal', () => {
            let modal = new bootstrap.Modal(document.getElementById('payModal'));
            modal.show();
        });

        $wire.on('close-pay-modal', () => {
            let el = document.getElementById('payModal');
            let modal = bootstrap.Modal.getInstance(el);
            if (modal) modal.hide();
        });
    </script>
    @endscript
</div>