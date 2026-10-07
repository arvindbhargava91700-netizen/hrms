<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Page Header -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1 fw-bold text-dark d-flex align-items-center">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Mark Attendance (Manual Override)
                </h4>
                <p class="text-muted mb-0 small">Select an employee and date to manually create or overwrite attendance records with full audit trail.</p>
            </div>
            <div class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2 fw-bold">
                <i class="bi bi-shield-check me-1"></i> Admin Override Mode
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Form Section -->
        <div class="col-lg-7 col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="bi bi-person-check text-primary me-2"></i>Select Employee & Date
                    </h5>
                </div>
                
                <form wire:submit.prevent="saveAttendance">
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Select Employee -->
                            <div class="col-12">
                                <label class="form-label text-muted small fw-bold text-uppercase">Employee <span class="text-danger">*</span></label>
                                <select class="form-select <?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="employee_id">
                                    <option value="">-- Choose Employee --</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($emp->id); ?>">
                                            <?php echo e($emp->name); ?> (<?php echo e($emp->employee_code ?: 'No Code'); ?>) - <?php echo e($emp->department->name ?? 'General'); ?>

                                        </option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['employee_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Date Input -->
                            <div class="col-md-6 col-12">
                                <label class="form-label text-muted small fw-bold text-uppercase">Date Manual Override <span class="text-danger">*</span></label>
                                <input type="date" class="form-control fw-bold <?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="date">
                                <span class="form-text text-muted extra-small" style="font-size: 0.72rem;">
                                    <i class="bi bi-info-circle me-0.5"></i> Overwrites existing record for the selected date.
                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Status Dropdown -->
                            <div class="col-md-6 col-12">
                                <label class="form-label text-muted small fw-bold text-uppercase">Status <span class="text-danger">*</span></label>
                                <select class="form-select fw-bold <?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" wire:model.live="status">
                                    <option value="present">Present (Completed / Punch Out)</option>
                                    <option value="punch_in">Punch In (Active / In Office)</option>
                                    <option value="half_day">Half Day</option>
                                    <option value="late">Late</option>
                                    <option value="short_leave">Short Leave</option>
                                    <option value="leave">Leave</option>
                                    <option value="absent">Absent</option>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Existing Record Found Preview Alert -->
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($existingAttendance): ?>
                                <div class="col-12">
                                    <div class="p-3 bg-info bg-opacity-10 border border-info border-opacity-25 rounded-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-info small">
                                                <i class="bi bi-clock-history me-1"></i>Existing Record Found for <?php echo e(\Carbon\Carbon::parse($date)->format('d M Y')); ?>

                                            </span>
                                            <span class="badge bg-secondary rounded-pill text-uppercase" style="font-size: 0.65rem;">
                                                Current: <?php echo e(str_replace('_', ' ', $existingAttendance->status)); ?>

                                            </span>
                                        </div>
                                        <div class="text-dark small">
                                            Check In: <strong><?php echo e($existingAttendance->check_in ? \Carbon\Carbon::parse($existingAttendance->check_in)->format('h:i A') : '--:--'); ?></strong> | 
                                            Check Out: <strong><?php echo e($existingAttendance->check_out ? \Carbon\Carbon::parse($existingAttendance->check_out)->format('h:i A') : '--:--'); ?></strong> |
                                            Mode: <strong><?php echo e(ucfirst($existingAttendance->working_mode ?: 'Office')); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <!-- Check In Time -->
                            <div class="col-md-6 col-12">
                                <label class="form-label text-muted small fw-bold text-uppercase">Check In Time</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-box-arrow-in-right text-success"></i></span>
                                    <input type="time" class="form-control fw-bold" wire:model="check_in" placeholder="--:--">
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['check_in'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Check Out Time -->
                            <div class="col-md-6 col-12">
                                <label class="form-label text-muted small fw-bold text-uppercase">Check Out Time</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-box-arrow-left text-danger"></i></span>
                                    <input type="time" class="form-control fw-bold" wire:model="check_out" placeholder="--:--">
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['check_out'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Shift Selector -->
                            <div class="col-md-6 col-12">
                                <label class="form-label text-muted small fw-bold text-uppercase">Shift</label>
                                <select class="form-select" wire:model="shift_id">
                                    <option value="">— No Shift —</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $shifts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shift): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($shift->id); ?>">
                                            <?php echo e($shift->name); ?> (<?php echo e(\Carbon\Carbon::parse($shift->start_time)->format('h:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($shift->end_time)->format('h:i A')); ?>)
                                        </option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['shift_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Working Mode -->
                            <div class="col-md-6 col-12">
                                <label class="form-label text-muted small fw-bold text-uppercase">Working Mode</label>
                                <select class="form-select" wire:model="working_mode">
                                    <option value="office">Office Mode</option>
                                    <option value="remote">Remote / WFH</option>
                                    <option value="field">Field Work</option>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['working_mode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <!-- Notes -->
                            <div class="col-12">
                                <label class="form-label text-muted small fw-bold text-uppercase">Notes / Reason for Override</label>
                                <textarea class="form-control" rows="3" wire:model="notes" placeholder="Optional notes (e.g. Employee forgot to punch out / biometric issue)..."></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-between align-items-center">
                        <span class="text-muted extra-small">
                            <i class="bi bi-database-check text-success me-1"></i> Overrides are automatically audited in the override table.
                        </span>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                            <i class="bi bi-check2-circle me-1"></i> Save Attendance Override
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help / Quick Info Card -->
        <div class="col-lg-5 col-12">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center">
                    <i class="bi bi-lightbulb-fill text-warning me-2"></i>How Manual Override Works
                </h6>
                <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                    <li><strong>Direct Overwrite:</strong> If an attendance record already exists for the selected date, it will be updated with the new times and status.</li>
                    <li><strong>Audit Trail:</strong> The previous record, along with who made the change and at what time, is permanently logged in the <code>attendance_overrides</code> table.</li>
                    <li><strong>Automatic Metrics:</strong> Working minutes and late minutes are automatically calculated based on the check-in time, check-out time, and assigned shift.</li>
                    <li><strong>Flexible Statuses:</strong> Mark as Present, Half Day, Late, Leave, or Absent instantly.</li>
                </ul>
            </div>

            <div class="card border-0 shadow-sm rounded-4 p-4 bg-light border">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-shield-lock text-primary fs-4 me-2"></i>
                    <h6 class="fw-bold text-dark mb-0">Audit & Compliance</h6>
                </div>
                <p class="text-muted small mb-0">
                    All manual overrides require administrative permissions and are timestamped with the administrator's ID for transparency during payroll generation.
                </p>
            </div>
        </div>
    </div>

    <!-- Recent Overrides History Audit Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="bi bi-clock-history text-primary me-2"></i>Recent Attendance Overrides Log
            </h5>
            <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-3 py-1">
                Last <?php echo e(count($recentOverrides)); ?> Overrides
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                    <tr>
                        <th class="ps-4">Employee</th>
                        <th>Attendance Date</th>
                        <th>Status Change</th>
                        <th>Check In / Out</th>
                        <th>Shift</th>
                        <th>Overridden By</th>
                        <th>Notes</th>
                        <th class="text-end pe-4">Logged At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentOverrides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $override): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?php echo e($override->employee->name ?? 'Unknown'); ?></div>
                                <div class="text-muted extra-small">
                                    <?php echo e($override->employee->employee_code ?: 'No Code'); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($override->employee?->department): ?>
                                        • <?php echo e($override->employee->department->name); ?>

                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </td>
                            <td class="fw-semibold text-dark">
                                <?php echo e(\Carbon\Carbon::parse($override->date)->format('d M Y')); ?>

                            </td>
                            <td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($override->previous_status): ?>
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 extra-small">
                                        <?php echo e(str_replace('_', ' ', $override->previous_status)); ?>

                                    </span>
                                    <i class="bi bi-arrow-right mx-1 text-muted small"></i>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-bold">
                                    <?php echo e(str_replace('_', ' ', $override->new_status)); ?>

                                </span>
                            </td>
                            <td class="small text-dark">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($override->new_check_in || $override->new_check_out): ?>
                                    <div>In: <strong><?php echo e($override->new_check_in ? \Carbon\Carbon::parse($override->new_check_in)->format('h:i A') : '--'); ?></strong></div>
                                    <div>Out: <strong><?php echo e($override->new_check_out ? \Carbon\Carbon::parse($override->new_check_out)->format('h:i A') : '--'); ?></strong></div>
                                <?php else: ?>
                                    <span class="text-muted">--</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?php echo e($override->shift->name ?? '—'); ?>

                            </td>
                            <td class="small">
                                <div class="fw-semibold text-dark"><?php echo e($override->overrider->name ?? 'System/Admin'); ?></div>
                            </td>
                            <td class="small text-muted text-truncate" style="max-width: 180px;">
                                <?php echo e($override->notes ?: '—'); ?>

                            </td>
                            <td class="text-end pe-4 text-muted extra-small">
                                <?php echo e($override->created_at ? $override->created_at->format('d M, h:i A') : '—'); ?>

                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                No manual attendance overrides logged yet.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/attendance/manual-attendance-override.blade.php ENDPATH**/ ?>