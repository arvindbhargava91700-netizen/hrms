<div>
    @if (session()->has('message'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-3 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Add Checklist Point Card -->
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-plus-circle me-2 text-primary"></i>Add Checklist Point</h5>
                    <small class="text-muted extra-small">Add questions employees must verify during check-in/out.</small>
                </div>
                <div class="card-body p-4">
                    <form wire:submit.prevent="addQuestion">
                        <div class="mb-3">
                            <label for="newQuestion" class="form-label fw-semibold text-dark small">Question / Requirement <span class="text-danger">*</span></label>
                            <input type="text" class="form-control bg-white" id="newQuestion" wire:model="newQuestion" placeholder="e.g., Are you wearing your ID Card?" required>
                            @error('newQuestion') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label for="newMode" class="form-label fw-semibold text-dark small">Working Mode <span class="text-danger">*</span></label>
                            <select class="form-select bg-white" id="newMode" wire:model="newMode" required>
                                <option value="punch_in">Punch In Only</option>
                                <option value="punch_out">Punch Out Only</option>
                                <option value="both">Both (Punch In & Out)</option>
                            </select>
                            @error('newMode') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold w-100 shadow-sm d-inline-flex align-items-center justify-content-center gap-2" style="font-size: 0.9rem;">
                            <i class="bi bi-check-circle-fill"></i> Add Point
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Current Checklist Table Card -->
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden h-100">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-list-check me-2 text-primary"></i>Current Checklist</h5>
                        <small class="text-muted extra-small">Active questions required for attendance verification.</small>
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1 fw-semibold">{{ count($checklists) }} Points</span>
                </div>
                <div class="card-body p-0">
                    @if(count($checklists) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                                    <tr>
                                        <th class="py-3.5 ps-4">Question / Requirement</th>
                                        <th class="py-3.5 px-3">Mode</th>
                                        <th class="py-3.5 px-3">Status</th>
                                        <th class="py-3.5 pe-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($checklists as $checklist)
                                        <tr>
                                            <td class="ps-4 py-3.5 align-middle">
                                                <span class="fw-bold text-dark">{{ $checklist->question }}</span>
                                            </td>
                                            <td class="py-3.5 px-3 align-middle">
                                                @if($checklist->mode == 'punch_in')
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-1.5 rounded-pill">Punch In</span>
                                                @elseif($checklist->mode == 'punch_out')
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-1.5 rounded-pill">Punch Out</span>
                                                @else
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 rounded-pill">Both</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-3 align-middle">
                                                @if($checklist->is_active)
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1.5 rounded-pill">Active</span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1.5 rounded-pill">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="pe-4 py-3.5 text-end align-middle">
                                                <div class="d-flex justify-content-end align-items-center gap-1.5">
                                                    <button type="button" wire:click="toggleActive({{ $checklist->id }})" class="btn btn-sm {{ $checklist->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-pill px-3" style="font-size:0.8rem;" title="Toggle Status">
                                                        <i class="bi {{ $checklist->is_active ? 'bi-pause-circle me-1' : 'bi-play-circle me-1' }}"></i>{{ $checklist->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                    <button type="button" wire:click="deleteQuestion({{ $checklist->id }})" class="btn btn-sm btn-outline-danger rounded-pill px-3" style="font-size:0.8rem;" title="Delete" onclick="confirm('Are you sure you want to delete this question?') || event.stopImmediatePropagation()">
                                                        <i class="bi bi-trash me-1"></i>Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-clipboard-x text-muted display-4"></i>
                            </div>
                            <h5 class="text-muted fw-bold">No checklist points added</h5>
                            <p class="text-muted small">Add requirements that employees must check off before marking attendance.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
