<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Advance Payments</h5>
            @if(auth()->user()->role === 'employee')
            <button class="btn btn-primary btn-sm" wire:click="createRequest">
                <i class="bi bi-plus-circle me-1"></i> Request Advance
            </button>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        @if(auth()->user()->role !== 'employee')
                        <th>Employee</th>
                        @endif
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Deduction Month</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advances as $advance)
                        <tr>
                            @if(auth()->user()->role !== 'employee')
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $advance->employee->avatar_url }}" class="rounded-circle flex-shrink-0" width="36" height="36" alt="{{ $advance->employee->name }}">
                                    <div>
                                        <div class="fw-600 text-dark">{{ $advance->employee->name }}</div>
                                    </div>
                                </div>
                            </td>
                            @endif
                            <td class="fw-600">₹{{ number_format($advance->amount, 0) }}</td>
                            <td>{{ Str::limit($advance->reason, 30) }}</td>
                           <td> @if($advance->deduction_year && $advance->deduction_month) {{ \Carbon\Carbon::create( $advance->deduction_year, $advance->deduction_month, 1 )->format('F Y') }} @else - @endif </td>
                            <td>
                                @if($advance->status === 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($advance->status === 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($advance->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @elseif($advance->status === 'deducted')
                                    <span class="badge bg-info">Deducted</span>
                                @endif
                            </td>
                            <td>{{ $advance->remarks ?: '-' }}</td>
                            <td class="text-end">
                                @if($advance->status === 'pending' && (auth()->user()->isPartner() || auth()->user()->canAccess('payroll_manage')))
                                <button class="btn btn-sm btn-outline-primary" wire:click="openApprovalModal('{{ $advance->id }}')">
                                    Review
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'employee' ? 6 : 7 }}" class="text-center text-muted py-5">
                                <i class="bi bi-wallet2 display-4 mb-3 d-block text-light"></i>
                                <p>No advance payment requests found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Request Modal -->
    @if($isRequestModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="saveRequest">
                    <div class="modal-header">
                        <h5 class="modal-title">Request Salary Advance</h5>
                        <button type="button" class="btn-close" wire:click="$set('isRequestModalOpen', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Amount (₹)</label>
                            <input type="number" class="form-control" wire:model="amount" required min="1">
                            @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" wire:model="reason" rows="3" required></textarea>
                            @error('reason') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Salary Deduction Month</label>
                            <input type="month" class="form-control" wire:model="deduction_month" required>
                            <div class="form-text">The month you expect this advance to be deducted from your salary.</div>
                            @error('deduction_month') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isRequestModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Approval Modal -->
    @if($isApprovalModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="processApproval">
                    <div class="modal-header">
                        <h5 class="modal-title">Review Advance Request</h5>
                        <button type="button" class="btn-close" wire:click="$set('isApprovalModalOpen', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Action</label>
                            <select class="form-select" wire:model="approvalStatus" required>
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                            @error('approvalStatus') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks (Optional)</label>
                            <textarea class="form-control" wire:model="approvalRemarks" rows="2"></textarea>
                            @error('approvalRemarks') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isApprovalModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Decision</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
