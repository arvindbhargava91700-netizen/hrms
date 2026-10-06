<div>
    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label class="form-label text-muted small mb-1">Filter by Listing</label>
                    <select class="form-select" wire:model.live="listingFilter">
                        <option value="">All Listings</option>
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
            </div>
        </div>
    </div>

    {{-- Logs Table --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-check me-2 text-success"></i>Attendance Logs</h5>
            <small class="text-muted">All daily punch-in / punch-out records</small>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Listing</th>
                        <th>Plan</th>
                        <th>Date</th>
                        <th>Punch In</th>
                        <th>Punch Out</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <div class="fw-600">{{ $log->customer->name ?? '—' }}</div>
                                <small class="text-muted">{{ \App\Helpers\AdminHelper::maskContact('mobile', $log->customer->mobile ?? '' ) }}</small>
                            </td>
                            <td>{{ $log->listing->title ?? '—' }}</td>
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
                                    @php
                                        $h = intdiv($log->duration_minutes, 60);
                                        $m = $log->duration_minutes % 60;
                                    @endphp
                                    <span class="badge bg-info text-dark">
                                        {{ $h > 0 ? "{$h}h " : '' }}{{ $m }}m
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->punch_out_at)
                                    <span class="badge bg-success">Completed</span>
                                @elseif($log->punch_in_at)
                                    <span class="badge bg-warning text-dark">In Progress</span>
                                @else
                                    <span class="badge bg-secondary">Absent</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
