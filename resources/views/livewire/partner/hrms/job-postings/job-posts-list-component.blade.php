<div>
    @if ($step === 0)
        <!-- List View -->
        <div class="px-2">
            <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
                <h4 class="fw-bold mb-0" style="color: #2c3e50;">All Jobs ({{ $jobPosts->total() }})</h4>
                @if(auth()->user()->isPartner() || auth()->user()->canAccess('jobpost_create'))
                <button class="btn btn-success rounded px-3 py-2 fw-bold" style="background-color: #198754; border-color: #198754;" wire:click="createJobPost">
                    Post a new job <i class="bi bi-chevron-down ms-1" style="font-size: 0.8rem;"></i>
                </button>
                @endif
            </div>

            <div class="d-flex gap-2 mb-4 overflow-auto pb-2">
                <button class="btn btn-outline-secondary rounded-pill btn-sm d-flex align-items-center gap-1 px-3 fw-medium"><i class="bi bi-funnel me-1"></i> All Filters</button>
                <button class="btn rounded-pill btn-sm d-flex align-items-center gap-1 px-3 fw-medium border shadow-sm bg-white text-dark" wire:click="$set('statusFilter', 'active')"><i class="bi bi-plus text-muted"></i> Active</button>
                <button class="btn rounded-pill btn-sm d-flex align-items-center gap-1 px-3 fw-medium border shadow-sm bg-white text-dark" wire:click="$set('statusFilter', 'draft')"><i class="bi bi-plus text-muted"></i> Under Review</button>
                <button class="btn rounded-pill btn-sm d-flex align-items-center gap-1 px-3 fw-medium border shadow-sm bg-white text-dark" wire:click="$set('statusFilter', 'closed')"><i class="bi bi-plus text-muted"></i> Expired</button>
                @if($search || $statusFilter)
                    <button class="btn rounded-pill btn-sm px-3 fw-medium border shadow-sm bg-white text-danger" wire:click="$set('statusFilter', ''); $set('search', '')">Clear</button>
                @endif
            </div>

            <div class="d-flex gap-3 mb-3">
                <div class="w-25">
                    <input type="text" wire:model.live="search" class="form-control rounded-pill shadow-sm border-0 bg-white" placeholder="Search jobs...">
                </div>
                
                @if(auth()->user()->isPartner() || auth()->user()->canAccess('jobpost_viewAny'))
                <div class="w-25">
                    <select wire:model.live="branch_id" class="form-select rounded-pill shadow-sm border-0 bg-white text-muted">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            @forelse($jobPosts as $job)
            <div class="card mb-2 shadow-sm border-0 rounded-3" style="border: 1px solid #eaeaea !important;">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <!-- Left Side -->
                        <div class="col-md-6 border-end">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <a href="{{ route('partner.hrms.job-posts.candidates', $job->job_code) }}" class="text-decoration-none">
                                    <h6 class="fw-bold mb-0 text-dark hover-primary" style="font-size: 1rem;">{{ $job->job_title }}</h6>
                                </a>
                                <span class="badge bg-{{ $job->status === 'active' ? 'success' : ($job->status === 'draft' ? 'warning' : 'danger') }}-subtle text-{{ $job->status === 'active' ? 'success' : ($job->status === 'draft' ? 'warning' : 'danger') }} border-0 px-2 py-0 rounded-pill fw-medium" style="font-size: 0.65rem;">
                                    {{ ucfirst($job->status) }}
                                </span>
                            </div>
                            <div class="text-muted mb-2" style="font-size: 0.75rem;">
                                {{ $job->job_city ?? 'Any Location' }} <span class="mx-1">|</span> <span class="text-dark fw-medium">{{ $job->branch->name ?? 'N/A' }}</span> <span class="mx-1">|</span> Posted on: {{ $job->created_at->format('d M Y') }} <span class="mx-1">|</span> <span class="text-dark">{{ $job->partner->name ?? 'Admin' }}</span>
                            </div>
                            
                            <div class="d-flex align-items-center bg-light rounded p-2 text-muted" style="width: fit-content; border: 1px solid #f0f0f0; font-size: 0.7rem;">
                                <i class="bi bi-info-circle me-1 text-secondary"></i>
                                @if($job->status === 'draft')
                                    Finish job posting to start receiving candidates
                                @elseif($job->status === 'closed')
                                    Repost now to receive new candidates
                                @else
                                    Job is active and receiving candidates. Expires on {{ $job->expires_at ? \Carbon\Carbon::parse($job->expires_at)->format('d M Y') : 'N/A' }}
                                @endif
                            </div>
                        </div>
                        
                        <!-- Middle Side (Stats) -->
                        <div class="col-md-3">
                            <div class="d-flex h-100 align-items-center justify-content-center gap-4">
                                <div class="text-center text-md-start">
                                    <a href="{{ route('partner.hrms.job-posts.candidates', $job->job_code) }}" class="text-decoration-none text-dark">
                                        <div class="fw-bold fs-6 d-flex align-items-center gap-2 hover-primary">
                                            {{ \App\Models\JobApplication::where('post_id', $job->id)->count() }} <i class="bi bi-chevron-right text-muted" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <div class="text-muted fw-medium" style="font-size: 0.75rem;">Applied to job</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Side (Actions) -->
                        <div class="col-md-3">
                            <div class="d-flex justify-content-md-end justify-content-center align-items-center gap-2 h-100 mt-3 mt-md-0">
                                @if($job->status === 'draft')
                                    <button class="btn btn-sm btn-outline-secondary rounded px-3 py-1 fw-medium text-dark border-1 shadow-sm" style="font-size: 0.75rem;" wire:click="editJobPost({{ $job->id }})">Finish posting</button>
                                @elseif($job->status === 'closed')
                                    <button class="btn btn-sm btn-outline-secondary rounded px-3 py-1 fw-medium text-dark border-1 shadow-sm" style="font-size: 0.75rem;" wire:click="editJobPost({{ $job->id }})">Repost now</button>
                                @else
                                    <a href="{{ route('partner.hrms.job-posts.candidates', $job->job_code) }}" class="btn btn-sm btn-outline-secondary rounded px-3 py-1 fw-medium text-dark border-1 shadow-sm" style="font-size: 0.75rem;">Manage</a>
                                @endif
                                
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary rounded px-2 border-1 shadow-sm text-dark" style="font-size: 0.75rem;" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.8rem;">
                                        <li><a class="dropdown-item py-1" href="#" wire:click.prevent="viewJobPostDetails({{ $job->id }})"><i class="bi bi-eye me-2 text-muted"></i> View</a></li>
                                        <li><a class="dropdown-item py-1" href="#" wire:click.prevent="editJobPost({{ $job->id }})"><i class="bi bi-pencil me-2 text-muted"></i> Edit</a></li>
                                        @if($job->status === 'active')
                                            <li><a class="dropdown-item text-warning py-1" href="#" wire:click.prevent="closeJobPost({{ $job->id }})"><i class="bi bi-x-circle me-2"></i> Close Job</a></li>
                                        @endif
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li><a class="dropdown-item text-danger py-1" href="#" wire:click.prevent="deleteJobPost({{ $job->id }})"><i class="bi bi-trash me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="card shadow-sm border-0 rounded-3 py-5 text-center">
                <i class="bi bi-briefcase text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="fw-bold text-dark">No job postings found</h5>
                <p class="text-muted">Create a new job posting to get started.</p>
                @if(auth()->user()->isPartner() || auth()->user()->canAccess('jobpost_create'))
                <button class="btn btn-success mt-3" wire:click="createJobPost">Post a Job</button>
                @endif
            </div>
            @endforelse

            <div class="mt-4">{{ $jobPosts->links() }}</div>
        </div>

    @elseif ($step === 1)
        <!-- Step 1: Select Template -->
        <div class="card card-custom">
            <div class="card-header d-flex justify-content-between align-items-center bg-white border-bottom-0 pt-4 pb-0">
                <h4 class="mb-0 fw-bold">Step 1: Choose a Job Template</h4>
                <button class="btn btn-outline-secondary btn-sm" wire:click="cancelWizard">Cancel</button>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Select a pre-defined template to fill out the details faster, or start from scratch.</p>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <!-- Blank Template -->
                    <div class="col">
                        <div class="card h-100 border-primary shadow-sm" style="cursor: pointer;" wire:click="selectTemplate(null)">
                            <div class="card-body text-center d-flex flex-column justify-content-center py-5">
                                <i class="bi bi-file-earmark-plus display-4 text-primary mb-3"></i>
                                <h5 class="card-title fw-bold">Start from Scratch</h5>
                                <p class="card-text text-muted small">Create a custom job post.</p>
                            </div>
                        </div>
                    </div>
                    @foreach($templates as $template)
                    <div class="col">
                        <div class="card h-100 shadow-sm" style="cursor: pointer;" wire:click="selectTemplate({{ $template->id }})">
                            <div class="card-body">
                                <h5 class="card-title fw-bold">{{ $template->title }}</h5>
                                <h6 class="card-subtitle mb-2 text-muted small"><i class="bi bi-tag"></i> {{ $template->category }}</h6>
                                <p class="card-text small text-truncate" style="max-height: 40px;">{{ $template->overview }}</p>
                            </div>
                            <div class="card-footer bg-white border-top-0 pt-0">
                                <span class="badge bg-light text-dark"><i class="bi bi-briefcase"></i> {{ $template->job_type }}</span>
                                @if($template->default_salary_min)
                                    <span class="badge bg-success-subtle text-success ms-1">₹{{ $template->default_salary_min }}-{{ $template->default_salary_max }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    @elseif ($step === 2)
        <!-- Step 2: Job Details -->
        <style>
            .apna-pill-radio input[type="radio"], .apna-pill-checkbox input[type="checkbox"] { display: none; }
            .apna-pill-radio label, .apna-pill-checkbox label { padding: 8px 16px; border: 1px solid #ced4da; border-radius: 20px; cursor: pointer; margin-right: 10px; margin-bottom: 10px; transition: all 0.2s; font-size: 14px; font-weight: 500; color: #495057; background: #fff;}
            .apna-pill-radio input[type="radio"]:checked + label, .apna-pill-checkbox input[type="checkbox"]:checked + label { border-color: #0d6efd; background-color: #f0f4ff; color: #0d6efd; }
            .wizard-step { height: 3px; background: #e9ecef; flex-grow: 1; margin: 0 10px; position: relative; top: 12px; }
            .wizard-step.active { background: #198754; }
            .step-circle { width: 28px; height: 28px; border-radius: 50%; background: #6c757d; color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; }
            .step-circle.active { background: #198754; }
        </style>
        <div class="card card-custom shadow-sm border-0">
            <div class="card-header bg-white p-4 border-bottom-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                <div class="d-flex align-items-center gap-3">
                    <h4 class="mb-0 fw-bold text-nowrap">Post a new job</h4>
                    <button class="btn btn-outline-secondary btn-sm" wire:click="cancelWizard">Cancel</button>
                </div>
                <div class="flex-grow-1" style="max-width: 450px;">
                    <div class="d-flex align-items-center justify-content-between px-3">
                        <div class="step-circle active">1</div><div class="wizard-step"></div>
                        <div class="step-circle">2</div><div class="wizard-step"></div>
                        <div class="step-circle">3</div><div class="wizard-step"></div>
                        <div class="step-circle">4</div>
                    </div>
                    <div class="d-flex justify-content-between px-2 mt-2 small fw-bold text-muted">
                        <span class="text-success">Job details</span><span>Requirements</span><span>Interviewer</span><span>Publish</span>
                    </div>
                </div>
            </div>
            <div class="card-body pt-4">
                <form wire:submit.prevent="nextStep">
                    <h5 class="fw-bold text-primary mb-1">Job details</h5>
                    <p class="small text-muted mb-4">We use this information to find the best candidates for the job.</p>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Job title / Designation <span class="text-danger">*</span></label>
                        <input type="text" wire:model.live.debounce.500ms="job_title" class="form-control" placeholder="Eg. Accountant" required>
                        @error('job_title') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Type of Job <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap apna-pill-radio">
                            <div>
                                <input type="radio" id="emp_ft" wire:model="employment_type" value="Full Time">
                                <label for="emp_ft">Full Time</label>
                            </div>
                            <div>
                                <input type="radio" id="emp_pt" wire:model="employment_type" value="Part Time">
                                <label for="emp_pt">Part Time</label>
                            </div>
                            <div>
                                <input type="radio" id="emp_both" wire:model="employment_type" value="Both">
                                <label for="emp_both">Both (Full-Time And Part-Time)</label>
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" wire:model="is_night_shift" id="nightShift">
                            <label class="form-check-label" for="nightShift">This is a night shift job</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-1 text-primary">Location</h6>
                        <span class="text-muted fw-normal small d-block mb-3">Let candidates know where they will be working from.</span>
                        <label class="form-label fw-bold small">Work location type <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap apna-pill-radio mb-3">
                            <div>
                                <input type="radio" id="loc_wfo" wire:model.live="work_location_type" value="Work From Office">
                                <label for="loc_wfo">Work From Office</label>
                            </div>
                            <div>
                                <input type="radio" id="loc_wfh" wire:model.live="work_location_type" value="Work From Home">
                                <label for="loc_wfh">Work From Home</label>
                            </div>
                            <div>
                                <input type="radio" id="loc_fj" wire:model.live="work_location_type" value="Field Job">
                                <label for="loc_fj">Field Job</label>
                            </div>
                        </div>
                        
                        @if($work_location_type === 'Work From Office')
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Office address / landmark <span class="text-danger">*</span></label>
                            <input type="text" wire:model="office_address" class="form-control" placeholder="Search for your address/locality">
                            <div class="mt-2">
                                <a href="#" class="text-decoration-none small text-primary">+ Add Floor / Plot no. / Shop no. (optional)</a>
                            </div>
                        </div>
                        @elseif($work_location_type === 'Work From Home')
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Job City <span class="text-danger">*</span></label>
                            <select wire:model="job_city" class="form-select">
                                <option value="">Select City</option>
                                <option value="Mumbai">Mumbai</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Bangalore">Bangalore</option>
                                <option value="Hyderabad">Hyderabad</option>
                                <option value="Chennai">Chennai</option>
                                <option value="Kolkata">Kolkata</option>
                                <option value="Pune">Pune</option>
                                <option value="Ahmedabad">Ahmedabad</option>
                                <!-- You can dynamically load cities here -->
                            </select>
                        </div>
                        @elseif($work_location_type === 'Field Job')
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Which area will the candidates be working in ? <span class="text-danger">*</span></label>
                            <input type="text" wire:model="working_area" class="form-control" placeholder="Search for your address/locality">
                        </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-1 text-primary">Compensation</h6>
                        <span class="text-muted fw-normal small d-block mb-3">Job postings with right salary & incentives will help you find the right candidates.</span>
                        <label class="form-label fw-bold small">What is the pay type? <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap apna-pill-radio mb-3">
                            <div>
                                <input type="radio" id="pay_f" wire:model.live="salary_type" value="Fixed Only">
                                <label for="pay_f">Fixed Only</label>
                            </div>
                            <div>
                                <input type="radio" id="pay_fi" wire:model.live="salary_type" value="Fixed + Incentive">
                                <label for="pay_fi">Fixed + Incentive</label>
                            </div>
                            <div>
                                <input type="radio" id="pay_i" wire:model.live="salary_type" value="Incentive Only">
                                <label for="pay_i">Incentive Only</label>
                            </div>
                        </div>

                        @if($salary_type === 'Fixed Only' || $salary_type === 'Fixed + Incentive')
                        <div class="row align-items-center mb-3">
                            <div class="col-md-{{ $salary_type === 'Fixed + Incentive' ? '7' : '12' }}">
                                <label class="form-label fw-bold small">
                                    Fixed salary / month {!! $salary_type === 'Fixed + Incentive' ? '<span class="text-muted fw-normal">(excluding incentives)</span>' : '' !!} <span class="text-danger">*</span>
                                </label>
                                <div class="d-flex align-items-center">
                                    <input type="number" wire:model="salary_min" class="form-control" placeholder="Minimum fixed salary" required>
                                    <div class="mx-2 bg-light px-3 py-2 rounded text-muted fw-bold border">to</div>
                                    <input type="number" wire:model="salary_max" class="form-control" placeholder="Maximum fixed salary" required>
                                </div>
                                @error('salary_min') <span class="text-danger small">{{ $message }}</span> @enderror
                                @error('salary_max') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            @if($salary_type === 'Fixed + Incentive')
                            <div class="col-md-1 text-center fw-bold fs-4 mt-3 text-dark">+</div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Average Incentive / month <span class="text-danger">*</span></label>
                                <input type="number" wire:model="average_incentive" class="form-control" placeholder="Eg. ₹2000" required>
                                @error('average_incentive') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            @endif
                        </div>
                        @endif
                        
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Number of Openings <span class="text-danger">*</span></label>
                                <input type="number" wire:model.live="vacancies_count" class="form-control" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Total Referral Budget (₹) <span class="text-danger">*</span></label>
                                <input type="number" wire:model.live="referral_budget" class="form-control" min="0" required>
                            </div>
                        </div>
                        <div class="bg-primary bg-opacity-10 text-primary p-3 rounded d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                            <span class="small">Estimated Referral Reward per Hire: <strong>₹{{ $vacancies_count > 0 ? number_format((float)$referral_budget / (int)$vacancies_count, 2) : '0' }}</strong></span>
                        </div>

                        <label class="form-label fw-bold small">Do you offer any additional perks?</label>
                        <div class="d-flex flex-wrap apna-pill-checkbox mb-4">
                            @foreach(['Flexible Working Hours', 'Weekly Payout', 'Joining Bonus', 'Health Insurance', 'Free Meals'] as $perk)
                            <div>
                                <input type="checkbox" id="perk_{{ Str::slug($perk) }}" wire:model="additional_perks" value="{{ $perk }}">
                                <label for="perk_{{ Str::slug($perk) }}">+ {{ $perk }}</label>
                            </div>
                            @endforeach
                        </div>

                        <label class="form-label fw-bold small">Is there any joining fee or deposit required from the candidate? <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap apna-pill-radio mb-3">
                            <div>
                                <input type="radio" id="fee_yes" wire:model="joining_fee_required" value="1">
                                <label for="fee_yes">Yes</label>
                            </div>
                            <div>
                                <input type="radio" id="fee_no" wire:model="joining_fee_required" value="0">
                                <label for="fee_no">No</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" class="btn btn-outline-secondary px-4 fw-bold" wire:click="goBack">Back</button>
                        <button type="submit" class="btn btn-success px-5 fw-bold">Continue</button>
                    </div>
                </form>
            </div>
        </div>

    @elseif ($step === 3)
        <!-- Step 3: Candidate Requirements -->
        <div class="card card-custom shadow-sm border-0">
            <div class="card-header bg-white p-4 border-bottom-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                <div class="d-flex align-items-center gap-3">
                    <h4 class="mb-0 fw-bold text-nowrap">Post a new job</h4>
                    <button class="btn btn-outline-secondary btn-sm" wire:click="cancelWizard">Cancel</button>
                </div>
                <div class="flex-grow-1" style="max-width: 450px;">
                    <div class="d-flex align-items-center justify-content-between px-3">
                        <div class="step-circle active"><i class="bi bi-check"></i></div><div class="wizard-step active"></div>
                        <div class="step-circle active">2</div><div class="wizard-step"></div>
                        <div class="step-circle">3</div><div class="wizard-step"></div>
                        <div class="step-circle">4</div>
                    </div>
                    <div class="d-flex justify-content-between px-2 mt-2 small fw-bold text-muted">
                        <span class="text-success">Job details</span><span class="text-success">Requirements</span><span>Interviewer</span><span>Publish</span>
                    </div>
                </div>
            </div>
            <div class="card-body pt-4">
                <form wire:submit.prevent="nextStep">
                    <h5 class="fw-bold text-primary mb-1">Basic Requirements</h5>
                    <p class="small text-muted mb-4">We'll use these requirement details to make your job visible to the right candidates.</p>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Minimum Education <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap apna-pill-radio">
                            @foreach(['10th or Below 10th', '12th Pass', 'Diploma', 'ITI', 'Graduate', 'Post Graduate'] as $edu)
                            <div>
                                <input type="radio" id="edu_{{ Str::slug($edu) }}" wire:model="minimum_education" value="{{ $edu }}">
                                <label for="edu_{{ Str::slug($edu) }}">{{ $edu }}</label>
                            </div>
                            @endforeach
                        </div>
                        @error('minimum_education') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">English level required <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap apna-pill-radio">
                            @foreach(['No English', 'Basic English', 'Good English'] as $eng)
                            <div>
                                <input type="radio" id="eng_{{ Str::slug($eng) }}" wire:model="english_level" value="{{ $eng }}">
                                <label for="eng_{{ Str::slug($eng) }}">{{ $eng }}</label>
                            </div>
                            @endforeach
                        </div>
                        @error('english_level') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Total experience required <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap apna-pill-radio mb-3">
                            <div>
                                <input type="radio" id="exp_any" wire:model.live="experience_type" value="Any">
                                <label for="exp_any">Any</label>
                            </div>
                            <div>
                                <input type="radio" id="exp_exp" wire:model.live="experience_type" value="Experienced Only">
                                <label for="exp_exp">Experienced Only</label>
                            </div>
                            <div>
                                <input type="radio" id="exp_fresh" wire:model.live="experience_type" value="Fresher Only">
                                <label for="exp_fresh">Fresher Only</label>
                            </div>
                        </div>
                        @if($experience_type === 'Experienced Only')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Min Years <span class="text-danger">*</span></label>
                                <input type="number" wire:model="min_experience_years" class="form-control" placeholder="e.g. 1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Max Years <span class="text-danger">*</span></label>
                                <input type="number" wire:model="max_experience_years" class="form-control" placeholder="e.g. 3" required>
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Gender Preference</label>
                        <div class="d-flex flex-wrap apna-pill-radio mb-3">
                            <div>
                                <input type="radio" id="gender_any" wire:model="gender_preference" value="">
                                <label for="gender_any">Any</label>
                            </div>
                            <div>
                                <input type="radio" id="gender_male" wire:model="gender_preference" value="Male">
                                <label for="gender_male">Male</label>
                            </div>
                            <div>
                                <input type="radio" id="gender_female" wire:model="gender_preference" value="Female">
                                <label for="gender_female">Female</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="fw-bold mb-3">Job Description</h5>
                    <div class="mb-4">
                        <div wire:ignore x-data="{
                            init() {
                                const loadEditor = () => {
                                    let quill = new Quill(this.$refs.editor, { theme: 'snow' });
                                    quill.on('text-change', () => {
                                        this.$wire.set('job_description', quill.root.innerHTML);
                                    });
                                };
                                
                                if (typeof Quill === 'undefined') {
                                    let link = document.createElement('link');
                                    link.rel = 'stylesheet';
                                    link.href = 'https://cdn.quilljs.com/1.3.6/quill.snow.css';
                                    document.head.appendChild(link);
                                    
                                    let script = document.createElement('script');
                                    script.src = 'https://cdn.quilljs.com/1.3.6/quill.js';
                                    script.onload = loadEditor;
                                    document.head.appendChild(script);
                                } else {
                                    loadEditor();
                                }
                            }
                        }">
                            <div x-ref="editor" style="height: 250px; background: white;">{!! $job_description !!}</div>
                        </div>
                        <textarea wire:model="job_description" id="hidden-job-description" class="d-none"></textarea>
                        @error('job_description') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" class="btn btn-outline-secondary px-4 fw-bold" wire:click="goBack">Back</button>
                        <button type="submit" class="btn btn-success px-5 fw-bold">Continue</button>
                    </div>
                </form>
            </div>
        </div>

    @elseif ($step === 4)
        <!-- Step 4: Interviewer Information -->
        <div class="card card-custom shadow-sm border-0">
            <div class="card-header bg-white p-4 border-bottom-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                <div class="d-flex align-items-center gap-3">
                    <h4 class="mb-0 fw-bold text-nowrap">Post a new job</h4>
                    <button class="btn btn-outline-secondary btn-sm" wire:click="cancelWizard">Cancel</button>
                </div>
                <div class="flex-grow-1" style="max-width: 450px;">
                    <div class="d-flex align-items-center justify-content-between px-3">
                        <div class="step-circle active"><i class="bi bi-check"></i></div><div class="wizard-step active"></div>
                        <div class="step-circle active"><i class="bi bi-check"></i></div><div class="wizard-step active"></div>
                        <div class="step-circle active">3</div><div class="wizard-step"></div>
                        <div class="step-circle">4</div>
                    </div>
                    <div class="d-flex justify-content-between px-2 mt-2 small fw-bold text-muted">
                        <span class="text-success">Job details</span><span class="text-success">Requirements</span><span class="text-success">Interviewer</span><span>Publish</span>
                    </div>
                </div>
            </div>
            <div class="card-body pt-4">
                <form wire:submit.prevent="nextStep">
                    <h5 class="fw-bold text-primary mb-1">Interview method and address</h5>
                    <p class="small text-muted mb-4">Let candidates know how interviews will be conducted for this job.</p>

                    <div class="mb-5">
                        <label class="form-label fw-bold small">Is this a walk-in interview? <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap apna-pill-radio mt-2">
                            <div>
                                <input type="radio" wire:model="is_walk_in" value="1" id="walkin_yes">
                                <label for="walkin_yes">Yes</label>
                            </div>
                            <div>
                                <input type="radio" wire:model="is_walk_in" value="0" id="walkin_no">
                                <label for="walkin_no">No</label>
                            </div>
                        </div>
                    </div>

                    <h5 class="fw-bold text-primary mb-1 mt-5">Communication Preferences</h5>
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Do you want candidates to contact you via Call / Whatsapp after they apply? <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column apna-pill-radio mt-2">
                            <div>
                                <input type="radio" wire:model="contact_preference" value="Yes, to myself" id="contact_1">
                                <label for="contact_1" class="w-100 mb-2">Yes, to myself</label>
                            </div>
                            <div>
                                <input type="radio" wire:model="contact_preference" value="Yes, to other recruiter" id="contact_2">
                                <label for="contact_2" class="w-100 mb-2">Yes, to other recruiter</label>
                            </div>
                            <div>
                                <input type="radio" wire:model="contact_preference" value="No, I will contact candidates first" id="contact_3">
                                <label for="contact_3" class="w-100 mb-2">No, I will contact candidates first</label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" class="btn btn-outline-secondary px-4 fw-bold" wire:click="goBack">Back</button>
                        <button type="submit" class="btn btn-success px-5 fw-bold">Continue</button>
                    </div>
                </form>
            </div>
        </div>

    @elseif ($step === 5)
        <!-- Step 5: Choose Plan & Publish -->
        <div class="card card-custom shadow-sm border-0">
            <div class="card-header bg-white p-4 border-bottom-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                <div class="d-flex align-items-center gap-3">
                    <h4 class="mb-0 fw-bold text-nowrap">Select Plan & Publish</h4>
                    <button class="btn btn-outline-secondary btn-sm" wire:click="cancelWizard">Cancel</button>
                </div>
                <div class="flex-grow-1" style="max-width: 450px;">
                    <div class="d-flex align-items-center justify-content-between px-3">
                        <div class="step-circle active"><i class="bi bi-check"></i></div><div class="wizard-step active"></div>
                        <div class="step-circle active"><i class="bi bi-check"></i></div><div class="wizard-step active"></div>
                        <div class="step-circle active"><i class="bi bi-check"></i></div><div class="wizard-step active"></div>
                        <div class="step-circle active">4</div>
                    </div>
                    <div class="d-flex justify-content-between px-2 mt-2 small fw-bold text-muted">
                        <span class="text-success">Job details</span><span class="text-success">Requirements</span><span class="text-success">Interviewer</span><span class="text-success">Publish</span>
                    </div>
                </div>
            </div>
            <div class="card-body pt-4">
                <p class="text-muted text-center mb-4">Choose a visibility plan for your job post.</p>
                @if(session()->has('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
                    @foreach($plans as $plan)
                    <div class="col">
                        <div class="card h-100 {{ $job_plan_id == $plan->id ? 'border-success border-2 shadow' : 'border' }}" style="cursor: pointer; transition: all 0.3s;" wire:click="selectPlan({{ $plan->id }})">
                            @if($plan->is_featured)
                                <span class="badge bg-warning text-dark position-absolute top-0 start-50 translate-middle shadow-sm px-3 py-2 rounded-pill">Recommended</span>
                            @endif
                            <div class="card-body text-center p-4">
                                <h4 class="card-title fw-bold text-dark">{{ $plan->name }}</h4>
                                <h2 class="display-5 fw-bold text-success my-3">₹{{ number_format($plan->price) }}</h2>
                                <ul class="list-unstyled text-start mt-4 mb-0 text-muted">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> {{ $plan->validity_days }} Days Visibility</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i> {{ $plan->job_limit ? $plan->job_limit . ' Jobs Limit' : 'Unlimited Jobs' }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($job_plan_id)
                @php 
                    $selectedPlan = $plans->firstWhere('id', $job_plan_id);
                    $total = $selectedPlan->price + $referral_budget;
                    $wBalance = (float)($walletBalance ?? 0);
                    $wDeducted = min($wBalance, $total);
                    $payable = $total - $wDeducted;
                @endphp
                <div class="bg-light p-4 rounded-4 border">
                    <h5 class="fw-bold mb-3 text-dark">Order Summary</h5>
                    <div class="d-flex justify-content-between mb-2 text-muted">
                        <span>Job Plan: <strong>{{ $selectedPlan->name }}</strong></span>
                        <span>₹{{ number_format($selectedPlan->price, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-muted">
                        <span>Referral Budget Deposit</span>
                        <span>₹{{ number_format($referral_budget, 2) }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2 fw-bold text-dark">
                        <span>Total Cost</span>
                        <span>₹{{ number_format($total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Wallet Deduction (Bal: ₹{{ number_format($wBalance, 2) }})</span>
                        <span>- ₹{{ number_format($wDeducted, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-0 fw-bold fs-4 text-success mt-3">
                        <span>Total Payable</span>
                        <span>₹{{ number_format($payable, 2) }}</span>
                    </div>
                </div>
                @endif

                <div class="d-flex justify-content-between mt-5">
                    <button type="button" class="btn btn-outline-secondary px-4 fw-bold" wire:click="goBack">Back</button>
                    <button type="button" class="btn btn-success px-5 fw-bold" wire:click="publishJob" {{ !$job_plan_id ? 'disabled' : '' }}>
                        <i class="bi bi-rocket-takeoff me-2"></i> Publish Job
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- View Modal (Only on step 0) -->
    @if($isViewModalOpen && $viewJobPost && $step === 0)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <!-- Header -->
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 position-relative">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <h4 class="modal-title fw-bold text-dark mb-1">{{ $viewJobPost->job_title }}</h4>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-{{ $viewJobPost->status === 'active' ? 'success' : ($viewJobPost->status === 'draft' ? 'warning' : 'danger') }}-subtle text-{{ $viewJobPost->status === 'active' ? 'success' : ($viewJobPost->status === 'draft' ? 'warning' : 'danger') }} border-0 px-3 py-1 rounded-pill fw-medium">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i> {{ ucfirst($viewJobPost->status) }}
                                </span>
                                <span class="text-muted small fw-medium"><i class="bi bi-hash"></i>{{ $viewJobPost->job_code }}</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close shadow-none" wire:click="closeModals" style="background-color: #f1f3f5; border-radius: 50%; padding: 0.6rem;"></button>
                    </div>
                </div>

                <!-- Body -->
                <div class="modal-body p-4 custom-scrollbar">
                    
                    <!-- Top Highlights -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-3 h-100 border border-light">
                                <div class="text-muted small mb-1 fw-medium"><i class="bi bi-wallet2 text-success me-1"></i> Salary Range</div>
                                <div class="fw-bold text-dark fs-5">₹{{ number_format($viewJobPost->salary_min) }} - ₹{{ number_format($viewJobPost->salary_max) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-3 h-100 border border-light">
                                <div class="text-muted small mb-1 fw-medium"><i class="bi bi-people text-primary me-1"></i> Vacancies</div>
                                <div class="fw-bold text-dark fs-5">{{ $viewJobPost->vacancies_count }} Openings</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light rounded-3 p-3 h-100 border border-light position-relative overflow-hidden">
                                <div class="position-absolute end-0 bottom-0 opacity-10" style="transform: translate(20%, 20%);"><i class="bi bi-gift-fill text-warning" style="font-size: 4rem;"></i></div>
                                <div class="text-muted small mb-1 fw-medium"><i class="bi bi-gift text-warning me-1"></i> Referral Bonus</div>
                                <div class="fw-bold text-dark fs-5">₹{{ number_format($viewJobPost->referral_budget, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Basic Details</h6>
                            <ul class="list-group list-group-flush border-top border-bottom rounded-0">
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-building me-2"></i>Branch</span>
                                    <span class="fw-medium text-dark text-end">{{ $viewJobPost->branch->name ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-diagram-3 me-2"></i>Department</span>
                                    <span class="fw-medium text-dark text-end">{{ $viewJobPost->department->name ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-geo-alt me-2"></i>Location</span>
                                    <span class="fw-medium text-dark text-end">{{ $viewJobPost->job_city ?? 'Any' }} <span class="badge bg-secondary-subtle text-secondary border-0 ms-1">{{ $viewJobPost->is_work_from_home ? 'WFH' : 'Office' }}</span></span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-pin-map me-2"></i>Radius</span>
                                    <span class="fw-medium text-dark text-end">{{ $viewJobPost->distance ?? 'Any' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-briefcase me-2"></i>Employment</span>
                                    <span class="fw-medium text-dark text-end">{{ $viewJobPost->employment_type }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-moon-stars me-2"></i>Shift</span>
                                    <span class="fw-medium text-dark text-end">{{ $viewJobPost->job_type ?? 'Day Shift' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-cash-stack me-2"></i>Pay Type</span>
                                    <span class="fw-medium text-dark text-end">{{ $viewJobPost->salary_type ?? 'Fixed' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-box-arrow-in-right me-2"></i>Joining Fee</span>
                                    <span class="fw-medium text-dark text-end">{{ $viewJobPost->joining_fee_required ? 'Yes' : 'No' }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-check text-primary me-2"></i>Requirements</h6>
                            <ul class="list-group list-group-flush border-top border-bottom rounded-0">
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-mortarboard me-2"></i>Education</span>
                                    <span class="fw-medium text-dark text-end" style="max-width: 60%;">{{ $viewJobPost->minimum_education ?? 'Any' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-clock-history me-2"></i>Experience</span>
                                    <span class="fw-medium text-dark text-end">
                                        {{ (!$viewJobPost->min_experience_years && !$viewJobPost->max_experience_years) ? 'Any' : ($viewJobPost->min_experience_years ?? 0) . ' - ' . ($viewJobPost->max_experience_years ?? 'Any') . ' Yrs' }}
                                    </span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <span class="text-muted small"><i class="bi bi-calendar-event me-2"></i>Age Limit</span>
                                    <span class="fw-medium text-dark text-end">
                                        {{ (!$viewJobPost->min_age && !$viewJobPost->max_age) ? 'Any' : ($viewJobPost->min_age ?? 18) . ' - ' . ($viewJobPost->max_age ?? 'Any') . ' Yrs' }}
                                    </span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted small"><i class="bi bi-gender-ambiguous me-2"></i>Gender</span>
                                    <span class="fw-medium text-dark text-end">{{ $viewJobPost->gender_preference ?? 'Any' }}</span>
                                </li>
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center border-bottom-0">
                                    <span class="text-muted small"><i class="bi bi-translate me-2"></i>English Level</span>
                                    <span class="fw-medium text-dark text-end">{{ $viewJobPost->english_level ?? 'Any' }}</span>
                                </li>
                                
                                <li class="list-group-item bg-transparent px-0 py-2 border-top">
                                    @php
                                        $interviewInfo = is_string($viewJobPost->interview_information) ? json_decode($viewJobPost->interview_information, true) : $viewJobPost->interview_information;
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small"><i class="bi bi-headset me-2"></i>Interview Contact</span>
                                        <span class="fw-medium text-dark text-end">{{ $interviewInfo['contact_preference'] ?? 'N/A' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="text-muted small"><i class="bi bi-door-open me-2"></i>Walk-in</span>
                                        <span class="fw-medium text-dark text-end">{{ isset($interviewInfo['is_walk_in']) && $interviewInfo['is_walk_in'] ? 'Yes' : 'No' }}</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Additional Perks -->
                    @php
                        $perks = is_string($viewJobPost->additional_perks) ? json_decode($viewJobPost->additional_perks, true) : $viewJobPost->additional_perks;
                    @endphp
                    @if(!empty($perks))
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-gift text-primary me-2"></i>Additional Perks</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($perks as $perk)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-medium">{{ $perk }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Skills -->
                    @if(!empty($viewJobPost->skills))
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-stars text-primary me-2"></i>Required Skills</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(explode(',', $viewJobPost->skills) as $skill)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-medium">{{ trim($skill) }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Screening Questions -->
                    @php
                        $sqData = $viewJobPost->screening_questions;
                        $sqArray = is_string($sqData) ? json_decode($sqData, true) : (is_array($sqData) ? $sqData : []);
                        $questionsList = $sqArray['questions'] ?? (isset($sqArray[0]) ? $sqArray : []);
                        $answersList = $sqArray['answers'] ?? [];
                    @endphp
                    @if(!empty($questionsList))
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-question-circle text-primary me-2"></i>Screening Questions</h6>
                        <ul class="list-group list-group-flush border-top border-bottom rounded-0">
                            @foreach($questionsList as $idx => $q)
                                @php
                                    $key = $q['id'] ?? $q['question'] ?? $idx;
                                    $ans = $answersList[$key] ?? $q['partner_answer'] ?? '';
                                    if(empty($ans)) $ans = 'N/A';
                                @endphp
                                <li class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center">
                                    <div class="fw-medium text-dark small">{{ $q['question'] ?? 'Question '.($idx+1) }} {!! !empty($q['required']) ? '<span class="text-danger">*</span>' : '' !!}</div>
                                    <div class="text-muted small text-end">
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
                    <div>
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-card-text text-primary me-2"></i>Job Description</h6>
                        <div class="p-3 bg-light rounded-3 text-dark" style="font-size: 0.9rem; line-height: 1.6; border: 1px solid #f0f0f0;">{!! $viewJobPost->job_description ?? 'No description provided.' !!}</div>
                    </div>

                </div>
                
                <!-- Footer -->
                <div class="modal-footer border-top-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-medium border" wire:click="closeModals">Close</button>
                    @if($viewJobPost->status === 'active')
                        <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-bold ms-auto" wire:click="closeJobPost({{ $viewJobPost->id }}); closeModals();">
                            <i class="bi bi-x-circle me-1"></i> Close Job
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

