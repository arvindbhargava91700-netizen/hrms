<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Roles</h5>
            @if(auth()->user()->isPartner() || auth()->user()->canAccess('role_create'))
            <button class="btn btn-primary btn-sm" wire:click="createRole">
                <i class="bi bi-plus-circle me-1"></i> Add Role
            </button>
            @endif
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
                    @forelse($roles as $role)
                        <tr>
                            <td class="fw-600 text-dark">{{ str_replace([$this->getPartnerId() . '_', '_' . $this->getPartnerId()], '', $role->name) }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                                    <i class="bi bi-shield-check me-1"></i> {{ $role->permissions->count() }} Permissions
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                                    <i class="bi bi-people me-1"></i> {{ $role->users_count ?? 0 }} Employees
                                </span>
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info me-1" wire:click="viewRole({{ $role->id }})" title="View Permissions">
                                    <i class="bi bi-eye"></i>
                                </button>
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('role_update'))
                                <button class="btn btn-sm btn-outline-secondary me-1" wire:click="editRole({{ $role->id }})" title="Edit Role">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endif
                                @if(auth()->user()->isPartner() || auth()->user()->canAccess('role_delete'))
                                <button class="btn btn-sm btn-outline-danger" wire:click="deleteRole({{ $role->id }})" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()" title="Delete Role">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-5">
                                <i class="bi bi-shield-lock display-4 mb-3 d-block text-light"></i>
                                No custom roles created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create/Edit Role Modal -->
    @if($isRoleModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form wire:submit.prevent="saveRole">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingRoleId ? 'Edit Role' : 'Create Role' }}</h5>
                        <button type="button" class="btn-close" wire:click="$set('isRoleModalOpen', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label class="form-label fw-600">Role Name</label>
                            <input type="text" class="form-control" wire:model="roleName" placeholder="e.g. Manager">
                            @error('roleName') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        
                        <label class="form-label fw-600">Permissions</label>
                        <div class="row">
                            @php
                                $colorMap = [
                                    'Leave' => ['bg' => '#e6f4ea', 'text' => '#1e8e3e', 'border' => '#fbbc04', 'headerBg' => '#fef7e0'],
                                    'Attendance' => ['bg' => '#fef0d8', 'text' => '#ff8c00', 'border' => '#fbbc04', 'headerBg' => '#fef7e0'],
                                    // Fallback defaults
                                    'default' => ['bg' => '#e8f0fe', 'text' => '#1967d2', 'border' => '#d2e3fc', 'headerBg' => '#f8f9fa'],
                                ];
                            @endphp

                            @foreach($permissionGroups as $module => $actions)
                                @php 
                                    $colors = $colorMap[$module] ?? $colorMap['default']; 
                                    // Calculate if all are selected for the "Select All" checkbox
                                    $allModulePerms = collect($actions)->map(fn($a) => strtolower($module) . '_' . strtolower($a))->toArray();
                                    $allSelected = count(array_intersect($allModulePerms, $selectedPermissions)) === count($allModulePerms);
                                @endphp
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-3" wire:key="module-{{ $module }}">
                                    <div class="card h-100" style="border: 1px solid {{ $colors['border'] }}; border-radius: 4px; box-shadow: none;">
                                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: {{ $colors['headerBg'] }}; border-bottom: 0; padding: 10px 15px;">
                                            <div class="d-flex align-items-center">
                                                @if($module == 'Leave')
                                                    <i class="bi bi-briefcase me-2 text-success"></i>
                                                @elseif($module == 'Attendance')
                                                    <i class="bi bi-clock me-2 text-warning"></i>
                                                @else
                                                    <i class="bi bi-grid me-2 text-primary"></i>
                                                @endif
                                                <span class="fw-bold text-uppercase" style="color: #333; font-size: 14px;">{{ $module }}</span>
                                            </div>
                                            <input class="form-check-input" type="checkbox" 
                                                style="width: 1.2rem; height: 1.2rem;"
                                                @if($allSelected) checked @endif
                                                wire:click="toggleModule('{{ $module }}')"
                                                id="toggle_{{ $module }}">
                                        </div>
                                        <div class="card-body p-3">
                                            @foreach($actions as $action)
                                                @php $permName = strtolower($module) . '_' . strtolower($action); @endphp
                                                <div class="d-flex align-items-center mb-2" wire:key="perm-{{ $permName }}">
                                                 @if(in_array($action, ['viewAny', 'viewBranch', 'viewTeam']))

                                                        <input 
                                                            class="form-check-input me-2" 
                                                            type="radio"
                                                            name="view_{{ strtolower($module) }}"
                                                            wire:click="selectViewPermission('{{ $module }}', '{{ $action }}')"
                                                            @checked(in_array($permName, $selectedPermissions))
                                                            id="perm_{{ $permName }}"
                                                            style="width: 1.2rem; height: 1.2rem;"
                                                        >

                                                    @else

                                                        <input 
                                                            class="form-check-input me-2" 
                                                            type="checkbox"
                                                            wire:model.live="selectedPermissions"
                                                            value="{{ $permName }}"
                                                            id="perm_{{ $permName }}"
                                                            style="width: 1.2rem; height: 1.2rem;"
                                                        >

                                                    @endif
                                                    <label class="form-check-label mb-0" for="perm_{{ $permName }}">
                                                        <span class="badge" style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; font-size: 13px; font-weight: 500; padding: 6px 10px;">
                                                            @if($action === 'viewAny') AnyView @elseif($action === 'viewOwn') OurView @elseif($action === 'viewTeam') TeamView @elseif($action === 'viewBranch') BranchView @else {{ $action }} @endif
                                                        </span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
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
    @endif

    <!-- View Role Modal -->
    @if($isViewModalOpen && $viewingRole)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Role: {{ str_replace([$this->getPartnerId() . '_', '_' . $this->getPartnerId()], '', $viewingRole->name) }}</h5>
                    <button type="button" class="btn-close" wire:click="$set('isViewModalOpen', false)"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @php
                            $colorMap = [
                                'Leave' => ['bg' => '#e6f4ea', 'text' => '#1e8e3e', 'border' => '#fbbc04', 'headerBg' => '#fef7e0'],
                                'Attendance' => ['bg' => '#fef0d8', 'text' => '#ff8c00', 'border' => '#fbbc04', 'headerBg' => '#fef7e0'],
                                'default' => ['bg' => '#e8f0fe', 'text' => '#1967d2', 'border' => '#d2e3fc', 'headerBg' => '#f8f9fa'],
                            ];
                            $rolePermissions = $viewingRole->permissions->pluck('name')->toArray();
                        @endphp

                        @foreach($permissionGroups as $module => $actions)
                            @php 
                                $modulePerms = collect($actions)->map(fn($a) => strtolower($module) . '_' . strtolower($a))->toArray();
                                $hasAny = count(array_intersect($modulePerms, $rolePermissions)) > 0;
                            @endphp

                            @if($hasAny)
                                @php $colors = $colorMap[$module] ?? $colorMap['default']; @endphp
                                <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                                    <div class="card h-100" style="border: 1px solid {{ $colors['border'] }}; border-radius: 4px; box-shadow: none;">
                                        <div class="card-header d-flex justify-content-between align-items-center" style="background-color: {{ $colors['headerBg'] }}; border-bottom: 0; padding: 10px 15px;">
                                            <div class="d-flex align-items-center">
                                                @if($module == 'Leave')
                                                    <i class="bi bi-briefcase me-2 text-success"></i>
                                                @elseif($module == 'Attendance')
                                                    <i class="bi bi-clock me-2 text-warning"></i>
                                                @else
                                                    <i class="bi bi-grid me-2 text-primary"></i>
                                                @endif
                                                <span class="fw-bold text-uppercase" style="color: #333; font-size: 14px;">{{ $module }}</span>
                                            </div>
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($actions as $action)
                                                    @php $permName = strtolower($module) . '_' . strtolower($action); @endphp
                                                    @if(in_array($permName, $rolePermissions))
                                                        <span class="badge" style="background-color: {{ $colors['bg'] }}; color: {{ $colors['text'] }}; font-size: 13px; font-weight: 500; padding: 6px 10px;">
                                                            @if($action === 'viewAny') AnyView @elseif($action === 'viewOwn') OurView @elseif($action === 'viewTeam') TeamView @elseif($action === 'viewBranch') BranchView @else {{ $action }} @endif
                                                        </span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="$set('isViewModalOpen', false)">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
