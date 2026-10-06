<div>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold mb-4" style="color: var(--primary-color);">Payment Report</h5>

            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search by payment ID or gateway ref..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" wire:model.live="startDate" title="Start Date">
                </div>
                <div class="col-md-3">
                    <input type="date" class="form-control" wire:model.live="endDate" title="End Date">
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
                            <th>Payment ID</th>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Gross Amount</th>
                            <th>Platform Fee</th>
                            <th>Net to Partner</th>
                            <th>Gateway</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $payment)
                        <tr>
                            <td>
                                <div class="fw-bold small">{{ $payment->id }}</div>
                            </td>
                            <td>
                                <div class="fw-bold small text-primary">{{ $payment->receipt_number ?? $payment->gateway_ref }}</div>
                            </td>
                            <td>
                                <div>{{ $payment->customer->name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-muted">{{ number_format($payment->amount, 2) }}</div>
                            </td>
                            @php
                                $platFee = $this->getPlatformFee($payment->booking_id ?? $payment->subscription_id);
                                $netAmt = $payment->amount - $platFee;
                            @endphp
                            <td>
                                <div class="fw-bold text-success">{{ number_format($platFee, 2) }}</div>
                            </td>
                            <td>
                                <div class="fw-bold text-info">{{ number_format($netAmt, 2) }}</div>
                            </td>
                            <td>
                                <div><span class="badge bg-light text-dark border">{{ ucfirst($payment->gateway) }}</span></div>
                            </td>
                            <td>
                                @if($payment->status === 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge bg-danger">Failed</span>
                                @elseif($payment->status === 'refunded')
                                    <span class="badge bg-secondary">Refunded</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No payments found for the selected criteria.
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
