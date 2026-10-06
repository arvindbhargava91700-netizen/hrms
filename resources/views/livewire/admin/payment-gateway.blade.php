<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Payment Gateway</h4>
            <p class="text-muted mb-0">Manage Payment Gateway Merchants</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMerchantModal">
            <i class="bi bi-plus-circle me-1"></i> Add New Merchant
        </button>
    </div>
    
    @if(session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>User Name</th>
                            <th>TPI Merchant ID</th>
                            <th>KYC ID</th>
                            <th>KYC Status</th>
                            <th>Status</th>
                            <th>Details</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($merchants as $merchant)
                            <tr>
                                <td>{{ $loop->iteration + $merchants->firstItem() - 1 }}</td>
                                <td>{{ $merchant->user ? $merchant->user->name : 'N/A' }}</td>
                                <td>{{ $merchant->tpi_merchant_id }}</td>
                                <td>{{ $merchant->tpi_kyc_id }}</td>
                                <td>
                                    @if($merchant->tpi_kyc_status == 'APPROVED')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($merchant->tpi_kyc_status == 'PENDING')
                                        <span class="badge bg-warning">Pending</span>
                                    @elseif($merchant->tpi_kyc_status == 'REJECTED')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $merchant->tpi_kyc_status ?? 'N/A' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <button wire:click="toggleStatus({{ $merchant->id }})" class="btn btn-sm btn-{{ $merchant->status === 'active' ? 'success' : 'danger' }}">
                                        {{ ucfirst($merchant->status ?? 'active') }}
                                    </button>
                                </td>
                                <td>
                                    @if($merchant->details)
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#merchantDetailsModal-{{ $merchant->id }}">
                                            View
                                        </button>
                                        
                                        <!-- Modal -->
                                        <div class="modal fade" id="merchantDetailsModal-{{ $merchant->id }}" tabindex="-1" aria-labelledby="merchantDetailsModalLabel-{{ $merchant->id }}" aria-hidden="true">
                                          <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content border-0 shadow">
                                              <div class="modal-header bg-light border-bottom-0">
                                                <h5 class="modal-title fw-bold" id="merchantDetailsModalLabel-{{ $merchant->id }}">Merchant Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                              </div>
                                              <div class="modal-body bg-light p-4">
                                                <div class="row g-3">
                                                    @php
                                                        $details = is_string($merchant->details) ? json_decode($merchant->details, true) : (array)$merchant->details;
                                                    @endphp
                                                    @if(is_array($details) && count($details) > 0)
                                                        @foreach($details as $key => $value)
                                                            <div class="col-md-6">
                                                                <div class="card h-100 border-0 shadow-sm rounded-3">
                                                                    <div class="card-body p-3">
                                                                        <h6 class="text-muted text-uppercase fw-semibold mb-2" style="font-size: 0.7rem; letter-spacing: 0.5px;">{{ Str::headline($key) }}</h6>
                                                                        <div class="fw-medium text-dark text-break" style="font-size: 0.9rem;">
                                                                            @if(is_array($value) || is_object($value))
                                                                                <pre class="mb-0 bg-light p-2 rounded border" style="font-size: 0.8rem; overflow-x: auto;">@json($value, JSON_PRETTY_PRINT)</pre>
                                                                            @else
                                                                                {{ $value === true ? 'Yes' : ($value === false ? 'No' : ($value === null || $value === '' ? 'N/A' : $value)) }}
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <div class="col-12 text-center py-5">
                                                            <div class="text-muted"><i class="bi bi-info-circle me-1"></i> No details available.</div>
                                                        </div>
                                                    @endif
                                                </div>
                                              </div>
                                            </div>
                                          </div>
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $merchant->created_at ? $merchant->created_at->format('d M, Y H:i') : 'N/A' }}</td>
                                <td>
                                    <button wire:click="editMerchant({{ $merchant->id }})" class="btn btn-sm btn-primary">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No merchants found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($merchants->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $merchants->links() }}
            </div>
        @endif
    </div>

    <!-- Add Merchant Modal -->
    <div class="modal fade" id="addMerchantModal" tabindex="-1" aria-labelledby="addMerchantModalLabel" aria-hidden="true" wire:ignore.self>
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-light">
            <h5 class="modal-title fw-bold" id="addMerchantModalLabel">Add New Merchant</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form wire:submit.prevent="saveMerchant">
              <div class="modal-body">
                <div class="mb-3" wire:ignore>
                    <label class="form-label fw-semibold">Select Partner</label>
                    <select class="form-select select2-searchable" wire:model="selected_partner_id" required>
                        <option value="">-- Choose Partner --</option>
                        @foreach($available_partners as $partner)
                            <option value="{{ $partner->id }}">{{ $partner->name }} ({{ $partner->email }})</option>
                        @endforeach
                    </select>
                </div>
                @error('selected_partner_id') <span class="text-danger small">{{ $message }}</span> @enderror
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">TPI Merchant ID</label>
                    <input type="text" class="form-control" wire:model="new_tpi_merchant_id" required>
                    @error('new_tpi_merchant_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">KYC ID</label>
                    <input type="text" class="form-control" wire:model="new_kyc_id" required>
                    @error('new_kyc_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">KYC Status</label>
                    <select class="form-select" wire:model="new_kyc_status">
                        <option value="PENDING">PENDING</option>
                        <option value="APPROVED">APPROVED</option>
                        <option value="REJECTED">REJECTED</option>
                    </select>
                    @error('new_kyc_status') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
              </div>
              <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span wire:loading wire:target="saveMerchant" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Save Merchant
                </button>
              </div>
          </form>
        </div>
      </div>
    </div>
    <!-- Edit Merchant Modal -->
    <div class="modal fade" id="editMerchantModal" tabindex="-1" aria-labelledby="editMerchantModalLabel" aria-hidden="true" wire:ignore.self>
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-light">
            <h5 class="modal-title fw-bold" id="editMerchantModalLabel">Edit Merchant</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form wire:submit.prevent="updateMerchant">
              <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">TPI Merchant ID</label>
                    <input type="text" class="form-control" wire:model="edit_tpi_merchant_id" required>
                    @error('edit_tpi_merchant_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">KYC ID</label>
                    <input type="text" class="form-control" wire:model="edit_kyc_id" required>
                    @error('edit_kyc_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">KYC Status</label>
                    <select class="form-select" wire:model="edit_kyc_status">
                        <option value="PENDING">PENDING</option>
                        <option value="APPROVED">APPROVED</option>
                        <option value="REJECTED">REJECTED</option>
                    </select>
                    @error('edit_kyc_status') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select class="form-select" wire:model="edit_status">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    @error('edit_status') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
              </div>
              <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <span wire:loading wire:target="updateMerchant" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Update Merchant
                </button>
              </div>
          </form>
        </div>
      </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('close-modal', (id) => {
                const modalElement = document.getElementById(id[0] || id);
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
            });

            Livewire.on('open-modal', (id) => {
                const modalElement = document.getElementById(id[0] || id);
                if (modalElement) {
                    let modal = bootstrap.Modal.getInstance(modalElement);
                    if (!modal) {
                        modal = new bootstrap.Modal(modalElement);
                    }
                    modal.show();
                }
            });
        });
    </script>
</div>
