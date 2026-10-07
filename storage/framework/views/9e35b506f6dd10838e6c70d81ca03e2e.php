<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">My Payslips</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Month & Year</th>
                        <th class="text-end">Basic Salary</th>
                        <th class="text-end">Allowances</th>
                        <th class="text-end">Deductions</th>
                        <th class="text-end">Bonuses</th>
                        <th class="text-end">Net Pay</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $payrolls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payroll): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-dark fs-6">
                                        <i class="bi bi-calendar-event text-primary me-2"></i>
                                        <?php echo e(date("F", mktime(0, 0, 0, $payroll->month, 1))); ?> <?php echo e($payroll->year); ?>

                                    </div>
                                    <div class="small text-muted mt-1">Generated: <?php echo e($payroll->created_at->format('M d, Y')); ?></div>
                                </td>
                                <td class="px-4 py-3 text-end text-muted">
                                    ₹<?php echo e(number_format($payroll->basic_salary, 2)); ?>

                                </td>
                                <td class="px-4 py-3 text-end text-success">
                                    <?php
                                        $totalAllowances = 0;
                                        if (is_array($payroll->allowances_breakdown)) {
                                            $totalAllowances = array_sum(array_map('floatval', $payroll->allowances_breakdown));
                                        }
                                    ?>
                                    ₹<?php echo e(number_format($totalAllowances, 2)); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($payroll->allowances_breakdown)): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $payroll->allowances_breakdown; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($val > 0): ?>
                                                <div class="small mt-1" style="font-size: 10px; color: #198754;"><i class="bi bi-info-circle me-1"></i><?php echo e(ucwords(str_replace('_', ' ', $key))); ?>: ₹<?php echo e(number_format($val, 2)); ?></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-end text-danger">
                                    -₹<?php echo e(number_format($payroll->deductions, 2)); ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($payroll->deductions_breakdown['salary_advance']) && $payroll->deductions_breakdown['salary_advance'] > 0): ?>
                                        <div class="small mt-1" style="font-size: 10px; color: #dc3545;"><i class="bi bi-info-circle me-1"></i>Advance: ₹<?php echo e(number_format($payroll->deductions_breakdown['salary_advance'], 2)); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-end text-success">
                                    +₹<?php echo e(number_format($payroll->bonuses, 2)); ?>

                                </td>
                                <td class="px-4 py-3 text-end fw-bold text-dark fs-6 bg-light bg-opacity-50 border-start border-end">
                                    ₹<?php echo e(number_format($payroll->net_pay, 2)); ?>

                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payroll->status == 'paid'): ?>
                                        <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> Paid</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-clock me-1"></i> Pending</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payroll->status == 'paid'): ?>
                                        <a href="<?php echo e(route('partner.hrms.payslip.download', $payroll->id)); ?>" target="_blank" class="btn btn-sm btn-outline-primary px-3 shadow-sm">
                                            <i class="bi bi-download me-1"></i> Download
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Available once paid</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-receipt fs-2"></i></div>
                                    <p class="mb-0 fw-bold">No payslips found.</p>
                                    <p class="small">Your salary slips will appear here once they are generated by HR.</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payrolls->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($payrolls->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/payroll/my-payroll.blade.php ENDPATH**/ ?>