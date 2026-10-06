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

                {{-- Category Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Category</label>
                    <select class="form-select bg-light border-0" wire:model.live="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="kyc">KYC / Identity</option>
                        <option value="offer_letter">Offer Letter</option>
                        <option value="appointment_letter">Appointment Letter</option>
                        <option value="agreement">Agreements</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Verification Status</label>
                    <select class="form-select bg-light border-0" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending_verification">Pending Verification</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>

                {{-- Search & Export Row --}}
                <div class="col-12 col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Search by document title, number, employee..." wire:model.live.debounce.300ms="search">
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
                        <th class="py-3">Category & Type</th>
                        <th class="py-3">Title & Number</th>
                        <th class="py-3">Expiry Date</th>
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
                            <td class="py-3">
                                <span class="badge bg-light text-dark border me-1">{{ ucwords(str_replace('_', ' ', $row->document_category)) }}</span>
                                <small class="text-muted d-block mt-1">{{ ucfirst($row->document_type) }}</small>
                            </td>
                            <td class="py-3">
                                <div class="fw-semibold text-dark">{{ $row->title }}</div>
                                @if($row->document_number)
                                    <small class="text-muted">Ref: {{ $row->document_number }}</small>
                                @endif
                            </td>
                            <td class="py-3">
                                {{ $row->expiry_date ? $row->expiry_date->format('d M Y') : 'No Expiry' }}
                            </td>
                            <td class="pe-3 py-3">
                                @if($row->status === 'verified')
                                    <span class="badge bg-success px-2 py-1">Verified</span>
                                @elseif($row->status === 'pending_verification')
                                    <span class="badge bg-warning text-dark px-2 py-1">Pending</span>
                                @elseif($row->status === 'rejected')
                                    <span class="badge bg-danger px-2 py-1">Rejected</span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1">Expired</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 opacity-50 d-block mb-2"></i>
                                No document records found for the selected filter parameters.
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
