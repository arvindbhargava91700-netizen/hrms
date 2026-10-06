<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-card-checklist text-danger me-2"></i>Exit Reasons Management
            </h4>
            <p class="text-muted small mb-0">Configure dynamic voluntary and involuntary reasons used across employee exit logs and attrition reports.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('partner.hrms.attrition') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-person-x-fill"></i> Attrition Analytics
            </a>
            @if(auth()->user()->isPartner() || auth()->user()->canAccess('exit_reason_create') || auth()->user()->canAccess('exitreason_create'))
                <button wire:click="openCreateModal" class="btn btn-danger btn-sm d-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-plus-circle-fill"></i> Add Exit Reason
                </button>
            @endif
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold mb-1">Search Reason</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search reason name...">
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Exit Type</label>
                    <select wire:model.live="filterType" class="form-select form-select-sm">
                        <option value="all">All Types</option>
                        <option value="voluntary">Voluntary</option>
                        <option value="involuntary">Involuntary</option>
                    </select>
                </div>
                <div class="col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Status</label>
                    <select wire:model.live="filterStatus" class="form-select form-select-sm">
                        <option value="all">All Status</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <label class="form-label text-muted small fw-bold mb-1">Per Page</label>
                    <select wire:model.live="perPage" class="form-select form-select-sm">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-list-stars me-2 text-danger"></i>Exit Reasons List
            </h6>
            <span class="badge bg-light text-muted border">Total: {{ $reasons->total() }}</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3" style="width: 50px;">#</th>
                        <th>Reason Name</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Scope</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($reasons as $index => $reason)
                        <tr>
                            <td class="ps-3 text-muted fw-bold">{{ $reasons->firstItem() + $index }}</td>
                            <td>
                                <span class="fw-bold text-dark">{{ $reason->name }}</span>
                            </td>
                            <td>
                                @if($reason->type === 'voluntary')
                                    <span class="badge bg-warning text-dark px-2 py-1">
                                        <i class="bi bi-person-walking me-1"></i>Voluntary
                                    </span>
                                @else
                                    <span class="badge bg-danger text-white px-2 py-1">
                                        <i class="bi bi-slash-circle me-1"></i>Involuntary
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('exit_reason_edit') || auth()->user()->canAccess('exitreason_update'))
                                    <button wire:click="toggleStatus({{ $reason->id }})" class="btn btn-sm p-0 border-0" title="Click to toggle status">
                                        @if($reason->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                                <i class="bi bi-check-circle-fill me-1"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 py-1">
                                                <i class="bi bi-x-circle-fill me-1"></i>Inactive
                                            </span>
                                        @endif
                                    </button>
                                @else
                                    @if($reason->is_active)
                                        <span class="badge bg-success text-white px-2 py-1">Active</span>
                                    @else
                                        <span class="badge bg-secondary text-white px-2 py-1">Inactive</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if(is_null($reason->partner_id))
                                    <span class="badge bg-light text-secondary border" title="System default exit reason">
                                        <i class="bi bi-shield-check me-1 text-primary"></i>System Default
                                    </span>
                                @else
                                    <span class="badge bg-light text-dark border" title="Custom reason created by your organization">
                                        <i class="bi bi-building me-1 text-success"></i>Custom
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('exit_reason_edit') || auth()->user()->canAccess('exitreason_update'))
                                    <button wire:click="editExitReason({{ $reason->id }})" class="btn btn-sm btn-outline-primary me-1" title="Edit Reason">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @endif

                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('exit_reason_delete') || auth()->user()->canAccess('exitreason_delete'))
                                    <button wire:click="deleteExitReason({{ $reason->id }})" wire:confirm="Are you sure you want to delete this exit reason?" class="btn btn-sm btn-outline-danger" title="Delete Reason">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No exit reasons found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reasons->hasPages())
            <div class="card-footer bg-white py-3 border-0">
                {{ $reasons->links() }}
            </div>
        @endif
    </div>

    {{-- Add / Edit Modal --}}
    @if($isModalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header border-0 bg-light py-3">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bi bi-card-heading text-danger me-2"></i>
                            {{ $editingId ? 'Edit Exit Reason' : 'Add Exit Reason' }}
                        </h5>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>

                    <form wire:submit.prevent="saveExitReason">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Reason Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Better Opportunity, Personal Reason...">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Exit Type <span class="text-danger">*</span></label>
                                <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="voluntary">Voluntary (Self-Resignation / Employee Initiated)</option>
                                    <option value="involuntary">Involuntary (Termination / Layoff / Company Initiated)</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Status <span class="text-danger">*</span></label>
                                <select wire:model="is_active" class="form-select @error('is_active') is-invalid @enderror">
                                    <option value="1">Active (Available in Exit Forms)</option>
                                    <option value="0">Inactive (Hidden from Exit Forms)</option>
                                </select>
                                @error('is_active') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="modal-footer border-0 bg-light py-2">
                            <button type="button" wire:click="closeModal" class="btn btn-light btn-sm px-3">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-sm px-4 d-flex align-items-center gap-1 shadow-sm">
                                <i class="bi bi-check-lg"></i> {{ $editingId ? 'Update Exit Reason' : 'Save Exit Reason' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
