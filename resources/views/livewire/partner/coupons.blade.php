<div>
    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;min-width:48px;">
                        <i class="bi bi-ticket-perforated text-white fs-4"></i>
                    </div>
                    <div>
                        <div class="text-white text-uppercase fw-bold" style="font-size:0.72rem;letter-spacing:1px;opacity:0.85">Total Coupons</div>
                        <div class="text-white fw-bold fs-4">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;min-width:48px;">
                        <i class="bi bi-check-circle text-white fs-4"></i>
                    </div>
                    <div>
                        <div class="text-white text-uppercase fw-bold" style="font-size:0.72rem;letter-spacing:1px;opacity:0.85">Active</div>
                        <div class="text-white fw-bold fs-4">{{ number_format($stats['active']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;min-width:48px;">
                        <i class="bi bi-clock-history text-white fs-4"></i>
                    </div>
                    <div>
                        <div class="text-white text-uppercase fw-bold" style="font-size:0.72rem;letter-spacing:1px;opacity:0.85">Expired</div>
                        <div class="text-white fw-bold fs-4">{{ number_format($stats['expired']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%);">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px;min-width:48px;">
                        <i class="bi bi-graph-up text-white fs-4"></i>
                    </div>
                    <div>
                        <div class="text-white text-uppercase fw-bold" style="font-size:0.72rem;letter-spacing:1px;opacity:0.85">Total Uses</div>
                        <div class="text-white fw-bold fs-4">{{ number_format($stats['used']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Main Card --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 rounded-top-4">
            <div>
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-ticket-perforated text-primary me-2"></i>My Coupons
                </h5>
                <small class="text-muted">Create listing-specific discount coupons for your customers</small>
            </div>
            <button class="btn btn-primary rounded-3 px-4" wire:click="create">
                <i class="bi bi-plus-lg me-2"></i>New Coupon
            </button>
        </div>

        {{-- Filters --}}
        <div class="card-body border-bottom py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search by code or title..." class="form-control border-start-0 ps-0">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-1">Discount Type</label>
                    <select class="form-select" wire:model.live="typeFilter">
                        <option value="">All Types</option>
                        <option value="flat">Flat Amount</option>
                        <option value="percent">Percentage</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Listing</th>
                        <th>Discount</th>
                        <th>Usage</th>
                        <th>Expiry</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        <tr wire:key="coupon-{{ $coupon->id }}">
                            <td class="ps-4">
                                <div class="fw-bold text-dark">
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-2 px-2 py-1" style="font-size:0.85rem;letter-spacing:1px;">{{ $coupon->code }}</span>
                                </div>
                                <div class="small text-muted mt-1">{{ $coupon->title }}</div>
                                @if($coupon->description)
                                    <div class="small text-muted" style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $coupon->description }}</div>
                                @endif
                            </td>
                            <td>
                                @php $listing = \App\Models\Listing::find($coupon->listing_id); @endphp
                                <span class="text-dark small">{{ $listing?->title ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-5">
                                    {{ $coupon->type === 'percent' ? $coupon->value . '%' : '₹' . number_format($coupon->value, 2) }}
                                </div>
                                <div class="small text-muted">Min: ₹{{ number_format($coupon->min_amount, 2) }}</div>
                                @if($coupon->max_discount)
                                    <div class="small text-muted">Max off: ₹{{ number_format($coupon->max_discount, 2) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $coupon->used_count }} / {{ $coupon->max_uses > 0 ? $coupon->max_uses : '∞' }}</div>
                                @if($coupon->max_uses > 0)
                                    @php $pct = min(100, round($coupon->used_count / $coupon->max_uses * 100)); @endphp
                                    <div class="progress mt-1" style="height:4px;width:80px;">
                                        <div class="progress-bar {{ $pct >= 90 ? 'bg-danger' : ($pct >= 60 ? 'bg-warning' : 'bg-success') }}" style="width:{{ $pct }}%"></div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($coupon->expires_at)
                                    <span class="{{ $coupon->expires_at->isPast() ? 'text-danger' : 'text-muted' }} small">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $coupon->expires_at->format('d M Y') }}
                                    </span>
                                    @if($coupon->expires_at->isPast())
                                        <span class="badge bg-danger bg-opacity-10 text-danger d-block mt-1" style="width:fit-content;font-size:0.7rem;">Expired</span>
                                    @endif
                                @else
                                    <span class="text-muted small"><i class="bi bi-infinity me-1"></i>No expiry</span>
                                @endif
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        {{ $coupon->is_active ? 'checked' : '' }}
                                        wire:click="toggleActive('{{ $coupon->id }}')"
                                        style="cursor:pointer;">
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary rounded-3" wire:click="edit({{ $coupon->id }})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="text-muted d-flex flex-column align-items-center">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center mb-3" style="width:80px;height:80px;">
                                        <i class="bi bi-ticket-perforated display-5 text-secondary" style="opacity:0.5"></i>
                                    </div>
                                    <h5 class="fw-semibold text-dark">No Coupons Found</h5>
                                    <p class="small mb-3">Create your first coupon to attract more customers!</p>
                                    <button class="btn btn-primary btn-sm rounded-3" wire:click="create">
                                        <i class="bi bi-plus-lg me-1"></i>Create Coupon
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="modal d-block" style="background:rgba(0,0,0,0.5);z-index:1055;" wire:key="coupon-modal">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 rounded-4 shadow-lg">
                    <div class="modal-header border-bottom py-3 px-4">
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">
                                <i class="bi bi-ticket-perforated text-primary me-2"></i>
                                {{ $couponId ? 'Edit Coupon' : 'Create New Coupon' }}
                            </h5>
                            <small class="text-muted">Coupons are scoped to your listings only</small>
                        </div>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                {{-- Code --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Coupon Code <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="code" class="form-control text-uppercase @error('code') is-invalid @enderror" placeholder="e.g. SAVE20">
                                    @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text">Customers will enter this code at checkout</div>
                                </div>
                                {{-- Title --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror" placeholder="e.g. Summer Sale Discount">
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                {{-- Description --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Description <small class="text-muted fw-normal">(Optional)</small></label>
                                    <textarea wire:model="description" class="form-control" rows="2" placeholder="Brief description shown to customers..."></textarea>
                                </div>
                                {{-- Listing --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Apply to Listing <span class="text-danger">*</span></label>
                                    <select wire:model="listing_id" class="form-select @error('listing_id') is-invalid @enderror">
                                        <option value="">— Select your listing —</option>
                                        @foreach($myListings as $id => $listingTitle)
                                            <option value="{{ $id }}">{{ $listingTitle }}</option>
                                        @endforeach
                                    </select>
                                    @error('listing_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <div class="form-text"><i class="bi bi-info-circle me-1"></i>Only your approved listings are shown</div>
                                </div>
                                {{-- Divider --}}
                                <div class="col-12"><hr class="my-1 text-muted opacity-25"></div>
                                {{-- Type & Value --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Discount Type <span class="text-danger">*</span></label>
                                    <select wire:model.live="type" class="form-select">
                                        <option value="flat">Flat Amount (₹)</option>
                                        <option value="percent">Percentage (%)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Value ({{ $type === 'percent' ? '%' : '₹' }}) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">{{ $type === 'percent' ? '%' : '₹' }}</span>
                                        <input type="number" step="0.01" wire:model="value" class="form-control @error('value') is-invalid @enderror" placeholder="0.00">
                                        @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                {{-- Min Amount & Max Discount --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Minimum Order Amount (₹)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" wire:model="min_amount" class="form-control" placeholder="0">
                                    </div>
                                    <div class="form-text">Coupon valid only above this amount</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Max Discount Cap (₹) <small class="text-muted fw-normal">(Optional)</small></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" wire:model="max_discount" class="form-control" placeholder="No cap" {{ $type === 'flat' ? 'disabled' : '' }}>
                                    </div>
                                    @if($type === 'flat') <div class="form-text text-muted">Not applicable for flat discounts</div> @endif
                                </div>
                                {{-- Max Uses & Expiry --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Max Uses <small class="text-muted fw-normal">(0 = unlimited)</small></label>
                                    <input type="number" wire:model="max_uses" class="form-control" placeholder="0">
                                    @error('max_uses') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Expires At <small class="text-muted fw-normal">(Optional)</small></label>
                                    <input type="date" wire:model="expires_at" class="form-control" min="{{ date('Y-m-d') }}">
                                </div>
                                {{-- Active Toggle --}}
                                <div class="col-12">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" id="isActive" wire:model="is_active" style="width:2.5rem;height:1.25rem;">
                                        <label class="form-check-label fw-semibold ms-2" for="isActive">
                                            {{ $is_active ? 'Active — customers can use this coupon' : 'Inactive — coupon is disabled' }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light rounded-bottom-4 px-4">
                            <button type="button" class="btn btn-outline-secondary rounded-3" wire:click="$set('showModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary rounded-3 px-4">
                                <i class="bi bi-check-lg me-2"></i>{{ $couponId ? 'Update Coupon' : 'Create Coupon' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
