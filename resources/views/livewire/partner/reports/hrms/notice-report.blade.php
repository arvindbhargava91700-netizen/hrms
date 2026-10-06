<div>
@include('partials.report-styles')

<div id="print-area">

    {{-- Print Header --}}
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Notice Report</h4>
        <p class="text-muted small mb-0">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        <hr>
    </div>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Notice Report</h4>
            <p class="text-muted mb-0 small">View and export notice announcements across branches and teams</p>
        </div>
        <div class="report-actions d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="exportCsv" wire:loading.attr="disabled">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="report-filter-card card mb-4 d-print-none shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel text-primary fs-5"></i>
                    <span class="fw-bold text-dark" style="font-size:0.95rem;">Filters</span>
                </div>
                @if($branchId || $teamId || $employeeId || $departmentId || $type || $startDate || $endDate || $search)
                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                @endif
            </div>
            <div class="row g-3">
                {{-- 1. Branch --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Branch</label>
                    <select class="form-select border-primary-subtle" wire:model.live="branchId">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Team / Reporting Manager --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Team / Reporting Manager</label>
                    <select class="form-select border-info-subtle" wire:model.live="teamId">
                        <option value="">All Teams / Reporting Managers</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }} {{ $team->employee_code ? '('.$team->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Employee --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Employee</label>
                    <select class="form-select" wire:model.live="employeeId">
                        <option value="">All Employees</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} {{ $emp->employee_code ? '('.$emp->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Search --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Search</label>
                    <input type="text" class="form-control" wire:model.live.debounce.300ms="search" placeholder="Title or content…">
                </div>

                {{-- 5. Department --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Department</label>
                    <select class="form-select" wire:model.live="departmentId">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 6. Notice Type --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Notice Type</label>
                    <select class="form-select" wire:model.live="type">
                        <option value="">All Types</option>
                        <option value="global">Global (All Staff)</option>
                        <option value="branch">Branch Specific</option>
                        <option value="department">Department Specific</option>
                        <option value="personal">Personal / Employee</option>
                    </select>
                </div>

                {{-- 7. From Date --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">From Date</label>
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>

                {{-- 8. To Date --}}
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">To Date</label>
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-12">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Notices</div>
                <div class="fw-bold fs-4 text-primary">{{ $reportData->total() }}</div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Global Notices</div>
                <div class="fw-bold fs-4 text-success">{{ $reportData->where('type', 'global')->count() }}</div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Targeted / Other Notices</div>
                <div class="fw-bold fs-4 text-info">{{ $reportData->where('type', '!=', 'global')->count() }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-3">ID</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Target Scope</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $i => $row)
                    <tr>
                        <td class="ps-3 text-muted small fw-bold">#{{ $row->id }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $row->title }}</div>
                            @if($row->content)
                                <div class="text-muted small text-truncate" style="max-width: 300px;">
                                    {{ Str::limit(strip_tags($row->content), 80) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            @php
                                $typeBadge = match($row->type) {
                                    'global' => 'bg-success text-success',
                                    'branch' => 'bg-primary text-primary',
                                    'department' => 'bg-info text-info',
                                    'personal', 'employee' => 'bg-warning text-warning',
                                    default => 'bg-secondary text-secondary',
                                };
                            @endphp
                            <span class="badge {{ $typeBadge }} bg-opacity-10 rounded-pill px-3">
                                {{ ucfirst($row->type ?? 'General') }}
                            </span>
                        </td>
                        <td>
                            @if($row->type == 'global')
                                <span class="badge bg-light text-success border"><i class="bi bi-globe me-1"></i>All Staff</span>
                            @elseif($row->type == 'branch' && !empty($row->branch_ids))
                                <span class="badge bg-light text-primary border"><i class="bi bi-geo-alt me-1"></i>{{ count($row->branch_ids) }} Branch(es)</span>
                            @elseif($row->type == 'department' && !empty($row->department_ids))
                                <span class="badge bg-light text-info border"><i class="bi bi-diagram-3 me-1"></i>{{ count($row->department_ids) }} Dept(s)</span>
                            @elseif(!empty($row->user_ids))
                                <span class="badge bg-light text-warning border"><i class="bi bi-people me-1"></i>{{ count($row->user_ids) }} Employee(s)</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="small">{{ $row->start_date ? \Carbon\Carbon::parse($row->start_date)->format('d M Y, h:i A') : '-' }}</td>
                        <td class="small">{{ $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('d M Y, h:i A') : '-' }}</td>
                        <td class="text-muted small">{{ $row->created_at ? $row->created_at->format('d M Y') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            No notice records found matching the filters.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())
        <div class="card-footer border-top bg-transparent p-4 d-print-none">
            {{ $reportData->links() }}
        </div>
        @endif
    </div>
</div>
</div>
