<div>
    @if (session('success'))
        <div class="alert alert-success mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex gap-3">
            <div>
                <h5 class="mb-0">KYC Fields</h5>
                <small class="text-muted">Define which identity and document inputs partners must submit.</small>
            </div>
            <button class="btn btn-primary ms-sm-auto" wire:click="openCreate">
                <i class="bi bi-plus-lg me-2"></i>Add Field
            </button>

        </div>

        <div class="table-responsive">
            <table class="table table-feetrack mb-0">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Required</th>
                        <th>Active</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requirements as $requirement)
                        <tr>
                            <td>
                                <div class="fw-600">{{ $requirement->label }}</div>
                                <div class="text-muted small">Key: {{ $requirement->key }}</div>
                                @if ($requirement->field_type === 'document')
                                    <div class="text-muted small">
                                        {{ $requirement->value_label ? 'Value: ' . $requirement->value_label . ' · ' : '' }}
                                        {{ $requirement->document_mode === 'front_back' ? 'Front + Back' : 'Single file' }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ strtoupper($requirement->field_type) }}</span>
                                @if ($requirement->field_type === 'text')
                                    <div class="text-muted small mt-1">{{ strtoupper($requirement->input_type) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $requirement->is_required ? 'bg-danger' : 'bg-secondary' }}">
                                    {{ $requirement->is_required ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $requirement->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $requirement->is_active ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary me-2"
                                    wire:click="openEdit('{{ $requirement->id }}')">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                    wire:click="delete('{{ $requirement->id }}')">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No KYC fields configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showModal)
        <div class="modal d-block" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editId ? 'Edit KYC Field' : 'Add KYC Field' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Label</label>
                                    <input type="text" class="form-control" wire:model.live="label"
                                        placeholder="Aadhaar Card">
                                    @error('label')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Key</label>
                                    <input type="text" class="form-control" wire:model="key"
                                        placeholder="aadhaar_card">
                                    @error('key')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Field Type</label>
                                    <select class="form-select" wire:model.live="fieldType">
                                        <option value="text">Text Input</option>
                                        <option value="document">Document Upload</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Input Type</label>
                                    <select class="form-select" wire:model="inputType">
                                        <option value="text">Text</option>
                                        <option value="number">Number</option>
                                        <option value="email">Email</option>
                                        <option value="tel">Phone</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Required</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" wire:model="isRequired">
                                        <label class="form-check-label">Yes / No</label>
                                    </div>
                                </div>

                                @if ($fieldType === 'document')
                                    <div class="col-md-6">
                                        <label class="form-label">Value Label</label>
                                        <input type="text" class="form-control" wire:model="valueLabel"
                                            placeholder="Aadhaar Number">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Document Mode</label>
                                        <select class="form-select" wire:model="documentMode">
                                            <option value="single">Single Upload</option>
                                            <option value="front_back">Front + Back</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" wire:model="hasValueField"
                                                id="hasValueField">
                                            <label class="form-check-label" for="hasValueField">Require number/text
                                                input</label>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-md-6">
                                    <label class="form-label">Placeholder</label>
                                    <input type="text" class="form-control" wire:model="placeholder"
                                        placeholder="Enter value">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Help Text</label>
                                    <input type="text" class="form-control" wire:model="helpText"
                                        placeholder="Short helper text">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Active</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" wire:model="isActive">
                                        <label class="form-check-label">Yes / No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                wire:click="$set('showModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Field</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
