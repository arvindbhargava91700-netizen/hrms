<div>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Packages & Pricing</h5>
            @if(auth()->user()->isPartner() || auth()->user()->canAccess('package_create'))
                <button class="btn btn-primary" wire:click="openCreate">
                    <i class="bi bi-plus-lg me-2"></i>New Package
                </button>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0">
                <thead>
                    <tr>
                        <th>Package</th>
                        <th>Listing</th>
                        <th>Room / Type</th>
                        <th>Duration / Type</th>
                        <th>Price</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $pkg)
                        <tr>
                            <td>
                                <div class="fw-600">{{ $pkg->name }}</div>
                                @if($pkg->features)
                                    <div class="text-muted fs-12">{{ count($pkg->features) }} features listed</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $pkg->listing->title ?? '-' }}</div>
                                <div class="text-muted fs-12">{{ $pkg->listing->category->name ?? '' }}</div>
                            </td>
                            <td>
                                <div>{{ $pkg->room_type ? ucfirst($pkg->room_type) : 'All types' }}</div>
                                <div class="badge bg-light text-dark border text-capitalize mt-1">{{ str_replace('_', ' ', $pkg->occupancy_type ?? 'standard') }}</div>
                            </td>
                            <td>
                                <div>{{ $pkg->duration_days }} days</div>
                                <div class="badge bg-light text-dark border text-capitalize mt-1">{{ str_replace('_', ' ', $pkg->type) }}</div>
                            </td>
                            <td class="fw-700 fs-15 text-success">₹{{ number_format($pkg->price) }}</td>
                            <td class="text-end">
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('package_update'))
                                    <button class="btn btn-icon btn-outline-primary btn-sm" wire:click="openEdit('{{ $pkg->id }}')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No packages found. Add packages to allow customers to subscribe.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($packages->hasPages())
            <div class="card-footer bg-transparent border-top p-3">
                {{ $packages->links() }}
            </div>
        @endif
    </div>

    {{-- Package Modal --}}
    @if($showModal)
        <div class="modal d-block" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editId ? 'Edit Package' : 'New Package' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Associated Listing</label>
                                <select class="form-select" wire:model.live="listing_id" required>
                                    <option value="">Select Listing</option>
                                    @foreach($listings as $listing)
                                        <option value="{{ $listing->id }}">{{ $listing->title }} ({{ $listing->category->name ?? '' }})</option>
                                    @endforeach
                                </select>
                                @error('listing_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            @if($selectedListing && in_array(strtolower($selectedListing->category->name ?? ''), ['pg / hostel', 'room rental']))
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Room Type</label>
                                    <select class="form-select" wire:model="room_type">
                                        <option value="">Select Type</option>
                                        @foreach($roomTypes as $rtype)
                                            <option value="{{ $rtype }}">{{ ucfirst($rtype) }}</option>
                                        @endforeach
                                    </select>
                                    @error('room_type') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Occupancy Type</label>
                                    <select class="form-select" wire:model="occupancy_type">
                                        <option value="single">Single</option>
                                        <option value="full_room">Full Room</option>
                                        <option value="per_bed">Per Bed</option>
                                    </select>
                                    @error('occupancy_type') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label">Package Name</label>
                                <input type="text" class="form-control" wire:model="name" placeholder="e.g. Premium Monthly" required>
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="form-label">Duration (Days)</label>
                                    <input type="number" class="form-control" wire:model="duration_days" min="1" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Billing Type</label>
                                    <select class="form-select" wire:model="type" required>
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="half_yearly">Half Yearly</option>
                                        <option value="yearly">Yearly</option>
                                        <option value="one_time">One-Time</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Price (₹)</label>
                                <input type="number" step="0.01" class="form-control" wire:model="price" placeholder="0.00" required>
                                <div class="form-text">For PG / room listings, this is managed per room package.</div>
                                @error('price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Features <span class="text-muted fs-12">(One per line)</span></label>
                                <textarea class="form-control" wire:model="featuresInput" rows="4" placeholder="Free WiFi&#10;AC Room&#10;24/7 Access"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Package</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>