<div>
@include('partials.report-styles')

<div id="print-area">

    {{-- Print Header --}}
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Staff Report</h4>
        <p class="text-muted small mb-0">Generated on: {{ now()->format('d M Y, h:i A') }}</p>
        <hr>
    </div>

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Staff Report</h4>
            <p class="text-muted mb-0 small">Filter staff by branch and team hierarchy, and export report</p>
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
                    <span class="fw-bold text-dark" style="font-size:0.95rem;">Filter Staff</span>
                </div>
                @if($branchId || $teamId || $departmentId || $roleName || $status || $search)
                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                @endif
            </div>

            <div class="row g-3">
                {{-- 1. Branch Filter --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Branch</label>
                    <select class="form-select border-primary-subtle" wire:model.live="branchId">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Team / Reporting Manager Filter --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Team / Reporting Manager</label>
                    <select class="form-select border-info-subtle" wire:model.live="teamId">
                        <option value="">All Teams / Reporting Managers</option>
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }} {{ $team->employee_code ? '('.$team->employee_code.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 3. Department Filter --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Department</label>
                    <select class="form-select" wire:model.live="departmentId">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 4. Role Filter --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Role</label>
                    <select class="form-select" wire:model.live="roleName">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            @php $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id; @endphp
                            <option value="{{ $role->name }}">{{ str_replace([$partnerId . '_', '_' . $partnerId], '', $role->name) }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 5. Status Filter --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Status</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                {{-- 6. Search Input --}}
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Search Staff</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" wire:model.live.debounce.300ms="search" placeholder="Name, code, email, mobile...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Staff</div>
                <div class="fw-bold fs-4 text-primary">{{ $reportData->total() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Active</div>
                <div class="fw-bold fs-4 text-success">{{ $reportData->where('status','active')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Inactive</div>
                <div class="fw-bold fs-4 text-danger">{{ $reportData->where('status','inactive')->count() }}</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Salary</div>
                <div class="fw-bold fs-4 text-dark">₹{{ number_format($reportData->sum('basic_salary'), 0) }}</div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4"># Code</th>
                        <th>Employee</th>
                        <th>Branch</th>
                        <th>Team / Reporting To</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Employment</th>
                        <th>Basic Salary</th>
                        <th>Joining Date</th>
                        <th class="pe-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $i => $row)
                    <tr>
                        <td class="ps-4 text-muted small fw-semibold">{{ $row->employee_code ?? substr($row->id, 0, 8) }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $row->avatar_url }}" class="rounded-circle border" width="34" height="34" alt="{{ $row->name }}" style="object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-dark">{{ $row->name }}</div>
                                    <div class="text-muted small">{{ $row->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                <i class="bi bi-geo-alt me-1"></i>{{ optional($row->branch)->name ?? 'Main Branch' }}
                            </span>
                        </td>
                        <td>
                            @if($row->reportingTo)
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-person-badge text-info"></i>
                                    <span class="fw-semibold text-dark">{{ $row->reportingTo->name }}</span>
                                </div>
                            @else
                                <span class="text-muted small fst-italic">Direct / None</span>
                            @endif
                        </td>
                        <td>
                            @if($row->department)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1">{{ $row->department->name }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            @php $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id; @endphp
                            <span class="badge bg-dark bg-opacity-10 text-dark rounded-pill px-2 py-1">
                                {{ $row->roles->first() ? str_replace([$partnerId.'_','_'.$partnerId],'',$row->roles->first()->name) : '-' }}
                            </span>
                        </td>
                        <td>
                            <div class="small text-muted">
                                @if($row->mobile)
                                    <div><i class="bi bi-telephone me-1"></i>{{ $row->mobile }}</div>
                                @else
                                    <span>-</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-1">
                                {{ ucfirst(str_replace('_', ' ', $row->employment_type ?? 'full_time')) }}
                            </span>
                        </td>
                        <td class="fw-bold text-dark">₹{{ number_format($row->basic_salary, 0) }}</td>
                        <td class="text-muted small">{{ $row->joining_date ? \Carbon\Carbon::parse($row->joining_date)->format('d M Y') : '-' }}</td>
                        <td class="pe-4 text-center">
                            <span class="badge bg-{{ $row->status=='active'?'success':'danger' }} bg-opacity-10 text-{{ $row->status=='active'?'success':'danger' }} rounded-pill px-3 py-1">
                                {{ ucfirst($row->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-people text-muted opacity-50 display-6 d-block mb-2"></i>
                            No staff records found matching the selected filter criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reportData->hasPages())
        <div class="card-footer border-top bg-transparent p-3 d-print-none">
            {{ $reportData->links() }}
        </div>
        @endif
    </div>
</div>
</div>
