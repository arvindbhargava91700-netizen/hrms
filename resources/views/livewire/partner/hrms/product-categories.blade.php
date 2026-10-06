<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Product Categories</h5>
            <button class="btn btn-primary btn-sm" wire:click="createCategory">
                <i class="bi bi-plus-circle me-1"></i> Add Category
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $category->name }}</div>
                            </td>
                            <td>
                                @php
                                    $badgeClass = $category->status === 'active' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger';
                                @endphp
                                <span class="badge rounded-pill {{ $badgeClass }} px-3 py-2">
                                    {{ ucfirst($category->status) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button wire:click="editCategory({{ $category->id }})" class="btn btn-sm btn-outline-secondary me-1">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button wire:click="deleteCategory({{ $category->id }})" class="btn btn-sm btn-outline-danger" onclick="confirm('Are you sure you want to delete this category?') || event.stopImmediatePropagation()">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="bi bi-tags display-4 mb-3 d-block text-light"></i>
                                No categories found.
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingId ? 'Edit Category' : 'Add Category' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('isModalOpen', false)"></button>
                </div>
                
                <form wire:submit.prevent="saveCategory">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select wire:model="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

