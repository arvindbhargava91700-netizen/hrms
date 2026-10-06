<div>
    @if (session()->has('success_task_status'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 text-success"></i> {{ session('success_task_status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-1 fw-bold text-dark"><i class="bi bi-kanban me-2 text-primary"></i>Task Workflow Statuses</h5>
                <p class="text-muted small mb-0">Define and customize the statuses available across all staff task workflows and pipeline tracking.</p>
            </div>
            @if(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'))
            <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold btn-sm" wire:click="createStatus">
                <i class="bi bi-plus-circle me-1"></i> Add Task Status
            </button>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                    <tr>
                        <th class="py-3.5 px-4" style="width: 80px;">Order</th>
                        <th class="py-3.5 px-4">Status Name & Badge Preview</th>
                        <th class="py-3.5 px-4">Status</th>
                        @if(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'))
                        <th class="py-3.5 px-4 text-end">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($taskStatuses as $item)
                        @php
                            $colorThemes = [
                                'warning'   => ['style' => 'background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d;', 'dot' => '#d97706'],
                                'primary'   => ['style' => 'background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;', 'dot' => '#0284c7'],
                                'info'      => ['style' => 'background-color: #cffafe; color: #0e7490; border: 1px solid #a5f3fc;', 'dot' => '#0891b2'],
                                'success'   => ['style' => 'background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;', 'dot' => '#16a34a'],
                                'danger'    => ['style' => 'background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;', 'dot' => '#dc2626'],
                                'purple'    => ['style' => 'background-color: #f3e8ff; color: #7e22ce; border: 1px solid #e9d5ff;', 'dot' => '#9333ea'],
                                'teal'      => ['style' => 'background-color: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4;', 'dot' => '#0d9488'],
                                'secondary' => ['style' => 'background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;', 'dot' => '#64748b'],
                                'dark'      => ['style' => 'background-color: #1e293b; color: #ffffff; border: 1px solid #0f172a;', 'dot' => '#94a3b8'],
                            ];
                            $theme = $colorThemes[$item->color] ?? $colorThemes['primary'];
                        @endphp
                        <tr>
                            <td class="py-3.5 px-4 fw-bold text-muted">
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1">
                                    #{{ $item->order }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="badge rounded-pill px-3 py-1.5 fw-bold fs-6 d-inline-flex align-items-center" style="{{ $theme['style'] }}">
                                        <span class="d-inline-block rounded-circle me-1.5" style="width: 8px; height: 8px; background-color: {{ $theme['dot'] }};"></span>
                                        {{ $item->name }}
                                    </span>
                                    <span class="text-muted extra-small"><code>slug: {{ $item->slug }}</code></span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'))
                                    <button class="btn btn-sm p-0 border-0" wire:click="toggleActive({{ $item->id }})" title="Toggle active status">
                                        @if($item->status)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">Active</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1">Inactive</span>
                                        @endif
                                    </button>
                                @else
                                    @if($item->status)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1">Active</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-3 py-1">Inactive</span>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-end">
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('hrmssetting_manage'))
                                    <button class="btn btn-sm btn-outline-primary rounded-pill me-1 px-3" wire:click="editStatus({{ $item->id }})">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" wire:click="deleteStatus({{ $item->id }})" onclick="confirm('Are you sure you want to delete this task status?') || event.stopImmediatePropagation()">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="bi bi-kanban display-4 mb-3 d-block text-secondary"></i>
                                No task statuses configured yet. Click "Add Task Status" to create your workflow.
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
                <form wire:submit.prevent="saveStatus">
                    <div class="modal-header border-bottom py-3 px-4">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bi {{ $editingStatusId ? 'bi-pencil-square' : 'bi-plus-circle' }} text-primary me-2"></i>
                            {{ $editingStatusId ? 'Edit Task Status' : 'Add Task Status' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('isModalOpen', false)"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Status Name -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Status Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="name" placeholder="e.g. Under Review, In Testing, Blocked, Done">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Color Selector -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Color Badge Theme <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-2 pt-1">
                                @php
                                    $colors = [
                                        'warning'   => ['label' => 'Amber / Pending', 'bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#fcd34d'],
                                        'primary'   => ['label' => 'Blue / In Progress', 'bg' => '#e0f2fe', 'text' => '#0369a1', 'border' => '#bae6fd'],
                                        'info'      => ['label' => 'Cyan / Review', 'bg' => '#cffafe', 'text' => '#0e7490', 'border' => '#a5f3fc'],
                                        'success'   => ['label' => 'Green / Done', 'bg' => '#dcfce7', 'text' => '#15803d', 'border' => '#bbf7d0'],
                                        'danger'    => ['label' => 'Red / Cancelled', 'bg' => '#fee2e2', 'text' => '#b91c1c', 'border' => '#fecaca'],
                                        'purple'    => ['label' => 'Purple', 'bg' => '#f3e8ff', 'text' => '#7e22ce', 'border' => '#e9d5ff'],
                                        'teal'      => ['label' => 'Teal', 'bg' => '#ccfbf1', 'text' => '#0f766e', 'border' => '#99f6e4'],
                                        'secondary' => ['label' => 'Gray', 'bg' => '#f1f5f9', 'text' => '#334155', 'border' => '#cbd5e1'],
                                        'dark'      => ['label' => 'Dark', 'bg' => '#1e293b', 'text' => '#ffffff', 'border' => '#0f172a'],
                                    ];
                                @endphp
                                @foreach($colors as $cKey => $cVal)
                                    <button type="button" 
                                            class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1.5"
                                            style="background: {{ $cVal['bg'] }}; color: {{ $cVal['text'] }}; border: {{ $color === $cKey ? '2px solid ' . $cVal['text'] : '1px solid ' . $cVal['border'] }}; box-shadow: {{ $color === $cKey ? '0 0 0 2px rgba(0,0,0,0.08)' : 'none' }};"
                                            wire:click="$set('color', '{{ $cKey }}')">
                                        @if($color === $cKey) <i class="bi bi-check2-circle"></i> @endif
                                        {{ $cVal['label'] }}
                                    </button>
                                @endforeach
                            </div>
                            @error('color') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- Display Order -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Sequence / Display Order</label>
                            <input type="number" min="0" class="form-control" wire:model="order" placeholder="e.g. 1, 2, 3">
                            <small class="text-muted">Order in which the status appears in task filter dropdowns and pipeline lists.</small>
                            @error('order') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>



                        <!-- Active Status Switch -->
                        <div class="mb-2 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="taskStatusActiveSwitch" wire:model="status">
                            <label class="form-check-label fw-bold text-dark" for="taskStatusActiveSwitch">Active Status</label>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4 bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">Save Task Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
