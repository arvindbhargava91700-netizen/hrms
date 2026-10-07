<div>
<?php echo $__env->make('partials.report-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<div id="print-area">

    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Attendance Report</h4>
        <p class="text-muted small mb-0">Generated: <?php echo e(now()->format('d M Y, h:i A')); ?></p><hr>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Attendance Report</h4>
            <p class="text-muted mb-0 small">Track employee check-in/out, branch, team hierarchy, and working hours</p>
        </div>
        <div class="report-actions d-flex gap-2">
            <button class="btn btn-outline-success" wire:click="exportCsv" wire:loading.attr="disabled">
                <i class="bi bi-filetype-csv me-1"></i> Export CSV
            </button>
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Print
            </button>
        </div>
    </div>

    
    <div class="report-filter-card card mb-4 d-print-none shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-funnel text-primary fs-5"></i>
                    <span class="fw-bold text-dark" style="font-size:0.95rem;">Filter Attendance</span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($branchId || $teamId || $employeeId || $statusFilter || $selectedWorkingMode): ?>
                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <div class="row g-3">
                
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Branch</label>
                    <select class="form-select border-primary-subtle" wire:model.live="branchId">
                        <option value="">All Branches</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Team / Reporting Manager</label>
                    <select class="form-select border-info-subtle" wire:model.live="teamId">
                        <option value="">All Teams / Reporting Managers</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($team->id); ?>"><?php echo e($team->name); ?> <?php echo e($team->employee_code ? '('.$team->employee_code.')' : ''); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label"> Employee</label>
                    <select class="form-select" wire:model.live="employeeId">
                        <option value="">All Employees</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->name); ?> <?php echo e($emp->employee_code ? '('.$emp->employee_code.')' : ''); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                
                <div class="col-md-3 col-sm-6">
                    <label class="filter-label">Status</label>
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="present">Present (All)</option>
                        <option value="punch_out">Present (Punch Out)</option>
                        <option value="punch_in">In Office (Punch In)</option>
                        <option value="half_day">Half Day</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                        <option value="leave">On Leave</option>
                    </select>
                </div>

                
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Working Mode</label>
                    <select class="form-select" wire:model.live="selectedWorkingMode">
                        <option value="">All Modes</option>
                        <option value="office">Office</option>
                        <option value="remote">Remote (WFH)</option>
                        <option value="field">Field Work</option>
                    </select>
                </div>

                
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">From Date</label>
                    <input type="date" class="form-control" wire:model.live="startDate">
                </div>

                
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">To Date</label>
                    <input type="date" class="form-control" wire:model.live="endDate">
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Records</div>
                <div class="fw-bold fs-4 text-primary"><?php echo e($reportSummary['totalRecords']); ?></div>
                <div class="text-muted" style="font-size: 0.72rem;"><?php echo e($reportSummary['totalHours']); ?> worked</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Present</div>
                <div class="fw-bold fs-4 text-success"><?php echo e($reportSummary['presentCount']); ?></div>
                <div class="text-muted" style="font-size: 0.72rem;">Avg: <?php echo e($reportSummary['avgWorkingMins']); ?>m</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Half Day</div>
                <div class="fw-bold fs-4 text-warning"><?php echo e($reportSummary['halfDayCount']); ?></div>
                <div class="text-muted" style="font-size: 0.72rem;">Partial work</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Absent</div>
                <div class="fw-bold fs-4 text-danger"><?php echo e($reportSummary['absentCount']); ?></div>
                <div class="text-muted" style="font-size: 0.72rem;">Under minimum</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">On Leave</div>
                <div class="fw-bold fs-4 text-info"><?php echo e($reportSummary['leaveCount']); ?></div>
                <div class="text-muted" style="font-size: 0.72rem;">Approved leave</div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="report-stat-card card p-3 text-center border-0 shadow-sm rounded-3">
                <div class="text-muted small fw-bold text-uppercase mb-1">Late Records</div>
                <div class="fw-bold fs-4 text-danger"><?php echo e($reportSummary['lateCount']); ?></div>
                <div class="text-muted" style="font-size: 0.72rem;"><?php echo e($reportSummary['missedPunch']); ?> missed punch</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Employee</th>
                        <th>Branch</th>
                        <th>Team / Reporting To</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Working</th>
                        <th>Late (Mins)</th>
                        <th>Status</th>
                        <th>Total Hours</th>
                        <th>Productive Hours</th>
                        <th>Overtime</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?php echo e(\Carbon\Carbon::parse($row->date)->format('d M Y')); ?></td>
                        <td>
                            <div class="fw-bold text-dark"><?php echo e(optional($row->employee)->name); ?></div>
                            <div class="small text-muted"><?php echo e(optional($row->employee)->employee_code ?? substr(optional($row->employee)->id,0,8)); ?></div>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                <i class="bi bi-geo-alt me-1"></i><?php echo e(optional(optional($row->employee)->branch)->name ?? 'Main Branch'); ?>

                            </span>
                        </td>
                        <td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(optional($row->employee)->reportingTo): ?>
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-person-badge text-info"></i>
                                    <span class="fw-semibold text-dark"><?php echo e($row->employee->reportingTo->name); ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small fst-italic">Direct / None</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td><?php echo e($row->check_in ? \Carbon\Carbon::parse($row->check_in)->format('h:i A') : '-'); ?></td>
                        <td><?php echo e($row->check_out ? \Carbon\Carbon::parse($row->check_out)->format('h:i A') : '-'); ?></td>
                        <td class="fw-bold"><?php echo e($row->working_minutes ?? 0); ?> min</td>
                        <td class="<?php echo e($row->late_minutes > 0 ? 'text-danger fw-bold' : 'text-muted'); ?>"><?php echo e($row->late_minutes ?? 0); ?></td>
                        <td>
                            <?php
                                $st = strtolower($row->status ?? '');
                                $badgeClass = match($st) {
                                    'punch_out', 'present' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                    'punch_in'  => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                    'half_day'  => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
                                    'absent'    => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                    'leave'     => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
                                    'late'      => 'bg-danger bg-opacity-10 text-danger',
                                    default     => 'bg-secondary bg-opacity-10 text-secondary',
                                };
                                $statusLabel = match($st) {
                                    'punch_out' => 'Present',
                                    'punch_in'  => 'In Office',
                                    'half_day'  => 'Half Day',
                                    'absent'    => 'Absent',
                                    'leave'     => 'On Leave',
                                    'late'      => 'Late',
                                    default     => ucfirst(str_replace('_', ' ', $st ?: 'Unknown')),
                                };
                            ?>
                            <span class="badge <?php echo e($badgeClass); ?> rounded-pill px-3 py-1 fw-bold">
                                <?php echo e($statusLabel); ?>

                            </span>
                        </td>
                        <?php
                            $worked = (int) ($row->working_minutes ?? 0);
                            $rowShift = optional($row->employee)->shift ?? null;
                            $req = ($rowShift && $rowShift->min_present_mins) ? (int) $rowShift->min_present_mins : $reportSummary['defaultRequiredMins'];
                            $prod = min($worked, $req);
                            $ov = max(0, $worked - $req);
                        ?>
                        <td class="fw-bold"><?php echo e(intdiv($worked, 60)); ?>h <?php echo e($worked % 60); ?>m</td>
                        <td class="fw-bold text-secondary"><?php echo e(intdiv($prod, 60)); ?>h <?php echo e($prod % 60); ?>m</td>
                        <td class="fw-bold text-warning"><?php echo e($ov > 0 ? (intdiv($ov, 60) . 'h ' . ($ov % 60) . 'm') : '-'); ?></td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr><td colspan="12" class="text-center py-5 text-muted">No attendance records found.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reportData->hasPages()): ?>
        <div class="card-footer border-top bg-transparent p-4 d-print-none"><?php echo e($reportData->links()); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/reports/hrms/attendance-report.blade.php ENDPATH**/ ?>