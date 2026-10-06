<div>
    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'basic' ? 'active' : '' }}" wire:click="$set('activeTab','basic')">
                <i class="bi bi-info-circle me-1"></i> Basic Info
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'fields' ? 'active' : '' }}"
                wire:click="$set('activeTab','fields')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-list-check me-1"></i> Details
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'packages' ? 'active' : '' }}"
                wire:click="$set('activeTab','packages')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-card-checklist me-1"></i> Packages
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
        <div class="card-header">
            <h6 class="mb-0">
                @php $genCatIcon = $category?->icon ?? 'bi-grid'; @endphp
                @if(\Illuminate\Support\Str::startsWith($genCatIcon, 'bi-'))
                    <i class="bi {{ $genCatIcon }} me-2"></i>
                @else
                    <img src="{{ asset('storage/' . $genCatIcon) }}" alt="icon" style="width: 1.25em; height: 1.25em; vertical-align: -0.2em;" class="me-2">
                @endif
                {{ $category?->name ?? 'New Listing' }} – Basic Info
            </h6>
        </div>
        <div class="card-body">
            <form wire:submit="saveBasic">
                <div class="row g-3">
                    @if(!$listingId)
                    <div class="col-md-6">
                        <label class="form-label fw-600">Category <span class="text-danger">*</span></label>
                        <select class="form-select" wire:model.live="categoryId">
                            <option value="">Select Category</option>
                            @foreach($allCategories as $cat)
                                @if(!in_array(strtolower($cat->name), ['gym', 'pg / hostel', 'room rental']))
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        @error('categoryId') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    @endif
                    <div class="col-12">
                        <label class="form-label fw-600">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="title" placeholder="Your listing name">
                        @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600">Description</label>
                        <textarea class="form-control" wire:model="description" rows="4"
                            placeholder="Describe your service..."></textarea>
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

                    @if($listing && $listing->images->count())
                    <div class="col-12">
                        <label class="form-label fw-600">Current Photos</label>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($listing->images as $img)
                            <div class="position-relative">
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
                        <div class="form-text">JPG, PNG. Max 2MB each.</div>
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
                        <i class="bi bi-arrow-right me-2"></i>Save & Next: Details
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- TAB 2: Packages --}}
    @if($activeTab === 'packages')
    <div class="card mb-4">
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
                        <input type="text" class="form-control" wire:model="packageName" placeholder="e.g. Monthly Standard">
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
                        <label class="form-label">Features<span class="text-muted fs-12">(One per line)</span></label>
                        <textarea class="form-control" wire:model="packageFeaturesInput" rows="3" placeholder="Access to WiFi&#10;Food included"></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="$set('activeTab','fields')">Back</button>
                    <button type="submit" class="btn btn-primary">Add Package</button>
                    <button type="button" class="btn btn-success" wire:click="saveCustomFields">Finish Listing</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- TAB 2: Custom Fields / Details --}}
    @if($activeTab === 'fields')
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="bi bi-list-check me-2"></i>
                {{ $category?->name ?? '' }} – Details
            </h6>
        </div>
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
                    <button type="button" class="btn btn-outline-secondary" wire:click="$set('activeTab','basic')">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-2"></i>Submit Listing
                    </button>
                </div>
            </form>
            @else
            <div class="alert alert-info mb-3">No additional details required for this category.</div>
            <a href="{{ route('partner.listings') }}" class="btn btn-success">
                <i class="bi bi-check-circle me-2"></i>Done – Go to My Listings
            </a>
            @endif
        </div>
    </div>
    @endif
</div>
