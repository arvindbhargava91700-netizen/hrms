<div>
    {{-- Page Header --}}
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color: #0f172a;">Employee Documents & KYC Management</h4>
            <p class="text-muted small mb-0">Track Aadhaar, PAN, Bank details, Offer/Appointment letters, Contracts & Expiry alerts</p>
        </div>
        @if(auth()->user()->isPartner() || auth()->user()->canAccess('document_create'))
            <button class="btn btn-primary d-inline-flex align-items-center gap-2 px-3 py-2 fw-semibold shadow-sm" wire:click="createDocument" style="border-radius: 8px;">
                <i class="bi bi-file-earmark-plus fs-6"></i>
                Upload New Document
            </button>
        @endif
    </div>

    {{-- Flash Notifications --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 8px;">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Stat Cards --}}
    <div class="row g-3 mb-4">
        {{-- Total Documents --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Total Documents</span>
                        <h3 class="fw-bold mb-0" style="color: #1e293b;">{{ $totalDocs }}</h3>
                    </div>
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: #e0f2fe; color: #0284c7; width: 48px; height: 48px;">
                        <i class="bi bi-folder2-open fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Verified Documents --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Verified Documents</span>
                        <h3 class="fw-bold mb-0 text-success">{{ $verifiedDocs }}</h3>
                    </div>
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: #dcfce7; color: #16a34a; width: 48px; height: 48px;">
                        <i class="bi bi-patch-check-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Verification --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Pending Verification</span>
                        <h3 class="fw-bold mb-0 text-warning">{{ $pendingDocs }}</h3>
                    </div>
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: #fef3c7; color: #d97706; width: 48px; height: 48px;">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expiring / Expired --}}
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; background: #ffffff; cursor: pointer;" wire:click="toggleExpiringFilter">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block mb-1">Expiring / Expired</span>
                        <h3 class="fw-bold mb-0 text-danger">{{ $expiringDocs }}</h3>
                    </div>
                    <div class="rounded-3 p-3 d-flex align-items-center justify-content-center" style="background: #fee2e2; color: #dc2626; width: 48px; height: 48px;">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                {{-- Search Bar --}}
                <div class="col-12 col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Search by title, doc no, employee..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                {{-- Category Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <select class="form-select bg-light border-0" wire:model.live="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="kyc">Aadhaar / PAN / Bank / KYC</option>
                        <option value="offer_letter">Offer Letter</option>
                        <option value="appointment_letter">Appointment Letter</option>
                        <option value="agreement">Agreements / Contracts</option>
                        <option value="other">Other Documents</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="col-12 col-sm-6 col-md-3">
                    <select class="form-select bg-light border-0" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending_verification">Pending Verification</option>
                        <option value="verified">Verified</option>
                        <option value="rejected">Rejected</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>

                {{-- Expiring Filter Toggle --}}
                <div class="col-12 col-md-2 text-md-end">
                    <button class="btn w-100 {{ $expiringFilter ? 'btn-danger' : 'btn-outline-secondary' }} btn-sm py-2 fw-semibold" wire:click="toggleExpiringFilter" style="border-radius: 8px;">
                        <i class="bi bi-alarm me-1"></i> {{ $expiringFilter ? 'Expiring Only (Active)' : 'Expiring Docs' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents Data Table --}}
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                <thead class="bg-light text-muted fw-semibold">
                    <tr>
                        <th class="ps-3 py-3">Employee</th>
                        <th class="py-3">Document Category</th>
                        <th class="py-3">Title & Number</th>
                        <th class="py-3">Expiry Date</th>
                        <th class="py-3">Status</th>
                        <th class="pe-3 py-3 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($documents as $doc)
                        <tr>
                            {{-- Employee Details --}}
                            <td class="ps-3 py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($doc->employee->name ?? 'E', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $doc->employee->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $doc->employee->employee_code ?? 'EMP' }}</small>
                                    </div>
                                </div>
                            </td>

                            {{-- Document Category --}}
                            <td class="py-3">
                                @if($doc->document_category === 'kyc')
                                    <span class="badge bg-info bg-opacity-10 text-info fw-semibold px-2 py-1"><i class="bi bi-card-heading me-1"></i>KYC / Identity</span>
                                @elseif($doc->document_category === 'offer_letter')
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold px-2 py-1"><i class="bi bi-file-earmark-text me-1"></i>Offer Letter</span>
                                @elseif($doc->document_category === 'appointment_letter')
                                    <span class="badge bg-purple bg-opacity-10 text-purple fw-semibold px-2 py-1" style="color: #8b5cf6; background: #f3e8ff;"><i class="bi bi-journal-check me-1"></i>Appointment Letter</span>
                                @elseif($doc->document_category === 'agreement')
                                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold px-2 py-1" style="color: #d97706;"><i class="bi bi-file-earmark-lock me-1"></i>Agreement</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold px-2 py-1"><i class="bi bi-paperclip me-1"></i>Other</span>
                                @endif
                                <div class="small text-muted mt-1">{{ ucfirst($doc->document_type) }}</div>
                            </td>

                            {{-- Title & Number --}}
                            <td class="py-3">
                                <div class="fw-semibold text-dark">{{ $doc->title }}</div>
                                @if($doc->document_number)
                                    <small class="text-muted"><i class="bi bi-hash"></i>{{ $doc->document_number }}</small>
                                @endif
                            </td>

                            {{-- Expiry Date --}}
                            <td class="py-3">
                                @if($doc->expiry_date)
                                    @php
                                        $isExpiring = $doc->expiry_date->isPast() || $doc->expiry_date->diffInDays(now()) <= 30;
                                    @endphp
                                    <div class="fw-semibold {{ $isExpiring ? 'text-danger' : 'text-dark' }}">
                                        {{ $doc->expiry_date->format('d M Y') }}
                                    </div>
                                    @if($doc->expiry_date->isPast())
                                        <small class="badge bg-danger bg-opacity-10 text-danger">Expired</small>
                                    @elseif($doc->expiry_date->diffInDays(now()) <= 30)
                                        <small class="badge bg-warning bg-opacity-10 text-warning">Expiring Soon</small>
                                    @endif
                                @else
                                    <span class="text-muted small">No Expiry</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3">
                                @if($doc->status === 'verified')
                                    <span class="badge bg-success bg-opacity-10 text-success fw-semibold px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i>Verified</span>
                                @elseif($doc->status === 'pending_verification')
                                    <span class="badge bg-warning bg-opacity-10 text-warning fw-semibold px-2 py-1"><i class="bi bi-hourglass-split me-1"></i>Pending</span>
                                @elseif($doc->status === 'rejected')
                                    <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold px-2 py-1"><i class="bi bi-x-circle-fill me-1"></i>Rejected</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold px-2 py-1"><i class="bi bi-slash-circle me-1"></i>Expired</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="pe-3 py-3 text-end">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light text-primary" wire:click="viewDocumentDetails({{ $doc->id }})" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if($doc->file_path)
                                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="btn btn-sm btn-light text-success" title="Download / Preview File">
                                            <i class="bi bi-download"></i>
                                        </a>
                                    @endif
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('document_update'))
                                        <button class="btn btn-sm btn-light text-secondary" wire:click="editDocument({{ $doc->id }})" title="Edit Document">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('document_delete'))
                                        <button class="btn btn-sm btn-light text-danger" wire:click="deleteDocument({{ $doc->id }})" wire:confirm="Are you sure you want to delete this document?" title="Delete Document">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-folder-x fs-1 opacity-50 d-block mb-2"></i>
                                    <h6>No Employee Documents Found</h6>
                                    <p class="small mb-0">Upload Aadhaar, PAN, Offer Letters, Appointment Letters or Agreements to manage KYC</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($documents->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-3">
                {{ $documents->links() }}
            </div>
        @endif
    </div>

    {{-- Create / Edit Modal --}}
    @if ($isFormModalOpen)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" style="color: #0f172a;">
                            {{ $isEditMode ? 'Edit Employee Document' : 'Upload New Employee Document' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>

                    <form wire:submit.prevent="saveDocument">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                {{-- Employee Selection --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Select Employee <span class="text-danger">*</span></label>
                                    <select class="form-select @error('employee_id') is-invalid @enderror" wire:model="employee_id">
                                        <option value="">Select Employee...</option>
                                        @foreach ($employees as $emp)
                                            <option value="{{ $emp->id }}" @selected($employee_id == $emp->id)>{{ $emp->name }} ({{ $emp->employee_code ?? 'EMP' }})</option>
                                        @endforeach
                                    </select>
                                    @error('employee_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Document Category --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Document Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('document_category') is-invalid @enderror" wire:model="document_category">
                                        <option value="kyc" @selected($document_category === 'kyc')>Aadhaar / PAN / Bank / KYC</option>
                                        <option value="offer_letter" @selected($document_category === 'offer_letter')>Offer Letter</option>
                                        <option value="appointment_letter" @selected($document_category === 'appointment_letter')>Appointment Letter</option>
                                        <option value="agreement" @selected($document_category === 'agreement')>Agreement / Contract</option>
                                        <option value="other" @selected($document_category === 'other')>Other Document</option>
                                    </select>
                                    @error('document_category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Document Type --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Document Type / Sub-Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('document_type') is-invalid @enderror" wire:model="document_type">
                                        <option value="aadhaar" @selected($document_type === 'aadhaar')>Aadhaar Card</option>
                                        <option value="pan" @selected($document_type === 'pan')>PAN Card</option>
                                        <option value="bank_passbook" @selected($document_type === 'bank_passbook')>Bank Passbook / Cancelled Cheque</option>
                                        <option value="kyc" @selected($document_type === 'kyc')>General KYC</option>
                                        <option value="offer_letter" @selected($document_type === 'offer_letter')>Offer Letter</option>
                                        <option value="appointment_letter" @selected($document_type === 'appointment_letter')>Appointment Letter</option>
                                        <option value="agreement" @selected($document_type === 'agreement')>Employment Agreement / NDA</option>
                                        <option value="other" @selected($document_type === 'other')>Other File</option>
                                    </select>
                                    @error('document_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Document Title --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Document Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" wire:model="title" placeholder="e.g. Employee Signed Offer Letter 2026">
                                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Document Number / Ref --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Document / Reference Number</label>
                                    <input type="text" class="form-control @error('document_number') is-invalid @enderror" wire:model="document_number" placeholder="e.g. 1234-5678-9012 or PAN ID">
                                    @error('document_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Status --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Verification Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" wire:model="status">
                                        <option value="pending_verification" @selected($status === 'pending_verification')>Pending Verification</option>
                                        <option value="verified" @selected($status === 'verified')>Verified</option>
                                        <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                                        <option value="expired" @selected($status === 'expired')>Expired</option>
                                    </select>
                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Issue Date --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Issue Date</label>
                                    <input type="date" class="form-control @error('issue_date') is-invalid @enderror" wire:model="issue_date">
                                    @error('issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- Expiry Date --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Expiry Date (If applicable)</label>
                                    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" wire:model="expiry_date">
                                    @error('expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                {{-- File Upload Input --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Document File (PDF / Image / Doc) {{ $isEditMode ? '' : '*' }}</label>
                                    <input type="file" class="form-control @error('new_file') is-invalid @enderror" wire:model="new_file">
                                    <div wire:loading wire:target="new_file" class="small text-primary mt-1">Uploading file, please wait...</div>
                                    @error('new_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @if($existing_file_path && !$new_file)
                                        <div class="small text-muted mt-1">
                                            Current File: <a href="{{ asset('storage/' . $existing_file_path) }}" target="_blank" class="text-primary"><i class="bi bi-file-earmark-arrow-down me-1"></i>View Uploaded File</a>
                                        </div>
                                    @endif
                                </div>

                                {{-- Remarks --}}
                                <div class="col-12">
                                    <label class="form-label fw-semibold small">Remarks / Verification Notes</label>
                                    <textarea class="form-control @error('remarks') is-invalid @enderror" wire:model="remarks" rows="2" placeholder="Verification notes, missing pages, or agreement terms..."></textarea>
                                    @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                            <button type="button" class="btn btn-light px-4 fw-semibold" wire:click="closeModals" style="border-radius: 8px;">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm" style="border-radius: 8px;">
                                <i class="bi bi-check-lg me-1"></i> {{ $isEditMode ? 'Update Document' : 'Save Document' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- View Details Modal --}}
    @if ($isViewModalOpen && $viewDocument)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                    <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold" style="color: #0f172a;">Document Details</h5>
                        <button type="button" class="btn-close" wire:click="closeModals"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ $viewDocument->title }}</h6>
                                <span class="badge bg-light text-dark border">{{ strtoupper($viewDocument->document_category) }}</span>
                            </div>
                            <div>
                                @if($viewDocument->status === 'verified')
                                    <span class="badge bg-success px-3 py-2">Verified</span>
                                @elseif($viewDocument->status === 'pending_verification')
                                    <span class="badge bg-warning text-dark px-3 py-2">Pending</span>
                                @elseif($viewDocument->status === 'rejected')
                                    <span class="badge bg-danger px-3 py-2">Rejected</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2">Expired</span>
                                @endif
                            </div>
                        </div>

                        <div class="row g-3 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Employee Name:</span>
                                <strong class="text-dark">{{ $viewDocument->employee->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Document Number:</span>
                                <strong class="text-dark">{{ $viewDocument->document_number ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Issue Date:</span>
                                <strong class="text-dark">{{ $viewDocument->issue_date ? $viewDocument->issue_date->format('d M Y') : 'N/A' }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Expiry Date:</span>
                                <strong class="text-dark">{{ $viewDocument->expiry_date ? $viewDocument->expiry_date->format('d M Y') : 'No Expiry' }}</strong>
                            </div>
                            @if($viewDocument->remarks)
                                <div class="col-12">
                                    <span class="text-muted d-block">Remarks / Notes:</span>
                                    <div class="p-2 bg-light rounded text-dark mt-1">{{ $viewDocument->remarks }}</div>
                                </div>
                            @endif
                            @if($viewDocument->file_path)
                                @php
                                    $fileUrl = asset('storage/' . $viewDocument->file_path);
                                    $fileExt = strtolower(pathinfo($viewDocument->file_path, PATHINFO_EXTENSION));
                                    $isImageFile = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                    $isPdfFile = $fileExt === 'pdf';
                                @endphp
                                <div class="col-12 mt-3">
                                    <span class="text-muted d-block mb-2">Document Preview:</span>
                                    <div class="text-center bg-light rounded p-2 border">
                                        @if($isImageFile)
                                            <img src="{{ $fileUrl }}" alt="{{ $viewDocument->title }}" class="img-fluid rounded" style="max-height: 320px; max-width: 100%; object-fit: contain;" onerror="this.style.display='none';document.getElementById('docPreviewFallback').style.display='block';">
                                            <div id="docPreviewFallback" class="text-danger small py-4" style="display: none;">
                                                <i class="bi bi-exclamation-triangle-fill d-block fs-3 mb-1"></i>
                                                Image could not be loaded.
                                            </div>
                                        @elseif($isPdfFile)
                                            <iframe src="{{ $fileUrl }}#toolbar=0" style="width: 100%; height: 400px; border: none; border-radius: 8px;"></iframe>
                                        @else
                                            <div class="text-muted py-4">
                                                <i class="bi bi-file-earmark-arrow-down fs-1 d-block mb-2"></i>
                                                <span class="small">Preview not available for .{{ $fileExt }} files</span>
                                            </div>
                                        @endif
                                    </div>
                                    <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary btn-sm w-100 fw-semibold mt-2">
                                        <i class="bi bi-box-arrow-in-up-right me-1"></i> Open in New Tab / Download
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light px-4 fw-semibold w-100" wire:click="closeModals" style="border-radius: 8px;">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
