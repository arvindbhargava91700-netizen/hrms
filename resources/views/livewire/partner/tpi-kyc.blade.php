<div>

    @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom p-4">
            <h5 class="mb-0 fw-bold">TPI Gateway Setup</h5>
        </div>
        <div class="card-body p-4">
            @if($merchant && $merchant->tpi_kyc_id)
                <div class="alert alert-info">
                    <h6 class="alert-heading fw-bold">KYC Status: <span class="badge bg-{{ $merchant->tpi_kyc_status === 'APPROVED' ? 'success' : ($merchant->tpi_kyc_status === 'REJECTED' ? 'danger' : 'warning') }}">{{ $merchant->tpi_kyc_status }}</span></h6>
                    <p class="mb-0">Your TPI Pay Merchant ID: <strong>{{ $merchant->tpi_merchant_id }}</strong></p>
                    <p class="mb-0">Your TPI Pay KYC ID: <strong>{{ $merchant->tpi_kyc_id }}</strong></p>
                    
                    @if($merchant->tpi_kyc_status === 'PENDING')
                        <p class="mt-2 mb-0 small text-muted">Your KYC is currently under review. This page will automatically refresh your status when you visit it.</p>
                    @endif
                </div>
            @endif

            <form wire:submit.prevent="submit" class="row g-4">
                {{-- BASIC INFORMATION --}}
                <div class="col-12 mt-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Legal Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" wire:model="legal_name" placeholder="E.g., John Doe" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('legal_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Business Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" wire:model="business_name" placeholder="E.g., JD Enterprises" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('business_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" wire:model="email" placeholder="E.g., john@example.com" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" wire:model="contact_number" placeholder="E.g., 9876543210" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('contact_number') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" wire:model="password" placeholder="Create a password for TPI Pay" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- BUSINESS DETAILS --}}
                <div class="col-12 mt-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-briefcase me-2"></i>Business Details</h6>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Business Category <span class="text-danger">*</span></label>
                    <select class="form-select" wire:model="business_category" {{ $merchant && $merchant->tpi_merchant_id ? 'disabled' : '' }}>
                        <option value="">Select Category</option>
                        @foreach(['School', 'College', 'University', 'Coaching Institute', 'Online Education', 'Training Institute', 'Hostel', 'PG Accommodation', 'Hotel', 'Gym', 'Fitness Center', 'Day Care', 'Hospital', 'Clinic', 'Medical Store', 'Grocery Shop', 'Super Market', 'Electronics Store', 'Clothing Store', 'Restaurant', 'Cafe', 'Petrol Pump', 'Fuel Station', 'Electricity', 'Water Utility', 'Insurance Agency', 'Government Service'] as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('business_category') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Business Type <span class="text-danger">*</span></label>
                    <select class="form-select" wire:model.live="business_type" {{ $merchant && $merchant->tpi_merchant_id ? 'disabled' : '' }}>
                        <option value="">Select Business Type</option>
                        <option value="SOLE_PROPRIETOR">SOLE PROPRIETOR</option>
                        <option value="PARTNERSHIP_FIRM">PARTNERSHIP FIRM</option>
                        <option value="LLP">LLP</option>
                        <option value="PRIVATE_LIMITED_COMPANY">PRIVATE LIMITED COMPANY</option>
                        <option value="PUBLIC_LIMITED">PUBLIC LIMITED</option>
                        <option value="TRUST">TRUST</option>
                        <option value="SOCIETY">SOCIETY</option>
                    </select>
                    @error('business_type') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Business Code <span class="text-muted">(Optional)</span></label>
                    <input type="text" class="form-control" wire:model="business_code" placeholder="E.g., BUS123" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('business_code') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- ADDRESS DETAILS --}}
                <div class="col-12 mt-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-geo-alt me-2"></i>Address Details (Optional)</h6>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Address</label>
                    <input type="text" class="form-control" wire:model="address" placeholder="Full address" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('address') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">City</label>
                    <input type="text" class="form-control" wire:model="city" placeholder="City" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('city') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">State</label>
                    <input type="text" class="form-control" wire:model="state" placeholder="State" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('state') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">PIN Code</label>
                    <input type="text" class="form-control" wire:model="pin_code" placeholder="PIN Code" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('pin_code') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- BANK DETAILS --}}
                <div class="col-12 mt-4">
                    <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-bank me-2"></i>Bank Details (Optional)</h6>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Account Holder Name</label>
                    <input type="text" class="form-control" wire:model="account_holder_name" placeholder="Name as per bank" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('account_holder_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Bank Name</label>
                    <input type="text" class="form-control" wire:model="bank_name" placeholder="E.g., HDFC Bank" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('bank_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Account Number</label>
                    <input type="text" class="form-control" wire:model="account_number" placeholder="Account Number" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('account_number') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">IFSC Code</label>
                    <input type="text" class="form-control" wire:model="ifsc_code" placeholder="IFSC Code" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('ifsc_code') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Branch Name</label>
                    <input type="text" class="form-control" wire:model="branch_name" placeholder="Branch Name" {{ $merchant && $merchant->tpi_merchant_id ? 'readonly' : '' }}>
                    @error('branch_name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                {{-- KYC DOCUMENTS --}}
                @if(count($requiredDocuments) > 0)
                    <div class="col-12 mt-4">
                        <h6 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-file-earmark-text me-2"></i>Required KYC Documents</h6>
                    </div>

                    <div class="row g-3">
                        @foreach($requiredDocuments as $doc)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border shadow-sm">
                                    <div class="card-body">
                                        <h6 class="card-title fw-semibold text-truncate" title="{{ $doc['name'] }}">{{ $doc['name'] }}</h6>
                                        @if(isset($existingDocuments[$doc['enumValue']]))
                                            @php $existingPath = $existingDocuments[$doc['enumValue']]; @endphp
                                            <div class="mt-3 p-2 border rounded bg-light">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Uploaded</span>
                                                    <a href="{{ Storage::url($existingPath) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0"><i class="bi bi-eye"></i> View</a>
                                                </div>
                                                @if(in_array(pathinfo($existingPath, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png']))
                                                    <div class="text-center">
                                                        <img src="{{ Storage::url($existingPath) }}" class="img-thumbnail" style="max-height: 100px; max-width: 100%; object-fit: contain;">
                                                    </div>
                                                @endif
                                                <div class="mt-2 pt-2 border-top">
                                                    <label class="small text-muted mb-1">Replace document (optional):</label>
                                                    <input type="file" class="form-control form-control-sm" wire:model="uploadedDocuments.{{ $doc['enumValue'] }}" accept=".jpg,.jpeg,.png,.pdf">
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-3">
                                                <input type="file" class="form-control form-control-sm" wire:model="uploadedDocuments.{{ $doc['enumValue'] }}" accept=".jpg,.jpeg,.png,.pdf">
                                                <div wire:loading wire:target="uploadedDocuments.{{ $doc['enumValue'] }}" class="mt-2 small text-muted">
                                                    <i class="bi bi-hourglass-split"></i> Uploading preview...
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if(isset($uploadedDocuments[$doc['enumValue']]) && !is_string($uploadedDocuments[$doc['enumValue']]))
                                            @php $file = $uploadedDocuments[$doc['enumValue']]; @endphp
                                            <div class="mt-2 text-center">
                                                @if(in_array($file->extension(), ['jpg', 'jpeg', 'png']))
                                                    <span class="badge bg-info mt-2"><i class="bi bi-info-circle"></i> New file selected</span>
                                                    <img src="{{ $file->temporaryUrl() }}" class="img-thumbnail mt-1" style="max-height: 100px; max-width: 100%; object-fit: contain;">
                                                @else
                                                    <span class="badge bg-success mt-2 text-wrap"><i class="bi bi-check-circle"></i> {{ $file->getClientOriginalName() }}</span>
                                                @endif
                                            </div>
                                        @endif
                                        @error('uploadedDocuments.'.$doc['enumValue'])
                                            <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @elseif($business_type)
                    <div class="col-12 mt-4">
                        <div class="alert alert-warning">No required documents found for this business type or failed to fetch.</div>
                    </div>
                @else
                    <div class="col-12 mt-4">
                        <div class="alert alert-info">Please select a Business Type to see required documents.</div>
                    </div>
                @endif

                <div class="col-12 mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4 rounded-3" wire:loading.attr="disabled" wire:target="submit, document_file">
                        <span wire:loading.remove wire:target="submit">Submit KYC</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
