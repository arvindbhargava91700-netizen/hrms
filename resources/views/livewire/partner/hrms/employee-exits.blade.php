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
            <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">Resignation & Exit Management</h5>
            <!-- <p class="text-muted small mb-0">Track employee resignations, notice periods, last working dates, exit reasons, departmental clearance, and F&F settlements</p> -->
        </div>
        @if(auth()->user()->isPartner() || auth()->user()->canAccess('resignationexit_create'))
        <button class="btn btn-primary d-flex align-items-center gap-2 px-4 rounded-3 shadow-sm" wire:click="createExit">
            <i class="bi bi-box-arrow-right"></i> Log Resignation / Exit
        </button>
        @endif
    </div>

    {{-- Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Resignations</span>
                        <h3 class="fw-bold mb-0 text-primary mt-1">{{ $totalResignations }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3">
                        <i class="bi bi-file-earmark-person fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Serving Notice</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1">{{ $totalServingNotice }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-danger border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Pending F&F / Clearance</span>
                        <h3 class="fw-bold mb-0 text-danger mt-1">{{ $totalPendingFnF }}</h3>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3">
                        <i class="bi bi-cash-stack fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-success border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Exited & Settled</span>
                        <h3 class="fw-bold mb-0 text-success mt-1">{{ $totalExitedSettled }}</h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    @if(auth()->user()->isPartner() || auth()->user()->canAccess('resignationexit_viewAny'))
    <div class="mb-4">
        @include('partials.hrms-filters')
    </div>
    @endif

    {{-- Search & Status Filter Row --}}
    <div class="card border-0 rounded-4 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-7">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" wire:model.live.debounce.300ms="search" placeholder="Search by employee name, code, exit reason, or notes...">
                    </div>
                </div>
                <div class="col-md-5">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Exit Statuses</option>
                        <option value="resigned">Resigned</option>
                        <option value="serving_notice">Serving Notice Period</option>
                        <option value="cleared">Clearance Completed</option>
                        <option value="exited">Fully Exited</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Resignation List Table --}}
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted small font-monospace">EMPLOYEE</th>
                        <th class="py-3 text-muted small font-monospace">RESIGNATION DATE</th>
                        <th class="py-3 text-muted small font-monospace">NOTICE & LAST WORKING DATE</th>
                        <th class="py-3 text-muted small font-monospace">EXIT REASON</th>
                        <th class="py-3 text-muted small font-monospace">CLEARANCE</th>
                        <th class="py-3 text-muted small font-monospace">F&F STATUS</th>
                        <th class="pe-4 py-3 text-end text-muted small font-monospace">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exits as $ex)
                        @php
                            $clearanceConfig = match($ex->clearance_status) {
                                'fully_cleared'     => ['class' => 'success',   'label' => 'Fully Cleared'],
                                'partially_cleared' => ['class' => 'primary',   'label' => 'Partially Cleared'],
                                'in_progress'       => ['class' => 'info',      'label' => 'In Progress'],
                                default             => ['class' => 'warning',   'label' => 'Pending'],
                            };

                            $fnfConfig = match($ex->fnf_status) {
                                'settled'     => ['class' => 'success',   'label' => 'Settled'],
                                'in_progress' => ['class' => 'info',      'label' => 'In Progress'],
                                'on_hold'     => ['class' => 'secondary', 'label' => 'On Hold'],
                                default       => ['class' => 'danger',    'label' => 'Pending'],
                            };

                            $isServing = in_array($ex->status, ['resigned', 'serving_notice']) && $ex->last_working_date && $ex->last_working_date->isFuture();
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px; font-size: 0.9rem;">
                                        {{ substr($ex->employee->name ?? 'E', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0">{{ $ex->employee->name ?? 'N/A' }}</div>
                                        <small class="text-muted">Code: {{ $ex->employee->employee_code ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small">
                                    {{ $ex->resignation_date ? $ex->resignation_date->format('d M, Y') : 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">
                                    Notice: {{ $ex->notice_period_days }} Days
                                </div>
                                <div class="small text-muted">
                                    Last Day: <span class="fw-bold text-dark">{{ $ex->last_working_date ? $ex->last_working_date->format('d M, Y') : 'N/A' }}</span>
                                </div>
                                @if($isServing)
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill small mt-1">
                                        <i class="bi bi-hourglass-split me-1 text-warning"></i>Serving Notice
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark small" style="max-width: 200px; white-space: normal;">
                                    {{ Str::limit($ex->exit_reason, 65) }}
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $clearanceConfig['class'] }}-subtle text-{{ $clearanceConfig['class'] }} border border-{{ $clearanceConfig['class'] }}-subtle px-3 py-1.5 fw-semibold">
                                    {{ $clearanceConfig['label'] }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $fnfConfig['class'] }}-subtle text-{{ $fnfConfig['class'] }} border border-{{ $fnfConfig['class'] }}-subtle px-3 py-1.5 fw-semibold d-inline-block">
                                    {{ $fnfConfig['label'] }}
                                </span>
                                @if($ex->fnf_amount > 0)
                                    <div class="small text-muted fw-bold mt-1">₹{{ number_format($ex->fnf_amount, 2) }}</div>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button class="btn btn-sm btn-light text-primary border-0 rounded-3" wire:click="viewExitDetails('{{ $ex->id }}')" title="View Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('resignationexit_update'))
                                    <button class="btn btn-sm btn-light text-secondary border-0 rounded-3" wire:click="editExit('{{ $ex->id }}')" title="Edit Exit Record">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    @endif
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('resignationexit_delete'))
                                    <button class="btn btn-sm btn-light text-danger border-0 rounded-3" wire:click="deleteExit('{{ $ex->id }}')" onclick="confirm('Are you sure you want to delete this exit record?') || event.stopImmediatePropagation()" title="Delete Record">
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
                                    <i class="bi bi-door-open display-6 mb-3 d-block text-secondary"></i>
                                    <h6 class="fw-semibold">No Resignation & Exit Records Found</h6>
                                    <p class="small text-muted mb-0">No employee resignations or exit clearances have been submitted yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($exits->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $exits->links() }}
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
                            {{ $isEditMode ? 'Edit Resignation & Exit Record' : 'Log Employee Resignation' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <form wire:submit.prevent="saveExit">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                {{-- Employee Selection --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Resigning Employee <span class="text-danger">*</span></label>
                                    <select class="form-select @error('employee_id') is-invalid @enderror" wire:model="employee_id">
                                        <option value="">Select Employee...</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}" @selected($employee_id == $emp->id)>{{ $emp->name }} ({{ $emp->employee_code ?? 'EMP' }})</option>
                                        @endforeach
                                    </select>
                                    @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Exit Lifecycle Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Exit Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                        <option value="resigned" @selected($status === 'resigned')>Resigned</option>
                                        <option value="serving_notice" @selected($status === 'serving_notice')>Serving Notice Period</option>
                                        <option value="cleared" @selected($status === 'cleared')>Clearance Completed</option>
                                        <option value="exited" @selected($status === 'exited')>Fully Exited</option>
                                        <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Resignation Date --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Resignation Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('resignation_date') is-invalid @enderror" wire:model.live="resignation_date">
                                    @error('resignation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Notice Period Days --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Notice Period (Days) <span class="text-danger">*</span></label>
                                    <input type="number" min="0" class="form-control @error('notice_period_days') is-invalid @enderror" wire:model.live="notice_period_days">
                                    @error('notice_period_days') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Last Working Date --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Last Working Date</label>
                                    <input type="date" class="form-control @error('last_working_date') is-invalid @enderror" wire:model="last_working_date">
                                    @error('last_working_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Exit Reason --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Exit Reason <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('exit_reason') is-invalid @enderror" rows="2" wire:model="exit_reason" placeholder="Describe the reason for resignation / exit..."></textarea>
                                    @error('exit_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Departmental Clearance Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Departmental Clearance Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('clearance_status') is-invalid @enderror" wire:model="clearance_status">
                                        <option value="pending" @selected($clearance_status === 'pending')>Pending Clearance</option>
                                        <option value="in_progress" @selected($clearance_status === 'in_progress')>In Progress</option>
                                        <option value="partially_cleared" @selected($clearance_status === 'partially_cleared')>Partially Cleared</option>
                                        <option value="fully_cleared" @selected($clearance_status === 'fully_cleared')>Fully Cleared</option>
                                    </select>
                                    @error('clearance_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- F&F Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">F&F Settlement Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('fnf_status') is-invalid @enderror" wire:model="fnf_status">
                                        <option value="pending" @selected($fnf_status === 'pending')>Pending Settlement</option>
                                        <option value="in_progress" @selected($fnf_status === 'in_progress')>In Progress</option>
                                        <option value="settled" @selected($fnf_status === 'settled')>Settled / Completed</option>
                                        <option value="on_hold" @selected($fnf_status === 'on_hold')>On Hold</option>
                                    </select>
                                    @error('fnf_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- F&F Amount --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">F&F Settlement Amount (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('fnf_amount') is-invalid @enderror" wire:model="fnf_amount">
                                    @error('fnf_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- F&F Settlement Date --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">F&F Settlement Date</label>
                                    <input type="date" class="form-control @error('fnf_settlement_date') is-invalid @enderror" wire:model="fnf_settlement_date">
                                    @error('fnf_settlement_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Exit Interview Notes --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Exit Interview Feedback & Notes</label>
                                    <textarea class="form-control @error('exit_interview_notes') is-invalid @enderror" rows="2" wire:model="exit_interview_notes" placeholder="Feedback provided during exit interview, handovers, asset return notes..."></textarea>
                                    @error('exit_interview_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Remarks --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Internal HR Remarks</label>
                                    <textarea class="form-control @error('remarks') is-invalid @enderror" rows="2" wire:model="remarks" placeholder="Optional internal HR notes..."></textarea>
                                    @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light rounded-3 px-4" wire:click="closeModals">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4">
                                <i class="bi bi-check-lg me-1"></i> {{ $isEditMode ? 'Update Exit Record' : 'Save Exit Record' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- View Details Modal --}}
    @if ($isViewModalOpen && $viewExit)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center fw-bold" style="width:45px; height:45px;">
                                {{ substr($viewExit->employee->name ?? 'E', 0, 2) }}
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $viewExit->employee->name ?? 'Employee Details' }}</h5>
                                <small class="text-muted">Employee Code: {{ $viewExit->employee->employee_code ?? 'N/A' }} | Email: {{ $viewExit->employee->email ?? 'N/A' }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Resignation Date</span>
                                    <strong class="text-dark">
                                        {{ $viewExit->resignation_date ? $viewExit->resignation_date->format('d M, Y') : 'N/A' }}
                                    </strong>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Notice Period</span>
                                    <strong class="text-dark">{{ $viewExit->notice_period_days }} Days</strong>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Last Working Date</span>
                                    <strong class="text-dark">
                                        {{ $viewExit->last_working_date ? $viewExit->last_working_date->format('d M, Y') : 'N/A' }}
                                    </strong>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Departmental Clearance</span>
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-1.5 fw-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $viewExit->clearance_status)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">F&F Settlement</span>
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1.5 fw-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $viewExit->fnf_status)) }}
                                    </span>
                                    @if($viewExit->fnf_amount > 0)
                                        <div class="fw-bold text-dark mt-1">Amount: ₹{{ number_format($viewExit->fnf_amount, 2) }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-2">Exit Reason</h6>
                                <div class="p-3 bg-light rounded-3 text-dark" style="white-space: pre-line;">
                                    {{ $viewExit->exit_reason }}
                                </div>
                            </div>

                            @if($viewExit->exit_interview_notes)
                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-2">Exit Interview Notes</h6>
                                <div class="p-3 bg-light rounded-3 text-dark" style="white-space: pre-line;">
                                    {{ $viewExit->exit_interview_notes }}
                                </div>
                            </div>
                            @endif

                            @if($viewExit->remarks)
                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-2">Internal HR Remarks</h6>
                                <div class="p-3 bg-light rounded-3 text-muted" style="white-space: pre-line;">
                                    {{ $viewExit->remarks }}
                                </div>
                            </div>
                            @endif

                            <div class="col-12 border-top pt-3 text-muted small d-flex justify-content-between">
                                <span>Logged By: {{ $viewExit->creator->name ?? 'System/Partner' }}</span>
                                <span>Record Date: {{ $viewExit->created_at->format('d M, Y h:i A') }}</span>
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
