<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">Performance Improvement Plan (PIP)</h5>
            <!-- <p class="text-muted small mb-0">Track employees under performance improvement plans, set target goals, and log evaluation outcomes</p> -->
        </div>
        @if(auth()->user()->isPartner() || auth()->user()->canAccess('pip_create'))
        <button class="btn btn-primary d-flex align-items-center gap-2 px-4 rounded-3 shadow-sm" wire:click="createPip">
            <i class="bi bi-plus-lg"></i> Add Employee to PIP
        </button>
        @endif
    </div>

    {{-- Filters --}}
    @if(auth()->user()->isPartner() || auth()->user()->canAccess('pip_viewAny'))
    <div class="mb-4">
        {{-- Search & Status Filter Row --}}
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-6 col-lg-7">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" wire:model.live.debounce.300ms="search" placeholder="Search by employee name, code, reason, or targets...">
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-5">
                        <select class="form-select" wire:model.live="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="active">Active PIP</option>
                            <option value="under_review">Under Review</option>
                            <option value="completed_passed">Completed - Passed</option>
                            <option value="completed_failed">Completed - Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- PIP List Table --}}
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted small font-monospace">EMPLOYEE</th>
                        <th class="py-3 text-muted small font-monospace">REASON FOR PIP</th>
                        <th class="py-3 text-muted small font-monospace">DURATION (START / END)</th>
                        <th class="py-3 text-muted small font-monospace">IMPROVEMENT TARGETS</th>
                        <th class="py-3 text-muted small font-monospace">REVIEW RESULT</th>
                        <th class="py-3 text-muted small font-monospace">STATUS</th>
                        <th class="pe-4 py-3 text-end text-muted small font-monospace">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pips as $pip)
                        @php
                            $statusConfig = match($pip->status) {
                                'active'           => ['class' => 'primary',   'label' => 'Active',           'icon' => 'bi-lightning-charge'],
                                'under_review'     => ['class' => 'warning',   'label' => 'Under Review',     'icon' => 'bi-hourglass-split'],
                                'completed_passed' => ['class' => 'success',   'label' => 'Completed (Passed)', 'icon' => 'bi-check-circle-fill'],
                                'completed_failed' => ['class' => 'danger',    'label' => 'Completed (Failed)', 'icon' => 'bi-x-circle-fill'],
                                'cancelled'        => ['class' => 'secondary', 'label' => 'Cancelled',        'icon' => 'bi-dash-circle'],
                                default            => ['class' => 'secondary', 'label' => ucfirst($pip->status), 'icon' => 'bi-info-circle'],
                            };
                            $isOverdue = $pip->status === 'active' && $pip->end_date && $pip->end_date->isPast();
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px; font-size: 0.9rem;">
                                        {{ substr($pip->employee->name ?? 'E', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0">{{ $pip->employee->name ?? 'N/A' }}</div>
                                        <small class="text-muted">Code: {{ $pip->employee->employee_code ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-dark fw-medium" style="max-width: 220px; white-space: normal;">
                                    {{ Str::limit($pip->reason, 75) }}
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">
                                    {{ $pip->start_date ? $pip->start_date->format('d M, Y') : 'N/A' }}
                                    <span class="text-muted">to</span>
                                    {{ $pip->end_date ? $pip->end_date->format('d M, Y') : 'N/A' }}
                                </div>
                                @if($isOverdue)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small mt-1">
                                        <i class="bi bi-clock-history me-1"></i>Review Due
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="text-muted small" style="max-width: 220px; white-space: normal;">
                                    {{ $pip->improvement_targets ? Str::limit($pip->improvement_targets, 65) : 'No targets specified' }}
                                </div>
                            </td>
                            <td>
                                <div class="text-muted small" style="max-width: 200px; white-space: normal;">
                                    @if($pip->review_result)
                                        <span class="fw-medium text-dark">{{ Str::limit($pip->review_result, 60) }}</span>
                                    @else
                                        <span class="text-muted fst-italic">Pending evaluation</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $statusConfig['class'] }}-subtle text-{{ $statusConfig['class'] }} border border-{{ $statusConfig['class'] }}-subtle px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                                    <i class="bi {{ $statusConfig['icon'] }}"></i>
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button class="btn btn-sm btn-light text-primary border-0 rounded-3" wire:click="viewPipDetails({{ $pip->id }})" title="View Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('pip_update'))
                                    <button class="btn btn-sm btn-light text-secondary border-0 rounded-3" wire:click="editPip({{ $pip->id }})" title="Edit PIP">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    @endif
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('pip_delete'))
                                    <button class="btn btn-sm btn-light text-danger border-0 rounded-3" wire:click="deletePip({{ $pip->id }})" onclick="confirm('Are you sure you want to delete this PIP record?') || event.stopImmediatePropagation()" title="Delete PIP">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-clipboard-x display-6 mb-3 d-block text-secondary"></i>
                                    <h6 class="fw-semibold">No PIP Records Found</h6>
                                    <p class="small text-muted mb-0">No performance improvement plans have been added yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pips->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $pips->links() }}
            </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if ($isFormModalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">
                            {{ $isEditMode ? 'Edit PIP Record' : 'Add Employee under PIP' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <form wire:submit.prevent="savePip">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                {{-- Employee Under PIP --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Employee under PIP <span class="text-danger">*</span></label>
                                    <select class="form-select @error('employee_id') is-invalid @enderror" wire:model="employee_id">
                                        <option value="">Select Employee...</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}" @selected($employee_id == $emp->id)>{{ $emp->name }} ({{ $emp->employee_code ?? 'EMP' }})</option>
                                        @endforeach
                                    </select>
                                    @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                        <option value="active" @selected($status === 'active')>Active PIP</option>
                                        <option value="under_review" @selected($status === 'under_review')>Under Review</option>
                                        <option value="completed_passed" @selected($status === 'completed_passed')>Completed - Passed</option>
                                        <option value="completed_failed" @selected($status === 'completed_failed')>Completed - Failed</option>
                                        <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Dates --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" wire:model="start_date">
                                    @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">End Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" wire:model="end_date">
                                    @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Reason --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Reason for PIP <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('reason') is-invalid @enderror" rows="3" wire:model="reason" placeholder="Describe the performance issues or reasons for placing the employee under PIP..."></textarea>
                                    @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Improvement Targets --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Improvement Targets</label>
                                    <textarea class="form-control @error('improvement_targets') is-invalid @enderror" rows="3" wire:model="improvement_targets" placeholder="List specific KPIs, targets, or improvement expectations..."></textarea>
                                    @error('improvement_targets') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Review Result --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Review Result / Evaluation Notes</label>
                                    <textarea class="form-control @error('review_result') is-invalid @enderror" rows="2" wire:model="review_result" placeholder="Record evaluation progress notes, final review result, or outcome..."></textarea>
                                    @error('review_result') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Additional Remarks --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Remarks / Internal Notes</label>
                                    <textarea class="form-control @error('remarks') is-invalid @enderror" rows="2" wire:model="remarks" placeholder="Optional internal notes or remarks..."></textarea>
                                    @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light rounded-3 px-4" wire:click="closeModals">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4">
                                <i class="bi bi-check-lg me-1"></i> {{ $isEditMode ? 'Update PIP Record' : 'Save PIP Record' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- View Details Modal --}}
    @if ($isViewModalOpen && $viewPip)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width:45px; height:45px;">
                                {{ substr($viewPip->employee->name ?? 'E', 0, 2) }}
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $viewPip->employee->name ?? 'Employee Details' }}</h5>
                                <small class="text-muted">Employee Code: {{ $viewPip->employee->employee_code ?? 'N/A' }} | Email: {{ $viewPip->employee->email ?? 'N/A' }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Status</span>
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-1.5 fw-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $viewPip->status)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">PIP Duration</span>
                                    <strong class="text-dark">
                                        {{ $viewPip->start_date ? $viewPip->start_date->format('d M, Y') : 'N/A' }}
                                        to
                                        {{ $viewPip->end_date ? $viewPip->end_date->format('d M, Y') : 'N/A' }}
                                    </strong>
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-2">Reason for PIP</h6>
                                <div class="p-3 bg-light rounded-3 text-dark" style="white-space: pre-line;">
                                    {{ $viewPip->reason }}
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-2">Improvement Targets</h6>
                                <div class="p-3 bg-light rounded-3 text-dark" style="white-space: pre-line;">
                                    {{ $viewPip->improvement_targets ?? 'No specific improvement targets recorded.' }}
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-2">Review Result / Evaluation</h6>
                                <div class="p-3 bg-light rounded-3 text-dark" style="white-space: pre-line;">
                                    {{ $viewPip->review_result ?? 'Evaluation review pending.' }}
                                </div>
                            </div>

                            @if($viewPip->remarks)
                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-2">Internal Remarks</h6>
                                <div class="p-3 bg-light rounded-3 text-muted" style="white-space: pre-line;">
                                    {{ $viewPip->remarks }}
                                </div>
                            </div>
                            @endif

                            <div class="col-12 border-top pt-3 text-muted small d-flex justify-content-between">
                                <span>Created By: {{ $viewPip->creator->name ?? 'System/Partner' }}</span>
                                <span>Created Date: {{ $viewPip->created_at->format('d M, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-secondary rounded-3 px-4" wire:click="closeModals">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
