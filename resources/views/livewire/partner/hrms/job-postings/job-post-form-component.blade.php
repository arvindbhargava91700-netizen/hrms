<div>
    @if ($step === 0)
        <!-- List View -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-white border-bottom p-2 p-md-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">Job Postings</h5>
                @if(auth()->user()->isPartner() || auth()->user()->canAccess('jobpost_create'))
                <button class="btn btn-primary px-4 py-2 rounded-pill fw-bold" wire:click="createJobPost">
                    <i class="bi bi-plus-lg me-1"></i> Post a Job
                </button>
                @endif
            </div>
            <div class="card-body p-0">
                @if(session()->has('success'))
                    <div class="alert alert-success m-3 rounded-3 border-0 bg-success-subtle text-success fw-semibold"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
                @endif
                <div class="p-3 bg-light d-flex gap-3 border-bottom">
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" wire:model.live="search" class="form-control border-start-0 ps-0" placeholder="Search jobs by title or code...">
                    </div>
                    <select wire:model.live="statusFilter" class="form-select w-auto text-muted">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small fw-semibold text-uppercase">
                            <tr>
                                <th class="ps-4 py-3">Job Title</th>
                                <th>Positions</th>
                                <th>Budget</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($jobPosts as $job)
                                <tr>
                                    <td class="ps-4 py-3">
                                        <div class="fw-bold text-dark">{{ $job->job_title }}</div>
                                        <div class="small text-muted">{{ $job->job_code }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark px-3 py-2 rounded-pill">{{ $job->vacancies_count }}</span></td>
                                    <td><span class="fw-semibold">&#8377;{{ number_format($job->referral_budget) }}</span></td>
                                    <td class="text-muted">{{ $job->expires_at ? \Carbon\Carbon::parse($job->expires_at)->format('d M, Y') : 'N/A' }}</td>
                                    <td>
                                        <span class="badge rounded-pill bg-{{ $job->status === 'active' ? 'success' : ($job->status === 'draft' ? 'secondary' : 'danger') }}-subtle text-{{ $job->status === 'active' ? 'success' : ($job->status === 'draft' ? 'secondary' : 'danger') }} px-3 py-2">
                                            <i class="bi bi-circle-fill small me-1" style="font-size: 8px;"></i> {{ ucfirst($job->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-light text-primary me-1 rounded-circle" wire:click="viewJobPostDetails({{ $job->id }})"><i class="bi bi-eye"></i></button>
                                        <button class="btn btn-sm btn-light text-primary me-1 rounded-circle" wire:click="editJobPost({{ $job->id }})"><i class="bi bi-pencil"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted mb-3"><i class="bi bi-folder2-open display-4"></i></div>
                                        <h5 class="text-dark fw-bold">No job postings found</h5>
                                        <p class="text-muted mb-0">You haven't created any job postings yet.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">{{ $jobPosts->links() }}</div>
            </div>
        </div>

    @else
        <!-- Wizard Form Wrapper -->
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
            
            /* Premium Wizard Stepper Styling */
            .wizard-container { max-width: 1000px; margin: 0 auto; font-family: 'Inter', sans-serif; padding-top: 15px; }
            .stepper-wrapper { display: flex; align-items: center; justify-content: space-between; position: relative; margin-bottom: 25px; padding: 0 10px; }
            .stepper-line { position: absolute; top: 50%; left: 20px; right: 20px; height: 2px; background: #ced4da; z-index: 1; transform: translateY(-50%); }
            .stepper-item { position: relative; z-index: 2; display: flex; align-items: center; justify-content: center; background: #f8f9fa; padding: 0 15px; }
            .step-circle { width: 22px; height: 22px; border-radius: 50%; background: #ced4da; color: white; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; transition: all 0.3s; }
            .step-circle.active { background: #4a3b5c; color: white; }
            .step-title { margin: 0 0 0 10px; font-weight: 600; font-size: 13px; color: #212529; }
            
            /* Premium Pill Radio Styling */
            .pill-radio { display: none; }
            .pill-label { display: inline-flex; align-items: center; padding: 6px 16px; border: 1px solid #ced4da; border-radius: 50px; cursor: pointer; margin-right: 10px; margin-bottom: 8px; color: #495057; background: white; font-size: 13px; font-weight: 500; transition: all 0.2s ease; }
            .pill-label:hover { border-color: #adb5bd; }
            .pill-radio:checked + .pill-label { border-color: #0d6efd; color: #0d6efd; background-color: white; font-weight: 600; box-shadow: 0 0 0 1px #0d6efd; }
            
            /* Form Input Styling */
            .premium-input { border: 1px solid #ced4da; border-radius: 4px; padding: 8px 12px; font-size: 13px; box-shadow: none !important; }
            .premium-input:focus { border-color: #86b7fe; outline: 0; box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25) !important; }
            
            .section-title { font-size: 0.95rem; font-weight: 600; color: #212529; margin-bottom: 0.15rem; }
            .section-desc { font-size: 0.75rem; color: #6c757d; margin-bottom: 1rem; }
            
            /* Clean Card */
            .form-card { background: white; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #e9ecef; overflow: hidden; padding: 25px; }
        </style>

        <div class="wizard-container">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0 text-dark">Post a new job</h4>
                <button class="btn btn-sm btn-white border shadow-sm fw-semibold text-dark rounded d-flex align-items-center" wire:click="cancelWizard">
                    <i class="bi bi-layout-text-window-reverse me-2 text-primary"></i> Use Templates
                </button>
            </div>
            
            <!-- Dynamic Stepper UI (Outside Card) -->
            <div class="stepper-wrapper">
                <div class="stepper-line"></div>
                <div class="stepper-item">
                    <div class="step-circle {{ $step >= 1 ? 'active' : '' }}">1</div>
                    <div class="step-title">Job details</div>
                </div>
                <div class="stepper-item">
                    <div class="step-circle {{ $step >= 2 ? 'active' : '' }}">2</div>
                </div>
                <div class="stepper-item">
                    <div class="step-circle {{ $step >= 3 ? 'active' : '' }}">3</div>
                </div>
                <div class="stepper-item">
                    <div class="step-circle {{ $step >= 4 ? 'active' : '' }}">4</div>
                </div>
            </div>

            <div class="form-card mb-4">
                    
                    @if ($step === 1)
                        <!-- Step 1: Select Template -->
                        <div class="text-center mb-3 mt-2">
                            <h4 class="fw-bold text-dark">Choose a Job Template</h4>
                            <p class="text-muted small">Select a pre-defined template to fill out details faster.</p>
                        </div>
                        
                        <div class="row row-cols-1 row-cols-md-2 g-3">
                            <!-- Blank Template -->
                            <div class="col">
                                <div class="card h-100 border-2 border-primary border-dashed rounded-4" style="cursor: pointer; border-style: dashed; transition: all 0.2s;" wire:click="selectTemplate(null)" onmouseover="this.style.backgroundColor='#f8fbff'" onmouseout="this.style.backgroundColor='transparent'">
                                    <div class="card-body text-center d-flex flex-column justify-content-center py-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mx-auto mb-2 text-primary">
                                            <i class="bi bi-plus-lg fs-5"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark">Start from Scratch</h6>
                                        <p class="text-muted small mb-0">Create a fully custom job post</p>
                                    </div>
                                </div>
                            </div>
                            @foreach($templates as $template)
                            <div class="col">
                                <div class="card h-100 border border-light shadow-sm rounded-4" style="cursor: pointer; transition: all 0.2s;" wire:click="selectTemplate({{ $template->id }})" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='#0d6efd'; this.style.boxShadow='0 8px 16px rgba(13,110,253,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='#f8f9fa'; this.style.boxShadow='0 .125rem .25rem rgba(0,0,0,.075)'">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold text-dark mb-0">{{ $template->title }}</h6>
                                            <span class="badge bg-light text-muted border rounded-pill px-2" style="font-size:10px;">{{ $template->category }}</span>
                                        </div>
                                        <p class="text-muted small mb-3" style="font-size:12px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $template->overview }}</p>
                                        
                                        <div class="d-flex align-items-center gap-2 mt-auto">
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1" style="font-size:11px;"><i class="bi bi-briefcase me-1"></i> {{ $template->job_type }}</span>
                                            @if($template->default_salary_min)
                                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size:11px;">&#8377;{{ number_format($template->default_salary_min) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                    @elseif ($step === 2)
                        <!-- Step 2: Job Details -->
                        <form wire:submit.prevent="nextStep" wire:key="form-step-{{ $step }}">
                            <div class="mb-4">
                                <h5 class="fw-bold text-dark mb-1" style="font-size: 15px;">Job details</h5>
                                <p class="text-muted small mb-0">We use this information to find the best candidates for the job.</p>
                                <p class="text-danger small mb-3">*Marked fields are mandatory</p>
                            </div>

                            <div class="mb-4 position-relative">
                                <label class="form-label fw-semibold text-dark small mb-1">Company you're hiring for <span class="text-danger">*</span></label>
                                <select wire:model="branch_id" class="form-select premium-input pe-5" required @if(!auth()->user()->isPartner() && auth()->user()->branch_id) disabled @endif>
                                    <option value="">Select Company</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <a href="#" class="position-absolute text-primary text-decoration-none small fw-medium" style="right: 15px; bottom: 8px; background: white; padding-left: 5px;">Change</a>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark small mb-1">Job title / Designation <span class="text-danger">*</span></label>
                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 gap-md-3">
                                    <div class="position-relative" style="max-width: 400px; flex: 1;" x-data="{ open: false }" @click.outside="open = false">
                                        <input type="text" wire:model.live.debounce.300ms="job_title" 
                                               @focus="open = true" @input="open = true"
                                               class="form-control premium-input pe-4" required placeholder="Eg. Telecom Engineer"
                                               autocomplete="off">
                                        @if($job_title)
                                            <i class="bi bi-x position-absolute top-50 end-0 translate-middle-y me-2 text-muted" style="cursor: pointer; font-size: 1.3rem; z-index: 5;" wire:click="$set('job_title', '')"></i>
                                        @endif
                                        
                                        <!-- Autocomplete Dropdown -->
                                        @if(!empty($jobTitleSuggestions))
                                            <div x-show="open" 
                                                 class="dropdown-menu show w-100 position-absolute shadow-sm border-0" 
                                                 style="max-height: 250px; overflow-y: auto; top: 100%; z-index: 1000; border-radius: 6px;"
                                                 x-transition.opacity>
                                                @foreach($jobTitleSuggestions as $item)
                                                    <a href="#" class="dropdown-item py-2 px-3 small text-dark" 
                                                       wire:click.prevent="selectJobTitle('{{ addslashes($item['title']) }}', '{{ $item['department_id'] }}')"
                                                       @click="open = false">
                                                        <span class="fw-medium">{{ $item['title'] }}</span>
                                                        @if($item['department_id'])
                                                            <div class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-tag me-1"></i>Auto-selects Category</div>
                                                        @endif
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <div class="text-primary small fw-medium" style="white-space: nowrap;">
                                        <i class="bi bi-info-circle-fill me-1"></i> Only similar job title edits are allowed after publishing
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark small mb-1">Job Role / Category <span class="text-danger">*</span></label>
                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-2 gap-md-3">
                                    <div style="max-width: 400px; flex: 1;">
                                        <select wire:model="department_id" class="form-select premium-input" required>
                                            <option value="">Select Role/Category</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="text-primary small fw-medium" style="white-space: nowrap;">
                                        <i class="bi bi-info-circle-fill me-1"></i> Job Role / Category can't be modified after publishing.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark d-block mb-2 small">Type of Job <span class="text-danger">*</span></label>
                                <div class="d-flex flex-wrap">
                                    <input type="radio" wire:model="employment_type" value="Full Time" class="pill-radio" id="emp_full">
                                    <label class="pill-label" for="emp_full">Full Time</label>
                                    
                                    <input type="radio" wire:model="employment_type" value="Part Time" class="pill-radio" id="emp_part">
                                    <label class="pill-label" for="emp_part">Part Time</label>
                                    
                                    <input type="radio" wire:model="employment_type" value="Both" class="pill-radio" id="emp_both">
                                    <label class="pill-label" for="emp_both">Both (Full-Time And Part-Time)</label>
                                </div>
                                <div class="mt-2">
                                    <div class="form-check custom-checkbox">
                                        <input class="form-check-input" type="checkbox" wire:model="is_night_shift" id="is_night_shift_check" value="1">
                                        <label class="form-check-label text-dark small fw-medium" for="is_night_shift_check" style="margin-top: 1px;">
                                            This is a night shift job
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 border-light" style="opacity: 0.5;">
                            
                            <div class="mt-2 mb-4">
                                <h5 class="fw-bold text-dark mb-1" style="font-size: 14px;">Location</h5>
                                <p class="text-muted small mb-0">Let candidates know where they will be working from.</p>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-dark d-block mb-2 small">Work location type <span class="text-danger">*</span></label>
                                <div class="d-flex flex-wrap">
                                    @foreach(['Work From Office', 'Work From Home', 'Field Job'] as $opt)
                                        <input type="radio" wire:model.live="work_location_type" value="{{ $opt }}" class="pill-radio" id="loc_{{ Str::slug($opt) }}" wire:key="radio-{{ Str::slug($opt) }}">
                                        <label class="pill-label" for="loc_{{ Str::slug($opt) }}">
                                            {{ $opt }}
                                            @if($opt === 'Field Job')
                                                <i class="bi bi-info-circle ms-1 text-muted" style="font-size: 0.85em;" title="Candidates will travel to various locations."></i>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            @if($work_location_type === 'Work From Office')
                                <div wire:key="loc-office" class="mb-3" x-data="{
                                    showDetails: {{ $office_address_details ? 'true' : 'false' }},
                                    hasLocation: {{ $office_lat ? 'true' : 'false' }},
                                    officeLat: @entangle('office_lat').live,
                                    officeLng: @entangle('office_lng').live,
                                    
                                    initAutocomplete() {
                                        window._initOfficeAC = () => {
                                            const input = document.getElementById('officeAddressInput');
                                            if (!input || input._acInited) return;
                                            input._acInited = true;
                                            const ac = new google.maps.places.Autocomplete(input, { 
                                                componentRestrictions: { country: 'in' }
                                            });
                                            ac.addListener('place_changed', () => {
                                                const place = ac.getPlace();
                                                if (place && place.geometry) {
                                                    @this.set('office_address', input.value);
                                                    @this.set('office_lat', place.geometry.location.lat());
                                                    @this.set('office_lng', place.geometry.location.lng());
                                                    
                                                    let city = '';
                                                    if(place.address_components) {
                                                        for(let c of place.address_components) {
                                                            if(c.types.includes('locality')) city = c.long_name;
                                                        }
                                                    }
                                                    @this.set('office_city', city);
                                                    
                                                    this.hasLocation = true;
                                                    setTimeout(() => {
                                                        this.initMap(place.geometry.location.lat(), place.geometry.location.lng());
                                                    }, 300);
                                                }
                                            });
                                            input.addEventListener('keydown', function(e) {
                                                if (e.key === 'Enter') e.preventDefault();
                                            });
                                            
                                            // Initialize map on load if editing
                                            if (this.hasLocation && this.officeLat && this.officeLng) {
                                                setTimeout(() => {
                                                    this.initMap(this.officeLat, this.officeLng);
                                                }, 300);
                                            }
                                        };
                                        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                                            window._initOfficeAC();
                                        } else if (!document.getElementById('gmap-places-script')) {
                                            let s = document.createElement('script');
                                            s.id = 'gmap-places-script';
                                            s.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=_initOfficeAC';
                                            s.async = true;
                                            document.head.appendChild(s);
                                        }
                                    },
                                    initMap(lat, lng) {
                                        const mapEl = document.getElementById('officeMap');
                                        if(!mapEl) return;
                                        const pos = { lat: parseFloat(lat), lng: parseFloat(lng) };
                                        
                                        if (!this.mapInstance) {
                                            this.mapInstance = new google.maps.Map(mapEl, {
                                                zoom: 15,
                                                center: pos,
                                                mapTypeControl: false,
                                            });
                                            this.markerInstance = new google.maps.Marker({
                                                position: pos,
                                                map: this.mapInstance,
                                                draggable: true
                                            });
                                            
                                            const updateLocation = () => {
                                                const newPos = this.markerInstance.getPosition();
                                                const newLat = newPos.lat();
                                                const newLng = newPos.lng();
                                                
                                                @this.set('office_lat', newLat);
                                                @this.set('office_lng', newLng);
                                                
                                                const geocoder = new google.maps.Geocoder();
                                                geocoder.geocode({ location: { lat: newLat, lng: newLng } }, (results, status) => {
                                                    if (status === 'OK' && results[0]) {
                                                        const place = results[0];
                                                        @this.set('office_address', place.formatted_address);
                                                        
                                                        let city = '';
                                                        for (let c of place.address_components) {
                                                            if(c.types.includes('locality')) city = c.long_name;
                                                        }
                                                        @this.set('office_city', city);
                                                    }
                                                });
                                            };

                                            this.markerInstance.addListener('dragend', updateLocation);
                                            
                                            this.mapInstance.addListener('click', (e) => {
                                                this.markerInstance.setPosition(e.latLng);
                                                updateLocation();
                                            });
                                        } else {
                                            this.mapInstance.setCenter(pos);
                                            this.markerInstance.setPosition(pos);
                                        }
                                    },
                                    clearLocation() {
                                        this.hasLocation = false;
                                        @this.set('office_address', '');
                                        @this.set('office_lat', '');
                                        @this.set('office_lng', '');
                                        @this.set('office_city', '');
                                        setTimeout(() => {
                                            let el = document.getElementById('officeAddressInput');
                                            if(el) { el.value = ''; el.focus(); }
                                        }, 100);
                                    }
                                }" x-init="initAutocomplete()">
                                    <label class="form-label fw-semibold text-dark small">Office address / landmark <span class="text-danger">*</span></label>
                                    
                                    <div wire:ignore x-show="!hasLocation">
                                        <input type="text" id="officeAddressInput" wire:model="office_address" class="form-control premium-input" placeholder="Search for your address/locality" autocomplete="off">
                                    </div>
                                    
                                    <div x-show="hasLocation" x-cloak class="mb-2">
                                        <div class="border rounded p-2 d-flex justify-content-between align-items-center" style="background: #fff; border-color: #ced4da;">
                                            <div class="text-dark small fw-medium text-truncate" style="padding-right: 15px;" x-text="$wire.office_address"></div>
                                            <button type="button" @click="clearLocation()" class="btn btn-link text-primary text-decoration-none p-0 fw-semibold" style="white-space: nowrap; font-size: 0.85rem;">Change</button>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <a href="#" x-show="!showDetails" @click.prevent="showDetails = true" class="text-decoration-none small text-primary fw-medium">+ Add Floor / Plot no. / Shop no. (optional)</a>
                                    </div>
                                    <div class="mt-3" x-show="showDetails" x-cloak style="display: none;">
                                        <label class="form-label fw-semibold text-dark small">Add Floor / Plot no. / Shop no. (optional)</label>
                                        <input type="text" wire:model="office_address_details" class="form-control premium-input" placeholder="Enter office floor / plot no. / shop no. (optional)">
                                    </div>

                                    <div x-show="hasLocation" x-cloak class="mt-3">
                                        <div class="alert py-2 px-3 small d-flex align-items-center mb-3 border-warning" style="background-color: #fff8e6; color: #856404;">
                                            <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-6"></i>
                                            <span class="fw-medium">Please provide your office area / locality to get the most relevant applications</span>
                                        </div>
                                        <div wire:ignore>
                                            <div id="officeMap" style="height: 250px; width: 100%; border: 2px solid #a5c8ff; border-radius: 8px; overflow: hidden;"></div>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <label class="form-label fw-semibold text-dark small" style="line-height: 1.4;">Would you also like to receive candidate applications from anywhere in India if they are willing to move to <span x-text="$wire.office_city || 'this city'"></span> for this job? <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-3 mt-2 mb-2">
                                                <div>
                                                    <input type="radio" wire:model.live="accept_pan_india_candidates" value="1" class="pill-radio" id="pan_india_yes">
                                                    <label class="pill-label" for="pan_india_yes">Yes</label>
                                                </div>
                                                <div>
                                                    <input type="radio" wire:model.live="accept_pan_india_candidates" value="0" class="pill-radio" id="pan_india_no">
                                                    <label class="pill-label" for="pan_india_no">No</label>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-primary-subtle text-primary p-2 rounded d-flex align-items-center mt-3">
                                                <i class="bi bi-info-circle-fill me-2"></i>
                                                <span class="small fw-medium">
                                                    <template x-if="$wire.accept_pan_india_candidates == 1">
                                                        <span>You will be receiving applications from anywhere in India</span>
                                                    </template>
                                                    <template x-if="$wire.accept_pan_india_candidates != 1">
                                                        <span>You will be receiving applications from within <span x-text="$wire.office_city || 'this city'"></span> city</span>
                                                    </template>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($work_location_type === 'Work From Home')
                                <div wire:key="loc-home" class="mb-3">
                                    <label class="form-label fw-semibold text-dark d-block mb-2 small">Job City <span class="text-danger">*</span></label>
                                    <select class="form-select premium-input" wire:model="job_city">
                                        <option value="">Select City</option>
                                        @php $s_cities = isset($jobSettings['city']) ? $jobSettings['city'] : collect([]); @endphp
                                        @foreach($s_cities as $c)
                                            <option value="{{ $c->name }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('job_city') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            @elseif($work_location_type === 'Field Job')
                                <div wire:key="loc-field" class="mb-3" x-data="{
                                    initAutocomplete() {
                                        window._initFieldAC = () => {
                                            const input = document.getElementById('fieldAreaInput');
                                            if (!input || input._acInited) return;
                                            input._acInited = true;
                                            const ac = new google.maps.places.Autocomplete(input, { 
                                                componentRestrictions: { country: 'in' }
                                            });
                                            ac.addListener('place_changed', () => {
                                                const place = ac.getPlace();
                                                if (place && input.value) {
                                                    @this.set('working_area', input.value);
                                                    input.dispatchEvent(new Event('input'));
                                                }
                                            });
                                            input.addEventListener('keydown', function(e) {
                                                if (e.key === 'Enter') e.preventDefault();
                                            });
                                        };
                                        if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                                            window._initFieldAC();
                                        } else if (!document.getElementById('gmap-places-script')) {
                                            let s = document.createElement('script');
                                            s.id = 'gmap-places-script';
                                            s.src = 'https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=_initFieldAC';
                                            s.async = true;
                                            document.head.appendChild(s);
                                        }
                                    }
                                }" x-init="initAutocomplete()">
                                    <label class="form-label fw-semibold text-dark small">Which area will the candidates be working in ? <span class="text-danger">*</span></label>
                                    <div wire:ignore wire:key="field-area-wrapper">
                                        <input type="text" id="fieldAreaInput" wire:model="working_area" class="form-control premium-input" placeholder="Search for your address/locality" autocomplete="off">
                                    </div>
                                </div>
                            @endif

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Number of Openings <span class="text-danger">*</span></label>
                                    <input type="number" wire:model.live="vacancies_count" class="form-control premium-input" min="1" required>
                                    @error('vacancies_count') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark small">Total Referral Budget (&#8377;) <span class="text-danger">*</span></label>
                                    <input type="number" wire:model.live="referral_budget" class="form-control premium-input" min="0" required>
                                    @error('referral_budget') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-12 mt-2">
                                    <div class="bg-primary-subtle text-primary p-2 rounded d-flex align-items-center">
                                        <i class="bi bi-lightning-charge-fill me-2 fs-5"></i>
                                        <span class="small">Estimated Referral Reward per Hire: <strong class="ms-1 fs-6">&#8377;{{ $vacancies_count > 0 ? number_format((float)$referral_budget / (int)$vacancies_count, 2) : '0' }}</strong></span>
                                    </div>
                                </div>
                            </div>



                            <hr class="my-4 border-light">

                            <div class="mt-2 mb-3">
                                <h4 class="section-title">Compensation</h4>
                                <p class="section-desc">Job postings with right salary & incentives will help you find the right candidates.</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark d-block mb-2 small">What is the pay type? <span class="text-danger">*</span></label>
                                <div>
                                    @foreach(['Fixed Only', 'Fixed + Incentive', 'Incentive Only'] as $opt)
                                        <input type="radio" wire:model.live="salary_type" value="{{ $opt }}" class="pill-radio" id="sal_{{ Str::slug($opt) }}">
                                        <label class="pill-label" for="sal_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="row align-items-center mb-3">
                                @if($salary_type === 'Fixed Only' || $salary_type === 'Fixed + Incentive')
                                <div class="col-md-{{ $salary_type === 'Fixed + Incentive' ? '7' : '12' }}">
                                    <label class="form-label fw-bold small">
                                        Fixed salary / month {!! $salary_type === 'Fixed + Incentive' ? '<span class="text-muted fw-normal">(excluding incentives)</span>' : '' !!} <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex align-items-center">
                                        <input type="number" wire:model="salary_min" class="form-control premium-input" placeholder="Minimum fixed salary" required>
                                        <div class="mx-2 bg-light px-3 py-2 rounded text-muted fw-bold border">to</div>
                                        <input type="number" wire:model="salary_max" class="form-control premium-input" placeholder="Maximum fixed salary" required>
                                    </div>
                                    @error('salary_min') <span class="text-danger small">{{ $message }}</span> @enderror
                                    @error('salary_max') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                @endif
                                
                                @if($salary_type === 'Fixed + Incentive')
                                <div class="col-md-1 text-center fw-bold fs-4 mt-3 text-dark">+</div>
                                @endif

                                @if($salary_type === 'Incentive Only' || $salary_type === 'Fixed + Incentive')
                                <div class="col-md-{{ $salary_type === 'Fixed + Incentive' ? '4' : '12' }}">
                                    <label class="form-label fw-bold small">Average Incentive / month <span class="text-danger">*</span> <i class="bi bi-info-circle text-muted"></i></label>
                                    <input type="number" wire:model="average_incentive" class="form-control premium-input" placeholder="Eg. ₹2000" required>
                                    @error('average_incentive') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark d-block mb-2 small">Do you offer any additional perks ?</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @php $s_perks = isset($jobSettings['perk']) ? $jobSettings['perk'] : collect([]); @endphp
                                    @foreach($s_perks as $p)
                                        <label class="btn btn-outline-secondary btn-sm rounded-pill {{ is_array($additional_perks) && in_array($p->name, $additional_perks) ? 'active bg-secondary text-white border-secondary' : '' }}" style="font-size: 13px;">
                                            <input type="checkbox" wire:model.live="additional_perks" value="{{ $p->name }}" class="d-none">
                                            {{ $p->name }} {{ is_array($additional_perks) && in_array($p->name, $additional_perks) ? '-' : '+' }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark d-block mb-2 small">Is there any joining fee or deposit required from the candidate? <span class="text-danger">*</span></label>
                                <div>
                                    <input type="radio" wire:model="joining_fee_required" value="1" class="pill-radio" id="jf_yes">
                                    <label class="pill-label" for="jf_yes">Yes</label>
                                    <input type="radio" wire:model="joining_fee_required" value="0" class="pill-radio" id="jf_no">
                                    <label class="pill-label" for="jf_no">No</label>
                                </div>
                                @error('joining_fee_required') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>

                            <hr class="my-4 border-light">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-sm" wire:click="goBack">Back</button>
                                <button type="submit" class="btn btn-sm btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm">Continue <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </form>

                    @elseif ($step === 3)
                        <!-- Step 3: Candidate Requirements -->
                        <form wire:submit.prevent="nextStep" wire:key="form-step-{{ $step }}">
                            <div class="mt-1">
                                <h4 class="section-title">Basic Requirements</h4>
                                <p class="section-desc">We'll use these requirement details to make your job visible to the right candidates.</p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark mb-2 small">Minimum Education <span class="text-danger">*</span></label>
                                <div>
                                    @foreach(['10th Or Below 10th', '12th Pass', 'Diploma', 'ITI', 'Graduate', 'Post Graduate'] as $opt)
                                        <input type="radio" wire:model.live="minimum_education" value="{{ $opt }}" class="pill-radio" id="edu_{{ Str::slug($opt) }}">
                                        <label class="pill-label" for="edu_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark mb-2 small">English level required <span class="text-danger">*</span></label>
                                <div>
                                    @foreach(['No English', 'Basic English', 'Good English'] as $opt)
                                        <input type="radio" wire:model="english_level" value="{{ $opt }}" class="pill-radio" id="eng_{{ Str::slug($opt) }}">
                                        <label class="pill-label" for="eng_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark mb-2 small">Total experience required <span class="text-danger">*</span></label>
                                <div>
                                    @foreach(['Any', 'Experienced Only', 'Fresher Only'] as $opt)
                                        <input type="radio" wire:model.live="experience_type" value="{{ $opt }}" class="pill-radio" id="exp_{{ Str::slug($opt) }}">
                                        <label class="pill-label" for="exp_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                    @endforeach
                                </div>
                                @if($experience_type === 'Experienced Only')
                                    <div class="row mt-2 g-2 p-3 bg-light rounded-4 border">
                                        <div class="col-md-6">
                                            <label class="small text-muted fw-semibold mb-1">Min Years Experience</label>
                                            <input type="number" wire:model="min_experience_years" class="form-control premium-input" min="0">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="small text-muted fw-semibold mb-1">Max Years Experience</label>
                                            <input type="number" wire:model="max_experience_years" class="form-control premium-input" min="0">
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <hr class="my-4 border-light">
                            <div class="mt-1">
                                <h4 class="section-title">Additional Requirements <span class="text-muted fw-normal fs-6">(Optional)</span></h4>
                                <p class="section-desc">Add additional requirement so that we can help you find the right candidates.</p>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @php
                                    $reqTypes = [
                                        'degrees' => 'Degree / Specialization',
                                        'gender' => 'Gender',
                                        'age' => 'Age',
                                        'distance' => 'Distance',
                                        'languages' => 'Regional Languages',
                                        'assets' => 'Assets',
                                        'skills' => 'Skills'
                                    ];
                                @endphp
                                @foreach($reqTypes as $key => $label)
                                    @if($key === 'degrees' && !in_array($minimum_education, ['Diploma', 'ITI', 'Graduate', 'Post Graduate']))
                                        @continue
                                    @endif
                                    <label wire:key="req-type-{{ $key }}" class="btn btn-outline-primary rounded-pill btn-sm d-flex align-items-center gap-1 {{ in_array($key, $selected_requirements) || ($key === 'degrees' && in_array($minimum_education, ['Diploma', 'ITI', 'Graduate', 'Post Graduate'])) ? 'active bg-primary text-white' : '' }}">
                                        <input type="checkbox" wire:model.live="selected_requirements" value="{{ $key }}" class="d-none">
                                        {{ $label }}
                                        @if(in_array($key, $selected_requirements) || ($key === 'degrees' && in_array($minimum_education, ['Diploma', 'ITI', 'Graduate', 'Post Graduate'])))
                                            <i class="bi bi-x-circle ms-1"></i>
                                        @else
                                            <i class="bi bi-plus"></i>
                                        @endif
                                    </label>
                                @endforeach
                            </div>

                            <!-- Skills Preference -->
                            @if(in_array('skills', $selected_requirements))
                            <div class="card shadow-sm border-0 mb-3 bg-light rounded-4">
                                <div class="card-body">
                                    <label class="form-label fw-bold small text-dark">Skills preference <i class="bi bi-info-circle text-muted ms-1"></i></label>
                                    <div class="mb-2">
                                        <input type="text" class="form-control premium-input rounded-3 bg-white" placeholder="Type to search for skills">
                                    </div>
                                    <div class="text-muted small mb-2 fw-semibold">Suggested skills:</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @php $s_skills = isset($jobSettings['skill']) ? $jobSettings['skill'] : collect([]); @endphp
                                        @foreach($s_skills as $s)
                                            <label wire:key="skill-{{ $s->id }}" class="btn btn-outline-secondary btn-sm rounded-pill {{ in_array($s->name, $selected_skills) ? 'active bg-secondary text-white' : '' }}">
                                                <input type="checkbox" wire:model.live="selected_skills" value="{{ $s->name }}" class="d-none">
                                                {{ $s->name }} {{ in_array($s->name, $selected_skills) ? 'x' : '+' }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Assets Required -->
                            @if(in_array('assets', $selected_requirements))
                            <div class="card shadow-sm border-0 mb-3 bg-light rounded-4">
                                <div class="card-body">
                                    <label class="form-label fw-bold small text-dark">Assets required <i class="bi bi-info-circle text-muted ms-1"></i></label>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @php $s_assets = isset($jobSettings['asset']) ? $jobSettings['asset'] : collect([]); @endphp
                                        @foreach($s_assets as $a)
                                            <label wire:key="asset-{{ $a->id }}" class="btn btn-outline-secondary btn-sm rounded-pill {{ in_array($a->name, $selected_assets) ? 'active bg-secondary text-white' : '' }}">
                                                <input type="checkbox" wire:model.live="selected_assets" value="{{ $a->name }}" class="d-none">
                                                {{ $a->name }} {{ in_array($a->name, $selected_assets) ? 'x' : '+' }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Regional Language -->
                            @if(in_array('languages', $selected_requirements))
                            <div class="card shadow-sm border-0 mb-3 bg-light rounded-4">
                                <div class="card-body">
                                    <label class="form-label fw-bold small text-dark">Regional language required <i class="bi bi-info-circle text-muted ms-1"></i></label>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @php $s_languages = isset($jobSettings['language']) ? $jobSettings['language'] : collect([]); @endphp
                                        @foreach($s_languages as $l)
                                            <label wire:key="lang-{{ $l->id }}" class="btn btn-outline-secondary btn-sm rounded-pill {{ in_array($l->name, $selected_languages) ? 'active bg-secondary text-white' : '' }}">
                                                <input type="checkbox" wire:model.live="selected_languages" value="{{ $l->name }}" class="d-none">
                                                {{ $l->name }} {{ in_array($l->name, $selected_languages) ? 'x' : '+' }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Distance -->
                            @if(in_array('distance', $selected_requirements))
                            <div class="card shadow-sm border-0 mb-3 bg-light rounded-4">
                                <div class="card-body">
                                    <label class="form-label fw-bold small text-dark">Distance - Prefer applications from <i class="bi bi-info-circle text-muted ms-1"></i></label>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @foreach(['10km', '25km', 'Entire City', 'Pan India'] as $opt)
                                            <input type="radio" wire:model="distance" value="{{ $opt }}" class="pill-radio" id="dist_{{ Str::slug($opt) }}">
                                            <label class="pill-label" for="dist_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                        @endforeach
                                    </div>
                                    @error('distance') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            @endif

                            <!-- Age -->
                            @if(in_array('age', $selected_requirements))
                            <div class="card shadow-sm border-0 mb-3 bg-light rounded-4">
                                <div class="card-body">
                                    <label class="form-label fw-bold small text-dark">Age (in years) <i class="bi bi-info-circle text-muted ms-1"></i></label>
                                    <div class="row g-2 align-items-center mt-2" style="max-width:300px;">
                                        <div class="col-5">
                                            <input type="number" wire:model="min_age" class="form-control premium-input text-center bg-white" min="16" placeholder="18">
                                        </div>
                                        <div class="col-2 text-center text-muted fw-semibold">to</div>
                                        <div class="col-5">
                                            <input type="number" wire:model="max_age" class="form-control premium-input text-center bg-white" min="16" placeholder="40">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Gender -->
                            @if(in_array('gender', $selected_requirements))
                            <div class="card shadow-sm border-0 mb-3 bg-light rounded-4">
                                <div class="card-body">
                                    <label class="form-label fw-bold small text-dark">Gender <i class="bi bi-info-circle text-muted ms-1"></i></label>
                                    <div class="d-flex flex-wrap gap-2 mt-2 mb-2">
                                        @foreach(['Any', 'Male', 'Female'] as $opt)
                                            <input type="radio" wire:model="gender_preference" value="{{ $opt }}" class="pill-radio" id="gender_req_{{ Str::slug($opt) }}">
                                            <label class="pill-label" for="gender_req_{{ Str::slug($opt) }}">{{ $opt }}</label>
                                        @endforeach
                                    </div>
                                    @if($gender_preference && $gender_preference !== 'Any')
                                        <div class="text-warning small fw-semibold"><i class="bi bi-exclamation-triangle"></i> This may reduce the number of applications for this job</div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Degree / Specialization -->
                            @if(in_array('degrees', $selected_requirements) || in_array($minimum_education, ['Diploma', 'ITI', 'Graduate', 'Post Graduate']))
                            <div class="card shadow-sm border-0 mb-3 bg-light rounded-4">
                                <div class="card-body">
                                    <label class="form-label fw-bold small text-dark">Degree / specialization <i class="bi bi-info-circle text-muted ms-1"></i></label>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        @php 
                                            $s_degrees = isset($jobSettings['degree']) ? $jobSettings['degree'] : collect([]); 
                                            // Filter degrees by dependency
                                            $s_degrees = $s_degrees->filter(function($d) use ($minimum_education) {
                                                return empty($d->depends_on) || $d->depends_on === $minimum_education;
                                            });
                                        @endphp
                                        @foreach($s_degrees as $d)
                                            <label wire:key="degree-{{ $d->id }}" class="btn btn-outline-secondary btn-sm rounded-pill {{ in_array($d->name, $selected_degrees) ? 'active bg-secondary text-white' : '' }}">
                                                <input type="checkbox" wire:model.live="selected_degrees" value="{{ $d->name }}" class="d-none">
                                                {{ $d->name }} {{ in_array($d->name, $selected_degrees) ? 'x' : '+' }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <hr class="my-4 border-light">

                            <h4 class="section-title">Additional Requirements (Dynamic)</h4>
                            <p class="section-desc">Custom screening questions defined by the selected Job Template.</p>
                            
                            @if(count($screening_questions) === 0)
                                <div class="bg-light p-4 rounded-4 text-center text-muted mb-3 border border-dashed">
                                    <i class="bi bi-card-checklist fs-2 mb-2 d-block text-secondary opacity-50"></i>
                                    No additional dynamic requirements for this template.
                                </div>
                            @endif

                            @foreach($screening_questions as $index => $q)
                                @php
                                    $showField = true;
                                    if(!empty($q['conditional_parent'])) {
                                        $parentAns = $screening_answers[$q['conditional_parent']] ?? null;
                                        if($parentAns !== $q['conditional_value']) {
                                            $showField = false;
                                        }
                                    }
                                @endphp
                                
                                @if($showField)
                                    <div class="mb-3 bg-white">
                                        <label class="form-label fw-semibold text-dark mb-2 small">{{ $q['question'] }} @if(!empty($q['required']))<span class="text-danger">*</span>@endif</label>
                                        
                                        @if(in_array($q['type'], ['radio', 'select', 'checkbox']))
                                            @php
                                                $options = !empty($q['options']) ? array_map('trim', explode(',', $q['options'])) : [];
                                            @endphp
                                            <div>
                                                @foreach($options as $optKey => $optVal)
                                                    <input type="radio" wire:model.live="screening_answers.{{ $q['id'] ?? $q['question'] }}" value="{{ $optVal }}" class="pill-radio" id="dyn_{{ $index }}_{{ $optKey }}">
                                                    <label class="pill-label" for="dyn_{{ $index }}_{{ $optKey }}">{{ $optVal }}</label>
                                                @endforeach
                                            </div>
                                        @else
                                            <input type="text" class="form-control premium-input" wire:model="screening_answers.{{ $q['id'] ?? $q['question'] }}" placeholder="Enter answer here...">
                                        @endif
                                    </div>
                                @endif
                            @endforeach

                            <hr class="my-4 border-light">

                            <h4 class="section-title">Job Description</h4>
                            <p class="section-desc">Describe the responsibilities of this job and other specific requirements here.</p>
                            
                            <div class="mb-3 border rounded-3 overflow-hidden shadow-sm" style="border-color: #e0e0e0 !important;">
                                <div wire:ignore wire:key="job-desc-editor" x-data="{
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
                                    <div x-ref="editor" style="height: 200px; background: white; border: none; font-size: 14px;">{!! $job_description !!}</div>
                                </div>
                                <textarea wire:model="job_description" id="hidden-job-description" class="d-none"></textarea>
                            </div>

                            <div class="d-flex justify-content-between mt-4">
                                <button type="button" class="btn btn-sm btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-sm" wire:click="goBack">Back</button>
                                <button type="submit" class="btn btn-sm btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm">Continue <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </form>

                    @elseif ($step === 4)
                        <!-- Step 4: Interviewer Information -->
                        <form wire:submit.prevent="nextStep" wire:key="form-step-{{ $step }}">
                            <h4 class="section-title mb-3 mt-1">Interview Setup</h4>
                            
                            <div class="mb-3 bg-light p-2 p-md-3 rounded-4 border">
                                <label class="form-label fw-bold text-dark fs-6 mb-3">Is this a walk-in interview? <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4">
                                    <div class="form-check form-check-inline m-0">
                                        <input type="radio" wire:model="is_walk_in" value="1" class="form-check-input" style="width: 20px; height: 20px; margin-top: 2px;" id="walkin_yes">
                                        <label for="walkin_yes" class="form-check-label ms-2 text-dark fw-medium cursor-pointer" style="font-size:15px;">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline m-0">
                                        <input type="radio" wire:model="is_walk_in" value="0" class="form-check-input" style="width: 20px; height: 20px; margin-top: 2px;" id="walkin_no">
                                        <label for="walkin_no" class="form-check-label ms-2 text-dark fw-medium cursor-pointer" style="font-size:15px;">No</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 bg-light p-2 p-md-3 rounded-4 border mt-3">
                                <label class="form-label fw-bold text-dark fs-6 mb-1">Communication Preferences</label>
                                <p class="text-muted small mb-3">Which candidates should be able to contact you directly?</p>
                                
                                <div class="form-check mb-2 p-3 bg-white border border-2 rounded-4 shadow-sm d-flex align-items-center" style="border-color: #e0e0e0 !important; cursor: pointer;" onclick="document.getElementById('cp_1').click()">
                                    <input type="radio" wire:model="contact_preference" value="All candidates" class="form-check-input ms-0 me-3" style="width: 18px; height: 18px;" id="cp_1">
                                    <label for="cp_1" class="form-check-label fw-semibold text-dark w-100 mb-0" style="cursor: pointer; font-size:14px;">All candidates</label>
                                </div>
                                <div class="form-check mb-2 p-3 bg-white border border-2 rounded-4 shadow-sm d-flex align-items-center" style="border-color: #e0e0e0 !important; cursor: pointer;" onclick="document.getElementById('cp_2').click()">
                                    <input type="radio" wire:model="contact_preference" value="High & Medium matches" class="form-check-input ms-0 me-3" style="width: 18px; height: 18px;" id="cp_2">
                                    <label for="cp_2" class="form-check-label fw-semibold text-dark w-100 mb-0" style="cursor: pointer; font-size:14px;">High & Medium matches only</label>
                                </div>
                                <div class="form-check mb-2 p-3 bg-white border border-2 rounded-4 shadow-sm d-flex align-items-center" style="border-color: #e0e0e0 !important; cursor: pointer;" onclick="document.getElementById('cp_3').click()">
                                    <input type="radio" wire:model="contact_preference" value="None" class="form-check-input ms-0 me-3" style="width: 18px; height: 18px;" id="cp_3">
                                    <label for="cp_3" class="form-check-label fw-semibold text-dark w-100 mb-0" style="cursor: pointer; font-size:14px;">None, I will contact candidates first</label>
                                </div>
                            </div>

                            <hr class="my-4 border-light">
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-sm btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-sm" wire:click="goBack">Back</button>
                                <button type="submit" class="btn btn-sm btn-primary px-4 py-2 fw-bold rounded-pill shadow-sm">Continue <i class="bi bi-arrow-right ms-1"></i></button>
                            </div>
                        </form>

                    @elseif ($step === 5)
                        <!-- Step 5: Choose Plan & Publish -->
                        <div class="text-center mb-3 mt-2">
                            <h4 class="fw-bold text-dark">Select Plan & Publish</h4>
                            <p class="text-muted small">Choose a visibility plan for your job post.</p>
                        </div>
                        
                        @if(session()->has('error'))
                            <div class="alert alert-danger mb-3 rounded-3 border-0 bg-danger-subtle text-danger small"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
                        @endif
                        
                        <div class="row row-cols-1 row-cols-md-3 g-3 mb-3">
                            @foreach($plans as $plan)
                            <div class="col">
                                <div class="card h-100 rounded-4 {{ $job_plan_id == $plan->id ? 'border-primary border-2 shadow' : 'border-light shadow-sm' }}" style="cursor: pointer; transition: all 0.3s; position: relative;" wire:click="selectPlan({{ $plan->id }})">
                                    @if($job_plan_id == $plan->id)
                                        <div class="position-absolute top-0 end-0 p-2 text-primary">
                                            <i class="bi bi-check-circle-fill fs-5 m-1 d-block"></i>
                                        </div>
                                    @endif
                                    @if($plan->is_featured)
                                        <span class="badge bg-gradient-warning text-dark position-absolute top-0 start-50 translate-middle shadow-sm px-3 py-1 rounded-pill fw-bold" style="z-index: 10; background: linear-gradient(45deg, #ffd700, #ff8c00); color: white !important; font-size: 11px;">RECOMMENDED</span>
                                    @endif
                                    <div class="card-body text-center p-3">
                                        <h6 class="fw-bold text-muted text-uppercase tracking-wide mt-2" style="font-size:12px;">{{ $plan->name }}</h6>
                                        <h2 class="fw-bold text-dark my-3">&#8377;{{ number_format($plan->price) }}</h2>
                                        <div class="text-start mt-3 bg-light p-2 rounded-3">
                                            <div class="d-flex align-items-center mb-1">
                                                <i class="bi bi-check-circle-fill text-success me-2" style="font-size:12px;"></i>
                                                <span class="text-dark fw-medium" style="font-size:12px;">{{ $plan->validity_days }} Days Visibility</span>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-check-circle-fill text-success me-2" style="font-size:12px;"></i>
                                                <span class="text-dark fw-medium" style="font-size:12px;">{{ $plan->job_limit ? $plan->job_limit . ' Jobs Limit' : 'Unlimited Jobs' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        @if($job_plan_id)
                        @php 
                            $selectedPlan = $plans->firstWhere('id', $job_plan_id);
                            $activeJobsCount = \App\Models\JobPost::where('partner_id', auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id)->where('job_plan_id', $selectedPlan->id)->where('status', 'active')->count();
                            
                            if (empty($selectedPlan->job_limit)) {
                                $planPrice = ($activeJobsCount > 0) ? 0 : $selectedPlan->price;
                                $isPackageApplied = ($activeJobsCount > 0);
                            } else {
                                $planPrice = ($activeJobsCount % $selectedPlan->job_limit === 0) ? $selectedPlan->price : 0;
                                $isPackageApplied = ($activeJobsCount % $selectedPlan->job_limit !== 0);
                            }

                            $total = $planPrice + $referral_budget;
                            $gst = $total * 0.18;
                            $grandTotal = $total + $gst;
                            $wBalance = (float)($walletBalance ?? 0);
                            $wDeducted = min($wBalance, $grandTotal);
                            $payable = $grandTotal - $wDeducted;
                        @endphp
                        <div class="bg-light p-2 p-md-3 rounded-4 border">
                            <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-receipt me-2 text-primary"></i> Order Summary</h6>
                            <div class="d-flex justify-content-between mb-2 text-secondary small">
                                <span>
                                    Job Plan: <strong class="text-dark">{{ $selectedPlan->name }}</strong>
                                    @if($isPackageApplied && $selectedPlan->price > 0)
                                        <span class="badge bg-success-subtle text-success ms-2">Package Applied</span>
                                    @endif
                                </span>
                                <span>
                                    @if($isPackageApplied && $selectedPlan->price > 0)
                                        <del class="text-muted me-2">&#8377;{{ number_format($selectedPlan->price, 2) }}</del>
                                    @endif
                                    &#8377;{{ number_format($planPrice, 2) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-secondary small">
                                <span>Referral Budget Deposit</span>
                                <span>&#8377;{{ number_format($referral_budget, 2) }}</span>
                            </div>
                            <hr class="border-secondary mb-3">
                            <div class="d-flex justify-content-between mb-2 fw-bold text-dark">
                                <span>Subtotal</span>
                                <span>&#8377;{{ number_format($total, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 text-secondary small">
                                <span>+ 18% GST</span>
                                <span>&#8377;{{ number_format($gst, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 fw-bold text-dark">
                                <span>Total Cost</span>
                                <span>&#8377;{{ number_format($grandTotal, 2) }}</span>
                            </div>
                            @if(auth()->user()->isPartner())
                            <div class="d-flex justify-content-between mb-3 text-danger fw-semibold small">
                                <span>Wallet Deduction (Bal: &#8377;{{ number_format($wBalance, 2) }})</span>
                                <span>- &#8377;{{ number_format($wDeducted, 2) }}</span>
                            </div>
                            @endif
                            <div class="d-flex justify-content-between align-items-center mb-0 mt-3 p-3 bg-white rounded-4 border border-2 border-success shadow-sm">
                                <span class="fw-bold text-success">Total Payable</span>
                                <span class="fw-bold fs-5 text-success">&#8377;{{ number_format($payable, 2) }}</span>
                            </div>
                        </div>
                        @endif

                        <hr class="my-4 border-light">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-sm btn-light px-4 py-2 fw-bold rounded-pill text-muted border shadow-sm" wire:click="goBack">Back</button>
                            <button type="button" class="btn btn-sm btn-success px-4 py-2 fw-bold rounded-pill shadow-sm" wire:click="publishJob" wire:loading.attr="disabled" wire:target="publishJob" {{ !$job_plan_id ? 'disabled' : '' }}>
                                <span wire:loading.remove wire:target="publishJob">
                                    <i class="bi bi-rocket-takeoff me-2"></i> Publish Job
                                </span>
                                <span wire:loading wire:target="publishJob">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Publishing...
                                </span>
                            </button>
                        </div>

                    @endif

                </div>
            </div>
        </div>
    @endif
</div>
