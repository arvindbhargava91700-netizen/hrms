<div>
    <div class="row g-3 mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="stat-label">Total Packages</div>
                    <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle text-success"></i></div>
                <div>
                    <div class="stat-label">Active Packages</div>
                    <div class="stat-value">{{ number_format($stats['active'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="stat-card" style="{{ ($stats['inactive'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-secondary);' : '' }}">
                <div class="stat-icon bg-secondary bg-opacity-10"><i class="bi bi-dash-circle text-secondary"></i></div>
                <div>
                    <div class="stat-label">Inactive Packages</div>
                    <div class="stat-value">{{ number_format($stats['inactive'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Partner Packages</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#packageModal" wire:click="resetForm">
            <i class="bi bi-plus-lg me-2"></i> Add Package
        </button>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-feetrack mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Platform Fees</th>
                            <th>Modules</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $pkg)
                            <tr>
                                <td class="fw-600">
                                    {{ $pkg->name }}
                                    @if($pkg->is_free_trial) <span class="badge bg-success ms-1">Trial</span> @endif
                                </td>
                                <td>₹{{ $pkg->price }}</td>
                                <td>{{ $pkg->duration_days > 0 ? $pkg->duration_days . ' days' : 'Infinite' }}</td>
                                <td>
                                    @if($pkg->commission_type === 'percent')
                                        {{ $pkg->commission_value }}%
                                    @else
                                        ₹{{ $pkg->commission_value }}
                                    @endif
                                </td>
                                <td>
                                    @if($pkg->systemModules->count() > 0)
                                        <span class="badge bg-info">{{ $pkg->systemModules->count() }} Modules</span>
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               wire:click="toggleActive('{{ $pkg->id }}')" 
                                               {{ $pkg->is_active ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-light text-primary me-1" 
                                            wire:click="viewPackageDetails('{{ $pkg->id }}')"
                                            data-bs-toggle="modal" data-bs-target="#viewPackageModal" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light me-1" 
                                            wire:click="edit('{{ $pkg->id }}')" title="Edit Package">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light text-danger" 
                                            wire:confirm="Are you sure you want to delete this package?"
                                            wire:click="deletePackage('{{ $pkg->id }}')" title="Delete Package">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">No packages found</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div wire:ignore.self class="modal fade" id="packageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $editingId ? 'Edit Package' : 'New Package' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Stepper -->
                    <div class="stepper mb-4 px-2 d-flex justify-content-between align-items-center position-relative">
                        <div class="progress position-absolute top-50 translate-middle-y" style="height: 2px; z-index: 1; left: 12.5%; width: 75%;">
                            <div class="progress-bar transition-all" style="width: {{ ($currentStep - 1) * 33.33 }}%; transition: width 0.3s ease;"></div>
                        </div>
                        
                        <div class="step-item text-center position-relative" style="z-index: 2; width: 25%;">
                            <div class="step-circle {{ $currentStep >= 1 ? 'bg-primary text-white' : 'bg-light text-muted' }} rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1 transition-all" style="width: 32px; height: 32px; font-weight: 600; border: 2px solid {{ $currentStep >= 1 ? 'var(--bs-primary)' : '#e9ecef' }}; transition: all 0.3s ease;">1</div>
                            <div class="step-title small fw-bold {{ $currentStep >= 1 ? 'text-primary' : 'text-muted' }}">Basic Info</div>
                        </div>
                        <div class="step-item text-center position-relative" style="z-index: 2; width: 25%;">
                            <div class="step-circle {{ $currentStep >= 2 ? 'bg-primary text-white' : 'bg-light text-muted' }} rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1 transition-all" style="width: 32px; height: 32px; font-weight: 600; border: 2px solid {{ $currentStep >= 2 ? 'var(--bs-primary)' : '#e9ecef' }}; transition: all 0.3s ease;">2</div>
                            <div class="step-title small fw-bold {{ $currentStep >= 2 ? 'text-primary' : 'text-muted' }}">Platform Fees</div>
                        </div>
                        <div class="step-item text-center position-relative" style="z-index: 2; width: 25%;">
                            <div class="step-circle {{ $currentStep >= 3 ? 'bg-primary text-white' : 'bg-light text-muted' }} rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1 transition-all" style="width: 32px; height: 32px; font-weight: 600; border: 2px solid {{ $currentStep >= 3 ? 'var(--bs-primary)' : '#e9ecef' }}; transition: all 0.3s ease;">3</div>
                            <div class="step-title small fw-bold {{ $currentStep >= 3 ? 'text-primary' : 'text-muted' }}">Modules</div>
                        </div>
                        <div class="step-item text-center position-relative" style="z-index: 2; width: 25%;">
                            <div class="step-circle {{ $currentStep >= 4 ? 'bg-primary text-white' : 'bg-light text-muted' }} rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1 transition-all" style="width: 32px; height: 32px; font-weight: 600; border: 2px solid {{ $currentStep >= 4 ? 'var(--bs-primary)' : '#e9ecef' }}; transition: all 0.3s ease;">4</div>
                            <div class="step-title small fw-bold {{ $currentStep >= 4 ? 'text-primary' : 'text-muted' }}">Settings</div>
                        </div>
                    </div>

                    <!-- Step 1 -->
                    @if($currentStep == 1)
                    <div class="step-content animation-fade-in">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Package Name</label>
                            <input type="text" class="form-control" wire:model="name" placeholder="e.g. Free Tier, Gold Plan">
                            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Price (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" class="form-control" wire:model="price" placeholder="0.00">
                                </div>
                                @error('price') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Duration (Days)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" wire:model="duration_days" placeholder="0 = Infinite">
                                    <span class="input-group-text">days</span>
                                </div>
                                @error('duration_days') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Payment Gateways -->
                        <div class="p-3 bg-light rounded border mb-3">
                            <h6 class="fw-bold mb-1"><i class="bi bi-credit-card-2-front text-primary me-2"></i>Supported Payment Gateways</h6>
                            <p class="text-muted small mb-3">Select the payment methods allowed when buying this package.</p>
                            <div class="row g-2">
                                @php
                                    $gateways = [
                                        ['id' => 'upi_autopay',   'label' => 'UPI AutoPay',       'icon' => 'bi-phone-vibrate', 'color' => 'text-success'],
                                        ['id' => 'enach',         'label' => 'eNACH Auto Debit',  'icon' => 'bi-bank',          'color' => 'text-primary'],
                                        ['id' => 'netbanking',    'label' => 'Net Banking',        'icon' => 'bi-globe',         'color' => 'text-info'],
                                        ['id' => 'credit_card',   'label' => 'Credit Card',        'icon' => 'bi-credit-card',   'color' => 'text-danger'],
                                        ['id' => 'debit_card',    'label' => 'Debit Card',         'icon' => 'bi-credit-card-2-front', 'color' => 'text-warning'],
                                        ['id' => 'wallet',        'label' => 'Digital Wallet',     'icon' => 'bi-wallet2',       'color' => 'text-purple'],
                                        ['id' => 'cash',          'label' => 'Cash / Offline',     'icon' => 'bi-cash-coin',     'color' => 'text-secondary'],
                                    ];
                                @endphp
                                @foreach($gateways as $gw)
                                <div class="col-md-6">
                                    <label class="d-flex align-items-center gap-2 p-2 rounded border bg-white cursor-pointer"
                                           style="cursor:pointer; transition: border-color 0.2s;"
                                           onclick="this.style.borderColor = this.querySelector('input').checked ? '#dee2e6' : 'var(--bs-primary)'">
                                        <input class="form-check-input m-0 flex-shrink-0" type="checkbox"
                                               wire:model="payment_gateways" value="{{ $gw['id'] }}"
                                               style="cursor:pointer;">
                                        <i class="bi {{ $gw['icon'] }} {{ $gw['color'] }} fs-5"></i>
                                        <span class="small fw-semibold">{{ $gw['label'] }}</span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @error('payment_gateways') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-check form-switch p-0 d-flex align-items-center">
                            <input class="form-check-input m-0 flex-shrink-0" type="checkbox" wire:model="is_active" id="isActiveCheck" style="width:2.5em;height:1.25em;cursor:pointer;">
                            <label class="form-check-label ms-3" for="isActiveCheck">Package is Active</label>
                        </div>
                    </div>
                    @endif

                    <!-- Step 2 -->
                    @if($currentStep == 2)
                    <div class="step-content animation-fade-in">

                        <!-- Online Commission -->
                        <div class="p-3 rounded border mb-3" style="background: #f0f9f0;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-success d-flex align-items-center justify-content-center me-2" style="width:28px;height:28px;">
                                        <i class="bi bi-wifi text-white" style="font-size:0.75rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">Online Platform Fees</h6>
                                        <small class="text-muted">Applied on payments via payment gateway (UPI, Card, etc.)</small>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success fw-bold" wire:click="addCommissionRange">
                                    <i class="bi bi-plus-lg me-1"></i> Add Range Tier
                                </button>
                            </div>
                            <div class="row g-3 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Base / Default Type</label>
                                    <select class="form-select form-select-sm" wire:model="commission_type">
                                        <option value="fixed">Fixed Amount (₹)</option>
                                        <option value="percent">Percentage (%)</option>
                                    </select>
                                    @error('commission_type') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Base / Default Value</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" class="form-control" wire:model="commission_value" placeholder="0">
                                        <span class="input-group-text">{{ $commission_type === 'percent' ? '%' : '₹' }}</span>
                                    </div>
                                    @error('commission_value') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            @if(!empty($commission_ranges))
                                <div class="table-responsive bg-white rounded border p-2 mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="fw-bold text-success"><i class="bi bi-layers me-1"></i> Tiered Platform Fees Ranges (Online)</small>
                                        <small class="text-muted" style="font-size: 0.75rem;">Max empty = Above limit</small>
                                    </div>
                                    <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Min Amount (₹)</th>
                                                <th>Max Amount (₹)</th>
                                                <th>Type</th>
                                                <th>Platform Fees (with GST)</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($commission_ranges as $index => $range)
                                            <tr>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control form-control-sm" wire:model="commission_ranges.{{ $index }}.min_amount" placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control form-control-sm" wire:model="commission_ranges.{{ $index }}.max_amount" placeholder="Above / No limit">
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm" wire:model="commission_ranges.{{ $index }}.type">
                                                        <option value="percent">Percent (%)</option>
                                                        <option value="fixed">Fixed (₹)</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control form-control-sm" wire:model="commission_ranges.{{ $index }}.value" placeholder="0">
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeCommissionRange({{ $index }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- Offline Commission -->
                        <div class="p-3 rounded border mb-3" style="background: #fff8ee;">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center me-2" style="width:28px;height:28px;">
                                        <i class="bi bi-cash-coin text-white" style="font-size:0.75rem;"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0">Offline Platform Fees</h6>
                                        <small class="text-muted">Applied on cash / offline payments collected manually</small>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-warning fw-bold text-dark" wire:click="addOfflineCommissionRange">
                                    <i class="bi bi-plus-lg me-1"></i> Add Range Tier
                                </button>
                            </div>
                            <div class="row g-3 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Base / Default Type</label>
                                    <select class="form-select form-select-sm" wire:model="offline_commission_type">
                                        <option value="fixed">Fixed Amount (₹)</option>
                                        <option value="percent">Percentage (%)</option>
                                    </select>
                                    @error('offline_commission_type') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Base / Default Value</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step="0.01" class="form-control" wire:model="offline_commission_value" placeholder="0">
                                        <span class="input-group-text">{{ $offline_commission_type === 'percent' ? '%' : '₹' }}</span>
                                    </div>
                                    @error('offline_commission_value') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            @if(!empty($offline_commission_ranges))
                                <div class="table-responsive bg-white rounded border p-2 mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="fw-bold text-warning"><i class="bi bi-layers me-1"></i> Tiered Platform Fees Ranges (Offline)</small>
                                        <small class="text-muted" style="font-size: 0.75rem;">Max empty = Above limit</small>
                                    </div>
                                    <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Min Amount (₹)</th>
                                                <th>Max Amount (₹)</th>
                                                <th>Type</th>
                                                <th>Platform Fees (with GST)</th>
                                                <th class="text-end">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($offline_commission_ranges as $index => $range)
                                            <tr>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control form-control-sm" wire:model="offline_commission_ranges.{{ $index }}.min_amount" placeholder="0">
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control form-control-sm" wire:model="offline_commission_ranges.{{ $index }}.max_amount" placeholder="Above / No limit">
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm" wire:model="offline_commission_ranges.{{ $index }}.type">
                                                        <option value="percent">Percent (%)</option>
                                                        <option value="fixed">Fixed (₹)</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control form-control-sm" wire:model="offline_commission_ranges.{{ $index }}.value" placeholder="0">
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeOfflineCommissionRange({{ $index }})">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        

                    </div>
                    @endif

                    <!-- Step 3 -->
                    @if($currentStep == 3)
                    <style>
                        .module-select-card {
                            border: 2px solid #e9ecef;
                            border-radius: 12px;
                            transition: all 0.2s ease;
                            background: #fff;
                        }
                        .module-select-card:hover {
                            border-color: #dee2e6;
                            background: #f8f9fa;
                        }
                        .module-checkbox:checked ~ .module-select-card {
                            border-color: var(--bs-primary);
                            background: rgba(var(--bs-primary-rgb), 0.05);
                            box-shadow: 0 4px 12px rgba(var(--bs-primary-rgb), 0.1);
                        }
                        .module-checkbox:checked ~ .module-select-card .module-icon {
                            color: var(--bs-primary) !important;
                        }
                    </style>
                    <div class="step-content animation-fade-in">
                        <div class="mb-3">
                            <label class="form-label mb-3 fw-bold">Select System Modules to Include:</label>
                            <div class="row g-3">
                                @foreach($systemModules as $module)
                                <div class="col-md-6">
                                    <label class="w-100" style="cursor:pointer;">
                                        <input type="checkbox" wire:model="selected_modules" value="{{ $module->id }}" class="d-none module-checkbox">
                                        <div class="module-select-card p-3 d-flex align-items-center h-100">
                                            <div class="module-icon text-muted me-3" style="font-size: 1.5rem; transition: color 0.2s;">
                                                <i class="bi bi-plug"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $module->name }}</h6>
                                            </div>
                                            <div class="ms-auto">
                                                <div class="form-check mb-0">
                                                    <input class="form-check-input" type="checkbox" wire:model="selected_modules" value="{{ $module->id }}" style="pointer-events: none;">
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            @error('selected_modules') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    @endif

                    <!-- Step 4 -->
                    @if($currentStep == 4)
                    <div class="step-content animation-fade-in">
                        <div class="p-3 bg-light rounded border mb-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock text-primary me-2"></i>Usage Quotas</h6>
                            <div class="row g-3">
                                <div class="col-md-6 d-none">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Maximum Categories Allowed</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" wire:model="category_limit" placeholder="Leave blank = Unlimited">
                                    </div>
                                    @error('category_limit') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-bold text-uppercase mb-1">Maximum Listings Allowed</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" wire:model="listing_limit" placeholder="Leave blank = Unlimited">
                                    </div>
                                    @error('listing_limit') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notification Settings -->
                        <div class="p-3 bg-light rounded border mb-4">
                            <h6 class="fw-bold mb-3"><i class="bi bi-bell text-warning me-2"></i>Notification Settings</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-0 d-flex align-items-center">
                                        <input class="form-check-input m-0 flex-shrink-0" type="checkbox" wire:model="email_notification" id="emailNotificationCheck" style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                        <label class="form-check-label ms-3" for="emailNotificationCheck" style="cursor: pointer;">
                                            <i class="bi bi-envelope text-primary me-1"></i> Email Notifications
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-0 d-flex align-items-center">
                                        <input class="form-check-input m-0 flex-shrink-0" type="checkbox" wire:model="app_notification" id="appNotificationCheck" style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                        <label class="form-check-label ms-3" for="appNotificationCheck" style="cursor: pointer;">
                                            <i class="bi bi-bell text-warning me-1"></i> App Notifications
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-0 d-flex align-items-center">
                                        <input class="form-check-input m-0 flex-shrink-0" type="checkbox" wire:model="sms_notification" id="smsNotificationCheck" style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                        <label class="form-check-label ms-3" for="smsNotificationCheck" style="cursor: pointer;">
                                            <i class="bi bi-chat-text text-info me-1"></i> SMS Notifications
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch p-0 d-flex align-items-center">
                                        <input class="form-check-input m-0 flex-shrink-0" type="checkbox" wire:model="whatsapp_notification" id="whatsappNotificationCheck" style="width: 2.5em; height: 1.25em; cursor: pointer;">
                                        <label class="form-check-label ms-3" for="whatsappNotificationCheck" style="cursor: pointer;">
                                            <i class="bi bi-whatsapp text-success me-1"></i> WhatsApp Notifications
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check form-switch p-0 d-flex align-items-center border rounded p-3 bg-primary bg-opacity-10">
                            <input class="form-check-input m-0 flex-shrink-0" type="checkbox" wire:model="is_free_trial" id="isFreeTrialCheck" style="width: 2.5em; height: 1.25em; cursor: pointer;">
                            <label class="form-check-label ms-3 fw-bold" for="isFreeTrialCheck" style="cursor: pointer;">
                                Mark as Free Trial (Default package for new users)
                            </label>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer justify-content-between">
                    <div>
                        @if($currentStep > 1)
                            <button type="button" class="btn btn-light" wire:click="previousStep">Back</button>
                        @else
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        @endif
                    </div>
                    <div>
                        @if($currentStep < 4)
                            <button type="button" class="btn btn-primary" wire:click="nextStep">Next Step <i class="bi bi-arrow-right ms-1"></i></button>
                        @else
                            <button type="button" class="btn btn-primary" wire:click="save"><i class="bi bi-check-lg me-1"></i> Save Package</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Package Details Modal -->
    <div wire:ignore.self class="modal fade" id="viewPackageModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title fw-bold">Package Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if($viewPackage)
                        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                            <div>
                                <h4 class="mb-1 fw-bold text-primary">{{ $viewPackage['name'] }}</h4>
                                <span class="badge {{ $viewPackage['is_active'] ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $viewPackage['is_active'] ? 'Active' : 'Inactive' }}
                                </span>
                                @if($viewPackage['is_free_trial'])
                                    <span class="badge bg-info text-white ms-1">Trial Package</span>
                                @endif
                            </div>
                            <div class="text-end">
                                <h3 class="mb-0 fw-bold text-dark">₹{{ $viewPackage['price'] }}</h3>
                                <small class="text-muted">{{ $viewPackage['duration_days'] > 0 ? $viewPackage['duration_days'] . ' Days' : 'Lifetime' }}</small>
                            </div>
                        </div>

                        <!-- Platform Fees Cards -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-3 rounded-3 h-100" style="background:#f0f9f0; border:1px solid #c3e6cb;">
                                    <p class="text-muted small mb-1 text-uppercase fw-bold"><i class="bi bi-wifi text-success me-1"></i>Online Platform Fees</p>
                                    <h5 class="mb-0 text-success">
                                        @if($viewPackage['commission_type'] === 'percent')
                                            {{ $viewPackage['commission_value'] }}%
                                        @else
                                            ₹{{ $viewPackage['commission_value'] }}
                                        @endif
                                    </h5>
                                    <small class="text-muted d-block mb-2">Base rate</small>
                                    @if(!empty($viewPackage['commission_ranges']))
                                        <div class="border-top pt-2 mt-2">
                                            <small class="fw-bold text-dark d-block mb-1">Tiered Ranges:</small>
                                            @foreach($viewPackage['commission_ranges'] as $r)
                                                <div class="small text-muted">
                                                    ₹{{ $r['min_amount'] ?? 0 }} - {{ !empty($r['max_amount']) ? '₹'.$r['max_amount'] : 'Above' }}: 
                                                    <strong class="text-success">{{ $r['value'] }}{{ ($r['type'] ?? 'percent') === 'percent' ? '%' : '₹' }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 rounded-3 h-100" style="background:#fff8ee; border:1px solid #ffc107;">
                                    <p class="text-muted small mb-1 text-uppercase fw-bold"><i class="bi bi-cash-coin text-warning me-1"></i>Offline Platform Fees</p>
                                    <h5 class="mb-0 text-warning">
                                        @if(($viewPackage['offline_commission_type'] ?? 'fixed') === 'percent')
                                            {{ $viewPackage['offline_commission_value'] ?? 0 }}%
                                        @else
                                            ₹{{ $viewPackage['offline_commission_value'] ?? 0 }}
                                        @endif
                                    </h5>
                                    <small class="text-muted d-block mb-2">Base rate</small>
                                    @if(!empty($viewPackage['offline_commission_ranges']))
                                        <div class="border-top pt-2 mt-2">
                                            <small class="fw-bold text-dark d-block mb-1">Tiered Ranges:</small>
                                            @foreach($viewPackage['offline_commission_ranges'] as $r)
                                                <div class="small text-muted">
                                                    ₹{{ $r['min_amount'] ?? 0 }} - {{ !empty($r['max_amount']) ? '₹'.$r['max_amount'] : 'Above' }}: 
                                                    <strong class="text-warning">{{ $r['value'] }}{{ ($r['type'] ?? 'percent') === 'percent' ? '%' : '₹' }}</strong>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Usage Quotas -->
                        <div class="p-3 bg-light rounded-3 mb-3">
                            <p class="text-muted small mb-2 text-uppercase fw-bold">Usage Quotas</p>
                            <div class="d-flex flex-column gap-1">
                                <div class="d-flex justify-content-between d-none">
                                    <span class="small">Categories:</span>
                                    <span class="small fw-bold">{{ $viewPackage['category_limit'] ?: 'Unlimited' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="small">Listings:</span>
                                    <span class="small fw-bold">{{ $viewPackage['listing_limit'] ?: 'Unlimited' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Gateways -->
                        @if(!empty($viewPackage['payment_gateways']))
                        <div class="mb-3">
                            <p class="text-muted small mb-2 text-uppercase fw-bold"><i class="bi bi-credit-card-2-front me-1"></i>Payment Gateways</p>
                            <div class="d-flex flex-wrap gap-2">
                                @php
                                    $gwLabels = [
                                        'upi_autopay' => ['label'=>'UPI AutoPay','icon'=>'bi-phone-vibrate','color'=>'success'],
                                        'enach' => ['label'=>'eNACH Auto Debit','icon'=>'bi-bank','color'=>'primary'],
                                        'netbanking' => ['label'=>'Net Banking','icon'=>'bi-globe','color'=>'info'],
                                        'credit_card' => ['label'=>'Credit Card','icon'=>'bi-credit-card','color'=>'danger'],
                                        'debit_card' => ['label'=>'Debit Card','icon'=>'bi-credit-card-2-front','color'=>'warning'],
                                        'wallet' => ['label'=>'Digital Wallet','icon'=>'bi-wallet2','color'=>'secondary'],
                                        'cash' => ['label'=>'Cash / Offline','icon'=>'bi-cash-coin','color'=>'secondary'],
                                    ];
                                @endphp
                                @foreach($viewPackage['payment_gateways'] as $gw)
                                    @if(isset($gwLabels[$gw]))
                                    <span class="badge bg-{{ $gwLabels[$gw]['color'] }}-soft border border-{{ $gwLabels[$gw]['color'] }} text-{{ $gwLabels[$gw]['color'] }} px-3 py-2">
                                        <i class="bi {{ $gwLabels[$gw]['icon'] }} me-1"></i> {{ $gwLabels[$gw]['label'] }}
                                    </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <div class="mb-4">
                            <p class="text-muted small mb-2 text-uppercase fw-bold">Notification Options</p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="badge border {{ $viewPackage['email_notification'] ? 'border-primary text-primary bg-primary-soft' : 'border-secondary text-secondary bg-light' }}">
                                    <i class="bi bi-envelope me-1"></i> Email
                                </span>
                                <span class="badge border {{ $viewPackage['app_notification'] ? 'border-warning text-warning bg-warning-soft' : 'border-secondary text-secondary bg-light' }}">
                                    <i class="bi bi-bell me-1"></i> App
                                </span>
                                <span class="badge border {{ $viewPackage['sms_notification'] ? 'border-info text-info bg-info-soft' : 'border-secondary text-secondary bg-light' }}">
                                    <i class="bi bi-chat-text me-1"></i> SMS
                                </span>
                                <span class="badge border {{ $viewPackage['whatsapp_notification'] ? 'border-success text-success bg-success-soft' : 'border-secondary text-secondary bg-light' }}">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                </span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <p class="text-muted small mb-2 text-uppercase fw-bold">Included Modules</p>
                            @if(count($viewPackage['system_modules'] ?? []) > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($viewPackage['system_modules'] as $module)
                                        <span class="badge bg-primary-soft text-primary px-3 py-2 border rounded-pill">
                                            <i class="bi bi-plug me-1"></i> {{ $module['name'] }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted bg-light p-3 rounded text-center small">
                                    No extra modules included in this package.
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        // Close package modal
        Livewire.on('close-modal', () => {
            const modal = bootstrap.Modal.getInstance(document.getElementById('packageModal'));
            if(modal) modal.hide();
        });

        // Open package modal AFTER Livewire has finished populating edit data
        Livewire.on('open-edit-modal', () => {
            const el = document.getElementById('packageModal');
            let modal = bootstrap.Modal.getInstance(el);
            if (!modal) modal = new bootstrap.Modal(el);
            modal.show();
        });
    });
</script>
