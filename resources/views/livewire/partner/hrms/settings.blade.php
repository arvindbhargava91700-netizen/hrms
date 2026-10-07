<div class="container-fluid py-4">
    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4 border-bottom-0" style="gap: 0.5rem; flex-wrap: nowrap; overflow-x: auto; white-space: nowrap;">

        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'general' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0' }}" 
               wire:click.prevent="switchTab('general')" 
               href="#" 
               style="{{ $activeTab === 'general' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;' }}">
                <i class="bi bi-buildings me-1 text-primary"></i> General Settings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'holidays' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0' }}" 
               wire:click.prevent="switchTab('holidays')" 
               href="#" 
               style="{{ $activeTab === 'holidays' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;' }}">
                <i class="bi bi-calendar-event me-1"></i> Holidays
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'commissions' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0' }}" 
               wire:click.prevent="switchTab('commissions')" 
               href="#" 
               style="{{ $activeTab === 'commissions' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;' }}">
                <i class="bi bi-diagram-3 me-1"></i> Commission Levels
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'pipeline' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0' }}" 
               wire:click.prevent="switchTab('pipeline')" 
               href="#" 
               style="{{ $activeTab === 'pipeline' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;' }}">
                <i class="bi bi-distribute-vertical me-1"></i> Pipeline Config
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'checklist' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0' }}" 
               wire:click.prevent="switchTab('checklist')" 
               href="#" 
               style="{{ $activeTab === 'checklist' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;' }}">
                <i class="bi bi-list-check me-1"></i> Attendance Checklist
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'leave_categories' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0' }}" 
               wire:click.prevent="switchTab('leave_categories')" 
               href="#" 
               style="{{ $activeTab === 'leave_categories' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;' }}">
                <i class="bi bi-folder-check me-1"></i> Leave Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'expense_categories' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0' }}" 
               wire:click.prevent="switchTab('expense_categories')" 
               href="#" 
               style="{{ $activeTab === 'expense_categories' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;' }}">
                <i class="bi bi-tags me-1"></i> Expense Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'payslip_config' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0' }}" 
               wire:click.prevent="switchTab('payslip_config')" 
               href="#" 
               style="{{ $activeTab === 'payslip_config' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;' }}">
                <i class="bi bi-receipt-cutoff me-1"></i> Payslip Config
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'task_statuses' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0' }}" 
               wire:click.prevent="switchTab('task_statuses')" 
               href="#" 
               style="{{ $activeTab === 'task_statuses' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;' }}">
                <i class="bi bi-kanban me-1"></i> Task Status
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === 'performance' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0' }}" 
               wire:click.prevent="switchTab('performance')" 
               href="#" 
               style="{{ $activeTab === 'performance' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;' }}">
                <i class="bi bi-trophy-fill me-1 text-warning"></i> Performance Config
            </a>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content">

        <!-- Tab: General Settings -->
        @if($activeTab === 'general')
        <div class="row fade show active">
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0 bg-white rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-buildings me-2 text-primary"></i>General Company Settings</h5>
                            <p class="text-muted small mb-0">Configure company identity, contact information, official logo, and browser favicon. These details represent your organization across the HRMS portal.</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if (session()->has('success_general'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success_general') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form wire:submit.prevent="saveGeneralSettings">
                            <div class="row g-4">
                                <!-- Left Column: Basic Information & Contact Details -->
                                <div class="col-lg-7">
                                    <div class="p-4 rounded-3 h-100" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center">
                                            <i class="bi bi-building me-2 text-primary"></i>Company Information
                                        </h6>

                                        <div class="row g-3">
                                            <!-- Company Name -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-dark mb-1">Company Name <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-building"></i></span>
                                                    <input type="text" class="form-control bg-white" wire:model="company_name" placeholder="Enter company name">
                                                </div>
                                                @error('company_name') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                            </div>

                                            <!-- About Company -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-dark mb-1">About Company</label>
                                                <textarea class="form-control bg-white" wire:model="company_about" rows="3" placeholder="Brief description, about company, mission or overview..."></textarea>
                                                @error('company_about') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                            </div>

                                            <!-- Mobile & Email -->
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-dark mb-1">Mobile / Phone Number</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-telephone"></i></span>
                                                    <input type="text" class="form-control bg-white" wire:model="company_mobile" placeholder="e.g. +91 9876543210">
                                                </div>
                                                @error('company_mobile') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-dark mb-1">Email Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-envelope"></i></span>
                                                    <input type="email" class="form-control bg-white" wire:model="company_email" placeholder="e.g. info@company.com">
                                                </div>
                                                @error('company_email') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                            </div>

                                            <!-- Address -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-dark mb-1">Company Address</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white text-muted"><i class="bi bi-geo-alt"></i></span>
                                                    <textarea class="form-control bg-white" wire:model="company_address" rows="2" placeholder="Full registered address, office location, city, state, pincode..."></textarea>
                                                </div>
                                                @error('company_address') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Branding & Media Assets -->
                                <div class="col-lg-5">
                                    <div class="p-4 rounded-3 h-100" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center">
                                            <i class="bi bi-palette me-2 text-primary"></i>Branding & Media
                                        </h6>

                                        <div class="row g-4">
                                            <!-- Company Logo -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-dark mb-1 d-flex justify-content-between align-items-center">
                                                    <span>Company Logo</span>
                                                    @if($company_logo)
                                                        <button type="button" wire:click="removeCompanyLogo" class="btn btn-link text-danger p-0 text-decoration-none" style="font-size: 0.75rem;" onclick="confirm('Are you sure you want to remove the logo?') || event.stopImmediatePropagation()">
                                                            <i class="bi bi-trash"></i> Remove Logo
                                                        </button>
                                                    @endif
                                                </label>
                                                <input type="file" class="form-control bg-white" wire:model="new_company_logo" accept="image/*">
                                                <div class="text-muted mt-1" style="font-size: 0.75rem;">Supported: PNG, JPG, SVG, WebP (Max 3MB).</div>
                                                @error('new_company_logo') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror

                                                <div wire:loading wire:target="new_company_logo" class="text-primary small mt-2">
                                                    <span class="spinner-border spinner-border-sm me-1"></span> Uploading logo preview...
                                                </div>

                                                <!-- Logo Preview Box -->
                                                <div class="mt-2 p-3 bg-white rounded-3 border d-flex align-items-center justify-content-center text-center" style="min-height: 90px;">
                                                    @if ($new_company_logo)
                                                        <div>
                                                            <img src="{{ $new_company_logo->temporaryUrl() }}" alt="New Logo Preview" style="max-height: 60px; max-width: 100%; object-fit: contain;">
                                                            <div class="badge bg-success bg-gradient mt-2 d-block">New Logo Selected</div>
                                                        </div>
                                                    @elseif ($company_logo)
                                                        <div>
                                                            <img src="{{ asset('storage/' . $company_logo) }}" alt="Current Logo" style="max-height: 60px; max-width: 100%; object-fit: contain;">
                                                            <div class="text-muted mt-1" style="font-size: 0.75rem;">Current Active Logo</div>
                                                        </div>
                                                    @else
                                                        <div class="text-muted py-2">
                                                            <i class="bi bi-image fs-3 d-block text-secondary opacity-50"></i>
                                                            <span class="small">No logo uploaded yet</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Favicon Icon -->
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-dark mb-1 d-flex justify-content-between align-items-center">
                                                    <span>Favicon Icon</span>
                                                    @if($company_favicon)
                                                        <button type="button" wire:click="removeCompanyFavicon" class="btn btn-link text-danger p-0 text-decoration-none" style="font-size: 0.75rem;" onclick="confirm('Are you sure you want to remove the favicon?') || event.stopImmediatePropagation()">
                                                            <i class="bi bi-trash"></i> Remove Favicon
                                                        </button>
                                                    @endif
                                                </label>
                                                <input type="file" class="form-control bg-white" wire:model="new_company_favicon" accept=".ico,.png,.jpg,.jpeg,.svg">
                                                <div class="text-muted mt-1" style="font-size: 0.75rem;">Browser tab icon (ICO, PNG, SVG - Max 1MB).</div>
                                                @error('new_company_favicon') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror

                                                <div wire:loading wire:target="new_company_favicon" class="text-primary small mt-2">
                                                    <span class="spinner-border spinner-border-sm me-1"></span> Uploading favicon preview...
                                                </div>

                                                <!-- Favicon Preview Box -->
                                                <div class="mt-2 p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="rounded border p-2 bg-light d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                                                            @if ($new_company_favicon)
                                                                <img src="{{ $new_company_favicon->temporaryUrl() }}" alt="New Favicon Preview" style="width: 28px; height: 28px; object-fit: contain;">
                                                            @elseif ($company_favicon)
                                                                <img src="{{ asset('storage/' . $company_favicon) }}" alt="Current Favicon" style="width: 28px; height: 28px; object-fit: contain;">
                                                            @else
                                                                <i class="bi bi-globe2 text-secondary fs-5"></i>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold text-dark small">Browser Tab Icon Preview</div>
                                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                                @if ($new_company_favicon)
                                                                    <span class="text-success fw-bold">New favicon selected</span>
                                                                @elseif ($company_favicon)
                                                                    <span>Active Custom Favicon</span>
                                                                @else
                                                                    <span>Default System Favicon</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="col-12 pt-2">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm" wire:loading.attr="disabled">
                                            <span wire:loading.remove wire:target="saveGeneralSettings">
                                                <i class="bi bi-check2-circle me-1"></i> Save General Settings
                                            </span>
                                            <span wire:loading wire:target="saveGeneralSettings">
                                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tab: Holidays -->
        @if($activeTab === 'holidays')
        <div class="row fade show active">
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0 bg-white rounded-4 overflow-hidden">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-calendar-event me-2 text-primary"></i>Holidays & Company Leaves</h5>
                            <p class="text-muted small mb-0">Define official holidays for your company. Employees will not be allowed to mark regular attendance on holidays.</p>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if (session()->has('success_holiday'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success_holiday') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form wire:submit.prevent="addHoliday" class="row g-3 mb-4 p-3.5 rounded-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark small">Branch (Optional)</label>
                                <select class="form-select bg-white" wire:model="holiday_branch_id">
                                    <option value="">Global (All Branches)</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('holiday_branch_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-dark small">Holiday Name</label>
                                <input type="text" class="form-control bg-white" placeholder="e.g. Diwali, Independence Day" wire:model="holiday_name">
                                @error('holiday_name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-dark small">Holiday Date</label>
                                <input type="date" class="form-control bg-white" wire:model="holiday_date">
                                @error('holiday_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary fw-bold w-100 py-2">
                                    <i class="bi bi-plus-circle me-1"></i> Add
                                </button>
                            </div>
                        </form>

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                                    <tr>
                                        <th class="py-3 px-4">Holiday Name</th>
                                        <th class="py-3 px-4">Branch</th>
                                        <th class="py-3 px-4">Date</th>
                                        <th class="py-3 px-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($holidays as $holiday)
                                        <tr>
                                            <td class="py-3 px-4 align-middle fw-bold text-dark">{{ $holiday->name }}</td>
                                            <td class="py-3 px-4 align-middle">
                                                @if($holiday->branch_id)
                                                    <span class="badge bg-info text-dark rounded-pill"><i class="bi bi-building me-1"></i>{{ $holiday->branch->name }}</span>
                                                @else
                                                    <span class="badge bg-secondary rounded-pill"><i class="bi bi-globe me-1"></i>Global</span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-4 align-middle text-secondary"><i class="bi bi-calendar-check me-1 text-primary"></i>{{ \Carbon\Carbon::parse($holiday->date)->format('M d, Y') }}</td>
                                            <td class="py-3 px-4 text-end align-middle">
                                                <button wire:click="deleteHoliday({{ $holiday->id }})" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirm('Are you sure you want to remove this holiday?') || event.stopImmediatePropagation()">
                                                    <i class="bi bi-trash me-1"></i> Remove
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">
                                                <i class="bi bi-calendar-x fs-1 text-secondary d-block mb-2"></i>
                                                No holidays defined yet. Add your first holiday above.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tab: Commissions -->
        @if($activeTab === 'commissions')
        <div class="row fade show active">
            <div class="col-12">
                <!-- Commission TDS Setting Card -->
                <div class="card shadow-sm border-0 bg-white rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-percent me-2 text-primary"></i>Commission TDS Deduction Setting</h5>
                        <p class="text-muted small mb-0">Configure the Tax Deducted at Source (TDS) percentage to deduct automatically during monthly commission processing.</p>
                    </div>
                    <div class="card-body p-4">
                        @if (session()->has('success_tds'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success_tds') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form wire:submit.prevent="saveTdsSetting" class="p-3.5 rounded-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                            <div class="row align-items-center g-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold text-dark mb-1">Commission TDS Rate (%)</label>
                                    <p class="text-muted extra-small mb-0">e.g. Setting 5% will deduct ₹250 TDS from a ₹5,000 gross commission payout.</p>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" class="form-control bg-white" wire:model="commission_tds_percent" placeholder="5.00">
                                        <span class="input-group-text bg-white fw-bold">%</span>
                                    </div>
                                    @error('commission_tds_percent') <span class="text-danger extra-small d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold w-100 shadow-sm" style="font-size:0.88rem;">Save TDS %</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0 bg-white rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-diagram-3 me-2 text-primary"></i>Commission Levels Hierarchy</h5>
                        <p class="text-muted small mb-0">Define the multi-level hierarchy and commission percentages. e.g. Level 1 = Junior (5%), Level 2 = Senior (3%), Level 3 = Team Lead (2%). Order #1 is the lowest level.</p>
                    </div>
                    <div class="card-body p-4">
                        @if (session()->has('success_level'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success_level') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form wire:submit.prevent="addCommissionLevel" class="p-3.5 rounded-3 mb-4" style="background:#f8fafc; border:1px solid #e2e8f0;">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark small">Level Name</label>
                                    <input type="text" class="form-control bg-white" placeholder="e.g. Junior (L1)" wire:model="level_name">
                                    @error('level_name') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark small">Hierarchy Order</label>
                                    <input type="number" class="form-control bg-white" placeholder="e.g. 1" wire:model="level_order" min="1">
                                    <small class="text-muted extra-small d-block">Lower = lower rank.</small>
                                    @error('level_order') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark small">Commission (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control bg-white" placeholder="5.0" wire:model="commission_percent">
                                        <span class="input-group-text bg-white text-muted">%</span>
                                    </div>
                                    @error('commission_percent') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark small">Description (Optional)</label>
                                    <input type="text" class="form-control bg-white" placeholder="Internal notes" wire:model="level_description">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2.5 fw-bold w-100 shadow-sm" style="font-size:0.88rem;">
                                        <i class="bi bi-plus-circle me-1"></i> Add Level
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                                    <tr>
                                        <th class="py-3.5 px-4">Order</th>
                                        <th class="py-3.5 px-4">Level Name</th>
                                        <th class="py-3.5 px-4">Commission %</th>
                                        <th class="py-3.5 px-4">Description</th>
                                        <th class="py-3.5 px-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($commissionLevels as $level)
                                        <tr>
                                            <td class="py-3.5 px-4 align-middle fw-bold text-dark">#{{ $level->level_order }}</td>
                                            <td class="py-3.5 px-4 align-middle text-dark fw-semibold">{{ $level->level_name }}</td>
                                            <td class="py-3.5 px-4 align-middle"><span class="badge bg-success bg-gradient rounded-pill px-3 py-1.5 fw-bold fs-6">{{ $level->commission_percent }}%</span></td>
                                            <td class="py-3.5 px-4 align-middle text-muted small">{{ $level->description ?: '-' }}</td>
                                            <td class="py-3.5 px-4 text-end align-middle">
                                                <button wire:click="deleteCommissionLevel({{ $level->id }})" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirm('Are you sure you want to remove this commission level?') || event.stopImmediatePropagation()">
                                                    <i class="bi bi-trash me-1"></i> Remove
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">
                                                <i class="bi bi-diagram-3 fs-1 text-secondary d-block mb-2"></i>
                                                No commission levels defined yet. Create your first level above.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Tab: Pipeline Config -->
        @if($activeTab === 'pipeline')
        <div class="row fade show active">
            <div class="col-12">
                @livewire('partner.hrms.pipeline-settings')
            </div>
        </div>
        @endif

        <!-- Tab: Attendance Checklist -->
        @if($activeTab === 'checklist')
        <div class="row fade show active">
            <div class="col-12">
                @livewire('partner.hrms.attendance.attendance-settings')
            </div>
        </div>
        @endif

        <!-- Tab: Leave Categories -->
        @if($activeTab === 'leave_categories')
        <div class="row fade show active">
            <div class="col-12">
                @livewire('partner.hrms.leave-categories')
            </div>
        </div>
        @endif

        <!-- Tab: Expense Categories -->
        @if($activeTab === 'expense_categories')
        <div class="row fade show active">
            <div class="col-12">
                @livewire('partner.hrms.expense-categories')
            </div>
        </div>
        @endif

        <!-- Tab: Task Statuses -->
        @if($activeTab === 'task_statuses')
        <div class="row fade show active">
            <div class="col-12">
                @livewire('partner.hrms.task-statuses')
            </div>
        </div>
        @endif

        <!-- Tab: Payslip Config -->
        @if($activeTab === 'payslip_config')
        <div class="row fade show active">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-white rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Payslip Configuration</h5>
                        <p class="text-muted small mb-0">Configure the details that appear on the downloaded salary slips (payslips) for your employees. The live preview updates as you type.</p>
                    </div>
                    <div class="card-body p-4">
                        @if (session()->has('success_payslip'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success_payslip') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="row g-4">
                            <!-- Left Side: Form -->
                            <div class="col-lg-6">
                                <form wire:submit.prevent="savePayslipSettings" class="p-4 rounded-3 h-100" style="background:#f8fafc; border:1px solid #e2e8f0;">
                                    <h6 class="fw-bold text-dark mb-4 border-bottom pb-2">Company Details</h6>
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark mb-1">Company Logo (Optional)</label>
                                            <input type="file" class="form-control bg-white" wire:model="new_payslip_logo" accept="image/*">
                                            <div class="text-muted mt-1" style="font-size: 0.75rem;">Max size: 2MB. Max dimensions: 1024x1024 pixels.</div>
                                            @error('new_payslip_logo') <span class="text-danger extra-small d-block">{{ $message }}</span> @enderror
                                            @if ($new_payslip_logo)
                                                <div class="mt-2 text-success small"><i class="bi bi-check-circle me-1"></i> New logo ready to save.</div>
                                            @elseif ($payslip_logo)
                                                <div class="mt-2"><img src="{{ asset('storage/' . $payslip_logo) }}" alt="Current Logo" style="max-height: 40px;"></div>
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark mb-1">Company Name</label>
                                            <input type="text" class="form-control bg-white" wire:model.live="payslip_company_name" placeholder="Enter Company Name">
                                            @error('payslip_company_name') <span class="text-danger extra-small d-block">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark mb-1">Company Address</label>
                                            <textarea class="form-control bg-white" wire:model.live="payslip_company_address" rows="2" placeholder="Enter full company address to appear on payslips"></textarea>
                                            @error('payslip_company_address') <span class="text-danger extra-small d-block">{{ $message }}</span> @enderror
                                        </div>
                                        
                                        <h6 class="fw-bold text-dark mb-2 mt-4 border-bottom pb-2">Signatory Details</h6>
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark mb-1">Digital Signature (Optional)</label>
                                            <input type="file" class="form-control bg-white" wire:model="new_payslip_signature" accept="image/*">
                                            <div class="text-muted mt-1" style="font-size: 0.75rem;">Max size: 2MB. Max dimensions: 1024x1024 pixels.</div>
                                            @error('new_payslip_signature') <span class="text-danger extra-small d-block">{{ $message }}</span> @enderror
                                            @if ($new_payslip_signature)
                                                <div class="mt-2 text-success small"><i class="bi bi-check-circle me-1"></i> New signature ready to save.</div>
                                            @elseif ($payslip_signature)
                                                <div class="mt-2"><img src="{{ asset('storage/' . $payslip_signature) }}" alt="Current Signature" style="max-height: 40px;"></div>
                                            @endif
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark mb-1">Authorized Signatory Name/Title</label>
                                            <input type="text" class="form-control bg-white" wire:model.live="payslip_authorized_signatory" placeholder="e.g. HR Manager / Director">
                                            @error('payslip_authorized_signatory') <span class="text-danger extra-small d-block">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-12" wire:ignore>
                                            <label class="form-label fw-bold text-dark mb-1">Terms & Conditions (Optional)</label>
                                            <div x-data="{ 
                                                    content: @entangle('payslip_terms_conditions').live,
                                                    initQuill() {
                                                        if (typeof Quill === 'undefined') return;
                                                        let quill = new Quill($refs.editor, {
                                                            theme: 'snow',
                                                            modules: {
                                                                toolbar: [
                                                                    ['bold', 'italic', 'underline'],
                                                                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                                                    ['clean']
                                                                ]
                                                            },
                                                            placeholder: 'Enter any terms and conditions to display on the payslip...'
                                                        });
                                                        
                                                        quill.root.innerHTML = this.content || '';
                                                        
                                                        quill.on('text-change', () => {
                                                            this.content = quill.root.innerHTML;
                                                        });
                                                        
                                                        this.$watch('content', (val) => {
                                                            if (val !== quill.root.innerHTML) {
                                                                quill.root.innerHTML = val || '';
                                                            }
                                                        });
                                                    }
                                                 }"
                                                 x-init="initQuill()">
                                                <div x-ref="editor" class="bg-white" style="min-height: 120px; border-radius: 0 0 6px 6px;"></div>
                                            </div>
                                            @error('payslip_terms_conditions') <span class="text-danger extra-small d-block mt-1">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-12 text-end mt-4">
                                            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm rounded-pill w-100">
                                                <i class="bi bi-save me-1"></i> Save Configuration
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Right Side: Live Preview -->
                            <div class="col-lg-6">
                                <div class="p-4 rounded-3 h-100 d-flex flex-column" style="background:#f1f5f9; border:1px dashed #cbd5e1;">
                                    <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-eye me-2"></i>Live Demo Preview</h6>
                                    
                                    <!-- Payslip Mockup -->
                                    <div class="bg-white rounded shadow-sm p-4 flex-grow-1" style="font-family: Arial, sans-serif;">
                                        <!-- Header Preview -->
                                        <div class="d-flex justify-content-between border-bottom pb-3 mb-3">
                                            <div>
                                                @if($new_payslip_logo)
                                                    <img src="{{ $new_payslip_logo->temporaryUrl() }}" alt="Logo" style="width: 200px; height: 80px; object-fit: contain; margin-bottom:10px; display:block; object-position: left;">
                                                @elseif($payslip_logo)
                                                    <img src="{{ Storage::url($payslip_logo) }}" alt="Logo" style="width: 200px; height: 80px; object-fit: contain; margin-bottom:10px; display:block; object-position: left;">
                                                @endif
                                                <div class="fw-bold" style="color: #0056b3; font-size: 1.2rem;">{{ $payslip_company_name ?: 'Company Name' }}</div>
                                                <div class="text-muted" style="font-size: 0.75rem; max-width: 200px; white-space: pre-line;">{{ $payslip_company_address ?: 'Company Address' }}</div>
                                            </div>
                                            <div class="text-end text-muted" style="font-size: 0.7rem;">
                                                Payslip for {{ now()->format('M Y') }}<br>
                                                Status: <span class="badge bg-success" style="font-size:0.6rem;">PAID</span>
                                            </div>
                                        </div>

                                        <!-- Body Mockup -->
                                        <div class="p-3 bg-light rounded text-center text-muted mb-3" style="font-size: 0.85rem; border: 1px dashed #dee2e6;">
                                            <p class="mb-1 fw-bold text-dark">Employee & Salary Details</p>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Basic Salary</span>
                                                <span class="text-dark">₹25,000</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Allowances</span>
                                                <span class="text-dark">₹10,000</span>
                                            </div>
                                            <div class="d-flex justify-content-between pt-2 border-top fw-bold text-dark">
                                                <span>Net Pay</span>
                                                <span>₹35,000</span>
                                            </div>
                                        </div>
                                        
                                        @if($payslip_terms_conditions)
                                        <div class="mb-3">
                                            <style>
                                                .terms-preview-content ul, .terms-preview-content ol { padding-left: 1.5rem; margin-bottom: 0.5rem; }
                                                .terms-preview-content li { margin-bottom: 0.25rem; }
                                                .terms-preview-content p { margin-bottom: 0.5rem; }
                                            </style>
                                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.75rem;">Terms & Conditions</h6>
                                            <div class="text-muted terms-preview-content" style="font-size: 0.65rem;">{!! $payslip_terms_conditions !!}</div>
                                        </div>
                                        @endif

                                        <!-- Footer Preview -->
                                        <div class="d-flex justify-content-between mt-auto pt-4">
                                            <div class="text-muted" style="font-size: 0.65rem; align-self: flex-end;">
                                                This is a system generated payslip.
                                            </div>
                                            <div class="text-center" style="width: 150px;">
                                                @if($new_payslip_signature)
                                                    <img src="{{ $new_payslip_signature->temporaryUrl() }}" alt="Signature" style="width: 150px; height: 60px; object-fit: contain; margin-bottom:5px;">
                                                @elseif($payslip_signature)
                                                    <img src="{{ Storage::url($payslip_signature) }}" alt="Signature" style="width: 150px; height: 60px; object-fit: contain; margin-bottom:5px;">
                                                @else
                                                    <div style="height:60px; margin-bottom:5px;"></div>
                                                    <div style="border-top: 1px solid #333; margin-top:2px;"></div>
                                                @endif
                                                <div class="fw-bold text-dark mt-1" style="font-size: 0.75rem;">{{ $payslip_authorized_signatory ?: 'Authorized Signatory' }}</div>
                                                <div class="text-muted" style="font-size: 0.65rem;">{{ $payslip_company_name ?: 'Company Name' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Tab: Performance Config -->
        @if($activeTab === 'performance')
        @php
            $customConfigCount = count($perf_staff_overrides);
            $totalStaffCount = $staffMembers->count();
            $defaultConfigCount = max(0, $totalStaffCount - $customConfigCount);
        @endphp
        <div class="row fade show active">
            <!-- Staff-Wise Score Configuration -->
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0 bg-white rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-3 bg-primary bg-opacity-10 p-2.5 text-primary">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="fw-bold mb-0 text-dark">Staff Performance Score Configuration</h5>
                                    <span class="badge bg-purple-subtle text-purple border rounded-pill small" style="background:#f3e8ff; color:#7c3aed; border-color:#d8b4fe;">Dynamic Per-Staff</span>
                                </div>
                                <p class="text-muted small mb-0">View all staff members and set custom score evaluation points for specific employees (e.g. Sales, Operations, Field staff).</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">
                                <i class="bi bi-person-check-fill text-primary me-1"></i> Total: <strong>{{ $totalStaffCount }}</strong>
                            </span>
                            <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-2 rounded-pill small">
                                <i class="bi bi-sliders text-warning me-1"></i> Custom: <strong>{{ $customConfigCount }}</strong>
                            </span>
                            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill small">
                                <i class="bi bi-building text-secondary me-1"></i> Default: <strong>{{ $defaultConfigCount }}</strong>
                            </span>
                            @if($customConfigCount > 0)
                                <button type="button" wire:click="resetAllStaffToDefault" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5" onclick="confirm('Are you sure you want to reset all staff back to company default weights?') || event.stopImmediatePropagation()">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset All to Default
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if (session()->has('success_staff_performance'))
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success_staff_performance') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Filter Controls -->
                        <div class="row g-3 mb-4 p-3 rounded-4" style="background:#f8fafc; border: 1px solid #e2e8f0;">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" class="form-control bg-white border-start-0 ps-0" placeholder="Search staff by name, employee code, or email..." wire:model.live.debounce.300ms="staffSearch">
                                    @if(!empty($staffSearch))
                                        <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" wire:click="$set('staffSearch', '')"><i class="bi bi-x-lg"></i></button>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5">
                                <select class="form-select bg-white" wire:model.live="staffDeptFilter">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Staff Table -->
                        <div class="table-responsive rounded-4 border">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                                    <tr>
                                        <th class="py-3.5 px-4">Staff Member</th>
                                        <th class="py-3.5 px-3">Department & Role</th>
                                        <th class="py-3.5 px-3 text-center">Score Mode</th>
                                        <th class="py-3.5 px-3 text-center">Point Weight Distribution</th>
                                        <th class="py-3.5 px-4 text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($staffMembers as $staff)
                                        @php
                                            $hasCustom = isset($perf_staff_overrides[$staff->id]);
                                            $sAtt = $hasCustom ? (int)$perf_staff_overrides[$staff->id]['attendance'] : $perf_attendance_weight;
                                            $sTask = $hasCustom ? (int)$perf_staff_overrides[$staff->id]['tasks'] : $perf_tasks_weight;
                                            $sMerch = $hasCustom ? (int)$perf_staff_overrides[$staff->id]['merchant'] : $perf_merchant_target_weight;
                                            $sMonth = $hasCustom ? (int)$perf_staff_overrides[$staff->id]['monthly'] : $perf_monthly_target_weight;
                                            $sTotal = $sAtt + $sTask + $sMerch + $sMonth;
                                        @endphp
                                        <tr>
                                            <td class="py-3 px-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="{{ $staff->avatar_url }}" alt="{{ $staff->name }}" class="rounded-circle border shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold text-dark fs-6">{{ $staff->name }}</div>
                                                        <div class="text-muted extra-small d-flex align-items-center gap-2">
                                                            <span class="badge bg-light text-secondary border">{{ $staff->employee_code ?: 'EMP-'.substr($staff->id,0,4) }}</span>
                                                            <span>{{ $staff->email }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3 px-3">
                                                <div class="fw-semibold text-dark small">{{ $staff->department?->name ?? 'General Department' }}</div>
                                                <div class="text-muted extra-small">
                                                    <i class="bi bi-person-badge me-1"></i>{{ $staff->designation?->name ?? 'Employee' }}
                                                    @if($staff->branch)
                                                        <span class="text-secondary ms-1">&bull; <i class="bi bi-geo-alt me-0.5"></i>{{ $staff->branch->name }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                @if($hasCustom)
                                                    <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.78rem;">
                                                        <i class="bi bi-sliders text-warning me-1"></i>Custom ({{ $sTotal }} pts)
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                                                        <i class="bi bi-building me-1"></i>Company Default
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <div class="d-flex justify-content-center align-items-center gap-1.5 flex-wrap">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 py-1" title="Attendance" style="font-size:0.75rem;">
                                                        Att: <strong>{{ $sAtt }}</strong>
                                                    </span>
                                                    <span class="badge rounded-pill px-2 py-1" title="Tasks" style="background:rgba(139,92,246,0.1); color:#8b5cf6; border:1px solid rgba(139,92,246,0.2); font-size:0.75rem;">
                                                        Task: <strong>{{ $sTask }}</strong>
                                                    </span>
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill px-2 py-1" title="Merchant Target" style="font-size:0.75rem;">
                                                        Merch: <strong>{{ $sMerch }}</strong>
                                                    </span>
                                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle rounded-pill px-2 py-1" title="Monthly Target" style="font-size:0.75rem;">
                                                        Month: <strong>{{ $sMonth }}</strong>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-end">
                                                <div class="d-flex justify-content-end align-items-center gap-2">
                                                    <button type="button" wire:click="openEmployeePerfModal('{{ $staff->id }}')" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold shadow-sm" style="font-size:0.82rem;">
                                                        <i class="bi bi-sliders me-1"></i> Configure
                                                    </button>
                                                    @if($hasCustom)
                                                        <button type="button" wire:click="resetEmployeeToDefault('{{ $staff->id }}')" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1.5" title="Reset to Company Default" onclick="confirm('Reset {{ addslashes($staff->name) }} to company default weights?') || event.stopImmediatePropagation()">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">
                                                <i class="bi bi-people fs-1 text-secondary d-block mb-2"></i>
                                                @if(!empty($staffSearch) || !empty($staffDeptFilter))
                                                    No staff members found matching your search or filters.
                                                @else
                                                    No staff members registered in your company yet.
                                                @endif
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Modal: Configure Individual Staff Score Weights -->
        @if($show_employee_modal)
        @php
            $empTotalPoints = (int)$employee_perf_attendance_weight + (int)$employee_perf_tasks_weight + (int)$employee_perf_merchant_target_weight + (int)$employee_perf_monthly_target_weight;
            $isEmpBalanced = ($empTotalPoints === 100);
        @endphp
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 1055;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <!-- Modal Header -->
                    <div class="modal-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $editing_employee_avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($editing_employee_name).'&background=2563EB&color=fff' }}" alt="{{ $editing_employee_name }}" class="rounded-circle border shadow-sm" style="width: 46px; height: 46px; object-fit: cover;">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="modal-title fw-bold text-dark mb-0">{{ $editing_employee_name }}</h5>
                                    <span class="badge bg-light text-secondary border rounded-pill small">{{ $editing_employee_code }}</span>
                                </div>
                                <div class="text-muted extra-small">
                                    <span>{{ $editing_employee_dept }}</span> &bull; <span>{{ $editing_employee_designation }}</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" wire:click="closeEmployeePerfModal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body p-4 bg-light">
                        <!-- Total Status Alert -->
                        <div class="d-flex justify-content-between align-items-center p-3 rounded-4 mb-3 bg-white shadow-sm border">
                            <div>
                                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-sliders me-1 text-primary"></i>Custom Evaluation Weightage</h6>
                                <small class="text-muted extra-small">Total points must sum to exactly 100.</small>
                            </div>
                            <div>
                                @if($isEmpBalanced)
                                    <span class="badge bg-success bg-gradient rounded-pill px-3 py-2 shadow-sm fs-7">
                                        <i class="bi bi-check-circle-fill me-1"></i>Total: 100 / 100 Pts (Balanced)
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-gradient rounded-pill px-3 py-2 shadow-sm fs-7">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Total: {{ $empTotalPoints }} / 100 Pts ({{ $empTotalPoints > 100 ? '+' . ($empTotalPoints - 100) . ' Over' : '-' . (100 - $empTotalPoints) . ' Remaining' }})
                                    </span>
                                @endif
                            </div>
                        </div>

                        @error('employee_perf_sum')
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3 border-0" role="alert">
                                <i class="bi bi-x-octagon-fill me-2"></i>{{ $message }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @enderror

                        <!-- Visualizer Bar -->
                        <div class="mb-3 p-3 rounded-4 bg-white border">
                            <div class="progress rounded-pill" style="height: 14px; background-color: #e2e8f0;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, $employee_perf_attendance_weight) }}%;" title="Attendance">
                                    @if($employee_perf_attendance_weight >= 10) {{ $employee_perf_attendance_weight }}% @endif
                                </div>
                                <div class="progress-bar" role="progressbar" style="width: {{ min(100, $employee_perf_tasks_weight) }}%; background-color: #8b5cf6;" title="Tasks">
                                    @if($employee_perf_tasks_weight >= 10) {{ $employee_perf_tasks_weight }}% @endif
                                </div>
                                <div class="progress-bar bg-info" role="progressbar" style="width: {{ min(100, $employee_perf_merchant_target_weight) }}%;" title="Merchant">
                                    @if($employee_perf_merchant_target_weight >= 10) {{ $employee_perf_merchant_target_weight }}% @endif
                                </div>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ min(100, $employee_perf_monthly_target_weight) }}%;" title="Monthly">
                                    @if($employee_perf_monthly_target_weight >= 10) {{ $employee_perf_monthly_target_weight }}% @endif
                                </div>
                            </div>
                        </div>

                        <!-- Presets -->
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" wire:click="setEmployeePerfPreset(25, 25, 25, 25)" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs">
                                    Equal Split (25/25/25/25)
                                </button>
                                <button type="button" wire:click="setEmployeePerfPreset(15, 15, 35, 35)" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs">
                                    Sales Heavy (15/15/35/35)
                                </button>
                                <button type="button" wire:click="setEmployeePerfPreset(20, 40, 20, 20)" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs">
                                    Tasks Heavy (20/40/20/20)
                                </button>
                                <button type="button" wire:click="setEmployeePerfPreset(35, 25, 20, 20)" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs">
                                    Attendance Heavy (35/25/20/20)
                                </button>
                            </div>
                        </div>

                        <!-- 4 Interactive Parameter Cards -->
                        <div class="row g-3">
                            <!-- 1. Attendance -->
                            <div class="col-md-6">
                                <div class="card border-0 rounded-4 shadow-sm p-3 bg-white" style="border-top: 3px solid #3b82f6 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-calendar2-check-fill text-primary"></i>
                                            <span class="fw-bold text-dark small">Attendance</span>
                                        </div>
                                        <span class="badge bg-primary rounded-pill px-2 py-1 fs-7 fw-bold">{{ $employee_perf_attendance_weight }} pts</span>
                                    </div>
                                    <div class="input-group input-group-sm mb-2">
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_attendance_weight', -5)">-5</button>
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_attendance_weight', -1)">-1</button>
                                        <input type="number" min="0" max="100" class="form-control text-center fw-bold bg-light" wire:model.live.debounce.300ms="employee_perf_attendance_weight">
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_attendance_weight', 1)">+1</button>
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_attendance_weight', 5)">+5</button>
                                    </div>
                                    <input type="range" class="form-range" min="0" max="100" wire:model.live="employee_perf_attendance_weight">
                                </div>
                            </div>

                            <!-- 2. Tasks -->
                            <div class="col-md-6">
                                <div class="card border-0 rounded-4 shadow-sm p-3 bg-white" style="border-top: 3px solid #8b5cf6 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-check2-square text-purple" style="color:#8b5cf6;"></i>
                                            <span class="fw-bold text-dark small">Tasks Deliverables</span>
                                        </div>
                                        <span class="badge rounded-pill px-2 py-1 fs-7 fw-bold" style="background-color: #8b5cf6; color: white;">{{ $employee_perf_tasks_weight }} pts</span>
                                    </div>
                                    <div class="input-group input-group-sm mb-2">
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_tasks_weight', -5)">-5</button>
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_tasks_weight', -1)">-1</button>
                                        <input type="number" min="0" max="100" class="form-control text-center fw-bold bg-light" wire:model.live.debounce.300ms="employee_perf_tasks_weight">
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_tasks_weight', 1)">+1</button>
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_tasks_weight', 5)">+5</button>
                                    </div>
                                    <input type="range" class="form-range" min="0" max="100" wire:model.live="employee_perf_tasks_weight">
                                </div>
                            </div>

                            <!-- 3. Merchant Target -->
                            <div class="col-md-6">
                                <div class="card border-0 rounded-4 shadow-sm p-3 bg-white" style="border-top: 3px solid #06b6d4 !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-shop text-info"></i>
                                            <span class="fw-bold text-dark small">Merchant Target</span>
                                        </div>
                                        <span class="badge bg-info text-dark rounded-pill px-2 py-1 fs-7 fw-bold">{{ $employee_perf_merchant_target_weight }} pts</span>
                                    </div>
                                    <div class="input-group input-group-sm mb-2">
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_merchant_target_weight', -5)">-5</button>
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_merchant_target_weight', -1)">-1</button>
                                        <input type="number" min="0" max="100" class="form-control text-center fw-bold bg-light" wire:model.live.debounce.300ms="employee_perf_merchant_target_weight">
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_merchant_target_weight', 1)">+1</button>
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_merchant_target_weight', 5)">+5</button>
                                    </div>
                                    <input type="range" class="form-range" min="0" max="100" wire:model.live="employee_perf_merchant_target_weight">
                                </div>
                            </div>

                            <!-- 4. Monthly Target -->
                            <div class="col-md-6">
                                <div class="card border-0 rounded-4 shadow-sm p-3 bg-white" style="border-top: 3px solid #f59e0b !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-graph-up-arrow text-warning"></i>
                                            <span class="fw-bold text-dark small">Monthly Target</span>
                                        </div>
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1 fs-7 fw-bold">{{ $employee_perf_monthly_target_weight }} pts</span>
                                    </div>
                                    <div class="input-group input-group-sm mb-2">
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_monthly_target_weight', -5)">-5</button>
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_monthly_target_weight', -1)">-1</button>
                                        <input type="number" min="0" max="100" class="form-control text-center fw-bold bg-light" wire:model.live.debounce.300ms="employee_perf_monthly_target_weight">
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_monthly_target_weight', 1)">+1</button>
                                        <button type="button" class="btn btn-outline-secondary" wire:click="adjustEmployeePerformanceWeight('employee_perf_monthly_target_weight', 5)">+5</button>
                                    </div>
                                    <input type="range" class="form-range" min="0" max="100" wire:model.live="employee_perf_monthly_target_weight">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="closeEmployeePerfModal">
                            Cancel
                        </button>
                        <div class="d-flex align-items-center gap-2">
                            @if(isset($perf_staff_overrides[$editing_employee_id]))
                                <button type="button" wire:click="resetEmployeeToDefault('{{ $editing_employee_id }}')" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-semibold" wire:click="closeEmployeePerfModal">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Default
                                </button>
                            @endif
                            <button type="button" wire:click="saveEmployeePerformanceSettings" wire:loading.attr="disabled" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" @if(!$isEmpBalanced) disabled @endif>
                                <span wire:loading.remove wire:target="saveEmployeePerformanceSettings">
                                    <i class="bi bi-check-lg me-1"></i> Save Staff Score
                                </span>
                                <span wire:loading wire:target="saveEmployeePerformanceSettings">
                                    <span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif
    </div>
</div>

@push('scripts')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
@endpush
