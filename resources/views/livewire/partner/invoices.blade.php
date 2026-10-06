<div>
    <div class="card mb-4">
        <div class="card-header d-flex flex-column flex-sm-row gap-3 align-items-sm-center">
            <div class="search-box" style="width:100%; max-width:320px;">
                <i class="bi bi-search search-icon"></i>
                <input type="text" class="form-control" wire:model.live="search" placeholder="Search invoice, customer, listing...">
            </div>
            <select class="form-select" wire:model.live="statusFilter" style="width:100%; max-width:200px;">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="sent">Sent</option>
                <option value="paid">Paid</option>
                <option value="overdue">Overdue</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="card-body border-bottom">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <button class="nav-link {{ $viewMode === 'subscription' ? 'active' : '' }}" wire:click="$set('viewMode', 'subscription')">Subscription Invoices</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link {{ $viewMode === 'payment' ? 'active' : '' }}" wire:click="$set('viewMode', 'payment')">Payment Receipts</button>
                </li>
            </ul>
        </div>

        @if($viewMode === 'subscription')
            <div class="table-responsive">
                <table class="table table-feetrack mb-0">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Listing</th>
                            <th>Amount</th>
                            <th>Total</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptionInvoices as $invoice)
                            <tr>
                                <td class="fw-600">{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->subscription->customer->name ?? 'Unknown' }}</td>
                                <td>
                                    <div class="fw-600">{{ $invoice->subscription->package->listing->title ?? '—' }}</div>
                                    <div class="small text-muted">LST: #{{ $invoice->subscription->package->listing->listing_number ?? '—' }}</div>
                                    <div class="small text-muted">SUB: #{{ $invoice->subscription->subscription_number ?? '—' }}</div>
                                    @if($invoice->subscription?->booking)
                                        <div class="small text-muted">BKG: #{{ $invoice->subscription->booking->booking_number ?? '—' }}</div>
                                    @endif
                                </td>
                                <td>₹{{ number_format((float) $invoice->amount, 2) }}</td>
                                <td>₹{{ number_format((float) $invoice->total, 2) }}</td>
                                <td>{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                                <td><span class="badge-status badge-{{ $invoice->status }}">{{ $invoice->status }}</span></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="viewInvoice('{{ $invoice->id }}')">
                                        <i class="bi bi-receipt me-1"></i> View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">No subscription invoices found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($subscriptionInvoices->hasPages())
                <div class="card-footer bg-transparent border-top p-3">{{ $subscriptionInvoices->links() }}</div>
            @endif
        @else
            <div class="table-responsive">
                <table class="table table-feetrack mb-0">
                    <thead>
                        <tr>
                            <th>Receipt Ref</th>
                            <th>Customer</th>
                            <th>Listing</th>
                            <th>Invoice #</th>
                            <th>Amount</th>
                            <th>Paid At</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentReceipts as $payment)
                            <tr>
                                <td class="fw-600 font-monospace">{{ $payment->receipt_number ?? $payment->gateway_ref }}</td>
                                <td>{{ $payment->customer->name ?? 'Unknown' }}</td>
                                <td>
                                    @php $lst = $payment->subscription?->package?->listing ?? $payment->booking?->package?->listing; @endphp
                                    <div class="fw-600">{{ $lst?->title ?? '—' }}</div>
                                    <div class="small text-muted">LST: #{{ $lst?->listing_number ?? '—' }}</div>
                                    @if($payment->subscription)
                                        <div class="small text-muted">SUB: #{{ $payment->subscription->subscription_number ?? '—' }}</div>
                                    @endif
                                    @if($payment->booking)
                                        <div class="small text-muted">BKG: #{{ $payment->booking->booking_number ?? '—' }}</div>
                                    @endif
                                </td>
                                <td>{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                                <td>₹{{ number_format((float) $payment->amount, 2) }}</td>
                                <td>{{ $payment->paid_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                <td><span class="badge-status badge-{{ $payment->status }}">{{ $payment->status }}</span></td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="viewPayment('{{ $payment->id }}')">
                                        <i class="bi bi-receipt me-1"></i> View
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted">No payment receipts found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($paymentReceipts->hasPages())
                <div class="card-footer bg-transparent border-top p-3">{{ $paymentReceipts->links() }}</div>
            @endif
        @endif
    </div>

    @if($viewingInvoice || $viewingPayment)
        @php
            $receipt = $viewingInvoice ?? $viewingPayment;
            $subscription = $receipt->subscription ?? null;
            $booking = $receipt->booking ?? null;
            $customer = $subscription->customer ?? $booking?->customer ?? null;
            $listing = $subscription?->package?->listing ?? $booking?->package?->listing ?? null;
            $payment = $viewingPayment ?? $receipt?->payments?->first();
            $receiptType = $viewingPayment ? 'payment' : 'invoice';
            $receiptId = $receipt->id ?? null;
        @endphp
        <div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="closeReceipt">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-1">Invoice Receipt</h5>
                            <div class="small text-muted">{{ $receipt->invoice_number ?? $payment?->gateway_ref ?? '—' }}</div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeReceipt"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted">Customer</div>
                                    <div class="fw-600">{{ $customer->name ?? 'Unknown' }}</div>
                                    <div class="small text-muted">@php $emailParts = explode('@', $customer?->email ?? ''); $maskedEmail = empty($customer?->email) ? '—' : str_repeat('*', max(1, strlen($emailParts[0] ?? ''))) . '@' . ($emailParts[1] ?? ''); @endphp {{ $maskedEmail }}</div>
                                    <div class="small text-muted">{{ $customer->mobile ?? '' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted">Listing</div>
                                    <div class="fw-600">{{ $listing->title ?? '—' }}</div>
                                    <div class="small text-muted">LST: #{{ $listing->listing_number ?? '—' }}</div>
                                    <div class="small text-muted">{{ $subscription?->package?->name ?? $booking?->package?->name ?? '—' }}</div>
                                    @if($subscription)
                                    <div class="small text-muted">Subscription #{{ $subscription->subscription_number ?? ($subscription->id ?? '—') }}</div>
                                    @endif
                                    @if($subscription?->booking || $booking)
                                    @php $bkg = $subscription?->booking ?? $booking; @endphp
                                    <div class="small text-muted">Booking #{{ $bkg->booking_number ?? ($bkg->id ?? '—') }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <th style="width:35%;">Invoice No</th>
                                        <td>{{ $receipt->invoice_number ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Receipt Ref</th>
                                        <td>{{ $payment?->receipt_number ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Gateway</th>
                                        <td>{{ $payment?->gateway ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Amount</th>
                                        <td>₹{{ number_format((float) ($receipt->amount ?? $payment?->amount ?? 0), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tax</th>
                                        <td>₹{{ number_format((float) ($receipt->tax ?? 0), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Total</th>
                                        <td>₹{{ number_format((float) ($receipt->total ?? $payment?->amount ?? 0), 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td><span class="badge-status badge-{{ $receipt->status ?? $payment?->status }}">{{ $receipt->status ?? $payment?->status ?? 'unknown' }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Due / Paid On</th>
                                        <td>{{ $receipt->due_date?->format('d M Y') ?? $payment?->paid_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-outline-secondary" href="{{ route('partner.invoices.receipt.show', ['type' => $receiptType, 'id' => $receiptId]) }}" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Open
                        </a>
                        <a class="btn btn-outline-primary" href="{{ route('partner.invoices.receipt.download', ['type' => $receiptType, 'id' => $receiptId]) }}">
                            <i class="bi bi-download me-1"></i> Download PDF
                        </a>
                        <button type="button" class="btn btn-dark" onclick="window.open('{{ route('partner.invoices.receipt.show', ['type' => $receiptType, 'id' => $receiptId]) }}?autoprint=1', '_blank', 'noopener')">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeReceipt">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
