<div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Roles</h5>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('role_create')): ?>
            <button class="btn btn-primary btn-sm" wire:click="createRole">
                <i class="bi bi-plus-circle me-1"></i> Add Role
            </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Permissions</th>
                        <th>Employees</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td class="fw-600 text-dark"><?php echo e(str_replace([$this->getPartnerId() . '_', '_' . $this->getPartnerId()], '', $role->name)); ?></td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                                    <i class="bi bi-shield-check me-1"></i> <?php echo e($role->permissions->count()); ?> Permissions
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                    <i class="bi bi-people me-1"></i> <?php echo e($role->users_count ?? 0); ?> Employees
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info me-1" wire:click="viewRole(<?php echo e($role->id); ?>)" title="View Permissions">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('role_update')): ?>
                                <button class="btn btn-sm btn-outline-secondary me-1" wire:click="editRole(<?php echo e($role->id); ?>)" title="Edit Role">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isPartner() || auth()->user()->canAccess('role_delete')): ?>
                                <button class="btn btn-sm btn-outline-danger" wire:click="deleteRole(<?php echo e($role->id); ?>)" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()" title="Delete Role">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="bi bi-shield-lock display-4 mb-3 d-block text-light"></i>
                                No custom roles created yet.
                            </td>
                        </tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Role Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isRoleModalOpen): ?>
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form wire:submit.prevent="saveRole">
                    <div class="modal-header">
                        <h5 class="modal-title"><?php echo e($editingRoleId ? 'Edit Role' : 'Create Role'); ?></h5>
                        <button type="button" class="btn-close" wire:click="$set('isRoleModalOpen', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label fw-600">Role Name</label>
                            <input type="text" class="form-control" wire:model="roleName" placeholder="e.g. Manager">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['roleName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="text-danger small"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        
                        <label class="form-label fw-600">Permissions</label>
                        <div class="row">
                            <?php
                                $colorMap = [
                                    'Leave' => ['bg' => '#e6f4ea', 'text' => '#1e8e3e', 'border' => '#fbbc04', 'headerBg' => '#fef7e0'],
                                    'Attendance' => ['bg' => '#fef0d8', 'text' => '#ff8c00', 'border' => '#fbbc04', 'headerBg' => '#fef7e0'],
                                    // Fallback defaults
                                    'default' => ['bg' => '#e8f0fe', 'text' => '#1967d2', 'border' => '#d2e3fc', 'headerBg' => '#f8f9fa'],
                                ];
                            ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $permissionGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $actions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php 
                                    $colors = $colorMap[$module] ?? $colorMap['default']; 
                                    // Calculate if all are selected for the "Select All" checkbox
                                    $allModulePerms = collect($actions)->map(fn($a) => strtolower($module) . '_' . strtolower($a))->toArray();
                                    $allSelected = count(array_intersect($allModulePerms, $selectedPermissions)) === count($allModulePerms);
                                ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-3" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'module-'.e($module).''; ?>wire:key="module-<?php echo e($module); ?>">
                                    <div class="card h-100" style="border: 1px solid <?php echo e($colors['border']); ?>; border-radius: 4px; box-shadow: none;">
                                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: <?php echo e($colors['headerBg']); ?>; border-bottom: 0; padding: 10px 15px;">
                                            <div class="d-flex align-items-center">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($module == 'Leave'): ?>
                                                    <i class="bi bi-briefcase me-2 text-success"></i>
                                                <?php elseif($module == 'Attendance'): ?>
                                                    <i class="bi bi-clock me-2 text-warning"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-grid me-2 text-primary"></i>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <span class="fw-bold text-uppercase" style="color: #333; font-size: 14px;"><?php echo e($module); ?></span>
                                            </div>
                                            <input class="form-check-input" type="checkbox" 
                                                style="width: 1.2rem; height: 1.2rem;"
                                                <?php if($allSelected): ?> checked <?php endif; ?>
                                                wire:click="toggleModule('<?php echo e($module); ?>')"
                                                id="toggle_<?php echo e($module); ?>">
                                        </div>
                                        <div class="card-body p-3">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <?php $permName = strtolower($module) . '_' . strtolower($action); ?>
                                                <div class="d-flex align-items-center mb-2" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'perm-'.e($permName).''; ?>wire:key="perm-<?php echo e($permName); ?>">
                                                 <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($action, ['viewAny', 'viewBranch', 'viewTeam'])): ?>

                                                        <input 
                                                            class="form-check-input me-2" 
                                                            type="radio"
                                                            name="view_<?php echo e(strtolower($module)); ?>"
                                                            wire:click="selectViewPermission('<?php echo e($module); ?>', '<?php echo e($action); ?>')"
                                                            <?php if(in_array($permName, $selectedPermissions)): echo 'checked'; endif; ?>
                                                            id="perm_<?php echo e($permName); ?>"
                                                            style="width: 1.2rem; height: 1.2rem;"
                                                        >

                                                    <?php else: ?>

                                                        <input 
                                                            class="form-check-input me-2" 
                                                            type="checkbox"
                                                            wire:model.live="selectedPermissions"
                                                            value="<?php echo e($permName); ?>"
                                                            id="perm_<?php echo e($permName); ?>"
                                                            style="width: 1.2rem; height: 1.2rem;"
                                                        >

                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    <label class="form-check-label mb-0" for="perm_<?php echo e($permName); ?>">
                                                        <span class="badge" style="background-color: <?php echo e($colors['bg']); ?>; color: <?php echo e($colors['text']); ?>; font-size: 13px; font-weight: 500; padding: 6px 10px;">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($action === 'viewAny'): ?> AnyView <?php elseif($action === 'viewOwn'): ?> OurView <?php elseif($action === 'viewTeam'): ?> TeamView <?php elseif($action === 'viewBranch'): ?> BranchView <?php else: ?> <?php echo e($action); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </span>
                                                    </label>
                                                </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('isRoleModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <!-- View Role Modal -->
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isViewModalOpen && $viewingRole): ?>
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Role: <?php echo e(str_replace([$this->getPartnerId() . '_', '_' . $this->getPartnerId()], '', $viewingRole->name)); ?></h5>
                    <button type="button" class="btn-close" wire:click="$set('isViewModalOpen', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <?php
                            $colorMap = [
                                'Leave' => ['bg' => '#e6f4ea', 'text' => '#1e8e3e', 'border' => '#fbbc04', 'headerBg' => '#fef7e0'],
                                'Attendance' => ['bg' => '#fef0d8', 'text' => '#ff8c00', 'border' => '#fbbc04', 'headerBg' => '#fef7e0'],
                                'default' => ['bg' => '#e8f0fe', 'text' => '#1967d2', 'border' => '#d2e3fc', 'headerBg' => '#f8f9fa'],
                            ];
                            $rolePermissions = $viewingRole->permissions->pluck('name')->toArray();
                        ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $permissionGroups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module => $actions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php 
                                $modulePerms = collect($actions)->map(fn($a) => strtolower($module) . '_' . strtolower($a))->toArray();
                                $hasAny = count(array_intersect($modulePerms, $rolePermissions)) > 0;
                            ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasAny): ?>
                                <?php $colors = $colorMap[$module] ?? $colorMap['default']; ?>
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                                    <div class="card h-100" style="border: 1px solid <?php echo e($colors['border']); ?>; border-radius: 4px; box-shadow: none;">
                                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: <?php echo e($colors['headerBg']); ?>; border-bottom: 0; padding: 10px 15px;">
                                            <div class="d-flex align-items-center">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($module == 'Leave'): ?>
                                                    <i class="bi bi-briefcase me-2 text-success"></i>
                                                <?php elseif($module == 'Attendance'): ?>
                                                    <i class="bi bi-clock me-2 text-warning"></i>
                                                <?php else: ?>
                                                    <i class="bi bi-grid me-2 text-primary"></i>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <span class="fw-bold text-uppercase" style="color: #333; font-size: 14px;"><?php echo e($module); ?></span>
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <?php $permName = strtolower($module) . '_' . strtolower($action); ?>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($permName, $rolePermissions)): ?>
                                                        <span class="badge" style="background-color: <?php echo e($colors['bg']); ?>; color: <?php echo e($colors['text']); ?>; font-size: 13px; font-weight: 500; padding: 6px 10px;">
                                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($action === 'viewAny'): ?> AnyView <?php elseif($action === 'viewOwn'): ?> OurView <?php elseif($action === 'viewTeam'): ?> TeamView <?php elseif($action === 'viewBranch'): ?> BranchView <?php else: ?> <?php echo e($action); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        </span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('isViewModalOpen', false)">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/livewire/partner/hrms/roles.blade.php ENDPATH**/ ?>