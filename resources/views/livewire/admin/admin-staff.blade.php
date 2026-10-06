<div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="search-box">
                    <i class="bi bi-search text-muted"></i>
                    <input type="text" class="form-control border-0 bg-light" placeholder="Search admins..." wire:model.live.debounce.300ms="search">
                </div>
                <button class="btn btn-primary rounded-pill px-4" wire:click="createStaff">
                    <i class="bi bi-plus-lg me-2"></i> Add Admin
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffs as $staff)
                        <tr wire:key="staff-{{ $staff->id }}">
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        {{ substr($staff->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold">{{ $staff->name }}</div>
                                        <div class="text-muted small">{{ $staff->mobile ?? 'No phone' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $staff->email }}</td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" 
                                           wire:change="toggleStatus('{{ $staff->id }}')" 
                                           {{ $staff->status === 'active' ? 'checked' : '' }}
                                           @if($staff->id === auth()->id()) disabled @endif>
                                </div>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-light rounded-pill" wire:click="editStaff('{{ $staff->id }}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @if($staff->id !== auth()->id())
                                <button class="btn btn-sm btn-light text-danger rounded-pill ms-2" 
                                        onclick="confirm('Are you sure you want to delete this admin?') || event.stopImmediatePropagation()" 
                                        wire:click="deleteStaff('{{ $staff->id }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-4 d-block mb-3 opacity-50"></i>
                                No admin staff found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $staffs->links() }}
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">{{ $isEditMode ? 'Edit Admin' : 'Add New Admin' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="name" placeholder="Full name">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" wire:model="email" placeholder="Email address">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Mobile</label>
                            <input type="text" class="form-control" wire:model="mobile" placeholder="Mobile number">
                            @error('mobile') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Password @if(!$isEditMode)<span class="text-danger">*</span>@endif</label>
                            <input type="password" class="form-control" wire:model="password" placeholder="{{ $isEditMode ? 'Leave blank to keep current' : 'Password' }}">
                            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="fw-bold mb-3">Module Permissions</h6>
                    <p class="text-muted small mb-3">Select which modules this admin can access and manage.</p>
                    
                    <div class="row g-3">
                        @foreach($availablePermissions as $permission)
                        <div class="col-md-6 col-lg-4">
                            <div class="form-check custom-checkbox">
                                <input class="form-check-input" type="checkbox" 
                                       wire:model="selectedPermissions" 
                                       value="{{ $permission->name }}" 
                                       id="perm_{{ $permission->id }}">
                                <label class="form-check-label" for="perm_{{ $permission->id }}">
                                    {{ str_replace('_', ' ', ucwords(str_replace('admin_', '', $permission->name))) }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" wire:click="$set('showModal', false)">Cancel</button>
                    <button type="button" class="btn btn-primary px-4" wire:click="saveStaff">
                        <span wire:loading wire:target="saveStaff" class="spinner-border spinner-border-sm me-2"></span>
                        {{ $isEditMode ? 'Update' : 'Save' }} Admin
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
