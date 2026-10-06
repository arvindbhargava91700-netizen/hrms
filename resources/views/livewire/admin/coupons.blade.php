<div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-ticket-perforated"></i></div>
                <div>
                    <div class="stat-label">Total Coupons</div>
                    <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle text-success"></i></div>
                <div>
                    <div class="stat-label">Active Coupons</div>
                    <div class="stat-value">{{ number_format($stats['active'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-info);">
                <div class="stat-icon bg-info-soft"><i class="bi bi-globe text-info"></i></div>
                <div>
                    <div class="stat-label">Global Coupons</div>
                    <div class="stat-value">{{ number_format($stats['global'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-warning);">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-shop text-warning"></i></div>
                <div>
                    <div class="stat-label">Partner Specific</div>
                    <div class="stat-value">{{ number_format($stats['partner'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Manage Coupons</h5>
                <small class="text-muted">Create global or listing-specific discount coupons.</small>
            </div>
            <button class="btn btn-primary" wire:click="create">
                <i class="bi bi-plus-lg me-2"></i>Add Coupon
            </button>
        </div>
        
        <div class="card-body border-bottom border-light">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted">Search</label>
                    <input wire:model.live="search" type="text" placeholder="Search by code or title..." class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted">Type</label>
                    <select class="form-select" wire:model.live="typeFilter">
                        <option value="">All</option>
                        <option value="flat">Flat Amount</option>
                        <option value="percent">Percentage</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted">Scope</label>
                    <select class="form-select" wire:model.live="scopeFilter">
                        <option value="">All</option>
                        <option value="global">Global</option>
                        <option value="listing">Specific Listing</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-feetrack mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Details</th>
                        <th>Discount</th>
                        <th>Usage</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $coupon->code }}</div>
                                <div class="small text-muted">{{ $coupon->title }}</div>
                            </td>
                            <td>
                                <div>{{ $coupon->listing_id ? 'Specific Listing' : 'Global' }}</div>
                                <div class="small text-muted">{{ $coupon->expires_at ? 'Exp: ' . $coupon->expires_at->format('Y-m-d') : 'No Expiry' }}</div>
                            </td>
                            <td>
                                <div class="fw-bold">{{ $coupon->type === 'percent' ? $coupon->value . '%' : '₹' . $coupon->value }}</div>
                                <div class="small text-muted">Min: ₹{{ $coupon->min_amount }}</div>
                            </td>
                            <td>
                                {{ $coupon->used_count }} / {{ $coupon->max_uses > 0 ? $coupon->max_uses : '∞' }}
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        {{ $coupon->is_active ? 'checked' : '' }}
                                        wire:click="toggleActive('{{ $coupon->id }}')">
                                </div>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-icon btn-outline-primary btn-sm" wire:click="edit({{ $coupon->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No coupons found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
        <div class="card-footer">
            {{ $coupons->links() }}
        </div>
        @endif
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="modal d-block" style="background:rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $couponId ? 'Edit Coupon' : 'Create Coupon' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('showModal', false)"></button>
                    </div>
                    <form wire:submit="save">
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Code</label>
                                    <input type="text" wire:model="code" class="form-control">
                                    @error('code') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Title</label>
                                    <input type="text" wire:model="title" class="form-control">
                                    @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea wire:model="description" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Type</label>
                                    <select wire:model.live="type" class="form-select">
                                        <option value="flat">Flat Amount</option>
                                        <option value="percent">Percentage</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Value ({{ $type === 'percent' ? '%' : '₹' }})</label>
                                    <input type="number" step="0.01" wire:model="value" class="form-control">
                                    @error('value') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Min Amount (₹)</label>
                                    <input type="number" step="0.01" wire:model="min_amount" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max Discount (₹) <small class="text-muted">(Optional)</small></label>
                                    <input type="number" step="0.01" wire:model="max_discount" class="form-control" {{ $type === 'flat' ? 'disabled' : '' }}>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Listing Scope</label>
                                    <select wire:model="listing_id" class="form-select">
                                        <option value="">Global (All Listings)</option>
                                        @foreach($listings as $id => $listingTitle)
                                            <option value="{{ $id }}">{{ $listingTitle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Max Uses <small class="text-muted">(0 = ∞)</small></label>
                                    <input type="number" wire:model="max_uses" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Expires At</label>
                                    <input type="date" wire:model="expires_at" class="form-control">
                                </div>
                                <div class="col-12 mt-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="isActive" wire:model="is_active">
                                        <label class="form-check-label" for="isActive">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" wire:click="$set('showModal', false)">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Coupon</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
