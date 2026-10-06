<div>
    <div class="card">
        <div class="card-header d-flex flex-column flex-sm-row gap-3">
            <div class="search-box" style="width:100%; max-width:300px;">
                <i class="bi bi-search search-icon"></i>
                <input type="text" class="form-control" wire:model.live="search" placeholder="Search Customer or Ref...">
            </div>
            <select class="form-select" wire:model.live="statusFilter" style="width:100%; max-width:200px;">
                <option value="">All Statuses</option>
                <option value="paid">Paid</option>
                <option value="failed">Failed</option>
                <option value="refunded">Refunded</option>
            </select>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Customer</th>
                        <th>Listing</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <div class="fw-600 font-monospace fs-14">{{ $payment->receipt_number ?? ($payment->gateway_ref ?? 'N/A') }}</div>
                                <div class="text-muted fs-12">{{ $payment->gateway ?? 'System' }}</div>
                                @if($payment->booking)
                                    <div class="text-muted fs-12 mt-1">BKG: #{{ $payment->booking->booking_number ?? substr($payment->booking->id, 0, 8) }}</div>
                                @endif
                                @if($payment->subscription)
                                    <div class="text-muted fs-12">SUB: #{{ $payment->subscription->subscription_number ?? substr($payment->subscription->id, 0, 8) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-600">{{ $payment->customer->name ?? 'Unknown' }}</div>
                            </td>
                            <td>
                                @php $lst = $payment->subscription?->package?->listing ?? $payment->booking?->package?->listing; @endphp
                                <div class="fw-600">{{ $lst?->title ?? '-' }}</div>
                                <div class="small text-muted">LST: #{{ $lst?->listing_number ?? '-' }}</div>
                            </td>
                            <td class="fw-700">₹{{ number_format($payment->amount) }}</td>
                            <td>{{ $payment->paid_at?->format('d M Y, h:i A') ?? '-' }}</td>
                            <td>
                                <span class="badge-status badge-{{ $payment->status }}">{{ $payment->status }}</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="viewPayment('{{ $payment->id }}')" wire:key="view-btn-{{ $payment->id }}">
                                    <i class="bi bi-receipt"></i> View
                                </button>
                                <a href="{{ route('partner.invoices.receipt.show', ['type' => 'payment', 'id' => $payment->id]) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="View/Print Receipt">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="{{ route('partner.invoices.receipt.download', ['type' => 'payment', 'id' => $payment->id]) }}" class="btn btn-sm btn-outline-primary" title="Download Receipt">
                                    <i class="bi bi-download"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">No payments found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="card-footer bg-transparent border-top p-3">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

{{-- Receipt Detail Modal --}}
@php
    $receipt = $viewingPayment;
    $receiptType = 'payment';
    $receiptId = $receipt?->id ?? null;
    $subscription = $receipt?->subscription ?? null;
    $booking = $receipt?->booking ?? null;
    $customer = $subscription?->customer ?? $booking?->customer ?? null;
    $listing = $subscription?->package?->listing ?? $booking?->package?->listing ?? null;
    $payment = $receipt;
    $invoice = $payment?->invoice ?? null;
@endphp
@if($receipt)
<div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="closeReceipt">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Receipt Details</h5>
                <button type="button" class="btn-close" wire:click="closeReceipt"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted">Customer</div>
                            <div class="fw-600">{{ $customer->name ?? '—' }}</div>
                            <div class="small text-muted">@php
                $emailParts = explode('@', $customer?->email ?? '');
                $maskedEmail = empty($customer?->email) ? '—' : str_repeat('*', max(1, strlen($emailParts[0] ?? ''))) . '@' . ($emailParts[1] ?? '');
            @endphp
            {{ $maskedEmail }}</div>
                            <div class="small text-muted">@php
                $maskedMobile = empty($customer?->mobile) ? '—' : str_repeat('*', max(0, strlen($customer?->mobile) - 4)) . substr($customer?->mobile, -4);
            @endphp
            {{ $maskedMobile }}</div>
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
                                <td>{{ $invoice?->invoice_number ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Receipt Ref</th>
                                <td>{{ $payment?->receipt_number ?? $payment?->gateway_ref ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Gateway</th>
                                <td>{{ $payment?->gateway ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Amount</th>
                                <td>₹{{ number_format((float) ($payment?->amount ?? 0), 2) }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="badge-status badge-{{ $payment?->status }}">{{ $payment?->status ?? 'unknown' }}</span></td>
                            </tr>
                            <tr>
                                <th>Paid On</th>
                                <td>{{ $payment?->paid_at?->format('d M Y, h:i A') ?? '—' }}</td>
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
