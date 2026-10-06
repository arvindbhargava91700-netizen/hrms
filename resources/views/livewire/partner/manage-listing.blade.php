<div>
    @if($category)
        <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);">
            <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;">
                        <i class="bi {{ $category->icon ?? 'bi-tag-fill' }} fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.72rem;">Selected Category</div>
                        <h5 class="mb-0 fw-bold text-dark">{{ $category->name }}</h5>
                    </div>
                </div>
                <div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2 rounded-pill fw-bold">
                        <i class="bi {{ $category->icon ?? 'bi-tag' }} me-1"></i> {{ $category->name }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    {{-- Tab Navigation --}}
        <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'basic' ? 'active' : '' }}" wire:click="$set('activeTab','basic')">
                <i class="bi bi-info-circle me-1"></i> Basic Info
            </button>
        </li>
        @if($category?->has_shifts)
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'shifts' ? 'active' : '' }}" wire:click="$set('activeTab','shifts')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-clock me-1"></i> Shifts
            </button>
        </li>
        @endif
        @if($category?->has_trainers)
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'trainers' ? 'active' : '' }}" wire:click="$set('activeTab','trainers')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-person-badge me-1"></i> Staff / Trainers
            </button>
        </li>
        @endif
        @if($category?->has_rooms)
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'floors' ? 'active' : '' }}" wire:click="$set('activeTab','floors')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-layers me-1"></i> Floors
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'rooms' ? 'active' : '' }}" wire:click="$set('activeTab','rooms')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-door-open me-1"></i> Rooms
            </button>
        </li>
        @endif
        @if($category?->has_packages)
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'packages' ? 'active' : '' }}" wire:click="$set('activeTab','packages')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-card-checklist me-1"></i> Packages
            </button>
        </li>
        @endif
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'fields' ? 'active' : '' }}" wire:click="$set('activeTab','fields')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-list-check me-1"></i> Details
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'keywords' ? 'active' : '' }}" wire:click="$set('activeTab','keywords')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-search me-1"></i> Search Keywords
            </button>
        </li>
    </ul>

    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

    {{-- TAB 1: Basic Info --}}
    @if($activeTab === 'basic')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
            @if($category)
                <span class="badge bg-primary text-white px-3 py-1.5 rounded-pill fw-bold" style="font-size: 0.82rem;">
                    <i class="bi {{ $category->icon ?? 'bi-tag' }} me-1"></i> {{ $category->name }}
                </span>
            @endif
        </div>
        <div class="card-body">
            <form wire:submit="saveBasic">
                <div class="row g-3">
                    
                    <div class="col-md-6">
                        <label class="form-label fw-600">Category</label>
                        <input type="text" class="form-control bg-light fw-bold text-primary" value="{{ $category?->name ?? 'N/A' }}" readonly disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Listing Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="title" placeholder="e.g. Gold's Fitness Center">
                        @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    @if($category && $category->has_rooms)
                    <div class="col-md-6">
                        <label class="form-label fw-600">PG / Hostel Type</label>
                        <select class="form-select" wire:model="genderType">
                            <option value="">Select Type</option>
                            <option value="boys">🏠 Boys PG</option>
                            <option value="girls">🏠 Girls PG</option>
                            <option value="co-living">🏠 Boys & Girls PG (Co-Living)</option>
                        </select>
                        @error('genderType') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    @endif
                    @if(!($category && $category->has_rooms))
                    <div class="col-md-6">
                        <label class="form-label fw-600">Security Deposit</label>
                        <div class="input-group">
                            <span class="input-group-text">₹</span>
                            <input type="number" step="0.01" class="form-control" wire:model="listingSecurityDeposit" placeholder="e.g. 500">
                        </div>
                        <div class="form-text text-muted">Fixed security deposit collected from customers.</div>
                        @error('listingSecurityDeposit') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label fw-600">Description</label>
                        <textarea class="form-control" wire:model="description" rows="4"
                            placeholder="Describe your service, equipment, facilities..."></textarea>
                    </div>

                    <div class="col-12" wire:ignore>
                        <label class="form-label fw-600">Address (Search Location) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" id="addressInput" class="form-control" wire:model.defer="address" placeholder="Search full address...">
                            <button class="btn btn-outline-secondary" type="button" id="detectLocationBtn" title="Detect Current Location">
                                <i class="bi bi-crosshair"></i> Detect
                            </button>
                        </div>
                        @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">City</label>
                        <input type="text" class="form-control bg-light" wire:model="city" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">State</label>
                        <input type="text" class="form-control bg-light" wire:model="state" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600">Pincode</label>
                        <input type="text" class="form-control bg-light" wire:model="pincode" readonly>
                    </div>
                    <div class="col-md-6 d-none">
                        <input type="text" wire:model="latitude">
                        <input type="text" wire:model="longitude">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Landmark</label>
                        <input type="text" class="form-control" wire:model="landmark" placeholder="Nearby landmark, mall, metro station">
                        @error('landmark') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Latitude <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="latitude" placeholder="e.g. 28.613939">
                        @error('latitude') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600">Longitude <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="longitude" placeholder="e.g. 77.209023">
                        @error('longitude') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    {{-- Opening / Closing Time — shown for any category with has_attendance enabled --}}
                    @if($category?->has_attendance)
                    <div class="col-12">
                        <hr class="my-1">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-door-open text-primary"></i>
                            <span class="fw-600 text-dark">Attendance Hours</span>
                            @if(!$category?->has_shifts)
                                <span class="badge bg-primary-subtle text-primary ms-1" style="font-size:0.75rem;">Used for customer attendance gate</span>
                            @else
                                <span class="badge bg-success-subtle text-success ms-1" style="font-size:0.75rem;">Auto — controlled by shift times</span>
                            @endif
                        </div>

                        @if($category?->has_shifts)
                        {{-- Gym / Shift-based: no manual time inputs — shift times control the window --}}
                        <div class="alert alert-success py-2 mb-0 d-flex align-items-start gap-2" style="font-size:0.82rem;">
                            <i class="bi bi-check-circle-fill text-success mt-1 flex-shrink-0"></i>
                            <div>
                                <strong>Shift-based attendance is automatic.</strong><br>
                                Each shift's own <em>Start Time → End Time</em> is used as the attendance window for customers booked in that shift. You do <strong>not</strong> need to set an opening/closing time here — manage individual windows from the <strong>Shifts</strong> tab.
                            </div>
                        </div>
                        @else
                        {{-- Room / PG: manual Opening & Closing time --}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600">Opening Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" wire:model="openingTime">
                                <div class="form-text text-muted">Customers can punch-in from this time.</div>
                                @error('openingTime') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600">Closing Time <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" wire:model="closingTime">
                                <div class="form-text text-muted">Attendance window closes at this time.</div>
                                @error('closingTime') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="alert alert-info py-2 mt-2 mb-0" style="font-size:0.82rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Room / PG:</strong> Customers can mark attendance only between Opening and Closing time, and must be within 100 metres of this listing's location.
                        </div>
                        @endif
                    </div>
                    @endif



                    @if($listing && $listing->images->count())
                    <div class="col-12">
                        <label class="form-label fw-600">Current Images</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($listing->images as $img)
                            <div class="position-relative" style="width:120px;">
                                <img src="{{ $img->url }}" class="rounded" style="width:120px;height:120px;object-fit:cover;">
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0"
                                    wire:click="deleteImage({{ $img->id }})" style="width:22px;height:22px;padding:0;font-size:10px;">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label fw-600">Upload Photos (Minimum 3 Required) <span class="text-danger">*</span></label>
                        
                        <div class="mt-2">
                            <input type="file" wire:model="newListingImages" class="form-control" accept="image/*" multiple>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-3 mt-3">
                            @if($newListingImages)
                                @foreach($newListingImages as $index => $tempImg)
                                <div class="position-relative" style="width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;">
                                    <img src="{{ $tempImg->temporaryUrl() }}" class="w-100 h-100" style="object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 22px; height: 22px;" wire:click="removeNewListingImage({{ $index }})"><i class="bi bi-x"></i></button>
                                </div>
                                @endforeach
                            @endif
                        </div>
                        
                        <div class="form-text mt-2">You can select multiple photos at once. Minimum 3 photos are mandatory before submission. JPG, PNG, WebP. Max 2MB each.</div>
                        @error('newListingImages') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        @error('newListingImages.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="button" class="btn btn-outline-secondary px-4 me-2" wire:click="saveDraft" wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveDraft" class="ft-btn-spinner dark"></span>
                        <i class="bi bi-save me-2" wire:loading.remove wire:target="saveDraft"></i>Save Draft
                    </button>
                    <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveBasic" class="ft-btn-spinner"></span>
                        <i class="bi bi-arrow-right me-2" wire:loading.remove wire:target="saveBasic"></i>Save &amp; Next: Shifts
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- TAB 2: Shifts --}}
    @if($activeTab === 'shifts')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-clock me-2"></i>Shifts</h6></div>
        <div class="card-body">
            {{-- Existing Shifts --}}
            @if($listing && $listing->shifts->count())
            <div class="row g-3 mb-4">
                @foreach($listing->shifts as $shift)
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--primary) !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-700 mb-1">{{ $shift->shift_label }}</h6>
                                    <div class="text-muted small">{{ $shift->start_time }} – {{ $shift->end_time }}</div>
                                    <div class="mt-2">
                                        <span class="badge bg-primary-subtle text-primary">
                                            <i class="bi bi-people me-1"></i>Max {{ $shift->max_members }}
                                        </span>
                                    </div>
                                </div>
                                <button class="btn btn-sm btn-outline-danger" wire:click="deleteShift({{ $shift->id }})"
                                    wire:confirm="Delete this shift?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="alert alert-info mb-4"><i class="bi bi-info-circle me-2"></i>No shifts added yet. Add shifts below.</div>
            @endif

            {{-- Add Shift Form --}}
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-600 mb-3">Add / Update Shift</h6>
                    <form wire:submit="addShift" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Shift <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="shiftName">
                                <option value="morning">🌅 Morning</option>
                                <option value="evening">🌆 Evening</option>
                                <option value="night">🌙 Night</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" wire:model="startTime">
                            @error('startTime') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" wire:model="endTime">
                            @error('endTime') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Members</label>
                            <input type="number" class="form-control" wire:model="maxMembers" min="1">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">
                                <span wire:loading wire:target="addShift" class="ft-btn-spinner"></span>
                                <span wire:loading.remove wire:target="addShift">Add</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="goToPreviousTab()">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </button>
                <button class="btn btn-primary" wire:click="goToNextTab()">
                    Next <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 3: Trainers --}}
    @if($activeTab === 'trainers')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Staff / Trainers</h6></div>
        <div class="card-body">
            {{-- Existing Trainers --}}
            @if($listing && $listing->trainers->count())
            <div class="row g-3 mb-4">
                @foreach($listing->trainers as $trainer)
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center h-100 overflow-hidden">
                        @php
                            $tPhotos = json_decode($trainer->photo, true);
                            if (!is_array($tPhotos)) $tPhotos = [];
                            $tPhotos = array_filter($tPhotos);
                        @endphp
                        @if(count($tPhotos) > 0)
                            <div id="trainerCarousel{{ $trainer->id }}" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner" style="height:200px;">
                                    @foreach($tPhotos as $idx => $p)
                                    <div class="carousel-item {{ $idx === 0 ? 'active' : '' }} h-100">
                                        <img src="{{ asset('storage/' . $p) }}" class="d-block w-100 h-100" style="object-fit:cover;">
                                    </div>
                                    @endforeach
                                </div>
                                @if(count($tPhotos) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#trainerCarousel{{ $trainer->id }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#trainerCarousel{{ $trainer->id }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                                @endif
                            </div>
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center text-muted" style="height:200px;">
                                <i class="bi bi-person text-muted" style="font-size: 4rem;"></i>
                            </div>
                        @endif

                        <div class="card-body d-flex flex-column pt-3">
                            <h6 class="fw-bold mb-1">{{ $trainer->name }}</h6>
                            <div class="text-primary small mb-2 fw-semibold">{{ $trainer->specialization ?? 'General Fitness' }}</div>
                            <div>
                                <span class="badge bg-secondary-subtle text-secondary mb-2">{{ $trainer->experience_years }} yrs exp</span>
                            </div>
                            @if($trainer->description)
                            <div class="text-muted small mb-3" style="max-height: 40px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                {{ $trainer->description }}
                            </div>
                            @endif
                            <div class="mt-auto pt-3 border-top d-flex justify-content-center gap-2">
                                <button class="btn btn-sm btn-outline-primary px-3" wire:click="editTrainer({{ $trainer->id }})">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger px-3" wire:click="deleteTrainer({{ $trainer->id }})"
                                    wire:confirm="Remove this trainer?">
                                    <i class="bi bi-trash me-1"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="alert alert-info mb-4"><i class="bi bi-info-circle me-2"></i>No staff/trainers added yet.</div>
            @endif

            {{-- Add/Edit Trainer Form --}}
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-600 mb-3">{{ $editingTrainerId ? 'Edit Staff / Trainer' : 'Add Staff / Trainer' }}</h6>
                    <form wire:submit="saveTrainer" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="trainerName" placeholder="Full name">
                            @error('trainerName') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Specialization</label>
                            <input type="text" class="form-control" wire:model="trainerSpec" placeholder="e.g. Yoga, Weights">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Experience (Years)</label>
                            <input type="number" class="form-control" wire:model="trainerExp" min="0">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Upload Photos (Minimum 3 Required) <span class="text-danger">*</span></label>
                            
                            <div class="mt-2">
                                <input type="file" wire:model="newTrainerPhotos" class="form-control" accept="image/*" multiple>
                            </div>
                            
                            <div class="d-flex flex-wrap gap-3 mt-3">
                                @if($editingTrainerId && $existingTrainerPhotos)
                                    @foreach($existingTrainerPhotos as $index => $exImg)
                                    <div class="position-relative" style="width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;">
                                        <img src="{{ asset('storage/' . $exImg) }}" class="w-100 h-100" style="object-fit: cover;">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 22px; height: 22px;" wire:click="removeExistingTrainerPhoto({{ $index }})"><i class="bi bi-x"></i></button>
                                    </div>
                                    @endforeach
                                @endif
                                @if($newTrainerPhotos)
                                    @foreach($newTrainerPhotos as $index => $tempImg)
                                    <div class="position-relative" style="width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;">
                                        <img src="{{ $tempImg->temporaryUrl() }}" class="w-100 h-100" style="object-fit: cover;">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 22px; height: 22px;" wire:click="removeNewTrainerPhoto({{ $index }})"><i class="bi bi-x"></i></button>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                            
                            <div class="form-text mt-2">You can select multiple photos. Minimum 3 photos required. JPG, PNG, WebP. Max 1MB each.</div>
                            @error('newTrainerPhotos') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @error('newTrainerPhotos.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description / Bio</label>
                            <textarea class="form-control" wire:model="trainerDesc" rows="2" placeholder="Brief description of the trainer's background or achievements..."></textarea>
                            @error('trainerDesc') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 mt-3 d-flex gap-2">
                            @if($editingTrainerId)
                                <button type="button" class="btn btn-outline-secondary" wire:click="cancelEditTrainer" wire:loading.attr="disabled">
                                    Cancel Edit
                                </button>
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading wire:target="saveTrainer" class="ft-btn-spinner"></span>
                                    <span wire:loading.remove wire:target="saveTrainer">Update</span>
                                </button>
                            @else
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading wire:target="saveTrainer" class="ft-btn-spinner"></span>
                                    <span wire:loading.remove wire:target="saveTrainer">Add Trainer</span>
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="goToPreviousTab()">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </button>
                <button class="btn btn-primary" wire:click="goToNextTab()">
                    Next <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB Floors --}}
    @if($activeTab === 'floors')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-layers me-2"></i>Floor Management</h6></div>
        <div class="card-body">

            @if($listing && $listing->floors->count())
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Floor #</th>
                            <th>Floor Name</th>
                            <th>Rooms Added</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($listing->floors as $floor)
                        <tr>
                            <td>{{ $floor->floor_number }}</td>
                            <td><strong>{{ $floor->name }}</strong></td>
                            <td>
                                <span class="badge bg-primary">{{ $floor->rooms->count() }} rooms</span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary me-1" wire:click="editFloor({{ $floor->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                    wire:click="deleteFloor({{ $floor->id }})"
                                    wire:confirm="Delete floor and all its rooms?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert alert-info mb-4"><i class="bi bi-info-circle me-2"></i>No floors added yet.</div>
            @endif

            {{-- Add Floor Form --}}
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-600 mb-3">{{ $editingFloorId ? 'Edit Floor' : 'Add Floor' }}</h6>
                    <form wire:submit="addFloor" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Floor Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="floorName" placeholder="e.g. Ground Floor">
                            @error('floorName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Floor Number <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" wire:model="floorNumber" placeholder="0 for Ground, 1 for First">
                            @error('floorNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" wire:loading.attr="disabled">
                                <span wire:loading wire:target="addFloor" class="ft-btn-spinner"></span>
                                <span wire:loading.remove wire:target="addFloor">{{ $editingFloorId ? 'Update' : 'Add' }}</span>
                            </button>
                        </div>
                        @if($editingFloorId)
                        <div class="col-12 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" wire:click="cancelEditFloor">Cancel Edit</button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="goToPreviousTab()">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </button>
                <button class="btn btn-primary" wire:click="goToNextTab()">
                    Next <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB Rooms --}}
    @if($activeTab === 'rooms')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-door-open me-2"></i>Room Management</h6></div>
        <div class="card-body">

            @if($listing && $listing->floors->count())
            {{-- Room Grid per Floor --}}
            @foreach($listing->floors as $floor)
            <div class="mb-4">
                <h6 class="fw-700 text-primary mb-2">
                    <i class="bi bi-layers me-1"></i> {{ $floor->name }}
                </h6>
                @if($floor->rooms->count())
                <div class="table-responsive mb-2">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Room #</th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Photos</th>
                                <th>Available</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($floor->rooms as $room)
                            <tr>
                                <td class="fw-600">{{ $room->room_number }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($room->room_type ?? 'N/A') }}</span></td>
                                <td>{{ $room->capacity }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($room->images as $img)
                                        <img src="{{ $img->url }}" class="rounded border" style="width:50px;height:50px;object-fit:cover;" alt="Room Photo">
                                        @endforeach
                                        @if($room->images->count() === 0)
                                        <span class="text-muted small">No photos</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $room->available_beds > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $room->available_beds }} beds free
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1" wire:click="editRoom('{{ $room->id }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="deleteRoom('{{ $room->id }}')"
                                        wire:confirm="Delete this room?">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-muted small mb-2">No rooms on this floor yet.</p>
                @endif
            </div>
            @endforeach
            @else
            <div class="alert alert-info mb-4"><i class="bi bi-info-circle me-2"></i>No floors available for rooms. Please add a floor first.</div>
            @endif

            {{-- Add Room Form --}}
            @if($listing && $listing->floors->count())
            <div class="card bg-light border-0 mt-4">
                <div class="card-body">
                    <h6 class="fw-600 mb-3">{{ $editingRoomId ? 'Edit Room' : 'Add Room' }}</h6>
                    <form wire:submit="addRoom" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Floor <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="roomFloorId">
                                <option value="">Select Floor</option>
                                @foreach($listing->floors as $floor)
                                    <option value="{{ $floor->id }}">{{ $floor->name }}</option>
                                @endforeach
                            </select>
                            @error('roomFloorId') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Room Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="roomNumber" placeholder="e.g. 101">
                            @error('roomNumber') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Room Type <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model.live="roomType">
                                <option value="1BHK">1BHK</option>
                                <option value="2BHK">2BHK</option>
                                <option value="3BHK">3BHK</option>
                                <option value="Single Room">Single Room</option>
                                <option value="Shared Room">Shared Room</option>
                                <option value="Dormitory">Dormitory</option>
                            </select>
                            <div class="form-text">
                                @if($roomType == 'Single Room')
                                    <strong>Single Room</strong>: An entire private room for one person.
                                @elseif($roomType == 'Shared Room')
                                    <strong>Shared Room</strong>: A room shared with other people.
                                @elseif($roomType == 'Dormitory')
                                    <strong>Dormitory</strong>: A large room with multiple beds.
                                @elseif(in_array($roomType, ['1BHK', '2BHK', '3BHK']))
                                    <strong>{{ $roomType }}</strong>: An entire apartment setup.
                                @endif
                            </div>
                            @error('roomType') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Capacity (Beds) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" wire:model="capacity" min="1">
                            @error('capacity') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Security Deposit per bed (₹)</label>
                            <!-- <div class="input-group">
                                <button type="button" class="btn btn-outline-secondary" wire:click.prevent="decrementSecurityDeposit">-</button>
                                <input type="number" class="form-control text-center" wire:model="securityDeposit" min="500" step="500">
                                <button type="button" class="btn btn-outline-secondary" wire:click.prevent="incrementSecurityDeposit">+</button>
                            </div> -->

                            <div class="input-group">
                                <input type="number" class="form-control text-center" wire:model="securityDeposit">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Upload Photos (Minimum 3 Required) <span class="text-danger">*</span></label>
                            
                            <div class="mt-2">
                                <input type="file" wire:model="newRoomImages" class="form-control" accept="image/*" multiple>
                            </div>
                            @error('newRoomImages') <span class="text-danger small">{{ $message }}</span> @enderror
                            @error('newRoomImages.*') <span class="text-danger small">{{ $message }}</span> @enderror
                            
                            @if(count($existingRoomImages) > 0)
                            <div class="mt-3">
                                <label class="form-label small fw-600 text-muted">Current Images</label>
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($existingRoomImages as $img)
                                    <div class="position-relative" style="width:70px;">
                                        <img src="{{ asset('storage/' . $img['image_path']) }}" class="rounded" style="width:70px;height:70px;object-fit:cover;">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0"
                                            wire:click="removeExistingRoomImage({{ $img['id'] }})" style="padding:0;width:20px;height:20px;font-size:10px;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            @if($newRoomImages)
                            <div class="d-flex flex-wrap gap-3 mt-3">
                                @foreach($newRoomImages as $index => $tempImg)
                                <div class="position-relative" style="width: 120px; height: 120px; border-radius: 8px; overflow: hidden; border: 1px solid #ddd;">
                                    <img src="{{ $tempImg->temporaryUrl() }}" class="w-100 h-100" style="object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 p-0 rounded-circle d-flex align-items-center justify-content-center" style="width: 22px; height: 22px;" wire:click="removeNewRoomImage({{ $index }})"><i class="bi bi-x"></i></button>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="col-12 mt-4 text-end">
                            @if($editingRoomId)
                                <button type="button" class="btn btn-outline-secondary me-2" wire:click="cancelEditRoom">Cancel Edit</button>
                            @endif
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading wire:target="addRoom" class="ft-btn-spinner"></span>
                                <span wire:loading.remove wire:target="addRoom">{{ $editingRoomId ? 'Update Room' : 'Save Room' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="goToPreviousTab()">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </button>
                <button class="btn btn-primary" wire:click="goToNextTab()">
                    Next <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB Packages --}}
    @if($activeTab === 'packages')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-card-checklist me-2"></i>Packages</h6>
            <span class="badge bg-primary">{{ $packages->count() }} packages</span>
        </div>
        <div class="card-body">
            @if($packages->count())
            <div class="table-responsive mb-4">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Package Type</th>
                            @if($category && $category->has_rooms)
                            <th>Room</th>
                            <th>Occupancy Type</th>
                            @endif
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Features</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                        <tr>
                            <td><strong>{{ $package->name }}</strong></td>
                            <td class="text-capitalize">{{ str_replace('_', ' ', $package->type ?? 'N/A') }}</td>
                            @if($category && $category->has_rooms)
                            <td>{{ $package->room ? $package->room->room_number . ' (' . ucfirst($package->room->room_type ?? 'room') . ')' : 'All Rooms' }}</td>
                            <td class="text-capitalize">{{ str_replace('_', ' ', $package->occupancy_type ?? 'standard') }}</td>
                            @endif
                            <td>{{ $package->duration_days }} days</td>
                            <td>₹{{ number_format($package->price) }}</td>
                            <td>
                                @php $features = is_array($package->features) ? $package->features : (json_decode($package->features, true) ?? []); @endphp
                                @if(is_array($features) && count($features) > 0)
                                    <small>{{ implode(', ', $features) }}</small>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                                @if($package->meal_plans && is_array($package->meal_plans))
                                    @php
                                        $meals = [];
                                        if($package->meal_plans['has_breakfast'] ?? false) $meals[] = 'Breakfast';
                                        if($package->meal_plans['has_lunch'] ?? false) $meals[] = 'Lunch';
                                        if($package->meal_plans['has_dinner'] ?? false) $meals[] = 'Dinner';
                                        $mType = $package->meal_plans['meal_type'] ?? 'none';
                                    @endphp
                                    @if(count($meals) > 0)
                                        <div class="mt-1 small">
                                            <strong>Meals:</strong> {{ implode(', ', $meals) }}
                                            @if($mType !== 'none')
                                                <span class="badge bg-secondary text-capitalize">{{ str_replace('_', '-', $mType) }}</span>
                                            @endif
                                        </div>
                                    @endif
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" wire:click="editPackage('{{ $package->id }}')">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="deletePackage('{{ $package->id }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <form wire:submit="savePackage">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Package Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="packageName">
                        @error('packageName') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Package Type</label>
                        <select class="form-select" wire:model.live="packageType">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="half_yearly">Half Yearly</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Price (₹)</label>
                        <input type="number" step="0.01" class="form-control" wire:model="packagePrice" min="0">
                    </div>
                    @if($packageType === 'custom')
                    <div class="col-md-3">
                        <label class="form-label">Duration (Days)</label>
                        <input type="number" class="form-control" wire:model="packageDurationDays" min="1">
                    </div>
                    @endif

                    @if($category && in_array(strtolower($category->name), ['pg / hostel', 'room rental']))
                    <div class="col-md-4">
                        <label class="form-label">Floor</label>
                        <select class="form-select" wire:model.live="packageFloorId">
                            <option value="">Select Floor</option>
                            @if($listing && $listing->floors)
                                @foreach($listing->floors as $floor)
                                    <option value="{{ $floor->id }}" wire:key="pkg-floor-{{ $floor->id }}">Floor {{ $floor->floor_number }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Room</label>
                        <select class="form-select" wire:model="packageRoomId">
                            <option value="">Select Room</option>
                            @php
                                $filteredRooms = $packageFloorId ? $rooms->where('floor_id', $packageFloorId) : $rooms;
                            @endphp
                            @foreach($filteredRooms as $room)
                                <option value="{{ $room->id }}" wire:key="pkg-room-{{ $room->id }}">{{ $room->room_number }} - {{ ucfirst($room->room_type ?? 'room') }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Select which room this package applies to.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Occupancy Pricing</label>
                        <select class="form-select" wire:model="packageOccupancyType">
                            <option value="full_room">Full Room</option>
                            <option value="per_bed">Per Bed</option>
                        </select>
                        <div class="form-text"><strong>Per Bed</strong>: Price per individual bed. <strong>Full Room</strong>: Price for the entire room.</div>
                    </div>
                    @endif

                    @if($category && $category->has_rooms)
                    <div class="col-12">
                        <label class="form-label mb-2 d-block">Meal Plan Details <span class="text-muted fs-12">(Optional)</span></label>
                        <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mealBreakfastGeneric" wire:model="packageHasBreakfast">
                                <label class="form-check-label" for="mealBreakfastGeneric">Breakfast</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mealLunchGeneric" wire:model="packageHasLunch">
                                <label class="form-check-label" for="mealLunchGeneric">Lunch</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mealDinnerGeneric" wire:model="packageHasDinner">
                                <label class="form-check-label" for="mealDinnerGeneric">Dinner</label>
                            </div>
                            <div class="ms-md-auto d-flex align-items-center gap-2">
                                <label class="form-label mb-0 fs-14 whitespace-nowrap" style="white-space: nowrap;">Food Type:</label>
                                <select class="form-select form-select-sm" style="width: 130px;" wire:model="packageMealType">
                                    <option value="none">None</option>
                                    <option value="veg">Veg</option>
                                    <option value="non_veg">Non-Veg</option>
                                    <option value="both">Veg & Non-Veg</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label">Features <span class="text-muted fs-12">(One per line)</span></label>
                        <textarea class="form-control" wire:model="packageFeaturesInput" rows="3" placeholder="wifi&#10;parking&#10;showers"></textarea>
                    </div>
                </div>
                @error('submitError')
                    <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
                <div class="mt-4 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="goToPreviousTab()">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </button>
                    @if($editingPackageId)
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="savePackage" class="ft-btn-spinner"></span>
                            <span wire:loading.remove wire:target="savePackage">Update Package</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancelEditPackage" wire:loading.attr="disabled">Cancel</button>
                    @else
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                            <span wire:loading wire:target="savePackage" class="ft-btn-spinner"></span>
                            <span wire:loading.remove wire:target="savePackage">Add Package</span>
                        </button>
                    @endif
                    <button type="button" class="btn btn-success" wire:click="saveCustomFields" wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveCustomFields" class="ft-btn-spinner"></span>
                        <i class="bi bi-check-circle me-1" wire:loading.remove wire:target="saveCustomFields"></i>
                        <span wire:loading.remove wire:target="saveCustomFields">Finish Listing</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- TAB 4: Custom Fields --}}
    @if($activeTab === 'fields')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Additional Details</h6></div>
        <div class="card-body">
            @if($customFields->count())
            <form wire:submit="saveCustomFields">
                <div class="row g-3">
                    @foreach($customFields as $field)
                    <div class="col-md-6">
                        <label class="form-label fw-600">
                            {{ $field->label }}
                            @if($field->is_required) <span class="text-danger">*</span> @endif
                        </label>
                        @if($field->field_type === 'text')
                            <input type="text" class="form-control" wire:model="metaValues.{{ $field->id }}">
                        @elseif($field->field_type === 'number')
                            <input type="number" class="form-control" wire:model="metaValues.{{ $field->id }}">
                        @elseif($field->field_type === 'textarea')
                            <textarea class="form-control" wire:model="metaValues.{{ $field->id }}" rows="3"></textarea>
                        @elseif($field->field_type === 'checkbox')
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox"
                                    wire:model="metaValues.{{ $field->id }}"
                                    id="field_{{ $field->id }}" value="1">
                                <label class="form-check-label" for="field_{{ $field->id }}">Yes</label>
                            </div>
                        @elseif($field->field_type === 'date')
                            <input type="date" class="form-control" wire:model="metaValues.{{ $field->id }}">
                        @endif
                    </div>
                    @endforeach
                </div>
                @error('submitError')
                    <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-outline-secondary" type="button" wire:click="goToPreviousTab()">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <span wire:loading.remove wire:target="saveCustomFields">Save & Next<i class="bi bi-arrow-right ms-2"></i></span>
                        <span wire:loading wire:target="saveCustomFields"><span class="spinner-border spinner-border-sm"></span> Saving...</span>
                    </button>
                </div>
            </form>
            @else
            <div class="alert alert-secondary">No extra details required for this category.</div>
            <div class="mt-3">
                <button type="button" class="btn btn-primary px-4" wire:click="goToNextTab">
                    Continue to Keywords <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- TAB: Search Keywords --}}
    @if($activeTab === 'keywords')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-search me-2"></i>Search Keywords</h6></div>
        <div class="card-body">
            <form wire:submit="saveKeywords">
                <div class="mb-3">
                    <label class="form-label fw-600">Search Keywords (Optional)</label>
                    <div x-data="{
                            tags: $wire.entangle('searchKeywords'),
                            newTag: '',
                            get tagArray() {
                                return this.tags ? String(this.tags).split(',').map(t => t.trim()).filter(t => t) : [];
                            },
                            addTag() {
                                let val = this.newTag.trim();
                                if (val) {
                                    let current = this.tagArray;
                                    if (!current.includes(val)) {
                                        current.push(val);
                                        this.tags = current.join(', ');
                                    }
                                }
                                this.newTag = '';
                            },
                            removeTag(index) {
                                let current = this.tagArray;
                                current.splice(index, 1);
                                this.tags = current.join(', ');
                            }
                        }">
                        <div class="d-flex flex-wrap gap-2 p-2 border rounded align-items-center" style="min-height: 50px; border-color: #dee2e6; background-color: #fcfcfc;">
                            <template x-for="(tag, index) in tagArray" :key="index">
                                <span class="badge bg-primary d-flex align-items-center gap-1" style="font-size: 14px; padding: 6px 12px; border-radius: 20px;">
                                    <span x-text="tag"></span>
                                    <i class="bi bi-x-circle-fill text-white ms-1" style="cursor: pointer; opacity: 0.7;" @click="removeTag(index)" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'"></i>
                                </span>
                            </template>
                            <input type="text" x-model="newTag" @keydown.enter.prevent="addTag" @keydown.comma.prevent="addTag" @blur="addTag"
                                class="border-0 flex-grow-1 p-1" style="outline: none; min-width: 250px; background: transparent;"
                                placeholder="Type a keyword and press Enter or Comma...">
                        </div>
                    </div>
                    <div class="form-text mt-2">These keywords help customers find your listing when they search. If left blank, we will generate them automatically based on your title and category.</div>
                </div>
                @error('submitError')
                    <div class="alert alert-danger mt-3">{{ $message }}</div>
                @enderror
                
                <div class="mt-4 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary px-4" wire:click="goToPreviousTab">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </button>
                    <button type="submit" class="btn btn-primary px-5">
                        <span wire:loading.remove wire:target="saveKeywords"><i class="bi bi-save me-2"></i>Save Keywords</span>
                        <span wire:loading wire:target="saveKeywords"><span class="spinner-border spinner-border-sm"></span> Saving...</span>
                    </button>
                    <button type="button" class="btn btn-success px-5 ms-auto" wire:click="submitListing">
                        <i class="bi bi-check-circle me-2"></i>Submit Listing for Review
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(config('services.google_maps.key'))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.key') }}&libraries=places&callback=initAutocomplete" async defer></script>
    @else
        <div class="alert alert-warning m-3">Google Maps API key is not configured. Location auto-fetch will not work.</div>
    @endif
