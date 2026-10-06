<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">Notice Board</h5>
            <p class="text-muted small mb-0">Manage global and personal notices</p>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->canAccess('notice_create')): ?>
        <button class="btn btn-primary d-flex align-items-center gap-2 px-4 rounded-3 shadow-sm" wire:click="createNotice">
            <i class="bi bi-plus-lg"></i> New Notice
        </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->canAccess('notice_viewAny') || auth()->user()->canAccess('notice_viewteam')): ?>
    <div class="mb-4">
        <?php echo $__env->make('partials.hrms-filters', ['viewAnyPermission' => 'notice_viewAny'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <div class="row g-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $notices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $typeConfig = match($notice->type) {
                    'global'     => ['icon' => 'bi-globe2',        'label' => 'Global Notice',     'class' => 'primary'],
                    'branch'     => ['icon' => 'bi-building',      'label' => 'Branch Notice',     'class' => 'info'],
                    'department' => ['icon' => 'bi-diagram-3',     'label' => 'Department Notice', 'class' => 'warning'],
                    'employee'   => ['icon' => 'bi-person-check',  'label' => 'Employee Notice',   'class' => 'success'],
                    default      => ['icon' => 'bi-megaphone',     'label' => ucfirst($notice->type), 'class' => 'secondary'],
                };
                $targetCount = match($notice->type) {
                    'employee'   => is_array($notice->user_ids) ? count($notice->user_ids) : ($notice->user_id ? 1 : 0),
                    'branch'     => is_array($notice->branch_ids) ? count($notice->branch_ids) : 0,
                    'department' => is_array($notice->department_ids) ? count($notice->department_ids) : 0,
                    default      => null,
                };
                $isExpired = $notice->end_date && \Carbon\Carbon::parse($notice->end_date)->isPast();
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 rounded-4 shadow-sm overflow-hidden position-relative">
                    
                    <div class="bg-<?php echo e($typeConfig['class']); ?>" style="height:4px;"></div>

                    <div class="card-body p-4 d-flex flex-column">
                        
                        <div class="d-flex align-items-start justify-content-between mb-3 gap-2">
                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1 px-3 py-2 fw-semibold text-<?php echo e($typeConfig['class']); ?> bg-<?php echo e($typeConfig['class']); ?>-subtle border border-<?php echo e($typeConfig['class']); ?>-subtle" style="font-size:.72rem;">
                                <i class="bi <?php echo e($typeConfig['icon']); ?>"></i>
                                <?php echo e($typeConfig['label']); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($targetCount !== null): ?> &bull; <?php echo e($targetCount); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->canAccess('notice_update') || Auth::user()->canAccess('notice_delete')): ?>
                            <div class="d-flex gap-1 flex-shrink-0">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->canAccess('notice_update')): ?>
                                <button wire:click="editNotice(<?php echo e($notice->id); ?>)" class="btn btn-sm btn-light border-0 text-primary rounded-3 px-2 py-1" title="Edit">
                                    <i class="bi bi-pencil-fill" style="font-size:.8rem;"></i>
                                </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->canAccess('notice_delete')): ?>
                                <button wire:click="deleteNotice(<?php echo e($notice->id); ?>)" class="btn btn-sm btn-light border-0 text-danger rounded-3 px-2 py-1" title="Delete"
                                    onclick="confirm('Delete this notice?') || event.stopImmediatePropagation()">
                                    <i class="bi bi-trash3-fill" style="font-size:.8rem;"></i>
                                </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        
                        <h6 class="fw-bold mb-2" style="font-size:1rem; line-height:1.4; color:var(--text-primary);"><?php echo e($notice->title); ?></h6>

                        
                        <p class="text-muted mb-3 flex-grow-1" style="font-size:.875rem; white-space:pre-line; line-height:1.6;"><?php echo e(Str::limit($notice->content, 120)); ?></p>

                        
                        <div class="mt-auto">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notice->start_date || $notice->end_date): ?>
                            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                <span class="badge <?php echo e($isExpired ? 'text-danger bg-danger-subtle border border-danger-subtle' : 'text-success bg-success-subtle border border-success-subtle'); ?> rounded-3 px-2 py-1" style="font-size:.72rem;">
                                    <i class="bi <?php echo e($isExpired ? 'bi-calendar-x' : 'bi-calendar-check'); ?> me-1"></i><?php echo e($isExpired ? 'Expired' : 'Active'); ?>

                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notice->start_date && $notice->end_date): ?>
                                <span class="text-muted" style="font-size:.75rem;">
                                    <?php echo e(\Carbon\Carbon::parse($notice->start_date)->format('d M')); ?> – <?php echo e(\Carbon\Carbon::parse($notice->end_date)->format('d M, Y')); ?>

                                </span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <span class="text-muted d-flex align-items-center gap-1" style="font-size:.75rem;">
                                    <i class="bi bi-clock"></i> <?php echo e($notice->created_at->diffForHumans()); ?>

                                </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notice->action_link): ?>
                                <a href="<?php echo e($notice->action_link); ?>" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" style="font-size:.78rem;">
                                    <i class="bi bi-box-arrow-up-right me-1"></i><?php echo e($notice->action_text ?: 'View'); ?>

                                </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <div class="col-12">
                <div class="card border-0 rounded-4 shadow-sm text-center">
                    <div class="card-body py-5">
                        <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle" style="width:72px; height:72px; background:var(--primary-light);">
                            <i class="bi bi-megaphone" style="font-size:2rem; color:var(--primary);"></i>
                        </div>
                        <h6 class="fw-bold mb-1" style="color:var(--text-primary);">No Notices Available</h6>
                        <p class="text-muted small mb-0">Check back later for news and announcements.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isModalOpen): ?>
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(10,10,30,0.55); z-index:1055; backdrop-filter:blur(2px); overflow-y:auto;">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width:860px; margin: 1.75rem auto;">
            <div class="modal-content border-0 rounded-4 overflow-hidden" style="box-shadow:var(--shadow-lg);">

                
                <div class="modal-header border-0 px-4 pt-4 pb-3" style="background:var(--primary);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px; height:42px; background:rgba(255,255,255,0.18);">
                            <i class="bi bi-megaphone-fill text-white fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0"><?php echo e($editingId ? 'Edit Notice' : 'Create New Notice'); ?></h5>
                            <p class="mb-0 text-white-50 small"><?php echo e($editingId ? 'Update the notice details below' : 'Fill in the details to broadcast a notice'); ?></p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto" wire:click="$set('isModalOpen', false)"></button>
                </div>

                <form wire:submit.prevent="saveNotice">
                    <div class="modal-body p-0" style="overflow:visible; max-height:none;">
                        <div class="row g-0" style="min-height:0;">
                            
                            <div class="col-lg-7 p-4 border-end bg-white" style="overflow-y:auto; max-height:70vh;">

                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-1">Notice Title <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.defer="title" class="form-control rounded-3" placeholder="e.g. Weekly Team Sync / Policy Update" style="font-size:.95rem;">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small mt-1 d-block"><i class="bi bi-exclamation-circle me-1"></i><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-2">Notice Type</label>
                                    <div class="row g-2">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = [
                                            ['val' => 'global',     'icon' => 'bi-globe2',       'label' => 'Global',     'desc' => 'All employees',  'cls' => 'primary'],
                                            ['val' => 'branch',     'icon' => 'bi-building',     'label' => 'Branch',     'desc' => 'Branch-wise',    'cls' => 'info'],
                                            ['val' => 'department', 'icon' => 'bi-diagram-3',    'label' => 'Department', 'desc' => 'Dept-wise',      'cls' => 'warning'],
                                            ['val' => 'employee',   'icon' => 'bi-person-check', 'label' => 'Employee',   'desc' => 'Specific staff', 'cls' => 'success'],
                                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="col-6">
                                            <label style="cursor:pointer; display:block;">
                                                <input type="radio" wire:model.live="type" value="<?php echo e($opt['val']); ?>" class="d-none">
                                                <div class="rounded-3 p-3 d-flex align-items-center gap-3 border-2"
                                                     style="border: 2px solid <?php echo e($type === $opt['val'] ? 'var(--'.$opt['cls'].')' : '#e2e8f0'); ?>;
                                                            background: <?php echo e($type === $opt['val'] ? '#f0f7ff' : '#f8fafc'); ?>;
                                                            transition: all .15s ease;">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white"
                                                         style="width:36px; height:36px; background: <?php echo e($type === $opt['val'] ? 'var(--'.$opt['cls'].')' : '#cbd5e1'); ?>; transition: background .15s;">
                                                        <i class="bi <?php echo e($opt['icon']); ?>" style="font-size:.85rem;"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold small" style="color: <?php echo e($type === $opt['val'] ? 'var(--primary)' : 'var(--text-primary)'); ?>;"><?php echo e($opt['label']); ?></div>
                                                        <div class="text-muted" style="font-size:.72rem;"><?php echo e($opt['desc']); ?></div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small mt-1 d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($type, ['personal', 'employee'])): ?>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-semibold small text-uppercase text-muted mb-0">Target Employees <span class="text-danger">*</span></label>
                                        <span class="badge rounded-pill bg-primary" style="font-size:.72rem;"><?php echo e(count($user_ids)); ?> selected</span>
                                    </div>
                                    <div class="input-group mb-2 rounded-3 overflow-hidden border">
                                        <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted small"></i></span>
                                        <input type="text" class="form-control border-0 ps-0" wire:model.live.debounce.150ms="employeeSearch" placeholder="Search name, email, code...">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($employeeSearch)): ?>
                                        <button type="button" class="btn btn-light border-0" wire:click="$set('employeeSearch', '')"><i class="bi bi-x"></i></button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold text-primary" style="font-size:.75rem;" wire:click="selectAllFilteredEmployees">Select All</button>
                                            <span class="text-muted" style="font-size:.75rem;">|</span>
                                            <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold text-danger" style="font-size:.75rem;" wire:click="deselectAllEmployees">Deselect All</button>
                                        </div>
                                        <span class="text-muted" style="font-size:.73rem;"><?php echo e(count($this->filteredEmployees)); ?> found</span>
                                    </div>
                                    <div class="border rounded-3 bg-white" style="max-height:175px; overflow-y:auto;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->filteredEmployees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <?php
                                                $eId    = is_array($emp) ? $emp['id'] : $emp->id;
                                                $eName  = is_array($emp) ? $emp['name'] : $emp->name;
                                                $eCode  = is_array($emp) ? ($emp['employee_code'] ?? '') : ($emp->employee_code ?? '');
                                                $eEmail = is_array($emp) ? ($emp['email'] ?? '') : ($emp->email ?? '');
                                                $isSel  = in_array($eId, $user_ids);
                                            ?>
                                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom"
                                                 style="cursor:pointer; background:<?php echo e($isSel ? 'var(--primary-light)' : 'transparent'); ?>; transition:background .1s;"
                                                 wire:click="toggleEmployee('<?php echo e($eId); ?>')">
                                                <div class="d-flex align-items-center gap-2 text-truncate">
                                                    <input class="form-check-input flex-shrink-0 m-0" type="checkbox" value="<?php echo e($eId); ?>" wire:model="user_ids" onclick="event.stopPropagation();">
                                                    <div class="text-truncate">
                                                        <span class="fw-semibold small" style="color:var(--text-primary);"><?php echo e($eName); ?></span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($eCode): ?> <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.68rem;"><?php echo e($eCode); ?></span> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </div>
                                                <span class="text-muted text-truncate ms-2 flex-shrink-0" style="font-size:.72rem; max-width:120px;"><?php echo e($eEmail); ?></span>
                                            </div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <div class="text-center py-4 text-muted small">
                                                <i class="bi bi-person-x d-block fs-4 mb-1"></i>No employees found
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['user_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small mt-1 d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'branch'): ?>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-2">Target Branches <span class="text-danger">*</span></label>
                                    <div class="border rounded-3 bg-white" style="max-height:160px; overflow-y:auto;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <label class="d-flex align-items-center gap-3 px-3 py-2 border-bottom m-0" style="cursor:pointer;">
                                            <input class="form-check-input flex-shrink-0 m-0" type="checkbox" value="<?php echo e($branch->id); ?>" wire:model="branch_ids">
                                            <div>
                                                <div class="fw-semibold small" style="color:var(--text-primary);"><?php echo e($branch->name); ?></div>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($branch->address): ?> <div class="text-muted" style="font-size:.72rem;"><?php echo e($branch->address); ?></div> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </label>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <div class="text-center py-4 text-muted small"><i class="bi bi-building d-block fs-4 mb-1"></i>No branches available</div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['branch_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small mt-1 d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'department'): ?>
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-2">Target Departments <span class="text-danger">*</span></label>
                                    <div class="border rounded-3 bg-white" style="max-height:160px; overflow-y:auto;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <label class="d-flex align-items-center gap-3 px-3 py-2 border-bottom m-0" style="cursor:pointer;">
                                            <input class="form-check-input flex-shrink-0 m-0" type="checkbox" value="<?php echo e($dept->id); ?>" wire:model="department_ids">
                                            <div class="fw-semibold small" style="color:var(--text-primary);"><?php echo e($dept->name); ?></div>
                                        </label>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <div class="text-center py-4 text-muted small"><i class="bi bi-diagram-3 d-block fs-4 mb-1"></i>No departments available</div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['department_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small mt-1 d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-2">Notice Duration</label>
                                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 border mb-2" style="background:#f8fafc;">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" wire:model.live="is_unlimited" id="isUnlimited" style="width:2.2rem; height:1.2rem;">
                                        </div>
                                        <label class="form-check-label small fw-semibold" for="isUnlimited" style="color:var(--text-primary);">
                                            <?php echo e($is_unlimited ? 'No expiry – unlimited duration' : 'Set a specific validity period'); ?>

                                        </label>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$is_unlimited): ?>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label text-muted small">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" wire:model="start_date" class="form-control rounded-3">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['start_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted small">End Date <span class="text-danger">*</span></label>
                                            <input type="date" wire:model="end_date" class="form-control rounded-3">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['end_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                
                                <div>
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-1">Notice Content <span class="text-danger">*</span></label>
                                    <textarea wire:model.defer="content" rows="4" class="form-control rounded-3" placeholder="Write your notice content here..." style="font-size:.95rem; resize:none;"></textarea>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small mt-1 d-block"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            
                            <div class="col-lg-5 p-4 d-flex flex-column" style="background:var(--bg-body); overflow-y:auto; max-height:70vh;">
                                <div class="mb-4">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <div class="d-flex align-items-center justify-content-center rounded-2" style="width:30px; height:30px; background:var(--primary-light);">
                                            <i class="bi bi-link-45deg" style="color:var(--primary);"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0" style="color:var(--text-primary);">Action Link & Button</h6>
                                    </div>
                                    <p class="text-muted mb-0" style="font-size:.78rem; padding-left:38px;">Add an optional CTA link shown directly on the Notice card.</p>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted fw-semibold small text-uppercase mb-1">Action URL</label>
                                    <div class="input-group rounded-3 overflow-hidden border bg-white">
                                        <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-link-45deg"></i></span>
                                        <input type="url" wire:model.defer="action_link" placeholder="https://..." class="form-control border-0 ps-0">
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['action_link'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small mt-1 d-block"><i class="bi bi-exclamation-circle me-1"></i><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted fw-semibold small text-uppercase mb-1">Button Label</label>
                                    <input type="text" wire:model="action_text" placeholder="e.g. Join Now, Register, View Details" class="form-control rounded-3 border mb-2">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['action_text'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <label class="text-muted mb-2 d-block" style="font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Quick Presets</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['Join Now' => 'bi-camera-video', 'Subscribe' => 'bi-bell', 'Register' => 'bi-pencil-square', 'View Details' => 'bi-eye']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $icon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <button type="button" class="btn btn-sm border rounded-pill bg-white px-3" style="font-size:.78rem;" wire:click="setQuickActionText('<?php echo e($label); ?>')">
                                            <i class="bi <?php echo e($icon); ?> me-1 text-primary"></i><?php echo e($label); ?>

                                        </button>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                </div>

                                
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($action_link || $action_text): ?>
                                <div class="p-3 rounded-3 border bg-white mb-3">
                                    <p class="text-muted mb-2" style="font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.4px;">Preview</p>
                                    <a href="#" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold" style="font-size:.82rem;">
                                        <i class="bi bi-box-arrow-up-right me-1"></i><?php echo e($action_text ?: 'View'); ?>

                                    </a>
                                </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                
                                <div class="mt-auto pt-3 border-top">
                                    <div class="d-flex align-items-start gap-2 rounded-3 p-3" style="background:var(--primary-light); border:1px solid rgba(37,99,235,.2);">
                                        <i class="bi bi-bell-fill mt-1 flex-shrink-0" style="color:var(--primary); font-size:.85rem;"></i>
                                        <p class="mb-0" style="font-size:.75rem; line-height:1.5; color:var(--text-primary);">
                                            <strong>Push Notification</strong> will be sent to targeted employees' devices when you publish this notice (if enabled in your plan).
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="modal-footer border-top px-4 py-3 bg-white">
                        <button type="button" class="btn btn-light rounded-3 px-4" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary px-5 rounded-3 fw-semibold shadow-sm" wire:loading.attr="disabled" wire:target="saveNotice">
                            <i class="bi bi-send me-2" wire:loading.remove wire:target="saveNotice"></i>
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" wire:loading wire:target="saveNotice"></span>
                            <?php echo e($editingId ? 'Update Notice' : 'Publish Notice'); ?>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/notices.blade.php ENDPATH**/ ?>