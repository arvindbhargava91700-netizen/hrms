<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-box-seam text-primary me-2"></i>Products</h5>
                <p class="text-muted small mb-0">Manage product catalog, pricing, and tax settings</p>
            </div>
            <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" wire:click="createProduct">
                <i class="bi bi-plus-circle me-1"></i> Add Product
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-feetrack mb-0 align-middle">
                <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                    <tr>
                        <th class="ps-4">Product Name</th>
                        <th>Category</th>
                        <th>Base Amount</th>
                        <th>GST</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $isGstIncluded = $product->gst_type === 'include' && (float)$product->gst_percent > 0;
                            $gstRate = $isGstIncluded ? (float)$product->gst_percent : 0;
                            $gstAmount = $isGstIncluded ? ((float)$product->amount * $gstRate / 100) : 0;
                            $totalPrice = (float)$product->amount + $gstAmount;
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark">{{ $product->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 small">
                                    {{ $product->category->name ?? 'Uncategorized' }}
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">₹{{ number_format($product->amount, 2) }}</td>
                            <td>
                                @if($isGstIncluded)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.78rem;">
                                        <i class="bi bi-check-circle me-1"></i>{{ number_format($gstRate, 1) }}% Included
                                    </span>
                                    <div class="text-muted extra-small" style="font-size: 0.7rem;">(+₹{{ number_format($gstAmount, 2) }})</div>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 extra-small">
                                        0% (Not Included)
                                    </span>
                                @endif
                            </td>
                            <td class="fw-bold text-primary">₹{{ number_format($totalPrice, 2) }}</td>
                            <td>
                                @php
                                    $badgeClass = $product->status === 'active' ? 'background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;' : 'background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;';
                                @endphp
                                <span class="badge rounded-pill px-3 py-1 fw-bold" style="{{ $badgeClass }}">
                                    {{ ucfirst($product->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="editProduct({{ $product->id }})" class="btn btn-sm btn-outline-primary rounded-pill px-3 me-1">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                <button wire:click="deleteProduct({{ $product->id }})" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirm('Are you sure you want to delete this product?') || event.stopImmediatePropagation()">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-box-seam display-4 mb-3 d-block text-secondary"></i>
                                No products found. Click "Add Product" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    @if($isModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi {{ $editingId ? 'bi-pencil-square text-primary' : 'bi-plus-circle-fill text-success' }} me-2"></i>
                        {{ $editingId ? 'Edit Product' : 'Add New Product' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="$set('isModalOpen', false)"></button>
                </div>
                
                <form wire:submit.prevent="saveProduct">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Category <span class="text-danger">*</span></label>
                            <select wire:model="category_id" class="form-select">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Product Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control" placeholder="e.g. Standard Gym Membership / Protein Powder">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Base Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">₹</span>
                                <input type="number" step="0.01" wire:model.live="amount" class="form-control fw-bold" placeholder="0.00">
                            </div>
                            @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <!-- GST Section -->
                        <div class="row g-2 mb-3">
                            <div class="{{ $gst_type === 'include' ? 'col-md-6' : 'col-12' }}">
                                <label class="form-label text-muted small fw-bold text-uppercase">GST Option <span class="text-danger">*</span></label>
                                <select wire:model.live="gst_type" class="form-select">
                                    <option value="notinclude">Not Include (0%)</option>
                                    <option value="include">Include (Add GST %)</option>
                                </select>
                                @error('gst_type') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            @if($gst_type === 'include')
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase">GST Percentage (%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="0.01" max="100" wire:model.live="gst_percent" class="form-control fw-bold text-success" placeholder="e.g. 18">
                                    <span class="input-group-text bg-white fw-bold">%</span>
                                </div>
                                @error('gst_percent') <span class="text-danger small">{{ $message }}</span> @enderror
                                
                                <!-- Quick GST Presets -->
                                <div class="d-flex gap-1 mt-1.5 flex-wrap">
                                    @foreach([5, 12, 18, 28] as $rate)
                                        <button type="button" wire:click="$set('gst_percent', {{ $rate }})" class="btn btn-xs py-0 px-2 rounded-pill {{ (float)$gst_percent == $rate ? 'btn-primary text-white' : 'btn-outline-secondary' }}" style="font-size: 0.7rem;">
                                            {{ $rate }}%
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Live Price Calculation Breakdown -->
                        @if((float)$amount > 0)
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center small mb-1">
                                    <span class="text-muted">Base Amount:</span>
                                    <span class="fw-semibold text-dark">₹{{ number_format((float)$amount, 2) }}</span>
                                </div>
                                @if($gst_type === 'include' && (float)$gst_percent > 0)
                                    @php
                                        $liveGstAmt = ((float)$amount * (float)$gst_percent) / 100;
                                        $liveTotalAmt = (float)$amount + $liveGstAmt;
                                    @endphp
                                    <div class="d-flex justify-content-between align-items-center small mb-1">
                                        <span class="text-success"><i class="bi bi-receipt me-1"></i>GST ({{ number_format((float)$gst_percent, 1) }}%):</span>
                                        <span class="text-success fw-semibold">+₹{{ number_format($liveGstAmt, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-top pt-1.5 fw-bold text-dark">
                                        <span>Total Price (with GST):</span>
                                        <span class="text-primary fs-6">₹{{ number_format($liveTotalAmt, 2) }}</span>
                                    </div>
                                @else
                                    <div class="d-flex justify-content-between align-items-center border-top pt-1.5 fw-bold text-dark">
                                        <span>GST Applied:</span>
                                        <span class="text-muted small">0% (₹0.00)</span>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Status</label>
                            <select wire:model="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                    <div class="modal-footer border-top py-3 px-4 bg-white">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">{{ $editingId ? 'Update Product' : 'Save Product' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
