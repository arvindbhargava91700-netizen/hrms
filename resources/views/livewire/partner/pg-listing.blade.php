<div>
    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'property' ? 'active' : '' }}" wire:click="$set('activeTab','property')">
                <i class="bi bi-building me-1"></i> Property Info
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'floors' ? 'active' : '' }}"
                wire:click="$set('activeTab','floors')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-layers me-1"></i> Floors
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link {{ $activeTab === 'rooms' ? 'active' : '' }}"
                wire:click="$set('activeTab','rooms')" {{ !$listingId ? 'disabled' : '' }}>
                <i class="bi bi-door-open me-1"></i> Rooms
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

    {{-- TAB 1: Property Info --}}
    @if($activeTab === 'property')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-building me-2"></i>Property Information</h6></div>
        <div class="card-body">
            <form wire:submit="saveProperty">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600">Property Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="title" placeholder="e.g. Sunrise Boys PG">
                        @error('title') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600">Description</label>
                        <textarea class="form-control" wire:model="description" rows="4"
                            placeholder="Describe amenities: WiFi, food, laundry, etc."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600">Full Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" wire:model="address" placeholder="Street, City, PIN">
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
                        <i class="bi bi-arrow-right me-2"></i>Save & Next: Floors
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- TAB 2: Floors --}}
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

            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-600 mb-3">Add Floor</h6>
                    <form wire:submit="addFloor" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Floor Number</label>
                            <input type="number" class="form-control" wire:model="floorNumber" placeholder="0 = Ground">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Floor Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="floorName" placeholder="e.g. Ground Floor, 1st Floor">
                            @error('floorName') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">Add Floor</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="$set('activeTab','property')">
                    <i class="bi bi-arrow-left me-2"></i>Back
                </button>
                <button class="btn btn-primary" wire:click="$set('activeTab','rooms')">
                    Next: Rooms <i class="bi bi-arrow-right ms-2"></i>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB 3: Rooms --}}
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
                                <td><span class="badge bg-primary-subtle text-primary">{{ $room->images->count() }} photos</span></td>
                                <td>
                                    <span class="badge {{ $room->available_beds > 0 ? 'bg-success' : 'bg-danger' }}">
                                        {{ $room->available_beds }} beds free
                                    </span>
                                </td>
                                <td>
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

                <button class="btn btn-sm btn-outline-primary" wire:click="selectFloor({{ $floor->id }})">
                    <i class="bi bi-plus-lg me-1"></i>Add Room to {{ $floor->name }}
                </button>
            </div>
            @endforeach
            @else
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Please add floors first before adding rooms.
            </div>
            @endif

            {{-- Add Room Form --}}
            @if($roomFloorId)
            <div class="card bg-light border-0 mt-3">
                <div class="card-body">
                    <h6 class="fw-600 mb-3">
                        Add Room to:
                        <span class="text-primary">{{ $listing?->floors->where('id',$roomFloorId)->first()?->name }}</span>
                    </h6>
                    <form wire:submit="addRoom" class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Room No. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="roomNumber" placeholder="101">
                            @error('roomNumber') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select class="form-select" wire:model.live="roomType">
                                <option value="single">Single</option>
                                <option value="double">Double</option>
                                <option value="triple">Triple</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Total Seats <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" wire:model="capacity" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Security (₹)</label>
                            <input type="number" class="form-control" wire:model="securityDeposit" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Room Photos</label>
                            <input type="file" class="form-control" wire:model="roomImages" multiple accept="image/*">
                            <div class="form-text">Upload multiple photos for this room.</div>
                            @error('roomImages.*') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-plus-lg me-1"></i>Add Room
                            </button>
                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('roomFloorId', null)">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-outline-secondary" wire:click="$set('activeTab','floors')">
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
                            <th>Billing Type</th>
                            <th>Room</th>
                            <th>Occupancy Type</th>
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
                            <td>{{ $package->room->room_number ?? 'All rooms' }}</td>
                            <td class="text-capitalize">{{ str_replace('_', ' ', $package->occupancy_type ?? 'standard') }}</td>
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
                    <div class="col-md-3">
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
                    <div class="col-md-3">
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
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Occupancy Pricing</label>
                        <select class="form-select" wire:model="packageOccupancyType">
                            <option value="full_room">Full Room</option>
                            <option value="per_bed">Per Bed</option>
                        </select>
                        <div class="form-text"><strong>Per Bed</strong>: Price for an individual bed. <strong>Full Room</strong>: Price for the entire room.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label mb-2 d-block">Meal Plan Details <span class="text-muted fs-12">(Optional)</span></label>
                        <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mealBreakfast" wire:model="packageHasBreakfast">
                                <label class="form-check-label" for="mealBreakfast">Breakfast</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mealLunch" wire:model="packageHasLunch">
                                <label class="form-check-label" for="mealLunch">Lunch</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="mealDinner" wire:model="packageHasDinner">
                                <label class="form-check-label" for="mealDinner">Dinner</label>
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
                    <div class="col-12">
                        <label class="form-label">Features <span class="text-muted fs-12">(One per line)</span></label>
                        <textarea class="form-control" wire:model="packageFeaturesInput" rows="3" placeholder="WiFi&#10;Water&#10;Laundry"></textarea>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" wire:click="$set('activeTab','rooms')">Back</button>
                    @if($editingPackageId)
                        <button type="submit" class="btn btn-primary">Update Package</button>
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancelEditPackage">Cancel</button>
                    @else
                        <button type="submit" class="btn btn-primary">Add Package</button>
                    @endif
                    <button type="button" class="btn btn-success" wire:click="saveCustomFields">Finish Listing</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- TAB 4: Custom Fields --}}
    @if($activeTab === 'fields')
    <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-list-check me-2"></i>Property Details</h6></div>
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
                    <button type="button" class="btn btn-outline-secondary" wire:click="$set('activeTab','rooms')">
                        <i class="bi bi-arrow-left me-2"></i>Back
                    </button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-2"></i>Submit Listing
                    </button>
                </div>
            </form>
            @else
            <div class="alert alert-secondary mb-3">No extra details required.</div>
            <a href="{{ route('partner.listings') }}" class="btn btn-success">
                <i class="bi bi-check-circle me-2"></i>Done – Go to My Listings
            </a>
            @endif
        </div>
    </div>
    @endif
</div>
