<div class="row g-4">
    <div class="col-lg-4">
        <!-- Main User Profile Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body text-center p-4">
                @if ($profile_image)
                    <img src="{{ $profile_image->temporaryUrl() }}" class="rounded-circle mb-3 shadow-sm" width="92" height="92" alt="avatar" style="object-fit: cover;">
                @else
                    <img src="{{ auth()->user()->avatar_url }}" class="rounded-circle mb-3 shadow-sm" width="92" height="92" alt="avatar" style="object-fit: cover;">
                @endif
                <h5 class="mb-1 fw-bold">{{ auth()->user()->name }}</h5>
                <div class="text-muted small">{{ auth()->user()->email }}</div>
                <div class="text-muted small">{{ auth()->user()->mobile ?? 'No mobile number' }}</div>
                @if(auth()->user()->employee_code)
                    <div class="badge bg-secondary bg-opacity-10 text-secondary mt-1">ID: {{ auth()->user()->employee_code }}</div>
                @endif

                <div class="mt-3 d-flex justify-content-center gap-2 flex-wrap">
                    <span class="badge-status badge-{{ auth()->user()->status }}">{{ ucfirst(auth()->user()->status) }}</span>
                    <span class="badge-status badge-primary">{{ ucfirst(auth()->user()->role) }}</span>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'employee')
        <!-- Separate Reporting Manager Details Card -->
        @if(auth()->user()->manager || auth()->user()->department)
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-person-badge text-primary me-2 fs-5"></i> Reporting Manager
                </h6>
            </div>
            <div class="card-body p-4">
                @if(auth()->user()->manager)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ auth()->user()->manager->avatar_url }}" class="rounded-circle shadow-sm" width="48" height="48" alt="avatar" style="object-fit: cover;">
                        <div>
                            <div class="fw-bold text-dark fs-6">{{ auth()->user()->manager->name }}</div>
                            <div class="text-muted small">{{ auth()->user()->manager->role ? ucfirst(auth()->user()->manager->role) : 'Manager' }}</div>
                        </div>
                    </div>

                    @if(auth()->user()->manager->email)
                        <div class="text-muted small mb-1">
                            <i class="bi bi-envelope me-2 text-primary"></i>{{ auth()->user()->manager->email }}
                        </div>
                    @endif

                    @if(auth()->user()->manager->mobile)
                        <div class="text-muted small mb-1">
                            <i class="bi bi-telephone me-2 text-primary"></i>{{ auth()->user()->manager->mobile }}
                        </div>
                    @endif
                @endif

                @if(auth()->user()->department)
                    <div class="{{ auth()->user()->manager ? 'pt-3 mt-3 border-top' : '' }}">
                        <div class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <i class="bi bi-diagram-3 text-primary me-1"></i> Department
                        </div>
                        <div class="fw-bold text-dark">{{ auth()->user()->department->name }}</div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Employment Details Card -->
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-briefcase text-primary me-2 fs-5"></i> Employment Details
                </h6>
            </div>
            <div class="card-body p-4">
                @if(auth()->user()->joining_date)
                <div class="{{ auth()->user()->branch || auth()->user()->shift || auth()->user()->working_mode ? 'mb-3' : '' }}">
                    <div class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-calendar-check text-primary me-1"></i> Joining Date
                    </div>
                    <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse(auth()->user()->joining_date)->format('d M, Y') }}</div>
                </div>
                @endif

                <div class="{{ auth()->user()->branch || auth()->user()->shift ? 'mb-3' : '' }}">
                    <div class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-geo-alt text-primary me-1"></i> Fixed Working Mode
                    </div>
                    <div class="fw-bold text-dark">{{ ucfirst(auth()->user()->working_mode ?? 'Office') }}</div>
                </div>

                @if(auth()->user()->branch)
                <div class="{{ auth()->user()->shift ? 'mb-3 pt-3 border-top' : 'pt-3 border-top' }}">
                    <div class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-building text-primary me-1"></i> Branch / Location
                    </div>
                    <div class="fw-bold text-dark">{{ auth()->user()->branch->name }}</div>
                </div>
                @endif

                @if(auth()->user()->shift)
                <div class="pt-3 border-top">
                    <div class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-clock text-primary me-1"></i> Work Shift
                    </div>
                    <div class="fw-bold text-dark">{{ auth()->user()->shift->name }}</div>
                    <div class="text-muted small">{{ \Carbon\Carbon::parse(auth()->user()->shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse(auth()->user()->shift->end_time)->format('h:i A') }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Basic Salary Details -->
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                            <i class="bi bi-cash-stack text-primary fs-5"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold text-dark">Basic Salary</h6>
                            <div class="text-muted small">Per Month</div>
                        </div>
                    </div>
                    <span class="fw-600 fw-bold text-primary">₹{{ number_format(auth()->user()->basic_salary ?? 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Salary Structure Overview -->
        @if(isset($salaryStructure) && $salaryStructure)
            @php
                $totalAllowances = is_array($salaryStructure->allowances) ? array_sum($salaryStructure->allowances) : (is_numeric($salaryStructure->allowances) ? $salaryStructure->allowances : 0);
                $totalDeductions = is_array($salaryStructure->deductions) ? array_sum($salaryStructure->deductions) : (is_numeric($salaryStructure->deductions) ? $salaryStructure->deductions : 0);
            @endphp
            <div class="card border-0 shadow-sm mt-4 rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-wallet2 text-primary me-2 fs-5"></i>Salary Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Salary Type</span>
                        <span class="fw-bold small">{{ ucfirst(str_replace('_', ' ', $salaryStructure->salary_type)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Basic Salary</span>
                        <span class="fw-bold small">₹{{ number_format($salaryStructure->basic_salary, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Allowances</span>
                        <span class="fw-bold text-success small">+ ₹{{ number_format($totalAllowances, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Deductions</span>
                        <span class="fw-bold text-danger small">- ₹{{ number_format($totalDeductions, 2) }}</span>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark small mb-0">Net Salary</span>
                        <span class="fw-bold text-primary mb-0">₹{{ number_format($salaryStructure->net_salary, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4 rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bullseye text-success me-2 fs-5"></i>Target & Commission</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Monthly Target</span>
                        <span class="fw-bold small">₹{{ number_format($salaryStructure->monthly_target, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Base Commission</span>
                        <span class="fw-bold small">{{ $salaryStructure->commission_percent }}%</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Recovery Commission</span>
                        <span class="fw-bold small">{{ $salaryStructure->recovery_percent }}%</span>
                    </div>
                </div>
            </div>
        @endif
        @endif
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark">Update Profile</h6>
            </div>
            <div class="card-body p-4">

                <form wire:submit.prevent="save" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Name</label>
                        <input type="text" class="form-control {{ auth()->user()->role === 'employee' ? 'bg-light' : '' }}" wire:model.defer="name" {{ auth()->user()->role === 'employee' ? 'disabled' : '' }}>
                        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Email</label>
                        <input type="email" class="form-control {{ auth()->user()->role === 'employee' ? 'bg-light' : '' }}" wire:model.defer="email" {{ auth()->user()->role === 'employee' ? 'disabled' : '' }}>
                        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Mobile</label>
                        <input type="text" class="form-control {{ auth()->user()->role === 'employee' ? 'bg-light' : '' }}" wire:model.defer="mobile" {{ auth()->user()->role === 'employee' ? 'disabled' : '' }}>
                        @error('mobile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Role</label>
                        <input type="text" class="form-control bg-light" value="{{ ucfirst($role) }}" disabled>
                    </div>

                    @if(auth()->user()->manager)
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Reporting Manager</label>
                        <input type="text" class="form-control bg-light" value="{{ auth()->user()->manager->name }} ({{ auth()->user()->manager->email }})" disabled>
                    </div>
                    @endif

                    @if(auth()->user()->department)
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Department</label>
                        <input type="text" class="form-control bg-light" value="{{ auth()->user()->department->name }}" disabled>
                    </div>
                    @endif

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Profile Image</label>
                        <input type="file" class="form-control" wire:model="profile_image" accept="image/*">
                        <div wire:loading wire:target="profile_image" class="text-muted small mt-1">Uploading...</div>
                        @error('profile_image') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Current Password</label>
                        <input type="password" class="form-control" wire:model.defer="current_password" placeholder="Required only if changing password">
                        @error('current_password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">New Password</label>
                        <input type="password" class="form-control" wire:model.defer="password" placeholder="Leave blank to keep current password">
                        @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Confirm Password</label>
                        <input type="password" class="form-control" wire:model.defer="password_confirmation">
                    </div>
                    <div class="col-12 d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                        <a href="{{ auth()->user()->dashboard_route }}" class="btn btn-outline-secondary">Back to Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
