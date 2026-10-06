<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Manage Work Shifts</h4>
        @if(auth()->user()->canAccess('shift_create') )
        <button wire:click="create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Shift
        </button>
        @endif
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Shift Name</th>
                            <th>Branch</th>
                            <th>Time</th>
                            <th>Late Tolerance</th>
                            <th>Auto-Mark Attendance</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                            <tr>
                                <td><div class="fw-bold">{{ $shift->name }}</div></td>
                                <td>{{ $shift->branch->name ?? 'All Branches' }}</td>
                                <td>
                                    <i class="bi bi-clock me-1 text-muted"></i> 
                                    {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                                </td>
                                <td>{{ $shift->late_tolerance_minutes }} mins</td>
                                <td>
                                    @if($shift->auto_mark_attendance)
                                        <span class="badge bg-success bg-opacity-10 text-success">Enabled (Marks {{ ucfirst($shift->auto_mark_status) }})</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Disabled</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button wire:click="view({{ $shift->id }})" class="btn btn-sm btn-light text-info me-2" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                     @if(auth()->user()->canAccess('shift_update') )
                                    <button wire:click="edit({{ $shift->id }})" class="btn btn-sm btn-light text-primary me-2" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    @endif
                                     @if(auth()->user()->canAccess('shift_delete') )
                                    <button wire:click="delete({{ $shift->id }})" wire:confirm="Are you sure you want to delete this shift?" class="btn btn-sm btn-light text-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No shifts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($isOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0">
                    <div class="modal-header bg-light border-0">
                        <h5 class="modal-title">{{ $shiftId ? 'Edit Shift' : 'Add New Shift' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form wire:submit.prevent="store">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label fw-500">Shift Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model="name" placeholder="e.g. Morning Shift" required>
                                    @error('name') <span class="text-danger fs-12">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label fw-500">Assigned Branch (Optional)</label>
                                    <select class="form-select" wire:model="branch_id">
                                        <option value="">-- Apply to All Branches --</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fs-13 mb-1">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" wire:model.live="start_time">
                                    @error('start_time') <span class="text-danger fs-12">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fs-13 mb-1">End Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control" wire:model.live="end_time">
                                    @error('end_time') <span class="text-danger fs-12">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-500">Late Tolerance (Minutes) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" wire:model="late_tolerance_minutes" min="0" required>
                                    <div class="form-text fs-12">Grace period before employee is marked late.</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-500">Min. Mins for Full Day</label>
                                    <input type="number" class="form-control" wire:model="min_present_mins" min="0">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-500">Min. Mins for Half Day</label>
                                    <input type="number" class="form-control" wire:model="min_half_day_mins" min="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-500">Auto Absent Mins</label>
                                    <input type="number" class="form-control" wire:model="auto_absent_mark_mins" min="0">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-500">Week Off Days</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="{{ $day }}" wire:model="week_off_days" id="s_day_{{ $day }}">
                                                <label class="form-check-label fs-12" for="s_day_{{ $day }}">{{ substr($day, 0, 3) }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="autoMark" wire:model.live="auto_mark_attendance">
                                        <label class="form-check-label fw-500" for="autoMark">Enable Auto-Mark Attendance</label>
                                    </div>
                                    <div class="form-text fs-12">Automatically mark attendance status at the end of the shift if the employee forgot to punch in/out.</div>
                                </div>

                                @if($auto_mark_attendance)
                                    <div class="col-md-12">
                                        <label class="form-label fw-500 text-primary">Auto-Mark Status As <span class="text-danger">*</span></label>
                                        <select class="form-select border-primary" wire:model="auto_mark_status" required>
                                            <option value="absent">Absent</option>
                                            <option value="present">Present</option>
                                            <option value="half_day">Half Day</option>
                                        </select>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-light me-2" wire:click="closeModal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> {{ $shiftId ? 'Update' : 'Save' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- View Modal -->
    @if($isViewOpen && $viewShift)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0">
                    <div class="modal-header bg-light border-0">
                        <h5 class="modal-title">Shift Details - {{ $viewShift->name }}</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Header / Employee Card -->
                            <div class="col-12">
                                <div class="d-flex align-items-center p-3 bg-info bg-opacity-10 rounded-3">
                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                        <i class="bi bi-people fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-12 text-uppercase fw-semibold">Employees Assigned</div>
                                        <div class="fs-4 fw-bold text-info">{{ $viewShift->employees->count() }}</div>
                                    </div>
                                    <div class="ms-auto">
                                        @if($viewShift->auto_mark_attendance)
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                                <i class="bi bi-check-circle-fill me-1"></i> Auto-Mark {{ ucfirst($viewShift->auto_mark_status) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                                                <i class="bi bi-x-circle-fill me-1"></i> Auto-Mark Disabled
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-clock-history text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Shift Name</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">{{ $viewShift->name }}</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-building text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Branch</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">{{ $viewShift->branch->name ?? 'All Branches' }}</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-stopwatch text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Timing</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">
                                        <span class="text-success">{{ \Carbon\Carbon::parse($viewShift->start_time)->format('h:i A') }}</span> 
                                        <i class="bi bi-arrow-right mx-1 text-muted fs-12"></i> 
                                        <span class="text-danger">{{ \Carbon\Carbon::parse($viewShift->end_time)->format('h:i A') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-hourglass-split text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Late Tolerance</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">{{ $viewShift->late_tolerance_minutes }} mins</div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <h6 class="fw-semibold mt-2 mb-3 border-bottom pb-2">Attendance Rules Overrides</h6>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="text-muted fs-12 mb-1">Full Day Min. Mins</div>
                                    <div class="fw-semibold">{{ $viewShift->min_present_mins ?? 'Global Setup' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="text-muted fs-12 mb-1">Half Day Min. Mins</div>
                                    <div class="fw-semibold">{{ $viewShift->min_half_day_mins ?? 'Global Setup' }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="text-muted fs-12 mb-1">Auto Absent Mins</div>
                                    <div class="fw-semibold">{{ $viewShift->auto_absent_mark_mins ?? 'Global Setup' }}</div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-calendar-x text-secondary me-2"></i>
                                        <span class="text-muted fs-13 fw-semibold">Week Off Days</span>
                                    </div>
                                    <div class="ps-4">
                                        @php $woff = $viewShift->week_off_days ? json_decode($viewShift->week_off_days, true) : null; @endphp
                                        @if($woff && count($woff) > 0)
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($woff as $day)
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">{{ $day }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="badge bg-light text-dark border">Uses Global Setup</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
