<div>
    <!-- Header / Job Details Summary -->
    <div class="card mb-3 shadow-sm border-0 rounded-3">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">{{ $jobPost->job_title }} <span class="badge bg-{{ $jobPost->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $jobPost->status === 'active' ? 'success' : 'secondary' }} rounded-pill ms-2 fs-6 align-middle" style="font-size: 0.75rem !important;">{{ ucfirst($jobPost->status) }}</span></h5>
                    <div class="text-muted small" style="font-size: 0.8rem;">
                        {{ $jobPost->job_city ?? 'Any Location' }} <span class="mx-1">•</span> ₹{{ number_format($jobPost->salary_min) }} - ₹{{ number_format($jobPost->salary_max) }} <span class="mx-1">•</span> {{ $jobPost->minimum_education ?? 'Any Education' }} <span class="mx-1">•</span> {{ $jobPost->min_experience_years ?? '0' }}-{{ $jobPost->max_experience_years ?? '30' }} Years of experience
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary rounded-pill btn-sm px-3 d-flex align-items-center gap-1" wire:click="viewJobPostDetails">
                        <i class="bi bi-eye" wire:loading.remove wire:target="viewJobPostDetails"></i>
                        <span class="spinner-border spinner-border-sm" wire:loading wire:target="viewJobPostDetails" role="status" aria-hidden="true"></span>
                        View Details
                    </button>
                    <a href="{{ route('partner.hrms.job-posts.edit', $jobPost->job_code) }}" class="btn btn-outline-secondary rounded-pill btn-sm px-3 d-flex align-items-center gap-1"><i class="bi bi-pencil"></i> Edit</a>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-top px-3 py-2">
            <ul class="nav nav-pills gap-2" id="candidateTabs">
                <li class="nav-item">
                    <a class="nav-link active bg-dark text-white rounded-3 px-3 py-1 fw-medium small" href="#">Applied to job ({{ $counts['all'] }})</a>
                </li>
                <li class="nav-item d-none">
                    <a class="nav-link text-muted bg-light rounded-3 px-3 py-1 fw-medium small" href="#">Database (0)</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Left Sidebar Filters -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-2 px-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-funnel me-2"></i> Filters</h6>
                </div>
                <div class="card-body px-0 py-0">
                    <!-- Search Box (Kept functional) -->
                    <div class="p-3 border-bottom">
                        <input type="text" wire:model.live="search" class="form-control form-control-sm" placeholder="Search Name or Phone...">
                    </div>

                    <!-- Accordion 1 -->
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2" data-bs-toggle="collapse" data-bs-target="#collapseShowCandidates" aria-expanded="true" style="cursor: pointer;">
                            <span class="fw-bold small" style="color: #223354;">Show candidates who</span>
                            <i class="bi bi-chevron-up text-muted toggle-icon"></i>
                        </div>
                        <div class="collapse show" id="collapseShowCandidates">
                            <div class="d-flex flex-column gap-3 mt-3 small" style="color: #4c5b76;">
                                <label class="d-flex align-items-center gap-2 m-0 cursor-pointer">
                                    <input type="checkbox" wire:model.live="filterMatched" class="form-check-input mt-0 shadow-none" style="width: 1.15rem; height: 1.15rem; border-color: #8c98a4; border-radius: 3px;"> 
                                    <span>Matched to job requirements ({{ $counts['matched'] ?? 0 }}) <i class="bi bi-info-circle ms-1" style="color: #aeb4c0;"></i></span>
                                </label>
                                <label class="d-flex align-items-center gap-2 m-0 cursor-pointer">
                                    <input type="checkbox" wire:model.live="filterHasResume" class="form-check-input mt-0 shadow-none" style="width: 1.15rem; height: 1.15rem; border-color: #8c98a4; border-radius: 3px;"> 
                                    <span>Have Resume Attached ({{ $counts['resume'] ?? 0 }})</span>
                                </label>
                                <label class="d-flex align-items-center gap-2 m-0 cursor-pointer">
                                    <input type="checkbox" wire:model.live="filterContacted" class="form-check-input mt-0 shadow-none" style="width: 1.15rem; height: 1.15rem; border-color: #8c98a4; border-radius: 3px;" disabled> 
                                    <span>Tried contacting you (0) <span class="badge text-success bg-success-subtle border-0 py-1" style="font-size: 0.6rem;">NEW</span></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Accordion 2 -->
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#collapseWhatsapp" aria-expanded="false" style="cursor: pointer; opacity: 0.6;">
                            <span class="fw-bold small" style="color: #aeb4c0;">Whatsapp Connect</span>
                            <i class="bi bi-chevron-down toggle-icon" style="color: #aeb4c0;"></i>
                        </div>
                        <div class="collapse" id="collapseWhatsapp">
                            <div class="mt-3 small text-muted">Feature coming soon.</div>
                        </div>
                    </div>

                    <!-- Accordion 3 -->
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3" data-bs-toggle="collapse" data-bs-target="#collapseAppliedIn" aria-expanded="true" style="cursor: pointer;">
                            <span class="fw-bold small" style="color: #223354;">Applied in</span>
                            <i class="bi bi-chevron-up text-muted toggle-icon"></i>
                        </div>
                        <div class="collapse show" id="collapseAppliedIn">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm rounded-pill px-3 {{ $filterAppliedIn === 'last 3 days' ? 'bg-primary-subtle text-primary border-primary' : 'bg-white border-secondary-subtle' }}" style="color: #223354; border: 1px solid #c9ced6;" wire:click="setFilterAppliedIn('last 3 days')">last 3 days</button>
                                <button type="button" class="btn btn-sm rounded-pill px-3 {{ $filterAppliedIn === 'last 7 days' ? 'bg-primary-subtle text-primary border-primary' : 'bg-white border-secondary-subtle' }}" style="color: #223354; border: 1px solid #c9ced6;" wire:click="setFilterAppliedIn('last 7 days')">last 7 days</button>
                                <button type="button" class="btn btn-sm rounded-pill px-3 {{ $filterAppliedIn === 'last 15 days' ? 'bg-primary-subtle text-primary border-primary' : 'bg-white border-secondary-subtle' }}" style="color: #223354; border: 1px solid #c9ced6;" wire:click="setFilterAppliedIn('last 15 days')">last 15 days</button>
                                <button type="button" class="btn btn-sm rounded-pill px-3 {{ $filterAppliedIn === 'last 30 days' ? 'bg-primary-subtle text-primary border-primary' : 'bg-white border-secondary-subtle' }}" style="color: #223354; border: 1px solid #c9ced6;" wire:click="setFilterAppliedIn('last 30 days')">last 30 days</button>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 text-center">
                        <a href="#" class="small fw-bold text-decoration-none" style="color: #29a073;">See more <i class="bi bi-chevron-down ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content Candidates List -->
        <div class="col-md-9">
            <!-- Tabs / Counters -->
            <div class="card shadow-sm border-0 rounded-3 mb-3">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-dark">Applied to job</h6>
                        <!-- Sorting could go here -->
                    </div>
                    
                    <div class="d-flex gap-2 text-center" style="overflow-x: auto;">
                        <div class="border rounded p-2 flex-grow-1 cursor-pointer {{ $statusFilter === 'All' ? 'border-success border-2' : 'border-secondary-subtle bg-white' }}" wire:click="setStatusFilter('All')" style="cursor:pointer; min-width: 90px;">
                            <div class="h5 mb-0 fw-bold {{ $statusFilter === 'All' ? 'text-success' : 'text-primary' }}">{{ $counts['all'] }}</div>
                            <div class="small {{ $statusFilter === 'All' ? 'text-success fw-bold' : 'text-muted fw-medium' }}" style="font-size: 0.75rem;">All candidates</div>
                        </div>
                        <div class="border rounded p-2 flex-grow-1 cursor-pointer {{ $statusFilter === 'Action Pending' ? 'border-success border-2' : 'border-secondary-subtle bg-white' }}" wire:click="setStatusFilter('Action Pending')" style="cursor:pointer; min-width: 90px;">
                            <div class="h5 mb-0 fw-bold {{ $statusFilter === 'Action Pending' ? 'text-success' : 'text-primary' }}">{{ $counts['pending'] }}</div>
                            <div class="small {{ $statusFilter === 'Action Pending' ? 'text-success fw-bold' : 'text-muted fw-medium' }}" style="font-size: 0.75rem;">Action Pending</div>
                        </div>
                        <div class="border rounded p-2 flex-grow-1 cursor-pointer {{ $statusFilter === 'Viewed' ? 'border-success border-2' : 'border-secondary-subtle bg-white' }}" wire:click="setStatusFilter('Viewed')" style="cursor:pointer; min-width: 90px;">
                            <div class="h5 mb-0 fw-bold {{ $statusFilter === 'Viewed' ? 'text-success' : 'text-primary' }}">{{ $counts['viewed'] }}</div>
                            <div class="small {{ $statusFilter === 'Viewed' ? 'text-success fw-bold' : 'text-muted fw-medium' }}" style="font-size: 0.75rem;">Viewed Number</div>
                        </div>
                        <div class="border rounded p-2 flex-grow-1 cursor-pointer {{ $statusFilter === 'Shortlisted' ? 'border-success border-2' : 'border-secondary-subtle bg-white' }}" wire:click="setStatusFilter('Shortlisted')" style="cursor:pointer; min-width: 90px;">
                            <div class="h5 mb-0 fw-bold {{ $statusFilter === 'Shortlisted' ? 'text-success' : 'text-primary' }}">{{ $counts['shortlisted'] }}</div>
                            <div class="small {{ $statusFilter === 'Shortlisted' ? 'text-success fw-bold' : 'text-muted fw-medium' }}" style="font-size: 0.75rem;">Shortlisted</div>
                        </div>
                        <div class="border rounded p-2 flex-grow-1 cursor-pointer {{ $statusFilter === 'Rejected' ? 'border-success border-2' : 'border-secondary-subtle bg-white' }}" wire:click="setStatusFilter('Rejected')" style="cursor:pointer; min-width: 90px;">
                            <div class="h5 mb-0 fw-bold {{ $statusFilter === 'Rejected' ? 'text-success' : 'text-primary' }}">{{ $counts['rejected'] }}</div>
                            <div class="small {{ $statusFilter === 'Rejected' ? 'text-success fw-bold' : 'text-muted fw-medium' }}" style="font-size: 0.75rem;">Rejected</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 px-3 py-2 d-flex justify-content-between align-items-center small">
                    <span class="text-muted fw-medium" style="font-size: 0.75rem;">Showing {{ $candidates->count() }} candidates</span>
                    <a href="#" wire:click.prevent="exportExcel" class="text-success text-decoration-none fw-bold d-flex align-items-center" style="font-size: 0.75rem;">
                        <i class="bi bi-download me-1" wire:loading.remove wire:target="exportExcel"></i>
                        <span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="exportExcel" role="status" aria-hidden="true"></span>
                        Download Excel
                    </a>
                </div>
            </div>
            
            <div class="alert mb-3 shadow-sm border-0 rounded-4 p-2 px-3 d-flex gap-3 align-items-center" style="background-color: #f0edff;">
                <i class="bi bi-stars fs-5" style="color: #6c47ff;"></i>
                <div>
                    <h6 class="fw-bold mb-0" style="color: #6c47ff; font-size: 0.85rem;">High Matches</h6>
                    <div class="small" style="color: #6c47ff; font-size: 0.75rem;">Candidates matching all your key requirements. Connect with them before someone else does!</div>
                </div>
            </div>

            <!-- Messages -->
            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Candidate Cards -->
            @forelse($candidates as $candidate)
            <div class="card mb-3 shadow-sm rounded-3" style="background-color: #f8f6ff; border: 1px solid #e5dfff;">
                <div class="card-body p-2 px-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex gap-2 align-items-center">
                            @if(!empty($candidate->applicant->profile_image_url))
                                <img src="{{ $candidate->applicant->profile_image_url }}" alt="Profile" class="rounded-circle shadow-sm" style="width: 32px; height: 32px; object-fit: cover; border: 1px solid #e5dfff;">
                            @else
                                <div class="rounded-circle bg-secondary text-white d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                    {{ strtoupper(substr($candidate->applicant->name ?? 'U', 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                                    {{ $candidate->applicant->name ?? 'Unknown' }}
                                    <span wire:click="viewProfile({{ $candidate->id }})" class="text-success fw-medium" style="cursor:pointer; font-size: 0.7rem;">
                                        <i class="bi bi-box-arrow-up-right me-1" wire:loading.remove wire:target="viewProfile({{ $candidate->id }})"></i>
                                        <span class="spinner-border spinner-border-sm me-1" wire:loading wire:target="viewProfile({{ $candidate->id }})" role="status" aria-hidden="true"></span>
                                        View profile <i class="bi bi-chevron-double-right"></i>
                                    </span>
                                </h6>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-person-fill"></i> {{ substr($candidate->gender ?? 'N/A', 0, 1) }}, {{ $candidate->age ?? '-' }} yr <span class="mx-1">•</span> 
                                    <i class="bi bi-briefcase-fill"></i> {{ $candidate->experience_years ?? '0' }} yrs <span class="mx-1">•</span> 
                                    <i class="bi bi-geo-alt-fill"></i> {{ $candidate->address ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-white text-primary border border-primary-subtle rounded-pill px-2 py-1 fw-medium shadow-sm" style="color: #6c47ff !important; font-size: 0.65rem;"><i class="bi bi-stars"></i> High Match</span>
                    </div>

                    <div class="bg-white border border-light rounded-2 py-1 px-2 mb-2 d-flex align-items-center gap-2 shadow-sm flex-wrap" style="font-size: 0.7rem;">
                        <span class="fw-bold" style="color: #6c47ff;"><i class="bi bi-stars"></i> Matching:</span>
                        <span class="badge rounded-pill border border-primary text-primary bg-white px-2 py-0"><i class="bi bi-check2"></i> Gender</span>
                        <span class="badge rounded-pill border border-primary text-primary bg-white px-2 py-0"><i class="bi bi-check2"></i> Age</span>
                        <span class="badge rounded-pill border border-primary text-primary bg-white px-2 py-0"><i class="bi bi-check2"></i> Skills</span>
                        <span class="badge rounded-pill border border-primary text-primary bg-white px-2 py-0"><i class="bi bi-check2"></i> Degree</span>
                    </div>

                    <div class="text-muted mb-2" style="font-size: 0.75rem;">
                        <div class="row mb-1">
                            <div class="col-3"><i class="bi bi-mortarboard me-2"></i> Education</div>
                            <div class="col-9 text-dark fw-medium">{{ $candidate->education_level ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-3"><i class="bi bi-tools me-2"></i> Skills</div>
                            <div class="col-9 text-dark">
                                @php
                                    $candidateSkills = $candidate->applicant?->candidateProfile?->skills;
                                @endphp
                                @if(is_array($candidateSkills) && count($candidateSkills) > 0)
                                    {{ implode(', ', $candidateSkills) }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="row mb-0">
                            <div class="col-3"><i class="bi bi-translate me-2"></i> Language</div>
                            <div class="col-9 text-dark">English ({{ $candidate->english_proficiency ?? 'N/A' }})</div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-1 pt-2 border-top">
                        <div class="d-flex gap-2">
                            <a href="tel:{{ $candidate->applicant->mobile ?? '' }}" class="btn btn-sm btn-success rounded-3 px-3 fw-medium shadow-sm d-flex align-items-center gap-2" style="font-size: 0.75rem;">
                                <i class="bi bi-telephone-fill"></i> View Number
                            </a>
                            <a href="https://wa.me/91{{ $candidate->applicant->mobile ?? '' }}" target="_blank" class="btn btn-sm btn-outline-success rounded-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 28px; height: 28px; padding: 0;">
                                <i class="bi bi-whatsapp" style="font-size: 0.85rem;"></i>
                            </a>
                        </div>
                        <div class="d-flex gap-2">
                            @if(empty($candidate->status) || $candidate->status === 'pending' || $candidate->status === 'applied' || $candidate->status === 'viewed')
                                <button class="btn btn-sm btn-outline-danger fw-medium rounded-3 px-3 shadow-sm d-flex align-items-center gap-1" wire:click="reject({{ $candidate->id }})" style="font-size: 0.75rem;">
                                    <i class="bi bi-x fs-6" wire:loading.remove wire:target="reject({{ $candidate->id }})"></i>
                                    <span class="spinner-border spinner-border-sm" wire:loading wire:target="reject({{ $candidate->id }})" role="status" aria-hidden="true"></span>
                                    Reject
                                </button>
                                <button class="btn btn-sm btn-outline-success fw-medium rounded-3 px-3 shadow-sm d-flex align-items-center gap-1" wire:click="shortlist({{ $candidate->id }})" style="font-size: 0.75rem;">
                                    <i class="bi bi-check2 fs-6" wire:loading.remove wire:target="shortlist({{ $candidate->id }})"></i>
                                    <span class="spinner-border spinner-border-sm" wire:loading wire:target="shortlist({{ $candidate->id }})" role="status" aria-hidden="true"></span>
                                    Shortlist
                                </button>
                            @else
                                <span class="badge bg-{{ $candidate->status === 'shortlisted' ? 'success' : 'danger' }}-subtle text-{{ $candidate->status === 'shortlisted' ? 'success' : 'danger' }} px-3 py-1 rounded-pill" style="font-size: 0.7rem; border: 1px solid {{ $candidate->status === 'shortlisted' ? '#198754' : '#dc3545' }};">
                                    <i class="bi bi-{{ $candidate->status === 'shortlisted' ? 'check2' : 'x' }} me-1"></i> {{ ucfirst($candidate->status) }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-2 text-muted" style="font-size: 0.65rem;">
                        <div>
                            Applied {{ $candidate->created_at->diffForHumans() }} <span class="mx-1">|</span> Active {{ $candidate->created_at->diffForHumans() }}
                        </div>
                        <div style="cursor:pointer;" class="text-dark fw-medium">
                            <i class="bi bi-pencil"></i> Add a note
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body text-center py-5">
                    <i class="bi bi-people fs-1 text-muted d-block mb-3"></i>
                    <h5 class="text-dark fw-bold">No candidates found</h5>
                    <p class="text-muted mb-0">Try adjusting your filters or wait for candidates to apply.</p>
                </div>
            </div>
            @endforelse

            <div class="mt-4">
                {{ $candidates->links() }}
            </div>
        </div>
    </div>

    <!-- View Modal -->
    @if($isViewModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                <!-- Header -->
                <div class="modal-header border-bottom-0 pb-0 pt-3 px-3 position-relative">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-1">{{ $jobPost->job_title }}</h5>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-{{ $jobPost->status === 'active' ? 'success' : ($jobPost->status === 'draft' ? 'warning' : 'danger') }}-subtle text-{{ $jobPost->status === 'active' ? 'success' : ($jobPost->status === 'draft' ? 'warning' : 'danger') }} border-0 px-2 py-1 rounded-pill fw-medium" style="font-size: 0.65rem;">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.4rem;"></i> {{ ucfirst($jobPost->status) }}
                                </span>
                                <span class="text-muted small fw-medium" style="font-size: 0.75rem;"><i class="bi bi-hash"></i>{{ $jobPost->job_code }}</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close shadow-none" wire:click="closeModals" style="background-color: #f1f3f5; border-radius: 50%; padding: 0.5rem; width: 10px; height: 10px;"></button>
                    </div>
                </div>

                <!-- Body -->
                <div class="modal-body p-3 custom-scrollbar">
                    
                    <!-- Top Highlights -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-2 h-100 border border-light">
                                <div class="text-muted mb-1 fw-medium" style="font-size: 0.75rem;"><i class="bi bi-wallet2 text-success me-1"></i> Salary Range</div>
                                <div class="fw-bold text-dark h6 mb-0">₹{{ number_format($jobPost->salary_min) }} - ₹{{ number_format($jobPost->salary_max) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-2 h-100 border border-light">
                                <div class="text-muted mb-1 fw-medium" style="font-size: 0.75rem;"><i class="bi bi-people text-primary me-1"></i> Vacancies</div>
                                <div class="fw-bold text-dark h6 mb-0">{{ $jobPost->vacancies_count }} Openings</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-2 h-100 border border-light position-relative overflow-hidden">
                                <div class="position-absolute end-0 bottom-0 opacity-10" style="transform: translate(20%, 20%);"><i class="bi bi-gift-fill text-warning" style="font-size: 3rem;"></i></div>
                                <div class="text-muted mb-1 fw-medium" style="font-size: 0.75rem;"><i class="bi bi-gift text-warning me-1"></i> Referral Bonus</div>
                                <div class="fw-bold text-dark h6 mb-0">₹{{ number_format($jobPost->referral_budget, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;"><i class="bi bi-info-circle text-primary me-2"></i>Basic Details</h6>
                            <ul class="list-group list-group-flush border-top border-bottom rounded-0" style="font-size: 0.75rem;">
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-building me-2"></i>Branch</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->branch->name ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-diagram-3 me-2"></i>Department</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->department->name ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-geo-alt me-2"></i>Location</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->job_city ?? 'Any' }} <span class="badge bg-secondary-subtle text-secondary border-0 ms-1" style="font-size: 0.6rem;">{{ $jobPost->is_work_from_home ? 'WFH' : 'Office' }}</span></span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-pin-map me-2"></i>Radius</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->distance ?? 'Any' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-briefcase me-2"></i>Employment</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->employment_type }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-moon-stars me-2"></i>Shift</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->job_type ?? 'Day Shift' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-cash-stack me-2"></i>Pay Type</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->salary_type ?? 'Fixed' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-box-arrow-in-right me-2"></i>Joining Fee</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->joining_fee_required ? 'Yes' : 'No' }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;"><i class="bi bi-person-check text-primary me-2"></i>Requirements</h6>
                            <ul class="list-group list-group-flush border-top border-bottom rounded-0" style="font-size: 0.75rem;">
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-mortarboard me-2"></i>Education</span>
                                    <span class="fw-medium text-dark text-end" style="max-width: 60%;">{{ $jobPost->minimum_education ?? 'Any' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-clock-history me-2"></i>Experience</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->min_experience_years ?? 0 }} - {{ $jobPost->max_experience_years ?? 'Any' }} Yrs</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-calendar-event me-2"></i>Age Limit</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->min_age ?? 18 }} - {{ $jobPost->max_age ?? 'Any' }} Yrs</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-gender-ambiguous me-2"></i>Gender</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->gender_preference ?? 'Any' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted"><i class="bi bi-translate me-2"></i>English Level</span>
                                    <span class="fw-medium text-dark text-end">{{ $jobPost->english_level ?? 'Any' }}</span>
                                </li>
                                
                                <li class="list-group-item bg-transparent px-0 py-1 border-bottom-0 border-top mt-1 pt-1">
                                    @php
                                        $interviewInfo = is_string($jobPost->interview_information) ? json_decode($jobPost->interview_information, true) : $jobPost->interview_information;
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted"><i class="bi bi-headset me-2"></i>Interview Contact</span>
                                        <span class="fw-medium text-dark text-end">{{ $interviewInfo['contact_preference'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="text-muted"><i class="bi bi-door-open me-2"></i>Walk-in</span>
                                        <span class="fw-medium text-dark text-end">{{ isset($interviewInfo['is_walk_in']) && $interviewInfo['is_walk_in'] ? 'Yes' : 'No' }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Additional Perks -->
                    @php
                        $perks = is_string($jobPost->additional_perks) ? json_decode($jobPost->additional_perks, true) : $jobPost->additional_perks;
                    @endphp
                    @if(!empty($perks))
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;"><i class="bi bi-gift text-primary me-2"></i>Additional Perks</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($perks as $perk)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-medium" style="font-size: 0.7rem;">{{ $perk }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Skills -->
                    @if(!empty($jobPost->skills))
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;"><i class="bi bi-stars text-primary me-2"></i>Required Skills</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(explode(',', $jobPost->skills) as $skill)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-medium" style="font-size: 0.7rem;">{{ trim($skill) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Screening Questions -->
                    @php
                        $sqData = $jobPost->screening_questions;
                        $sqArray = is_string($sqData) ? json_decode($sqData, true) : (is_array($sqData) ? $sqData : []);
                        $questionsList = $sqArray['questions'] ?? (isset($sqArray[0]) ? $sqArray : []);
                        $answersList = $sqArray['answers'] ?? [];
                    @endphp
                    @if(!empty($questionsList))
                    <div class="mb-3">
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;"><i class="bi bi-question-circle text-primary me-2"></i>Screening Questions</h6>
                        <ul class="list-group list-group-flush border-top border-bottom rounded-0" style="font-size: 0.75rem;">
                            @foreach($questionsList as $idx => $q)
                                @php
                                    $key = $q['id'] ?? $q['question'] ?? $idx;
                                    $ans = $answersList[$key] ?? $q['partner_answer'] ?? '';
                                    if(empty($ans)) $ans = 'N/A';
                                @endphp
                                <li class="list-group-item bg-transparent px-0 py-1 d-flex justify-content-between align-items-center border-bottom-0">
                                    <div class="fw-medium text-dark">{{ $q['question'] ?? 'Question '.($idx+1) }} {!! !empty($q['required']) ? '<span class="text-danger">*</span>' : '' !!}</div>
                                    <div class="text-muted text-end" style="font-size: 0.7rem;">
                                        @if(($q['type'] ?? '') === 'file')
                                            <i class="bi bi-file-earmark-arrow-up text-primary me-1"></i><strong>File Upload</strong>
                                        @else
                                            <i class="bi bi-check2-circle text-success me-1"></i><strong>{{ is_array($ans) ? implode(', ', $ans) : $ans }}</strong>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Job Description -->
                    <div class="mb-2">
                        <h6 class="fw-bold text-dark mb-2" style="font-size: 0.85rem;"><i class="bi bi-card-text text-primary me-2"></i>Job Description</h6>
                        <div class="p-2 bg-light rounded-3 text-dark" style="white-space: pre-wrap; font-size: 0.75rem; line-height: 1.5; border: 1px solid #f0f0f0;">{{ $jobPost->job_description ?? 'No description provided.' }}</div>
                    </div>

                </div>
                
                <!-- Footer -->
                <div class="modal-footer border-top-0 pt-0 px-3 pb-3">
                    <button type="button" class="btn btn-light btn-sm rounded-pill px-4 fw-medium border" wire:click="closeModals">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Candidate Profile Modal -->
    @if($isCandidateProfileModalOpen && $this->selectedCandidate)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; height: 90vh;">
                <!-- Header -->
                <div class="modal-header border-bottom px-4 py-2 d-flex justify-content-between align-items-center bg-light">
                    <div class="d-flex align-items-center gap-3">
                        @if(!empty($this->selectedCandidate->applicant->profile_image_url))
                            <img src="{{ $this->selectedCandidate->applicant->profile_image_url }}" alt="Profile" class="rounded-circle shadow-sm" style="width: 45px; height: 45px; object-fit: cover; border: 1px solid #e5dfff;">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex justify-content-center align-items-center fw-bold shadow-sm" style="width: 45px; height: 45px; font-size: 1.1rem;">
                                {{ strtoupper(substr($this->selectedCandidate->applicant->name ?? 'U', 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">{{ $this->selectedCandidate->applicant->name ?? 'Unknown Candidate' }}</h5>
                            <div class="text-muted" style="font-size: 0.8rem; margin-top: 2px;">
                                <i class="bi bi-telephone-fill me-1"></i> {{ $this->selectedCandidate->applicant->mobile ?? 'N/A' }} 
                                <span class="mx-2 text-light">|</span>
                                <i class="bi bi-envelope-fill me-1"></i> {{ $this->selectedCandidate->applicant->email ?? 'N/A' }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        @if(empty($this->selectedCandidate->status) || $this->selectedCandidate->status === 'pending' || $this->selectedCandidate->status === 'applied' || $this->selectedCandidate->status === 'viewed')
                            <button class="btn btn-sm btn-danger fw-medium px-3 shadow-sm d-flex align-items-center gap-1" wire:click="reject({{ $this->selectedCandidate->id }}); closeModals();">
                                <span wire:loading.remove wire:target="reject({{ $this->selectedCandidate->id }})">Reject</span>
                                <span class="spinner-border spinner-border-sm" wire:loading wire:target="reject({{ $this->selectedCandidate->id }})" role="status" aria-hidden="true"></span>
                            </button>
                            <button class="btn btn-sm btn-success fw-medium px-3 shadow-sm d-flex align-items-center gap-1" wire:click="shortlist({{ $this->selectedCandidate->id }}); closeModals();">
                                <span wire:loading.remove wire:target="shortlist({{ $this->selectedCandidate->id }})">Shortlist</span>
                                <span class="spinner-border spinner-border-sm" wire:loading wire:target="shortlist({{ $this->selectedCandidate->id }})" role="status" aria-hidden="true"></span>
                            </button>
                        @else
                            <span class="badge bg-{{ $this->selectedCandidate->status === 'shortlisted' ? 'success' : 'danger' }} px-3 py-1 rounded-pill" style="font-size: 0.8rem;">
                                {{ ucfirst($this->selectedCandidate->status) }}
                            </span>
                        @endif
                        <button type="button" class="btn-close shadow-none ms-2" wire:click="closeModals" style="background-color: #e9ecef; border-radius: 50%; padding: 0.5rem;"></button>
                    </div>
                </div>

                <!-- Body -->
                <div class="modal-body p-0 custom-scrollbar d-flex flex-column flex-md-row">
                    <!-- Left Sidebar (Details) -->
                    <div class="border-end p-3 bg-white" style="flex: 0 0 350px; overflow-y: auto; font-size: 0.85rem;">
                        @php
                            $profile = $this->selectedCandidate->applicant->candidateProfile ?? null;
                        @endphp
                        <h6 class="fw-bold text-dark mb-3 border-bottom pb-2" style="font-size: 0.95rem;">Candidate Details</h6>
                        
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="text-muted mb-1" style="font-size: 0.75rem;">Gender & Age</div>
                                <div class="fw-medium text-dark">{{ $this->selectedCandidate->gender ?? 'N/A' }}, {{ $this->selectedCandidate->age ?? '-' }} yrs</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted mb-1" style="font-size: 0.75rem;">Total Experience</div>
                                <div class="fw-medium text-dark">{{ $this->selectedCandidate->experience_years ?? '0' }} Years</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted mb-1" style="font-size: 0.75rem;">English Proficiency</div>
                            <div class="fw-medium text-dark">{{ $this->selectedCandidate->english_proficiency ?? 'N/A' }}</div>
                        </div>

                        <div class="mb-3">
                            <div class="text-muted mb-1" style="font-size: 0.75rem;">Location / Address</div>
                            <div class="fw-medium text-dark">{{ $this->selectedCandidate->address ?? 'N/A' }}</div>
                        </div>

                        <!-- Extended Profile Data -->
                        @if($profile)
                            <div class="mb-3 pt-2 border-top">
                                <h6 class="fw-bold text-dark mb-3 mt-2" style="font-size: 0.9rem;"><i class="bi bi-person-lines-fill text-primary me-2"></i>Preferences & Salary</h6>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-muted mb-1" style="font-size: 0.75rem;">Expected Salary</div>
                                        <div class="fw-medium text-dark">{{ $profile->expected_salary ? '₹' . number_format($profile->expected_salary) : 'N/A' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted mb-1" style="font-size: 0.75rem;">Current Salary</div>
                                        <div class="fw-medium text-dark">{{ $profile->current_monthly_salary ? '₹' . number_format($profile->current_monthly_salary) : 'N/A' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted mb-1" style="font-size: 0.75rem;">Job Type</div>
                                        <div class="fw-medium text-dark">{{ $profile->preferred_job_type ?? 'Any' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted mb-1" style="font-size: 0.75rem;">Work Mode</div>
                                        <div class="fw-medium text-dark">{{ $profile->preferred_work_mode ?? 'Any' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-muted mb-1" style="font-size: 0.75rem;">Shift</div>
                                        <div class="fw-medium text-dark">{{ $profile->preferred_shift ?? 'Any' }}</div>
                                    </div>
                                </div>
                            </div>

                            @if(!empty($profile->preferred_job_roles))
                                <div class="mb-3">
                                    <div class="text-muted mb-2 fw-medium" style="font-size: 0.75rem;">Preferred Roles</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach(is_string($profile->preferred_job_roles) ? json_decode($profile->preferred_job_roles, true) : $profile->preferred_job_roles as $role)
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1" style="font-weight: 500;">{{ $role }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($profile->preferred_locations))
                                <div class="mb-3">
                                    <div class="text-muted mb-2 fw-medium" style="font-size: 0.75rem;">Preferred Locations</div>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach(is_string($profile->preferred_locations) ? json_decode($profile->preferred_locations, true) : $profile->preferred_locations as $loc)
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1" style="font-weight: 500;"><i class="bi bi-geo-alt me-1"></i>{{ $loc }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($profile->skills))
                                <div class="mb-3">
                                    <h6 class="fw-bold text-dark mb-2 mt-3" style="font-size: 0.9rem;"><i class="bi bi-stars text-primary me-2"></i>Skills</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach(is_string($profile->skills) ? json_decode($profile->skills, true) : $profile->skills as $skill)
                                            <span class="badge bg-light text-dark border px-2 py-1" style="font-weight: 500;">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($profile->educations))
                                <div class="mb-3">
                                    <h6 class="fw-bold text-dark mb-2 mt-3" style="font-size: 0.9rem;"><i class="bi bi-mortarboard text-primary me-2"></i>Education</h6>
                                    @if($profile->highest_education)
                                        <div class="text-primary fw-bold mb-2" style="font-size: 0.75rem;">Highest: {{ $profile->highest_education }}</div>
                                    @endif
                                    @php $educations = is_string($profile->educations) ? json_decode($profile->educations, true) : $profile->educations; @endphp
                                    @if(is_array($educations))
                                        @foreach($educations as $edu)
                                            <div class="mb-1 p-2 bg-light rounded" style="border: 1px solid #f0f0f0;">
                                                <div class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $edu['degree'] ?? 'N/A' }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $edu['university'] ?? 'N/A' }} • {{ $edu['type'] ?? '' }}</div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endif

                            @if(!empty($profile->work_experiences))
                                <div class="mb-3">
                                    <h6 class="fw-bold text-dark mb-2 mt-3" style="font-size: 0.9rem;"><i class="bi bi-briefcase text-primary me-2"></i>Work Experience</h6>
                                    @php $experiences = is_string($profile->work_experiences) ? json_decode($profile->work_experiences, true) : $profile->work_experiences; @endphp
                                    @if(is_array($experiences))
                                        @foreach($experiences as $exp)
                                            <div class="mb-2 p-2 bg-light rounded border-start border-3 border-primary" style="border-radius: 6px !important;">
                                                <div class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $exp['job_title'] ?? 'N/A' }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $exp['company'] ?? 'N/A' }} • {{ $exp['type'] ?? '' }}</div>
                                                @if(isset($exp['currently_working']) && $exp['currently_working'])
                                                    <span class="badge bg-success-subtle text-success border-0 mt-1" style="font-size: 0.65rem;">Currently Working</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endif

                            @if(!empty($profile->internships))
                                <div class="mb-3">
                                    <h6 class="fw-bold text-dark mb-2 mt-3" style="font-size: 0.9rem;"><i class="bi bi-journal-bookmark text-primary me-2"></i>Internships</h6>
                                    @php $internships = is_string($profile->internships) ? json_decode($profile->internships, true) : $profile->internships; @endphp
                                    @if(is_array($internships))
                                        @foreach($internships as $int)
                                            <div class="mb-1 p-2 bg-light rounded" style="border: 1px solid #f0f0f0;">
                                                <div class="fw-bold text-dark" style="font-size: 0.8rem;">{{ $int['role'] ?? 'N/A' }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $int['company'] ?? 'N/A' }}</div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            @endif

                            @if(!empty($profile->documents_and_assets))
                                <div class="mb-3">
                                    <h6 class="fw-bold text-dark mb-2 mt-3" style="font-size: 0.9rem;"><i class="bi bi-file-earmark-check text-primary me-2"></i>Documents & Assets</h6>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach(is_string($profile->documents_and_assets) ? json_decode($profile->documents_and_assets, true) : $profile->documents_and_assets as $doc)
                                            <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-check2 text-success me-1"></i>{{ $doc }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif

                        @if($this->selectedCandidate->screening_answers)
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="fw-bold text-dark mb-3">Screening Answers</h6>
                            @php
                                $answers = is_string($this->selectedCandidate->screening_answers) ? json_decode($this->selectedCandidate->screening_answers, true) : $this->selectedCandidate->screening_answers;
                            @endphp
                            @if(is_array($answers))
                                @foreach($answers as $question => $answer)
                                    <div class="mb-3">
                                        <div class="text-muted small mb-1">{{ $question }}</div>
                                        <div class="fw-medium text-dark bg-light p-2 rounded">{{ is_array($answer) ? implode(', ', $answer) : $answer }}</div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        @endif
                    </div>

                    <!-- Right Content (Resume Viewer) -->
                    <div class="flex-grow-1 bg-light position-relative p-0 h-100">
                        @php
                            $resumePath = null;
                            $resumeUrl = null;
                            $applicationResume = $this->selectedCandidate->resume;
                            $profileResume = $profile->resume_path ?? null;

                            // Check Profile Resume FIRST (Latest updated resume)
                            if ($profileResume && (str_starts_with($profileResume, 'http') || file_exists(public_path('storage/' . $profileResume)))) {
                                $resumePath = $profileResume;
                                $resumeUrl = $profile->resume_url;
                            } 
                            // Fallback to Application Resume (The one they originally applied with)
                            elseif ($applicationResume && (str_starts_with($applicationResume, 'http') || file_exists(public_path('storage/' . $applicationResume)))) {
                                $resumePath = $applicationResume;
                                $resumeUrl = $this->selectedCandidate->resume_url;
                            } 
                            // If neither physically exists, fallback to whatever is set to show 'Not Found'
                            else {
                                $resumePath = $profileResume ?? $applicationResume;
                                $resumeUrl = $profile->resume_url ?? ($this->selectedCandidate->resume_url ?? null);
                            }

                            $fileExists = $resumePath && (str_starts_with($resumePath, 'http') || file_exists(public_path('storage/' . $resumePath)));
                            $extension = $resumePath ? pathinfo($resumePath, PATHINFO_EXTENSION) : '';
                        @endphp
                        
                        @if($resumePath)
                            @if(!$fileExists)
                                <div class="d-flex flex-column justify-content-center align-items-center h-100 p-5 text-center">
                                    <i class="bi bi-file-earmark-break text-danger mb-3" style="font-size: 5rem;"></i>
                                    <h5 class="fw-bold text-dark">Resume Not Found</h5>
                                    <p class="text-muted">The resume file could not be located on the server.</p>
                                </div>
                            @elseif(in_array(strtolower($extension), ['pdf', 'jpg', 'jpeg', 'png']) || str_starts_with($resume, 'http'))
                                <iframe src="{{ $resumeUrl }}" width="100%" height="100%" style="border: none; min-height: 500px;"></iframe>
                            @else
                                <div class="d-flex flex-column justify-content-center align-items-center h-100 p-5 text-center">
                                    <i class="bi bi-file-earmark-text text-primary mb-3" style="font-size: 5rem;"></i>
                                    <h5 class="fw-bold text-dark">Resume Attached</h5>
                                    <p class="text-muted">This file type cannot be previewed in the browser.</p>
                                    <a href="{{ $resumeUrl }}" download class="btn btn-primary mt-2"><i class="bi bi-download me-2"></i>Download Resume</a>
                                </div>
                            @endif
                        @else
                            <div class="d-flex flex-column justify-content-center align-items-center h-100 p-5 text-center">
                                <i class="bi bi-file-earmark-x text-muted mb-3" style="font-size: 5rem;"></i>
                                <h5 class="fw-bold text-muted">No Resume Attached</h5>
                                <p class="text-muted small">This candidate applied without submitting a resume document.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
