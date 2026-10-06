<div>
    <div class="mb-4">
        <a href="{{ route('admin.customer-kyc') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row g-4">
        <!-- Customer Details -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">Customer Details</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="{{ $kyc->customer->avatar_url }}" class="rounded-circle mb-3 border shadow-sm" width="96" height="96" alt="{{ $kyc->customer->name }}">
                        <h5 class="mb-1 fw-bold">{{ $kyc->customer->name }}</h5>
                        <p class="text-muted mb-0">{{ \App\Helpers\AdminHelper::maskContact('email', $kyc->customer->email) }}</p>
                        <p class="text-muted mb-0">{{ \App\Helpers\AdminHelper::maskContact('mobile', $kyc->customer->mobile) }}</p>
                    </div>

                    <ul class="list-group list-group-flush border-top pt-3">
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Registered On</span>
                            <span class="fw-500">{{ $kyc->customer->created_at->format('d M Y') }}</span>
                        </li>
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span class="text-muted">KYC Status</span>
                            <span class="badge-status badge-{{ $kyc->status }}">{{ ucfirst($kyc->status) }}</span>
                        </li>
                    </ul>

                    @if($kyc->status === 'pending')
                        <div class="mt-4 d-grid gap-2">
                            <button class="btn btn-success" wire:click="approve">
                                <i class="bi bi-check-circle me-1"></i> Approve Profile
                            </button>
                            <button class="btn btn-outline-danger" onclick="let r = prompt('Enter rejection reason:'); if(r) @this.call('reject', r)">
                                <i class="bi bi-x-circle me-1"></i> Reject Profile
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">Submitted Documents</h5>
                </div>
                <div class="card-body">
                    
                    @if($kyc->live_photo)
                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">Live Photo</h6>
                            <a href="{{ $kyc->getStoredFileUrl($kyc->live_photo) }}" target="_blank">
                                <img src="{{ $kyc->getStoredFileUrl($kyc->live_photo) }}" class="img-thumbnail" style="max-height: 200px;">
                            </a>
                        </div>
                    @endif

                    <div class="row g-4">
                        <!-- Aadhaar -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">Aadhaar Card</h6>
                            @if($kyc->aadhaar_front)
                                <div class="mb-2">
                                    <span class="text-muted small">Front:</span><br>
                                    <a href="{{ $kyc->getStoredFileUrl($kyc->aadhaar_front) }}" target="_blank">
                                        <img src="{{ $kyc->getStoredFileUrl($kyc->aadhaar_front) }}" class="img-thumbnail" style="max-height: 150px;">
                                    </a>
                                </div>
                            @endif
                            @if($kyc->aadhaar_back)
                                <div>
                                    <span class="text-muted small">Back:</span><br>
                                    <a href="{{ $kyc->getStoredFileUrl($kyc->aadhaar_back) }}" target="_blank">
                                        <img src="{{ $kyc->getStoredFileUrl($kyc->aadhaar_back) }}" class="img-thumbnail" style="max-height: 150px;">
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- PAN -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">PAN Card</h6>
                            @if($kyc->pan_front)
                                <div class="mb-2">
                                    <span class="text-muted small">Front:</span><br>
                                    <a href="{{ $kyc->getStoredFileUrl($kyc->pan_front) }}" target="_blank">
                                        <img src="{{ $kyc->getStoredFileUrl($kyc->pan_front) }}" class="img-thumbnail" style="max-height: 150px;">
                                    </a>
                                </div>
                            @endif
                            @if($kyc->pan_back)
                                <div>
                                    <span class="text-muted small">Back:</span><br>
                                    <a href="{{ $kyc->getStoredFileUrl($kyc->pan_back) }}" target="_blank">
                                        <img src="{{ $kyc->getStoredFileUrl($kyc->pan_back) }}" class="img-thumbnail" style="max-height: 150px;">
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Bank Statement -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">Bank Statement</h6>
                            @if($kyc->bank_statement)
                                <div>
                                    <a href="{{ $kyc->getStoredFileUrl($kyc->bank_statement) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-text"></i> View Statement
                                    </a>
                                </div>
                            @else
                                <span class="text-muted">Not Provided</span>
                            @endif
                        </div>

                        <!-- Passport -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">Passport (Optional)</h6>
                            @if($kyc->passport_front)
                                <div class="mb-2">
                                    <span class="text-muted small">Front:</span><br>
                                    <a href="{{ $kyc->getStoredFileUrl($kyc->passport_front) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-text"></i> View Passport Front
                                    </a>
                                </div>
                            @endif
                            @if($kyc->passport_back)
                                <div>
                                    <span class="text-muted small">Back:</span><br>
                                    <a href="{{ $kyc->getStoredFileUrl($kyc->passport_back) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-text"></i> View Passport Back
                                    </a>
                                </div>
                            @endif
                            @if(!$kyc->passport_front && !$kyc->passport_back)
                                <span class="text-muted">Not Provided</span>
                            @endif
                        </div>

                        <!-- Driving License -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-2">Driving License (Optional)</h6>
                            @if($kyc->driving_license_front)
                                <div class="mb-2">
                                    <span class="text-muted small">Front:</span><br>
                                    <a href="{{ $kyc->getStoredFileUrl($kyc->driving_license_front) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-text"></i> View License Front
                                    </a>
                                </div>
                            @endif
                            @if($kyc->driving_license_back)
                                <div>
                                    <span class="text-muted small">Back:</span><br>
                                    <a href="{{ $kyc->getStoredFileUrl($kyc->driving_license_back) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-file-earmark-text"></i> View License Back
                                    </a>
                                </div>
                            @endif
                            @if(!$kyc->driving_license_front && !$kyc->driving_license_back)
                                <span class="text-muted">Not Provided</span>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
