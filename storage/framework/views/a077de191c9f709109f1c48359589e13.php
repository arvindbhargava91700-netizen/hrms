<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body p-4 d-flex justify-content-between align-items-center">
                    <div>
                        <?php
                            $_hasViewAny   = auth()->user()->canAccess('attendance_viewAny');
                            $_hasViewTeam  = auth()->user()->canAccess('attendance_viewBranch') || auth()->user()->canAccess('attendance_viewTeam');
                        ?>
                        <h4 class="mb-1 text-dark fw-bold"><?php echo e($_hasViewAny ? 'Manage Employee Attendance' : ($_hasViewTeam ? 'Team Attendance' : 'My Attendance History')); ?></h4>
                        <p class="text-muted mb-0"><?php echo e($_hasViewAny ? 'View check-ins, selfies, and GPS locations of all employees.' : ($_hasViewTeam ? 'View attendance records of your team members.' : 'View your attendance history.')); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
   <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('attendance_viewTeam') || auth()->user()->canAccess('attendance_viewAny') ): ?>
    <?php echo $__env->make('partials.hrms-filters', ['viewAnyPermission' => 'attendance_viewAny'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
  <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white h-100">
                <div class="card-body p-3">
                    <label class="form-label fw-bold small text-muted"><i class="bi bi-calendar-month me-1"></i>Select Month</label>
                    <input type="month" wire:model.live="selectedMonth" class="form-control form-control-sm bg-light border-0">
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white h-100">
                <div class="card-body p-3">
                    <label class="form-label fw-bold small text-muted"><i class="bi bi-calendar-day me-1"></i>Select Date</label>
                    <input type="date" wire:model.live="selectedDate" class="form-control form-control-sm bg-light border-0">
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-white h-100">
                <div class="card-body p-3">
                    <label class="form-label fw-bold small text-muted"><i class="bi bi-check2-circle me-1"></i>Select Status</label>
                                    <select wire:model.live="selectedStatus" class="form-select form-select-sm bg-light border-0">
                                        <option value="">All Statuses</option>
                                        <option value="punch_out">Punch Out</option>
                                        <option value="absent">Absent</option>
                                        <option value="half_day">Half Day</option>
                                        <option value="punch_in">Punch In</option>
                                        <option value="leave">Leave</option>
                                        <option value="holiday">Holiday</option>
                                        <option value="weekOff">Week Off</option>
                                        <option value="late">Late</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="card shadow-sm border-0 bg-white h-100">
                                <div class="card-body p-3">
                                    <label class="form-label fw-bold small text-muted"><i class="bi bi-laptop me-1"></i>Select Working Mode</label>
                                    <select wire:model.live="selectedWorkingMode" class="form-select form-select-sm bg-light border-0">
                                        <option value="">All Modes</option>
                                        <option value="office">Office</option>
                                        <option value="remote">Remote (WFH)</option>
                                        <option value="field">Field Work</option>
                                    </select>
                                </div>
                            </div>
                        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-white">
                <div class="card-body p-3">
                    <div class="row g-2 text-center">
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-primary mb-1 fw-bold" style="font-size: 0.8rem;">Punch In</h6>
                                <h5 class="mb-0 text-dark"><?php echo e($summary['punch_in'] ?? 0); ?></h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-success mb-1 fw-bold" style="font-size: 0.8rem;">Punch Out</h6>
                                <h5 class="mb-0 text-dark"><?php echo e($summary['punch_out'] ?? 0); ?></h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-danger mb-1 fw-bold" style="font-size: 0.8rem;">Absent</h6>
                                <h5 class="mb-0 text-dark"><?php echo e($summary['absent'] ?? 0); ?></h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-warning mb-1 fw-bold" style="font-size: 0.8rem;">Half Day</h6>
                                <h5 class="mb-0 text-dark"><?php echo e($summary['half_day'] ?? 0); ?></h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-info mb-1 fw-bold" style="font-size: 0.8rem;">Leave</h6>
                                <h5 class="mb-0 text-dark"><?php echo e($summary['leave'] ?? 0); ?></h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-secondary mb-1 fw-bold" style="font-size: 0.8rem;">Week Off/Holiday</h6>
                                <h5 class="mb-0 text-dark"><?php echo e(($summary['weekOff'] ?? 0) + ($summary['holiday'] ?? 0)); ?></h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-danger mb-1 fw-bold" style="font-size: 0.8rem;">Late</h6>
                                <h5 class="mb-0 text-dark"><?php echo e($summary['late'] ?? 0); ?></h5>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-2 border rounded border-light bg-light">
                                <h6 class="text-dark mb-1 fw-bold" style="font-size: 0.8rem;">Missed Punch</h6>
                                <h5 class="mb-0 text-dark"><?php echo e($summary['missedPunch'] ?? 0); ?></h5>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 text-center mt-2 pt-2 border-top">
                        <div class="col-md-4 col-12">
                            <div class="p-2 border rounded border-light bg-light">
                                <span class="text-muted small fw-bold"><i class="bi bi-clock me-1"></i>Total Hours:</span>
                                <span class="fw-bold text-dark ms-1"><?php echo e($summary['total_hours'] ?? '0h 0m'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="p-2 border rounded border-light bg-light">
                                <span class="text-success small fw-bold"><i class="bi bi-check-circle me-1"></i>Productive Hours:</span>
                                <span class="fw-bold text-success ms-1"><?php echo e($summary['productive_hours'] ?? '0h 0m'); ?></span>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="p-2 border rounded border-light bg-light">
                                <span class="text-warning small fw-bold"><i class="bi bi-hourglass-split me-1"></i>Overtime:</span>
                                <span class="fw-bold text-dark ms-1"><?php echo e($summary['overtime_hours'] ?? '0h 0m'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 bg-white">
        <div class="card-header bg-white border-bottom py-3 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark fs-6"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Employee Attendance Records</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($selectedUsers) > 0): ?>
                    <span class="badge bg-primary rounded-pill px-2.5 py-1">
                        <?php echo e(count($selectedUsers)); ?> Selected
                    </span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" wire:click="toggleExpandAll" class="btn btn-sm <?php echo e($allExpanded || (count($pageRowKeys) > 0 && count(array_intersect($pageRowKeys, $expandedKeys)) === count($pageRowKeys)) ? 'btn-primary' : 'btn-outline-primary'); ?> fw-semibold rounded-pill px-3 shadow-none">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allExpanded || (count($pageRowKeys) > 0 && count(array_intersect($pageRowKeys, $expandedKeys)) === count($pageRowKeys))): ?>
                        <i class="bi bi-arrows-collapse me-1"></i> Collapse All Details
                    <?php else: ?>
                        <i class="bi bi-arrows-expand me-1"></i> Show All Employee Details
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase extra-small text-muted fw-bold">
                        <tr>
                            <th class="py-3.5 ps-3" style="width: 44px;">
                                <input type="checkbox" class="form-check-input" wire:click="toggleSelectAll" title="Select / Expand All"
                                    <?php if(count($pageEmployeeIds) > 0 && collect($pageEmployeeIds)->every(fn($id) => in_array($id, $selectedUsers))): ?> checked <?php endif; ?>>
                            </th>
                            <th class="ps-4 py-3.5">
                                Employee
                                <span class="text-muted fw-normal text-capitalize ms-1" style="font-size: 0.72rem;">(Click to view details)</span>
                            </th>
                            <th class="py-3.5 px-3">Date</th>
                            <th class="py-3.5 px-3">Working Mode</th>
                            <th class="py-3.5 px-3">Status</th>
                            <th class="py-3.5 px-3">Check In</th>
                            <th class="py-3.5 px-3">Check Out</th>
                            <th class="py-3.5 px-3 text-center">Total Hours</th>
                            <th class="py-3.5 px-3 text-center">Productive Hours</th>
                            <th class="py-3.5 px-3 text-center">Overtime</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $attendances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attendance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $rowKey = (string) ($attendance->id
                                    ? $attendance->id
                                    : 'v_' . $attendance->employee_id . '_' . $attendance->date);

                                $isExpanded = in_array($rowKey, $expandedKeys)
                                    || ($expandedKey === $rowKey)
                                    || in_array((string)$attendance->employee_id, $selectedUsers)
                                    || $allExpanded;

                                if (!empty($attendance->working_minutes)) {
                                    $worked = (int) $attendance->working_minutes;
                                } elseif ($attendance->check_in && $attendance->check_out) {
                                    $dateStr = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
                                    $inTime = \Carbon\Carbon::parse($dateStr . ' ' . $attendance->check_in);
                                    $outTime = \Carbon\Carbon::parse($dateStr . ' ' . $attendance->check_out);
                                    $worked = (int) abs($outTime->diffInMinutes($inTime));
                                } elseif ($attendance->check_in && \Carbon\Carbon::parse($attendance->date)->isToday()) {
                                    $inTime = \Carbon\Carbon::parse(\Carbon\Carbon::today()->format('Y-m-d') . ' ' . $attendance->check_in);
                                    $worked = (int) max(0, \Carbon\Carbon::now()->diffInMinutes($inTime));
                                } else {
                                    $worked = 0;
                                }

                                $rowShift = $attendance->employee->shift ?? null;
                                $required = ($rowShift && $rowShift->min_present_mins)
                                    ? (int) $rowShift->min_present_mins
                                    : ($defaultRequiredMins ?? 480);

                                $productive = min($worked, $required);
                                $overtime = max(0, $worked - $required);
                            ?>
                            <tr wire:click="toggleDetails('<?php echo e($rowKey); ?>')" style="cursor: pointer;" class="hover-shadow-sm transition-all <?php if($isExpanded): ?> table-active <?php endif; ?>">
                                <td class="ps-3 py-3.5 align-middle" @click.stop>
                                    <input type="checkbox" class="form-check-input" wire:model.live="selectedUsers" value="<?php echo e($attendance->employee_id); ?>">
                                </td>
                                <td class="ps-4 py-3.5 align-middle">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <?php echo e(strtoupper(substr($attendance->employee->name, 0, 1))); ?>

                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark"><?php echo e($attendance->employee->name); ?></h6>
                                            <small class="text-muted"><?php echo e($attendance->employee->email); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-3 align-middle text-secondary fw-semibold"><?php echo e(\Carbon\Carbon::parse($attendance->date)->format('M d, Y')); ?></td>
                                <td class="py-3.5 px-3 align-middle">
                                    <span class="badge bg-dark bg-gradient rounded-pill px-3 py-1.5 fs-7 fw-semibold">
                                        <i class="bi bi-laptop me-1"></i><?php echo e(ucfirst($attendance->working_mode ?: 'office')); ?>

                                    </span>
                                </td>
                                <td class="py-3.5 px-3 align-middle">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->status === 'punch_out'): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-1.5">Punch Out</span>
                                    <?php elseif($attendance->status === 'absent'): ?>
                                        <span class="badge bg-danger rounded-pill px-3 py-1.5">Absent</span>
                                    <?php elseif($attendance->status === 'half_day'): ?>
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5">Half Day</span>
                                    <?php elseif($attendance->status === 'short_leave'): ?>
                                        <span class="badge bg-info rounded-pill px-3 py-1.5">Short Leave</span>
                                    <?php elseif($attendance->status === 'punch_in' || ($attendance->check_in && !$attendance->check_out)): ?>
                                        <span class="badge bg-primary rounded-pill px-3 py-1.5">Punch In</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary rounded-pill px-3 py-1.5"><?php echo e(ucfirst($attendance->status ?: 'Punch In')); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->late_minutes > 0): ?>
                                        <div class="mt-1">
                                             <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                                <i class="bi bi-clock-history me-1"></i>Late by <?php echo e($attendance->late_minutes); ?> mins
                                            </span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="py-3.5 px-3 align-middle">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_in): ?>
                                        <div class="fw-bold text-dark"><?php echo e(\Carbon\Carbon::parse($attendance->check_in)->format('h:i A')); ?></div>
                                        <div class="mt-1">
                                             <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_in_selfie): ?> <i class="bi bi-camera text-primary me-1" title="Selfie attached"></i> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_in_lat): ?> <i class="bi bi-geo-alt text-danger me-1" title="GPS attached"></i> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->checklist_responses && count($attendance->checklist_responses) > 0): ?> <i class="bi bi-card-checklist text-success" title="Checklist attached"></i> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">--:--</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="py-3.5 px-3 align-middle">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_out): ?>
                                        <div class="fw-bold text-dark"><?php echo e(\Carbon\Carbon::parse($attendance->check_out)->format('h:i A')); ?></div>
                                        <div class="mt-1">
                                             <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_out_selfie): ?> <i class="bi bi-camera text-primary me-1" title="Selfie attached"></i> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_out_lat): ?> <i class="bi bi-geo-alt text-danger me-1" title="GPS attached"></i> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_out_checklist_responses && count($attendance->check_out_checklist_responses) > 0): ?> <i class="bi bi-card-checklist text-success" title="Checklist attached"></i> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">--:--</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="py-3.5 px-3 align-middle text-center fw-semibold text-dark"><?php echo e(intdiv($worked, 60)); ?>h <?php echo e($worked % 60); ?>m</td>
                                <td class="py-3.5 px-3 align-middle text-center fw-semibold text-success"><?php echo e(intdiv($productive, 60)); ?>h <?php echo e($productive % 60); ?>m</td>
                                <td class="py-3.5 px-3 align-middle text-center fw-semibold text-warning"><?php echo e(intdiv($overtime, 60)); ?>h <?php echo e($overtime % 60); ?>m</td>
                            </tr>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isExpanded): ?>
                                <tr class="table-light">
                                    <td colspan="10" class="px-4 py-3">
                                        <!-- Employee Info Summary Strip -->
                                        <div class="card border border-light shadow-sm rounded-3 bg-white mb-3">
                                            <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="avatar avatar-md bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5" style="width: 44px; height: 44px;">
                                                        <?php echo e(strtoupper(substr($attendance->employee->name ?? 'E', 0, 1))); ?>

                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <h6 class="mb-0 fw-bold text-dark"><?php echo e($attendance->employee->name ?? 'Employee'); ?></h6>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($attendance->employee->employee_code)): ?>
                                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-0.5 small"><?php echo e($attendance->employee->employee_code); ?></span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap text-muted small mt-1">
                                                            <span><i class="bi bi-envelope me-1"></i><?php echo e($attendance->employee->email ?? 'N/A'); ?></span>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($attendance->employee->mobile)): ?>
                                                                <span>•</span>
                                                                <span><i class="bi bi-telephone me-1"></i><?php echo e($attendance->employee->mobile); ?></span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($attendance->employee->branch)): ?>
                                                        <span class="badge bg-light text-dark border px-2.5 py-1.5">
                                                            <i class="bi bi-building me-1 text-primary"></i>Branch: <?php echo e($attendance->employee->branch->name); ?>

                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($attendance->employee->department)): ?>
                                                        <span class="badge bg-light text-dark border px-2.5 py-1.5">
                                                            <i class="bi bi-diagram-3 me-1 text-info"></i>Dept: <?php echo e($attendance->employee->department->name); ?>

                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($attendance->employee->shift)): ?>
                                                        <span class="badge bg-light text-dark border px-2.5 py-1.5">
                                                            <i class="bi bi-clock-history me-1 text-success"></i>Shift: <?php echo e($attendance->employee->shift->name); ?>

                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->employee->shift->start_time && $attendance->employee->shift->end_time): ?>
                                                                (<?php echo e(\Carbon\Carbon::parse($attendance->employee->shift->start_time)->format('h:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($attendance->employee->shift->end_time)->format('h:i A')); ?>)
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($attendance->employee->reportingTo)): ?>
                                                        <span class="badge bg-light text-dark border px-2.5 py-1.5">
                                                            <i class="bi bi-person-check me-1 text-warning"></i>Reports To: <?php echo e($attendance->employee->reportingTo->name); ?>

                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->status === 'absent'): ?>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1.5">
                                                            <i class="bi bi-x-circle me-1"></i>Marked Absent (No check-in)
                                                        </span>
                                                    <?php elseif($attendance->status === 'leave'): ?>
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1.5">
                                                            <i class="bi bi-calendar-event me-1"></i>On Leave <?php echo e(!empty($attendance->status_reason) ? '(' . $attendance->status_reason . ')' : ''); ?>

                                                        </span>
                                                    <?php elseif($attendance->status === 'weekOff'): ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1.5">
                                                            <i class="bi bi-calendar-check me-1"></i>Weekly Off
                                                        </span>
                                                    <?php elseif($attendance->status === 'half_day'): ?>
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1.5">
                                                            <i class="bi bi-adjust me-1"></i>Half Day
                                                        </span>
                                                    <?php elseif($attendance->status === 'punch_in' || ($attendance->check_in && !$attendance->check_out)): ?>
                                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1.5">
                                                            <i class="bi bi-clock me-1"></i>In Office (Check-out Pending)
                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <!-- Check In Details -->
                                            <div class="col-md-6">
                                                <div class="card h-100 border border-light bg-light rounded-3 shadow-sm">
                                                    <div class="card-body p-3.5">
                                                        <div class="d-flex align-items-center justify-content-between border-bottom border-light pb-2 mb-3">
                                                            <h6 class="fw-bold text-primary mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Check In Details</h6>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_in): ?>
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($attendance->late_minutes ?? 0) > 0): ?>
                                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small">
                                                                        <i class="bi bi-clock-history me-1"></i>Late by <?php echo e($attendance->late_minutes); ?> mins
                                                                    </span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                                                        <i class="bi bi-check-circle me-1"></i>On Time
                                                                    </span>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Time</small>
                                                            <div class="fs-5 text-dark fw-semibold">
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_in): ?>
                                                                    <i class="bi bi-clock me-1 text-primary"></i> <?php echo e(\Carbon\Carbon::parse($attendance->check_in)->format('h:i A')); ?>

                                                                <?php else: ?>
                                                                    <span class="text-muted"><i class="bi bi-dash-circle me-1"></i>Not recorded</span>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Location & GPS</small>
                                                            <div class="text-dark mt-1">
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_in_lat && $attendance->check_in_lng): ?>
                                                                    <div class="small fw-semibold text-dark mb-1">
                                                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                                        Lat: <?php echo e(number_format((float)$attendance->check_in_lat, 5)); ?>, Lng: <?php echo e(number_format((float)$attendance->check_in_lng, 5)); ?>

                                                                    </div>
                                                                    <a href="https://maps.google.com/?q=<?php echo e($attendance->check_in_lat); ?>,<?php echo e($attendance->check_in_lng); ?>" target="_blank" class="btn btn-sm btn-outline-danger px-2.5 py-1 rounded-pill">
                                                                        <i class="bi bi-map me-1"></i> View on Google Maps <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted"><i class="bi bi-geo-alt me-1"></i> Location not captured</span>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <div class="mb-2">
                                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Captured Check-in Image / Selfie</small>
                                                                <?php
                                                                    $inImageUrl = \App\Models\EmployeeAttendance::formatImageUrl($attendance->check_in_selfie ?? $attendance->check_in_photo ?? null);
                                                                ?>
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inImageUrl): ?>
                                                                    <a href="<?php echo e($inImageUrl); ?>" target="_blank" class="small text-primary text-decoration-none">
                                                                        <i class="bi bi-arrows-fullscreen me-1"></i>Full Image
                                                                    </a>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                            <div class="text-center bg-white rounded-3 border border-light p-2 position-relative" style="min-height: 160px; display: flex; align-items: center; justify-content: center; background-color: #fafbfc;">
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($inImageUrl): ?>
                                                                    <div class="position-relative w-100">
                                                                        <img src="<?php echo e($inImageUrl); ?>"
                                                                             class="img-fluid rounded-3 shadow-xs"
                                                                             style="max-height: 220px; max-width: 100%; object-fit: contain;"
                                                                             alt="Check In Selfie"
                                                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'text-muted py-3\'><i class=\'bi bi-image-alt fs-2 text-secondary d-block mb-1\'></i><small class=\'text-muted\'>Image file not reachable on storage</small></div>';">
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="text-muted py-3">
                                                                        <i class="bi bi-camera-fill fs-2 text-black-50 d-block mb-1"></i>
                                                                        <small class="text-muted">No check-in selfie captured</small>
                                                                    </div>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Check Out Details -->
                                            <div class="col-md-6">
                                                <div class="card h-100 border border-light bg-light rounded-3 shadow-sm">
                                                    <div class="card-body p-3.5">
                                                        <div class="d-flex align-items-center justify-content-between border-bottom border-light pb-2 mb-3">
                                                            <h6 class="fw-bold text-secondary mb-0"><i class="bi bi-box-arrow-left me-2"></i>Check Out Details</h6>
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_out): ?>
                                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                                                                    <i class="bi bi-check2-all me-1"></i>Completed
                                                                </span>
                                                            <?php elseif($attendance->check_in): ?>
                                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 small">
                                                                    <i class="bi bi-hourglass-split me-1"></i>Punch Out Pending
                                                                </span>
                                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Time</small>
                                                            <div class="fs-5 text-dark fw-semibold">
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_out): ?>
                                                                    <i class="bi bi-clock me-1 text-secondary"></i> <?php echo e(\Carbon\Carbon::parse($attendance->check_out)->format('h:i A')); ?>

                                                                <?php else: ?>
                                                                    <span class="text-muted"><i class="bi bi-dash-circle me-1"></i>Not recorded</span>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Location & GPS</small>
                                                            <div class="text-dark mt-1">
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_out_lat && $attendance->check_out_lng): ?>
                                                                    <div class="small fw-semibold text-dark mb-1">
                                                                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                                                        Lat: <?php echo e(number_format((float)$attendance->check_out_lat, 5)); ?>, Lng: <?php echo e(number_format((float)$attendance->check_out_lng, 5)); ?>

                                                                    </div>
                                                                    <a href="https://maps.google.com/?q=<?php echo e($attendance->check_out_lat); ?>,<?php echo e($attendance->check_out_lng); ?>" target="_blank" class="btn btn-sm btn-outline-danger px-2.5 py-1 rounded-pill">
                                                                        <i class="bi bi-map me-1"></i> View on Google Maps <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted"><i class="bi bi-geo-alt me-1"></i> Location not captured</span>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                        </div>

                                                        <div class="mb-2">
                                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Captured Check-out Image / Selfie</small>
                                                                <?php
                                                                    $outImageUrl = \App\Models\EmployeeAttendance::formatImageUrl($attendance->check_out_selfie ?? $attendance->check_out_photo ?? null);
                                                                ?>
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($outImageUrl): ?>
                                                                    <a href="<?php echo e($outImageUrl); ?>" target="_blank" class="small text-secondary text-decoration-none">
                                                                        <i class="bi bi-arrows-fullscreen me-1"></i>Full Image
                                                                    </a>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                            <div class="text-center bg-white rounded-3 border border-light p-2 position-relative" style="min-height: 160px; display: flex; align-items: center; justify-content: center; background-color: #fafbfc;">
                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($outImageUrl): ?>
                                                                    <div class="position-relative w-100">
                                                                        <img src="<?php echo e($outImageUrl); ?>"
                                                                             class="img-fluid rounded-3 shadow-xs"
                                                                             style="max-height: 220px; max-width: 100%; object-fit: contain;"
                                                                             alt="Check Out Selfie"
                                                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'text-muted py-3\'><i class=\'bi bi-image-alt fs-2 text-secondary d-block mb-1\'></i><small class=\'text-muted\'>Image file not reachable on storage</small></div>';">
                                                                    </div>
                                                                <?php else: ?>
                                                                    <div class="text-muted py-3">
                                                                        <i class="bi bi-camera-fill fs-2 text-black-50 d-block mb-1"></i>
                                                                        <small class="text-muted">No check-out selfie captured</small>
                                                                    </div>
                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Checklist Details -->
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->checklist_responses && count($attendance->checklist_responses) > 0): ?>
                                                <div class="col-12 mt-2">
                                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-2 text-success"></i>Pre-Check-in Checklist</h6>
                                                    <div class="card border-0 shadow-sm rounded-3">
                                                        <div class="table-responsive rounded-3">
                                                            <table class="table table-hover table-borderless align-middle mb-0">
                                                                <thead class="bg-light">
                                                                    <tr>
                                                                        <th class="ps-4 fw-600 text-muted">Question</th>
                                                                        <th class="text-end pe-4 fw-600 text-muted">Response</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $attendance->checklist_responses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $response): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                                        <tr class="border-bottom border-light">
                                                                            <td class="ps-4 fw-600 py-3"><?php echo e($response['question']); ?></td>
                                                                            <td class="text-end pe-4 py-3">
                                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($response['answer'] == 'Yes'): ?>
                                                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                                                                <?php else: ?>
                                                                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i> No</span>
                                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendance->check_out_checklist_responses && count($attendance->check_out_checklist_responses) > 0): ?>
                                                <div class="col-12 mt-2">
                                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-2 text-success"></i>Pre-Check-out Checklist</h6>
                                                    <div class="card border-0 shadow-sm rounded-3">
                                                        <div class="table-responsive rounded-3">
                                                            <table class="table table-hover table-borderless align-middle mb-0">
                                                                <thead class="bg-light">
                                                                    <tr>
                                                                        <th class="ps-4 fw-600 text-muted">Question</th>
                                                                        <th class="text-end pe-4 fw-600 text-muted">Response</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $attendance->check_out_checklist_responses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $response): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                                        <tr class="border-bottom border-light">
                                                                            <td class="ps-4 fw-600 py-3"><?php echo e($response['question']); ?></td>
                                                                            <td class="text-end pe-4 py-3">
                                                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($response['answer'] == 'Yes'): ?>
                                                                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                                                                <?php else: ?>
                                                                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i> No</span>
                                                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                                            </td>
                                                                        </tr>
                                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x fs-1 d-block mb-3"></i>
                                    No attendance records found for this date.
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($attendances->hasPages()): ?>
            <div class="card-footer bg-white border-0 py-3">
                <?php echo e($attendances->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    
    <!-- Attendance Details Modal (disabled: details now shown inline) -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(false): ?>
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-0">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-md me-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.2rem;">
                            <?php echo e(strtoupper(substr($selectedAttendance->employee->name, 0, 1))); ?>

                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h5 class="modal-title fw-bold text-dark mb-0"><?php echo e($selectedAttendance->employee->name); ?></h5>
                                <span class="badge bg-dark bg-gradient rounded-pill px-3 py-1 fs-7">
                                    <i class="bi bi-laptop me-1"></i><?php echo e(ucfirst($selectedAttendance->working_mode ?: 'office')); ?> Mode
                                </span>
                            </div>
                                <small class="text-muted"><i class="bi bi-calendar3 me-1"></i> <?php echo e(\Carbon\Carbon::parse($selectedAttendance->date)->format('l, F j, Y')); ?></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" wire:click="closeDetailsModal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Check In Details -->
                        <div class="col-md-6">
                            <div class="card h-100 border border-light bg-light rounded-3 shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary border-bottom border-light pb-2 mb-3"><i class="bi bi-box-arrow-in-right me-2"></i>Check In</h6>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Time</small>
                                        <div class="fs-5 text-dark">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_in): ?>
                                                <i class="bi bi-clock me-1 text-primary"></i> <?php echo e(\Carbon\Carbon::parse($selectedAttendance->check_in)->format('h:i A')); ?>

                                            <?php else: ?>
                                                <span class="text-muted">Not recorded</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Location</small>
                                        <div class="text-dark">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_in_lat && $selectedAttendance->check_in_lng): ?>
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> 
                                                <a href="https://maps.google.com/?q=<?php echo e($selectedAttendance->check_in_lat); ?>,<?php echo e($selectedAttendance->check_in_lng); ?>" target="_blank" class="text-decoration-none">
                                                    View on Map <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="bi bi-geo-alt me-1"></i> Location not captured</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Capture Image</small>
                                        <div class="mt-2 text-center bg-white rounded border border-light p-2" style="min-height: 150px; display: flex; align-items: center; justify-content: center;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_in_selfie): ?>
                                                <img src="<?php echo e($selectedAttendance->check_in_selfie); ?>" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check In Selfie">
                                            <?php elseif($selectedAttendance->check_in_photo): ?>
                                                <img src="<?php echo e($selectedAttendance->check_in_photo); ?>" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check In Photo">
                                            <?php else: ?>
                                                <div class="text-muted">
                                                    <i class="bi bi-camera-fill fs-1 text-light d-block mb-1"></i>
                                                    <small>No image captured</small>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Check Out Details -->
                        <div class="col-md-6">
                            <div class="card h-100 border border-light bg-light rounded-3 shadow-sm">
                                <div class="card-body">
                                    <h6 class="fw-bold text-secondary border-bottom border-light pb-2 mb-3"><i class="bi bi-box-arrow-left me-2"></i>Check Out</h6>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Time</small>
                                        <div class="fs-5 text-dark">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_out): ?>
                                                <i class="bi bi-clock me-1 text-secondary"></i> <?php echo e(\Carbon\Carbon::parse($selectedAttendance->check_out)->format('h:i A')); ?>

                                            <?php else: ?>
                                                <span class="text-muted">Not recorded</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Location</small>
                                        <div class="text-dark">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_out_lat && $selectedAttendance->check_out_lng): ?>
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> 
                                                <a href="https://maps.google.com/?q=<?php echo e($selectedAttendance->check_out_lat); ?>,<?php echo e($selectedAttendance->check_out_lng); ?>" target="_blank" class="text-decoration-none">
                                                    View on Map <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="bi bi-geo-alt me-1"></i> Location not captured</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Capture Image</small>
                                        <div class="mt-2 text-center bg-white rounded border border-light p-2" style="min-height: 150px; display: flex; align-items: center; justify-content: center;">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_out_selfie): ?>
                                                <img src="<?php echo e($selectedAttendance->check_out_selfie); ?>" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check Out Selfie">
                                            <?php elseif($selectedAttendance->check_out_photo): ?>
                                                <img src="<?php echo e($selectedAttendance->check_out_photo); ?>" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;" alt="Check Out Photo">
                                            <?php else: ?>
                                                <div class="text-muted">
                                                    <i class="bi bi-camera-fill fs-1 text-light d-block mb-1"></i>
                                                    <small>No image captured</small>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Checklist Details -->
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->checklist_responses && count($selectedAttendance->checklist_responses) > 0): ?>
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-2 text-success"></i>Pre-Check-in Checklist</h6>
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="table-responsive rounded-3">
                                    <table class="table table-hover table-borderless align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 fw-600 text-muted">Question</th>
                                                <th class="text-end pe-4 fw-600 text-muted">Response</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $selectedAttendance->checklist_responses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $response): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <tr class="border-bottom border-light">
                                                    <td class="ps-4 fw-600 py-3"><?php echo e($response['question']); ?></td>
                                                    <td class="text-end pe-4 py-3">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($response['answer'] == 'Yes'): ?>
                                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i> No</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAttendance->check_out_checklist_responses && count($selectedAttendance->check_out_checklist_responses) > 0): ?>
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check me-2 text-success"></i>Pre-Check-out Checklist</h6>
                            <div class="card border-0 shadow-sm rounded-3">
                                <div class="table-responsive rounded-3">
                                    <table class="table table-hover table-borderless align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4 fw-600 text-muted">Question</th>
                                                <th class="text-end pe-4 fw-600 text-muted">Response</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $selectedAttendance->check_out_checklist_responses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $response): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <tr class="border-bottom border-light">
                                                    <td class="ps-4 fw-600 py-3"><?php echo e($response['question']); ?></td>
                                                    <td class="text-end pe-4 py-3">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($response['answer'] == 'Yes'): ?>
                                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i> Yes</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i> No</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/attendance/employee-attendance.blade.php ENDPATH**/ ?>