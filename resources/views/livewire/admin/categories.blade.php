<div>
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-grid"></i></div>
                <div>
                    <div class="stat-label">Total Categories</div>
                    <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle text-success"></i></div>
                <div>
                    <div class="stat-label">Active Categories</div>
                    <div class="stat-value">{{ number_format($stats['active'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="stat-card" style="{{ ($stats['inactive'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-secondary);' : '' }}">
                <div class="stat-icon bg-secondary bg-opacity-10"><i class="bi bi-dash-circle text-secondary"></i></div>
                <div>
                    <div class="stat-label">Inactive Categories</div>
                    <div class="stat-value">{{ number_format($stats['inactive'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header d-flex gap-3">
            <div>
                <h5 class="mb-0">Service Categories</h5>
                <small class="text-muted">Filter, manage, and export categories.</small>
            </div>
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <button class="btn btn-primary ms-sm-auto" wire:click="openCreate">
                    <i class="bi bi-plus-lg me-2"></i>Add Category
                </button>
                <a href="{{ $this->exportUrl }}" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Listings</th>
                        <th>Custom Fields</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="cat-badge"
                                        style="background: rgba(37,99,235,0.1); color: var(--primary);">
                                        @if(\Illuminate\Support\Str::startsWith($category->icon, 'bi-'))
                                            <i class="bi {{ $category->icon }}"></i>
                                        @elseif($category->icon)
                                            <img src="{{ asset('storage/' . $category->icon) }}" alt="icon" style="width: 24px; height: 24px; object-fit: contain;">
                                        @else
                                            <i class="bi bi-grid"></i>
                                        @endif
                                    </div>
                                    <div class="fw-600">{{ $category->name }}</div>
                                </div>
                            </td>
                            <td>{{ $category->listings_count }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary"
                                    wire:click="openFields('{{ $category->id }}')">
                                    <i class="bi bi-list-check me-1"></i> {{ $category->custom_fields_count }} Fields
                                </button>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        {{ $category->is_active ? 'checked' : '' }}
                                        wire:click="toggleActive('{{ $category->id }}')">
                                </div>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-icon btn-outline-primary btn-sm"
                                    wire:click="openEdit('{{ $category->id }}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Category Modal --}}
    @if ($showModal)
        <div class="modal d-block" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editId ? 'Edit Category' : 'New Category' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" wire:model="name" required>
                                @error('name')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Category Icon (Image)</label>
                                <input type="file" class="form-control" wire:model="icon" accept="image/*">
                                <div wire:loading wire:target="icon" class="text-primary mt-1 small">Uploading...</div>
                                @if ($icon && !is_string($icon))
                                    <div class="mt-2">
                                        <img src="{{ $icon->temporaryUrl() }}" alt="Preview" style="max-height: 50px;">
                                    </div>
                                @elseif(is_string($icon) && \Illuminate\Support\Str::startsWith($icon, 'bi-'))
                                    <div class="mt-2">
                                        <i class="bi {{ $icon }} fs-4 text-primary"></i>
                                    </div>
                                @elseif(is_string($icon) && $icon)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $icon) }}" alt="Current Icon" style="max-height: 50px;">
                                    </div>
                                @endif
                                @error('icon')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="isActiveCheck" wire:model="is_active">
                                <label class="form-check-label" for="isActiveCheck">Active</label>
                            </div>
                            <hr class="my-3">
                            <h6>Modules</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="hasShiftsCheck" wire:model="has_shifts">
                                <label class="form-check-label" for="hasShiftsCheck">Enable Shifts</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="hasTrainersCheck" wire:model="has_trainers">
                                <label class="form-check-label" for="hasTrainersCheck">Enable Trainers</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="hasRoomsCheck" wire:model="has_rooms">
                                <label class="form-check-label" for="hasRoomsCheck">Enable Floors & Rooms</label>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="hasPackagesCheck" wire:model="has_packages">
                                <label class="form-check-label" for="hasPackagesCheck">Enable Packages (Pricing Plans)</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="hasAttendanceCheck" wire:model="has_attendance">
                                <label class="form-check-label" for="hasAttendanceCheck">
                                    <i class="bi bi-calendar-check me-1 text-success"></i>Enable Attendance (Daily Punch-in / Punch-out)
                                </label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                wire:click="$set('showModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Custom Fields Modal --}}
    @if ($showFieldModal)
        <div class="modal d-block" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title">Dynamic Fields</h5>
                        <button type="button" class="btn-close" wire:click="$set('showFieldModal', false)"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <div class="card mb-4 bg-light shadow-none">
                            <div class="card-body">
                                <form wire:submit="addField" class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label">Field Label</label>
                                        <input type="text" class="form-control" wire:model="fieldLabel" required>
                                        @error('fieldLabel') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Type</label>
                                        <select class="form-select" wire:model="fieldType">
                                            <option value="text">Text</option>
                                            <option value="number">Number</option>
                                            <option value="checkbox">Yes/No</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 pb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" wire:model="fieldRequired"
                                                id="reqCheck">
                                            <label class="form-check-label" for="reqCheck">Required</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100">Add</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th>Type</th>
                                        <th>Required</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($selectedFields as $field)
                                        <tr>
                                            <td class="fw-500">{{ $field->label }}</td>
                                            <td><code>{{ $field->field_type }}</code></td>
                                            <td>
                                                @if ($field->is_required)
                                                    <span class="badge bg-danger">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <button class="btn btn-icon btn-sm btn-outline-danger"
                                                    wire:click="deleteField({{ $field->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No custom fields
                                                defined.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
