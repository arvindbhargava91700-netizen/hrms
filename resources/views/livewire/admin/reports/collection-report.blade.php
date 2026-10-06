<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Collection Report</h4>
            <p class="text-muted mb-0 small">Overview of all system collections with date filters.</p>
        </div>
        <div>
            <button wire:click="exportCsv" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-download me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-2">Gross Collected</h6>
                    <h3 class="fw-bold text-primary mb-0">₹{{ number_format($summary['total_collections'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-2">Platform Fee Earned</h6>
                    <h3 class="fw-bold text-success mb-0">₹{{ number_format($summary['platform_fee'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-2">Net to Partners</h6>
                    <h3 class="fw-bold text-info mb-0">₹{{ number_format($summary['net_partner'], 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <h6 class="text-muted mb-2">Failed/Pending</h6>
                    <h3 class="fw-bold text-warning mb-0">₹{{ number_format($summary['failed_pending'], 2) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by Transaction ID or Customer...">
                </div>
                
                <div class="col-md-8 d-flex justify-content-end gap-2 align-items-center flex-wrap">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-secondary {{ $dateFilter === 'today' ? 'active' : '' }}" wire:click="setDateFilter('today')">Today</button>
                        <button type="button" class="btn btn-outline-secondary {{ $dateFilter === 'yesterday' ? 'active' : '' }}" wire:click="setDateFilter('yesterday')">Yesterday</button>
                        <button type="button" class="btn btn-outline-secondary {{ $dateFilter === 'last_month' ? 'active' : '' }}" wire:click="setDateFilter('last_month')">Last Month</button>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <input type="date" wire:model.live="startDate" class="form-control form-control-sm" style="max-width: 130px;">
                        <span class="text-muted">to</span>
                        <input type="date" wire:model.live="endDate" class="form-control form-control-sm" style="max-width: 130px;">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Gross Amount</th>
                            <th>Platform Fee</th>
                            <th>Net to Partner</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reportData as $payment)
                        <tr>
                            <td><span class="fw-500 text-primary">{{ $payment->receipt_number ?? $payment->gateway_ref ?? 'N/A' }}</span></td>
                            <td>
                                @if($payment->booking && $payment->booking->customer)
                                    {{ $payment->booking->customer->name }}
                                @elseif($payment->subscription && $payment->subscription->customer)
                                    {{ $payment->subscription->customer->name }}
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="fw-bold text-muted">₹{{ number_format($payment->amount, 2) }}</td>
                            @php
                                $platFee = $this->getPlatformFee($payment->booking_id ?? $payment->subscription_id);
                                $netAmt = $payment->amount - $platFee;
                            @endphp
                            <td class="fw-bold text-success">₹{{ number_format($platFee, 2) }}</td>
                            <td class="fw-bold text-info">₹{{ number_format($netAmt, 2) }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ ucfirst($payment->gateway ?? 'N/A') }}</span>
                            </td>
                            <td>
                                @if($payment->status === 'success' || $payment->status === 'completed' || $payment->status === 'paid')
                                    <span class="badge bg-success-subtle text-success">Successful</span>
                                @elseif($payment->status === 'failed')
                                    <span class="badge bg-danger-subtle text-danger">Failed</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size:13px;">{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No collections found for the selected criteria.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $reportData->links() }}
            </div>
        </div>
    </div>
</div>
