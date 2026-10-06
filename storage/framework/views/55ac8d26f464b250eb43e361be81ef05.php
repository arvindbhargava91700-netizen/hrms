<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-dark">
                <i class="bi bi-clock-history text-primary me-2"></i> Recovery History
            </h4>
            <p class="text-muted mb-0">Complete audit log and history of all collected recovery payments</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo e(route('partner.hrms.lead-orders.recovery')); ?>" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-wallet2 me-1"></i> Outstanding Recovery
            </a>
            <button wire:click="exportCsv" class="btn btn-success rounded-pill px-3 shadow-sm text-white">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border: 1px solid #bbf7d0 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted extra-small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Recovered</div>
                        <div class="fw-bold fs-4 text-success mt-1">₹<?php echo e(number_format($totalRecovered, 2)); ?></div>
                        <div class="text-muted extra-small mt-1" style="font-size: 0.7rem;">Lifetime collections</div>
                    </div>
                    <div class="bg-white rounded-circle p-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cash-coin fs-4 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted extra-small fw-bold text-uppercase" style="font-size: 0.72rem;">This Month</div>
                        <div class="fw-bold fs-4 text-primary mt-1">₹<?php echo e(number_format($thisMonthRecovered, 2)); ?></div>
                        <div class="text-muted extra-small mt-1" style="font-size: 0.7rem;">Current month collection</div>
                    </div>
                    <div class="bg-white rounded-circle p-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-calendar-check fs-4 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: linear-gradient(135deg, #fefce8 0%, #fef08a 100%); border: 1px solid #fde047 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted extra-small fw-bold text-uppercase" style="font-size: 0.72rem;">Today Recovered</div>
                        <div class="fw-bold fs-4 text-warning mt-1" style="color: #b45309 !important;">₹<?php echo e(number_format($todayRecovered, 2)); ?></div>
                        <div class="text-muted extra-small mt-1" style="font-size: 0.7rem;">Collected today</div>
                    </div>
                    <div class="bg-white rounded-circle p-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-lightning-charge fs-4 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3" style="background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border: 1px solid #e9d5ff !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-muted extra-small fw-bold text-uppercase" style="font-size: 0.72rem;">Transactions</div>
                        <div class="fw-bold fs-4 text-purple mt-1" style="color: #7e22ce !important;"><?php echo e(number_format($totalTransactions)); ?></div>
                        <div class="text-muted extra-small mt-1" style="font-size: 0.7rem;">Total receipts recorded</div>
                    </div>
                    <div class="bg-white rounded-circle p-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-receipt fs-4 text-purple" style="color: #7e22ce;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Search customer, mobile, ref or notes..." wire:model.live.debounce.400ms="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="paymentMethod">
                        <option value="">All Payment Methods</option>
                        <option value="cash">Cash</option>
                        <option value="upi">UPI / QR</option>
                        <option value="bank">Bank Transfer / NEFT</option>
                        <option value="card">Card</option>
                        <option value="online">Online Payment</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.live="fromDate" title="From Date">
                </div>
                <div class="col-md-2">
                    <input type="date" class="form-control" wire:model.live="toDate" title="To Date">
                </div>
                <div class="col-md-1 text-end">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($search || $paymentMethod || $fromDate || $toDate): ?>
                    <button wire:click="$set('search', ''); $set('paymentMethod', ''); $set('fromDate', ''); $set('toDate', '');" class="btn btn-light rounded-pill w-100" title="Reset Filters">
                        <i class="bi bi-x-circle text-danger"></i>
                    </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments History Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover table-feetrack mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Receipt / Date</th>
                        <th>Customer / Lead</th>
                        <th>Order Details</th>
                        <th class="text-end">Recovered Amount</th>
                        <th>Method</th>
                        <th>Collected By</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="fw-bold text-dark">
                                <i class="bi bi-receipt text-primary me-1"></i>
                                #<?php echo e(substr($payment->id, 0, 8)); ?>

                            </div>
                            <div class="text-muted small">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?php echo e(\Carbon\Carbon::parse($payment->payment_date)->format('d M Y')); ?>

                            </div>
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-dark"><?php echo e($payment->order?->lead?->customer_name ?? 'N/A'); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->order?->lead?->customer_mobile): ?>
                            <div class="text-muted small">
                                <i class="bi bi-telephone me-1"></i> <?php echo e($payment->order->lead->customer_mobile); ?>

                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="py-3">
                            <div class="small fw-semibold text-dark">
                                Order #<?php echo e(substr($payment->lead_order_id, 0, 8)); ?>

                            </div>
                            <div class="text-muted extra-small" style="font-size: 0.72rem;">
                                Order Total: ₹<?php echo e(number_format((float)($payment->order?->final_amount ?? 0), 2)); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->order): ?>
                                    &bull; Balance: <span class="<?php echo e($payment->order->remaining_balance > 0 ? 'text-danger' : 'text-success'); ?> fw-bold">₹<?php echo e(number_format((float)$payment->order->remaining_balance, 2)); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </td>
                        <td class="py-3 text-end">
                            <div class="fw-bold text-success fs-6">
                                +₹<?php echo e(number_format((float)$payment->amount, 2)); ?>

                            </div>
                        </td>
                        <td class="py-3">
                            <?php
                                $badgeClass = match($payment->payment_method) {
                                    'cash' => 'bg-success bg-opacity-10 text-success border-success',
                                    'upi' => 'bg-primary bg-opacity-10 text-primary border-primary',
                                    'bank' => 'bg-info bg-opacity-10 text-info border-info',
                                    'card' => 'bg-warning bg-opacity-10 text-dark border-warning',
                                    default => 'bg-secondary bg-opacity-10 text-secondary border-secondary',
                                };
                            ?>
                            <span class="badge <?php echo e($badgeClass); ?> border rounded-pill px-2.5 py-1 text-uppercase" style="font-size: 0.72rem;">
                                <?php echo e($payment->payment_method); ?>

                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payment->reference): ?>
                                <div class="text-muted extra-small mt-0.5" style="font-size: 0.68rem;" title="Reference / Transaction ID">
                                    Ref: <?php echo e($payment->reference); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="py-3">
                            <div class="fw-medium text-dark small">
                                <?php echo e($payment->paidBy?->name ?? 'System'); ?>

                            </div>
                            <div class="text-muted extra-small" style="font-size: 0.68rem;">
                                <?php echo e($payment->created_at ? $payment->created_at->diffForHumans() : ''); ?>

                            </div>
                        </td>
                        <td class="py-3 text-end pe-4">
                            <button wire:click="viewDetails('<?php echo e($payment->id); ?>')" class="btn btn-sm btn-light border shadow-sm rounded-pill px-3 text-dark">
                                <i class="bi bi-eye text-primary me-1"></i> View
                            </button>
                        </td>
                    </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <div class="text-muted mb-2"><i class="bi bi-clock-history fs-1"></i></div>
                            <h6 class="fw-bold text-secondary">No Recovery History Found</h6>
                            <p class="text-muted small mb-0">Payments recorded on recovery orders will appear here.</p>
                        </td>
                    </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($payments->hasPages()): ?>
        <div class="card-footer bg-white border-0 py-3">
            <?php echo e($payments->links()); ?>

        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <!-- Payment Detail Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showDetailModal && $selectedPayment): ?>
    <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom py-3 px-4 bg-white d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold text-dark mb-0">
                        <i class="bi bi-receipt text-success me-2"></i> Payment Receipt Details
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeDetailModal"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <!-- Amount Callout -->
                    <div class="text-center p-3 bg-white rounded-3 border border-success border-opacity-25 shadow-sm mb-3">
                        <div class="text-muted extra-small fw-bold text-uppercase">Recovered Amount</div>
                        <div class="fs-3 fw-bold text-success mt-1">₹<?php echo e(number_format((float)$selectedPayment->amount, 2)); ?></div>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1 extra-small fw-semibold mt-1">
                            Recorded on <?php echo e(\Carbon\Carbon::parse($selectedPayment->payment_date)->format('d M Y')); ?>

                        </span>
                    </div>

                    <!-- Details Grid -->
                    <div class="bg-white rounded-3 p-3 border shadow-sm small">
                        <div class="row g-2.5">
                            <div class="col-6">
                                <div class="text-muted extra-small">Customer Name</div>
                                <div class="fw-bold text-dark"><?php echo e($selectedPayment->order?->lead?->customer_name ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted extra-small">Contact Number</div>
                                <div class="fw-bold text-dark"><?php echo e($selectedPayment->order?->lead?->customer_mobile ?? 'N/A'); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted extra-small">Order ID</div>
                                <div class="fw-bold text-dark">#<?php echo e(substr($selectedPayment->lead_order_id, 0, 8)); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted extra-small">Payment Method</div>
                                <div class="fw-bold text-primary text-uppercase"><?php echo e($selectedPayment->payment_method); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted extra-small">Reference Number</div>
                                <div class="fw-medium text-dark"><?php echo e($selectedPayment->reference ?: 'None'); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted extra-small">Collected By</div>
                                <div class="fw-medium text-dark"><?php echo e($selectedPayment->paidBy?->name ?? 'System'); ?></div>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedPayment->notes): ?>
                            <div class="col-12 mt-2 pt-2 border-top">
                                <div class="text-muted extra-small">Notes / Remarks</div>
                                <div class="text-dark"><?php echo e($selectedPayment->notes); ?></div>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-white">
                    <button type="button" class="btn btn-light rounded-pill px-4" wire:click="closeDetailModal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/lead-orders/recovery-history.blade.php ENDPATH**/ ?>