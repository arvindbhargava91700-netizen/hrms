<div>
    @include('partials.report-styles')
    <div id="print-area">
        <div class="report-print-header mb-3">
            <h4 class="fw-bold mb-0">Bookings Report</h4>
            <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr>
        </div>

        <div class="row g-3 mb-4 d-print-none">
            <div class="col-xl-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-soft"><i class="bi bi-calendar-check"></i></div>
                    <div>
                        <div class="stat-label">Total Bookings</div>
                        <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="{{ ($stats['pending'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                    <div class="stat-icon bg-warning-soft"><i class="bi bi-clock-history text-warning"></i></div>
                    <div>
                        <div class="stat-label">Pending Bookings</div>
                        <div class="stat-value">{{ number_format($stats['pending'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="border-left: 4px solid var(--bs-info);">
                    <div class="stat-icon bg-info-soft"><i class="bi bi-check-circle text-info"></i></div>
                    <div>
                        <div class="stat-label">Confirmed Bookings</div>
                        <div class="stat-value">{{ number_format($stats['confirmed'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                    <div class="stat-icon bg-success-soft"><i class="bi bi-check2-all text-success"></i></div>
                    <div>
                        <div class="stat-label">Completed Bookings</div>
                        <div class="stat-value">{{ number_format($stats['completed'] ?? 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
        @if(session('success'))
            <div class="alert alert-success d-print-none">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-print-none">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 d-print-none">
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <div class="search-box" style="min-width: 250px;">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" wire:model.live="search" placeholder="Search customer...">
                    </div>
                    <div style="min-width: 200px;">
                        <select class="form-select" wire:model.live="bookingFilter">
                            <option value="">All Booking Status</option>
                            <option value="pending_otp">Pending OTP</option>
                            <option value="confirmed">Confirmed (Awaiting Payment)</option>
                            <option value="active">Active</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="btn-group shadow-sm ms-lg-auto align-self-start align-self-lg-center mt-2 mt-lg-0">
                    <button class="btn d-flex align-items-center justify-content-center gap-2 px-3 py-2 {{ $viewMode === 'list' ? 'btn-primary' : 'btn-light border' }}" wire:click="switchView('list')">
                        <i class="bi bi-list-ul"></i> <span>List</span>
                    </button>
                    <button class="btn d-flex align-items-center justify-content-center gap-2 px-3 py-2 {{ $viewMode === 'calendar' ? 'btn-primary' : 'btn-light border' }}" wire:click="switchView('calendar')">
                        <i class="bi bi-calendar3"></i> <span>Calendar</span>
                    </button>
                </div>
            </div>
            
            @if($viewMode === 'list')
            <div class="table-responsive">
                <table class="table table-feetrack mb-0">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Listing & Plan</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Booking</th>
                            <th class="text-end d-print-none">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td>
                                    <div class="fw-600">{{ $booking->customer->name ?? 'Unknown' }}</div>
                                    <div class="text-muted fs-12">{{ \App\Helpers\AdminHelper::maskContact('mobile', $booking->customer->mobile ?? '' ) }}</div>
                                </td>
                                <td>
                                    <div class="fw-500">{{ $booking->package->listing->title ?? 'N/A' }}</div>
                                    <div class="text-muted fs-12">{{ $booking->package->name ?? '' }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">₹{{ number_format((float) $booking->final_amount, 2) }}</div>
                                    @if($booking->security_deposit > 0)
                                        <div class="text-muted fs-12 mt-1" title="Reserve Amount">
                                            <i class="bi bi-shield-lock text-warning"></i> Reserve: ₹{{ number_format($booking->security_deposit, 2) }}
                                        </div>
                                    @endif
                                    <div class="small fw-semibold text-primary mt-1" title="Partner Net Earning">Net: ₹{{ number_format($booking->net_earn, 2) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ str_replace('_', ' ', $booking->payment_method ?? '—') }}</span>
                                    @if($booking->auto_renew)
                                        <div class="text-muted fs-12 mt-1"><i class="bi bi-arrow-repeat"></i> Auto-pay</div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $badge = match($booking->status) {
                                            'pending_otp' => 'warning',
                                            'confirmed' => 'info',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ str_replace('_', ' ', $booking->status) }}</span>
                                    @if($booking->status === 'confirmed')
                                        <div class="text-muted fs-12 mt-1">Waiting for customer payment</div>
                                    @endif
                                </td>
                                <td class="text-end d-print-none">
                                    <div class="d-flex justify-content-end gap-2 flex-wrap">
                                        <button class="btn btn-icon btn-outline-secondary btn-sm" wire:click="viewBooking('{{ $booking->id }}')" title="View">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        @if($booking->status === 'pending_otp')
                                            <button class="btn btn-sm btn-outline-primary" wire:click="sendOtp('{{ $booking->id }}')" wire:loading.attr="disabled" wire:target="sendOtp('{{ $booking->id }}')">
                                                <span wire:loading wire:target="sendOtp('{{ $booking->id }}')" class="ft-btn-spinner dark"></span>
                                                <i class="bi bi-send" wire:loading.remove wire:target="sendOtp('{{ $booking->id }}')"></i> Send OTP
                                            </button>
                                            <button class="btn btn-sm btn-success" wire:click="openVerify('{{ $booking->id }}')" wire:loading.attr="disabled" wire:target="openVerify('{{ $booking->id }}')">
                                                <span wire:loading wire:target="openVerify('{{ $booking->id }}')" class="ft-btn-spinner"></span>
                                                <i class="bi bi-shield-check" wire:loading.remove wire:target="openVerify('{{ $booking->id }}')"></i> Enter OTP
                                            </button>
                                        @endif
                                        @if($booking->status === 'confirmed' && $booking->payment_method === 'cash')
                                            <button class="btn btn-sm btn-success" wire:click="markPaymentReceived('{{ $booking->id }}')" wire:loading.attr="disabled" wire:target="markPaymentReceived('{{ $booking->id }}')">
                                                <span wire:loading wire:target="markPaymentReceived('{{ $booking->id }}')" class="ft-btn-spinner"></span>
                                                <i class="bi bi-cash" wire:loading.remove wire:target="markPaymentReceived('{{ $booking->id }}')"></i> Payment Received
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">No bookings found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($bookings->hasPages())
                <div class="card-footer bg-transparent border-top p-3 d-print-none">
                    {{ $bookings->links() }}
                </div>
            @endif
            @elseif($viewMode === 'calendar')
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
                    <button wire:click="previousMonth" class="btn btn-sm btn-outline-secondary px-3"><i class="bi bi-chevron-left me-1"></i> Previous</button>
                    <h5 class="mb-0 fw-bold text-center">{{ \Carbon\Carbon::create($currentYear, $currentMonth)->format('F Y') }}</h5>
                    <button wire:click="nextMonth" class="btn btn-sm btn-outline-secondary px-3">Next <i class="bi bi-chevron-right ms-1"></i></button>
                </div>
                <div class="d-block d-md-none p-2 text-center text-muted small bg-light border-bottom">
                    <i class="bi bi-arrows-expand me-1"></i> Swipe to see full calendar
                </div>
                <div class="calendar-wrapper" style="overflow-x: auto;">
                    <div style="min-width: 800px;">
                        <div style="display: grid; grid-template-columns: repeat(7, 1fr);">
                            <!-- Days of week -->
                            <div class="border-bottom border-end text-center fw-600 text-muted bg-light py-2">Sunday</div>
                            <div class="border-bottom border-end text-center fw-600 text-muted bg-light py-2">Monday</div>
                            <div class="border-bottom border-end text-center fw-600 text-muted bg-light py-2">Tuesday</div>
                            <div class="border-bottom border-end text-center fw-600 text-muted bg-light py-2">Wednesday</div>
                            <div class="border-bottom border-end text-center fw-600 text-muted bg-light py-2">Thursday</div>
                            <div class="border-bottom border-end text-center fw-600 text-muted bg-light py-2">Friday</div>
                            <div class="border-bottom border-end text-center fw-600 text-muted bg-light py-2">Saturday</div>
                            
                            <!-- Empty padding days -->
                            @for($i = 0; $i < $startDayOfWeek; $i++)
                                <div class="border-end border-bottom bg-light bg-opacity-50" style="min-height: 120px;"></div>
                            @endfor
                            
                            <!-- Actual days -->
                            @for($day = 1; $day <= $daysInMonth; $day++)
                                @php 
                                    $dateStr = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
                                    $count = $monthBookings[$dateStr] ?? 0;
                                    $isToday = \Carbon\Carbon::create($currentYear, $currentMonth, $day)->isToday();
                                @endphp
                                
                                <div class="border-end border-bottom p-2 position-relative {{ $isToday ? 'bg-primary bg-opacity-10' : '' }}" 
                                     style="min-height: 120px; cursor: {{ $count > 0 ? 'pointer' : 'default' }}; transition: background 0.2s;"
                                     @if($count > 0) wire:click="viewDateBookings('{{ $dateStr }}')" onmouseover="this.classList.add('bg-light')" onmouseout="this.classList.remove('bg-light')" @endif>
                                    
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="badge {{ $isToday ? 'bg-primary shadow-sm' : 'bg-secondary bg-opacity-25 text-dark' }} fs-6 rounded-pill px-2">{{ $day }}</span>
                                    </div>
                                    
                                    @if($count > 0)
                                        <div class="mt-3 text-center">
                                            <span class="badge bg-success rounded-pill px-3 py-2 fs-6 shadow-sm">
                                                {{ $count }} {{ $count == 1 ? 'Booking' : 'Bookings' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @endfor
                            
                            <!-- Padding at end -->
                            @php $remainingDays = 7 - (($daysInMonth + $startDayOfWeek) % 7); @endphp
                            @if($remainingDays < 7)
                                @for($i = 0; $i < $remainingDays; $i++)
                                    <div class="border-end border-bottom bg-light bg-opacity-50" style="min-height: 120px;"></div>
                                @endfor
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Verify OTP Modal --}}
    @if($verifyingId)
    <div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="closeVerify">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Booking with OTP</h5>
                    <button type="button" class="btn-close" wire:click="closeVerify"></button>
                </div>
                <form wire:submit="verifyOtp">
                    <div class="modal-body">
                        <p class="text-muted small mb-3">
                            Send OTP to the customer first. Ask them for the 6-digit code received on their mobile, then enter it here to confirm the booking.
                        </p>
                        <label class="form-label">Customer OTP</label>
                        <input type="text" class="form-control form-control-lg text-center @error('otp') is-invalid @enderror"
                               wire:model="otp" maxlength="6" placeholder="000000" autocomplete="one-time-code" inputmode="numeric">
                        @error('otp') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeVerify">Cancel</button>
                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled" wire:target="verifyOtp">
                            <span wire:loading wire:target="verifyOtp" class="ft-btn-spinner"></span>
                            <span wire:loading.remove wire:target="verifyOtp">Confirm Booking</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Booking Detail Modal --}}
    @if($viewingBooking)
    <div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="$set('viewingId', null)">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" wire:click="$set('viewingId', null)"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm mb-3">
                        <tr><td class="text-muted" style="width:30%">Customer</td><td>{{ $viewingBooking->customer->name ?? '—' }}<div class="small text-muted">{{ \App\Helpers\AdminHelper::maskContact('mobile', $viewingBooking->customer->mobile ?? '' ) }}</div></td></tr>
                        <tr><td class="text-muted">Listing</td><td>{{ $viewingBooking->package->listing->title ?? '—' }}</td></tr>
                        <tr><td class="text-muted">Plan</td><td>{{ $viewingBooking->package->name ?? '—' }}</td></tr>
                        @if($viewingBooking->room_id)
                        <tr><td class="text-muted">Room</td><td>{{ $viewingBooking->room->room_number ?? '—' }} ({{ $viewingBooking->room->room_type ?? '' }})</td></tr>
                        @endif
                        @if($viewingBooking->shift_id)
                        <tr><td class="text-muted">Shift</td><td>{{ $viewingBooking->shift->shift_label ?? '—' }} ({{ \Carbon\Carbon::parse($viewingBooking->shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($viewingBooking->shift->end_time)->format('h:i A') }})</td></tr>
                        @endif
                        @if($viewingBooking->trainer_ids)
                        <tr><td class="text-muted">Trainers</td><td>
                            @php
                                $trainerNames = \App\Models\ListingTrainer::whereIn('id', $viewingBooking->trainer_ids)->pluck('name')->implode(', ');
                            @endphp
                            {{ $trainerNames ?: '—' }}
                        </td></tr>
                        @endif
                        <tr><td class="text-muted">Subtotal</td><td>₹{{ number_format((float) ($viewingBooking->invoices->first()?->amount ?? $viewingBooking->package->price), 2) }}</td></tr>
                        @if($viewingBooking->security_deposit > 0)
                        <tr><td class="text-muted">Reserve Amount</td><td><i class="bi bi-shield-lock text-warning"></i> ₹{{ number_format((float) $viewingBooking->final_amount, 2) }}</td></tr>
                        @endif
                        <tr><td class="text-muted">Amount</td><td>₹{{ number_format((float) $viewingBooking->final_amount, 2) }}</td></tr>
                        <tr><td class="text-muted">Payment Method</td><td>{{ str_replace('_', ' ', $viewingBooking->payment_method ?? '—') }}</td></tr>
                        <tr><td class="text-muted">Booking Status</td><td>{{ str_replace('_', ' ', $viewingBooking->status) }}</td></tr>
                        @if($viewingBooking->otp_verified_at)
                        <tr><td class="text-muted">OTP Verified</td><td>{{ $viewingBooking->otp_verified_at->format('d M Y H:i') }}</td></tr>
                        @endif
                    </table>
                    @if($viewingBooking->invoices->count())
                        <h6 class="mt-4 mb-2 fw-bold text-dark border-bottom pb-2">Invoices</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mt-2 mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewingBooking->invoices as $inv)
                                        <tr>
                                            <td class="fw-bold text-primary">{{ $inv->invoice_number }}</td>
                                            <td class="fw-bold">₹{{ number_format($inv->total, 2) }}</td>
                                            <td>
                                                @if($inv->status === 'paid')
                                                    <span class="badge bg-success">Paid</span>
                                                @elseif($inv->status === 'cancelled')
                                                    <span class="badge bg-danger">Cancelled</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">{{ ucfirst($inv->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.invoices.receipt.download', ['type' => 'invoice', 'id' => $inv->id]) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="Download Receipt">
                                                    <i class="bi bi-download small"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @if($viewingBooking->payments->count())
                        <h6 class="mt-3 mb-2 fw-bold text-dark border-bottom pb-2">Payments</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mt-2 mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Method</th>
                                        <th>Gross Amount</th>
                                        <th>Platform Fee</th>
                                        <th>Net to Partner</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewingBooking->payments as $pay)
                                        @php
                                            $superAdminId = \App\Models\User::where('role', 'super_admin')->first()->id ?? auth()->id();
                                            $platFee = \App\Models\WalletTransaction::where('reference_id', $viewingBooking->id)
                                                ->where('user_id', $superAdminId)
                                                ->where('type', 'credit')
                                                ->value('amount') ?? 0;
                                            $netAmt = $pay->amount - $platFee;
                                        @endphp
                                        <tr>
                                            <td>{{ ucfirst($pay->gateway) }}</td>
                                            <td class="text-muted">₹{{ number_format($pay->amount, 2) }}</td>
                                            <td class="text-success">₹{{ number_format($platFee, 2) }}</td>
                                            <td class="text-info fw-bold">₹{{ number_format($netAmt, 2) }}</td>
                                            <td>
                                                @if($pay->status === 'paid' || $pay->status === 'success')
                                                    <span class="badge bg-success">Paid</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">{{ ucfirst($pay->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.invoices.receipt.download', ['type' => 'payment', 'id' => $pay->id]) }}" class="btn btn-sm btn-outline-primary py-0 px-2" title="Download Receipt">
                                                    <i class="bi bi-download small"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="$set('viewingId', null)">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Date Bookings Modal --}}
    @if($selectedDate)
    <div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="closeDateBookings">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>
                        Bookings on {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeDateBookings"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Customer</th>
                                    <th>Listing & Plan</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dateBookings as $booking)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $booking->customer->name ?? 'Unknown' }}</div>
                                            <div class="text-muted small">{{ \App\Helpers\AdminHelper::maskContact('mobile', $booking->customer->mobile ?? '' ) }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $booking->package->listing->title ?? 'N/A' }}</div>
                                            <div class="text-muted small">{{ $booking->package->name ?? '' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">₹{{ number_format((float) $booking->final_amount, 2) }}</div>
                                            <div class="small fw-semibold text-primary mt-1" title="Partner Net Earning">Net: ₹{{ number_format($booking->net_earn, 2) }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $badge = match($booking->status) {
                                                    'pending_otp' => 'warning',
                                                    'confirmed' => 'info',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $badge }}">{{ str_replace('_', ' ', $booking->status) }}</span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-sm btn-light border" wire:click="viewBooking('{{ $booking->id }}')">
                                                View Details <i class="bi bi-arrow-right ms-1"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                            No bookings on this date.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary px-4" wire:click="closeDateBookings">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