</div>

@script
<script>
    function initAutocomplete() {
        const input = document.getElementById('addressInput');
        if (!input) return;

        const autocomplete = new google.maps.places.Autocomplete(input, {
            fields: ["address_components", "geometry", "formatted_address"],
            types: ["geocode", "establishment"]
        });

        // Prevent form submission on Enter key in the search box
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
            }
        });

        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (!place.geometry) return;

            let city = '';
            let state = '';
            let pincode = '';

            for (const component of place.address_components) {
                const componentType = component.types[0];
                if (componentType === 'locality') {
                    city = component.long_name;
                } else if (componentType === 'administrative_area_level_1') {
                    state = component.long_name;
                } else if (componentType === 'postal_code') {
                    pincode = component.long_name;
                }
            }

            $wire.set('address', place.formatted_address, false);
            $wire.set('city', city, false);
            $wire.set('state', state, false);
            $wire.set('pincode', pincode, false);
            $wire.set('latitude', place.geometry.location.lat(), false);
            $wire.set('longitude', place.geometry.location.lng(), false);
        });

        const detectBtn = document.getElementById('detectLocationBtn');
        if (detectBtn) {
            detectBtn.addEventListener('click', function() {
                if (navigator.geolocation) {
                    detectBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Detecting...';
                    detectBtn.disabled = true;

                    navigator.geolocation.getCurrentPosition(function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        const geocoder = new google.maps.Geocoder();
                        geocoder.geocode({ location: { lat: lat, lng: lng } }, function(results, status) {
                            detectBtn.innerHTML = '<i class="bi bi-crosshair"></i> Detect';
                            detectBtn.disabled = false;

                            if (status === "OK" && results[0]) {
                                const place = results[0];
                                let city = '';
                                let state = '';
                                let pincode = '';

                                for (const component of place.address_components) {
                                    const componentType = component.types[0];
                                    if (componentType === 'locality') {
                                        city = component.long_name;
                                    } else if (componentType === 'administrative_area_level_1') {
                                        state = component.long_name;
                                    } else if (componentType === 'postal_code') {
                                        pincode = component.long_name;
                                    }
                                }

                                input.value = place.formatted_address;
                                $wire.set('address', place.formatted_address, false);
                                $wire.set('city', city, false);
                                $wire.set('state', state, false);
                                $wire.set('pincode', pincode, false);
                                $wire.set('latitude', lat, false);
                                $wire.set('longitude', lng, false);
                            } else {
                                alert("Could not reverse geocode your location: " + status);
                            }
                        });
                    }, function(error) {
                        detectBtn.innerHTML = '<i class="bi bi-crosshair"></i> Detect';
                        detectBtn.disabled = false;
                        alert("Error getting location: " + error.message);
                    });
                } else {
                    alert("Geolocation is not supported by this browser.");
                }
            });
        }
    }

    if (typeof google !== 'undefined') {
        initAutocomplete();
    } else {
        window.initAutocomplete = initAutocomplete;
    }
</script>
@endscript
