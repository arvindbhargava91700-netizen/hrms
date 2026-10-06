<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Payments Report</h4>
        <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-currency-rupee text-success"></i></div>
                <div>
                    <div class="stat-label">Total Volume (Paid)</div>
                    <div class="stat-value text-success">₹{{ number_format($stats['total_volume'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-receipt"></i></div>
                <div>
                    <div class="stat-label">Paid Transactions</div>
                    <div class="stat-value">{{ number_format($stats['paid_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="{{ ($stats['pending_count'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-hourglass-split text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending Transactions</div>
                    <div class="stat-value">{{ number_format($stats['pending_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="{{ ($stats['failed_count'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-danger);' : '' }}">
                <div class="stat-icon bg-danger-soft"><i class="bi bi-exclamation-triangle text-danger"></i></div>
                <div>
                    <div class="stat-label">Failed Transactions</div>
                    <div class="stat-value">{{ number_format($stats['failed_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
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
            <div class="ms-sm-auto d-flex gap-2">
                <a href="{{ $this->exportUrl }}" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
                <button class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th class="text-end d-print-none">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <div class="fw-600 font-monospace fs-14">{{ $payment->gateway_ref ?? 'N/A' }}</div>
                                <div class="text-muted fs-12">{{ $payment->gateway ?? 'System' }}</div>
                            </td>
                            <td>
                                <div class="fw-600">{{ $payment->subscription->customer->name ?? 'Unknown' }}</div>
                            </td>
                            <td class="fw-700">₹{{ number_format($payment->amount) }}</td>
                            <td>{{ $payment->paid_at?->format('d M Y, h:i A') ?? '-' }}</td>
                            <td>
                                <span class="badge-status badge-{{ $payment->status }}">{{ $payment->status }}</span>
                            </td>
                            <td class="text-end d-print-none">
                                <a href="{{ route('admin.invoices.receipt.show', ['type' => 'payment', 'id' => $payment->id]) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="View/Print Receipt">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="{{ route('admin.invoices.receipt.download', ['type' => 'payment', 'id' => $payment->id]) }}" class="btn btn-sm btn-outline-primary" title="Download Receipt">
                                    <i class="bi bi-download"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No payments found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="card-footer bg-transparent border-top p-3 d-print-none">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
</div>
