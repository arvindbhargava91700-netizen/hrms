<div class="nav-item dropdown">
    <a class="nav-link dropdown-toggle position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="padding-top: 10px; padding-bottom: 10px;">
        <i class="bi bi-bell fs-5"></i>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unreadCount > 0): ?>
            <span class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                <?php echo e($unreadCount > 99 ? '99+' : $unreadCount); ?>

            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </a>
    
    <div class="dropdown-menu dropdown-menu-end shadow p-0" style="width: 350px; max-height: 500px; overflow-y: auto;">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom bg-light">
            <h6 class="mb-0 fw-bold">Notifications</h6>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unreadCount > 0): ?>
                <button wire:click="markAllAsRead" class="btn btn-sm btn-link text-decoration-none p-0 text-primary">Mark all as read</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        
        <div class="list-group list-group-flush">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="list-group-item list-group-item-action p-3 <?php echo e($notification->read_at ? 'bg-white text-muted' : 'bg-light'); ?>">
                    <div class="d-flex gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notification->image): ?>
                            <div class="flex-shrink-0">
                                <img src="<?php echo e($notification->image); ?>" class="rounded" style="width: 48px; height: 48px; object-fit: cover;">
                            </div>
                        <?php else: ?>
                            <div class="flex-shrink-0 d-flex align-items-center justify-content-center bg-primary text-white rounded-circle" style="width: 40px; height: 40px;">
                                <i class="bi bi-bell-fill"></i>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div class="flex-grow-1 min-w-0" style="cursor: pointer;" wire:click="markAsRead('<?php echo e($notification->id); ?>')">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-truncate <?php echo e($notification->read_at ? 'fw-normal' : 'fw-bold'); ?>" style="font-size: 0.9rem;">
                                    <?php echo e($notification->title); ?>

                                </h6>
                                <small class="text-nowrap ms-2" style="font-size: 0.75rem;">
                                    <?php echo e($notification->created_at->diffForHumans(null, true, true)); ?>

                                </small>
                            </div>
                            <p class="mb-0 text-break" style="font-size: 0.85rem; line-height: 1.4;">
                                <?php echo e(\Illuminate\Support\Str::limit($notification->message, 80)); ?>

                            </p>
                        </div>
                        
                        <div class="ms-2 d-flex flex-column justify-content-center align-items-center">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$notification->read_at): ?>
                                <div class="bg-primary rounded-circle mb-2" style="width: 8px; height: 8px;"></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <button wire:click.stop="deleteNotification('<?php echo e($notification->id); ?>')" class="btn btn-sm btn-link text-muted p-0 border-0" title="Delete">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-bell-slash fs-3 mb-2 d-block text-black-50"></i>
                    <p class="mb-0 small">No notifications yet.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($notifications) >= 10): ?>
            <div class="p-2 text-center border-top bg-light">
                <span class="small text-muted">Showing latest 10</span>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/notification-bell.blade.php ENDPATH**/ ?>