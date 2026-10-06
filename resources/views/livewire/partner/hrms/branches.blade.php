<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Manage Branches</h4>
        @if(auth()->user()->canAccess('branch_create'))
        <button wire:click="create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Branch
        </button>
        @endif
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Location (Lat, Lng)</th>
                            <th>Radius</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $branch->name }}</div>
                                    @if($branch->manager_id)
                                        <div class="text-muted fs-12">Manager: {{ $branch->manager->name ?? 'Unknown' }}</div>
                                    @endif
                                </td>
                                <td>{{ Str::limit($branch->address, 30) }}</td>
                                <td>{{ $branch->lat }}, {{ $branch->lng }}</td>
                                <td>{{ $branch->radius }}m</td>
                                <td>
                                    <span class="badge bg-{{ $branch->status === 'active' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $branch->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($branch->status) }}
                                    </span>
                                </td>
                                <td class="text-end">

                                    <button wire:click="view({{ $branch->id }})" class="btn btn-sm btn-light text-info me-1" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                        @if(auth()->user()->canAccess('branch_update'))
                                    <button wire:click="edit({{ $branch->id }})" class="btn btn-sm btn-light text-primary me-1" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                       @endif
                                        @if(auth()->user()->canAccess('branch_delete'))
                                    <button wire:click="delete({{ $branch->id }})" wire:confirm="Are you sure you want to delete this branch?" class="btn btn-sm btn-light text-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No branches found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($isOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0">
                    <div class="modal-header bg-light border-0">
                        <h5 class="modal-title">{{ $branchId ? 'Edit Branch' : 'Add New Branch' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body p-0" x-data="{ tab: 'basic' }">
                        <!-- Tabs Navigation -->
                        <div class="bg-light px-4 pt-3 border-bottom">
                            <ul class="nav nav-tabs border-bottom-0 gap-3">
                                <li class="nav-item">
                                    <button type="button" class="nav-link border-0 fw-bold px-3 py-3" :class="tab === 'basic' ? 'active text-primary border-bottom border-primary border-3 bg-transparent' : 'text-muted bg-transparent hover-bg-light'" @click="tab = 'basic'">
                                        <i class="bi bi-geo-alt me-1"></i> Branch Location & Basic Info
                                    </button>
                                </li>
                                <li class="nav-item">
                                    <button type="button" class="nav-link border-0 fw-bold px-3 py-3" :class="tab === 'profile' ? 'active text-primary border-bottom border-primary border-3 bg-transparent' : 'text-muted bg-transparent hover-bg-light'" @click="tab = 'profile'">
                                        <i class="bi bi-building me-1"></i> Company Profile
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <form wire:submit.prevent="store" class="p-4">
                            <!-- Basic Info Tab -->
                            <div x-show="tab === 'basic'" class="row g-4" x-transition>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Branch Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-lg bg-light border-0" wire:model="name" placeholder="e.g. Headquarters" required>
                                    @error('name') <span class="text-danger fs-12 mt-1 d-block">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Branch Manager</label>
                                    <select class="form-select form-select-lg bg-light border-0" wire:model="manager_id">
                                        <option value="">-- Unassigned --</option>
                                        @foreach($managers as $m)
                                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->role }})</option>
                                        @endforeach
                                    </select>
                                    @error('manager_id') <span class="text-danger fs-12 mt-1 d-block">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Complete Address</label>
                                    <textarea class="form-control bg-light border-0" wire:model="address" rows="2" placeholder="Street, City, State..."></textarea>
                                </div>

                                <div class="col-12 mt-2 text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-medium"
                                        x-data="{ loading: false }"
                                        @click="
                                            loading = true;
                                            if (navigator.geolocation) {
                                                navigator.geolocation.getCurrentPosition(
                                                    async (position) => {
                                                        const lat = position.coords.latitude;
                                                        const lng = position.coords.longitude;
                                                        $wire.lat = lat;
                                                        $wire.lng = lng;

                                                        try {
                                                            const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                                                            const data = await response.json();
                                                            if (data && data.display_name) {
                                                                $wire.address = data.display_name;
                                                            }
                                                        } catch (err) {
                                                            console.error('Reverse geocoding failed', err);
                                                        }
                                                        
                                                        loading = false;
                                                    },
                                                    (error) => {
                                                        alert('Error getting location: ' + error.message);
                                                        loading = false;
                                                    }
                                                );
                                            } else {
                                                alert('Geolocation is not supported by this browser.');
                                                loading = false;
                                            }
                                        "
                                    >
                                        <span x-show="!loading"><i class="bi bi-geo-alt-fill me-1"></i> Auto-fetch Location</span>
                                        <span x-show="loading"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Fetching...</span>
                                    </button>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Latitude (Lat)</label>
                                    <input type="text" class="form-control form-control-lg bg-light border-0" wire:model="lat" placeholder="28.704060">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Longitude (Lng)</label>
                                    <input type="text" class="form-control form-control-lg bg-light border-0" wire:model="lng" placeholder="77.102493">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Radius (m) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control form-control-lg bg-light border-0" wire:model="radius" min="1" required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Status <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-lg bg-light border-0" wire:model="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Company Profile Tab -->
                            <div x-show="tab === 'profile'" class="row g-4" style="display: none;" x-transition>
                                
                                <div class="col-md-6 d-flex flex-column justify-content-center">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Company Logo</label>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative" style="width: 60px; height: 60px;">
                                            @if ($company_logo)
                                                <img src="{{ $company_logo->temporaryUrl() }}" class="rounded-circle object-fit-cover shadow-sm border" width="60" height="60">
                                            @elseif ($existing_company_logo)
                                                <img src="{{ Storage::url($existing_company_logo) }}" class="rounded-circle object-fit-cover shadow-sm border" width="60" height="60">
                                            @else
                                                <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-muted" style="width: 60px; height: 60px;">
                                                    <i class="bi bi-image fs-4"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <input type="file" class="form-control form-control-sm" wire:model="company_logo" accept="image/*" id="logo-upload">
                                            <div wire:loading wire:target="company_logo" class="text-primary mt-1 small"><span class="spinner-border spinner-border-sm"></span> Uploading...</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">GST Number</label>
                                    <input type="text" class="form-control form-control-lg bg-light border-0 text-uppercase" wire:model="gst_number" placeholder="e.g. 22AAAAA0000A1Z5">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Founded Year</label>
                                    <input type="number" class="form-control form-control-lg bg-light border-0" wire:model="founded_year" placeholder="e.g. 2015" min="1800" max="{{ date('Y') }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Website URL</label>
                                    <input type="url" class="form-control form-control-lg bg-light border-0" wire:model="website" placeholder="https://example.com">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Company Size</label>
                                    <select class="form-select form-select-lg bg-light border-0" wire:model="company_size">
                                        <option value="">Select Size</option>
                                        <option value="1-10">1-10 Employees</option>
                                        <option value="11-50">11-50 Employees</option>
                                        <option value="51-200">51-200 Employees</option>
                                        <option value="201-500">201-500 Employees</option>
                                        <option value="500+">500+ Employees</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Type of Company</label>
                                    <select class="form-select form-select-lg bg-light border-0" wire:model="company_type">
                                        <option value="">Select Type</option>
                                        <option value="Private Limited">Private Limited</option>
                                        <option value="Public Limited">Public Limited</option>
                                        <option value="Partnership">Partnership</option>
                                        <option value="Proprietorship">Proprietorship</option>
                                        <option value="NGO">NGO / Non-Profit</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">Industry</label>
                                    <input type="text" class="form-control form-control-lg bg-light border-0" wire:model="industry" placeholder="e.g. IT Services & Consulting">
                                </div>

                                <div class="col-12">
                                    <label class="form-label text-muted small fw-bold text-uppercase tracking-wider">About Company</label>
                                    <textarea class="form-control bg-light border-0" wire:model="about_company" rows="3" placeholder="Brief description of the company..."></textarea>
                                </div>
                                
                                <div class="col-12 mt-2">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="border-bottom flex-grow-1"></div>
                                        <span class="text-muted small fw-bold text-uppercase tracking-wider">Social Profiles</span>
                                        <div class="border-bottom flex-grow-1"></div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-0 text-primary"><i class="bi bi-linkedin"></i></span>
                                        <input type="url" class="form-control bg-light border-0" wire:model="linkedin_url" placeholder="LinkedIn URL">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-0 text-primary"><i class="bi bi-facebook"></i></span>
                                        <input type="url" class="form-control bg-light border-0" wire:model="facebook_url" placeholder="Facebook URL">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-0 text-danger"><i class="bi bi-instagram"></i></span>
                                        <input type="url" class="form-control bg-light border-0" wire:model="instagram_url" placeholder="Instagram URL">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end mt-5 pt-3 border-top">
                                <button type="button" class="btn btn-light rounded-pill px-4 me-2 fw-medium border" wire:click="closeModal">Cancel</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-medium shadow-sm">
                                    <i class="bi bi-save me-1"></i> {{ $branchId ? 'Save Changes' : 'Create Branch' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- View Modal -->
    @if($isViewOpen && $viewBranch)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0">
                    <div class="modal-header bg-light border-0">
                        <h5 class="modal-title">Branch Details - {{ $viewBranch->name }}</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Header / Employee Card -->
                            <div class="col-12">
                                <div class="d-flex align-items-center p-3 bg-primary bg-opacity-10 rounded-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                        <i class="bi bi-people fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="text-muted fs-12 text-uppercase fw-semibold">Employees Assigned</div>
                                        <div class="fs-4 fw-bold text-primary">{{ $viewBranch->employees->count() }}</div>
                                    </div>
                                    <div class="ms-auto">
                                        <span class="badge bg-{{ $viewBranch->status === 'active' ? 'success' : 'danger' }} bg-opacity-10 text-{{ $viewBranch->status === 'active' ? 'success' : 'danger' }} px-3 py-2 rounded-pill">
                                            <i class="bi bi-circle-fill fs-10 me-1"></i> {{ ucfirst($viewBranch->status) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Details Section -->
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-building text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Branch Name</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">{{ $viewBranch->name }}</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-person-badge text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Branch Manager</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">{{ $viewBranch->manager->name ?? 'Not Assigned' }}</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-geo-alt text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Full Address</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">{{ $viewBranch->address ?? 'No address provided' }}</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="bi bi-crosshair text-secondary me-2"></i>
                                            <span class="text-muted fs-13">Coordinates</span>
                                        </div>
                                        <div class="fw-semibold fs-15 ps-4">{{ $viewBranch->lat ?? 'N/A' }}, {{ $viewBranch->lng ?? 'N/A' }}</div>
                                    </div>
                                    @if($viewBranch->lat && $viewBranch->lng)
                                        <div class="ps-4 mt-2">
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $viewBranch->lat }},{{ $viewBranch->lng }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 fs-12">
                                                <i class="bi bi-map me-1"></i> View on Map
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-radar text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Geofence Radius</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">{{ $viewBranch->radius }} meters</div>
                                </div>
                            </div>
                            
                            <!-- Company Profile Section -->
                            <div class="col-12 mt-4">
                                <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary">
                                    <i class="bi bi-building-check me-2"></i> Company Profile Details
                                </h6>
                            </div>

                            <div class="col-md-12 mb-3">
                                <div class="d-flex align-items-center p-3 border rounded-3 bg-light bg-opacity-50">
                                    <div class="me-4 shadow-sm bg-white rounded border overflow-hidden d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                        @if($viewBranch->company_logo)
                                            <img src="{{ Storage::url($viewBranch->company_logo) }}" alt="Company Logo" class="img-fluid object-fit-cover w-100 h-100">
                                        @else
                                            <i class="bi bi-image text-muted fs-3"></i>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 row">
                                        <div class="col-md-4 mb-2 mb-md-0">
                                            <div class="text-muted fs-13 mb-1">GST Number</div>
                                            <div class="fw-semibold fs-15 text-uppercase">{{ $viewBranch->gst_number ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-4 mb-2 mb-md-0">
                                            <div class="text-muted fs-13 mb-1">Founded Year</div>
                                            <div class="fw-semibold fs-15">{{ $viewBranch->founded_year ?? 'N/A' }}</div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-muted fs-13 mb-1">Website</div>
                                            @if($viewBranch->website)
                                                <a href="{{ $viewBranch->website }}" target="_blank" class="fw-semibold fs-15 text-decoration-none text-primary"><i class="bi bi-box-arrow-up-right me-1"></i> Visit Site</a>
                                            @else
                                                <div class="fw-semibold fs-15">N/A</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-people text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Company Size</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">{{ $viewBranch->company_size ?? 'N/A' }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-briefcase text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Company Type</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">{{ $viewBranch->company_type ?? 'N/A' }}</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 h-100 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-1">
                                        <i class="bi bi-gear text-secondary me-2"></i>
                                        <span class="text-muted fs-13">Industry</span>
                                    </div>
                                    <div class="fw-semibold fs-15 ps-4">{{ $viewBranch->industry ?? 'N/A' }}</div>
                                </div>
                            </div>

                            @if($viewBranch->about_company)
                            <div class="col-12">
                                <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-info-circle text-secondary me-2"></i>
                                        <span class="text-muted fs-13">About Company</span>
                                    </div>
                                    <div class="fw-semibold fs-14 text-dark lh-base">{{ $viewBranch->about_company }}</div>
                                </div>
                            </div>
                            @endif

                            <div class="col-12">
                                <div class="p-3 border rounded-3 bg-light bg-opacity-50 d-flex gap-4">
                                    <div>
                                        <span class="text-muted fs-13 me-2">Social Profiles:</span>
                                    </div>
                                    @if($viewBranch->linkedin_url)
                                        <a href="{{ $viewBranch->linkedin_url }}" target="_blank" class="text-primary text-decoration-none fs-15"><i class="bi bi-linkedin me-1"></i> LinkedIn</a>
                                    @endif
                                    @if($viewBranch->facebook_url)
                                        <a href="{{ $viewBranch->facebook_url }}" target="_blank" class="text-primary text-decoration-none fs-15"><i class="bi bi-facebook me-1"></i> Facebook</a>
                                    @endif
                                    @if($viewBranch->instagram_url)
                                        <a href="{{ $viewBranch->instagram_url }}" target="_blank" class="text-danger text-decoration-none fs-15"><i class="bi bi-instagram me-1"></i> Instagram</a>
                                    @endif
                                    
                                    @if(!$viewBranch->linkedin_url && !$viewBranch->facebook_url && !$viewBranch->instagram_url)
                                        <span class="text-muted fs-14">No social profiles added</span>
                                    @endif
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

