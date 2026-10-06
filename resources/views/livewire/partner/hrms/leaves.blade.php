<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Leave Requests</h5>
            @if(auth()->user()->canAccess('leave_create'))
            <button class="btn btn-primary btn-sm" wire:click="createLeave">
                <i class="bi bi-plus-circle me-1"></i> Add Leave Request
            </button>
            @endif
        </div>
        
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Reason</th>
                        <th>Status</th>
                        @if(auth()->user()->canAccess('leave_status_update') || auth()->user()->canAccess('leave_delete'))
                        <th class="text-end">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                        <tr>
                            <td class="fw-600 text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:32px; height:32px; font-size:12px;">
                                        {{ substr($leave->employee->name ?? '?', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="text-dark fw-600">{{ $leave->employee->name ?? 'Unknown' }}</div>
                                        <div class="text-muted small">{{ $leave->employee->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary">{{ $leave->leaveCategory->name ?? $leave->type }}</span>
                            </td>
                            <td>
                                <div class="small fw-600">{{ date('d M Y', strtotime($leave->start_date)) }}</div>
                                <div class="text-muted small">to {{ date('d M Y', strtotime($leave->end_date)) }}</div>
                            </td>
                            <td>
                                <span class="text-muted small d-inline-block text-truncate" style="max-width: 200px;">
                                    {{ $leave->reason ?: '-' }}
                                </span>
                            </td>
                            <td>
                                @if($leave->status === 'approved')
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle"></i> Approved</span>
                                @elseif($leave->status === 'rejected')
                                    <span class="badge bg-danger-subtle text-danger"><i class="bi bi-x-circle"></i> Rejected</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning"><i class="bi bi-clock"></i> Pending</span>
                                @endif
                                @if($leave->remarks)
                                   <div class="small text-muted mt-1">Remark: {{ $leave->remarks }}</div>
                                @endif
                            </td>
                            @if(auth()->user()->canAccess('leave_status_update') || auth()->user()->canAccess('leave_delete'))
                            <td class="text-end">
                                @if(auth()->user()->canAccess('leave_status_update'))
                                    @if($leave->status === 'pending')
                                        <button class="btn btn-sm btn-outline-primary me-1" wire:click="openApprovalModal({{ $leave->id }})">
                                            <i class="bi bi-pencil-square"></i> Review
                                        </button>
                                    @else
                                        <button class="btn btn-sm btn-light me-1" disabled>Resolved</button>
                                    @endif
                                @endif
                                
                                @if(auth()->user()->canAccess('leave_delete'))
                                    <button class="btn btn-sm btn-outline-danger" wire:click="deleteLeave({{ $leave->id }})" onclick="confirm('Are you sure you want to delete this leave request?') || event.stopImmediatePropagation()">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-calendar2-minus display-4 mb-3 d-block text-light"></i>
                                No leave requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Request Modal -->
    @if($isModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="saveLeave">
                    <div class="modal-header">
                        <h5 class="modal-title">Create Leave Request</h5>
                        <button type="button" class="btn-close" wire:click="$set('isModalOpen', false)"></button>
                    </div>
                    <div class="modal-body">
                        @if(auth()->user()->canAccess('leave_viewAny'))
                        <div class="mb-3">
                            <label class="form-label fw-600">Select Employee</label>
                            <div wire:ignore>
                            <select class="form-select select2-searchable" wire:model="employeeId">
                                <option value="">Select Employee</option>
                                @foreach($staff as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                            </div>
                            @error('employeeId') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600">Start Date</label>
                                <input type="date" class="form-control" wire:model="startDate">
                                @error('startDate') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">End Date</label>
                                <input type="date" class="form-control" wire:model="endDate">
                                @error('endDate') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-600">Leave Category</label>
                            <select class="form-select" wire:model="leaveCategoryId">
                                <option value="">Select Leave Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->days }} Days yearly limit)</option>
                                @endforeach
                            </select>
                            @error('leaveCategoryId') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-600">Reason</label>
                            <textarea class="form-control" wire:model="reason" rows="3"></textarea>
                            @error('reason') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isModalOpen', false)">Cancel</button>
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
                        <h5 class="modal-title">Review Leave Request</h5>
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
