<div>
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                {{-- Date Range Filters --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Start Date</label>
                    <input type="date" class="form-control bg-light border-0" wire:model.live="startDate">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">End Date</label>
                    <input type="date" class="form-control bg-light border-0" wire:model.live="endDate">
                </div>

                {{-- Record Type Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Record Type</label>
                    <select class="form-select bg-light border-0" wire:model.live="typeFilter">
                        <option value="">All Record Types</option>
                        <option value="complaint">Complaints</option>
                        <option value="warning">Warnings</option>
                        <option value="show_cause">Show-Cause Notices</option>
                        <option value="disciplinary_action">Disciplinary Actions</option>
                    </select>
                </div>

                {{-- Resolution Status --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Resolution Status</label>
                    <select class="form-select bg-light border-0" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="open">Open</option>
                        <option value="under_investigation">Under Investigation</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>

                {{-- Search & Export Row --}}
                <div class="col-12 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Search case title, description, employee..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-12 col-md-3 text-md-end">
                    <button class="btn btn-success w-100 py-2 fw-semibold" wire:click="exportCsv" style="border-radius: 8px;">
                        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                <thead class="bg-light text-muted fw-semibold">
                    <tr>
                        <th class="ps-3 py-3">Employee</th>
                        <th class="py-3">Record Type</th>
                        <th class="py-3">Case Title & Incident Date</th>
                        <th class="py-3">Action Taken</th>
                        <th class="pe-3 py-3">Resolution Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData as $row)
                        <tr>
                            <td class="ps-3 py-3">
                                <div class="fw-bold text-dark">{{ $row->employee->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $row->employee->employee_code ?? 'EMP' }}</small>
                            </td>
                            <td class="py-3">
                                @if($row->record_type === 'complaint')
                                    <span class="badge bg-danger bg-opacity-10 text-danger font-semibold px-2 py-1">Complaint</span>
                                @elseif($row->record_type === 'warning')
                                    <span class="badge bg-warning bg-opacity-10 text-warning font-semibold px-2 py-1">Warning</span>
                                @elseif($row->record_type === 'show_cause')
                                    <span class="badge bg-purple bg-opacity-10 text-purple font-semibold px-2 py-1" style="color: #8b5cf6; background: #f3e8ff;">Show-Cause</span>
                                @else
                                    <span class="badge bg-dark bg-opacity-10 text-dark font-semibold px-2 py-1">Disciplinary Action</span>
                                @endif
                            </td>
                            <td class="py-3">
                                <div class="fw-semibold text-dark">{{ $row->title }}</div>
                                @if($row->incident_date)
                                    <small class="text-muted">{{ $row->incident_date->format('d M Y') }}</small>
                                @endif
                            </td>
                            <td class="py-3 text-muted small text-truncate" style="max-width: 250px;">{{ $row->action_taken ?? '-' }}</td>
                            <td class="pe-3 py-3">
                                @if($row->status === 'open')
                                    <span class="badge bg-danger px-2 py-1">Open</span>
                                @elseif($row->status === 'under_investigation')
                                    <span class="badge bg-warning text-dark px-2 py-1">Under Investigation</span>
                                @elseif($row->status === 'resolved')
                                    <span class="badge bg-success px-2 py-1">Resolved</span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">Closed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 opacity-50 d-block mb-2"></i>
                                No grievance or disciplinary records found for the selected filter parameters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($reportData->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-3">
                {{ $reportData->links() }}
            </div>
        @endif
    </div>
</div>
