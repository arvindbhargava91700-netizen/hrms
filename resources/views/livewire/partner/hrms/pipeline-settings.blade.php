<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Add/Edit Form -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">{{ $isEditMode ? 'Edit Pipeline Stage' : 'Add New Stage' }}</h6>
                </div>
                <div class="card-body p-4">
                    <form wire:submit.prevent="saveStage">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Stage Name</label>
                            <input type="text" class="form-control" wire:model="name" placeholder="e.g. Finance Approval">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Responsible Department</label>
                            <select class="form-select" wire:model.live="department_id">
                                <option value="">Select Department...</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <div class="form-text small">Select the department responsible for this stage.</div>
                            @error('department_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Assign to Staff (Optional)</label>
                            <select class="form-select" wire:model="assigned_to">
                                <option value="">All Users in Department</option>
                                @foreach($availableStaff as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->employee_code ?: 'EMP' }})@if($staff->designation) - {{ $staff->designation->name }}@endif</option>
                                @endforeach
                            </select>
                            <div class="form-text small">Select a specific staff member or leave as "All Users in Department" to allow everyone in the department to approve.</div>
                            @error('assigned_to') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4 form-check form-switch bg-light p-3 rounded border">
                            <input class="form-check-input ms-0 me-2" type="checkbox" id="targetSwitch" wire:model="counts_towards_target">
                            <label class="form-check-label fw-bold small text-dark" for="targetSwitch">Counts Towards Employee Target / Business</label>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                When an order is approved at this stage, the order's Amount Paid Now ($) will be added to the employee's business target.
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                {{ $isEditMode ? 'Update Stage' : 'Add Stage' }}
                            </button>
                            @if($isEditMode)
                                <button type="button" class="btn btn-light w-100" wire:click="resetForm">Cancel</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stages List -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">Current Pipeline Flow</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ count($stages) }} Stages</span>
                </div>
                <div class="card-body p-0">
                    @if(count($stages) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                                    <tr>
                                        <th class="py-3.5 ps-4" style="width: 80px;">Order</th>
                                        <th class="py-3.5 px-3">Stage Name</th>
                                        <th class="py-3.5 px-3">Department</th>
                                        <th class="py-3.5 px-3">Assigned Staff</th>
                                        <th class="py-3.5 px-3">Target Credit</th>
                                        <th class="py-3.5 pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stages as $index => $stage)
                                    <tr>
                                        <td class="ps-4 py-3.5 align-middle">
                                            <div class="d-flex flex-column gap-1" style="width: 30px;">
                                                @if(!$loop->first)
                                                    <button type="button" class="btn btn-sm btn-light py-0 px-1 text-muted hover-primary" wire:click="moveUp('{{ $stage->id }}')"><i class="bi bi-chevron-up"></i></button>
                                                @else
                                                    <div style="height: 24px;"></div>
                                                @endif
                                                <div class="text-center fw-bold text-primary">{{ $stage->order_index }}</div>
                                                @if(!$loop->last)
                                                    <button type="button" class="btn btn-sm btn-light py-0 px-1 text-muted hover-primary" wire:click="moveDown('{{ $stage->id }}')"><i class="bi bi-chevron-down"></i></button>
                                                @else
                                                    <div style="height: 24px;"></div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-3 align-middle">
                                            <div class="fw-bold text-dark">{{ $stage->name }}</div>
                                        </td>
                                        <td class="py-3.5 px-3 align-middle">
                                            @if($stage->department)
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1.5"><i class="bi bi-diagram-3 me-1"></i>{{ $stage->department->name }}</span>
                                            @else
                                                <span class="text-danger small">Department missing</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-3 align-middle">
                                            @if($stage->assignedUser)
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1.5">
                                                    <i class="bi bi-person-fill me-1"></i>{{ $stage->assignedUser->name }}
                                                </span>
                                            @else
                                                <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5">
                                                    <i class="bi bi-people me-1"></i>All Department Staff
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-3 align-middle">
                                            @if($stage->counts_towards_target)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1.5"><i class="bi bi-check-circle me-1"></i>Target Credit</span>
                                            @else
                                                <span class="badge bg-light text-muted border rounded-pill px-3 py-1.5">No</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 py-3.5 text-end align-middle">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-1 px-3" wire:click="editStage('{{ $stage->id }}')">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" wire:click="deleteStage('{{ $stage->id }}')" wire:confirm="Are you sure you want to delete this stage? This could affect pending orders.">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-diagram-2 text-light mb-3" style="font-size: 3rem;"></i>
                            <h6>No Pipeline Stages Defined</h6>
                            <p class="small">Use the form to add your first stage.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

