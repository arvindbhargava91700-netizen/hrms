<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0">
            <h5 class="mb-0">Salary Management</h5>
        </div>
        
        <div class="card-body pb-0">
            <?php echo $__env->make('partials.hrms-filters', ['viewAnyPermission' => 'salary_viewAny'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('message')): ?>
                <div class="alert alert-success m-3 d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div><?php echo e(session('message')); ?></div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th class="text-end">Basic Salary</th>
                        <th class="text-end">Gross Salary</th>
                        <th class="text-end">Net Salary</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $structure = $structures->get($employee->id);
                                $basic = (float)($structure ? $structure->basic_salary : ($employee->basic_salary ?: 0));
                                $allowancesList = ($structure && is_array($structure->allowances)) ? $structure->allowances : [];
                                $totalAllowances = array_sum(array_map('floatval', $allowancesList));
                                $perfIncentive = (float)($allowancesList['performance_incentive'] ?? 0);
                                $salesIncentive = (float)($allowancesList['sales_incentive'] ?? 0);
                                $totalIncentives = $perfIncentive + $salesIncentive;
                                $gross = $structure ? (float)$structure->gross_salary : ($basic + $totalAllowances);
                                
                                $deductionsList = ($structure && is_array($structure->deductions)) ? $structure->deductions : [];
                                $totalDeductions = array_sum(array_map('floatval', $deductionsList));
                                $net = $structure ? (float)$structure->net_salary : ($gross - $totalDeductions);
                            ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                            <?php echo e(substr($employee->name, 0, 1)); ?>

                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo e($employee->name); ?></div>
                                            <div class="small text-muted"><?php echo e($employee->email); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted">
                                    <?php echo e($employee->department ? $employee->department->name : 'N/A'); ?>

                                </td>
                                <td class="px-4 py-3 text-end fw-bold text-dark fs-6">
                                    ₹<?php echo e(number_format($basic, 2)); ?>

                                </td>
                                <td class="px-4 py-3 text-end">
                                    <?php
                                        $perfIncentive = (float)($allowancesList['performance_incentive'] ?? 0);
                                        $salesIncentive = (float)($allowancesList['sales_incentive'] ?? 0);
                                        $totalIncentives = $perfIncentive + $salesIncentive;
                                        $incentiveAmount = $totalIncentives > 0 ? $totalIncentives : $totalAllowances;
                                    ?>
                                    <div class="fw-bold text-success fs-6">
                                        +₹<?php echo e(number_format($incentiveAmount, 2)); ?>

                                    </div>
                                    <div class="text-muted extra-small" style="font-size: 0.72rem; line-height: 1.25;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($perfIncentive > 0 && $salesIncentive > 0): ?>
                                            ₹<?php echo e(number_format($perfIncentive, 0)); ?> Performance + ₹<?php echo e(number_format($salesIncentive, 0)); ?> Incentive
                                        <?php elseif($perfIncentive > 0): ?>
                                            ₹<?php echo e(number_format($perfIncentive, 0)); ?> Performance Incentive
                                        <?php elseif($salesIncentive > 0): ?>
                                            ₹<?php echo e(number_format($salesIncentive, 0)); ?> Sales Incentive
                                        <?php elseif($totalAllowances > 0): ?>
                                            ₹<?php echo e(number_format($totalAllowances, 0)); ?> Performance & Allowance
                                        <?php else: ?>
                                            ₹0 Performance + ₹0 Incentive
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-end fw-bold text-primary fs-6">
                                    ₹<?php echo e(number_format($net, 2)); ?>

                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="d-flex justify-content-end align-items-center">
                                        <button wire:click="viewSalary('<?php echo e($employee->id); ?>')" class="btn btn-sm btn-info px-3 shadow-sm text-white me-2">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('salary_update')): ?>
                                        <button wire:click="editSalary('<?php echo e($employee->id); ?>')" class="btn btn-sm btn-primary px-3 shadow-sm">
                                            <i class="bi bi-pencil-square"></i> Manage Structure
                                        </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-wallet2 fs-2"></i></div>
                                    <p class="mb-0 fw-bold">No employees found.</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($employees->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($employees->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Edit Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showModal): ?>
    <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold text-dark mb-0">
                        <i class="bi bi-cash-stack me-2 text-primary"></i> Salary Structure: <span class="text-primary"><?php echo e($employeeName); ?></span>
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeEdit"></button>
                </div>
                
                <div class="modal-body p-4 bg-light">
                    
                    <!-- Live Salary Calculation Summary Banner -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #f0fdf4 0%, #eff6ff 100%); border: 1.5px solid #86efac !important;">
                        <div class="card-body p-3.5">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 rounded-pill px-3 py-1.5 fw-bold" style="font-size: 0.78rem;">
                                    <i class="bi bi-calculator me-1.5"></i> Live Gross & Net Salary Calculation Preview
                                </span>
                                <div class="small fw-bold text-dark">
                                    Calculation: <span class="text-secondary">Base (₹<?php echo e(number_format((float)$basic_salary, 0)); ?>)</span> + <span class="text-success">Allowances (₹<?php echo e(number_format($this->totalAllowances, 0)); ?>)</span> = <span class="text-success fw-bolder">Gross (₹<?php echo e(number_format($this->grossSalary, 0)); ?>)</span>
                                </div>
                            </div>

                            <div class="row g-3 text-center align-items-stretch mt-1">
                                <div class="col-md-3 col-6">
                                    <div class="bg-white rounded-3 p-2.5 shadow-sm h-100 border">
                                        <div class="text-muted extra-small fw-bold text-uppercase" style="font-size: 0.7rem;">Basic Salary</div>
                                        <div class="fw-bold fs-5 text-dark mt-1">₹<?php echo e(number_format((float)$basic_salary, 2)); ?></div>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary extra-small rounded-pill px-2 py-0.5 mt-1">Fixed Base Wage</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="bg-white rounded-3 p-2.5 shadow-sm h-100 border">
                                        <div class="text-muted extra-small fw-bold text-uppercase" style="font-size: 0.7rem;">Total Allowances & Incentives</div>
                                        <div class="fw-bold fs-5 text-success mt-1">+₹<?php echo e(number_format($this->totalAllowances, 2)); ?></div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->totalIncentives > 0): ?>
                                            <span class="badge bg-info bg-opacity-10 text-info extra-small rounded-pill px-2 py-0.5 mt-1" title="Performance & Sales incentives">
                                                ₹<?php echo e(number_format($this->totalIncentives, 0)); ?> Incentives
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success extra-small rounded-pill px-2 py-0.5 mt-1">Allowances</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="bg-white rounded-3 p-2.5 shadow-sm h-100 border" style="border: 1.5px solid #86efac !important; background: #f0fdf4 !important;">
                                        <div class="text-success extra-small fw-bold text-uppercase" style="font-size: 0.7rem;">Calculated Gross Salary</div>
                                        <div class="fw-bold fs-5 text-success mt-1">₹<?php echo e(number_format($this->grossSalary, 2)); ?></div>
                                        <div class="text-muted extra-small" style="font-size: 0.68rem;">
                                            (₹<?php echo e(number_format((float)$basic_salary, 0)); ?> + ₹<?php echo e(number_format($this->totalAllowances, 0)); ?>)
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="bg-white rounded-3 p-2.5 shadow-sm h-100 border" style="border: 1.5px solid #93c5fd !important; background: #eff6ff !important;">
                                        <div class="text-primary extra-small fw-bold text-uppercase" style="font-size: 0.7rem;">Net Take-Home</div>
                                        <div class="fw-bold fs-5 text-primary mt-1">₹<?php echo e(number_format($this->netSalary, 2)); ?></div>
                                        <div class="text-muted extra-small" style="font-size: 0.68rem;">
                                            (-₹<?php echo e(number_format($this->totalDeductions, 0)); ?> Deductions)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->totalAllowances > 0): ?>
                            <div class="mt-2.5 p-2 bg-white bg-opacity-80 rounded-3 border border-success border-opacity-25 small d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-1.5 text-muted extra-small">
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    <span><strong>Gross Salary Formula:</strong> Basic (₹<?php echo e(number_format((float)$basic_salary, 2)); ?>) + Allowances (₹<?php echo e(number_format($this->totalAllowances, 2)); ?>) = <strong class="text-success">₹<?php echo e(number_format($this->grossSalary, 2)); ?></strong></span>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->totalIncentives > 0): ?>
                                    <span class="badge bg-warning bg-opacity-20 text-dark border border-warning border-opacity-30 rounded-pill px-2.5 py-1 extra-small fw-semibold">
                                        <i class="bi bi-trophy-fill text-warning me-1"></i> ₹<?php echo e(number_format($this->totalIncentives, 2)); ?> Active Performance/Incentive
                                    </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Basic Salary Section -->
                        <div class="col-12">
                            <div class="card shadow-sm border-0 rounded-4">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-piggy-bank text-warning me-2"></i> Core Compensation & Commissions</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold">SALARY TYPE</label>
                                            <select class="form-select form-select-lg fw-bold" wire:model.live="salary_type" <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                                <option value="base_plus_target">Base Salary + Target Commission</option>
                                                <option value="commission_only">Commission Only (Minimum Target Base)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label text-muted small fw-bold">BASIC SALARY (₹) <span class="text-danger">*</span></label>
                                            <input type="number" wire:model.live="basic_salary" class="form-control form-control-lg fw-bold" step="0.01" placeholder="e.g. 10000" <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['basic_salary'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($salary_type === 'commission_only'): ?>
                                                <small class="text-muted" style="font-size:0.7rem;">(Optional basic payout if min target not met)</small>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">MONTHLY BUSINESS TARGET (₹)</label>
                                            <input type="number" wire:model.live="monthly_target" class="form-control" step="0.01" <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                            <small class="text-muted" style="font-size:0.7rem;">Target to achieve before commissions apply</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">MERCHANT TARGET</label>
                                            <input type="number" wire:model.live="merchant_target" class="form-control" step="0.01" <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                            <small class="text-muted" style="font-size:0.7rem;">Merchant target count/volume</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">COMMISSION (%)</label>
                                            <input type="number" wire:model.live="commission_percent" class="form-control" step="0.01" <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                            <small class="text-muted" style="font-size:0.7rem;">Earned on business *above* target</small>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label text-muted small fw-bold">RECOVERY BUSINESS (%)</label>
                                            <input type="number" wire:model.live="recovery_percent" class="form-control" step="0.01" <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                            <small class="text-muted" style="font-size:0.7rem;">Commission on collected old payments</small>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label text-muted small fw-bold">COMMISSION HIERARCHY LEVEL (Optional)</label>
                                            <select class="form-select" wire:model="commission_level_id" <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                                <option value="">No Override Level (Direct Commission Only)</option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $commissionLevels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $level): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <option value="<?php echo e($level->id); ?>"><?php echo e($level->level_name); ?> (<?php echo e($level->commission_percent); ?>%) - Order #<?php echo e($level->level_order); ?></option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            </select>
                                            <small class="text-muted" style="font-size:0.7rem;">Assign a level to enable hierarchical commissions for this employee.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Allowances Section -->
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 rounded-4 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0 text-success"><i class="bi bi-plus-circle-fill me-2"></i> Allowances (Earnings)</h6>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2.5 py-1 extra-small fw-bold">
                                            Total: +₹<?php echo e(number_format($this->totalAllowances, 2)); ?>

                                        </span>
                                    </div>
                                    
                                    <div class="row g-2.5">
                                        <?php
                                            $allowanceLabels = [
                                                'hra' => 'House Rent Allowance (HRA)',
                                                'da' => 'Dearness Allowance (DA)',
                                                'conveyance' => 'Conveyance Allowance',
                                                'medical' => 'Medical Allowance',
                                                'special' => 'Special Allowance',
                                                'travel' => 'Travel Allowance',
                                                'internet' => 'Internet/Mobile Allowance',
                                                'food' => 'Food Allowance',
                                                'performance_incentive' => 'Performance Incentive (Target Bonus)',
                                                'sales_incentive' => 'Sales Incentive (Revenue Reward)',
                                                'bonus' => 'Bonus',
                                                'overtime' => 'Overtime Allowance',
                                                'shift' => 'Shift Allowance',
                                                'other' => 'Other Allowances',
                                            ];
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $allowanceLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="col-12">
                                            <div class="p-2 rounded-3 <?php echo e(in_array($key, ['performance_incentive', 'sales_incentive']) ? 'bg-warning bg-opacity-10 border border-warning border-opacity-25' : 'bg-light border-0'); ?> d-flex align-items-center justify-content-between">
                                                <div class="text-secondary small fw-medium <?php echo e(in_array($key, ['performance_incentive', 'sales_incentive']) ? 'text-dark fw-bold' : ''); ?>">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($key, ['performance_incentive', 'sales_incentive'])): ?>
                                                        <i class="bi bi-star-fill text-warning me-1"></i>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    <?php echo e($label); ?>

                                                </div>
                                                <div class="input-group input-group-sm" style="width: 140px;">
                                                    <span class="input-group-text bg-white">₹</span>
                                                    <input type="number" wire:model.live="allowances.<?php echo e($key); ?>" class="form-control text-end fw-semibold" step="0.01" placeholder="0.00" <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deductions Section -->
                        <div class="col-md-6">
                            <div class="card shadow-sm border-0 rounded-4 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-dash-circle-fill me-2"></i> Deductions</h6>
                                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2.5 py-1 extra-small fw-bold">
                                            Total: -₹<?php echo e(number_format($this->totalDeductions, 2)); ?>

                                        </span>
                                    </div>
                                    
                                    <div class="row g-2.5">
                                        <?php
                                            $deductionLabels = [
                                                'pf' => 'Employee PF',
                                                'esi' => 'Employee ESI',
                                                'pt' => 'Professional Tax (PT)',
                                                'tds' => 'TDS',
                                                'lwf' => 'Labour Welfare Fund (LWF)',
                                                'notice_period' => 'Notice Period Recovery',
                                                'other' => 'Other Deductions',
                                            ];
                                        ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $deductionLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="col-12">
                                            <div class="p-2 rounded-3 bg-light d-flex align-items-center justify-content-between">
                                                <div class="text-secondary small fw-medium"><?php echo e($label); ?></div>
                                                <div class="input-group input-group-sm" style="width: 140px;">
                                                    <span class="input-group-text bg-white">₹</span>
                                                    <input type="number" wire:model.live="deductions.<?php echo e($key); ?>" class="form-control text-end fw-semibold" step="0.01" placeholder="0.00" <?php echo e($viewMode ? 'disabled' : ''); ?>>
                                                </div>
                                            </div>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer border-top py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        <strong>Gross Total:</strong> ₹<?php echo e(number_format($this->grossSalary, 2)); ?> &bull; <strong>Net Take-Home:</strong> ₹<?php echo e(number_format($this->netSalary, 2)); ?>

                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" wire:click="closeEdit"><?php echo e($viewMode ? 'Close' : 'Cancel'); ?></button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$viewMode): ?>
                        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" wire:click="saveSalary">Save Salary Structure</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/payroll/salary-management.blade.php ENDPATH**/ ?>