<div class="row g-4">
    <div class="col-lg-4">
        <!-- Main User Profile Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body text-center p-4">
                <div class="position-relative d-inline-block mb-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($profile_image): ?>
                        <img src="<?php echo e($profile_image->temporaryUrl()); ?>" class="rounded-circle shadow-sm border border-3 border-primary" width="100" height="100" alt="avatar" style="object-fit: cover;">
                    <?php else: ?>
                        <img src="<?php echo e(auth()->user()->avatar_url); ?>" class="rounded-circle shadow-sm border border-3 border-primary" width="100" height="100" alt="avatar" style="object-fit: cover;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo e(urlencode(auth()->user()->name)); ?>&background=2563EB&color=fff';">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <label for="profileImageInput" class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle shadow-sm" style="cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border: 2px solid #fff;" title="Click to upload new photo">
                        <i class="bi bi-camera-fill" style="font-size: 13px;"></i>
                    </label>
                </div>
                <h5 class="mb-1 fw-bold"><?php echo e(auth()->user()->name); ?></h5>
                <div class="text-muted small"><?php echo e(auth()->user()->email); ?></div>
                <div class="text-muted small"><?php echo e(auth()->user()->mobile ?? 'No mobile number'); ?></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->employee_code): ?>
                    <div class="badge bg-secondary bg-opacity-10 text-secondary mt-1">ID: <?php echo e(auth()->user()->employee_code); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="mt-3 d-flex justify-content-center gap-2 flex-wrap">
                    <span class="badge-status badge-<?php echo e(auth()->user()->status); ?>"><?php echo e(ucfirst(auth()->user()->status)); ?></span>
                    <span class="badge-status badge-primary"><?php echo e(ucfirst(auth()->user()->role)); ?></span>
                </div>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->role === 'employee'): ?>
        <!-- Separate Reporting Manager Details Card -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->manager || auth()->user()->department): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-person-badge text-primary me-2 fs-5"></i> Reporting Manager
                </h6>
            </div>
            <div class="card-body p-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->manager): ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="<?php echo e(auth()->user()->manager->avatar_url); ?>" class="rounded-circle shadow-sm border" width="48" height="48" alt="avatar" style="object-fit: cover;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo e(urlencode(auth()->user()->manager->name)); ?>&background=2563EB&color=fff';">
                        <div>
                            <div class="fw-bold text-dark fs-6"><?php echo e(auth()->user()->manager->name); ?></div>
                            <div class="text-muted small"><?php echo e(auth()->user()->manager->role ? ucfirst(auth()->user()->manager->role) : 'Manager'); ?></div>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->manager->email): ?>
                        <div class="text-muted small mb-1">
                            <i class="bi bi-envelope me-2 text-primary"></i><?php echo e(auth()->user()->manager->email); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->manager->mobile): ?>
                        <div class="text-muted small mb-1">
                            <i class="bi bi-telephone me-2 text-primary"></i><?php echo e(auth()->user()->manager->mobile); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->department): ?>
                    <div class="<?php echo e(auth()->user()->manager ? 'pt-3 mt-3 border-top' : ''); ?>">
                        <div class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <i class="bi bi-diagram-3 text-primary me-1"></i> Department
                        </div>
                        <div class="fw-bold text-dark"><?php echo e(auth()->user()->department->name); ?></div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Employment Details Card -->
        <div class="card border-0 shadow-sm rounded-4 mt-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-briefcase text-primary me-2 fs-5"></i> Employment Details
                </h6>
            </div>
            <div class="card-body p-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->joining_date): ?>
                <div class="<?php echo e(auth()->user()->branch || auth()->user()->shift || auth()->user()->working_mode ? 'mb-3' : ''); ?>">
                    <div class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-calendar-check text-primary me-1"></i> Joining Date
                    </div>
                    <div class="fw-bold text-dark"><?php echo e(\Carbon\Carbon::parse(auth()->user()->joining_date)->format('d M, Y')); ?></div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="<?php echo e(auth()->user()->branch || auth()->user()->shift ? 'mb-3' : ''); ?>">
                    <div class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-geo-alt text-primary me-1"></i> Fixed Working Mode
                    </div>
                    <div class="fw-bold text-dark"><?php echo e(ucfirst(auth()->user()->working_mode ?? 'Office')); ?></div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->branch): ?>
                <div class="<?php echo e(auth()->user()->shift ? 'mb-3 pt-3 border-top' : 'pt-3 border-top'); ?>">
                    <div class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-building text-primary me-1"></i> Branch / Location
                    </div>
                    <div class="fw-bold text-dark"><?php echo e(auth()->user()->branch->name); ?></div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->shift): ?>
                <div class="pt-3 border-top">
                    <div class="text-muted fw-bold text-uppercase mb-1" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <i class="bi bi-clock text-primary me-1"></i> Work Shift
                    </div>
                    <div class="fw-bold text-dark"><?php echo e(auth()->user()->shift->name); ?></div>
                    <div class="text-muted small"><?php echo e(\Carbon\Carbon::parse(auth()->user()->shift->start_time)->format('h:i A')); ?> - <?php echo e(\Carbon\Carbon::parse(auth()->user()->shift->end_time)->format('h:i A')); ?></div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                    <span class="fw-600 fw-bold text-primary">₹<?php echo e(number_format(auth()->user()->basic_salary ?? 0)); ?></span>
                </div>
            </div>
        </div>

        <!-- Salary Structure Overview -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($salaryStructure) && $salaryStructure): ?>
            <?php
                $totalAllowances = is_array($salaryStructure->allowances) ? array_sum($salaryStructure->allowances) : (is_numeric($salaryStructure->allowances) ? $salaryStructure->allowances : 0);
                $totalDeductions = is_array($salaryStructure->deductions) ? array_sum($salaryStructure->deductions) : (is_numeric($salaryStructure->deductions) ? $salaryStructure->deductions : 0);
            ?>
            <div class="card border-0 shadow-sm mt-4 rounded-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-wallet2 text-primary me-2 fs-5"></i>Salary Details</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Salary Type</span>
                        <span class="fw-bold small"><?php echo e(ucfirst(str_replace('_', ' ', $salaryStructure->salary_type))); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Basic Salary</span>
                        <span class="fw-bold small">₹<?php echo e(number_format($salaryStructure->basic_salary, 2)); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Allowances</span>
                        <span class="fw-bold text-success small">+ ₹<?php echo e(number_format($totalAllowances, 2)); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Deductions</span>
                        <span class="fw-bold text-danger small">- ₹<?php echo e(number_format($totalDeductions, 2)); ?></span>
                    </div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark small mb-0">Net Salary</span>
                        <span class="fw-bold text-primary mb-0">₹<?php echo e(number_format($salaryStructure->net_salary, 2)); ?></span>
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
                        <span class="fw-bold small">₹<?php echo e(number_format($salaryStructure->monthly_target, 2)); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Base Commission</span>
                        <span class="fw-bold small"><?php echo e($salaryStructure->commission_percent); ?>%</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Recovery Commission</span>
                        <span class="fw-bold small"><?php echo e($salaryStructure->recovery_percent); ?>%</span>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                        <input type="text" class="form-control <?php echo e(auth()->user()->role === 'employee' ? 'bg-light' : ''); ?>" wire:model.defer="name" <?php echo e(auth()->user()->role === 'employee' ? 'disabled' : ''); ?>>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Email</label>
                        <input type="email" class="form-control <?php echo e(auth()->user()->role === 'employee' ? 'bg-light' : ''); ?>" wire:model.defer="email" <?php echo e(auth()->user()->role === 'employee' ? 'disabled' : ''); ?>>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Mobile</label>
                        <input type="text" class="form-control <?php echo e(auth()->user()->role === 'employee' ? 'bg-light' : ''); ?>" wire:model.defer="mobile" <?php echo e(auth()->user()->role === 'employee' ? 'disabled' : ''); ?>>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['mobile'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Role</label>
                        <input type="text" class="form-control bg-light" value="<?php echo e(ucfirst($role)); ?>" disabled>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->manager): ?>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Reporting Manager</label>
                        <input type="text" class="form-control bg-light" value="<?php echo e(auth()->user()->manager->name); ?> (<?php echo e(auth()->user()->manager->email); ?>)" disabled>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->department): ?>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Department</label>
                        <input type="text" class="form-control bg-light" value="<?php echo e(auth()->user()->department->name); ?>" disabled>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold" for="profileImageInput">Profile Image</label>
                        <input type="file" id="profileImageInput" class="form-control" wire:model="profile_image" accept="image/*">
                        <div wire:loading wire:target="profile_image" class="text-primary small mt-1">
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span> Uploading photo...
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['profile_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Current Password</label>
                        <input type="password" class="form-control" wire:model.defer="current_password" placeholder="Required only if changing password">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['current_password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">New Password</label>
                        <input type="password" class="form-control" wire:model.defer="password" placeholder="Leave blank to keep current password">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="text-danger small mt-1"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small fw-bold">Confirm Password</label>
                        <input type="password" class="form-control" wire:model.defer="password_confirmation">
                    </div>
                    <div class="col-12 d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                        <a href="<?php echo e(auth()->user()->dashboard_route); ?>" class="btn btn-outline-secondary">Back to Dashboard</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/profile.blade.php ENDPATH**/ ?>