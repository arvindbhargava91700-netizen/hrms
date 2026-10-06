<div>
    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-4" id="gymTabs">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'basic' ? 'active' : '' }}" wire:click="$set('activeTab','basic')">
                <i class="bi bi-image me-1"></i> Basic Info
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'shifts' ? 'active' : '' }}"
                wire:click="$set('activeTab','shifts')"
                {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-clock me-1"></i> Shifts
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'trainers' ? 'active' : '' }}"
                wire:click="$set('activeTab','trainers')"
                {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-person-badge me-1"></i> Trainers
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'packages' ? 'active' : '' }}"
                wire:click="$set('activeTab','packages')"
                {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-card-checklist me-1"></i> Packages
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'fields' ? 'active' : '' }}"
                wire:click="$set('activeTab','fields')"
                {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-list-check me-1"></i> Details
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
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Gym Basic Information</h6></div>
        <div class="card-body">
            <form wire:submit="saveBasic">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600">Gym Name / Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="title" placeholder="e.g. Gold's Fitness Center">
                        @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600">Description</label>
                        <textarea class="form-control" wire:model="description" rows="4"
                            placeholder="Describe your gym, equipment, facilities..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600">Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="address" placeholder="Full address">
                        @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
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

                    {{-- Uploaded Images --}}
                    @if($listing && $listing->images->count())
                    <div class="col-12">
                        <label class="form-label fw-600">Current Images</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($listing->images as $img)
                            <div class="position-relative" style="width:100px;">
                                <img src="{{ $img->url }}" class="rounded" style="width:100px;height:75px;object-fit:cover;">
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
                        <label class="form-label fw-600">Upload Photos</label>
                        <input type="file" class="form-control" wire:model="newImages" multiple accept="image/*">
                        <div class="form-text">JPG, PNG, WebP. Max 2MB each.</div>
                        @error('newImages.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                        {{-- Preview New Images --}}
                        @if($newImages)
                        <div class="mt-3 d-flex flex-wrap gap-2">
                            @foreach($newImages as $img)
                            <div class="position-relative">
                                <img src="{{ $img->temporaryUrl() }}" class="rounded shadow-sm border" style="width:100px;height:75px;object-fit:cover;">
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-arrow-right me-2"></i>Save & Next: Shifts
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- TAB 2: Shifts --}}
    @if($activeTab === 'shifts')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-clock me-2"></i>Gym Shifts</h6></div>
        <div class="card-body">
            {{-- Existing Shifts --}}
            @if($listing && $listing->gymShifts->count())
            <div class="row g-3 mb-4">
                @foreach($listing->gymShifts as $shift)
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid var(--primary) !important;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-700 mb-1">{{ $shift->shift_label }}</h6>
                                    <div class="text-muted small">{{ $shift->start_time }} – {{ $shift->end_time }}</div>
                                    <div class="mt-2">
                                        <span class="badge bg-primary-subtle text-primary me-1">
                                            <i class="bi bi-people me-1"></i>Max {{ $shift->max_members }}
                                        </span>
                                        <span class="badge bg-success-subtle text-success">
                                            ₹{{ number_format($shift->fee) }}/month
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
                        <div class="col-md-2">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" wire:model="startTime">
                            @error('startTime') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" wire:model="endTime">
                            @error('endTime') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Max Members</label>
                            <input type="number" class="form-control" wire:model="maxMembers" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fee / Month (₹)</label>
                            <input type="number" class="form-control" wire:model="shiftFee" min="0">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Add</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="$set('activeTab','basic')">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </button>
                <button class="btn btn-primary" wire:click="$set('activeTab','trainers')">
                    Next: Trainers <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 3: Trainers --}}
    @if($activeTab === 'trainers')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Trainers</h6></div>
        <div class="card-body">
            {{-- Existing Trainers --}}
            @if($listing && $listing->gymTrainers->count())
            <div class="row g-3 mb-4">
                @foreach($listing->gymTrainers as $trainer)
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm text-center h-100">
                        <div class="card-body">
                            <img src="{{ $trainer->photo_url }}" class="rounded-circle mb-2" style="width:60px;height:60px;object-fit:cover;">
                            <div class="fw-600">{{ $trainer->name }}</div>
                            <div class="text-muted small">{{ $trainer->specialization ?? 'General Fitness' }}</div>
                            <div class="badge bg-secondary-subtle text-secondary mt-1">{{ $trainer->experience_years }} yrs exp</div>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-danger" wire:click="deleteTrainer({{ $trainer->id }})"
                                    wire:confirm="Remove this trainer?">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="alert alert-info mb-4"><i class="bi bi-info-circle me-2"></i>No trainers added yet.</div>
            @endif

            {{-- Add Trainer Form --}}
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-600 mb-3">Add Trainer</h6>
                    <form wire:submit="addTrainer" class="row g-3">
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
                        <div class="col-md-3">
                            <label class="form-label">Photo</label>
                            <input type="file" class="form-control" wire:model="trainerPhoto" accept="image/*">
                            @error('trainerPhoto') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Add</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="$set('activeTab','shifts')">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </button>
                <button class="btn btn-primary" wire:click="$set('activeTab','packages')">
                    Next: Packages <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 4: Packages --}}
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
                            <th>Type</th>
                            <th>Duration</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($packages as $package)
                        <tr>
                            <td>{{ $package->name }}</td>
                            <td class="text-capitalize">{{ str_replace('_', ' ', $package->type) }}</td>
                            <td>{{ $package->duration_days }} days</td>
                            <td>₹{{ number_format($package->price) }}</td>
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
                        <input type="text" class="form-control" wire:model="packageName" placeholder="e.g. Monthly Basic">
                        @error('packageName') <div class="text-danger small">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Duration (Days)</label>
                        <input type="number" class="form-control" wire:model="packageDurationDays" min="1">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Price (₹)</label>
                        <input type="number" step="0.01" class="form-control" wire:model="packagePrice" min="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Billing Type</label>
                        <select class="form-select" wire:model="packageType">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="half_yearly">Half Yearly</option>
                            <option value="yearly">Yearly</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Features <span class="text-muted fs-12">(One per line)</span></label>
                        <textarea class="form-control" wire:model="packageFeaturesInput" rows="3" placeholder="eg: wifi&#10;parking&#10;showers"></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="$set('activeTab','trainers')">Back</button>
                    <button type="submit" class="btn btn-primary">Add Package</button>
                    <button type="button" class="btn btn-success" wire:click="saveCustomFields">Finish Listing</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- TAB 4: Custom Fields --}}
    @if($activeTab === 'fields')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Gym Details</h6></div>
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
                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-outline-secondary" type="button" wire:click="$set('activeTab','trainers')">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-2"></i>Submit Listing
                    </button>
                </div>
            </form>
            @else
            <div class="alert alert-secondary">No extra details required for this category.</div>
            <div class="mt-3">
                <a href="{{ route('partner.listings') }}" class="btn btn-success">
                    <i class="bi bi-check-circle me-2"></i>Done – Go to My Listings
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
