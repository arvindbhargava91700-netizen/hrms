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

                {{-- Employee Dropdown --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Employee</label>
                    <select class="form-select bg-light border-0" wire:model.live="employeeId">
                        <option value="">All Employees</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_code ?? 'EMP' }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">PIP Status</label>
                    <select class="form-select bg-light border-0" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="under_review">Under Review</option>
                        <option value="completed_passed">Completed - Passed</option>
                        <option value="completed_failed">Completed - Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                {{-- Search & Export Row --}}
                <div class="col-12 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Search by reason, targets, employee name..." wire:model.live.debounce.300ms="search">
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
                        <th class="py-3">Reason / Targets</th>
                        <th class="py-3">Start Date</th>
                        <th class="py-3">End Date</th>
                        <th class="py-3">Status</th>
                        <th class="pe-3 py-3">Review Result</th>
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
                                <div class="fw-semibold text-dark">{{ $row->reason }}</div>
                                @if($row->improvement_targets)
                                    <small class="text-muted text-truncate d-block" style="max-width: 250px;">{{ $row->improvement_targets }}</small>
                                @endif
                            </td>
                            <td class="py-3">{{ $row->start_date ? $row->start_date->format('d M Y') : '-' }}</td>
                            <td class="py-3">{{ $row->end_date ? $row->end_date->format('d M Y') : '-' }}</td>
                            <td class="py-3">
                                @if($row->status === 'active')
                                    <span class="badge bg-warning text-dark px-2 py-1">Active</span>
                                @elseif($row->status === 'completed_passed')
                                    <span class="badge bg-success px-2 py-1">Passed</span>
                                @elseif($row->status === 'completed_failed')
                                    <span class="badge bg-danger px-2 py-1">Failed</span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">{{ ucwords(str_replace('_', ' ', $row->status)) }}</span>
                                @endif
                            </td>
                            <td class="pe-3 py-3 text-muted small">{{ $row->review_result ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 opacity-50 d-block mb-2"></i>
                                No PIP records found for the selected filter parameters.
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
