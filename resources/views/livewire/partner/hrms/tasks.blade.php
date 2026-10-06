<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Employee Tasks</h5>
            <div class="d-flex gap-2">
                @if(Auth::user()->canAccess('task_create') || (request('scope') === 'me' && Auth::user()->canAccess('task_viewOwn')))
                <button class="btn btn-primary btn-sm" wire:click="createTask">
                    <i class="bi bi-plus-circle me-1"></i> New Task
                </button>
                @endif
            </div>
        </div>
        @if(auth()->user()->canAccess('task_viewAny') || auth()->user()->canAccess('task_viewTeam'))
        <div class="card-body pb-0">
            @include('partials.hrms-filters', ['viewAnyPermission' => 'task_viewAny'])
        </div>
        @endif
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Task Title</th>
                        <th>Assigned To</th>
                        <th>Assigned By</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        @php
                            $matchedStatus = $taskStatuses->firstWhere('slug', $task->status) ?? $taskStatuses->firstWhere('name', $task->status);
                            $isCompleted = ($task->status === 'completed') || ($matchedStatus && $matchedStatus->is_completed);
                            
                            $endDateVal = $task->end_date ?? $task->due_date;
                            $endDateCarbon = $endDateVal ? Carbon\Carbon::parse($endDateVal)->endOfDay() : null;
                            $isOverdue = !$isCompleted && $endDateCarbon && $endDateCarbon->isPast();

                            $statusColor = $matchedStatus->color ?? match($task->status) {
                                'pending'     => 'warning',
                                'in_progress' => 'primary',
                                'completed'   => 'success',
                                default       => 'secondary'
                            };

                            $colorStyles = [
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

                            $theme = $colorStyles[$statusColor] ?? $colorStyles['secondary'];
                        @endphp
                        <tr class="{{ $isOverdue ? 'bg-danger bg-opacity-10' : '' }}">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-dark">{{ $task->title }}</span>
                                    @if($isOverdue)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Overdue
                                        </span>
                                    @endif
                                </div>
                                <div class="text-muted small text-truncate" style="max-width: 200px;">{{ $task->description ?? 'No description' }}</div>
                                @if($task->remark)
                                    <div class="small text-info mt-1"><strong>Remark:</strong> {{ $task->remark }}</div>
                                @endif
                            </td>
                            <td>{{ $task->employee ? $task->employee->name : 'N/A' }}</td>
                            <td>{{ $task->assigner ? $task->assigner->name : 'N/A' }}</td>
                            <td>{{ $task->start_date ? Carbon\Carbon::parse($task->start_date)->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                @if($endDateVal)
                                    @php
                                        $parsedEndDate = Carbon\Carbon::parse($endDateVal);
                                    @endphp
                                    @if($isOverdue)
                                        <div class="d-flex flex-column">
                                            <span class="text-danger fw-bold d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-calendar-x-fill text-danger"></i> {{ $parsedEndDate->format('M d, Y') }}
                                            </span>
                                            <span class="text-danger extra-small fw-semibold mt-0.5" style="font-size: 0.75rem;">
                                                <i class="bi bi-alarm me-1"></i> Overdue ({{ $parsedEndDate->diffForHumans() }})
                                            </span>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column">
                                            <span class="text-dark fw-medium">
                                                <i class="bi bi-calendar-event me-1 text-muted"></i> {{ $parsedEndDate->format('M d, Y') }}
                                            </span>
                                            @if($isCompleted)
                                                <span class="text-success extra-small fw-semibold mt-0.5" style="font-size: 0.75rem;">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Completed
                                                </span>
                                            @elseif($parsedEndDate->isToday())
                                                <span class="text-warning extra-small fw-semibold mt-0.5" style="font-size: 0.75rem;">
                                                    <i class="bi bi-hourglass-split me-1"></i> Due Today
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center" style="{{ $theme['style'] }}">
                                    <span class="d-inline-block rounded-circle me-1.5" style="width: 7px; height: 7px; background-color: {{ $theme['dot'] }};"></span>
                                    {{ $matchedStatus->name ?? str_replace('_', ' ', ucfirst($task->status)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                @if(Auth::user()->id === $task->employee_id)
                                <button class="btn btn-sm btn-outline-info" wire:click="openStatusUpdate({{ $task->id }})">
                                    <i class="bi bi-arrow-repeat"></i> Update Status
                                </button>
                                @endif
                                <button class="btn btn-sm btn-outline-primary" wire:click="openAddRemark({{ $task->id }})">
                                    <i class="bi bi-chat-left-text"></i> Add Remark
                                </button>
                                <button class="btn btn-sm btn-outline-dark" wire:click="openViewRemarks({{ $task->id }})">
                                    <i class="bi bi-eye"></i> View
                                </button>
                                @if(Auth::user()->canAccess('task_update'))
                                <button class="btn btn-sm btn-outline-secondary" wire:click="editTask({{ $task->id }})">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-list-task display-4 mb-3 d-block text-light"></i>
                                No tasks found.
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
                    <h5 class="modal-title">{{ $editingId ? 'Edit Task' : 'New Task' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('isModalOpen', false)"></button>
                </div>
                
                <form wire:submit.prevent="saveTask">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" wire:model="title" class="form-control">
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        @if(Auth::user()->canAccess('task_viewAny') || Auth::user()->canAccess('task_viewteam') || Auth::user()->canAccess('task_create'))
                        <div class="mb-3">
                            <label class="form-label">Assign To <span class="text-danger">*</span></label>
                            <div wire:ignore>
                            <select wire:model="employee_id" class="form-select select2-searchable">
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                            </div>
                            @error('employee_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        @endif
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Start Date</label>
                                <input type="date" wire:model="start_date" class="form-control">
                                @error('start_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">End Date</label>
                                <input type="date" wire:model="end_date" class="form-control">
                                @error('end_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="status" class="form-select">
                                    @forelse($taskStatuses as $st)
                                        <option value="{{ $st->slug }}">{{ $st->name }}</option>
                                    @empty
                                        <option value="pending">Pending</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="completed">Completed</option>
                                    @endforelse
                                </select>
                                @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea wire:model="description" rows="3" class="form-control"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Status Update Modal (assigned employee only) -->
    @if($isStatusModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Task Status</h5>
                    <button type="button" class="btn-close" wire:click="$set('isStatusModalOpen', false)"></button>
                </div>

                <form wire:submit.prevent="saveStatusUpdate">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select wire:model="updateStatus" class="form-select">
                                @forelse($taskStatuses as $st)
                                    <option value="{{ $st->slug }}">{{ $st->name }}</option>
                                @empty
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                @endforelse
                            </select>
                            @error('updateStatus') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Remark</label>
                            <textarea wire:model="remark" rows="3" class="form-control" placeholder="Add a remark..."></textarea>
                            @error('remark') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isStatusModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Add Remark Modal -->
    @if($isRemarkModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Remark</h5>
                    <button type="button" class="btn-close" wire:click="$set('isRemarkModalOpen', false)"></button>
                </div>

                <form wire:submit.prevent="saveRemark">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Remark</label>
                            <textarea wire:model="remarkText" rows="4" class="form-control" placeholder="Write your remark..."></textarea>
                            @error('remarkText') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isRemarkModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Remark</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- View Remarks Modal -->
    @if($isViewRemarksOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Remarks — {{ $viewRemarkTaskTitle }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('isViewRemarksOpen', false)"></button>
                </div>

                <div class="modal-body">
                    @if(count($viewRemarks))
                        <ul class="list-group">
                            @foreach($viewRemarks as $r)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $r->user ? $r->user->name : 'Unknown' }}</strong>
                                        <small class="text-muted">{{ $r->created_at ? Carbon\Carbon::parse($r->created_at)->format('M d, Y h:i A') : '' }}</small>
                                    </div>
                                    <div class="mt-1">{{ $r->remark }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted text-center py-3 mb-0">No remarks yet.</p>
                    @endif
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('isViewRemarksOpen', false)">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
