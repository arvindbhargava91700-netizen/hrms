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

                {{-- Branch Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1"> Branch</label>
                    <select class="form-select bg-light border-0" wire:model.live="branchId">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Team / Reporting Manager Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1"> Team / Reporting Manager</label>
                    <select class="form-select bg-light border-0" wire:model.live="teamId">
                        <option value="">All Teams / Reporting Managers</option>
                        @foreach ($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}{{ $team->employee_code ? ' ('.$team->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
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

                {{-- Exit Status --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Exit Status</label>
                    <select class="form-select bg-light border-0" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="in_notice_period">In Notice Period</option>
                        <option value="completed">Completed / Exited</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                {{-- Search & Export Row --}}
                <div class="col-12 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Search exit reason, remarks, employee name..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-12 col-md-4 text-md-end">
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
                        <th class="py-3">Resignation Date</th>
                        <th class="py-3">Notice Period</th>
                        <th class="py-3">Last Working Date</th>
                        <th class="py-3">Clearance & F&F</th>
                        <th class="pe-3 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData as $row)
                        <tr>
                            <td class="ps-3 py-3">
                                <div class="fw-bold text-dark">{{ $row->employee->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $row->employee->employee_code ?? 'EMP' }}</small>
                            </td>
                            <td class="py-3">{{ $row->resignation_date ? $row->resignation_date->format('d M Y') : '-' }}</td>
                            <td class="py-3">{{ $row->notice_period_days }} Days</td>
                            <td class="py-3 fw-semibold text-dark">{{ $row->last_working_date ? $row->last_working_date->format('d M Y') : '-' }}</td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark border me-1">Clearance: {{ ucwords(str_replace('_', ' ', $row->clearance_status)) }}</span>
                                <span class="badge bg-light text-dark border">F&F: {{ ucwords(str_replace('_', ' ', $row->fnf_status)) }}</span>
                            </td>
                            <td class="pe-3 py-3">
                                @if($row->status === 'completed')
                                    <span class="badge bg-success px-2 py-1">Completed</span>
                                @elseif($row->status === 'in_notice_period')
                                    <span class="badge bg-warning text-dark px-2 py-1">In Notice Period</span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">{{ ucwords(str_replace('_', ' ', $row->status)) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 opacity-50 d-block mb-2"></i>
                                No exit records found for the selected filter parameters.
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
