<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Invoices Report</h4>
        <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr>
    </div>

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
                <table class="table table-feetrack mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Invoice #</th>
                            <th>Customer</th>
                            <th>Listing</th>
                            <th>Amount</th>
                            <th>Total</th>
                            <th class="text-nowrap">Due Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptionInvoices as $invoice)
                            <tr>
                                <td class="align-middle fw-600 text-nowrap">{{ $invoice->invoice_number }}</td>
                                <td class="align-middle">{{ $invoice->subscription->customer->name ?? 'Unknown' }}</td>
                                <td class="align-middle">{{ $invoice->subscription->package->listing->title ?? '—' }}</td>
                                <td class="align-middle text-nowrap">₹{{ number_format((float) $invoice->amount, 2) }}</td>
                                <td class="align-middle text-nowrap">₹{{ number_format((float) $invoice->total, 2) }}</td>
                                <td class="align-middle text-nowrap">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</td>
                                <td class="align-middle"><span class="badge-status badge-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></td>
                                <td class="align-middle text-end">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="viewInvoice('{{ $invoice->id }}')">
                                        <i class="bi bi-receipt"></i> View
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
                <table class="table table-feetrack mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Receipt Ref</th>
                            <th>Customer</th>
                            <th>Listing</th>
                            <th class="text-nowrap">Invoice #</th>
                            <th>Amount</th>
                            <th class="text-nowrap">Paid At</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paymentReceipts as $payment)
                            <tr>
                                <td class="align-middle fw-600 font-monospace text-nowrap">{{ $payment->gateway_ref ?? $payment->id }}</td>
                                <td class="align-middle">{{ $payment->subscription->customer->name ?? 'Unknown' }}</td>
                                <td class="align-middle">{{ $payment->subscription->package->listing->title ?? '—' }}</td>
                                <td class="align-middle text-nowrap">{{ $payment->invoice?->invoice_number ?? '—' }}</td>
                                <td class="align-middle text-nowrap">₹{{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="align-middle text-nowrap">{{ $payment->paid_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                <td class="align-middle"><span class="badge-status badge-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                                <td class="align-middle text-end">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="viewPayment('{{ $payment->id }}')">
                                        <i class="bi bi-receipt"></i> View
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
</div>

{{-- Receipt Detail Modal --}}
@php
    $receipt = $viewingInvoice ?? $viewingPayment;
    $isInvoice = $viewingInvoice !== null;
    $receiptType = $isInvoice ? 'invoice' : 'payment';
    $receiptId = $receipt->id ?? null;
    $subscription = $receipt->subscription ?? null;
    $customer = $subscription->customer ?? null;
    $listing = $subscription->package->listing ?? null;
    $payment = $viewingPayment ?? null;
@endphp
@if($receipt)
<div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="closeReceipt">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ $isInvoice ? 'Invoice' : 'Payment Receipt' }} Details</h5>
                <button type="button" class="btn-close" wire:click="closeReceipt"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted">Customer</div>
                            <div class="fw-600">{{ $customer->name ?? '—' }}</div>
                            <div class="small text-muted">@php
                $canViewContact = auth()->check() && auth()->user()->can('admin_view_contact_info');
                $emailParts = explode('@', $customer->email ?? '');
                $maskedEmail = $canViewContact ? ($customer->email ?? '—') : (empty($customer->email) ? '—' : str_repeat('*', max(1, strlen($emailParts[0]))) . '@' . ($emailParts[1] ?? ''));
            @endphp
            {{ $maskedEmail }}</div>
                            <div class="small text-muted">@php
                $canViewContact = auth()->check() && auth()->user()->can('admin_view_contact_info');
                $maskedMobile = $canViewContact ? ($customer->mobile ?? '—') : (empty($customer->mobile) ? '—' : str_repeat('*', max(0, strlen($customer->mobile) - 4)) . substr($customer->mobile, -4));
            @endphp
            {{ $maskedMobile }}</div>
                        </div>
                    </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted">Listing</div>
                                    <div class="fw-600">{{ $listing->title ?? '—' }}</div>
                                    <div class="small text-muted">{{ $subscription->package->name ?? '—' }}</div>
                                    <div class="small text-muted">Subscription #{{ $subscription->id ?? '—' }}</div>
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
                                        <td>{{ $payment?->gateway_ref ?? '—' }}</td>
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
                        <a class="btn btn-outline-secondary" href="{{ route('admin.invoices.receipt.show', ['type' => $receiptType, 'id' => $receiptId]) }}" target="_blank" rel="noopener">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Open
                        </a>
                        <a class="btn btn-outline-primary" href="{{ route('admin.invoices.receipt.download', ['type' => $receiptType, 'id' => $receiptId]) }}">
                            <i class="bi bi-download me-1"></i> Download PDF
                        </a>
                        <button type="button" class="btn btn-dark" onclick="window.open('{{ route('admin.invoices.receipt.show', ['type' => $receiptType, 'id' => $receiptId]) }}?autoprint=1', '_blank', 'noopener')">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeReceipt">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
