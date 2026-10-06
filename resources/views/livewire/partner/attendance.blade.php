<div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Today's Stats ─────────────────────────────────────────────────────────────── --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10">
                        <i class="bi bi-people fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Today — Total</div>
                        <div class="fw-700 fs-4">{{ $todayTotal }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-warning bg-opacity-10">
                        <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Today — Still Inside</div>
                        <div class="fw-700 fs-4">{{ $todayOpen }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-success bg-opacity-10">
                        <i class="bi bi-check-circle fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Today — Completed</div>
                        <div class="fw-700 fs-4">{{ $todayDone }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters + Mark Button ──────────────────────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Search Customer</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" wire:model.live.debounce.300ms="search"
                            placeholder="Name or mobile…">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Filter by Date</label>
                    <input type="date" class="form-control" wire:model.live="dateFilter">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small mb-1">Filter by Listing</label>
                    <select class="form-select" wire:model.live="listingFilter">
                        <option value="">All My Listings</option>
                        @foreach($listings as $listing)
                            <option value="{{ $listing->id }}">{{ $listing->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button class="btn btn-outline-secondary w-100"
                        wire:click="$set('dateFilter',''); $set('listingFilter',''); $set('search','')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-success w-100" wire:click="openMarkModal">
                        <i class="bi bi-person-check me-2"></i>Mark Attendance
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Logs Table ──────────────────────────────────────────────────────────────────── --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-check me-2 text-success"></i>Attendance Logs</h5>
            <small class="text-muted">Customer punch-in / punch-out history</small>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Plan</th>
                        <th>Date</th>
                        <th>Punch In</th>
                        <th>Punch Out</th>
                        <th>Duration</th>
                        <th>Note</th>
                        <th>Status</th>
                        <th class="text-end">Edit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="fw-600">{{ $log->customer->name ?? '—' }}</div>
                                <small class="text-muted">{{ $log->customer->mobile ?? '' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $log->subscription?->package?->name ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $log->date->format('d M Y') }}</td>
                            <td>
                                @if($log->punch_in_at)
                                    <span class="text-success fw-500">
                                        <i class="bi bi-box-arrow-in-right me-1"></i>
                                        {{ $log->punch_in_at->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->punch_out_at)
                                    <span class="text-danger fw-500">
                                        <i class="bi bi-box-arrow-right me-1"></i>
                                        {{ $log->punch_out_at->format('h:i A') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->duration_minutes !== null)
                                    @php $h = intdiv($log->duration_minutes, 60); $m = $log->duration_minutes % 60; @endphp
                                    <span class="badge bg-info text-dark">
                                        {{ $h > 0 ? "{$h}h " : '' }}{{ $m }}m
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">{{ $log->note ?? '—' }}</small>
                            </td>
                            <td>
                                @if($log->punch_out_at)
                                    <span class="badge bg-success">Completed</span>
                                @elseif($log->punch_in_at)
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-activity me-1"></i>Inside
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Absent</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button class="btn btn-icon btn-sm btn-outline-primary"
                                    wire:click="openEdit({{ $log->id }})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer">{{ $logs->links() }}</div>
        @endif
    </div>

    {{-- ── Mark Attendance Modal ──────────────────────────────────────────────────── --}}
    @if($showMarkModal)
        <div class="modal d-block" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-person-check me-2 text-success"></i>Mark Attendance
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showMarkModal', false)"></button>
                    </div>
                    <div class="modal-body">

                        {{-- Date --}}
                        <div class="mb-3">
                            <label class="form-label">Attendance Date</label>
                            <input type="date" class="form-control" wire:model="markDate">
                        </div>

                        {{-- Customer Search --}}
                        <div class="mb-3">
                            <label class="form-label">Search Customer (Name / Mobile)</label>
                            <div class="input-group">
                                <input type="text" class="form-control"
                                    wire:model="markSearch"
                                    placeholder="e.g. Ravi or 9000000000">
                                <button class="btn btn-outline-primary" wire:click="searchSubscription">
                                    <i class="bi bi-search"></i> Find
                                </button>
                            </div>
                        </div>

                        {{-- Error --}}
                        @if($markError)
                            <div class="alert alert-danger py-2">
                                <i class="bi bi-exclamation-triangle me-2"></i>{{ $markError }}
                            </div>
                        @endif

                        {{-- Customer Preview --}}
                        @if($markPreview)
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body py-3">
                                    <div class="d-flex align-items-center gap-3 mb-2">
                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                                            <i class="bi bi-person-fill text-primary fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-600">{{ $markPreview['customer_name'] }}</div>
                                            <small class="text-muted">{{ $markPreview['customer_mobile'] }}</small>
                                        </div>
                                    </div>
                                    <div class="row g-2 small">
                                        <div class="col-6">
                                            <span class="text-muted">Listing:</span>
                                            <span class="fw-500 ms-1">{{ $markPreview['listing'] }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted">Plan:</span>
                                            <span class="fw-500 ms-1">{{ $markPreview['plan'] }}</span>
                                        </div>
                                        @if($markPreview['existing_status'])
                                            <div class="col-12">
                                                <span class="text-muted">Today's Status:</span>
                                                @if($markPreview['existing_status'] === 'completed')
                                                    <span class="badge bg-success ms-1">Completed</span>
                                                    <small class="text-muted ms-1">
                                                        In: {{ $markPreview['punch_in_at'] }} / Out: {{ $markPreview['punch_out_at'] }}
                                                    </small>
                                                @else
                                                    <span class="badge bg-warning text-dark ms-1">Still Inside</span>
                                                    <small class="text-muted ms-1">In: {{ $markPreview['punch_in_at'] }}</small>
                                                @endif
                                            </div>
                                        @else
                                            <div class="col-12">
                                                <span class="text-muted">Today's Status:</span>
                                                <span class="badge bg-secondary ms-1">Not Punched In Yet</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Action --}}
                            <div class="mb-3">
                                <label class="form-label">Action</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="actionIn"
                                            wire:model="markAction" value="punch_in">
                                        <label class="form-check-label text-success fw-500" for="actionIn">
                                            <i class="bi bi-box-arrow-in-right me-1"></i>Punch In
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="actionOut"
                                            wire:model="markAction" value="punch_out">
                                        <label class="form-check-label text-danger fw-500" for="actionOut">
                                            <i class="bi bi-box-arrow-right me-1"></i>Punch Out
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{-- Note --}}
                            <div class="mb-1">
                                <label class="form-label">Note <span class="text-muted small">(optional)</span></label>
                                <input type="text" class="form-control" wire:model="markNote"
                                    placeholder="e.g. Late arrival">
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary"
                            wire:click="$set('showMarkModal', false)">Cancel</button>
                        @if($markPreview)
                            <button class="btn {{ $markAction === 'punch_in' ? 'btn-success' : 'btn-danger' }}"
                                wire:click="saveMark">
                                <i class="bi {{ $markAction === 'punch_in' ? 'bi-box-arrow-in-right' : 'bi-box-arrow-right' }} me-2"></i>
                                {{ $markAction === 'punch_in' ? 'Confirm Punch In' : 'Confirm Punch Out' }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Edit Attendance Modal ──────────────────────────────────────────────────── --}}
    @if($showEditModal)
        <div class="modal d-block" style="background:rgba(0,0,0,.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="bi bi-pencil me-2 text-primary"></i>Edit Attendance Record
                        </h5>
                        <button type="button" class="btn-close" wire:click="$set('showEditModal', false)"></button>
                    </div>
                    <form wire:submit="saveEdit">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Punch In Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control @error('editPunchIn') is-invalid @enderror"
                                    wire:model="editPunchIn">
                                @error('editPunchIn')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">
                                    Punch Out Time
                                    <span class="text-muted small">(leave blank if still inside)</span>
                                </label>
                                <input type="time" class="form-control @error('editPunchOut') is-invalid @enderror"
                                    wire:model="editPunchOut">
                                @error('editPunchOut')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Duration will be auto-calculated on save.</small>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Note <span class="text-muted small">(optional)</span></label>
                                <input type="text" class="form-control" wire:model="editNote"
                                    placeholder="e.g. Manual correction">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary"
                                wire:click="$set('showEditModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
