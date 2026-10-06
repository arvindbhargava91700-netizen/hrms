<div class="container-fluid py-4 bg-light min-vh-100">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-end align-items-center">
            <div class="d-flex align-items-center bg-white border rounded px-3 py-2 shadow-sm">
                <i class="bi bi-calendar3 text-muted me-2"></i>
                <input type="date" wire:model.live="reportDate" class="form-control border-0 bg-transparent p-0 fw-semibold text-dark" style="width: 130px; box-shadow: none; outline: none;">
            </div>
        </div>
    </div>

    @if(session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Left Sidebar -->
        <div class="col-lg-4 mb-4">
            <!-- Status Card -->
            <div class="card shadow-sm border-0 mb-4 text-center p-4">
                <div class="d-flex justify-content-center mb-3">
                    @if($report->status === 'draft')
                        <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-pencil-fill fs-4"></i>
                        </div>
                    @elseif($report->status === 'submitted')
                        <div class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-send-fill fs-4"></i>
                        </div>
                    @elseif($report->status === 'manager_approved')
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-person-check-fill fs-4"></i>
                        </div>
                    @elseif($report->status === 'partner_approved')
                        <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-check-circle-fill fs-4"></i>
                        </div>
                    @elseif($report->status === 'rejected')
                        <div class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                            <i class="bi bi-x-circle-fill fs-4"></i>
                        </div>
                    @endif
                </div>
                <h5 class="fw-bold mb-1 text-capitalize">{{ str_replace('_', ' ', $report->status) }}</h5>
                <p class="text-muted small mb-4">{{ $items->count() }} item(s) added.</p>

                <div class="progress mb-2" style="height: 6px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $report->completion_percentage }}%;" aria-valuenow="{{ $report->completion_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="text-muted small">{{ $report->completion_percentage }}% completion</div>
                
                @if(in_array($report->status, ['draft', 'rejected']))
                    <button wire:click="submitReport" class="btn btn-primary w-100 mt-4 shadow-sm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submitReport">Submit Report</span>
                        <span wire:loading wire:target="submitReport">Submitting...</span>
                    </button>
                @endif
            </div>

            <!-- Overall Summary Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h6 class="fw-bold text-dark d-flex align-items-center">
                        <i class="bi bi-chat-left-text text-primary me-2"></i> Overall Summary
                    </h6>
                </div>
                <div class="card-body">
                    <textarea wire:model="summary" class="form-control mb-3 bg-light" rows="5" placeholder="Summarise your day - what went well, blockers, plan for tomorrow..." {{ !in_array($report->status, ['draft', 'rejected']) ? 'disabled' : '' }}></textarea>
                    
                    @if(in_array($report->status, ['draft', 'rejected']))
                        <button wire:click="saveSummary" class="btn btn-outline-primary w-100 fw-semibold">
                            <i class="bi bi-save me-1"></i> SAVE SUMMARY
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Main Area -->
        <div class="col-lg-8">
            
            @if(in_array($report->status, ['draft', 'rejected']))
                <!-- Add New Work Item Form -->
                <div class="card shadow-sm border-primary border-opacity-25 mb-4">
                    <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary border-opacity-25 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-plus-circle me-2"></i> Add New Work Item</h6>
                    </div>
                    <div class="card-body p-4">
                        <form wire:submit.prevent="addItem">
                            @if(session()->has('item_message'))
                                <div class="alert alert-success py-2 px-3 small">{{ session('item_message') }}</div>
                            @endif

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">WORK TITLE <span class="text-danger">*</span></label>
                                <input type="text" wire:model="title" class="form-control" placeholder="e.g. Fixed login bug on mobile app" required>
                                @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold">DETAILS</label>
                                <textarea wire:model="details" class="form-control" rows="3" placeholder="What exactly was done? Any blockers or notes..."></textarea>
                                @error('details') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label text-muted small fw-bold">CATEGORY</label>
                                    <select wire:model="category" class="form-select">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                    @error('category') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold">STATUS</label>
                                    <select wire:model="status" class="form-select">
                                        @foreach($statuses as $stat)
                                            <option value="{{ $stat }}">{{ $stat }}</option>
                                        @endforeach
                                    </select>
                                    @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label text-muted small fw-bold">PRIORITY</label>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-warning me-2" style="width: 12px; height: 12px;"></div>
                                        <select wire:model="priority" class="form-select border-0 bg-light p-1 ps-2">
                                            @foreach($priorities as $pri)
                                                <option value="{{ $pri }}">{{ $pri }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('priority') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <label class="form-label text-muted small fw-bold">TIME SPENT</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="input-group input-group-sm" style="width: 80px;">
                                            <input type="number" wire:model="timeSpentHours" class="form-control text-center" min="0">
                                            <span class="input-group-text bg-light text-muted border-start-0">h</span>
                                        </div>
                                        <div class="input-group input-group-sm" style="width: 80px;">
                                            <input type="number" wire:model="timeSpentMinutes" class="form-control text-center" min="0" max="59">
                                            <span class="input-group-text bg-light text-muted border-start-0">m</span>
                                        </div>
                                    </div>
                                    @error('timeSpentHours') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                    @error('timeSpentMinutes') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold">LINK TASK</label>
                                    <select wire:model="linkedTaskId" class="form-select">
                                        <option value="">-- None --</option>
                                        @foreach($availableTasks as $task)
                                            <option value="{{ $task->id }}">{{ $task->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('linkedTaskId') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 border-top pt-3">
                                <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm" wire:loading.attr="disabled">
                                    <i class="bi bi-plus me-1"></i> ADD ITEM
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Work Items List -->
            @if($items->count() > 0)
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Work Items <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $items->count() }}</span></h6>
                </div>
                
                @foreach($items as $item)
                    <div class="card shadow-sm border-0 mb-3 border-start border-4 {{ $item->status === 'Completed' ? 'border-success' : 'border-warning' }}">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 text-dark">{{ $item->title }}</h6>
                                @if(in_array($report->status, ['draft', 'rejected']))
                                    <button wire:click="deleteItem({{ $item->id }})" class="btn btn-sm text-danger p-0" title="Delete" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                            
                            @if($item->details)
                                <p class="text-muted small mb-3">{{ $item->details }}</p>
                            @endif

                            <div class="d-flex flex-wrap gap-3 mt-3 align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-tag text-primary me-1"></i> {{ $item->category }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    @if($item->status === 'Completed')
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1"><i class="bi bi-check-circle me-1"></i> Completed</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1"><i class="bi bi-hourglass-split me-1"></i> {{ $item->status }}</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="bi bi-clock me-1"></i> {{ $item->time_spent_hours }}h {{ $item->time_spent_minutes }}m
                                </div>
                                @if($item->task)
                                    <div class="d-flex align-items-center text-primary small">
                                        <i class="bi bi-link-45deg me-1"></i> {{ $item->task->title }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="card shadow-sm border-0 bg-white text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-clipboard text-muted opacity-25" style="font-size: 3rem;"></i>
                    </div>
                    <h6 class="text-muted mb-1">No work items yet</h6>
                    <p class="text-muted small">Click "Add Work Item" to log what you worked on today.</p>
                </div>
            @endif

        </div>
    </div>
</div>
