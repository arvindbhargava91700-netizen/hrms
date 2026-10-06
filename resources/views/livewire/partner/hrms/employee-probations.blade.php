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
            <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">Employee Probation & Asset Tracking</h5>
            <p class="text-muted small mb-0">Track employees on probation, asset allocations, confirmation due dates, extensions, and confirmed staff</p>
        </div>
        @if(auth()->user()->isPartner() || auth()->user()->canAccess('probation_create'))
        <button class="btn btn-primary d-flex align-items-center gap-2 px-4 rounded-3 shadow-sm" wire:click="createProbation">
            <i class="bi bi-person-plus-fill"></i> Add Employee to Probation
        </button>
        @endif
    </div>

    {{-- Metric Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-primary border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">On Probation</span>
                        <h3 class="fw-bold mb-0 text-primary mt-1">{{ $totalOnProbation }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Extended Probation</span>
                        <h3 class="fw-bold mb-0 text-warning mt-1">{{ $totalExtended }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3">
                        <i class="bi bi-calendar-plus fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-danger border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Due Soon / Overdue</span>
                        <h3 class="fw-bold mb-0 text-danger mt-1">{{ $totalDueSoon }}</h3>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3">
                        <i class="bi bi-exclamation-triangle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm p-3 bg-white border-start border-success border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Confirmed Staff</span>
                        <h3 class="fw-bold mb-0 text-success mt-1">{{ $totalConfirmed }}</h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3">
                        <i class="bi bi-patch-check-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    @if(auth()->user()->isPartner() || auth()->user()->canAccess('probation_viewAny'))
    <div class="mb-4">
        {{-- Search & Status Filter Row --}}
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" wire:model.live.debounce.300ms="search" placeholder="Search by employee name, code, allocated assets, or evaluation notes...">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <select class="form-select" wire:model.live="statusFilter">
                            <option value="">All Probation Statuses</option>
                            <option value="on_probation">On Probation</option>
                            <option value="extended">Extended Probation</option>
                            <option value="confirmed">Confirmed Employees</option>
                            <option value="rejected_failed">Failed / Rejected</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Probation Table --}}
    

    {{-- Probation Table --}}
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted small font-monospace">EMPLOYEE</th>
                        <th class="py-3 text-muted small font-monospace">PROBATION DATES</th>
                        <th class="py-3 text-muted small font-monospace">ASSET ALLOCATION</th>
                        <th class="py-3 text-muted small font-monospace">PROBATION STATUS</th>
                        <th class="py-3 text-muted small font-monospace">CONFIRMATION DATE</th>
                        <th class="pe-4 py-3 text-end text-muted small font-monospace">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($probations as $prob)
                        @php
                            $effectiveDueDate = $prob->extended_due_date ?? $prob->confirmation_due_date;
                            $isOverdue = in_array($prob->status, ['on_probation', 'extended']) && $effectiveDueDate && $effectiveDueDate->isPast();
                            $isDueSoon = in_array($prob->status, ['on_probation', 'extended']) && $effectiveDueDate && $effectiveDueDate->isFuture() && $effectiveDueDate->diffInDays(now()) <= 14;

                            $statusConfig = match($prob->status) {
                                'on_probation'    => ['class' => 'primary',   'label' => 'On Probation',     'icon' => 'bi-hourglass-split'],
                                'extended'        => ['class' => 'warning',   'label' => 'Extended',         'icon' => 'bi-calendar-plus'],
                                'confirmed'       => ['class' => 'success',   'label' => 'Confirmed Staff',  'icon' => 'bi-patch-check-fill'],
                                'rejected_failed' => ['class' => 'danger',    'label' => 'Failed / Rejected', 'icon' => 'bi-x-circle-fill'],
                                default           => ['class' => 'secondary', 'label' => ucfirst($prob->status), 'icon' => 'bi-info-circle'],
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px; font-size: 0.9rem;">
                                        {{ substr($prob->employee->name ?? 'E', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0">{{ $prob->employee->name ?? 'N/A' }}</div>
                                        <small class="text-muted">Code: {{ $prob->employee->employee_code ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">
                                    Start: {{ $prob->start_date ? $prob->start_date->format('d M, Y') : 'N/A' }}
                                </div>
                                <div class="small text-muted mt-0.5">
                                    Due: <span class="fw-bold text-dark">{{ $effectiveDueDate ? $effectiveDueDate->format('d M, Y') : 'N/A' }}</span>
                                </div>
                                @if($isOverdue)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small mt-1">
                                        <i class="bi bi-clock-history me-1"></i>Confirmation Overdue
                                    </span>
                                @elseif($isDueSoon)
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill small mt-1">
                                        <i class="bi bi-bell-fill me-1 text-warning"></i>Due Soon
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="text-dark small" style="max-width: 240px; white-space: normal;">
                                    @if($prob->asset_allocation)
                                        <i class="bi bi-box-seam me-1 text-primary"></i>{{ Str::limit($prob->asset_allocation, 65) }}
                                    @else
                                        <span class="text-muted fst-italic">No assets allocated</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $statusConfig['class'] }}-subtle text-{{ $statusConfig['class'] }} border border-{{ $statusConfig['class'] }}-subtle px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                                    <i class="bi {{ $statusConfig['icon'] }}"></i>
                                    {{ $statusConfig['label'] }}
                                </span>
                                @if($prob->is_extended)
                                    <span class="badge bg-light text-warning border border-warning rounded-pill small ms-1" title="Probation Period Extended">
                                        Extended
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($prob->confirmation_date)
                                    <div class="fw-bold text-success small">
                                        <i class="bi bi-check-all me-1"></i>{{ $prob->confirmation_date->format('d M, Y') }}
                                    </div>
                                @else
                                    <span class="text-muted small fst-italic">Pending</span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button class="btn btn-sm btn-light text-primary border-0 rounded-3" wire:click="viewProbationDetails({{ $prob->id }})" title="View Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('probation_update'))
                                    <button class="btn btn-sm btn-light text-secondary border-0 rounded-3" wire:click="editProbation({{ $prob->id }})" title="Edit Probation">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    @endif
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('probation_delete'))
                                    <button class="btn btn-sm btn-light text-danger border-0 rounded-3" wire:click="deleteProbation({{ $prob->id }})" onclick="confirm('Are you sure you want to delete this probation record?') || event.stopImmediatePropagation()" title="Delete Probation">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-person-bounding-box display-6 mb-3 d-block text-secondary"></i>
                                    <h6 class="fw-semibold">No Probation Records Found</h6>
                                    <p class="small text-muted mb-0">No employees are currently listed under probation tracking.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($probations->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $probations->links() }}
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
                            {{ $isEditMode ? 'Edit Probation Record' : 'Add Employee to Probation' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <form wire:submit.prevent="saveProbation">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                {{-- Employee Selection --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Employee under Probation <span class="text-danger">*</span></label>
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
                                    <label class="form-label fw-semibold small">Probation & Confirmation Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                        <option value="on_probation" @selected($status === 'on_probation')>On Probation</option>
                                        <option value="extended" @selected($status === 'extended')>Extended Probation</option>
                                        <option value="confirmed" @selected($status === 'confirmed')>Confirmed Employee</option>
                                        <option value="rejected_failed" @selected($status === 'rejected_failed')>Failed / Rejected</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Start Date --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Probation Start Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" wire:model="start_date">
                                    @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Confirmation Due Date --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Confirmation Due Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('confirmation_due_date') is-invalid @enderror" wire:model="confirmation_due_date">
                                    @error('confirmation_due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Extended Probation Toggle & Extended Due Date --}}
                                <div class="col-md-6">
                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" id="is_extended_check" wire:model.live="is_extended">
                                        <label class="form-check-label fw-semibold small" for="is_extended_check">
                                            Extend Probation Period
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Extended Due Date</label>
                                    <input type="date" class="form-control @error('extended_due_date') is-invalid @enderror" wire:model="extended_due_date" {{ !$is_extended ? 'disabled' : '' }}>
                                    @error('extended_due_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                @if($is_extended || $status === 'extended')
                                {{-- Extension Reason --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Extension Reason</label>
                                    <textarea class="form-control @error('extension_reason') is-invalid @enderror" rows="2" wire:model="extension_reason" placeholder="State reason for extending the probation period..."></textarea>
                                    @error('extension_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                @endif

                               
                                                              {{-- Asset Allocation --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Asset Allocation Details</label>
                                    <div class="d-flex gap-2 mb-2">
                                        <select class="form-select" wire:model="selectedAssetId">
                                            <option value="">Select Asset to Allocate...</option>
                                            @foreach ($assetsForAllocation as $a)
                                                <option value="{{ $a->id }}">{{ $a->asset_code }} - {{ $a->name }}@if($a->brand || $a->model) ({{ $a->brand }} {{ $a->model }})@endif</option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-outline-primary flex-shrink-0" wire:click="addAssetToAllocation">
                                            <i class="bi bi-plus-lg me-1"></i>Add
                                        </button>
                                    </div>

                                    @if(count($selectedAssets))
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            @foreach ($selectedAssets as $index => $sa)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle d-inline-flex align-items-center gap-2 px-3 py-2">
                                                    <i class="bi bi-box-seam"></i>{{ $sa['label'] }}
                                                    <i class="bi bi-x-lg" style="cursor:pointer;" wire:click="removeAssetFromAllocation({{ $index }})"></i>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted small fst-italic mb-0">No assets allocated yet. Select and click Add.</p>
                                    @endif

                                    @error('asset_allocation') <div class="text-danger small">{{ $message }}</div> @enderror
                                </div>

                                {{-- Official Confirmation Date --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Official Confirmation Date</label>
                                    <input type="date" class="form-control @error('confirmation_date') is-invalid @enderror" wire:model="confirmation_date">
                                    @error('confirmation_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Evaluation Notes --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Evaluation Notes & Review Feedback</label>
                                    <textarea class="form-control @error('evaluation_notes') is-invalid @enderror" rows="3" wire:model="evaluation_notes" placeholder="Evaluation notes on performance, behavior, task achievements during probation..."></textarea>
                                    @error('evaluation_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light rounded-3 px-4" wire:click="closeModals">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4">
                                <i class="bi bi-check-lg me-1"></i> {{ $isEditMode ? 'Update Probation Record' : 'Save Probation Record' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- View Details Modal --}}
    @if ($isViewModalOpen && $viewProbation)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width:45px; height:45px;">
                                {{ substr($viewProbation->employee->name ?? 'E', 0, 2) }}
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $viewProbation->employee->name ?? 'Employee Details' }}</h5>
                                <small class="text-muted">Employee Code: {{ $viewProbation->employee->employee_code ?? 'N/A' }} | Email: {{ $viewProbation->employee->email ?? 'N/A' }}</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Probation Status</span>
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-1.5 fw-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $viewProbation->status)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Confirmation Due Date</span>
                                    <strong class="text-dark">
                                        {{ $viewProbation->extended_due_date ? $viewProbation->extended_due_date->format('d M, Y') : ($viewProbation->confirmation_due_date ? $viewProbation->confirmation_due_date->format('d M, Y') : 'N/A') }}
                                    </strong>
                                </div>
                            </div>

                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-box-seam me-1 text-primary"></i>Asset Allocation List</h6>
                                <div class="p-3 bg-light rounded-3 text-dark" style="white-space: pre-line;">
                                    {{ $viewProbation->asset_allocation ?? 'No physical assets allocated.' }}
                                </div>
                            </div>

                            @if($viewProbation->is_extended || $viewProbation->extension_reason)
                            <div class="col-12">
                                <h6 class="fw-bold text-warning mb-2"><i class="bi bi-exclamation-circle me-1"></i>Probation Extension Details</h6>
                                <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 text-dark" style="white-space: pre-line;">
                                    <strong>Extended Due Date:</strong> {{ $viewProbation->extended_due_date ? $viewProbation->extended_due_date->format('d M, Y') : 'N/A' }}
                                    <br>
                                    <strong>Reason:</strong> {{ $viewProbation->extension_reason ?? 'Not specified' }}
                                </div>
                            </div>
                            @endif

                            @if($viewProbation->confirmation_date)
                            <div class="col-12">
                                <h6 class="fw-bold text-success mb-2"><i class="bi bi-patch-check me-1"></i>Official Confirmation Date</h6>
                                <div class="p-3 bg-success bg-opacity-10 rounded-3 fw-bold text-success">
                                    {{ $viewProbation->confirmation_date->format('d M, Y') }}
                                </div>
                            </div>
                            @endif

                            @if($viewProbation->evaluation_notes)
                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-2">Evaluation & Review Notes</h6>
                                <div class="p-3 bg-light rounded-3 text-dark" style="white-space: pre-line;">
                                    {{ $viewProbation->evaluation_notes }}
                                </div>
                            </div>
                            @endif

                            <div class="col-12 border-top pt-3 text-muted small d-flex justify-content-between">
                                <span>Created By: {{ $viewProbation->creator->name ?? 'System/Partner' }}</span>
                                <span>Created Date: {{ $viewProbation->created_at->format('d M, Y h:i A') }}</span>
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
