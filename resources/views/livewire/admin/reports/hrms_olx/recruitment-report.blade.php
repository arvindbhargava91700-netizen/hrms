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

                {{-- Department Dropdown --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Department</label>
                    <select class="form-select bg-light border-0" wire:model.live="departmentId">
                        <option value="">All Departments</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Selection Status --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Selection Status</label>
                    <select class="form-select bg-light border-0" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="under_review">Under Review</option>
                        <option value="selected">Selected</option>
                        <option value="rejected">Rejected</option>
                        <option value="on_hold">On Hold</option>
                    </select>
                </div>

                {{-- Search & Export Row --}}
                <div class="col-12 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Search position, candidate name, email, phone..." wire:model.live.debounce.300ms="search">
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
                        <th class="ps-3 py-3">Position Title</th>
                        <th class="py-3">Candidate Details</th>
                        <th class="py-3">Department</th>
                        <th class="py-3">Interview Stage</th>
                        <th class="py-3">Selection Status</th>
                        <th class="pe-3 py-3">Cost Per Hire</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reportData as $row)
                        <tr>
                            <td class="ps-3 py-3">
                                <div class="fw-bold text-dark">{{ $row->job_title }}</div>
                                <small class="text-muted">Vacancies: {{ $row->vacancies_count }}</small>
                            </td>
                            <td class="py-3">
                                <div class="fw-semibold text-dark">{{ $row->candidate_name }}</div>
                                <small class="text-muted">{{ $row->candidate_email ?? '-' }} | {{ $row->candidate_phone ?? '-' }}</small>
                            </td>
                            <td class="py-3">{{ optional($row->department)->name ?? '-' }}</td>
                            <td class="py-3"><span class="badge bg-light text-dark border">{{ ucwords(str_replace('_', ' ', $row->interview_stage)) }}</span></td>
                            <td class="py-3">
                                @if($row->status === 'selected')
                                    <span class="badge bg-success px-2 py-1">Selected</span>
                                @elseif($row->status === 'rejected')
                                    <span class="badge bg-danger px-2 py-1">Rejected</span>
                                @else
                                    <span class="badge bg-warning text-dark px-2 py-1">{{ ucwords(str_replace('_', ' ', $row->status)) }}</span>
                                @endif
                            </td>
                            <td class="pe-3 py-3 fw-bold text-dark">₹{{ number_format($row->cost_per_hire, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 opacity-50 d-block mb-2"></i>
                                No recruitment records found for the selected filter parameters.
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
