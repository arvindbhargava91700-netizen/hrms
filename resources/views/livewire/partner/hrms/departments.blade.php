<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Departments</h5>
            @if(auth()->user()->isPartner() || auth()->user()->canAccess('department_create'))
            <button class="btn btn-primary btn-sm" wire:click="createDepartment">
                <i class="bi bi-plus-circle me-1"></i> Add Department
            </button>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 40px;"></th>
                        <th>Department Name</th>
                        <th>Parent</th>
                        <th>Employees</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="department-sortable">
                    @forelse($departments as $dept)
                        <tr data-id="{{ $dept->id }}">
                            <td class="drag-handle text-center" style="cursor: grab; color: #adb5bd;">
                                <i class="bi bi-grip-vertical"></i>
                            </td>
                            <td class="fw-600 text-dark">
                                {{ $dept->name }}
                                @if($dept->description)
                                    <br><small class="text-muted">{{ Str::limit($dept->description, 30) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($dept->parent)
                                    <span class="badge bg-light text-dark border">{{ $dept->parent->name }}</span>
                                @else
                                    -
                                @endif
                            </td>
                           
                            <td>
                                <span class="badge bg-secondary">{{ $dept->employees_count }}</span>
                            </td>
                            <td class="text-end">
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('department_update'))
                                <button class="btn btn-sm btn-outline-secondary me-1" wire:click="editDepartment({{ $dept->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endif
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('department_delete'))
                                <button class="btn btn-sm btn-outline-danger" wire:click="deleteDepartment({{ $dept->id }})" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-diagram-3 display-4 mb-3 d-block text-light"></i>
                                No departments added yet.
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
                <form wire:submit.prevent="saveDepartment">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingDepartmentId ? 'Edit Department' : 'Add Department' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('isModalOpen', false)"></button>
                    </div>
                    <div class="modal-body">
<div class="mb-3">
                            <label class="form-label fw-600">Department Name</label>
                            <input type="text" class="form-control" wire:model="name" placeholder="e.g. Engineering">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                   
                        <div class="mb-3">
                            <label class="form-label fw-600">Parent Department</label>
                            <select class="form-select" wire:model="parent_id">
                                <option value="">None (Top Level)</option>
                                @foreach($departments as $d)
                                    @if($d->id !== $editingDepartmentId)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @error('parent_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3 p-3 bg-light rounded border">
                            <label class="form-label fw-600 mb-3"><i class="bi bi-diagram-3 me-2 text-primary"></i>Branch-wise Department Heads</label>
                            @forelse($branches as $branch)
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-5 fw-semibold text-secondary">
                                        <i class="bi bi-building me-1"></i> {{ $branch->name }}
                                    </div>
                                    <div class="col-md-7">
                                        <select class="form-select form-select-sm" wire:model="branch_heads.{{ $branch->id }}">
                                            <option value="">-- No Head Assigned --</option>
                                            @foreach($employees as $emp)
                                                {{-- Allow selecting any team employee, but optionally filter by branch if strict --}}
                                                <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted small fst-italic">No branches found. Please create branches first to assign heads.</div>
                            @endforelse
                            @error('branch_heads') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-600">Description</label>
                            <textarea class="form-control" wire:model="description" rows="2" placeholder="Optional"></textarea>
                            @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('department-sortable');
        if (!el) return;

        Sortable.create(el, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-light',
            onEnd: function () {
                const ids = Array.from(el.querySelectorAll('tr[data-id]'))
                    .map(tr => tr.dataset.id);
                if (window.Livewire && @this) {
                    @this.call('updateSortOrder', ids);
                }
            }
        });
    });
</script>
@endpush
