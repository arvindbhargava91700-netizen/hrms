<div>
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Form -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">{{ $isEditMode ? 'Edit Expense Category' : 'Add Expense Category' }}</h6>
                </div>
                <div class="card-body p-4">
                    <form wire:submit.prevent="saveCategory">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Category Name</label>
                            <input type="text" class="form-control" wire:model="name" placeholder="e.g. Petrol / Travel Allowance">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Calculation Type</label>
                            <select class="form-select" wire:model.live="type">
                                <option value="per_unit">Per Unit Rate (e.g. ₹6 per KM)</option>
                                <option value="max_limit">Fixed Max Limit (e.g. Max ₹500)</option>
                                <option value="actual">Actual Bill Amount</option>
                            </select>
                            @error('type') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @if($type === 'per_unit')
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Unit Name</label>
                                    <input type="text" class="form-control" wire:model="unit_name" placeholder="e.g. KM, Day, Liter">
                                    @error('unit_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase">Rate Per Unit (₹)</label>
                                    <input type="number" step="0.01" class="form-control" wire:model="rate_per_unit" placeholder="6.00">
                                    @error('rate_per_unit') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @elseif($type === 'max_limit')
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Max Allowed Amount (₹)</label>
                                <input type="number" step="0.01" class="form-control" wire:model="max_limit_amount" placeholder="500.00">
                                @error('max_limit_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Status</label>
                            <select class="form-select" wire:model="status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                {{ $isEditMode ? 'Update Category' : 'Create Category' }}
                            </button>
                            @if($isEditMode)
                                <button type="button" class="btn btn-light w-100" wire:click="resetForm">Cancel</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark">Expense Categories</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ count($categories) }} Categories</span>
                </div>
                <div class="card-body p-0">
                    @if(count($categories) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 border-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4 border-0 text-muted fw-semibold small">Category Name</th>
                                        <th class="border-0 text-muted fw-semibold small">Calculation Rule</th>
                                        <th class="border-0 text-muted fw-semibold small">Rate / Limit</th>
                                        <th class="border-0 text-muted fw-semibold small">Status</th>
                                        <th class="pe-4 border-0 text-muted fw-semibold small text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $cat)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark">{{ $cat->name }}</div>
                                        </td>
                                        <td class="py-3">
                                            @if($cat->type === 'per_unit')
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1">
                                                    <i class="bi bi-speedometer2 me-1"></i> Per Unit ({{ $cat->unit_name }})
                                                </span>
                                            @elseif($cat->type === 'max_limit')
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1">
                                                    <i class="bi bi-slash-circle me-1"></i> Max Limit
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1">
                                                    <i class="bi bi-receipt me-1"></i> Actual Bill
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if($cat->type === 'per_unit')
                                                <span class="fw-bold text-success">₹{{ number_format($cat->rate_per_unit, 2) }}</span> / {{ $cat->unit_name }}
                                            @elseif($cat->type === 'max_limit')
                                                Max <span class="fw-bold text-dark">₹{{ number_format($cat->max_limit_amount, 2) }}</span>
                                            @else
                                                <span class="text-muted small">Actual Amount</span>
                                            @endif
                                        </td>
                                        <td class="py-3">
                                            @if($cat->status === 'active')
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1">Active</span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="pe-4 py-3 text-end">
                                            <button type="button" class="btn btn-sm btn-light text-primary me-2" wire:click="editCategory('{{ $cat->id }}')">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light text-danger" wire:click="deleteCategory('{{ $cat->id }}')" wire:confirm="Are you sure you want to delete this category?">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-tag text-light mb-3" style="font-size: 3rem;"></i>
                            <h6>No Expense Categories Created</h6>
                            <p class="small">Add your first category using the form.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
