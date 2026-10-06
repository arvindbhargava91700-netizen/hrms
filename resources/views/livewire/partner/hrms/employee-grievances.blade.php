<div>
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #0f172a;">Grievance & Disciplinary Management</h4>
            <p class="text-muted small mb-0">Record, investigate, and resolve employee complaints, warnings, show-cause notices & disciplinary actions</p>
        </div>
        @if(auth()->user()->isPartner() || auth()->user()->canAccess('grievance_create'))
            <button class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-sm" wire:click="createGrievance" style="border-radius: 8px;">
                <i class="bi bi-shield-exclamation fs-6"></i>
                Add New Case / Action
            </button>
        @endif
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 8px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        {{-- Total Cases --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Total Cases</span>
                        <h3 class="fw-bold mb-0" style="color: #1e293b;">{{ $totalCases }}</h3>
                    </div>
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: #e0f2fe; color: #0284c7; width: 48px; height: 48px;">
                        <i class="bi bi-journal-text fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Complaints --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Complaints</span>
                        <h3 class="fw-bold mb-0 text-danger">{{ $complaintsCount }}</h3>
                    </div>
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: #fee2e2; color: #dc2626; width: 48px; height: 48px;">
                        <i class="bi bi-megaphone-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Under Investigation --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Under Investigation</span>
                        <h3 class="fw-bold mb-0 text-warning">{{ $investigatingCount }}</h3>
                    </div>
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: #fef3c7; color: #d97706; width: 48px; height: 48px;">
                        <i class="bi bi-search fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resolved Cases --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Resolved / Closed</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $resolvedCount }}</h3>
                    </div>
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: #dcfce7; color: #16a34a; width: 48px; height: 48px;">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                {{-- Search Bar --}}
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Search by case title, description, employee..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                {{-- Record Type Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <select class="form-select bg-light border-0" wire:model.live="typeFilter">
                        <option value="">All Record Types</option>
                        <option value="complaint">Complaints</option>
                        <option value="warning">Warnings</option>
                        <option value="show_cause">Show-Cause Notices</option>
                        <option value="disciplinary_action">Disciplinary Actions</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="col-12 col-sm-6 col-md-4">
                    <select class="form-select bg-light border-0" wire:model.live="statusFilter">
                        <option value="">All Resolution Statuses</option>
                        <option value="open">Open / Pending</option>
                        <option value="under_investigation">Under Investigation</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                <thead class="bg-light text-muted fw-semibold">
                    <tr>
                        <th class="ps-3 py-3">Employee</th>
                        <th class="py-3">Record Type</th>
                        <th class="py-3">Case Title & Incident Date</th>
                        <th class="py-3">Resolution Status</th>
                        <th class="pe-3 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grievances as $grv)
                        <tr>
                            {{-- Employee Details --}}
                            <td class="ps-3 py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($grv->employee->name ?? 'E', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $grv->employee->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $grv->employee->employee_code ?? 'EMP' }}</small>
                                    </div>
                                </div>
                            </td>

                            {{-- Record Type Badge --}}
                            <td class="py-3">
                                @if($grv->record_type === 'complaint')
                                    <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold px-2 py-1"><i class="bi bi-exclamation-octagon me-1"></i>Complaint</span>
                                @elseif($grv->record_type === 'warning')
                                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold px-2 py-1" style="color: #d97706;"><i class="bi bi-exclamation-triangle me-1"></i>Warning</span>
                                @elseif($grv->record_type === 'show_cause')
                                    <span class="badge bg-purple bg-opacity-10 text-purple fw-semibold px-2 py-1" style="color: #8b5cf6; background: #f3e8ff;"><i class="bi bi-envelope-paper me-1"></i>Show-Cause Notice</span>
                                @else
                                    <span class="badge bg-dark bg-opacity-10 text-dark fw-semibold px-2 py-1"><i class="bi bi-shield-x me-1"></i>Disciplinary Action</span>
                                @endif
                            </td>

                            {{-- Title & Incident Date --}}
                            <td class="py-3">
                                <div class="fw-semibold text-dark">{{ $grv->title }}</div>
                                @if($grv->incident_date)
                                    <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ $grv->incident_date->format('d M Y') }}</small>
                                @endif
                            </td>

                            {{-- Resolution Status Badge --}}
                            <td class="py-3">
                                @if($grv->status === 'open')
                                    <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold px-2 py-1"><i class="bi bi-clock me-1"></i>Open</span>
                                @elseif($grv->status === 'under_investigation')
                                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold px-2 py-1"><i class="bi bi-search me-1"></i>Under Investigation</span>
                                @elseif($grv->status === 'resolved')
                                    <span class="badge bg-success bg-opacity-10 text-success fw-semibold px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Resolved</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold px-2 py-1"><i class="bi bi-archive me-1"></i>Closed</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="pe-3 py-3 text-end">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light text-primary" wire:click="viewGrievanceDetails({{ $grv->id }})" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if($grv->file_path)
                                        <a href="{{ asset('storage/' . $grv->file_path) }}" target="_blank" class="btn btn-sm btn-light text-success" title="Download Notice / Attachment">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('grievance_update'))
                                        <button class="btn btn-sm btn-light text-secondary" wire:click="editGrievance({{ $grv->id }})" title="Edit Case">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('grievance_delete'))
                                        <button class="btn btn-sm btn-light text-danger" wire:click="deleteGrievance({{ $grv->id }})" wire:confirm="Are you sure you want to delete this record?" title="Delete Record">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-shield-check fs-1 opacity-50 d-block mb-2"></i>
                                    <h6>No Grievances or Disciplinary Records Found</h6>
                                    <p class="small mb-0">Record employee complaints, warnings, show-cause notices, and disciplinary resolutions</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($grievances->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-3">
                {{ $grievances->links() }}
            </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if ($isFormModalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" style="color: #0f172a;">
                            {{ $isEditMode ? 'Edit Grievance / Disciplinary Record' : 'Record New Grievance / Disciplinary Action' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>

                    <form wire:submit.prevent="saveGrievance">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                {{-- Employee Selection --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Employee Concerned <span class="text-danger">*</span></label>
                                    <select class="form-select @error('employee_id') is-invalid @enderror" wire:model="employee_id">
                                        <option value="">Select Employee...</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}" @selected($employee_id == $emp->id)>{{ $emp->name }} ({{ $emp->employee_code ?? 'EMP' }})</option>
                                        @endforeach
                                    </select>
                                    @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Record Type --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Record Type <span class="text-danger">*</span></label>
                                    <select class="form-select @error('record_type') is-invalid @enderror" wire:model="record_type">
                                        <option value="complaint" @selected($record_type === 'complaint')>Complaint / Grievance</option>
                                        <option value="warning" @selected($record_type === 'warning')>Official Warning</option>
                                        <option value="show_cause" @selected($record_type === 'show_cause')>Show-Cause Notice</option>
                                        <option value="disciplinary_action" @selected($record_type === 'disciplinary_action')>Disciplinary Action</option>
                                    </select>
                                    @error('record_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Case Title --}}
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold small">Case Title / Subject <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model="title" placeholder="e.g. Unauthorized Absence Warning / Harassment Complaint">
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Incident Date --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Incident / Issue Date</label>
                                    <input type="date" class="form-control @error('incident_date') is-invalid @enderror" wire:model="incident_date">
                                    @error('incident_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Description / Incident Details --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Description / Incident Details</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" wire:model="description" rows="3" placeholder="Provide complete facts, background, and statements of the incident..."></textarea>
                                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Action Taken (Disciplinary penalty/warning terms) --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Action Taken / Warning Issued</label>
                                    <textarea class="form-control @error('action_taken') is-invalid @enderror" wire:model="action_taken" rows="2" placeholder="Details of written warning, suspension days, or penalties..."></textarea>
                                    @error('action_taken') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Resolution Notes --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Resolution / Committee Findings</label>
                                    <textarea class="form-control @error('resolution_notes') is-invalid @enderror" wire:model="resolution_notes" rows="2" placeholder="Investigation outcome, employee explanation response, or final settlement..."></textarea>
                                    @error('resolution_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Resolution Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Resolution Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                        <option value="open" @selected($status === 'open')>Open / Pending Response</option>
                                        <option value="under_investigation" @selected($status === 'under_investigation')>Under Investigation</option>
                                        <option value="resolved" @selected($status === 'resolved')>Resolved</option>
                                        <option value="closed" @selected($status === 'closed')>Closed</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- File Upload Attachment --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Attach Warning Copy / Show-cause PDF</label>
                                    <input type="file" class="form-control @error('new_file') is-invalid @enderror" wire:model="new_file">
                                    <div wire:loading wire:target="new_file" class="small text-primary mt-1">Uploading attachment...</div>
                                    @error('new_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($existing_file_path && !$new_file)
                                        <div class="small text-muted mt-1">
                                            Attachment: <a href="{{ asset('storage/' . $existing_file_path) }}" target="_blank" class="text-primary"><i class="bi bi-file-earmark-arrow-down me-1"></i>View Attached Document</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                            <button type="button" class="btn btn-light px-4 fw-semibold" wire:click="closeModals" style="border-radius: 8px;">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm" style="border-radius: 8px;">
                                <i class="bi bi-check-lg me-1"></i> {{ $isEditMode ? 'Update Case Record' : 'Save Record' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- View Details Modal --}}
    @if ($isViewModalOpen && $viewGrievance)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" style="color: #0f172a;">Case Details</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ $viewGrievance->title }}</h6>
                                <span class="badge bg-light text-dark border">{{ strtoupper(str_replace('_', ' ', $viewGrievance->record_type)) }}</span>
                            </div>
                            <div>
                                @if($viewGrievance->status === 'open')
                                    <span class="badge bg-danger px-3 py-2">Open</span>
                                @elseif($viewGrievance->status === 'under_investigation')
                                    <span class="badge bg-warning text-dark px-3 py-2">Under Investigation</span>
                                @elseif($viewGrievance->status === 'resolved')
                                    <span class="badge bg-success px-3 py-2">Resolved</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2">Closed</span>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Employee Concerned:</span>
                                <strong class="text-dark">{{ $viewGrievance->employee->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Incident Date:</span>
                                <strong class="text-dark">{{ $viewGrievance->incident_date ? $viewGrievance->incident_date->format('d M Y') : 'N/A' }}</strong>
                            </div>
                            @if($viewGrievance->description)
                                <div class="col-12">
                                    <span class="text-muted d-block">Description:</span>
                                    <div class="p-2 bg-light rounded text-dark mt-1">{{ $viewGrievance->description }}</div>
                                </div>
                            @endif
                            @if($viewGrievance->action_taken)
                                <div class="col-12">
                                    <span class="text-muted d-block">Action Taken / Warning:</span>
                                    <div class="p-2 bg-warning bg-opacity-10 rounded text-dark mt-1">{{ $viewGrievance->action_taken }}</div>
                                </div>
                            @endif
                            @if($viewGrievance->resolution_notes)
                                <div class="col-12">
                                    <span class="text-muted d-block">Resolution / Outcome:</span>
                                    <div class="p-2 bg-success bg-opacity-10 rounded text-dark mt-1">{{ $viewGrievance->resolution_notes }}</div>
                                </div>
                            @endif
                            @if($viewGrievance->file_path)
                                <div class="col-12 mt-3 text-center">
                                    <a href="{{ asset('storage/' . $viewGrievance->file_path) }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 fw-semibold">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Open & Download Attachment / Notice PDF
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light px-4 fw-semibold w-100" wire:click="closeModals" style="border-radius: 8px;">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
