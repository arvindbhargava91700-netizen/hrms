<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Lead Orders Pipeline</h4>
            <p class="text-muted mb-0">Dynamic Approval Process</p>
        </div>
        <div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('leadorder_create')): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createOrderModal">
                <i class="bi bi-plus-lg"></i> Create Order
            </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="alert alert-success border-0 shadow-sm">
            <i class="bi bi-check-circle me-2"></i> <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm">
            <i class="bi bi-exclamation-triangle me-2"></i> <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- Pipeline Tabs -->
    <ul class="nav nav-tabs nav-tabs-custom mb-4 overflow-auto flex-nowrap" style="white-space: nowrap;">
        <li class="nav-item">
            <a class="nav-link text-muted <?php echo e($currentTab === 'all' ? 'active border-primary text-primary fw-bold' : ''); ?>" 
               href="#" wire:click.prevent="setTab('all')">
               All Orders
            </a>
        </li>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($myStageIds) > 0): ?>
        <li class="nav-item">
            <a class="nav-link text-muted <?php echo e($currentTab === 'action_required' ? 'active border-primary text-primary fw-bold' : ''); ?>" 
               href="#" wire:click.prevent="setTab('action_required')">
                <i class="bi bi-exclamation-circle text-warning me-1"></i> Requires My Action
            </a>
        </li>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pipelineStages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <li class="nav-item">
            <a class="nav-link text-muted <?php echo e($currentTab === $stage->id ? 'active border-primary text-primary fw-bold' : ''); ?>" 
               href="#" wire:click.prevent="setTab('<?php echo e($stage->id); ?>')"><?php echo e($stage->name); ?></a>
        </li>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        
        <li class="nav-item">
            <a class="nav-link text-muted <?php echo e($currentTab === 'completed' ? 'active border-success text-success fw-bold' : ''); ?>" 
               href="#" wire:click.prevent="setTab('completed')">Completed</a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-muted <?php echo e($currentTab === 'rejected' ? 'active border-danger text-danger fw-bold' : ''); ?>" 
               href="#" wire:click.prevent="setTab('rejected')">Rejected</a>
        </li>
    </ul>

    <style>
        .nav-tabs-custom .nav-link {
            border-top: none;
            border-left: none;
            border-right: none;
            border-bottom: 2px solid transparent;
            padding-bottom: 0.75rem;
            margin-bottom: -1px;
            background: transparent;
        }
        .nav-tabs-custom .nav-link:hover:not(.active) {
            border-bottom-color: var(--border-color);
            color: var(--text-primary) !important;
        }
    </style>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Order / Lead</th>
                        <th>Financials</th>
                        <th>Current Stage</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark">Order #<?php echo e(str_pad($order->id, 5, '0', STR_PAD_LEFT)); ?></div>
                            <div class="text-muted small"><i class="bi bi-person me-1"></i> <?php echo e($order->lead?->customer_name ?? 'Unknown Lead'); ?></div>
                            <div class="text-muted small mt-1">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary rounded-pill px-2">
                                    <?php echo e($order->items->count()); ?> Items
                                </span>
                            </div>
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-dark">Total: ₹<?php echo e(number_format($order->total_amount, 2)); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->gst_type === 'include' && (float)$order->gst_percent > 0): ?>
                                <div>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                        <i class="bi bi-receipt me-1"></i><?php echo e(number_format($order->gst_percent, 1)); ?>% GST (+₹<?php echo e(number_format($order->gst_amount, 2)); ?>)
                                    </span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="text-success small fw-medium">Paid: ₹<?php echo e(number_format($order->paid_amount, 2)); ?></div>
                            <div class="text-danger small fw-medium">Bal: ₹<?php echo e(number_format($order->remaining_balance, 2)); ?></div>
                        </td>
                        <td class="py-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->approval_status === 'completed'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3">
                                    COMPLETED
                                </span>
                            <?php elseif($order->approval_status === 'rejected'): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3">
                                    REJECTED
                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->currentStage): ?>
                                    <div class="small text-muted mt-1">at <?php echo e($order->currentStage->name); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3">
                                    <?php echo e(strtoupper($order->currentStage ? $order->currentStage->name : 'PENDING')); ?>

                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->currentStage): ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->currentStage->assignedUser): ?>
                                        <div class="extra-small text-muted mt-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-person-check text-primary me-1"></i>Assigned: <strong class="text-dark"><?php echo e($order->currentStage->assignedUser->name); ?></strong>
                                        </div>
                                    <?php elseif($order->currentStage->department): ?>
                                        <div class="extra-small text-muted mt-1" style="font-size: 0.75rem;">
                                            <i class="bi bi-diagram-3 text-secondary me-1"></i><?php echo e($order->currentStage->department->name); ?>

                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="py-3 pe-4 text-end">
                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2" wire:click="showOrder('<?php echo e($order->id); ?>')">View</button>
                            
                            <?php $actionStageId = $this->actionStageFor($order); ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($actionStageId): ?>
                                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 me-2" wire:click="addComment('<?php echo e($order->id); ?>')">
                                    <i class="bi bi-chat-left-text me-1"></i> Comment
                                </button>
                                <div class="btn-group">
                                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('leadorder_approve') || $actionStageId): ?>
                                    <button class="btn btn-sm btn-outline-success rounded-pill px-3 me-2" wire:click="approveStage('<?php echo e($order->id); ?>', '<?php echo e($actionStageId); ?>')"
                                            wire:confirm="Are you sure you want to approve this order?">
                                        <i class="bi bi-check-circle me-1"></i> Approve
                                    </button>
                                     <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                     <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('leadorder_reject')): ?>
                                    <button class="btn btn-sm btn-outline-danger rounded-pill px-3" wire:click="initiateRejection('<?php echo e($order->id); ?>', '<?php echo e($actionStageId); ?>')">
                                        <i class="bi bi-x-circle me-1"></i> Reject
                                    </button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="4" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-inbox fs-1 opacity-50"></i></div>
                            <div class="text-muted">No orders found in this stage.</div>
                        </td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($orders->hasPages()): ?>
        <div class="card-footer border-top bg-transparent p-4">
            <?php echo e($orders->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="rejectModalLabel">Reject Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" rows="3" wire:model="rejectionNotes" placeholder="Enter rejection reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-4" wire:click="confirmRejection">Confirm Rejection</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Order Modal -->
    <div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white">
                    <h5 class="modal-title fw-bold text-dark" id="createOrderModalLabel">
                        <i class="bi bi-plus-circle-fill text-primary me-2"></i>Create New Lead Order
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Select Lead (Won Status) <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model="newOrderLeadId">
                                <option value="">-- Choose Lead --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $availableLeads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($lead->id); ?>"><?php echo e($lead->customer_name); ?> (<?php echo e($lead->customer_mobile ?: 'No Mobile'); ?>)</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newOrderLeadId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Select Product <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model.live="newOrderProductId">
                                <option value="">-- Choose Product --</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $availableProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?> (Base: ₹<?php echo e(number_format($product->amount, 2)); ?>)</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newOrderProductId'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Product Amount and Fixed GST Section -->
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Product Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">₹</span>
                                <input type="number" step="0.01" min="0" class="form-control fw-bold" wire:model.live="newOrderAmount" placeholder="0.00">
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newOrderAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">GST Option <i class="bi bi-lock-fill text-muted ms-1" title="Fixed from product"></i></label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($newOrderGstType === 'include'): ?>
                                <div class="form-control bg-light d-flex align-items-center justify-content-between text-success fw-bold" style="cursor: not-allowed;">
                                    <span><i class="bi bi-check-circle-fill me-1.5"></i>Include (Add GST)</span>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2" style="font-size:0.65rem;">Fixed</span>
                                </div>
                            <?php else: ?>
                                <div class="form-control bg-light d-flex align-items-center justify-content-between text-muted" style="cursor: not-allowed;">
                                    <span><i class="bi bi-dash-circle me-1.5"></i>Not Include (0%)</span>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2" style="font-size:0.65rem;">Fixed</span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">GST Percentage (%) <i class="bi bi-lock-fill text-muted ms-1" title="Fixed from product"></i></label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($newOrderGstType === 'include'): ?>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light text-success fw-bold" value="<?php echo e(number_format($newOrderGstPercent, 1)); ?>" readonly disabled style="cursor: not-allowed;">
                                    <span class="input-group-text bg-light fw-bold text-success">%</span>
                                </div>
                            <?php else: ?>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light text-muted" value="0.0" readonly disabled style="cursor: not-allowed;">
                                    <span class="input-group-text bg-light text-muted">%</span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control fw-bold" wire:model.live="newOrderQuantity" min="1">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newOrderQuantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Discount (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">₹</span>
                                <input type="number" step="0.01" class="form-control text-danger fw-semibold" wire:model.live="newOrderDiscount" min="0" placeholder="0.00">
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newOrderDiscount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">Amount Paid Now (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">₹</span>
                                <input type="number" step="0.01" class="form-control text-success fw-bold" wire:model.live="newOrderPaidAmount" min="0" placeholder="0.00">
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['newOrderPaidAmount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <!-- Summary Calculation -->
                        <div class="col-12 mt-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <h6 class="fw-bold mb-3 text-dark d-flex align-items-center">
                                    <i class="bi bi-calculator text-primary me-2"></i>Order & Tax Calculation Summary
                                </h6>
                                <?php
                                    $totals = $this->calculatedTotals;
                                ?>
                                
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Product Base Amount (<?php echo e($newOrderQuantity); ?> × ₹<?php echo e(number_format($totals['base_unit_price'], 2)); ?>):</span>
                                    <span class="fw-semibold text-dark">₹<?php echo e(number_format($totals['base_subtotal'], 2)); ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>GST Applied <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totals['is_gst_included']): ?> (<?php echo e(number_format($totals['gst_rate'], 1)); ?>%) <?php else: ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>:</span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totals['is_gst_included']): ?>
                                        <span class="text-success fw-bold">+₹<?php echo e(number_format($totals['gst_amount'], 2)); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">₹0.00 (Not Included)</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between text-dark small mb-1 pt-1 border-top">
                                    <span>Total (Base + GST):</span>
                                    <span class="fw-bold">₹<?php echo e(number_format($totals['subtotal'], 2)); ?></span>
                                </div>

                                <div class="d-flex justify-content-between text-muted small mb-1">
                                    <span>Discount:</span>
                                    <span class="text-danger fw-semibold">-₹<?php echo e(number_format($totals['discount'], 2)); ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between text-dark py-2 border-top border-bottom">
                                    <strong class="fs-6">Final Payable Amount:</strong>
                                    <strong class="fs-6 text-primary">₹<?php echo e(number_format($totals['final_amount'], 2)); ?></strong>
                                </div>
                                
                                <div class="d-flex justify-content-between text-muted small mt-2 mb-1">
                                    <span>Paid Amount Now:</span>
                                    <span class="text-success fw-bold">₹<?php echo e(number_format($totals['paid_amount'], 2)); ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between text-dark pt-1">
                                    <strong>Remaining Balance:</strong>
                                    <strong class="<?php echo e($totals['remaining_balance'] > 0 ? 'text-warning' : 'text-success'); ?>">₹<?php echo e(number_format($totals['remaining_balance'], 2)); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4 bg-white">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" wire:click="createOrder">Create Order</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Order Modal -->
    <div class="modal fade" id="viewOrderModal" tabindex="-1" aria-labelledby="viewOrderModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white">
                    <h5 class="modal-title fw-bold text-dark" id="viewOrderModalLabel">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>Order Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewOrder): ?>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Customer Name</h6>
                            <p class="fw-bold fs-5 mb-0"><?php echo e($viewOrder->lead->customer_name); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted small mb-1">Mobile Number</h6>
                            <p class="fw-bold fs-5 mb-0"><i class="bi bi-telephone text-primary me-2"></i><?php echo e($viewOrder->lead->customer_mobile ?: 'N/A'); ?></p>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <h6 class="text-muted small mb-1">Order ID</h6>
                            <p class="fw-bold mb-0">#<?php echo e(str_pad($viewOrder->id, 5, '0', STR_PAD_LEFT)); ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted small mb-1">Status / Stage</h6>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewOrder->approval_status === 'completed'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3">COMPLETED</span>
                            <?php elseif($viewOrder->approval_status === 'rejected'): ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-3">REJECTED</span>
                            <?php else: ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary rounded-pill px-3"><?php echo e(strtoupper($viewOrder->currentStage ? $viewOrder->currentStage->name : 'PENDING')); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewOrder->currentStage?->assignedUser): ?>
                                    <div class="extra-small text-muted mt-1" style="font-size: 0.75rem;"><i class="bi bi-person-check text-primary me-1"></i>Assigned: <strong class="text-dark"><?php echo e($viewOrder->currentStage->assignedUser->name); ?></strong></div>
                                <?php elseif($viewOrder->currentStage?->department): ?>
                                    <div class="extra-small text-muted mt-1" style="font-size: 0.75rem;"><i class="bi bi-diagram-3 text-secondary me-1"></i>Dept: <?php echo e($viewOrder->currentStage->department->name); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted small mb-1">Date</h6>
                            <p class="fw-bold mb-0"><?php echo e(\Carbon\Carbon::parse($viewOrder->created_at)->format('M d, Y')); ?></p>
                        </div>
                    </div>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($pipelineStages) > 0): ?>
                    <div class="mb-4 p-3 bg-light rounded-4 border">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark small text-uppercase mb-0"><i class="bi bi-diagram-3-fill text-primary me-2"></i>Approval Pipeline Stages</h6>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border rounded-pill px-2.5 py-1 extra-small" style="font-size:0.7rem;"><?php echo e(count($pipelineStages)); ?> Stages in Flow</span>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pipelineStages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sIndex => $pStage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $isCurrent = $viewOrder->current_stage_id === $pStage->id && $viewOrder->approval_status === 'pending';
                                    $isPassed = $viewOrder->approval_status === 'completed' || ($viewOrder->currentStage && $pStage->order_index < $viewOrder->currentStage->order_index);
                                ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-2 py-1.5 px-3 rounded-pill <?php echo e($isCurrent ? 'bg-primary text-white shadow-sm' : ($isPassed ? 'bg-success text-white' : 'bg-white border text-muted')); ?>" style="font-size:0.8rem;">
                                        <i class="bi <?php echo e($isPassed ? 'bi-check-circle-fill' : ($isCurrent ? 'bi-arrow-right-circle-fill' : 'bi-circle')); ?>"></i>
                                        <span class="fw-bold"><?php echo e($pStage->name); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pStage->assignedUser): ?>
                                            <span class="badge <?php echo e($isCurrent || $isPassed ? 'bg-light text-dark' : 'bg-light text-secondary border'); ?> rounded-pill" style="font-size:0.65rem;">
                                                <i class="bi bi-person me-0.5"></i><?php echo e($pStage->assignedUser->name); ?>

                                            </span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$loop->last): ?>
                                        <i class="bi bi-chevron-right text-muted small"></i>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewOrder->stageComments->isNotEmpty()): ?>
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark small text-uppercase mb-3"><i class="bi bi-chat-left-text text-primary me-2"></i>Stage Comments</h6>
                        <div class="d-flex flex-column gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $viewOrder->stageComments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stageComment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="d-flex justify-content-between gap-3 mb-1">
                                        <strong class="small text-dark"><?php echo e($stageComment->user->name ?? 'User'); ?></strong>
                                        <span class="text-muted small"><?php echo e($stageComment->created_at->format('d M Y, h:i A')); ?></span>
                                    </div>
                                    <div class="text-muted small mb-1"><?php echo e($stageComment->stage->name ?? 'Pipeline Stage'); ?></div>
                                    <div class="text-dark"><?php echo e($stageComment->comment); ?></div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <h6 class="fw-bold mb-3 border-bottom pb-2">Order Items</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Base Price</th>
                                    <th>GST Rate</th>
                                    <th>Unit Price (with GST)</th>
                                    <th>Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $viewOrder->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $itemBase = $item->base_price ?: $item->price;
                                    $itemGstRate = (float)($item->gst_percent ?: ($viewOrder->gst_percent ?: 0));
                                    $itemGstType = $item->gst_type ?: ($viewOrder->gst_type ?: 'notinclude');
                                ?>
                                <tr>
                                    <td class="fw-bold"><?php echo e($item->product->name ?? 'Unknown Product'); ?></td>
                                    <td>₹<?php echo e(number_format($itemBase, 2)); ?></td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($itemGstType === 'include' && $itemGstRate > 0): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2"><?php echo e(number_format($itemGstRate, 1)); ?>%</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted rounded-pill px-2">0%</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td>₹<?php echo e(number_format($item->price, 2)); ?></td>
                                    <td><?php echo e($item->quantity); ?></td>
                                    <td class="text-end fw-bold">₹<?php echo e(number_format($item->price * $item->quantity, 2)); ?></td>
                                </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row justify-content-end">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded border border-light-subtle">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((float)$viewOrder->base_amount > 0): ?>
                                    <div class="d-flex justify-content-between text-muted small mb-1">
                                        <span>Base Subtotal:</span>
                                        <span>₹<?php echo e(number_format($viewOrder->base_amount, 2)); ?></span>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($viewOrder->gst_type === 'include' && (float)$viewOrder->gst_percent > 0): ?>
                                        <div class="d-flex justify-content-between text-success small mb-1">
                                            <span>GST (<?php echo e(number_format($viewOrder->gst_percent, 1)); ?>%):</span>
                                            <span>+₹<?php echo e(number_format($viewOrder->gst_amount, 2)); ?></span>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>Total Amount:</span>
                                    <span>₹<?php echo e(number_format($viewOrder->total_amount, 2)); ?></span>
                                </div>
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>Discount:</span>
                                    <span class="text-danger">-₹<?php echo e(number_format($viewOrder->discount, 2)); ?></span>
                                </div>
                                <div class="d-flex justify-content-between text-dark mb-2 pb-2 border-bottom">
                                    <strong>Final Amount:</strong>
                                    <strong>₹<?php echo e(number_format($viewOrder->final_amount, 2)); ?></strong>
                                </div>
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span>Paid Amount:</span>
                                    <span class="text-success fw-bold">₹<?php echo e(number_format($viewOrder->paid_amount, 2)); ?></span>
                                </div>
                                <div class="d-flex justify-content-between text-dark mt-2 pt-2 border-top">
                                    <strong>Remaining Balance:</strong>
                                    <strong class="text-warning">₹<?php echo e(number_format($viewOrder->remaining_balance, 2)); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Stage Comment Modal -->
    <div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="commentModalLabel"><i class="bi bi-chat-left-text text-primary me-2"></i>Add Stage Comment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" rows="4" wire:model="commentText" placeholder="Add a comment for this pipeline stage..."></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['commentText'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" wire:click="saveComment">Save Comment</button>
                </div>
            </div>
        </div>
    </div>
    
        <?php
        $__scriptKey = '188631104-0';
        ob_start();
    ?>
    <script>
        $wire.on('open-reject-modal', () => {
            let modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
        });
        
        $wire.on('close-reject-modal', () => {
            let el = document.getElementById('rejectModal');
            let modal = bootstrap.Modal.getInstance(el);
            if (modal) modal.hide();
        });

        $wire.on('close-create-modal', () => {
            let el = document.getElementById('createOrderModal');
            let modal = bootstrap.Modal.getInstance(el);
            if (modal) modal.hide();
        });
        
        $wire.on('open-view-modal', () => {
            let modal = new bootstrap.Modal(document.getElementById('viewOrderModal'));
            modal.show();
        });

        $wire.on('open-comment-modal', () => {
            let modal = new bootstrap.Modal(document.getElementById('commentModal'));
            modal.show();
        });

        $wire.on('close-comment-modal', () => {
            let el = document.getElementById('commentModal');
            let modal = bootstrap.Modal.getInstance(el);
            if (modal) modal.hide();
        });
    </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/lead-orders/index.blade.php ENDPATH**/ ?>