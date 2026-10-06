<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #1e293b;">
                <i class="bi bi-laptop-fill text-primary me-2"></i>Assets Management
            </h4>
            <p class="text-muted small mb-0">Register, update, and manage company laptops, mobiles, SIM cards, ID cards, and hardware inventory.</p>
        </div>
        <div class="d-flex align-items-center gap-2 d-print-none">
            <button class="btn btn-primary btn-sm d-flex align-items-center gap-1 shadow-sm px-3 py-2" wire:click="openAssetModal()">
                <i class="bi bi-plus-lg"></i> Register Asset
            </button>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4 d-print-none">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Category</label>
                    <select wire:model.live="filterCategory" class="form-select form-select-sm">
                        <option value="all">All Categories</option>
                        <option value="laptop">Laptop</option>
                        <option value="mobile">Mobile</option>
                        <option value="sim">SIM Card</option>
                        <option value="id_card">ID Card</option>
                        <option value="other">Other Asset</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Status</label>
                    <select wire:model.live="filterStatus" class="form-select form-select-sm">
                        <option value="all">All Statuses</option>
                        <option value="available">Available</option>
                        <option value="issued">Issued</option>
                        <option value="damaged">Damaged</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Condition</label>
                    <select wire:model.live="filterCondition" class="form-select form-select-sm">
                        <option value="all">All Conditions</option>
                        <option value="new">New</option>
                        <option value="good">Good</option>
                        <option value="fair">Fair</option>
                        <option value="damaged">Damaged</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Branch</label>
                    <select wire:model.live="filterBranchId" class="form-select form-select-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Department</label>
                    <select wire:model.live="filterDepartmentId" class="form-select form-select-sm">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-xl-2 col-md-3 col-6">
                    <label class="form-label text-muted small fw-bold mb-1">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control form-control-sm" placeholder="Code, serial, IMEI, name...">
                </div>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Total Assets</div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($totalAssets) }}</div>
                        </div>
                        <div class="bg-primary-subtle text-primary p-2 rounded-circle">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Available / Active</div>
                            <div class="fs-4 fw-bold text-success mt-1">{{ number_format($availableAssets) }}</div>
                        </div>
                        <div class="bg-success-subtle text-success p-2 rounded-circle">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #ef4444 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Damaged Items</div>
                            <div class="fs-4 fw-bold text-danger mt-1">{{ number_format($damagedAssets) }}</div>
                        </div>
                        <div class="bg-danger-subtle text-danger p-2 rounded-circle">
                            <i class="bi bi-exclamation-octagon fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #8b5cf6 !important;">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-muted small fw-semibold">Total Asset Cost</div>
                            <div class="fs-4 fw-bold text-purple mt-1">₹{{ number_format($totalAssetValue, 2) }}</div>
                        </div>
                        <div class="bg-purple-subtle text-purple p-2 rounded-circle">
                            <i class="bi bi-currency-rupee fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Asset Master List Table Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-list-task text-primary me-2"></i>Company Assets Catalog
            </h6>
            <div class="d-flex align-items-center gap-2 d-print-none">
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: 80px;">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3">Code</th>
                        <th>Category</th>
                        <th>Asset Title / Details</th>
                        <th>Identifier (Serial / IMEI / SIM)</th>
                        <th>Condition</th>
                        <th>Status</th>
                        <th>Cost (₹)</th>
                        <th class="pe-3 text-end d-print-none">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paginatedAssets as $asset)
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-light text-dark border fw-bold">{{ $asset->asset_code }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                    @if($asset->category === 'laptop') <i class="bi bi-laptop me-1"></i> Laptop
                                    @elseif($asset->category === 'mobile') <i class="bi bi-phone me-1"></i> Mobile
                                    @elseif($asset->category === 'sim') <i class="bi bi-sim me-1"></i> SIM Card
                                    @elseif($asset->category === 'id_card') <i class="bi bi-person-badge me-1"></i> ID Card
                                    @else <i class="bi bi-box me-1"></i> Other
                                    @endif
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $asset->name }}</div>
                                @if($asset->brand || $asset->model)
                                    <small class="text-muted">{{ $asset->brand }} {{ $asset->model }}</small>
                                @endif
                            </td>
                            <td>
                                @if($asset->category === 'laptop')
                                    <div><small class="text-muted">Serial:</small> {{ $asset->serial_number ?: 'N/A' }}</div>
                                @elseif($asset->category === 'mobile')
                                    <div><small class="text-muted">IMEI:</small> {{ $asset->imei_number ?: 'N/A' }}</div>
                                @elseif($asset->category === 'sim')
                                    <div><small class="text-muted">Mobile:</small> {{ $asset->mobile_number ?: 'N/A' }}</div>
                                    <small class="text-muted">SIM: {{ $asset->sim_number }}</small>
                                @elseif($asset->category === 'id_card')
                                    <div><small class="text-muted">Card No:</small> {{ $asset->card_number ?: $asset->asset_code }}</div>
                                @else
                                    <span class="text-muted">{{ $asset->serial_number ?: '-' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($asset->condition === 'new')
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">New</span>
                                @elseif($asset->condition === 'good')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Good</span>
                                @elseif($asset->condition === 'fair')
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Fair</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Damaged</span>
                                @endif
                            </td>
                            <td>
                                @if($asset->status === 'available')
                                    <span class="badge bg-success text-white">AVAILABLE</span>
                                @elseif($asset->status === 'issued')
                                    <span class="badge bg-info text-white">ISSUED</span>
                                @else
                                    <span class="badge bg-danger text-white">DAMAGED</span>
                                @endif
                            </td>
                            <td class="fw-semibold text-dark">
                                {{ $asset->purchase_cost ? '₹' . number_format($asset->purchase_cost, 2) : '-' }}
                            </td>
                            <td class="pe-3 text-end d-print-none">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" title="Edit Asset" wire:click="openAssetModal({{ $asset->id }})">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    <button class="btn btn-outline-danger" title="Delete Asset" wire:click="deleteAsset({{ $asset->id }})" onclick="return confirm('Are you sure you want to delete asset {{ $asset->name }}?')">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No assets registered matching your filters. Click <strong>"Register Asset"</strong> to add company hardware/items.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginatedAssets->hasPages())
            <div class="card-footer bg-white py-3 border-0">
                {{ $paginatedAssets->links() }}
            </div>
        @endif
    </div>

    {{-- Register / Edit Asset Modal --}}
    @if($showAssetModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="fw-bold modal-title">{{ $editingAssetId ? 'Edit Asset Details' : 'Register New Asset' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeAssetModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Asset Category *</label>
                                <select wire:model.live="category" class="form-select">
                                    <option value="laptop">Laptop</option>
                                    <option value="mobile">Mobile Phone</option>
                                    <option value="sim">SIM Card</option>
                                    <option value="id_card">ID Card</option>
                                    <option value="other">Other Asset</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Asset Code *</label>
                                <input type="text" wire:model="asset_code" class="form-control" placeholder="e.g. LAP-0001">
                                @error('asset_code') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Asset Title / Name *</label>
                                <input type="text" wire:model="name" class="form-control" placeholder="e.g. Dell Latitude 5420">
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Dynamic Fields based on Category --}}
                        <div class="row g-2 mb-3">
                            @if(in_array($category, ['laptop', 'mobile', 'other']))
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Brand</label>
                                    <input type="text" wire:model="brand" class="form-control" placeholder="Dell, Apple, Samsung">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Model</label>
                                    <input type="text" wire:model="model" class="form-control" placeholder="Latitude 5420, Galaxy A55">
                                </div>
                            @endif

                            @if($category === 'laptop' || $category === 'other')
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Serial Number</label>
                                    <input type="text" wire:model="serial_number" class="form-control" placeholder="Device Serial No.">
                                </div>
                            @endif

                            @if($category === 'mobile')
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">IMEI Number</label>
                                    <input type="text" wire:model="imei_number" class="form-control" placeholder="15-digit IMEI">
                                </div>
                            @endif

                            @if($category === 'sim')
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Mobile Number</label>
                                    <input type="text" wire:model="mobile_number" class="form-control" placeholder="98XXXXXXXX">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">SIM Card Number</label>
                                    <input type="text" wire:model="sim_number" class="form-control" placeholder="ICCID SIM No.">
                                </div>
                            @endif

                            @if($category === 'id_card')
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Card Number</label>
                                    <input type="text" wire:model="card_number" class="form-control" placeholder="ID Card Badge No.">
                                </div>
                            @endif
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Purchase Date</label>
                                <input type="date" wire:model="purchase_date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Purchase Cost (₹)</label>
                                <input type="number" step="0.01" wire:model="purchase_cost" class="form-control" placeholder="0.00">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Condition</label>
                                <select wire:model="condition" class="form-select">
                                    <option value="new">New</option>
                                    <option value="good">Good</option>
                                    <option value="fair">Fair</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Status</label>
                                <select wire:model="status" class="form-select">
                                    <option value="available">Available</option>
                                    <option value="issued">Issued</option>
                                    <option value="damaged">Damaged</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description / Remarks</label>
                            <textarea wire:model="description" class="form-control" rows="2" placeholder="Additional specifications..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" wire:click="closeAssetModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveAsset()">Save Asset</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
