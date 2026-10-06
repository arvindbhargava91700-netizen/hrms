<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-person-x-fill text-danger me-2"></i>Attrition Management & Analytics
            </h4>
            <p class="text-muted small mb-0">Track employee exits, analyze voluntary vs. involuntary turnover, and view department/branch metrics.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('partner.hrms.exit-reasons') }}" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-card-checklist"></i> Exit Reasons
            </a>
            <button wire:click="exportCsv" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-download"></i> Export CSV
            </button>
            <button wire:click="openCreateModal" class="btn btn-danger btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-person-dash-fill"></i> Record Employee Exit
            </button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Active Headcount</div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($currentActiveEmployees) }}</div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                            <i class="bi bi-people-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #ef4444 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Total Exits</div>
                            <div class="fs-4 fw-bold text-danger mt-1">{{ number_format($totalExits) }}</div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i class="bi bi-box-arrow-right fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Voluntary Exits</div>
                            <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($voluntaryExits) }}</div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                            <i class="bi bi-person-walking fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #dc2626 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Involuntary Exits</div>
                            <div class="fs-4 fw-bold text-danger mt-1">{{ number_format($involuntaryExits) }}</div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(220, 38, 38, 0.1); color: #dc2626;">
                            <i class="bi bi-slash-circle-fill fs-5"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #8b5cf6 !important; background: linear-gradient(135deg, #ffffff 0%, #f3e8ff 100%);">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Attrition Rate</div>
                            <div class="fs-3 fw-extrabold text-purple mt-1" style="color: #7c3aed;">
                                {{ $attritionRate }}%
                            </div>
                        </div>
                        <div class="rounded-circle p-2" style="background: rgba(124, 58, 237, 0.15); color: #7c3aed;">
                            <i class="bi bi-graph-down-arrow fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-2 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Year</label>
                    <select wire:model.live="filterYear" class="form-select form-select-sm">
                        @for ($y = date('Y'); $y >= date('Y') - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Month</label>
                    <select wire:model.live="filterMonth" class="form-select form-select-sm">
                        <option value="all">All Months</option>
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Branch</label>
                    <select wire:model.live="filterBranchId" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Department</label>
                    <select wire:model.live="filterDepartmentId" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Exit Type</label>
                    <select wire:model.live="filterExitType" class="form-select form-select-sm">
                        <option value="all">All Types</option>
                        <option value="voluntary">Voluntary</option>
                        <option value="involuntary">Involuntary</option>
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Exit Reason</label>
                    <select wire:model.live="filterExitReason" class="form-select form-select-sm">
                        <option value="all">All Reasons</option>
                        @foreach ($allExitReasonsForFilter as $reason)
                            <option value="{{ $reason->name }}">{{ $reason->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Search Employee</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-select-sm form-control" placeholder="Search name/code...">
                </div>
            </div>
        </div>
    </div>

    {{-- Analytics Visualizations --}}
    <div class="row g-3 mb-4">
        {{-- Monthly Exits Chart --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Monthly Exit Trends ({{ $filterYear }})</h6>
                    <span class="badge bg-light text-dark fw-normal border">Total Exits: {{ $totalExits }}</span>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-end justify-content-between gap-1 pt-4 pb-2" style="height: 180px;">
                        @php
                            $maxExits = max(array_column($monthlyExits, 'exits')) ?: 1;
                        @endphp
                        @foreach ($monthlyExits as $m)
                            @php
                                $heightPct = $maxExits > 0 ? round(($m['exits'] / $maxExits) * 100) : 0;
                            @endphp
                            <div class="d-flex flex-column align-items-center flex-fill" style="height: 100%;">
                                <small class="text-muted fw-bold mb-1" style="font-size: 0.7rem;">{{ $m['exits'] > 0 ? $m['exits'] : '' }}</small>
                                <div class="w-100 rounded-top" style="height: {{ max($heightPct, 6) }}%; background: {{ $m['exits'] > 0 ? 'linear-gradient(180deg, #ef4444 0%, #f87171 100%)' : '#e2e8f0' }}; transition: height 0.3s ease;"></div>
                                <small class="mt-2 text-secondary fw-semibold" style="font-size: 0.72rem;">{{ $m['month'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Department & Type Breakdown --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart-fill text-warning me-2"></i>Exit Breakdown</h6>
                </div>
                <div class="card-body pt-1">
                    {{-- Exit Type Ratio --}}
                    <div class="mb-4">
                        <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span>Exit Type Distribution</span>
                            <span>{{ $totalExits }} Exits</span>
                        </div>
                        @php
                            $volPct = $totalExits > 0 ? round(($voluntaryExits / $totalExits) * 100) : 0;
                            $involPct = $totalExits > 0 ? round(($involuntaryExits / $totalExits) * 100) : 0;
                        @endphp
                        <div class="progress" style="height: 14px; border-radius: 20px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $volPct }}%" title="Voluntary: {{ $volPct }}%">{{ $volPct > 15 ? $volPct.'%' : '' }}</div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $involPct }}%" title="Involuntary: {{ $involPct }}%">{{ $involPct > 15 ? $involPct.'%' : '' }}</div>
                        </div>
                        <div class="d-flex justify-content-between small text-muted mt-2">
                            <span><i class="bi bi-circle-fill text-warning me-1"></i> Voluntary ({{ $voluntaryExits }})</span>
                            <span><i class="bi bi-circle-fill text-danger me-1"></i> Involuntary ({{ $involuntaryExits }})</span>
                        </div>
                    </div>

                    {{-- Top Departments --}}
                    <div>
                        <div class="small fw-bold mb-2">Department Attrition Rates</div>
                        @forelse($deptAttrition->take(4) as $dept)
                            <div class="mb-2">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span class="fw-medium text-dark">{{ $dept['department'] }}</span>
                                    <span class="text-muted">{{ $dept['exits'] }} exits ({{ $dept['rate'] }}%)</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-secondary" role="progressbar" style="width: {{ min($dept['rate'], 100) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small mb-0">No department exits recorded for this period.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Exit Records Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-task me-2 text-danger"></i>Employee Exit Logs</h6>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show</span>
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 70px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Branch</th>
                        <th>Department / Designation</th>
                        <th>Exit Date</th>
                        <th>Exit Type</th>
                        <th>Reason</th>
                        <th>Notice (Days)</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($exits as $exit)
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $exit->employee?->avatar_url }}" class="rounded-circle" width="32" height="32">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $exit->employee?->name ?? 'Unknown' }}</div>
                                        <div class="text-muted small">{{ $exit->employee?->email ?? $exit->employee?->employee_code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $exit->branch?->name ?? 'Main Branch' }}</td>
                            <td>
                                <div>{{ $exit->department?->name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $exit->designation?->name ?? '' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $exit->exit_date?->format('d M Y') }}</div>
                                <small class="text-muted">Last: {{ $exit->last_working_date?->format('d M Y') }}</small>
                            </td>
                            <td>
                                @if($exit->exit_type === 'voluntary')
                                    <span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-person-walking me-1"></i>Voluntary</span>
                                @else
                                    <span class="badge bg-danger text-white px-2 py-1"><i class="bi bi-slash-circle me-1"></i>Involuntary</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $exit->exit_reason }}</span></td>
                            <td>{{ $exit->notice_period_days }} days</td>
                            <td class="text-end pe-3">
                                <button wire:click="openEditModal('{{ $exit->id }}')" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button wire:click="deleteExit('{{ $exit->id }}')" wire:confirm="Are you sure you want to delete this exit record? This will revert employee status to Active." class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No employee exit records found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($exits->hasPages())
            <div class="card-footer bg-white py-3 border-0">
                {{ $exits->links() }}
            </div>
        @endif
    </div>

    {{-- Record Exit Modal --}}
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-3">
                    <div class="modal-header border-0 bg-light py-3">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bi bi-person-dash me-2 text-danger"></i>
                            {{ $editingId ? 'Edit Exit Record' : 'Record Employee Exit' }}
                        </h5>
                        <button type="button" wire:click="closeModal" class="btn-close"></button>
                    </div>

                    <form wire:submit.prevent="saveExit">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                {{-- Select Employee --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-bold small">Select Employee <span class="text-danger">*</span></label>
                                    <select wire:model.live="formEmployeeId" class="form-select @error('formEmployeeId') is-invalid @enderror" {{ $editingId ? 'disabled' : '' }}>
                                        <option value="">-- Choose Employee --</option>
                                        @foreach($activeEmployeesList as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_code ?? $emp->email }})</option>
                                        @endforeach
                                    </select>
                                    @error('formEmployeeId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Employee Snapshot Preview --}}
                                @if($selectedEmployeeBranch || $selectedEmployeeDepartment)
                                    <div class="col-md-12">
                                        <div class="p-2 bg-light rounded border d-flex justify-content-around text-center small">
                                            <div><span class="text-muted">Branch:</span> <strong>{{ $selectedEmployeeBranch }}</strong></div>
                                            <div><span class="text-muted">Department:</span> <strong>{{ $selectedEmployeeDepartment }}</strong></div>
                                            <div><span class="text-muted">Designation:</span> <strong>{{ $selectedEmployeeDesignation }}</strong></div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Exit Type --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Exit Type <span class="text-danger">*</span></label>
                                    <select wire:model.live="formExitType" class="form-select @error('formExitType') is-invalid @enderror">
                                        <option value="voluntary">Voluntary (Resignation / Self Exits)</option>
                                        <option value="involuntary">Involuntary (Termination / Layoff)</option>
                                    </select>
                                    @error('formExitType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Exit Reason --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Exit Reason <span class="text-danger">*</span></label>
                                    <select wire:model="formExitReason" class="form-select @error('formExitReason') is-invalid @enderror">
                                        <option value="">-- Select Reason --</option>
                                        @foreach($dynamicExitReasons as $reasonItem)
                                            <option value="{{ $reasonItem->name }}">{{ $reasonItem->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('formExitReason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Exit Date --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Exit Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="formExitDate" class="form-control @error('formExitDate') is-invalid @enderror">
                                    @error('formExitDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Last Working Date --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Last Working Date</label>
                                    <input type="date" wire:model="formLastWorkingDate" class="form-control @error('formLastWorkingDate') is-invalid @enderror">
                                    @error('formLastWorkingDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Notice Period --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Notice Period Served (Days)</label>
                                    <input type="number" wire:model="formNoticePeriod" class="form-control @error('formNoticePeriod') is-invalid @enderror" min="0">
                                    @error('formNoticePeriod') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Remarks --}}
                                <div class="col-md-12">
                                    <label class="form-label fw-bold small">Remarks / Notes</label>
                                    <textarea wire:model="formRemarks" class="form-control @error('formRemarks') is-invalid @enderror" rows="3" placeholder="Enter exit details, exit interview notes, or remarks..."></textarea>
                                    @error('formRemarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-0 py-3">
                            <button type="button" wire:click="closeModal" class="btn btn-secondary btn-sm">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-sm px-4">
                                <i class="bi bi-check-circle me-1"></i> Save Exit Record
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
