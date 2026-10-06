<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-1 fw-bold text-dark"><i class="bi bi-folder-check me-2 text-primary"></i>Leave Categories & Balances</h5>
                <p class="text-muted small mb-0">Manage allowed leave types and default annual quotas for your company staff.</p>
            </div>
            @if(auth()->user()->isPartner() || auth()->user()->canAccess('leavecategory_create') || auth()->user()->canAccess('hrmssetting_manage'))
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold btn-sm" wire:click="createCategory">
                <i class="bi bi-plus-circle me-1"></i> Add Category
            </button>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                    <tr>
                        <th class="py-3.5 px-4">Category Name</th>
                        <th class="py-3.5 px-4">Default Yearly Balance (Days)</th>
                        <th class="py-3.5 px-4">Status</th>
                        @if(auth()->user()->isPartner() || auth()->user()->canAccess('leavecategory_update') || auth()->user()->canAccess('leavecategory_delete') || auth()->user()->canAccess('hrmssetting_manage'))
                        <th class="py-3.5 px-4 text-end">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td class="py-3.5 px-4 fw-bold text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.85rem;">
                                        <i class="bi bi-calendar2-week"></i>
                                    </span>
                                    <span>{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($category->is_unlimited)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-1.5 fw-semibold fs-6">
                                        <i class="bi bi-infinity me-1"></i> Unlimited
                                    </span>
                                @else
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-1.5 fw-semibold fs-6">
                                        <i class="bi bi-calendar-event me-1"></i> {{ $category->days }} Days
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($category->status)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">Active</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1">Inactive</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-end">
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('leavecategory_update') || auth()->user()->canAccess('hrmssetting_manage'))
                                    <button class="btn btn-sm btn-outline-primary rounded-pill me-1 px-3" wire:click="editCategory({{ $category->id }})">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                @endif
                                
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('leavecategory_delete') || auth()->user()->canAccess('hrmssetting_manage'))
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" wire:click="deleteCategory({{ $category->id }})" onclick="confirm('Are you sure you want to delete this category?') || event.stopImmediatePropagation()">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-folder-x display-4 mb-3 d-block text-secondary"></i>
                                No leave categories configured yet. Create your first category above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    @if($isModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <form wire:submit.prevent="saveCategory">
                    <div class="modal-header border-bottom py-3 px-4">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bi {{ $editingCategoryId ? 'bi-pencil-square' : 'bi-plus-circle' }} text-primary me-2"></i>
                            {{ $editingCategoryId ? 'Edit Leave Category' : 'Add Leave Category' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('isModalOpen', false)"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="name" placeholder="e.g. Paid Leave, Sick Leave, Unpaid Leave">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Unlimited Checkbox Option -->
                        <div class="mb-3 p-3 rounded-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="unlimitedSwitch" wire:model.live="is_unlimited">
                                <label class="form-check-label fw-bold text-dark" for="unlimitedSwitch">
                                    <i class="bi bi-infinity text-info me-1"></i> Unlimited Leave
                                </label>
                                <div class="text-muted extra-small mt-1">Check this if staff can take unlimited days for this leave type (e.g. Unpaid Leave).</div>
                            </div>
                        </div>

                        @if(!$is_unlimited)
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Yearly Balance (Days) <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control" wire:model="days" placeholder="e.g. 6, 12">
                            <small class="text-muted">Total number of days allowed for this leave category per year.</small>
                            @error('days') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="mb-2 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="statusSwitch" wire:model="status">
                            <label class="form-check-label fw-bold text-dark" for="statusSwitch">Active Status</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
