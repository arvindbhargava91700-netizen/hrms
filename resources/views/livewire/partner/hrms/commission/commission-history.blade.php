<div>
    <div class="row align-items-center mb-4">
        <div class="col">
            <h4 class="mb-1 fw-bold">Commission History</h4>
            <p class="text-muted mb-0">View all processed commission records, TDS deductions, and payout statuses for your team.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('partner.hrms.commission.process') }}" class="btn btn-primary shadow-sm fw-bold">
                <i class="bi bi-gear-fill me-2"></i>Process Commissions
            </a>
        </div>
    </div>

    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4 rounded-3">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-bold text-uppercase mb-1">Month</label>
                    <select class="form-select bg-light border-0" wire:model.live="month">
                        <option value="">All Months</option>
                        @foreach($months as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted fw-bold text-uppercase mb-1">Year</label>
                    <select class="form-select bg-light border-0" wire:model.live="year">
                        <option value="">All Years</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-bold text-uppercase mb-1">Employee</label>
                    <select class="form-select bg-light border-0" wire:model.live="employeeId">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0">Employee</th>
                        <th class="border-0">Period</th>
                        <th class="border-0 text-end">Target / Business</th>
                        <th class="border-0 text-end">Gross Comm.</th>
                        <th class="border-0 text-end">TDS Deducted</th>
                        <th class="border-0 text-end">Net Payout</th>
                        <th class="border-0 text-center">Status</th>
                        <th class="border-0 text-end">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($payouts as $payout)
                        @php
                            $grossComm = $payout->total_payout > 0 ? $payout->total_payout : ($payout->commission_earned + $payout->recovery_earned + $payout->upline_commission_earned);
                            $tdsAmt = $payout->tds_amount > 0 ? $payout->tds_amount : 0;
                            $netAmt = $payout->net_payout > 0 ? $payout->net_payout : ($grossComm - $tdsAmt);
                            $tdsPct = $payout->tds_percent > 0 ? $payout->tds_percent : 0;
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $payout->employee->avatar_url }}" alt="avatar" class="rounded-circle me-2" width="36" height="36">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $payout->employee->name }}</div>
                                        <div class="small text-muted">{{ $payout->employee->employee_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1 rounded-pill fw-semibold">
                                    {{ date('M', mktime(0, 0, 0, $payout->month, 10)) }} {{ $payout->year }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="fw-bold text-dark">₹{{ number_format($payout->total_new_business + $payout->total_recovery_business, 2) }}</div>
                                <div class="extra-small text-muted">Target: ₹{{ number_format($payout->target_amount, 2) }}</div>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                ₹{{ number_format($grossComm, 2) }}
                                <div class="extra-small text-muted">
                                    Base: ₹{{ number_format($payout->commission_earned, 2) }} 
                                    @if($payout->upline_commission_earned > 0) | Up: ₹{{ number_format($payout->upline_commission_earned, 2) }} @endif
                                </div>
                            </td>
                            <td class="text-end">
                                @if($tdsAmt > 0)
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">
                                        -₹{{ number_format($tdsAmt, 2) }} <small>({{ number_format($tdsPct, 1) }}%)</small>
                                    </span>
                                @else
                                    <span class="text-muted extra-small">₹0.00</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-success fs-6">
                                ₹{{ number_format($netAmt, 2) }}
                            </td>
                            <td class="text-center">
                                @if($payout->status === 'paid')
                                    <span class="badge bg-success bg-gradient rounded-pill px-3 py-1"><i class="bi bi-check-circle-fill me-1"></i>Paid</span>
                                    @if($payout->paid_at)
                                        <div class="extra-small text-muted mt-0.5">{{ $payout->paid_at->format('M d, Y') }}</div>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark bg-opacity-20 border border-warning rounded-pill px-3 py-1 fw-semibold"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($payout->status !== 'paid')
                                    <button wire:click="markAsPaid({{ $payout->id }})" 
                                            class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-bold d-inline-flex align-items-center gap-1"
                                            onclick="confirm('Are you sure you want to mark this commission as PAID?') || event.stopImmediatePropagation()">
                                        <i class="bi bi-cash-stack"></i> Mark Paid
                                    </button>
                                @else
                                    <span class="text-muted extra-small"><i class="bi bi-lock-fill me-1"></i>Settled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-folder-x fs-1 d-block mb-3 text-secondary"></i>
                                    <h5>No Commission Records Found</h5>
                                    <p class="mb-0">Try adjusting your filters or process new commissions.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payouts->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>
</div>
