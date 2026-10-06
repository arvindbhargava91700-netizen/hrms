<div>
    @include('partials.report-styles')
    <div id="print-area">
        <div class="report-print-header mb-3">
            <h4 class="fw-bold mb-0">Withdrawals Report</h4>
            <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-soft"><i class="bi bi-bank"></i></div>
                    <div>
                        <div class="stat-label">Total Requests</div>
                        <div class="stat-value">{{ number_format($stats['total_requests'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                    <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle text-success"></i></div>
                    <div>
                        <div class="stat-label">Approved Amount</div>
                        <div class="stat-value text-success">₹{{ number_format($stats['approved_amount'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="{{ ($stats['pending_amount'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                    <div class="stat-icon bg-warning-soft"><i class="bi bi-hourglass-split text-warning"></i></div>
                    <div>
                        <div class="stat-label">Pending Amount</div>
                        <div class="stat-value">₹{{ number_format($stats['pending_amount'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="{{ ($stats['pending_count'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                    <div class="stat-icon bg-warning-soft"><i class="bi bi-exclamation-circle text-warning"></i></div>
                    <div>
                        <div class="stat-label">Pending Requests</div>
                        <div class="stat-value">{{ number_format($stats['pending_count'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4 d-print-none">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label text-muted small text-uppercase fw-bold mb-1">Search Partner</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" placeholder="Name, Email, Mobile..." wire:model.live.debounce.500ms="search">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-muted small text-uppercase fw-bold mb-1">Status</label>
                        <select class="form-select" wire:model.live="status">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small text-uppercase fw-bold mb-1">Start Date</label>
                        <input type="date" class="form-control" wire:model.live="startDate">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label text-muted small text-uppercase fw-bold mb-1">End Date</label>
                        <input type="date" class="form-control" wire:model.live="endDate">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="ms-sm-auto d-flex gap-2">
                            <button wire:click="export" class="btn btn-outline-success">
                                <i class="bi bi-download me-1"></i> Export CSV
                            </button>
                            <button class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header border-bottom-0 pb-0">
                <h5 class="mb-0">Withdrawal Requests</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-feetrack mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Partner</th>
                                <th>Amount</th>
                                <th>Requested On</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th class="d-print-none">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($withdrawals as $req)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $req->user->avatar_url }}" alt="" class="rounded-circle" width="36" height="36">
                                            <div>
                                                <div class="fw-600">{{ $req->user->name }}</div>
                                                <div class="text-muted small">{{ \App\Helpers\AdminHelper::maskContact('mobile', $req->user->mobile) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="fw-bold fs-15 text-dark">₹{{ number_format($req->amount, 2) }}</td>
                                    <td>
                                        <div class="fw-500">{{ $req->created_at->format('d M, Y') }}</div>
                                        <div class="text-muted small">{{ $req->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $req->payment_method ?? 'Bank/UPI' }}</span>
                                    </td>
                                    <td>
                                        @if($req->status === 'pending')
                                            <span class="badge bg-warning-soft text-warning px-2 py-1"><i class="bi bi-clock me-1"></i>Pending</span>
                                        @elseif($req->status === 'approved')
                                            <span class="badge bg-success-soft text-success px-2 py-1"><i class="bi bi-check-circle me-1"></i>Approved</span>
                                            <div class="small text-muted mt-1">{{ $req->paid_at?->format('d M, Y') }}</div>
                                        @else
                                            <span class="badge bg-danger-soft text-danger px-2 py-1"><i class="bi bi-x-circle me-1"></i>Rejected</span>
                                        @endif
                                    </td>
                                    <td class="d-print-none">
                                        @if($req->status === 'pending')
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                    Actions <i class="bi bi-chevron-down ms-1"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li>
                                                        <button class="dropdown-item text-success fw-500" wire:click="updateStatus('{{ $req->id }}', 'approved')">
                                                            <i class="bi bi-check-circle me-2"></i> Approve (Apply Platform Fee)
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item text-danger fw-500" wire:click="updateStatus('{{ $req->id }}', 'rejected')">
                                                            <i class="bi bi-x-circle me-2"></i> Reject & Refund
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        @else
                                            <span class="text-muted small">Processed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-bank fs-1 text-light mb-3 d-block"></i>
                                    No withdrawal requests found matching your filters.
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($withdrawals->hasPages())
                <div class="card-footer bg-transparent border-top p-3 d-print-none">
                    {{ $withdrawals->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
