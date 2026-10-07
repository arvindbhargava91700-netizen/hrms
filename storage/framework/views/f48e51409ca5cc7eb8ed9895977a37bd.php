<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center border-0 pb-0">
            <h5 class="mb-0">Leave Approvals</h5>
        </div>
        
        <div class="card-body pb-0">
            <?php echo $__env->make('partials.hrms-filters', ['viewAnyPermission' => 'leave_viewAny'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
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
                        <th>Date Applied</th>
                        <th>Leave Period</th>
                        <th>Type & Duration</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $start = \Carbon\Carbon::parse($request->start_date);
                                $end = \Carbon\Carbon::parse($request->end_date);
                                $days = $start->diffInDays($end) + 1;
                            ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; font-weight: bold;">
                                            <?php echo e(substr($request->employee->name, 0, 1)); ?>

                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?php echo e($request->employee->name); ?></div>
                                            <div class="small text-muted"><?php echo e($request->employee->email); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted">
                                    <?php echo e($request->created_at->format('M d, Y')); ?>

                                </td>
                                <td class="px-4 py-3 fw-bold">
                                    <?php echo e($start->format('M d, Y')); ?> - <?php echo e($end->format('M d, Y')); ?>

                                </td>
                                <td class="px-4 py-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($request->type == 'casual'): ?>
                                        <span class="badge bg-info text-dark d-block mb-1">Casual (CL)</span>
                                    <?php elseif($request->type == 'sick'): ?>
                                        <span class="badge bg-danger d-block mb-1">Sick (SL)</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary d-block mb-1">Unpaid (LWP)</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <span class="small text-muted"><?php echo e($days); ?> Day(s)</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="d-inline-block text-truncate" style="max-width: 200px;" title="<?php echo e($request->reason); ?>">
                                        <?php echo e($request->reason); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($request->status == 'pending'): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Pending</span>
                                    <?php elseif($request->status == 'approved'): ?>
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> Rejected</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($request->status == 'pending'): ?>
                                        <div class="d-flex gap-2">
                                             <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('leave_approved')): ?>
                                            <button wire:click="updateStatus(<?php echo e($request->id); ?>, 'approved')" class="btn btn-sm btn-success px-3">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                             <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('leave_rejected')): ?>
                                            <button wire:click="updateStatus(<?php echo e($request->id); ?>, 'rejected')" class="btn btn-sm btn-outline-danger px-3">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Processed</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-inbox fs-2"></i></div>
                                    <p class="mb-0 fw-bold">No leave requests found.</p>
                                    <p class="small">There are currently no leave requests from any employees.</p>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($requests->hasPages()): ?>
            <div class="card-footer">
                <?php echo e($requests->links()); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/leaves/leave-approvals.blade.php ENDPATH**/ ?>