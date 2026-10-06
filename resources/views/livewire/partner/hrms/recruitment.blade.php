<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">Recruitment & Hiring Management</h5>
            <!-- <p class="text-muted small mb-0">Track open job vacancies, candidates, interview stages, selection status, and cost per hire</p> -->
        </div>
        @if(auth()->user()->isPartner() || auth()->user()->canAccess('recruitment_create'))
        <button class="btn btn-primary d-flex align-items-center gap-2 px-4 rounded-3 shadow-sm" wire:click="createRecruitment">
            <i class="bi bi-person-plus-fill"></i> Add Candidate / Vacancy
        </button>
        @endif
    </div>

    {{-- Filters --}}
    @if(auth()->user()->isPartner() || auth()->user()->canAccess('recruitment_viewAny'))
    <div class="mb-4">
        {{-- Search & Filter Row --}}
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" wire:model.live.debounce.300ms="search" placeholder="Search by candidate name, email, phone, or job title...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" wire:model.live="filterBranchId">
                            <option value="">All Branches</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}" @selected($filterBranchId == $branch->id)>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" wire:model.live="filterDepartmentId">
                            <option value="">All Departments</option>
                            @foreach ($allDepartments as $dept)
                                <option value="{{ $dept->id }}" @selected($filterDepartmentId == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" wire:model.live="stageFilter">
                            <option value="">All Interview Stages</option>
                            <option value="applied">Applied</option>
                            <option value="screening">Screening</option>
                            <option value="technical_round">Technical Round</option>
                            <option value="hr_round">HR Round</option>
                            <option value="final_round">Final Round</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select" wire:model.live="statusFilter">
                            <option value="">All Selection Statuses</option>
                            <option value="under_review">Under Review</option>
                            <option value="selected">Selected</option>
                            <option value="rejected">Rejected</option>
                            <option value="on_hold">On Hold</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Recruitment List Table --}}
    <div class="card border-0 rounded-4 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-muted small font-monospace">CANDIDATE</th>
                        <th class="py-3 text-muted small font-monospace">VACANCY / POSITION</th>
                        <th class="py-3 text-muted small font-monospace">INTERVIEW STAGE</th>
                        <th class="py-3 text-muted small font-monospace">SELECTION RESULT</th>
                        <th class="py-3 text-muted small font-monospace">OFFER / JOINING</th>
                        <th class="py-3 text-muted small font-monospace">COST PER HIRE</th>
                        <th class="pe-4 py-3 text-end text-muted small font-monospace">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recruitments as $rec)
                        @php
                            $stageConfig = match($rec->interview_stage) {
                                'applied'         => ['class' => 'info',      'label' => 'Applied'],
                                'screening'       => ['class' => 'primary',   'label' => 'Screening'],
                                'technical_round' => ['class' => 'warning',   'label' => 'Technical Round'],
                                'hr_round'        => ['class' => 'dark',      'label' => 'HR Round'],
                                'final_round'     => ['class' => 'success',   'label' => 'Final Round'],
                                default           => ['class' => 'secondary', 'label' => ucfirst($rec->interview_stage)],
                            };

                            $statusConfig = match($rec->status) {
                                'selected'     => ['class' => 'success',   'label' => 'Selected',     'icon' => 'bi-check-circle-fill'],
                                'rejected'     => ['class' => 'danger',    'label' => 'Rejected',     'icon' => 'bi-x-circle-fill'],
                                'on_hold'      => ['class' => 'secondary', 'label' => 'On Hold',      'icon' => 'bi-pause-circle-fill'],
                                default        => ['class' => 'warning',   'label' => 'Under Review', 'icon' => 'bi-hourglass-split'],
                            };
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width:40px; height:40px; font-size: 0.9rem;">
                                        {{ substr($rec->candidate_name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0">{{ $rec->candidate_name }}</div>
                                        <small class="text-muted">
                                            @if($rec->candidate_phone) <i class="bi bi-telephone me-1"></i>{{ $rec->candidate_phone }} @endif
                                            @if($rec->candidate_email) &bull; {{ $rec->candidate_email }} @endif
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-0">{{ $rec->job_title }}</div>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.7rem;">
                                        Vacancies: {{ $rec->vacancies_count }}
                                    </span>
                                    @if($rec->department)
                                        <small class="text-muted">{{ $rec->department->name }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $stageConfig['class'] }}-subtle text-{{ $stageConfig['class'] }} border border-{{ $stageConfig['class'] }}-subtle px-3 py-1.5 fw-semibold">
                                    {{ $stageConfig['label'] }}
                                </span>
                                @if($rec->interview_date)
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-calendar-event me-1"></i>{{ $rec->interview_date->format('d M, Y') }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-{{ $statusConfig['class'] }}-subtle text-{{ $statusConfig['class'] }} border border-{{ $statusConfig['class'] }}-subtle px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                                    <i class="bi {{ $statusConfig['icon'] }}"></i>
                                    {{ $statusConfig['label'] }}
                                </span>
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">
                                    Offer: <span class="text-capitalize text-muted">{{ str_replace('_', ' ', $rec->offer_status) }}</span>
                                </div>
                                <div class="small text-muted">
                                    Joining: <span class="text-capitalize">{{ str_replace('_', ' ', $rec->joining_status) }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    ₹{{ number_format($rec->cost_per_hire, 2) }}
                                </div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <button class="btn btn-sm btn-light text-primary border-0 rounded-3" wire:click="viewRecruitmentDetails({{ $rec->id }})" title="View Details">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('recruitment_update'))
                                    <button class="btn btn-sm btn-light text-secondary border-0 rounded-3" wire:click="editRecruitment({{ $rec->id }})" title="Edit Record">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    @endif
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('recruitment_delete'))
                                    <button class="btn btn-sm btn-light text-danger border-0 rounded-3" wire:click="deleteRecruitment({{ $rec->id }})" onclick="confirm('Are you sure you want to delete this recruitment record?') || event.stopImmediatePropagation()" title="Delete Record">
                                        <i class="bi bi-trash3-fill"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-person-workspace display-6 mb-3 d-block text-secondary"></i>
                                    <h6 class="fw-semibold">No Recruitment Records Found</h6>
                                    <p class="small text-muted mb-0">No job vacancies or candidate applications have been created yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($recruitments->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $recruitments->links() }}
            </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if ($isFormModalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">
                            {{ $isEditMode ? 'Edit Recruitment Record' : 'Add Candidate / Job Vacancy' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <form wire:submit.prevent="saveRecruitment">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                {{-- Vacancy / Position Title --}}
                                <div class="col-md-8">
                                    <label class="form-label fw-semibold small">Job Vacancy / Position Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('job_title') is-invalid @enderror" wire:model="job_title" placeholder="e.g. Senior Software Engineer, Fitness Coach">
                                    @error('job_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Vacancies Count --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Vacancies Count <span class="text-danger">*</span></label>
                                    <input type="number" min="1" class="form-control @error('vacancies_count') is-invalid @enderror" wire:model="vacancies_count">
                                    @error('vacancies_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Candidate Selection Dropdown --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Select Candidate / Employee</label>
                                    <select class="form-select" wire:model.live="selected_candidate_id">
                                        <option value="">Select Existing Candidate / Staff...</option>
                                        @foreach ($candidates as $cand)
                                            <option value="{{ $cand->id }}" @selected($selected_candidate_id == $cand->id)>{{ $cand->name }} ({{ $cand->email ?? 'No Email' }})</option>
                                        @endforeach
                                        <option value="custom">+ Enter New Candidate Manually</option>
                                    </select>
                                </div>

                                {{-- Candidate Name --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Candidate Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('candidate_name') is-invalid @enderror" wire:model="candidate_name" placeholder="Full candidate name">
                                    @error('candidate_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Candidate Email --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Candidate Email</label>
                                    <input type="email" class="form-control @error('candidate_email') is-invalid @enderror" wire:model="candidate_email" placeholder="candidate@example.com">
                                    @error('candidate_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Candidate Phone --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Candidate Phone</label>
                                    <input type="text" class="form-control @error('candidate_phone') is-invalid @enderror" wire:model="candidate_phone" placeholder="+91 9876543210">
                                    @error('candidate_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Branch --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Branch</label>
                                    <select class="form-select @error('branch_id') is-invalid @enderror" wire:model.live="branch_id">
                                        <option value="">Select Branch...</option>
                                        @php
                                            \Log::info('View branch_id: ' . ($branch_id ?? 'empty') . ' type: ' . gettype($branch_id));
                                            \Log::info('View branches count: ' . count($branches));
                                        @endphp
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}" @selected($branch_id == $branch->id)>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Department --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Department</label>
                                    <select class="form-select @error('department_id') is-invalid @enderror" wire:model="department_id">
                                        <option value="">Select Department...</option>
                                        @php
                                            $departmentsToShow = $filteredDepartments ?? $allDepartments ?? collect();
                                            \Log::info('View departments count: ' . count($departmentsToShow));
                                        @endphp
                                        @foreach ($departmentsToShow as $dept)
                                            <option value="{{ $dept->id }}" @selected($department_id == $dept->id)>{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Interview Date --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Interview Date</label>
                                    <input type="date" class="form-control @error('interview_date') is-invalid @enderror" wire:model="interview_date">
                                    @error('interview_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Interview Stage --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Interview Stage <span class="text-danger">*</span></label>
                                    <select class="form-select @error('interview_stage') is-invalid @enderror" wire:model="interview_stage">
                                        <option value="applied" @selected($interview_stage === 'applied')>Applied</option>
                                        <option value="screening" @selected($interview_stage === 'screening')>Screening</option>
                                        <option value="technical_round" @selected($interview_stage === 'technical_round')>Technical Round</option>
                                        <option value="hr_round" @selected($interview_stage === 'hr_round')>HR Round</option>
                                        <option value="final_round" @selected($interview_stage === 'final_round')>Final Round</option>
                                    </select>
                                    @error('interview_stage') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Selection Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Selection Result / Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                        <option value="under_review" @selected($status === 'under_review')>Under Review</option>
                                        <option value="selected" @selected($status === 'selected')>Selected</option>
                                        <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                                        <option value="on_hold" @selected($status === 'on_hold')>On Hold</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Offer Status --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Offer Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('offer_status') is-invalid @enderror" wire:model="offer_status">
                                        <option value="pending" @selected($offer_status === 'pending')>Pending</option>
                                        <option value="offered" @selected($offer_status === 'offered')>Offered</option>
                                        <option value="offer_accepted" @selected($offer_status === 'offer_accepted')>Offer Accepted</option>
                                        <option value="offer_declined" @selected($offer_status === 'offer_declined')>Offer Declined</option>
                                    </select>
                                    @error('offer_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Joining Status --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Joining Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('joining_status') is-invalid @enderror" wire:model="joining_status">
                                        <option value="pending" @selected($joining_status === 'pending')>Pending</option>
                                        <option value="joined" @selected($joining_status === 'joined')>Joined</option>
                                        <option value="not_joined" @selected($joining_status === 'not_joined')>Not Joined</option>
                                    </select>
                                    @error('joining_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Cost Per Hire --}}
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Cost Per Hire (₹) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control @error('cost_per_hire') is-invalid @enderror" wire:model="cost_per_hire">
                                    @error('cost_per_hire') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Remarks --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Evaluation Notes & Remarks</label>
                                    <textarea class="form-control @error('remarks') is-invalid @enderror" rows="3" wire:model="remarks" placeholder="Notes on candidate skill set, interview feedback, salary negotiations..."></textarea>
                                    @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-light rounded-3 px-4" wire:click="closeModals">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4">
                                <i class="bi bi-check-lg me-1"></i> {{ $isEditMode ? 'Update Recruitment Record' : 'Save Recruitment Record' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- View Details Modal --}}
    @if ($isViewModalOpen && $viewRecruitment)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 rounded-4 shadow">
                    <div class="modal-header border-bottom pb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width:45px; height:45px;">
                                {{ substr($viewRecruitment->candidate_name, 0, 2) }}
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0">{{ $viewRecruitment->candidate_name }}</h5>
                                <small class="text-muted">
                                    Position: {{ $viewRecruitment->job_title }} (Vacancies: {{ $viewRecruitment->vacancies_count }})
                                </small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Contact Information</span>
                                    <div class="fw-bold text-dark">
                                        <i class="bi bi-envelope me-1"></i>{{ $viewRecruitment->candidate_email ?? 'No Email' }}
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-telephone me-1"></i>{{ $viewRecruitment->candidate_phone ?? 'No Phone' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Cost Per Hire</span>
                                    <strong class="text-dark fs-5">
                                        ₹{{ number_format($viewRecruitment->cost_per_hire, 2) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Interview Stage</span>
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-1.5 fw-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $viewRecruitment->interview_stage)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Selection Result</span>
                                    <span class="badge rounded-pill bg-secondary bg-opacity-10 text-dark px-3 py-1.5 fw-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $viewRecruitment->status)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 bg-light rounded-3">
                                    <span class="text-muted small d-block mb-1">Joining Status</span>
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1.5 fw-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $viewRecruitment->joining_status)) }}
                                    </span>
                                </div>
                            </div>

                            @if($viewRecruitment->remarks)
                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-2">Evaluation Notes & Remarks</h6>
                                <div class="p-3 bg-light rounded-3 text-dark" style="white-space: pre-line;">
                                    {{ $viewRecruitment->remarks }}
                                </div>
                            </div>
                            @endif

                            <div class="col-12 border-top pt-3 text-muted small d-flex justify-content-between">
                                <span>Created By: {{ $viewRecruitment->creator->name ?? 'System/Partner' }}</span>
                                <span>Date Created: {{ $viewRecruitment->created_at->format('d M, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-secondary rounded-3 px-4" wire:click="closeModals">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
