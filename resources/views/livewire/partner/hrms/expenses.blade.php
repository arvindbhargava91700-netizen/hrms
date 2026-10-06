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
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi {{ request('scope') === 'me' ? 'bi-person-badge' : (request('scope') === 'team' ? 'bi-people' : 'bi-receipt') }} text-primary me-2"></i>
                    {{ request('scope') === 'me' ? 'My Expenses' : (request('scope') === 'team' ? 'Team Expenses' : 'Employee Expenses') }}
                </h5>
                @if(request('scope') === 'me')
                    <p class="text-muted small mb-0">View and track your personal submitted expense claims and reimbursement status.</p>
                @endif
            </div>
            <div class="d-flex gap-2">
                @if(Auth::user()->canAccess('expense_create'))
                <button class="btn btn-primary btn-sm rounded-pill px-3 fw-bold" wire:click="createExpense">
                    <i class="bi bi-plus-circle me-1"></i> New Expense Claim
                </button>
                @endif
            </div>
        </div>
        @if((auth()->user()->canAccess('expense_viewAny') || auth()->user()->canAccess('expense_viewTeam')) && request('scope') !== 'me')
        <div class="card-body pb-0">
            @include('partials.hrms-filters', ['viewAnyPermission' => 'expense_viewAny'])
        </div>
        @endif
        
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Date</th>
                        <th>Category & Details</th>
                        <th>Amount</th>
                        <th>Receipt / Proof</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="ps-4 fw-medium">{{ $expense->employee ? $expense->employee->name : 'N/A' }}</td>
                            <td>{{ Carbon\Carbon::parse($expense->date)->format('M d, Y') }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $expense->category }}</div>
                                @if($expense->quantity && $expense->unit_rate)
                                    <div class="text-muted small">
                                        <i class="bi bi-calculator me-1"></i>{{ $expense->quantity }} {{ $expense->categoryRelation->unit_name ?? 'Units' }} @ ₹{{ number_format($expense->unit_rate, 2) }}/unit
                                    </div>
                                @endif
                            </td>
                            <td class="fw-bold text-success">₹{{ number_format($expense->amount, 2) }}</td>
                            <td>
                                @if($expense->upload_file)
                                    @php
                                        $ext = strtolower(pathinfo($expense->upload_file, PATHINFO_EXTENSION));
                                        $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'jfif']);
                                        $fileType = $isImg ? 'image' : ($ext === 'pdf' ? 'pdf' : 'doc');
                                        $fileUrl = route('partner.hrms.expenses.file', $expense->id);
                                    @endphp
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" wire:click="openPreviewModal('{{ $fileUrl }}', '{{ $expense->category }} - {{ $expense->employee ? $expense->employee->name : 'Claim' }} Proof', '{{ $fileType }}')" class="btn btn-sm btn-light text-primary border rounded-pill px-2.5 py-1 fw-bold d-inline-flex align-items-center shadow-sm" style="font-size: 0.8rem;" title="Preview proof in popup">
                                            <i class="bi {{ $isImg ? 'bi-image' : ($ext === 'pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-text') }} me-1.5 text-primary"></i> View Proof
                                        </button>
                                        <a href="{{ $fileUrl }}" target="_blank" download class="btn btn-sm btn-light text-secondary border rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 26px; height: 26px;" title="Open in new window / download">
                                            <i class="bi bi-box-arrow-up-right" style="font-size: 0.65rem;"></i>
                                        </a>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 extra-small">
                                        <i class="bi bi-dash-circle me-1"></i> No file
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted small text-truncate" style="max-width: 180px;">{{ $expense->description ?? 'No description' }}</td>
                            <td>
                                @php
                                    $badgeStyle = match($expense->status) {
                                        'pending'  => 'background-color: #fef3c7; color: #92400e; border: 1px solid #fcd34d;',
                                        'approved' => 'background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;',
                                        'rejected' => 'background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;',
                                        default    => 'background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;'
                                    };
                                @endphp
                                <span class="badge rounded-pill px-3 py-1.5 fw-bold" style="{{ $badgeStyle }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if(Auth::user()->canAccess('expense_update') || Auth::id() === $expense->employee_id)
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" wire:click="editExpense({{ $expense->id }})">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bi bi-receipt display-4 mb-3 d-block text-secondary"></i>
                                {{ request('scope') === 'me' ? 'No personal expense claims found. Click "New Expense Claim" to submit one.' : 'No expenses found.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expense Claim Modal -->
    @if($isModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark">{{ $editingId ? 'Edit Expense Claim' : 'New Expense Claim' }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('isModalOpen', false)"></button>
                </div>
                
                <form wire:submit.prevent="saveExpense">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Employee <span class="text-danger">*</span></label>
                            <div wire:ignore>
                            <select wire:model="employee_id" class="form-select select2-searchable" {{ (!Auth::user()->canAccess('expense_viewAny') && !$editingId) || request('scope') === 'me' ? 'disabled' : '' }}>
                                <option value="">Select Employee</option>
                                @if(!Auth::user()->canAccess('expense_viewAny') || request('scope') === 'me')
                                    <option value="{{ Auth::id() }}">{{ Auth::user()->name }}</option>
                                @else
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            </div>
                            @error('employee_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Expense Category <span class="text-danger">*</span></label>
                            @if(count($categories) > 0)
                                <select wire:model.live="expense_category_id" class="form-select">
                                    <option value="">Select Category...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">
                                            {{ $cat->name }} 
                                             @if($cat->type === 'per_unit')
                                                (₹{{ number_format($cat->rate_per_unit, 2) }} / {{ $cat->unit_name }})
                                            @elseif($cat->type === 'max_limit')
                                                (Max Limit: ₹{{ number_format($cat->max_limit_amount, 2) }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('expense_category_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            @else
                                <input type="text" wire:model="category" placeholder="e.g. Petrol, Travel, Food" class="form-control">
                                @error('category') <span class="text-danger small">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        <!-- Dynamic Calculation / Amount Section -->
                        @if($selectedCategory && $selectedCategory->type === 'per_unit')
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1">
                                        <i class="bi bi-speedometer2 me-1"></i> Per Unit Rate
                                    </span>
                                    <span class="fw-bold text-dark small">
                                        Rate: <span class="text-success">₹{{ number_format($selectedCategory->rate_per_unit, 2) }}</span> / {{ $selectedCategory->unit_name }}
                                    </span>
                                </div>
                                <div class="row g-2 align-items-center mt-1">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted mb-1">Distance / Quantity ({{ $selectedCategory->unit_name }}) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" wire:model.live="quantity" placeholder="e.g. 50" class="form-control">
                                        @error('quantity') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-muted mb-1">Calculated Total Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="text" class="form-control bg-white fw-bold text-success" value="{{ number_format((float)$amount, 2) }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Claim Amount (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" wire:model="amount" class="form-control" placeholder="0.00">
                                @if($selectedCategory && $selectedCategory->type === 'max_limit')
                                    <div class="form-text text-warning small">
                                        <i class="bi bi-info-circle me-1"></i> Maximum allowable claim limit for this category is <strong>₹{{ number_format($selectedCategory->max_limit_amount, 2) }}</strong>.
                                    </div>
                                @endif
                                @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <!-- Bill / Receipt File Upload -->
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase d-flex justify-content-between align-items-center">
                                <span>
                                    Bill / Receipt Proof (JPG, PNG, WEBP, PDF)
                                    @if(!$editingId || (!$existing_upload_file && !$upload_file))
                                        <span class="text-danger">*</span>
                                    @endif
                                </span>
                                <span class="extra-small text-muted font-monospace" style="font-size: 0.72rem;">Max 10MB</span>
                            </label>

                            <!-- Loading Pill while uploading -->
                            <div wire:loading wire:target="upload_file" class="w-100 mb-2">
                                <div class="d-flex align-items-center gap-2 px-3.5 py-2 rounded-pill shadow-sm" style="background: #eff6ff; border: 1.5px dashed #3b82f6; color: #1d4ed8;">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status" style="width: 1rem; height: 1rem;"></div>
                                    <span class="small fw-bold">Uploading document... please wait</span>
                                </div>
                            </div>

                            @if ($upload_file)
                                @php
                                    $clientName = $upload_file->getClientOriginalName();
                                    $clientExt = strtolower(pathinfo($clientName, PATHINFO_EXTENSION));
                                    $isImg = in_array($clientExt, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                                    $fileSize = round($upload_file->getSize() / 1024, 1);
                                    $tempUrl = null;
                                    try {
                                        if ($isImg) {
                                            $tempUrl = $upload_file->temporaryUrl();
                                        }
                                    } catch (\Exception $e) {
                                        $tempUrl = null;
                                    }
                                @endphp
                                <!-- Newly Selected File Pill -->
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 rounded-pill shadow-sm mb-2" 
                                     style="background: #f0fdf4; border: 1.5px solid #86efac; color: #166534;">
                                    <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                                        <span class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                              style="width: 32px; height: 32px; background: #dcfce7; color: #15803d; font-size: 1rem;">
                                            <i class="bi {{ $isImg ? 'bi-file-earmark-image' : 'bi-file-earmark-pdf' }}"></i>
                                        </span>
                                        <div class="d-flex flex-column text-truncate">
                                            <span class="fw-bold text-truncate text-dark" style="font-size: 0.85rem;" title="{{ $clientName }}">{{ $clientName }}</span>
                                            <span class="text-success extra-small fw-semibold" style="font-size: 0.72rem;">
                                                <i class="bi bi-check2 me-1"></i> {{ $fileSize }} KB &bull; Attached & ready
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            wire:click="removeUploadFile" 
                                            class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" 
                                            style="width: 28px; height: 28px; font-size: 0.85rem; border-width: 1.5px;" 
                                            title="Delete / Remove this attached file">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <!-- Visual Preview Container -->
                                <div class="p-2.5 rounded-3 border bg-light mt-2 text-center">
                                    <div class="d-flex justify-content-between align-items-center mb-1.5 px-1">
                                        <span class="extra-small fw-bold text-muted text-uppercase"><i class="bi bi-eye me-1"></i>Live Attachment Preview</span>
                                        @if($tempUrl)
                                            <button type="button" wire:click="openPreviewModal('{{ $tempUrl }}', '{{ $clientName }}')" class="btn btn-sm btn-link p-0 extra-small text-decoration-none fw-bold">
                                                <i class="bi bi-arrows-fullscreen me-1"></i>Fullscreen
                                            </button>
                                        @endif
                                    </div>
                                    @if($isImg && $tempUrl)
                                        <div class="rounded-3 overflow-hidden border bg-white p-1 d-inline-block shadow-sm" style="max-height: 180px;">
                                            <img src="{{ $tempUrl }}" alt="Preview" class="img-fluid rounded" style="max-height: 170px; object-fit: contain; cursor: pointer;" wire:click="openPreviewModal('{{ $tempUrl }}', '{{ $clientName }}')">
                                        </div>
                                    @elseif($clientExt === 'pdf')
                                        <div class="p-3 bg-white rounded-3 border shadow-sm d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                                                <div class="text-start">
                                                    <div class="fw-bold text-dark small text-truncate" style="max-width: 200px;">{{ $clientName }}</div>
                                                    <div class="text-muted extra-small">PDF Document ({{ $fileSize }} KB)</div>
                                                </div>
                                            </div>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 small">PDF Ready</span>
                                        </div>
                                    @else
                                        <div class="p-3 bg-white rounded-3 border shadow-sm d-flex align-items-center gap-2">
                                            <i class="bi bi-file-earmark-text-fill text-primary fs-3"></i>
                                            <div class="text-start">
                                                <div class="fw-bold text-dark small text-truncate" style="max-width: 200px;">{{ $clientName }}</div>
                                                <div class="text-muted extra-small">Attached Document ({{ $fileSize }} KB)</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @elseif ($existing_upload_file)
                                @php
                                    $fileName = basename($existing_upload_file);
                                    $fileExt = strtolower(pathinfo($existing_upload_file, PATHINFO_EXTENSION));
                                    $isImg = in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'jfif']);
                                    $existingUrl = $editingId ? route('partner.hrms.expenses.file', $editingId) : asset('storage/' . $existing_upload_file);
                                    $existingType = $isImg ? 'image' : ($fileExt === 'pdf' ? 'pdf' : 'doc');
                                @endphp
                                <!-- Existing File Pill (Edit mode) -->
                                <div class="d-flex align-items-center justify-content-between gap-2 px-3 py-2 rounded-pill shadow-sm mb-2" 
                                     style="background: #f8fafc; border: 1.5px solid #cbd5e1; color: #334155;">
                                    <div class="d-flex align-items-center gap-2.5 overflow-hidden">
                                        <span class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" 
                                              style="width: 32px; height: 32px; background: #e2e8f0; color: #475569; font-size: 1rem;">
                                            <i class="bi {{ $isImg ? 'bi-file-earmark-image' : ($fileExt === 'pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-text') }}"></i>
                                        </span>
                                        <div class="d-flex flex-column text-truncate">
                                            <span class="fw-bold text-truncate text-dark" style="font-size: 0.85rem;" title="{{ $fileName }}">{{ $fileName }}</span>
                                            <a href="javascript:void(0)" wire:click="openPreviewModal('{{ $existingUrl }}', '{{ $fileName }}', '{{ $existingType }}')" class="text-primary extra-small text-decoration-none fw-semibold" style="font-size: 0.72rem;">
                                                <i class="bi bi-eye me-1"></i> Preview Attached File
                                            </a>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            wire:click="removeExistingFile" 
                                            class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" 
                                            style="width: 28px; height: 28px; font-size: 0.85rem; border-width: 1.5px;" 
                                            title="Delete existing file">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <!-- Existing Visual Preview Box -->
                                <div class="p-2.5 rounded-3 border bg-light mt-2 text-center">
                                    <div class="d-flex justify-content-between align-items-center mb-1.5 px-1">
                                        <span class="extra-small fw-bold text-muted text-uppercase"><i class="bi bi-paperclip me-1"></i>Current Uploaded File</span>
                                        <button type="button" wire:click="openPreviewModal('{{ $existingUrl }}', '{{ $fileName }}', '{{ $existingType }}')" class="btn btn-sm btn-link p-0 extra-small text-decoration-none fw-bold">
                                            <i class="bi bi-arrows-fullscreen me-1"></i>Fullscreen
                                        </button>
                                    </div>
                                    @if($isImg)
                                        <div class="rounded-3 overflow-hidden border bg-white p-1 d-inline-block shadow-sm" style="max-height: 180px;">
                                            <img src="{{ $existingUrl }}" alt="Current Document" class="img-fluid rounded" style="max-height: 170px; object-fit: contain; cursor: pointer;" wire:click="openPreviewModal('{{ $existingUrl }}', '{{ $fileName }}', 'image')">
                                        </div>
                                    @elseif($fileExt === 'pdf')
                                        <div class="p-3 bg-white rounded-3 border shadow-sm d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-file-earmark-pdf-fill text-danger fs-3"></i>
                                                <div class="text-start">
                                                    <div class="fw-bold text-dark small text-truncate" style="max-width: 200px;">{{ $fileName }}</div>
                                                    <div class="text-muted extra-small">PDF Document</div>
                                                </div>
                                            </div>
                                            <button type="button" wire:click="openPreviewModal('{{ $existingUrl }}', '{{ $fileName }}', 'pdf')" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                                                <i class="bi bi-eye me-1"></i> View PDF
                                            </button>
                                        </div>
                                    @else
                                        <div class="p-3 bg-white rounded-3 border shadow-sm d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-file-earmark-text-fill text-primary fs-3"></i>
                                                <div class="text-start">
                                                    <div class="fw-bold text-dark small text-truncate" style="max-width: 200px;">{{ $fileName }}</div>
                                                    <div class="text-muted extra-small">Attached File</div>
                                                </div>
                                            </div>
                                            <a href="{{ $existingUrl }}" target="_blank" download class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">
                                                <i class="bi bi-download me-1"></i> Download
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div wire:loading.remove wire:target="upload_file">
                                    <input type="file" wire:model="upload_file" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,image/*,application/pdf">
                                    <small class="text-muted extra-small mt-1 d-block">
                                        <i class="bi bi-info-circle me-1"></i> Upload a clear photo or PDF scan of your bill/receipt.
                                    </small>
                                </div>
                            @endif
                            @error('upload_file') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase">Expense Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="date" class="form-control">
                                @error('date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase">Approval Status</label>
                                @if(Auth::user()->canAccess('expense_status_update'))
                                <select wire:model="status" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                                @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                                @else
                                <input type="text" value="{{ ucfirst($status) }}" disabled class="form-control bg-light text-muted">
                                @endif
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Description / Notes</label>
                            <textarea wire:model="description" rows="3" class="form-control" placeholder="Details about this expense claim..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer border-top py-3">
                        <button type="button" class="btn btn-light" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4">Submit Expense Claim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Dedicated Full Preview Modal -->
    @if($isPreviewModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.75); z-index: 1065;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi {{ $previewFileType === 'image' ? 'bi-image text-success' : 'bi-file-earmark-pdf text-danger' }} fs-5"></i>
                        <h6 class="modal-title fw-bold text-dark mb-0">{{ $previewFileTitle ?: 'Receipt / Bill Preview' }}</h6>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ $previewFileUrl }}" target="_blank" download class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-box-arrow-up-right me-1"></i> Open External
                        </a>
                        <button type="button" class="btn-close" wire:click="closePreviewModal"></button>
                    </div>
                </div>
                <div class="modal-body p-3 text-center bg-dark bg-opacity-10 d-flex align-items-center justify-content-center" style="min-height: 400px; max-height: 75vh; overflow-y: auto;">
                    @if($previewFileType === 'image')
                        <div class="bg-white p-2 rounded-3 shadow-sm d-inline-block">
                            <img src="{{ $previewFileUrl }}" alt="Preview" class="img-fluid rounded" style="max-height: 65vh; max-width: 100%; object-fit: contain;">
                        </div>
                    @elseif($previewFileType === 'pdf')
                        <iframe src="{{ $previewFileUrl }}" style="width: 100%; height: 65vh; border: none; border-radius: 8px;"></iframe>
                    @else
                        <div class="p-5 text-center bg-white rounded-3 shadow-sm">
                            <i class="bi bi-file-earmark-arrow-down display-1 text-primary mb-3 d-block"></i>
                            <h5 class="fw-bold text-dark">Document Preview</h5>
                            <p class="text-muted small">Please click below to download or view this file.</p>
                            <a href="{{ $previewFileUrl }}" target="_blank" download class="btn btn-primary rounded-pill px-4 mt-2">
                                <i class="bi bi-download me-1"></i> Download / Open File
                            </a>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-white">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" wire:click="closePreviewModal">Close Preview</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
