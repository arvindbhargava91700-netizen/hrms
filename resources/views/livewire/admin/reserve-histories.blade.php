<div>
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-header bg-white border-bottom p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h5 class="mb-0 fw-bold"><i class="bi bi-shield-lock text-primary me-2"></i> Reserve History</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <input type="text" class="form-control form-control-sm w-auto" wire:model.live="search" placeholder="Search ID/Name...">
                    <select class="form-select form-select-sm w-auto" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="active">Active (Held)</option>
                        <option value="refunded_wallet">Refunded (Wallet)</option>
                        <option value="refunded_cash">Refunded (Cash)</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-feetrack mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Partner</th>
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
                                    @if($rh->partner)
                                        <div class="fw-bold">{{ $rh->partner->name }}</div>
                                        <div class="small text-muted">{{ \App\Helpers\AdminHelper::maskContact('email', $rh->partner->email) }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if($rh->customer)
                                        <div class="fw-bold">{{ $rh->customer->name }}</div>
                                        <div class="small text-muted">{{ \App\Helpers\AdminHelper::maskContact('mobile', $rh->customer->mobile) }}</div>
                                    @else
                                        -
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
                                <td>
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
                            <tr><td colspan="6" class="text-center py-4 text-muted">No security deposits currently held.</td></tr>
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
                        <tr><td class="text-muted">Customer</td><td>{{ $viewingBooking->customer->name ?? '—' }}<div class="small text-muted">{{ \App\Helpers\AdminHelper::maskContact('mobile', $viewingBooking->customer->mobile ?? '' ) }}</div></td></tr>
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
