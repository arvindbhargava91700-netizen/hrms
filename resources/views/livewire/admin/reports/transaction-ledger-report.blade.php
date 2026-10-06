<div>
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body p-4">
            <h5 class="card-title fw-bold mb-4" style="color: var(--primary-color);">Transaction Ledger</h5>

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

            <!-- Filters -->
            <div class="row g-3 mb-4 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted">Search</label>
                    <input type="text" class="form-control" placeholder="Search ID, user, desc..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Status / Type</label>
                    <select class="form-select" wire:model.live="typeFilter">
                        <option value="">All</option>
                        <option value="credit">Credit</option>
                        <option value="debit">Debit</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Start Date</label>
                    <input type="date" class="form-control" wire:model.live="startDate" title="Start Date">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">End Date</label>
                    <input type="date" class="form-control" wire:model.live="endDate" title="End Date">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" wire:click="export"><i class="bi bi-download"></i> Export</button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Txn ID</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $txn)
                        <tr>
                            <td>
                                <div class="fw-bold small">{{ substr($txn->id, 0, 8) }}...</div>
                            </td>
                            <td>
                                <div>{{ $txn->user->name ?? 'System' }}</div>
                                <div class="small text-muted">{{ \App\Helpers\AdminHelper::maskContact('email', $txn->user->email ?? '' ) }}</div>
                            </td>
                            <td>
                                @if(strtolower($txn->type) === 'credit')
                                    <span class="badge bg-success"><i class="bi bi-arrow-down-left"></i> Credit</span>
                                @elseif(strtolower($txn->type) === 'debit')
                                    <span class="badge bg-danger"><i class="bi bi-arrow-up-right"></i> Debit</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($txn->type) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold {{ strtolower($txn->type) === 'credit' ? 'text-success' : 'text-danger' }}">
                                    {{ strtolower($txn->type) === 'credit' ? '+' : '-' }}{{ number_format($txn->amount, 2) }}
                                </div>
                            </td>
                            <td>
                                <div class="small">{{ $txn->description }}</div>
                            </td>
                            <td>{{ $txn->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No transactions found for the selected criteria.
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
