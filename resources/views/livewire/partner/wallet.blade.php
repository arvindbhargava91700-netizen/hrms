<div>
<style>
    /* Professional Tab Styling */
    .nav-tabs .nav-link {
        color: #64748b;
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        padding: 0.75rem 1.25rem;
    }
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        color: #1e293b;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom: 2px solid #0d6efd;
        background: transparent;
        font-weight: 700;
    }
    .nav-tabs .nav-link:focus {
        outline: none;
        box-shadow: none;
    }
    /* Premium Wallet Card Gradient */
    .wallet-card-gradient {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        border: none;
    }
</style>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card wallet-card-gradient text-white shadow-sm rounded-4">
                <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-md-center p-4">
                    <div>
                        <h6 class="text-white-50 mb-1 fw-normal">Available Balance</h6>
                        <h2 class="text-white fw-bold mb-0 display-6">₹{{ number_format($user->wallet_balance, 2) }}</h2>
                    </div>
                    @if(auth()->user()->canAccess('wallet_action'))
                        <div class="mt-3 mt-md-0 d-flex gap-2">
                            <button class="btn btn-primary px-4 fw-600 shadow-sm border-0" data-bs-toggle="modal" data-bs-target="#rechargeModal" style="background-color: #0d6efd;">
                                <i class="bi bi-plus-circle me-1"></i> Recharge Wallet
                            </button>
                            <button class="btn btn-outline-light px-4 fw-600" data-bs-toggle="modal" data-bs-target="#withdrawModal" {{ $user->wallet_balance <= 0 ? 'disabled' : '' }} style="border-color: rgba(255,255,255,0.4);">
                                <i class="bi bi-bank me-1"></i> Request Withdrawal
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
            <ul class="nav nav-tabs border-bottom" id="historyTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="ledger-tab" data-bs-toggle="tab" data-bs-target="#ledger-tab-pane" type="button" role="tab" aria-controls="ledger-tab-pane" aria-selected="true">
                        Wallet Ledger
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="withdrawals-tab" data-bs-toggle="tab" data-bs-target="#withdrawals-tab-pane" type="button" role="tab" aria-controls="withdrawals-tab-pane" aria-selected="false">
                        Withdrawal History
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="recharges-tab" data-bs-toggle="tab" data-bs-target="#recharges-tab-pane" type="button" role="tab" aria-controls="recharges-tab-pane" aria-selected="false">
                        Recharge History
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="reserves-tab" data-bs-toggle="tab" data-bs-target="#reserves-tab-pane" type="button" role="tab" aria-controls="reserves-tab-pane" aria-selected="false">
                        Reserve History
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-0 border-top">
            <div class="tab-content" id="historyTabsContent">
                <!-- Ledger Tab -->
                <div class="tab-pane fade show active" id="ledger-tab-pane" role="tabpanel" aria-labelledby="ledger-tab" tabindex="0">
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                         <select class="form-select form-select-sm w-auto" wire:model.live="transactionTypeFilter">
                            <option value="">All Transactions</option>
                            <option value="withdrawal">Withdrawals Only</option>
                        </select>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-feetrack mb-0">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $tx)
                                    <tr>
                                        <td>{{ $tx->created_at->format('d M, Y h:i A') }}</td>
                                        <td class="fw-bold">{{ $tx->description }}</td>
                                        <td class="fw-bold text-end {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                            {{ $tx->type === 'credit' ? '+' : '-' }} ₹{{ $tx->amount }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-4 text-muted">No transactions found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($transactions->hasPages())
                    <div class="card-footer py-2 border-top-0 bg-white">
                        {{ $transactions->links() }}
                    </div>
                    @endif
                </div>

                <!-- Withdrawals Tab -->
                <div class="tab-pane fade" id="withdrawals-tab-pane" role="tabpanel" aria-labelledby="withdrawals-tab" tabindex="0">
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex gap-2 flex-wrap">
                            <input type="date" class="form-control form-control-sm w-auto" wire:model.live="startDate" placeholder="Start Date">
                            <input type="date" class="form-control form-control-sm w-auto" wire:model.live="endDate" placeholder="End Date">
                            <select class="form-select form-select-sm w-auto" wire:model.live="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        @if(auth()->user()->canAccess('wallet_action'))
                            <button class="btn btn-sm btn-outline-secondary" wire:click="exportWithdrawals">
                                <i class="bi bi-download me-1"></i> Export
                            </button>
                        @endif
                    </div>
                    <div class="table-responsive">
                        <table class="table table-feetrack mb-0">
                            <thead>
                                <tr>
                                    <th>Requested On</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $req)
                                    <tr>
                                        <td>{{ $req->created_at->format('d M, Y') }}</td>
                                        <td class="fw-bold">₹{{ $req->amount }}</td>
                                        <td>{{ $req->payment_method }}</td>
                                        <td class="text-end">
                                            @if($req->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($req->status === 'approved')
                                                <span class="badge bg-success">Approved ({{ $req->paid_at?->format('d M, Y') }})</span>
                                            @else
                                                <span class="badge bg-danger">Rejected</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No withdrawal history found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($withdrawals->hasPages())
                    <div class="card-footer py-2 border-top-0 bg-white">
                        {{ $withdrawals->links() }}
                    </div>
                    @endif
                </div>

                <!-- Recharges Tab -->
                <div class="tab-pane fade" id="recharges-tab-pane" role="tabpanel" aria-labelledby="recharges-tab" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-feetrack mb-0">
                            <thead>
                                <tr>
                                    <th>Requested On</th>
                                    <th>Amount</th>
                                    <th>Transaction ID</th>
                                    <th>Notes</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rechargeRequests as $req)
                                    <tr>
                                        <td>{{ $req->created_at->format('d M, Y h:i A') }}</td>
                                        <td class="fw-bold text-success">+₹{{ $req->amount }}</td>
                                        <td>{{ $req->transaction_id }}</td>
                                        <td class="text-muted small">{{ $req->notes ?: '-' }}</td>
                                        <td class="text-end">
                                            @if($req->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($req->status === 'approved')
                                                <span class="badge bg-success">Approved ({{ $req->approved_at?->format('d M, Y') }})</span>
                                            @else
                                                <span class="badge bg-danger">Rejected</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No recharge requests found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($rechargeRequests->hasPages())
                    <div class="card-footer py-2 border-top-0 bg-white">
                        {{ $rechargeRequests->links() }}
                    </div>
                    @endif
                </div>

                <!-- Reserve History Tab -->
                <div class="tab-pane fade" id="reserves-tab-pane" role="tabpanel" aria-labelledby="reserves-tab" tabindex="0">
                    <div class="table-responsive">
                        <table class="table table-feetrack mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Booking</th>
                                    <th>Amount</th>
                                    <th class="text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reserveHistories as $rh)
                                    <tr>
                                        <td>{{ $rh->created_at->format('d M, Y h:i A') }}</td>
                                        <td>
                                            @if($rh->customer)
                                                <div class="d-flex align-items-center gap-2">
                                                    @if($rh->customer->profile_image)
                                                        <img src="{{ Storage::url($rh->customer->profile_image) }}" alt="Customer" class="rounded-circle" width="30" height="30" style="object-fit:cover;">
                                                    @else
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:30px; height:30px; font-size:12px;">
                                                            {{ strtoupper(substr($rh->customer->name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">{{ $rh->customer->name }}</div>
                                                        <div class="small text-muted">{{ $rh->customer->mobile }}</div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">Unknown</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rh->booking)
                                                <div class="mb-1">{{ $rh->booking->package?->listing?->title }}</div>
                                                <button wire:click="viewBooking('{{ $rh->booking_id }}')" class="btn btn-sm btn-outline-primary py-0" style="font-size: 0.75rem;">
                                                    <i class="bi bi-eye"></i> View Booking
                                                </button>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="fw-bold">
                                            @if($rh->booking)
                                                ₹{{ number_format($rh->amount, 2) }}
                                            @else
                                                ₹{{ number_format($rh->amount, 2) }}
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($rh->status === 'active')
                                                <span class="badge bg-warning text-dark">Active (Held)</span>
                                            @elseif($rh->status === 'refunded_wallet')
                                                <span class="badge bg-success">Refunded (Wallet)</span>
                                            @elseif($rh->status === 'refunded_cash')
                                                <span class="badge bg-info">Refunded (Cash)</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($rh->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-4 text-muted">No security deposits currently held.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($reserveHistories->hasPages())
                    <div class="card-footer py-2 border-top-0 bg-white">
                        {{ $reserveHistories->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Withdraw Modal -->
    <div wire:ignore.self class="modal fade" id="withdrawModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <form wire:submit.prevent="requestWithdrawal">

                    <div class="modal-header">
                        <h5 class="modal-title">Request Withdrawal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Amount (₹)</label>
                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                wire:model="amount"
                                max="{{ $user->wallet_balance }}"
                            >
                            @error('amount')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method / Details</label>
                            <input
                                type="text"
                                class="form-control"
                                wire:model="payment_method"
                                placeholder="e.g. Bank Account / UPI ID"
                            >
                            @error('payment_method')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea
                                class="form-control"
                                rows="2"
                                wire:model="notes"
                            ></textarea>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="requestWithdrawal">
                                Submit Request
                            </span>

                            <span wire:loading wire:target="requestWithdrawal">
                                Submitting...
                            </span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- Recharge Modal -->
    <div wire:ignore.self class="modal fade" id="rechargeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <form wire:submit.prevent="initiateRecharge">

                    <div class="modal-header">
                        <h5 class="modal-title">Recharge Wallet (Add Funds)</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Amount (₹)</label>
                            <input
                                type="number"
                                step="0.01"
                                class="form-control"
                                wire:model.live="recharge_amount"
                                placeholder="e.g. 1000"
                                required
                            >
                            @error('recharge_amount')
                                <span class="text-danger small">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="alert alert-info py-2 small mb-0">
                            You will be redirected to the payment gateway to complete this transaction securely.
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove wire:target="initiateRecharge">
                                Proceed to Pay
                            </span>

                            <span wire:loading wire:target="initiateRecharge">
                                Redirecting...
                            </span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- View Booking Modal -->
    @if($viewingBooking)
    <div class="modal fade show bg-dark bg-opacity-50" style="display: block;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" wire:click="closeBookingView"></button>
                </div>
                <div class="modal-body p-4">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" style="width:35%">Booking ID</td><td class="fw-bold">{{ $viewingBooking->id }}</td></tr>
                        <tr><td class="text-muted">Customer</td><td>{{ $viewingBooking->customer->name ?? '—' }}<div class="small text-muted">{{ $viewingBooking->customer->mobile ?? '' }}</div></td></tr>
                        <tr><td class="text-muted">Listing</td><td>{{ $viewingBooking->package->listing->title ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Plan</td><td>{{ $viewingBooking->package->name ?? '—' }}</td></tr>
                        @if($viewingBooking->room_id)
                        <tr><td class="text-muted">Room</td><td>{{ $viewingBooking->room->room_number ?? '—' }} ({{ $viewingBooking->room->room_type ?? '' }})</td></tr>
                        @endif
                        @if($viewingBooking->shift_id)
                        <tr><td class="text-muted">Shift</td><td>{{ $viewingBooking->shift->shift_label ?? '—' }} ({{ \Carbon\Carbon::parse($viewingBooking->shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($viewingBooking->shift->end_time)->format('h:i A') }})</td></tr>
                        @endif
                        <tr><td class="text-muted">Subtotal</td><td>₹{{ number_format((float) ($viewingBooking->invoices->first()?->amount ?? $viewingBooking->package->price), 2) }}</td></tr>
                        @if($viewingBooking->discount_amount > 0)
                        <tr><td class="text-muted text-danger">Discount</td><td class="text-danger">- ₹{{ number_format((float) $viewingBooking->discount_amount, 2) }}</td></tr>
                        @endif
                        <tr><td class="text-muted">Final Amount</td><td class="fw-bold">₹{{ number_format((float) $viewingBooking->final_amount, 2) }}</td></tr>
                        <tr><td class="text-muted">Payment Method</td><td>{{ str_replace('_', ' ', $viewingBooking->payment_method ?? '—') }}</td></tr>
                        <tr><td class="text-muted">Booking Status</td><td>{{ str_replace('_', ' ', $viewingBooking->status) }}</td></tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="closeBookingView">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>