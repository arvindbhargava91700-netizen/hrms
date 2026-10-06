<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $this->tab === 'modules' ? 'active' : '' }}" href="#" wire:click.prevent="switchTab('modules')">
                <i class="bi bi-grid me-1"></i> Modules
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $this->tab === 'commission' ? 'active' : '' }}" href="#" wire:click.prevent="switchTab('commission')">
                <i class="bi bi-percent me-1"></i> App Commission
            </a>
        </li>
    </ul>

    @if ($this->tab === 'commission')
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">App Commission</h5>
            </div>
            <div class="card-body">
                <form wire:submit.prevent="saveCommission">
                    <div class="mb-3">
                        <label class="form-label fw-600">App Commission (% of transaction)</label>
                        <input type="number" step="0.01" min="0" class="form-control" wire:model="appCommission">
                        @error('appCommission') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Save Commission</button>
                </form>
            </div>
        </div>
    @else
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Modules</h5>
            <button class="btn btn-primary btn-sm" wire:click="createModule">
                <i class="bi bi-plus-circle me-1"></i> Add Module
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Module Name</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modules as $module)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px;">
                                        <i class="{{ $module->icon ? str_replace('fas fa-', 'bi bi-', $module->icon) : 'bi bi-puzzle' }}"></i>
                                    </div>
                                    <div class="fw-600">{{ $module->name }}</div>
                                </div>
                            </td>
                            <td><span class="text-muted">{{ $module->slug }}</span></td>
                            <td>
                                <span class="badge-status badge-{{ $module->is_active ? 'active' : 'suspended' }}">
                                    {{ $module->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-secondary me-1" wire:click="editModule({{ $module->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" wire:click="deleteModule({{ $module->id }})" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No modules found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Create/Edit Module Modal -->
    @if($isModuleModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <form wire:submit.prevent="saveModule">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingModuleId ? 'Edit Module' : 'Create Module' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('isModuleModalOpen', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-600">Name</label>
                            <input type="text" class="form-control" wire:model="moduleName">
                            @error('moduleName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Slug</label>
                            <input type="text" class="form-control" wire:model="moduleSlug">
                            @error('moduleSlug') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Description</label>
                            <textarea class="form-control" wire:model="moduleDescription" rows="3"></textarea>
                            @error('moduleDescription') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Icon Class (Bootstrap Icons)</label>
                            <input type="text" class="form-control" wire:model="moduleIcon" placeholder="e.g. bi-people">
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" wire:model="moduleIsActive" id="activeSwitch">
                            <label class="form-check-label" for="activeSwitch">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isModuleModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Module</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
