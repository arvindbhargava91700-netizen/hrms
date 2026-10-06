<div>
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0 fw-bold" style="color: var(--text-primary);">Notice Board</h5>
            <p class="text-muted small mb-0">Manage global and personal notices</p>
        </div>
        @if(Auth::user()->canAccess('notice_create'))
        <button class="btn btn-primary d-flex align-items-center gap-2 px-4 rounded-3 shadow-sm" wire:click="createNotice">
            <i class="bi bi-plus-lg"></i> New Notice
        </button>
        @endif
    </div>

    {{-- Filters --}}
    @if(auth()->user()->canAccess('notice_viewAny') || auth()->user()->canAccess('notice_viewteam'))
    <div class="mb-4">
        @include('partials.hrms-filters', ['viewAnyPermission' => 'notice_viewAny'])
    </div>
    @endif

    {{-- Notice Cards Grid --}}
    <div class="row g-4">
        @forelse ($notices as $notice)
            @php
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
            @endphp
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 rounded-4 shadow-sm overflow-hidden position-relative">
                    {{-- Colored Top Bar --}}
                    <div class="bg-{{ $typeConfig['class'] }}" style="height:4px;"></div>

                    <div class="card-body p-4 d-flex flex-column">
                        {{-- Header Row --}}
                        <div class="d-flex align-items-start justify-content-between mb-3 gap-2">
                            <span class="badge rounded-pill d-inline-flex align-items-center gap-1 px-3 py-2 fw-semibold text-{{ $typeConfig['class'] }} bg-{{ $typeConfig['class'] }}-subtle border border-{{ $typeConfig['class'] }}-subtle" style="font-size:.72rem;">
                                <i class="bi {{ $typeConfig['icon'] }}"></i>
                                {{ $typeConfig['label'] }}
                                @if($targetCount !== null) &bull; {{ $targetCount }} @endif
                            </span>
                            @if(Auth::user()->canAccess('notice_update') || Auth::user()->canAccess('notice_delete'))
                            <div class="d-flex gap-1 flex-shrink-0">
                                @if(Auth::user()->canAccess('notice_update'))
                                <button wire:click="editNotice({{ $notice->id }})" class="btn btn-sm btn-light border-0 text-primary rounded-3 px-2 py-1" title="Edit">
                                    <i class="bi bi-pencil-fill" style="font-size:.8rem;"></i>
                                </button>
                                @endif
                                @if(Auth::user()->canAccess('notice_delete'))
                                <button wire:click="deleteNotice({{ $notice->id }})" class="btn btn-sm btn-light border-0 text-danger rounded-3 px-2 py-1" title="Delete"
                                    onclick="confirm('Delete this notice?') || event.stopImmediatePropagation()">
                                    <i class="bi bi-trash3-fill" style="font-size:.8rem;"></i>
                                </button>
                                @endif
                            </div>
                            @endif
                        </div>

                        {{-- Title --}}
                        <h6 class="fw-bold mb-2" style="font-size:1rem; line-height:1.4; color:var(--text-primary);">{{ $notice->title }}</h6>

                        {{-- Content --}}
                        <p class="text-muted mb-3 flex-grow-1" style="font-size:.875rem; white-space:pre-line; line-height:1.6;">{{ Str::limit($notice->content, 120) }}</p>

                        {{-- Footer --}}
                        <div class="mt-auto">
                            @if($notice->start_date || $notice->end_date)
                            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                <span class="badge {{ $isExpired ? 'text-danger bg-danger-subtle border border-danger-subtle' : 'text-success bg-success-subtle border border-success-subtle' }} rounded-3 px-2 py-1" style="font-size:.72rem;">
                                    <i class="bi {{ $isExpired ? 'bi-calendar-x' : 'bi-calendar-check' }} me-1"></i>{{ $isExpired ? 'Expired' : 'Active' }}
                                </span>
                                @if($notice->start_date && $notice->end_date)
                                <span class="text-muted" style="font-size:.75rem;">
                                    {{ \Carbon\Carbon::parse($notice->start_date)->format('d M') }} – {{ \Carbon\Carbon::parse($notice->end_date)->format('d M, Y') }}
                                </span>
                                @endif
                            </div>
                            @endif

                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                <span class="text-muted d-flex align-items-center gap-1" style="font-size:.75rem;">
                                    <i class="bi bi-clock"></i> {{ $notice->created_at->diffForHumans() }}
                                </span>
                                @if($notice->action_link)
                                <a href="{{ $notice->action_link }}" target="_blank" rel="noopener noreferrer"
                                    class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" style="font-size:.78rem;">
                                    <i class="bi bi-box-arrow-up-right me-1"></i>{{ $notice->action_text ?: 'View' }}
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
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
        @endforelse
    </div>

    {{-- Create / Edit Modal --}}
    @if($isModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(10,10,30,0.55); z-index:1055; backdrop-filter:blur(2px); overflow-y:auto;">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width:860px; margin: 1.75rem auto;">
            <div class="modal-content border-0 rounded-4 overflow-hidden" style="box-shadow:var(--shadow-lg);">

                {{-- Modal Header --}}
                <div class="modal-header border-0 px-4 pt-4 pb-3" style="background:var(--primary);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px; height:42px; background:rgba(255,255,255,0.18);">
                            <i class="bi bi-megaphone-fill text-white fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-white mb-0">{{ $editingId ? 'Edit Notice' : 'Create New Notice' }}</h5>
                            <p class="mb-0 text-white-50 small">{{ $editingId ? 'Update the notice details below' : 'Fill in the details to broadcast a notice' }}</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto" wire:click="$set('isModalOpen', false)"></button>
                </div>

                <form wire:submit.prevent="saveNotice">
                    <div class="modal-body p-0" style="overflow:visible; max-height:none;">
                        <div class="row g-0" style="min-height:0;">
                            {{-- LEFT PANEL --}}
                            <div class="col-lg-7 p-4 border-end bg-white" style="overflow-y:auto; max-height:70vh;">

                                {{-- Title --}}
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-1">Notice Title <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.defer="title" class="form-control rounded-3" placeholder="e.g. Weekly Team Sync / Policy Update" style="font-size:.95rem;">
                                    @error('title') <span class="text-danger small mt-1 d-block"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span> @enderror
                                </div>

                                {{-- Type as clickable tiles --}}
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-2">Notice Type</label>
                                    <div class="row g-2">
                                        @foreach([
                                            ['val' => 'global',     'icon' => 'bi-globe2',       'label' => 'Global',     'desc' => 'All employees',  'cls' => 'primary'],
                                            ['val' => 'branch',     'icon' => 'bi-building',     'label' => 'Branch',     'desc' => 'Branch-wise',    'cls' => 'info'],
                                            ['val' => 'department', 'icon' => 'bi-diagram-3',    'label' => 'Department', 'desc' => 'Dept-wise',      'cls' => 'warning'],
                                            ['val' => 'employee',   'icon' => 'bi-person-check', 'label' => 'Employee',   'desc' => 'Specific staff', 'cls' => 'success'],
                                        ] as $opt)
                                        <div class="col-6">
                                            <label style="cursor:pointer; display:block;">
                                                <input type="radio" wire:model.live="type" value="{{ $opt['val'] }}" class="d-none">
                                                <div class="rounded-3 p-3 d-flex align-items-center gap-3 border-2"
                                                     style="border: 2px solid {{ $type === $opt['val'] ? 'var(--'.$opt['cls'].')' : '#e2e8f0' }};
                                                            background: {{ $type === $opt['val'] ? '#f0f7ff' : '#f8fafc' }};
                                                            transition: all .15s ease;">
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 text-white"
                                                         style="width:36px; height:36px; background: {{ $type === $opt['val'] ? 'var(--'.$opt['cls'].')' : '#cbd5e1' }}; transition: background .15s;">
                                                        <i class="bi {{ $opt['icon'] }}" style="font-size:.85rem;"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold small" style="color: {{ $type === $opt['val'] ? 'var(--primary)' : 'var(--text-primary)' }};">{{ $opt['label'] }}</div>
                                                        <div class="text-muted" style="font-size:.72rem;">{{ $opt['desc'] }}</div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                    @error('type') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                </div>

                                {{-- Employee selector --}}
                                @if(in_array($type, ['personal', 'employee']))
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="form-label fw-semibold small text-uppercase text-muted mb-0">Target Employees <span class="text-danger">*</span></label>
                                        <span class="badge rounded-pill bg-primary" style="font-size:.72rem;">{{ count($user_ids) }} selected</span>
                                    </div>
                                    <div class="input-group mb-2 rounded-3 overflow-hidden border">
                                        <span class="input-group-text bg-white border-0"><i class="bi bi-search text-muted small"></i></span>
                                        <input type="text" class="form-control border-0 ps-0" wire:model.live.debounce.150ms="employeeSearch" placeholder="Search name, email, code...">
                                        @if(!empty($employeeSearch))
                                        <button type="button" class="btn btn-light border-0" wire:click="$set('employeeSearch', '')"><i class="bi bi-x"></i></button>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold text-primary" style="font-size:.75rem;" wire:click="selectAllFilteredEmployees">Select All</button>
                                            <span class="text-muted" style="font-size:.75rem;">|</span>
                                            <button type="button" class="btn btn-link p-0 text-decoration-none fw-bold text-danger" style="font-size:.75rem;" wire:click="deselectAllEmployees">Deselect All</button>
                                        </div>
                                        <span class="text-muted" style="font-size:.73rem;">{{ count($this->filteredEmployees) }} found</span>
                                    </div>
                                    <div class="border rounded-3 bg-white" style="max-height:175px; overflow-y:auto;">
                                        @forelse($this->filteredEmployees as $emp)
                                            @php
                                                $eId    = is_array($emp) ? $emp['id'] : $emp->id;
                                                $eName  = is_array($emp) ? $emp['name'] : $emp->name;
                                                $eCode  = is_array($emp) ? ($emp['employee_code'] ?? '') : ($emp->employee_code ?? '');
                                                $eEmail = is_array($emp) ? ($emp['email'] ?? '') : ($emp->email ?? '');
                                                $isSel  = in_array($eId, $user_ids);
                                            @endphp
                                            <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom"
                                                 style="cursor:pointer; background:{{ $isSel ? 'var(--primary-light)' : 'transparent' }}; transition:background .1s;"
                                                 wire:click="toggleEmployee('{{ $eId }}')">
                                                <div class="d-flex align-items-center gap-2 text-truncate">
                                                    <input class="form-check-input flex-shrink-0 m-0" type="checkbox" value="{{ $eId }}" wire:model="user_ids" onclick="event.stopPropagation();">
                                                    <div class="text-truncate">
                                                        <span class="fw-semibold small" style="color:var(--text-primary);">{{ $eName }}</span>
                                                        @if($eCode) <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1" style="font-size:.68rem;">{{ $eCode }}</span> @endif
                                                    </div>
                                                </div>
                                                <span class="text-muted text-truncate ms-2 flex-shrink-0" style="font-size:.72rem; max-width:120px;">{{ $eEmail }}</span>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-muted small">
                                                <i class="bi bi-person-x d-block fs-4 mb-1"></i>No employees found
                                            </div>
                                        @endforelse
                                    </div>
                                    @error('user_ids') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                </div>
                                @endif

                                {{-- Branch selector --}}
                                @if($type === 'branch')
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-2">Target Branches <span class="text-danger">*</span></label>
                                    <div class="border rounded-3 bg-white" style="max-height:160px; overflow-y:auto;">
                                        @forelse($branches as $branch)
                                        <label class="d-flex align-items-center gap-3 px-3 py-2 border-bottom m-0" style="cursor:pointer;">
                                            <input class="form-check-input flex-shrink-0 m-0" type="checkbox" value="{{ $branch->id }}" wire:model="branch_ids">
                                            <div>
                                                <div class="fw-semibold small" style="color:var(--text-primary);">{{ $branch->name }}</div>
                                                @if($branch->address) <div class="text-muted" style="font-size:.72rem;">{{ $branch->address }}</div> @endif
                                            </div>
                                        </label>
                                        @empty
                                            <div class="text-center py-4 text-muted small"><i class="bi bi-building d-block fs-4 mb-1"></i>No branches available</div>
                                        @endforelse
                                    </div>
                                    @error('branch_ids') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                </div>
                                @endif

                                {{-- Department selector --}}
                                @if($type === 'department')
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-2">Target Departments <span class="text-danger">*</span></label>
                                    <div class="border rounded-3 bg-white" style="max-height:160px; overflow-y:auto;">
                                        @forelse($departments as $dept)
                                        <label class="d-flex align-items-center gap-3 px-3 py-2 border-bottom m-0" style="cursor:pointer;">
                                            <input class="form-check-input flex-shrink-0 m-0" type="checkbox" value="{{ $dept->id }}" wire:model="department_ids">
                                            <div class="fw-semibold small" style="color:var(--text-primary);">{{ $dept->name }}</div>
                                        </label>
                                        @empty
                                            <div class="text-center py-4 text-muted small"><i class="bi bi-diagram-3 d-block fs-4 mb-1"></i>No departments available</div>
                                        @endforelse
                                    </div>
                                    @error('department_ids') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                </div>
                                @endif

                                {{-- Duration --}}
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-2">Notice Duration</label>
                                    <div class="d-flex align-items-center gap-3 p-3 rounded-3 border mb-2" style="background:#f8fafc;">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" wire:model.live="is_unlimited" id="isUnlimited" style="width:2.2rem; height:1.2rem;">
                                        </div>
                                        <label class="form-check-label small fw-semibold" for="isUnlimited" style="color:var(--text-primary);">
                                            {{ $is_unlimited ? 'No expiry – unlimited duration' : 'Set a specific validity period' }}
                                        </label>
                                    </div>
                                    @if(!$is_unlimited)
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <label class="form-label text-muted small">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" wire:model="start_date" class="form-control rounded-3">
                                            @error('start_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted small">End Date <span class="text-danger">*</span></label>
                                            <input type="date" wire:model="end_date" class="form-control rounded-3">
                                            @error('end_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                {{-- Content --}}
                                <div>
                                    <label class="form-label fw-semibold small text-uppercase text-muted mb-1">Notice Content <span class="text-danger">*</span></label>
                                    <textarea wire:model.defer="content" rows="4" class="form-control rounded-3" placeholder="Write your notice content here..." style="font-size:.95rem; resize:none;"></textarea>
                                    @error('content') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            {{-- RIGHT PANEL --}}
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
                                    @error('action_link') <span class="text-danger small mt-1 d-block"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</span> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label text-muted fw-semibold small text-uppercase mb-1">Button Label</label>
                                    <input type="text" wire:model="action_text" placeholder="e.g. Join Now, Register, View Details" class="form-control rounded-3 border mb-2">
                                    @error('action_text') <span class="text-danger small">{{ $message }}</span> @enderror

                                    <label class="text-muted mb-2 d-block" style="font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px;">Quick Presets</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        @foreach(['Join Now' => 'bi-camera-video', 'Subscribe' => 'bi-bell', 'Register' => 'bi-pencil-square', 'View Details' => 'bi-eye'] as $label => $icon)
                                        <button type="button" class="btn btn-sm border rounded-pill bg-white px-3" style="font-size:.78rem;" wire:click="setQuickActionText('{{ $label }}')">
                                            <i class="bi {{ $icon }} me-1 text-primary"></i>{{ $label }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Live preview --}}
                                @if($action_link || $action_text)
                                <div class="p-3 rounded-3 border bg-white mb-3">
                                    <p class="text-muted mb-2" style="font-size:.72rem; font-weight:600; text-transform:uppercase; letter-spacing:.4px;">Preview</p>
                                    <a href="#" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold" style="font-size:.82rem;">
                                        <i class="bi bi-box-arrow-up-right me-1"></i>{{ $action_text ?: 'View' }}
                                    </a>
                                </div>
                                @endif

                                {{-- Push Notification hint --}}
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

                    {{-- Footer --}}
                    <div class="modal-footer border-top px-4 py-3 bg-white">
                        <button type="button" class="btn btn-light rounded-3 px-4" wire:click="$set('isModalOpen', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary px-5 rounded-3 fw-semibold shadow-sm" wire:loading.attr="disabled" wire:target="saveNotice">
                            <i class="bi bi-send me-2" wire:loading.remove wire:target="saveNotice"></i>
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" wire:loading wire:target="saveNotice"></span>
                            {{ $editingId ? 'Update Notice' : 'Publish Notice' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
