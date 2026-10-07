<div>
<?php echo $__env->make('partials.report-styles', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<div id="print-area">

    
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Staff Report</h4>
        <p class="text-muted small mb-0">Generated on: <?php echo e(now()->format('d M Y, h:i A')); ?></p>
        <hr>
    </div>

    
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none flex-wrap gap-2">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Staff Report</h4>
            <p class="text-muted mb-0 small">Filter staff by branch and team hierarchy, and export report</p>
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
                    <span class="fw-bold text-dark" style="font-size:0.95rem;">Filter Staff</span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($branchId || $teamId || $departmentId || $roleName || $status || $search): ?>
                    <button class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1 rounded-pill px-3" wire:click="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="row g-3">
                
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Branch</label>
                    <select class="form-select border-primary-subtle" wire:model.live="branchId">
                        <option value="">All Branches</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Team / Reporting Manager</label>
                    <select class="form-select border-info-subtle" wire:model.live="teamId">
                        <option value="">All Teams / Reporting Managers</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($team->id); ?>"><?php echo e($team->name); ?> <?php echo e($team->employee_code ? '('.$team->employee_code.')' : ''); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Department</label>
                    <select class="form-select" wire:model.live="departmentId">
                        <option value="">All Departments</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Role</label>
                    <select class="form-select" wire:model.live="roleName">
                        <option value="">All Roles</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id; ?>
                            <option value="<?php echo e($role->name); ?>"><?php echo e(str_replace([$partnerId . '_', '_' . $partnerId], '', $role->name)); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </div>

                
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Status</label>
                    <select class="form-select" wire:model.live="status">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                
                <div class="col-md-4 col-sm-6">
                    <label class="filter-label">Search Staff</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" wire:model.live.debounce.300ms="search" placeholder="Name, code, email, mobile...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Staff</div>
                <div class="fw-bold fs-4 text-primary"><?php echo e($reportData->total()); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Active</div>
                <div class="fw-bold fs-4 text-success"><?php echo e($reportData->where('status','active')->count()); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Inactive</div>
                <div class="fw-bold fs-4 text-danger"><?php echo e($reportData->where('status','inactive')->count()); ?></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="report-stat-card card p-3 text-center">
                <div class="text-muted small fw-bold text-uppercase mb-1">Total Salary</div>
                <div class="fw-bold fs-4 text-dark">₹<?php echo e(number_format($reportData->sum('basic_salary'), 0)); ?></div>
            </div>
        </div>
    </div>

    
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table report-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4"># Code</th>
                        <th>Employee</th>
                        <th>Branch</th>
                        <th>Team / Reporting To</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Contact</th>
                        <th>Employment</th>
                        <th>Basic Salary</th>
                        <th>Joining Date</th>
                        <th class="pe-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reportData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="ps-4 text-muted small fw-semibold"><?php echo e($row->employee_code ?? substr($row->id, 0, 8)); ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="<?php echo e($row->avatar_url); ?>" class="rounded-circle border" width="34" height="34" alt="<?php echo e($row->name); ?>" style="object-fit: cover;">
                                <div>
                                    <div class="fw-bold text-dark"><?php echo e($row->name); ?></div>
                                    <div class="text-muted small"><?php echo e($row->email); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                <i class="bi bi-geo-alt me-1"></i><?php echo e(optional($row->branch)->name ?? 'Main Branch'); ?>

                            </span>
                        </td>
                        <td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->reportingTo): ?>
                                <div class="d-flex align-items-center gap-1">
                                    <i class="bi bi-person-badge text-info"></i>
                                    <span class="fw-semibold text-dark"><?php echo e($row->reportingTo->name); ?></span>
                                </div>
                            <?php else: ?>
                                <span class="text-muted small fst-italic">Direct / None</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->department): ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1"><?php echo e($row->department->name); ?></span>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td>
                            <?php $partnerId = auth()->user()->isPartner() ? auth()->id() : auth()->user()->parent_id; ?>
                            <span class="badge bg-dark bg-opacity-10 text-dark rounded-pill px-2 py-1">
                                <?php echo e($row->roles->first() ? str_replace([$partnerId.'_','_'.$partnerId],'',$row->roles->first()->name) : '-'); ?>

                            </span>
                        </td>
                        <td>
                            <div class="small text-muted">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row->mobile): ?>
                                    <div><i class="bi bi-telephone me-1"></i><?php echo e($row->mobile); ?></div>
                                <?php else: ?>
                                    <span>-</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-1">
                                <?php echo e(ucfirst(str_replace('_', ' ', $row->employment_type ?? 'full_time'))); ?>

                            </span>
                        </td>
                        <td class="fw-bold text-dark">₹<?php echo e(number_format($row->basic_salary, 0)); ?></td>
                        <td class="text-muted small"><?php echo e($row->joining_date ? \Carbon\Carbon::parse($row->joining_date)->format('d M Y') : '-'); ?></td>
                        <td class="pe-4 text-center">
                            <span class="badge bg-<?php echo e($row->status=='active'?'success':'danger'); ?> bg-opacity-10 text-<?php echo e($row->status=='active'?'success':'danger'); ?> rounded-pill px-3 py-1">
                                <?php echo e(ucfirst($row->status)); ?>

                            </span>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-people text-muted opacity-50 display-6 d-block mb-2"></i>
                            No staff records found matching the selected filter criteria.
                        </td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reportData->hasPages()): ?>
        <div class="card-footer border-top bg-transparent p-3 d-print-none">
            <?php echo e($reportData->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/reports/hrms/staff-report.blade.php ENDPATH**/ ?>