<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0">
            <h5 class="mb-0">All Staff</h5>
            @if(auth()->user()->isPartner() || auth()->user()->canAccess('staff_create'))
            <button class="btn btn-primary btn-sm" wire:click="createStaff">
                <i class="bi bi-person-plus me-1"></i> Add Staff
            </button>
            @endif
        </div>
        
        <div class="card-body pb-0">
            @include('partials.hrms-filters', ['viewAnyPermission' => 'staff_viewAny'])
        </div>
        
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Contact</th>
                        <th>Role & Dept</th>
                        <th>Reporting To</th>
                        <th>Employment</th>
                        <th>Basic Salary</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $member->avatar_url }}" class="rounded-circle flex-shrink-0" width="36" height="36" alt="{{ $member->name }}">
                                    <div>
                                        <div class="fw-600 text-dark">{{ $member->name }}</div>
                                        <div class="text-muted small">ID: {{ $member->employee_code ?? substr($member->id, 0, 8) }}</div>
                                        @if($member->joining_date)
                                            <div class="text-muted small mt-1"><i class="bi bi-calendar-check me-1"></i> Joined: {{ \Carbon\Carbon::parse($member->joining_date)->format('d M Y') }}</div>
                                        @endif
                                        @php
                                            $statusLabels = [
                                                'active'   => ['label' => 'Active', 'class' => 'bg-success'],
                                                'inactive' => ['label' => 'Inactive', 'class' => 'bg-secondary'],
                                            ];
                                            $st = $statusLabels[$member->status] ?? ['label' => ucfirst($member->status ?? 'unknown'), 'class' => 'bg-secondary'];
                                        @endphp
                                        <span class="badge {{ $st['class'] }} mt-1">{{ $st['label'] }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="text-nowrap small text-muted"><i class="bi bi-envelope me-1"></i> {{ $member->email }}</div>
                                <div class="text-nowrap small text-muted"><i class="bi bi-telephone me-1"></i> {{ $member->mobile }}</div>
                            </td>
                            <td>
                                @foreach($member->roles as $role)
                                    @if($role->name !== 'employee')
                                    <span class="badge bg-secondary mb-1">
                                        {{ str_replace([$this->getPartnerId() . '_', '_' . $this->getPartnerId()], '', $role->name) }}
                                    </span>
                                    @endif
                                @endforeach
                                @if($member->department)
                                    <br><span class="text-muted small"><i class="bi bi-diagram-3"></i> {{ $member->department->name }}</span>
                                @endif
                                @if($member->branch)
                                    <br><span class="text-muted small"><i class="bi bi-geo-alt"></i> {{ $member->branch->name }}</span>
                                @endif
                                @if($member->shift)
                                    <br><span class="text-muted small"><i class="bi bi-clock"></i> {{ $member->shift->name }}</span>
                                @endif
                            </td>
                            <td>
                                @if($member->manager)
                                    <span class="text-primary"><i class="bi bi-person-up"></i> {{ $member->manager->name }}</span>
                                @else
                                    <span class="text-muted fst-italic">None</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $employmentTypeLabels = [
                                        'full_time' => ['label' => 'Full Time', 'class' => 'bg-success'],
                                        'part_time' => ['label' => 'Part Time', 'class' => 'bg-info'],
                                        'contract'  => ['label' => 'Contract', 'class' => 'bg-warning'],
                                    ];
                                    $et = $employmentTypeLabels[$member->employment_type] ?? ['label' => ucfirst(str_replace('_', ' ', $member->employment_type ?? 'full_time')), 'class' => 'bg-secondary'];
                                @endphp
                                <span class="badge {{ $et['class'] }} mb-1">{{ $et['label'] }}</span>
                                @if($member->employment_status && $member->employment_status !== 'active')
                                    <br><span class="text-muted small fst-italic text-capitalize">{{ $member->employment_status }}</span>
                                @endif
                            </td>
                            <td>
                                @if($member->basic_salary)
                                    <span class="fw-600">₹{{ number_format($member->basic_salary, 0) }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('partner.hrms.staff.profile', $member->id) }}" class="btn btn-sm btn-outline-info me-1" title="View Profile">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('staff_update'))
                                <button class="btn btn-sm btn-outline-secondary me-1" wire:click="editStaff('{{ $member->id }}')" title="Edit Staff">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endif
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('staff_delete'))
                                <button class="btn btn-sm btn-outline-danger" wire:click="deleteStaff('{{ $member->id }}')" onclick="confirm('Are you sure you want to delete this staff member? They will lose access immediately.') || event.stopImmediatePropagation()">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                <i class="bi bi-people display-4 mb-3 d-block text-light"></i>
                                <p>No staff members added yet.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Staff Modal -->
    @if($isStaffModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form wire:submit.prevent="saveStaff">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingStaffId ? 'Edit Staff Member' : 'Add Staff Member' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('isStaffModalOpen', false)"></button>
                    </div>
                    <div class="modal-body bg-light">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="fw-600 mb-3 text-dark">Personal Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Employee ID / Code</label>
                                        <input type="text" class="form-control" wire:model="staffEmployeeCode" placeholder="Optional">
                                        @error('staffEmployeeCode') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Full Name</label>
                                        <input type="text" class="form-control" wire:model="staffName" placeholder="e.g. John Doe">
                                        @error('staffName') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Email Address</label>
                                        <input type="email" class="form-control" wire:model="staffEmail" placeholder="e.g. john@example.com">
                                        @error('staffEmail') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Mobile Number</label>
                                        <input type="text" class="form-control" wire:model="staffMobile" placeholder="e.g. 9876543210">
                                        @error('staffMobile') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Role</label>
                                        <select class="form-select" wire:model="staffRoleId">
                                            <option value="">Select a role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ str_replace([$this->getPartnerId() . '_', '_' . $this->getPartnerId()], '', $role->name) }}</option>
                                            @endforeach
                                        </select>
                                        @error('staffRoleId') <span class="text-danger small">{{ $message }}</span> @enderror
                                        @if(count($roles) === 0)
                                            <div class="form-text text-warning small"><i class="bi bi-exclamation-triangle"></i> Create Roles first.</div>
                                        @endif
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Department</label>
                                        <select class="form-select" wire:model="staffDepartmentId">
                                            <option value="">No Department</option>
                                            @foreach($departments as $dept)
                                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('staffDepartmentId') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Branch / Location</label>
                                        <select class="form-select" wire:model.live="staffBranchId">
                                            <option value="">No Branch</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('staffBranchId') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Work Shift</label>
                                        <select class="form-select" wire:model="staffShiftId">
                                            <option value="">No Shift</option>
                                            @foreach($shifts as $shift)
                                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('staffShiftId') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted">Fixed Working Mode</label>
                                            <select class="form-select" wire:model="staffWorkingMode">
                                                <option value="office">Office</option>
                                                <option value="remote">Remote (WFH)</option>
                                                <option value="field">Field Work</option>
                                            </select>
                                            @error('staffWorkingMode') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    <div class="col-md-4">
                                        <label class="form-label small text-muted">Reporting To (Manager)</label>
                                        <div wire:ignore>
                                        <select class="form-select select2-searchable" wire:model="staffReportingTo">
                                            <option value="">No Direct Manager</option>
                                            @foreach($staff as $mgr)
                                                @if($mgr->id !== $editingStaffId)
                                                    <option value="{{ $mgr->id }}">{{ $mgr->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        </div>
                                        @error('staffReportingTo') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="fw-600 mb-3 text-dark">Employment Lifecycle</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Joining Date</label>
                                        <input type="date" class="form-control" wire:model="staffJoiningDate">
                                        @error('staffJoiningDate') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Employment Type</label>
                                        <select class="form-select" wire:model="staffEmploymentType">
                                            <option value="full_time">Full Time</option>
                                            <option value="part_time">Part Time</option>
                                            <option value="contract">Contract</option>
                                        </select>
                                        @error('staffEmploymentType') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Employment Status</label>
                                        <select class="form-select" wire:model.live="staffEmploymentStatus">
                                            <option value="active">Active</option>
                                            <option value="resigned">Resigned</option>
                                            <option value="terminated">Terminated</option>
                                        </select>
                                        @error('staffEmploymentStatus') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    @if($staffEmploymentStatus === 'resigned' || $staffEmploymentStatus === 'terminated')
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Resignation Date</label>
                                        <input type="date" class="form-control" wire:model="staffResignationDate">
                                        @error('staffResignationDate') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Termination/Last Working Date</label>
                                        <input type="date" class="form-control" wire:model="staffTerminationDate">
                                        @error('staffTerminationDate') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="fw-600 mb-3 text-dark">Account & Payroll</h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Basic Salary (Monthly)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted">₹</span>
                                            <input type="number" step="0.01" class="form-control" wire:model="staffSalary" placeholder="0.00">
                                        </div>
                                        @error('staffSalary') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small text-muted">Login Password</label>
                                        <input type="password" class="form-control" wire:model="staffPassword" placeholder="{{ $editingStaffId ? 'Leave blank to keep current' : 'Enter a strong password' }}">
                                        @error('staffPassword') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isStaffModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Staff</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
