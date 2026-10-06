<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Subscriptions Report</h4>
        <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-card-checklist"></i></div>
                <div>
                    <div class="stat-label">Total Subscriptions</div>
                    <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle text-success"></i></div>
                <div>
                    <div class="stat-label">Active Subscriptions</div>
                    <div class="stat-value">{{ number_format($stats['active'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="{{ ($stats['expiring'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-clock-history text-warning"></i></div>
                <div>
                    <div class="stat-label">Expiring Soon (7 Days)</div>
                    <div class="stat-value">{{ number_format($stats['expiring'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-danger);">
                <div class="stat-icon bg-danger-soft"><i class="bi bi-x-circle text-danger"></i></div>
                <div>
                    <div class="stat-label">Expired / Cancelled</div>
                    <div class="stat-value">{{ number_format($stats['expired'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    {{-- Header Section --}}
    <div class="card mb-4">
        <div class="card-header">
            <div class="filter-group" style="width: 100%; max-width: 100%;">
                <div style="width: 100%; max-width: 300px;">
                    <div class="search-box" style="width:100%;">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" class="form-control" wire:model.live="search" 
                               placeholder="Search by customer, email, or package...">
                    </div>
                </div>

                <select class="form-select" wire:model.live="categoryFilter" style="width:100%; max-width:180px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <select class="form-select" wire:model.live="statusFilter" style="width:100%; max-width:180px;">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="expired">Expired</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <select class="form-select" wire:model.live="sortBy" style="width:100%; max-width:160px;">
                    <option value="latest">Latest</option>
                    <option value="expiring">Expiring Soon</option>
                    <option value="oldest">Oldest</option>
                    <option value="customer">By Customer</option>
                </select>
                <div class="ms-sm-auto d-flex gap-2">
                <a href="#" wire:click.prevent="exportCsv" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
                <button class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
            </div>
        </div>

        {{-- Subscriptions Table --}}
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 30%;">Customer</th>
                        <th style="width: 25%;">Package</th>
                        <th style="width: 20%;">Period</th>
                        <th style="width: 15%;">Status</th>
                        <th class="text-end d-print-none" style="width: 10%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            {{-- Customer --}}
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $sub->customer->avatar_url }}"
                                         alt="{{ $sub->customer->name }}"
                                         class="rounded-circle flex-shrink-0"
                                         style="width:32px; height:32px; border:2px solid var(--primary);">
                                    <div>
                                        <div class="fw-600" style="font-size:13px;">{{ $sub->customer->name ?? 'Unknown' }}</div>
                                        <div class="text-muted" style="font-size:12px;">{{ $sub->customer->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Package --}}
                            <td class="align-middle">
                                <div class="fw-600" style="font-size:13px; color:var(--primary);">{{ $sub->package->name ?? 'N/A' }}</div>
                                <div class="text-muted" style="font-size:12px;">
                                    {{ $sub->package->listing->title ?? 'Ã¢â‚¬â€' }}
                                    @if($sub->package && $sub->package->listing && $sub->package->listing->category)
                                        <span class="badge bg-light text-dark ms-1" style="font-size: 10px;">{{ $sub->package->listing->category->name }}</span>
                                    @endif
                                </div>
                                <div class="text-muted mt-1" style="font-size:11px;">
                                    Booking ID: <strong>#{{ substr($sub->booking_id, 0, 8) }}</strong>
                                </div>
                                @if($sub->package)
                                    <div style="font-size:12px; font-weight:600; color:var(--text-primary); margin-top:2px;">
                                        ₹{{ number_format($sub->package->price, 2) }}
                                    </div>
                                @endif
                            </td>

                            {{-- Period --}}
                            <td class="align-middle">
                                <div style="font-size:13px; font-weight:500;">
                                    {{ $sub->starts_at?->format('d M Y') ?? 'Ã¢â‚¬â€' }}
                                </div>
                                <div class="text-muted" style="font-size:12px;">
                                    to {{ $sub->expires_at?->format('d M Y') ?? 'Ã¢â‚¬â€' }}
                                </div>
                                @php
                                    $daysRemaining = $sub->daysRemaining();
                                    $isExpiring = $sub->isExpiringSoon(7);
                                @endphp
                                @if($sub->isActive() && $daysRemaining > 0)
                                    <div class="days-remaining{{ $isExpiring ? ' expiring' : '' }}" style="margin-top:4px; width:fit-content;">
                                        <i class="bi bi-clock-history"></i>
                                        {{ $daysRemaining }} day{{ $daysRemaining !== 1 ? 's' : '' }}
                                    </div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="status-indicator {{ $sub->status }}"></span>
                                    <span class="subscription-status-badge {{ $sub->status }}">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                </div>
                                @if($sub->grace_period_active)
                                    <div class="mt-1">
                                        <span class="badge bg-info text-dark" title="Pending Amount: Ã¢â€šÂ¹{{ $sub->pending_amount }}"><i class="bi bi-calendar-event me-1"></i>Scheduled: {{ $sub->scheduled_payment_date?->format('d M') }}</span>
                                    </div>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="align-middle text-end d-print-none">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <button class="btn btn-sm btn-outline-primary"
                                            wire:click="viewSubscription('{{ $sub->id }}')">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    @if($sub->status === 'active' || $sub->status === 'expired')
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                @if(auth()->user()->canAccess('subscription_update'))
                                                    <li>
                                                        <button class="dropdown-item text-primary fw-500"
                                                            wire:click="renewSubscriptionOnline('{{ $sub->id }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="renewSubscriptionOnline('{{ $sub->id }}')">
                                                            <i class="bi bi-credit-card me-2"></i>Renew (Online)
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item text-success fw-500"
                                                            wire:click="renewSubscriptionCash('{{ $sub->id }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="renewSubscriptionCash('{{ $sub->id }}')"
                                                            onclick="confirm('Mark this subscription as renewed with cash?') || event.stopImmediatePropagation();">
                                                            <i class="bi bi-cash me-2"></i>Renew (Cash)
                                                        </button>
                                                    </li>
                                                @endif
                                                @if($sub->status === 'active')
                                                    <li><hr class="dropdown-divider"></li>
                                                    @if($sub->grace_period_active)
                                                        <li>
                                                            <button class="dropdown-item text-success fw-500"
                                                                wire:click="markScheduledPaid('{{ $sub->id }}')"
                                                                onclick="confirm('Mark this scheduled payment as paid in cash?') || event.stopImmediatePropagation();">
                                                                <i class="bi bi-cash me-2"></i>Mark Paid
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button class="dropdown-item text-warning fw-500"
                                                                wire:click="cancelScheduledPayment('{{ $sub->id }}')"
                                                                onclick="confirm('Cancel this scheduled payment?') || event.stopImmediatePropagation();">
                                                                <i class="bi bi-x-circle me-2"></i>Cancel Schedule
                                                            </button>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <button class="dropdown-item text-info fw-500"
                                                                wire:click="openSchedulePaymentModal('{{ $sub->id }}')">
                                                                <i class="bi bi-calendar-plus me-2"></i>Schedule Pay
                                                            </button>
                                                        </li>
                                                    @endif
                                                    <li>
                                                        <button class="dropdown-item text-danger fw-500"
                                                            wire:click="cancel('{{ $sub->id }}')"
                                                            wire:loading.attr="disabled"
                                                            wire:target="cancel('{{ $sub->id }}')"
                                                            onclick="confirm('Cancel this subscription?') || event.stopImmediatePropagation();">
                                                            <i class="bi bi-x-circle me-2"></i>Cancel
                                                        </button>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox" style="font-size: 32px; opacity: 0.3; display: block; margin-bottom: 8px;"></i>
                                No subscriptions found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($subscriptions->hasPages())
            <div class="card-footer bg-transparent border-top p-3 d-print-none">
                {{ $subscriptions->links() }}
            </div>
        @endif
</div>
</div>
    {{-- Schedule Payment Modal --}}
    <div class="modal fade" id="schedulePaymentModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Schedule Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Scheduled Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" wire:model="scheduledPaymentDate" min="{{ date('Y-m-d') }}">
                        @error('scheduledPaymentDate') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pending Amount (₹)</label>
                        <input type="text" class="form-control bg-light" wire:model="scheduledPaymentAmount" readonly>
                        <div class="form-text">Amount is auto-calculated based on package price.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveScheduledPayment">Save Schedule</button>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
        $wire.on('show-schedule-modal', () => {
            let modal = new bootstrap.Modal(document.getElementById('schedulePaymentModal'));
            modal.show();
        });
        $wire.on('hide-schedule-modal', () => {
            let modalEl = document.getElementById('schedulePaymentModal');
            let modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }
        });
    </script>
    @endscript


    {{-- Subscription Detail Modal --}}
    @if($viewingSubscription)
    <div class="modal d-block d-print-none"
         style="background: rgba(0,0,0,0.55);"
         wire:click.self="closeView()">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none;">

                {{-- Header --}}
                <div class="modal-header" style="background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); color: white; border: none; padding: 16px 20px;">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <img src="{{ $viewingSubscription->customer->avatar_url }}"
                             alt="{{ $viewingSubscription->customer->name }}"
                             style="width: 42px; height: 42px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.45); object-fit: cover; flex-shrink: 0;">
                        <div>
                            <div style="font-size: 15px; font-weight: 700; color: white; line-height: 1.2;">{{ $viewingSubscription->customer->name ?? 'â€”' }}</div>
                            <div style="font-size: 12px; opacity: 0.85; margin-top: 2px;">
                                {{ \App\Helpers\AdminHelper::maskContact('email', $viewingSubscription->customer->email ?? 'â€”') }}
                                &nbsp;&middot;&nbsp;
                                <span class="badge" style="background: rgba(255,255,255,0.2); font-size: 10px;">{{ ucfirst($viewingSubscription->customer->role) }}</span>
                            </div>
                        </div>
                        <span class="ms-auto badge"
                              style="background: {{ $viewingSubscription->status === 'active' ? 'rgba(16,185,129,0.3)' : 'rgba(239,68,68,0.3)' }};
                                     color: {{ $viewingSubscription->status === 'active' ? '#6EE7B7' : '#FCA5A5' }};
                                     border: 1px solid {{ $viewingSubscription->status === 'active' ? 'rgba(16,185,129,0.5)' : 'rgba(239,68,68,0.5)' }};
                                     font-size: 11px; padding: 4px 10px; border-radius: 20px;">
                            {{ ucfirst($viewingSubscription->status) }}
                        </span>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-2" wire:click="closeView()"></button>
                </div>

                {{-- Nav Tabs --}}
                <ul class="nav nav-tabs border-bottom px-3 pt-2" style="background: #f8fafc; gap: 4px;">
                    <li class="nav-item">
                        <button class="nav-link active" id="prt-ov-tab" data-bs-toggle="tab" data-bs-target="#prt-ov" type="button" style="font-size: 13px; font-weight: 600; padding: 8px 14px;">
                            <i class="bi bi-person-lines-fill me-1"></i>Overview
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" id="prt-pay-tab" data-bs-toggle="tab" data-bs-target="#prt-pay" type="button" style="font-size: 13px; font-weight: 600; padding: 8px 14px;">
                            <i class="bi bi-credit-card me-1"></i>Payments &amp; Payouts
                        </button>
                    </li>
                </ul>

                {{-- Body --}}
                <div class="modal-body p-3" style="background: #f8fafc;">
                    <div class="tab-content">

                        {{-- OVERVIEW --}}
                        <div class="tab-pane fade show active" id="prt-ov" role="tabpanel">

                            {{-- Info Bar --}}
                            <div style="background: white; border-radius: 10px; padding: 12px 14px; margin-bottom: 14px; border: 1px solid #e5e7eb; display: flex; flex-wrap: wrap; gap: 16px; font-size: 12px;">
                                <div>
                                    <div style="color: #9CA3AF; margin-bottom: 2px;">Subscription No.</div>
                                    <div style="font-weight: 700; font-family: monospace; font-size: 11px;">{{ $viewingSubscription->subscription_number ?? Str::limit($viewingSubscription->id, 16) }}</div>
                                </div>
                                @if($viewingSubscription->booking_id)
                                <div>
                                    <div style="color: #9CA3AF; margin-bottom: 2px;">Booking ID</div>
                                    <div style="font-weight: 700; font-family: monospace; font-size: 11px;">#{{ substr($viewingSubscription->booking_id, 0, 8) }}</div>
                                </div>
                                @endif
                                <div>
                                    <div style="color: #9CA3AF; margin-bottom: 2px;">Started</div>
                                    <div style="font-weight: 700;">{{ optional($viewingSubscription->starts_at)->format('d M Y') ?? 'â€”' }}</div>
                                </div>
                                <div>
                                    <div style="color: #9CA3AF; margin-bottom: 2px;">Expires</div>
                                    <div style="font-weight: 700;">{{ optional($viewingSubscription->expires_at)->format('d M Y') ?? 'â€”' }}</div>
                                </div>
                                @if($viewingSubscription->isActive())
                                    @php $daysLeft = $viewingSubscription->daysRemaining(); @endphp
                                    <div>
                                        <div style="color: #9CA3AF; margin-bottom: 2px;">Days Left</div>
                                        <span class="badge {{ $viewingSubscription->isExpiringSoon() ? 'bg-danger' : 'bg-success' }}" style="font-size: 11px;">{{ $daysLeft }} day{{ $daysLeft !== 1 ? 's' : '' }}</span>
                                    </div>
                                @endif
                                @if($viewingSubscription->auto_renew)
                                <div>
                                    <div style="color: #9CA3AF; margin-bottom: 2px;">Auto Renew</div>
                                    <span class="badge bg-info" style="font-size: 11px;"><i class="bi bi-arrow-clockwise me-1"></i>On</span>
                                </div>
                                @endif
                            </div>

                            {{-- Package --}}
                            <div style="background: white; border-radius: 10px; padding: 14px; margin-bottom: 14px; border: 1px solid #e5e7eb;">
                                <div style="font-size: 10px; text-transform: uppercase; letter-spacing: .7px; color: #9CA3AF; font-weight: 700; margin-bottom: 10px;">Package</div>
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                    <div>
                                        <div style="font-size: 15px; font-weight: 700; color: #111827;">{{ $viewingSubscription->package->name ?? 'â€”' }}</div>
                                        <div style="font-size: 13px; color: #4B5563; margin-top: 3px;">
                                            {{ $viewingSubscription->package->listing->title ?? 'â€”' }}
                                            @if(isset($viewingSubscription->package->listing->category))
                                                @php $catIcon = $viewingSubscription->package->listing->category->icon ?? 'bi-tag'; @endphp
                                                <span class="badge bg-light text-dark border ms-1" style="font-size: 11px;">
                                                    @if(\Illuminate\Support\Str::startsWith($catIcon, 'bi-'))
                                                        <i class="bi {{ $catIcon }} me-1"></i>
                                                    @else
                                                        <img src="{{ asset('storage/'.$catIcon) }}" style="width:13px;height:13px;object-fit:contain;" class="me-1">
                                                    @endif
                                                    {{ $viewingSubscription->package->listing->category->name }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($viewingSubscription->package)
                                        <div style="font-size: 12px; color: #9CA3AF; margin-top: 4px;"><i class="bi bi-clock me-1"></i>{{ $viewingSubscription->package->duration_label }}</div>
                                        @endif
                                    </div>
                                    @if($viewingSubscription->package)
                                    <div style="font-size: 22px; font-weight: 800; color: #2563EB;">&#8377;{{ number_format($viewingSubscription->package->price, 2) }}</div>
                                    @endif
                                </div>
                            </div>

                            {{-- Booking Specifics --}}
                            @if($viewingSubscription->room_id || $viewingSubscription->shift_id || $viewingSubscription->trainer_ids || ($viewingSubscription->booking && $viewingSubscription->booking->security_deposit > 0))
                            <div style="background: white; border-radius: 10px; padding: 14px; margin-bottom: 14px; border: 1px solid #e5e7eb;">
                                <div style="font-size: 10px; text-transform: uppercase; letter-spacing: .7px; color: #9CA3AF; font-weight: 700; margin-bottom: 12px;">Booking Specifics</div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px;">
                                    @if($viewingSubscription->room_id)
                                    <div style="background: #eff6ff; border-radius: 8px; padding: 10px;">
                                        <div style="font-size: 11px; color: #6B7280;"><i class="bi bi-door-closed me-1"></i>Room</div>
                                        <div style="font-size: 14px; font-weight: 700; color: #1e3a8a; margin-top: 2px;">{{ $viewingSubscription->room->room_number ?? 'â€”' }}</div>
                                        <div style="font-size: 11px; color: #6B7280;">{{ $viewingSubscription->room->room_type ?? '' }}</div>
                                    </div>
                                    @endif
                                    @if($viewingSubscription->shift_id)
                                    <div style="background: #f0fdf4; border-radius: 8px; padding: 10px;">
                                        <div style="font-size: 11px; color: #6B7280;"><i class="bi bi-clock me-1"></i>Shift</div>
                                        <div style="font-size: 14px; font-weight: 700; color: #14532d; margin-top: 2px;">{{ $viewingSubscription->shift->shift_label ?? 'â€”' }}</div>
                                        <div style="font-size: 11px; color: #6B7280;">{{ \Carbon\Carbon::parse($viewingSubscription->shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($viewingSubscription->shift->end_time)->format('h:i A') }}</div>
                                    </div>
                                    @endif
                                    @if($viewingSubscription->trainer_ids)
                                    @php $trainerNames = \App\Models\ListingTrainer::whereIn('id', $viewingSubscription->trainer_ids)->pluck('name')->implode(', '); @endphp
                                    <div style="background: #fdf4ff; border-radius: 8px; padding: 10px;">
                                        <div style="font-size: 11px; color: #6B7280;"><i class="bi bi-person-badge me-1"></i>Trainer</div>
                                        <div style="font-size: 13px; font-weight: 700; color: #581c87; margin-top: 2px;">{{ $trainerNames ?: 'â€”' }}</div>
                                    </div>
                                    @endif
                                    @if($viewingSubscription->booking && $viewingSubscription->booking->security_deposit > 0)
                                    <div style="background: #fffbeb; border-radius: 8px; padding: 10px;">
                                        <div style="font-size: 11px; color: #6B7280;"><i class="bi bi-shield-check me-1"></i>Security Deposit</div>
                                        <div style="font-size: 16px; font-weight: 800; color: #d97706; margin-top: 2px;">&#8377;{{ number_format($viewingSubscription->booking->security_deposit, 2) }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            {{-- Stats --}}
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px;">
                                <div style="background: white; border-radius: 8px; padding: 12px; border: 1px solid #e5e7eb;">
                                    <div style="font-size: 11px; color: #9CA3AF; margin-bottom: 4px;">Total Payments</div>
                                    <div style="font-size: 22px; font-weight: 800; color: #2563EB;">{{ $viewingSubscription->payments->count() }}</div>
                                </div>
                                <div style="background: white; border-radius: 8px; padding: 12px; border: 1px solid #e5e7eb;">
                                    <div style="font-size: 11px; color: #9CA3AF; margin-bottom: 4px;">Created On</div>
                                    <div style="font-size: 12px; font-weight: 700; color: #374151;">{{ $viewingSubscription->created_at->format('d M Y') }}</div>
                                </div>
                                <div style="background: white; border-radius: 8px; padding: 12px; border: 1px solid #e5e7eb;">
                                    <div style="font-size: 11px; color: #9CA3AF; margin-bottom: 4px;">Last Updated</div>
                                    <div style="font-size: 12px; font-weight: 700; color: #374151;">{{ $viewingSubscription->updated_at->format('d M Y') }}</div>
                                </div>
                            </div>

                        </div>
                        {{-- END OVERVIEW --}}

                        {{-- PAYMENTS --}}
                        <div class="tab-pane fade" id="prt-pay" role="tabpanel">

                            {{-- Payment History --}}
                            <div style="background: white; border-radius: 10px; padding: 14px; margin-bottom: 14px; border: 1px solid #e5e7eb;">
                                <div style="font-size: 10px; text-transform: uppercase; letter-spacing: .7px; color: #9CA3AF; font-weight: 700; margin-bottom: 12px;"><i class="bi bi-credit-card text-primary me-1"></i>Payment History</div>
                                @if($viewingSubscription->payments->count())
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0" style="font-size: 13px;">
                                        <thead>
                                            <tr style="background: #f8fafc;">
                                                <th class="border-0" style="font-size: 11px; color: #6B7280; font-weight: 600; padding: 8px 10px;">Date</th>
                                                <th class="border-0" style="font-size: 11px; color: #6B7280; font-weight: 600; padding: 8px 10px;">Amount</th>
                                                <th class="border-0" style="font-size: 11px; color: #6B7280; font-weight: 600; padding: 8px 10px;">Transaction ID</th>
                                                <th class="border-0" style="font-size: 11px; color: #6B7280; font-weight: 600; padding: 8px 10px;">Method</th>
                                                <th class="border-0" style="font-size: 11px; color: #6B7280; font-weight: 600; padding: 8px 10px;">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($viewingSubscription->payments as $pay)
                                            <tr>
                                                <td style="padding: 10px; color: #374151;">{{ optional($pay->paid_at)->format('d M Y') ?? 'Pending' }}</td>
                                                <td style="padding: 10px; font-weight: 700; color: #2563EB;">&#8377;{{ number_format($pay->amount, 2) }}</td>
                                                <td style="padding: 10px; font-family: monospace; font-size: 11px; color: #6B7280;">{{ $pay->gateway_ref ?? 'â€”' }}</td>
                                                <td style="padding: 10px;"><span class="badge bg-light text-dark border" style="font-size: 11px;">{{ ucfirst($pay->gateway ?? 'Unknown') }}</span></td>
                                                <td style="padding: 10px;">
                                                    <span class="badge {{ strtolower($pay->status) === 'paid' ? 'bg-success' : (strtolower($pay->status) === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}" style="font-size: 11px;">{{ ucfirst($pay->status) }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div style="text-align: center; padding: 24px; color: #9CA3AF;">
                                    <i class="bi bi-inbox" style="font-size: 30px; display: block; margin-bottom: 8px; opacity: 0.4;"></i>
                                    No payments recorded
                                </div>
                                @endif
                            </div>

                            {{-- Payouts --}}
                            @php
                                $walletTxns = \App\Models\WalletTransaction::where('reference_type', \App\Models\Subscription::class)
                                    ->where('reference_id', $viewingSubscription->id)->latest()->get();
                            @endphp
                            @if($walletTxns->count())
                            <div style="background: white; border-radius: 10px; padding: 14px; margin-bottom: 14px; border: 1px solid #e5e7eb;">
                                <div style="font-size: 10px; text-transform: uppercase; letter-spacing: .7px; color: #9CA3AF; font-weight: 700; margin-bottom: 12px;"><i class="bi bi-wallet2 text-success me-1"></i>Payouts / Platform Fees</div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0" style="font-size: 13px;">
                                        <thead>
                                            <tr style="background: #f8fafc;">
                                                <th class="border-0" style="font-size: 11px; color: #6B7280; font-weight: 600; padding: 8px 10px;">Date</th>
                                                <th class="border-0" style="font-size: 11px; color: #6B7280; font-weight: 600; padding: 8px 10px;">Description</th>
                                                <th class="border-0" style="font-size: 11px; color: #6B7280; font-weight: 600; padding: 8px 10px;">Type</th>
                                                <th class="border-0" style="font-size: 11px; color: #6B7280; font-weight: 600; padding: 8px 10px;">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($walletTxns as $txn)
                                            <tr>
                                                <td style="padding: 10px; color: #374151;">{{ $txn->created_at->format('d M Y') }}</td>
                                                <td style="padding: 10px; color: #6B7280; font-size: 12px;">{{ $txn->description }}</td>
                                                <td style="padding: 10px;"><span class="badge {{ $txn->type === 'credit' ? 'bg-success' : 'bg-danger' }}" style="font-size: 11px;">{{ ucfirst($txn->type) }}</span></td>
                                                <td style="padding: 10px; font-weight: 700; color: {{ $txn->type === 'credit' ? '#10B981' : '#EF4444' }};">{{ $txn->type === 'credit' ? '+' : '-' }}&#8377;{{ number_format(abs($txn->amount), 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            {{-- Invoices --}}
                            @if($viewingSubscription->invoices->count())
                            <div style="background: white; border-radius: 10px; padding: 14px; border: 1px solid #e5e7eb;">
                                <div style="font-size: 10px; text-transform: uppercase; letter-spacing: .7px; color: #9CA3AF; font-weight: 700; margin-bottom: 12px;"><i class="bi bi-receipt text-warning me-1"></i>Invoices</div>
                                @foreach($viewingSubscription->invoices as $invoice)
                                <div class="d-flex align-items-center gap-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div style="width: 34px; height: 34px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <i class="bi bi-file-earmark-text text-primary" style="font-size: 15px;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div style="font-size: 13px; font-weight: 600; color: #111827;">Invoice #{{ $invoice->id }}</div>
                                        <div style="font-size: 11px; color: #9CA3AF;">{{ $invoice->created_at->format('d M Y') }}</div>
                                    </div>
                                    <div style="font-size: 14px; font-weight: 700; color: #111827;">&#8377;{{ number_format($invoice->amount ?? 0, 2) }}</div>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('partner.invoices.receipt.show', ['type' => 'invoice', 'id' => $invoice->id]) }}" target="_blank" class="btn btn-sm btn-outline-secondary" style="padding: 3px 8px;" title="View"><i class="bi bi-printer" style="font-size: 12px;"></i></a>
                                        <a href="{{ route('partner.invoices.receipt.download', ['type' => 'invoice', 'id' => $invoice->id]) }}" class="btn btn-sm btn-outline-primary" style="padding: 3px 8px;" title="Download"><i class="bi bi-download" style="font-size: 12px;"></i></a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif

                        </div>
                        {{-- END PAYMENTS --}}

                    </div>
                </div>

                {{-- Footer --}}
                <div class="modal-footer border-top" style="background: #f8fafc; padding: 10px 20px; gap: 8px;">
                    @if(auth()->user()->canAccess('subscription_update'))
                        @if($viewingSubscription->status === 'active' || $viewingSubscription->status === 'expired')
                            <div class="dropdown">
                                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Renew Subscription
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <button class="dropdown-item text-primary" wire:click="renewSubscriptionOnline('{{ $viewingSubscription->id }}')">
                                            <i class="bi bi-credit-card me-2"></i>Renew (Online)
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item text-success" wire:click="renewSubscriptionCash('{{ $viewingSubscription->id }}')"
                                                onclick="confirm('Mark as renewed with cash?') || event.stopImmediatePropagation();">
                                            <i class="bi bi-cash me-2"></i>Renew (Cash)
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        @endif
                        @if($viewingSubscription->status === 'active')
                            <button type="button" class="btn btn-outline-danger btn-sm"
                                    wire:click="cancel('{{ $viewingSubscription->id }}')" wire:loading.attr="disabled"
                                    onclick="confirm('Are you sure?') || event.stopImmediatePropagation();">
                                <span wire:loading wire:target="cancel('{{ $viewingSubscription->id }}')" class="ft-btn-spinner dark"></span>
                                <i class="bi bi-x-circle me-1" wire:loading.remove wire:target="cancel('{{ $viewingSubscription->id }}')"></i>
                                <span wire:loading.remove wire:target="cancel('{{ $viewingSubscription->id }}')">Cancel</span>
                            </button>
                        @endif
                    @endif
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="closeView()">Close</button>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>

