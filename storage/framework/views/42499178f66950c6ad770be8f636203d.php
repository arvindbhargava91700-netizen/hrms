<div class="container-fluid py-4">
    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4 border-bottom-0" style="gap: 0.5rem; flex-wrap: nowrap; overflow-x: auto; white-space: nowrap;">

        <li class="nav-item">
            <a class="nav-link <?php echo e($activeTab === 'holidays' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0'); ?>" 
               wire:click.prevent="switchTab('holidays')" 
               href="#" 
               style="<?php echo e($activeTab === 'holidays' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;'); ?>">
                <i class="bi bi-calendar-event me-1"></i> Holidays
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo e($activeTab === 'commissions' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0'); ?>" 
               wire:click.prevent="switchTab('commissions')" 
               href="#" 
               style="<?php echo e($activeTab === 'commissions' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;'); ?>">
                <i class="bi bi-diagram-3 me-1"></i> Commission Levels
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo e($activeTab === 'pipeline' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0'); ?>" 
               wire:click.prevent="switchTab('pipeline')" 
               href="#" 
               style="<?php echo e($activeTab === 'pipeline' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;'); ?>">
                <i class="bi bi-distribute-vertical me-1"></i> Pipeline Config
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo e($activeTab === 'checklist' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0'); ?>" 
               wire:click.prevent="switchTab('checklist')" 
               href="#" 
               style="<?php echo e($activeTab === 'checklist' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;'); ?>">
                <i class="bi bi-list-check me-1"></i> Attendance Checklist
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo e($activeTab === 'leave_categories' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0'); ?>" 
               wire:click.prevent="switchTab('leave_categories')" 
               href="#" 
               style="<?php echo e($activeTab === 'leave_categories' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;'); ?>">
                <i class="bi bi-folder-check me-1"></i> Leave Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo e($activeTab === 'expense_categories' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0'); ?>" 
               wire:click.prevent="switchTab('expense_categories')" 
               href="#" 
               style="<?php echo e($activeTab === 'expense_categories' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;'); ?>">
                <i class="bi bi-tags me-1"></i> Expense Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo e($activeTab === 'payslip_config' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0'); ?>" 
               wire:click.prevent="switchTab('payslip_config')" 
               href="#" 
               style="<?php echo e($activeTab === 'payslip_config' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;'); ?>">
                <i class="bi bi-receipt-cutoff me-1"></i> Payslip Config
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo e($activeTab === 'task_statuses' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0'); ?>" 
               wire:click.prevent="switchTab('task_statuses')" 
               href="#" 
               style="<?php echo e($activeTab === 'task_statuses' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;'); ?>">
                <i class="bi bi-kanban me-1"></i> Task Status
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo e($activeTab === 'performance' ? 'active shadow-sm fw-bold border-bottom-0' : 'bg-light text-muted border-0'); ?>" 
               wire:click.prevent="switchTab('performance')" 
               href="#" 
               style="<?php echo e($activeTab === 'performance' ? 'border-radius: 8px 8px 0 0;' : 'border-radius: 8px;'); ?>">
                <i class="bi bi-trophy-fill me-1 text-warning"></i> Performance Config
            </a>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content">


        <!-- Tab: Holidays -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'holidays'): ?>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success_holiday')): ?>
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success_holiday')); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <form wire:submit.prevent="addHoliday" class="row g-3 mb-4 p-3.5 rounded-3" style="background:#f8fafc; border:1px solid #e2e8f0;">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-dark small">Branch (Optional)</label>
                                <select class="form-select bg-white" wire:model="holiday_branch_id">
                                    <option value="">Global (All Branches)</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['holiday_branch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-dark small">Holiday Name</label>
                                <input type="text" class="form-control bg-white" placeholder="e.g. Diwali, Independence Day" wire:model="holiday_name">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['holiday_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-dark small">Holiday Date</label>
                                <input type="date" class="form-control bg-white" wire:model="holiday_date">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['holiday_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $holidays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $holiday): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <tr>
                                            <td class="py-3 px-4 align-middle fw-bold text-dark"><?php echo e($holiday->name); ?></td>
                                            <td class="py-3 px-4 align-middle">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($holiday->branch_id): ?>
                                                    <span class="badge bg-info text-dark rounded-pill"><i class="bi bi-building me-1"></i><?php echo e($holiday->branch->name); ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary rounded-pill"><i class="bi bi-globe me-1"></i>Global</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="py-3 px-4 align-middle text-secondary"><i class="bi bi-calendar-check me-1 text-primary"></i><?php echo e(\Carbon\Carbon::parse($holiday->date)->format('M d, Y')); ?></td>
                                            <td class="py-3 px-4 text-end align-middle">
                                                <button wire:click="deleteHoliday(<?php echo e($holiday->id); ?>)" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirm('Are you sure you want to remove this holiday?') || event.stopImmediatePropagation()">
                                                    <i class="bi bi-trash me-1"></i> Remove
                                                </button>
                                            </td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-5">
                                                <i class="bi bi-calendar-x fs-1 text-secondary d-block mb-2"></i>
                                                No holidays defined yet. Add your first holiday above.
                                            </td>
                                        </tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Tab: Commissions -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'commissions'): ?>
        <div class="row fade show active">
            <div class="col-12">
                <!-- Commission TDS Setting Card -->
                <div class="card shadow-sm border-0 bg-white rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-percent me-2 text-primary"></i>Commission TDS Deduction Setting</h5>
                        <p class="text-muted small mb-0">Configure the Tax Deducted at Source (TDS) percentage to deduct automatically during monthly commission processing.</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success_tds')): ?>
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
                                <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success_tds')); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['commission_tds_percent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger extra-small d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success_level')): ?>
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
                                <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success_level')); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <form wire:submit.prevent="addCommissionLevel" class="p-3.5 rounded-3 mb-4" style="background:#f8fafc; border:1px solid #e2e8f0;">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold text-dark small">Level Name</label>
                                    <input type="text" class="form-control bg-white" placeholder="e.g. Junior (L1)" wire:model="level_name">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['level_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark small">Hierarchy Order</label>
                                    <input type="number" class="form-control bg-white" placeholder="e.g. 1" wire:model="level_order" min="1">
                                    <small class="text-muted extra-small d-block">Lower = lower rank.</small>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['level_order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold text-dark small">Commission (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control bg-white" placeholder="5.0" wire:model="commission_percent">
                                        <span class="input-group-text bg-white text-muted">%</span>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['commission_percent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $commissionLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <tr>
                                            <td class="py-3.5 px-4 align-middle fw-bold text-dark">#<?php echo e($level->level_order); ?></td>
                                            <td class="py-3.5 px-4 align-middle text-dark fw-semibold"><?php echo e($level->level_name); ?></td>
                                            <td class="py-3.5 px-4 align-middle"><span class="badge bg-success bg-gradient rounded-pill px-3 py-1.5 fw-bold fs-6"><?php echo e($level->commission_percent); ?>%</span></td>
                                            <td class="py-3.5 px-4 align-middle text-muted small"><?php echo e($level->description ?: '-'); ?></td>
                                            <td class="py-3.5 px-4 text-end align-middle">
                                                <button wire:click="deleteCommissionLevel(<?php echo e($level->id); ?>)" class="btn btn-sm btn-outline-danger rounded-pill px-3" onclick="confirm('Are you sure you want to remove this commission level?') || event.stopImmediatePropagation()">
                                                    <i class="bi bi-trash me-1"></i> Remove
                                                </button>
                                            </td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">
                                                <i class="bi bi-diagram-3 fs-1 text-secondary d-block mb-2"></i>
                                                No commission levels defined yet. Create your first level above.
                                            </td>
                                        </tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <!-- Tab: Pipeline Config -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'pipeline'): ?>
        <div class="row fade show active">
            <div class="col-12">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('partner.hrms.pipeline-settings');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1412379431-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Tab: Attendance Checklist -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'checklist'): ?>
        <div class="row fade show active">
            <div class="col-12">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('partner.hrms.attendance.attendance-settings');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1412379431-1', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Tab: Leave Categories -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'leave_categories'): ?>
        <div class="row fade show active">
            <div class="col-12">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('partner.hrms.leave-categories');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1412379431-2', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Tab: Expense Categories -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'expense_categories'): ?>
        <div class="row fade show active">
            <div class="col-12">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('partner.hrms.expense-categories');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1412379431-3', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Tab: Task Statuses -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'task_statuses'): ?>
        <div class="row fade show active">
            <div class="col-12">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('partner.hrms.task-statuses');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1412379431-4', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Tab: Payslip Config -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'payslip_config'): ?>
        <div class="row fade show active">
            <div class="col-12">
                <div class="card shadow-sm border-0 bg-white rounded-4 overflow-hidden mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-receipt-cutoff me-2 text-primary"></i>Payslip Configuration</h5>
                        <p class="text-muted small mb-0">Configure the details that appear on the downloaded salary slips (payslips) for your employees. The live preview updates as you type.</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success_payslip')): ?>
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4">
                                <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success_payslip')); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['new_payslip_logo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger extra-small d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($new_payslip_logo): ?>
                                                <div class="mt-2 text-success small"><i class="bi bi-check-circle me-1"></i> New logo ready to save.</div>
                                            <?php elseif($payslip_logo): ?>
                                                <div class="mt-2"><img src="<?php echo e(Storage::url($payslip_logo)); ?>" alt="Current Logo" style="max-height: 40px;"></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark mb-1">Company Name</label>
                                            <input type="text" class="form-control bg-white" wire:model.live="payslip_company_name" placeholder="Enter Company Name">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['payslip_company_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger extra-small d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark mb-1">Company Address</label>
                                            <textarea class="form-control bg-white" wire:model.live="payslip_company_address" rows="2" placeholder="Enter full company address to appear on payslips"></textarea>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['payslip_company_address'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger extra-small d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        
                                        <h6 class="fw-bold text-dark mb-2 mt-4 border-bottom pb-2">Signatory Details</h6>
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark mb-1">Digital Signature (Optional)</label>
                                            <input type="file" class="form-control bg-white" wire:model="new_payslip_signature" accept="image/*">
                                            <div class="text-muted mt-1" style="font-size: 0.75rem;">Max size: 2MB. Max dimensions: 1024x1024 pixels.</div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['new_payslip_signature'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger extra-small d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($new_payslip_signature): ?>
                                                <div class="mt-2 text-success small"><i class="bi bi-check-circle me-1"></i> New signature ready to save.</div>
                                            <?php elseif($payslip_signature): ?>
                                                <div class="mt-2"><img src="<?php echo e(Storage::url($payslip_signature)); ?>" alt="Current Signature" style="max-height: 40px;"></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-bold text-dark mb-1">Authorized Signatory Name/Title</label>
                                            <input type="text" class="form-control bg-white" wire:model.live="payslip_authorized_signatory" placeholder="e.g. HR Manager / Director">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['payslip_authorized_signatory'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger extra-small d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="col-12" wire:ignore>
                                            <label class="form-label fw-bold text-dark mb-1">Terms & Conditions (Optional)</label>
                                            <div x-data="{ 
                                                    content: <?php if ((object) ('payslip_terms_conditions') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('payslip_terms_conditions'->value()); ?>')<?php echo e('payslip_terms_conditions'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('payslip_terms_conditions'); ?>')<?php endif; ?>.live,
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
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['payslip_terms_conditions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger extra-small d-block mt-1"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($new_payslip_logo): ?>
                                                    <img src="<?php echo e($new_payslip_logo->temporaryUrl()); ?>" alt="Logo" style="width: 200px; height: 80px; object-fit: contain; margin-bottom:10px; display:block; object-position: left;">
                                                <?php elseif($payslip_logo): ?>
                                                    <img src="<?php echo e(Storage::url($payslip_logo)); ?>" alt="Logo" style="width: 200px; height: 80px; object-fit: contain; margin-bottom:10px; display:block; object-position: left;">
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <div class="fw-bold" style="color: #0056b3; font-size: 1.2rem;"><?php echo e($payslip_company_name ?: 'Company Name'); ?></div>
                                                <div class="text-muted" style="font-size: 0.75rem; max-width: 200px; white-space: pre-line;"><?php echo e($payslip_company_address ?: 'Company Address'); ?></div>
                                            </div>
                                            <div class="text-end text-muted" style="font-size: 0.7rem;">
                                                Payslip for <?php echo e(now()->format('M Y')); ?><br>
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
                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payslip_terms_conditions): ?>
                                        <div class="mb-3">
                                            <style>
                                                .terms-preview-content ul, .terms-preview-content ol { padding-left: 1.5rem; margin-bottom: 0.5rem; }
                                                .terms-preview-content li { margin-bottom: 0.25rem; }
                                                .terms-preview-content p { margin-bottom: 0.5rem; }
                                            </style>
                                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.75rem;">Terms & Conditions</h6>
                                            <div class="text-muted terms-preview-content" style="font-size: 0.65rem;"><?php echo $payslip_terms_conditions; ?></div>
                                        </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <!-- Footer Preview -->
                                        <div class="d-flex justify-content-between mt-auto pt-4">
                                            <div class="text-muted" style="font-size: 0.65rem; align-self: flex-end;">
                                                This is a system generated payslip.
                                            </div>
                                            <div class="text-center" style="width: 150px;">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($new_payslip_signature): ?>
                                                    <img src="<?php echo e($new_payslip_signature->temporaryUrl()); ?>" alt="Signature" style="width: 150px; height: 60px; object-fit: contain; margin-bottom:5px;">
                                                <?php elseif($payslip_signature): ?>
                                                    <img src="<?php echo e(Storage::url($payslip_signature)); ?>" alt="Signature" style="width: 150px; height: 60px; object-fit: contain; margin-bottom:5px;">
                                                <?php else: ?>
                                                    <div style="height:60px; margin-bottom:5px;"></div>
                                                    <div style="border-top: 1px solid #333; margin-top:2px;"></div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <div class="fw-bold text-dark mt-1" style="font-size: 0.75rem;"><?php echo e($payslip_authorized_signatory ?: 'Authorized Signatory'); ?></div>
                                                <div class="text-muted" style="font-size: 0.65rem;"><?php echo e($payslip_company_name ?: 'Company Name'); ?></div>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <!-- Tab: Performance Config -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'performance'): ?>
        <?php
            $customConfigCount = count($perf_staff_overrides);
            $totalStaffCount = $staffMembers->count();
            $defaultConfigCount = max(0, $totalStaffCount - $customConfigCount);
        ?>
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
                                <i class="bi bi-person-check-fill text-primary me-1"></i> Total: <strong><?php echo e($totalStaffCount); ?></strong>
                            </span>
                            <span class="badge bg-warning-subtle text-dark border border-warning px-3 py-2 rounded-pill small">
                                <i class="bi bi-sliders text-warning me-1"></i> Custom: <strong><?php echo e($customConfigCount); ?></strong>
                            </span>
                            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill small">
                                <i class="bi bi-building text-secondary me-1"></i> Default: <strong><?php echo e($defaultConfigCount); ?></strong>
                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($customConfigCount > 0): ?>
                                <button type="button" wire:click="resetAllStaffToDefault" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1.5" onclick="confirm('Are you sure you want to reset all staff back to company default weights?') || event.stopImmediatePropagation()">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset All to Default
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success_staff_performance')): ?>
                            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success_staff_performance')); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- Filter Controls -->
                        <div class="row g-3 mb-4 p-3 rounded-4" style="background:#f8fafc; border: 1px solid #e2e8f0;">
                            <div class="col-md-7">
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" class="form-control bg-white border-start-0 ps-0" placeholder="Search staff by name, employee code, or email..." wire:model.live.debounce.300ms="staffSearch">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($staffSearch)): ?>
                                        <button class="btn btn-outline-secondary border-start-0 bg-white" type="button" wire:click="$set('staffSearch', '')"><i class="bi bi-x-lg"></i></button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <select class="form-select bg-white" wire:model.live="staffDeptFilter">
                                    <option value="">All Departments</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $staffMembers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $staff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php
                                            $hasCustom = isset($perf_staff_overrides[$staff->id]);
                                            $sAtt = $hasCustom ? (int)$perf_staff_overrides[$staff->id]['attendance'] : $perf_attendance_weight;
                                            $sTask = $hasCustom ? (int)$perf_staff_overrides[$staff->id]['tasks'] : $perf_tasks_weight;
                                            $sMerch = $hasCustom ? (int)$perf_staff_overrides[$staff->id]['merchant'] : $perf_merchant_target_weight;
                                            $sMonth = $hasCustom ? (int)$perf_staff_overrides[$staff->id]['monthly'] : $perf_monthly_target_weight;
                                            $sTotal = $sAtt + $sTask + $sMerch + $sMonth;
                                        ?>
                                        <tr>
                                            <td class="py-3 px-4">
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="<?php echo e($staff->avatar_url); ?>" alt="<?php echo e($staff->name); ?>" class="rounded-circle border shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">
                                                    <div>
                                                        <div class="fw-bold text-dark fs-6"><?php echo e($staff->name); ?></div>
                                                        <div class="text-muted extra-small d-flex align-items-center gap-2">
                                                            <span class="badge bg-light text-secondary border"><?php echo e($staff->employee_code ?: 'EMP-'.substr($staff->id,0,4)); ?></span>
                                                            <span><?php echo e($staff->email); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3 px-3">
                                                <div class="fw-semibold text-dark small"><?php echo e($staff->department?->name ?? 'General Department'); ?></div>
                                                <div class="text-muted extra-small">
                                                    <i class="bi bi-person-badge me-1"></i><?php echo e($staff->designation?->name ?? 'Employee'); ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($staff->branch): ?>
                                                        <span class="text-secondary ms-1">&bull; <i class="bi bi-geo-alt me-0.5"></i><?php echo e($staff->branch->name); ?></span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasCustom): ?>
                                                    <span class="badge bg-warning-subtle text-dark border border-warning rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.78rem;">
                                                        <i class="bi bi-sliders text-warning me-1"></i>Custom (<?php echo e($sTotal); ?> pts)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 fw-semibold" style="font-size: 0.78rem;">
                                                        <i class="bi bi-building me-1"></i>Company Default
                                                    </span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="py-3 px-3 text-center">
                                                <div class="d-flex justify-content-center align-items-center gap-1.5 flex-wrap">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 py-1" title="Attendance" style="font-size:0.75rem;">
                                                        Att: <strong><?php echo e($sAtt); ?></strong>
                                                    </span>
                                                    <span class="badge rounded-pill px-2 py-1" title="Tasks" style="background:rgba(139,92,246,0.1); color:#8b5cf6; border:1px solid rgba(139,92,246,0.2); font-size:0.75rem;">
                                                        Task: <strong><?php echo e($sTask); ?></strong>
                                                    </span>
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill px-2 py-1" title="Merchant Target" style="font-size:0.75rem;">
                                                        Merch: <strong><?php echo e($sMerch); ?></strong>
                                                    </span>
                                                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning-subtle rounded-pill px-2 py-1" title="Monthly Target" style="font-size:0.75rem;">
                                                        Month: <strong><?php echo e($sMonth); ?></strong>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="py-3 px-4 text-end">
                                                <div class="d-flex justify-content-end align-items-center gap-2">
                                                    <button type="button" wire:click="openEmployeePerfModal('<?php echo e($staff->id); ?>')" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold shadow-sm" style="font-size:0.82rem;">
                                                        <i class="bi bi-sliders me-1"></i> Configure
                                                    </button>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasCustom): ?>
                                                        <button type="button" wire:click="resetEmployeeToDefault('<?php echo e($staff->id); ?>')" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1.5" title="Reset to Company Default" onclick="confirm('Reset <?php echo e(addslashes($staff->name)); ?> to company default weights?') || event.stopImmediatePropagation()">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </button>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">
                                                <i class="bi bi-people fs-1 text-secondary d-block mb-2"></i>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($staffSearch) || !empty($staffDeptFilter)): ?>
                                                    No staff members found matching your search or filters.
                                                <?php else: ?>
                                                    No staff members registered in your company yet.
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Modal: Configure Individual Staff Score Weights -->
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show_employee_modal): ?>
        <?php
            $empTotalPoints = (int)$employee_perf_attendance_weight + (int)$employee_perf_tasks_weight + (int)$employee_perf_merchant_target_weight + (int)$employee_perf_monthly_target_weight;
            $isEmpBalanced = ($empTotalPoints === 100);
        ?>
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 1055;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                    <!-- Modal Header -->
                    <div class="modal-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?php echo e($editing_employee_avatar ?: 'https://ui-avatars.com/api/?name='.urlencode($editing_employee_name).'&background=2563EB&color=fff'); ?>" alt="<?php echo e($editing_employee_name); ?>" class="rounded-circle border shadow-sm" style="width: 46px; height: 46px; object-fit: cover;">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="modal-title fw-bold text-dark mb-0"><?php echo e($editing_employee_name); ?></h5>
                                    <span class="badge bg-light text-secondary border rounded-pill small"><?php echo e($editing_employee_code); ?></span>
                                </div>
                                <div class="text-muted extra-small">
                                    <span><?php echo e($editing_employee_dept); ?></span> &bull; <span><?php echo e($editing_employee_designation); ?></span>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isEmpBalanced): ?>
                                    <span class="badge bg-success bg-gradient rounded-pill px-3 py-2 shadow-sm fs-7">
                                        <i class="bi bi-check-circle-fill me-1"></i>Total: 100 / 100 Pts (Balanced)
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-gradient rounded-pill px-3 py-2 shadow-sm fs-7">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Total: <?php echo e($empTotalPoints); ?> / 100 Pts (<?php echo e($empTotalPoints > 100 ? '+' . ($empTotalPoints - 100) . ' Over' : '-' . (100 - $empTotalPoints) . ' Remaining'); ?>)
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['employee_perf_sum'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3 border-0" role="alert">
                                <i class="bi bi-x-octagon-fill me-2"></i><?php echo e($message); ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <!-- Visualizer Bar -->
                        <div class="mb-3 p-3 rounded-4 bg-white border">
                            <div class="progress rounded-pill" style="height: 14px; background-color: #e2e8f0;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo e(min(100, $employee_perf_attendance_weight)); ?>%;" title="Attendance">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee_perf_attendance_weight >= 10): ?> <?php echo e($employee_perf_attendance_weight); ?>% <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="progress-bar" role="progressbar" style="width: <?php echo e(min(100, $employee_perf_tasks_weight)); ?>%; background-color: #8b5cf6;" title="Tasks">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee_perf_tasks_weight >= 10): ?> <?php echo e($employee_perf_tasks_weight); ?>% <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="progress-bar bg-info" role="progressbar" style="width: <?php echo e(min(100, $employee_perf_merchant_target_weight)); ?>%;" title="Merchant">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee_perf_merchant_target_weight >= 10): ?> <?php echo e($employee_perf_merchant_target_weight); ?>% <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo e(min(100, $employee_perf_monthly_target_weight)); ?>%;" title="Monthly">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employee_perf_monthly_target_weight >= 10): ?> <?php echo e($employee_perf_monthly_target_weight); ?>% <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                                        <span class="badge bg-primary rounded-pill px-2 py-1 fs-7 fw-bold"><?php echo e($employee_perf_attendance_weight); ?> pts</span>
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
                                        <span class="badge rounded-pill px-2 py-1 fs-7 fw-bold" style="background-color: #8b5cf6; color: white;"><?php echo e($employee_perf_tasks_weight); ?> pts</span>
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
                                        <span class="badge bg-info text-dark rounded-pill px-2 py-1 fs-7 fw-bold"><?php echo e($employee_perf_merchant_target_weight); ?> pts</span>
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
                                        <span class="badge bg-warning text-dark rounded-pill px-2 py-1 fs-7 fw-bold"><?php echo e($employee_perf_monthly_target_weight); ?> pts</span>
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
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($perf_staff_overrides[$editing_employee_id])): ?>
                                <button type="button" wire:click="resetEmployeeToDefault('<?php echo e($editing_employee_id); ?>')" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-semibold" wire:click="closeEmployeePerfModal">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Default
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button type="button" wire:click="saveEmployeePerformanceSettings" wire:loading.attr="disabled" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-sm" <?php if(!$isEmpBalanced): ?> disabled <?php endif; ?>>
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
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/settings.blade.php ENDPATH**/ ?>