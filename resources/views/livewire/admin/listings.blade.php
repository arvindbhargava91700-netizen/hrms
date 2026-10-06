<div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-buildings"></i></div>
                <div>
                    <div class="stat-label">Total Listings</div>
                    <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle text-success"></i></div>
                <div>
                    <div class="stat-label">Approved</div>
                    <div class="stat-value">{{ number_format($stats['approved'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="{{ ($stats['pending'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-hourglass-split text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending Approval</div>
                    <div class="stat-value">{{ number_format($stats['pending'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="{{ ($stats['rejected'] ?? 0) > 0 ? 'border-left: 4px solid var(--bs-danger);' : '' }}">
                <div class="stat-icon bg-danger-soft"><i class="bi bi-x-circle text-danger"></i></div>
                <div>
                    <div class="stat-label">Rejected</div>
                    <div class="stat-value">{{ number_format($stats['rejected'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header d-flex flex-column flex-sm-row gap-3">
            <div class="search-box" style="width:100%; max-width:300px;">
                <i class="bi bi-search search-icon"></i>
                <input type="text" class="form-control" wire:model.live="search" placeholder="Search title...">
            </div>
            <select class="form-select" wire:model.live="categoryFilter" style="width:100%; max-width:200px;">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select class="form-select" wire:model.live="statusFilter" style="width:100%; max-width:200px;">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="suspended">Suspended</option>
            </select>
            <div class="ms-sm-auto">
                <a href="{{ $this->exportUrl }}" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Listing</th>
                        <th>Category</th>
                        <th>Partner</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listings as $listing)
                        <tr>
                            <td class="align-middle">
                                <div class="fw-600">{{ $listing->title }}</div>
                                <div class="text-muted" style="font-size:12px;">
                                    <i class="bi bi-geo-alt me-1"></i>{{ Str::limit($listing->address, 30) }}
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge bg-light text-dark border">
                                    <i class="bi {{ $listing->category->icon ?? 'bi-tag' }} me-1"></i>
                                    {{ $listing->category->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $listing->partner->avatar_url ?? '' }}" class="rounded-circle flex-shrink-0" width="24" height="24">
                                    <span class="fs-14 fw-500 text-nowrap">{{ $listing->partner->name ?? 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="align-middle">
                                <span class="badge-status badge-{{ $listing->status }}">{{ ucfirst($listing->status) }}</span>
                            </td>
                            <td class="align-middle text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="viewDetails('{{ $listing->id }}')" title="View Details">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <a href="{{ route('admin.listing.details', $listing->id) }}" class="btn btn-sm btn-outline-success" title="Capacity Map">
                                        <i class="bi bi-map"></i> Map
                                    </a>
                                    <button class="btn btn-sm btn-outline-primary" wire:click="editListing('{{ $listing->id }}')" title="Edit">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <button class="btn btn-icon btn-outline-danger btn-sm"
                                        wire:click="deleteListing('{{ $listing->id }}')"
                                        wire:confirm="Delete this listing?"
                                        title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    @if($listing->status === 'pending')
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <button class="dropdown-item text-success fw-500"
                                                        wire:click="approve('{{ $listing->id }}')" wire:loading.attr="disabled" wire:target="approve('{{ $listing->id }}')">
                                                        <i class="bi bi-check-circle me-2"></i>Approve Listing
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger fw-500"
                                                        wire:click="reject('{{ $listing->id }}')" wire:loading.attr="disabled" wire:target="reject('{{ $listing->id }}')">
                                                        <i class="bi bi-x-circle me-2"></i>Reject Listing
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-shop fs-2 d-block mb-2"></i>No listings found
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($listings->hasPages())
            <div class="card-footer bg-transparent border-top p-3">
                {{ $listings->links() }}
            </div>
        @endif
    </div>

    {{-- Listing Detail Modal --}}
    @if($viewingListing)
    <div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="$set('viewingId', null)">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-0">{{ $viewingListing->title }}</h5>
                        <div class="text-muted small">
                            <i class="bi {{ $viewingListing->category->icon ?? 'bi-tag' }} me-1"></i>
                            {{ $viewingListing->category->name ?? '' }}
                            &nbsp;·&nbsp;
                            <i class="bi bi-geo-alt me-1"></i>{{ $viewingListing->address }}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge-status badge-{{ $viewingListing->status }}">{{ $viewingListing->status }}</span>
                        <button type="button" class="btn-close ms-2" wire:click="$set('viewingId', null)"></button>
                    </div>
                </div>
                <div class="modal-body">
                    
                    {{-- Quick Stats --}}
                    <div class="row g-3 mb-4">
                        <div class="col-sm-4">
                            <a href="{{ route('admin.subscriptions', ['listing' => $viewingListing->id]) }}" class="text-decoration-none">
                                <div class="card bg-light border-0 h-100 transition shadow-sm">
                                    <div class="card-body text-center py-3">
                                        <i class="bi bi-card-checklist fs-4 text-primary mb-1"></i>
                                        <div class="text-muted small mb-1">Subscriptions</div>
                                        <div class="fw-bold fs-5 text-dark">{{ $viewingListing->subscriptions_count ?? 0 }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-4">
                            <a href="{{ route('admin.visits', ['listing' => $viewingListing->id]) }}" class="text-decoration-none">
                                <div class="card bg-light border-0 h-100 transition shadow-sm">
                                    <div class="card-body text-center py-3">
                                        <i class="bi bi-calendar-check fs-4 text-info mb-1"></i>
                                        <div class="text-muted small mb-1">Visits</div>
                                        <div class="fw-bold fs-5 text-dark">{{ $viewingListing->visit_bookings_count ?? 0 }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-sm-4">
                            <a href="{{ route('admin.coupons', ['listing' => $viewingListing->id]) }}" class="text-decoration-none">
                                <div class="card bg-light border-0 h-100 transition shadow-sm">
                                    <div class="card-body text-center py-3">
                                        <i class="bi bi-ticket-perforated fs-4 text-warning mb-1"></i>
                                        <div class="text-muted small mb-1">Coupons</div>
                                        <div class="fw-bold fs-5 text-dark">{{ $viewingListing->coupons_count ?? 0 }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>

                    {{-- Images --}}
                    @if($viewingListing->images->count())
                    <div class="d-flex gap-2 mb-4 flex-wrap">
                        @foreach($viewingListing->images as $img)
                        <img src="{{ $img->url }}" class="rounded-3 shadow-sm" style="height:50px;object-fit:cover;width:50px;">
                        @endforeach
                    </div>
                    @endif

                    <div class="row g-4">
                        {{-- Basic Info --}}
                        <div class="col-12">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body p-4">
                                    <h6 class="fw-bold mb-4 text-dark" style="font-size: 1.1rem;">
                                        <i class="bi bi-info-square me-2 text-primary"></i>Basic Information
                                    </h6>
                                    
                                    <div class="row g-4">
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                    <i class="bi bi-person fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Partner</div>
                                                    <div class="fw-bold text-dark mt-1">{{ $viewingListing->partner->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                    <i class="bi bi-envelope fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Email</div>
                                                    <div class="fw-bold text-dark mt-1">{{ \App\Helpers\AdminHelper::maskContact('email', $viewingListing->partner->email) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                    <i class="bi bi-telephone fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Mobile</div>
                                                    <div class="fw-bold text-dark mt-1">{{ \App\Helpers\AdminHelper::maskContact('mobile', $viewingListing->partner->mobile) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                    <i class="bi bi-calendar3 fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Created On</div>
                                                    <div class="fw-bold text-dark mt-1">{{ $viewingListing->created_at->format('d M Y') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <hr class="my-1 border-light">
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-secondary bg-opacity-10 text-secondary rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                    <i class="bi bi-geo-alt fs-5"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing: 0.5px; font-size: 0.7rem;">Address</div>
                                                    <div class="fw-bold text-dark mt-1">{{ $viewingListing->address }}</div>
                                                    <div class="d-flex align-items-center gap-3 mt-2">
                                                        <div class="small"><span class="text-muted">Lat:</span> <span class="fw-medium">{{ $viewingListing->lat ?? '—' }}</span></div>
                                                        <div class="small"><span class="text-muted">Lng:</span> <span class="fw-medium">{{ $viewingListing->lng ?? '—' }}</span></div>
                                                        @if($viewingListing->lat && $viewingListing->lng)
                                                            <a href="https://maps.google.com/?q={{ $viewingListing->lat }},{{ $viewingListing->lng }}" target="_blank" class="text-primary small text-decoration-none fw-medium"><i class="bi bi-box-arrow-up-right me-1"></i>Map</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if($viewingListing->search_keywords)
                                        <div class="col-12">
                                            <div class="d-flex align-items-start gap-3">
                                                <div class="bg-secondary bg-opacity-10 text-secondary rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                                    <i class="bi bi-tags fs-5"></i>
                                                </div>
                                                <div>
                                                    <div class="text-muted small fw-semibold text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 0.7rem;">Search Keywords</div>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach(explode(',', $viewingListing->search_keywords) as $kw)
                                                            <span class="badge bg-light text-dark border px-2 py-1">{{ trim($kw) }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @if($viewingListing->description)
                                    <div class="mt-3 text-muted small">{{ $viewingListing->description }}</div>
                                    @endif

                                </div>
                            </div>
                        </div>

                        {{-- Custom Fields / Meta --}}
                        @if($viewingListing->meta->count())
                        <div class="col-12">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-700 mb-3"><i class="bi bi-list-check me-2 text-primary"></i>Details</h6>
                                    <table class="table table-sm mb-0">
                                        @foreach($viewingListing->meta as $m)
                                        <tr>
                                            <td class="text-muted fw-600" style="width:50%">{{ $m->customField?->label }}</td>
                                            <td>{{ $m->value ?: '—' }}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- ── Attendance Section ── --}}
                        @if($viewingListing->category->has_attendance)
                        <div class="col-12">
                            <div class="card border-0" style="background:linear-gradient(135deg,#eff6ff,#f8faff); border-left:3px solid #3b82f6 !important;">
                                <div class="card-body">
                                    <h6 class="fw-700 mb-3">
                                        <i class="bi bi-person-check-fill me-2 text-primary"></i>Customer Attendance
                                        <span class="badge bg-success ms-2" style="font-size:0.72rem;">Enabled</span>
                                    </h6>
                                    <div class="row g-3">
                                        {{-- GPS Gate --}}
                                        <div class="col-md-4">
                                            <div class="rounded-3 p-3 h-100" style="background:#fff;border:1px solid #dbeafe;">
                                                <div class="fw-600 mb-1" style="font-size:13px;"><i class="bi bi-geo-alt-fill text-primary me-1"></i>GPS Radius Gate</div>
                                                <div style="font-size:12px;"><span class="badge bg-primary">100 m</span> <span class="text-muted">radius required</span></div>
                                                @if($viewingListing->lat && $viewingListing->lng)
                                                <div class="text-muted mt-1" style="font-size:11px;"><i class="bi bi-pin-map me-1"></i>{{ number_format((float)$viewingListing->lat,6) }}, {{ number_format((float)$viewingListing->lng,6) }}</div>
                                                <a href="https://maps.google.com/?q={{ $viewingListing->lat }},{{ $viewingListing->lng }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 px-2 py-0" style="font-size:11px;"><i class="bi bi-map me-1"></i>View on Map</a>
                                                @else
                                                <div class="text-warning mt-1" style="font-size:11px;"><i class="bi bi-exclamation-triangle me-1"></i>No GPS coordinates set</div>
                                                @endif
                                            </div>
                                        </div>
                                        {{-- Time Window --}}
                                        <div class="col-md-4">
                                            <div class="rounded-3 p-3 h-100" style="background:#fff;border:1px solid #dbeafe;">
                                                <div class="fw-600 mb-2" style="font-size:13px;"><i class="bi bi-clock-fill text-primary me-1"></i>
                                                    @if($viewingListing->category->has_shifts) Shift Windows @else Attendance Window @endif
                                                </div>
                                                @if($viewingListing->category->has_shifts)
                                                    @if($viewingListing->shifts->count())
                                                    <div class="d-flex flex-column gap-1">
                                                        @foreach($viewingListing->shifts as $sh)
                                                        <div class="d-flex align-items-center justify-content-between" style="font-size:12px;">
                                                            <span class="fw-600 text-dark text-capitalize">{{ $sh->shift_label }}</span>
                                                            <span class="text-muted">{{ \Carbon\Carbon::parse($sh->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($sh->end_time)->format('h:i A') }}</span>
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @else
                                                    <div class="text-muted" style="font-size:12px;">No shifts configured yet.</div>
                                                    @endif
                                                    <div class="text-muted mt-2" style="font-size:11px;"><i class="bi bi-info-circle me-1"></i>Each customer is gated to their booked shift window.</div>
                                                @else
                                                    @if($viewingListing->opening_time && $viewingListing->closing_time)
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:12px;"><i class="bi bi-door-open me-1"></i>{{ \Carbon\Carbon::parse($viewingListing->opening_time)->format('h:i A') }}</span>
                                                        <i class="bi bi-arrow-right text-muted" style="font-size:11px;"></i>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:12px;"><i class="bi bi-door-closed me-1"></i>{{ \Carbon\Carbon::parse($viewingListing->closing_time)->format('h:i A') }}</span>
                                                    </div>
                                                    @else
                                                    <div class="text-warning" style="font-size:11px;"><i class="bi bi-exclamation-triangle me-1"></i>Opening/Closing time not set. <a href="{{ route('admin.listing.edit', $viewingListing->id) }}">Edit</a></div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        {{-- Late Policy --}}
                                        <div class="col-md-4">
                                            <div class="rounded-3 p-3 h-100" style="background:#fff;border:1px solid #dbeafe;">
                                                <div class="fw-600 mb-2" style="font-size:13px;"><i class="bi bi-alarm-fill text-primary me-1"></i>Late Policy</div>
                                                <div style="font-size:12px; color:#374151;">
                                                    <div class="d-flex align-items-start gap-2 mb-1">
                                                        <i class="bi bi-clock-history text-warning mt-1 flex-shrink-0"></i>
                                                        <span>Arriving <strong>after</strong> window start → marked <strong class="text-danger">Late</strong> + minutes recorded.</span>
                                                    </div>
                                                    <div class="d-flex align-items-start gap-2">
                                                        <i class="bi bi-x-circle text-danger mt-1 flex-shrink-0"></i>
                                                        <span>Punch-in <strong>blocked</strong> outside allowed window.</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Packages --}}
                        <div class="col-12">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <h6 class="fw-700 mb-3"><i class="bi bi-card-checklist me-2 text-primary"></i>Packages
                                        <span class="badge bg-primary ms-1">{{ $viewingListing->packages->count() }}</span>
                                    </h6>
                                    @if($viewingListing->packages->count())
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($viewingListing->packages as $pkg)
                                        <div class="rounded-3 bg-white border overflow-hidden">
                                            <div class="d-flex align-items-center justify-content-between px-3 py-2">
                                                <div>
                                                    <div class="fw-600 small">{{ $pkg->name }}</div>
                                                    <div class="text-muted" style="font-size:11px;">
                                                        {{ $pkg->duration_days }} days
                                                        · {{ ucfirst(str_replace('_',' ',$pkg->type)) }}
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="fw-700 text-success">₹{{ number_format($pkg->price) }}</div>
                                                    <button class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;" data-bs-toggle="collapse" data-bs-target="#admin-pkg-{{ $pkg->id }}">
                                                        View
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="collapse" id="admin-pkg-{{ $pkg->id }}">
                                                <div class="px-3 py-2 border-top bg-light" style="font-size:12px;">
                                                    <div class="row gx-3">
                                                        <div class="col-6 mb-1">
                                                            <span class="text-muted fw-600">Room:</span> 
                                                            @if($pkg->room)
                                                                {{ $pkg->room->room_number }} ({{ strtoupper($pkg->room->room_type) }})
                                                            @elseif($pkg->room_type)
                                                                {{ ucfirst($pkg->room_type) }}
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                        <div class="col-6 mb-1">
                                                            <span class="text-muted fw-600">Occupancy:</span> 
                                                            @if($pkg->occupancy_type === 'per_bed')
                                                                Per Bed
                                                            @elseif($pkg->occupancy_type === 'full_room')
                                                                Full Room
                                                            @else
                                                                {{ ucfirst(str_replace('_', ' ', $pkg->occupancy_type)) ?: 'N/A' }}
                                                            @endif
                                                        </div>
                                                        <div class="col-12 mb-1">
                                                            <span class="text-muted fw-600">Features:</span> 
                                                            @if(is_array($pkg->features) && count($pkg->features))
                                                                {{ implode(', ', $pkg->features) }}
                                                            @else
                                                                None
                                                            @endif
                                                        </div>
                                                        @if(is_array($pkg->meal_plans) && count($pkg->meal_plans))
                                                        <div class="col-12 mb-1">
                                                            <span class="text-muted fw-600">Meals:</span> 
                                                            @php
                                                                $mealsList = [];
                                                                if(!empty($pkg->meal_plans['has_breakfast'])) $mealsList[] = 'Breakfast';
                                                                if(!empty($pkg->meal_plans['has_lunch'])) $mealsList[] = 'Lunch';
                                                                if(!empty($pkg->meal_plans['has_dinner'])) $mealsList[] = 'Dinner';
                                                                $mType = $pkg->meal_plans['meal_type'] ?? 'none';
                                                            @endphp
                                                            @if(count($mealsList) > 0)
                                                                {{ implode(', ', $mealsList) }}
                                                                @if($mType !== 'none') <span class="text-muted">({{ ucfirst(str_replace('_', '-', $mType)) }})</span> @endif
                                                            @else
                                                                None
                                                            @endif
                                                        </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @else
                                    <p class="text-muted small mb-0">No packages available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- ── Subscriptions (per package) ── --}}
                        @include('partials.listing-subscriptions')

                        {{-- GYM: Shifts --}}
                        @if($viewingListing->shifts->count())
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-700 mb-3"><i class="bi bi-clock me-2 text-primary"></i>Gym Shifts</h6>
                                    <div class="row g-3">
                                        @foreach($viewingListing->shifts as $shift)
                                        <div class="col-md-4">
                                            <div class="card border shadow-none">
                                                <div class="card-body py-3">
                                                    <div class="fw-700">{{ $shift->shift_label }}</div>
                                                    <div class="text-muted small">{{ $shift->start_time }} – {{ $shift->end_time }}</div>
                                                    <div class="mt-2 d-flex gap-2">
                                                        <span class="badge bg-primary-subtle text-primary">Max {{ $shift->max_members }}</span>
                                                        <span class="badge bg-success-subtle text-success">₹{{ number_format($shift->fee) }}/mo</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- GYM: Trainers --}}
                        @if($viewingListing->trainers->count())
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-700 mb-3"><i class="bi bi-person-badge me-2 text-primary"></i>Trainers</h6>
                                    <div class="row g-3">
                                        @foreach($viewingListing->trainers as $trainer)
                                        <div class="col-md-3 col-6">
                                            <div class="card border shadow-none text-center h-100">
                                                <div class="card-body py-3">
                                                    <div class="mb-3">
                                                        @php
                                                            $tPhotos = json_decode($trainer->photo, true);
                                                            if (!is_array($tPhotos)) $tPhotos = [$trainer->photo];
                                                            $tPhotos = array_filter($tPhotos);
                                                        @endphp
                                                        @if(count($tPhotos) > 0)
                                                            <div id="adminCarouselTrainer{{ $trainer->id }}" class="carousel slide" data-bs-ride="carousel">
                                                                <div class="carousel-inner rounded" style="height: 120px;">
                                                                    @foreach($tPhotos as $i => $p)
                                                                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }} h-100">
                                                                            <img src="{{ asset('storage/' . $p) }}" class="d-block w-100 h-100" style="object-fit:cover;" alt="Trainer Photo">
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                                @if(count($tPhotos) > 1)
                                                                    <button class="carousel-control-prev" type="button" data-bs-target="#adminCarouselTrainer{{ $trainer->id }}" data-bs-slide="prev">
                                                                        <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: invert(1) grayscale(100); width: 20px; height: 20px;"></span>
                                                                        <span class="visually-hidden">Previous</span>
                                                                    </button>
                                                                    <button class="carousel-control-next" type="button" data-bs-target="#adminCarouselTrainer{{ $trainer->id }}" data-bs-slide="next">
                                                                        <span class="carousel-control-next-icon" aria-hidden="true" style="filter: invert(1) grayscale(100); width: 20px; height: 20px;"></span>
                                                                        <span class="visually-hidden">Next</span>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height: 120px;">
                                                                <i class="bi bi-person text-secondary fs-1"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="fw-600 small">{{ $trainer->name }}</div>
                                                    <div class="text-muted" style="font-size:11px;">{{ $trainer->specialization }}</div>
                                                    <span class="badge bg-secondary-subtle text-secondary mt-1">{{ $trainer->experience_years }}yr exp</span>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- PG/ROOM: Floors & Rooms --}}
                        @if($viewingListing->floors->count())
                        <div class="col-12">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-700 mb-3"><i class="bi bi-layers me-2 text-primary"></i>Floors & Rooms</h6>
                                    @foreach($viewingListing->floors as $floor)
                                    <div class="mb-3">
                                        <div class="fw-600 text-primary mb-2">
                                            <i class="bi bi-layers me-1"></i>{{ $floor->name }}
                                            <span class="badge bg-primary ms-2">{{ $floor->rooms->count() }} rooms</span>
                                        </div>
                                        @if($floor->rooms->count())
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0 bg-white">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Room#</th>
                                                        <th>Type</th>
                                                        <th>Capacity</th>
                                                        <th>Photos</th>
                                                        <th>Available</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($floor->rooms as $room)
                                                    <tr>
                                                        <td class="fw-600">{{ $room->room_number }}</td>
                                                        <td>{{ ucfirst($room->room_type ?? 'N/A') }}</td>
                                                        <td>{{ $room->capacity }}</td>
                                                        <td>
                                                            @if($room->images && $room->images->count() > 0)
                                                                <div class="d-flex gap-1 flex-wrap">
                                                                    @foreach($room->images as $rImg)
                                                                        <img src="{{ $rImg->url }}" class="rounded" style="width:50px; height:50px; object-fit:cover;">
                                                                    @endforeach
                                                                </div>
                                                            @else
                                                                <span class="text-muted small">No photos</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $room->available_beds > 0 ? 'bg-success' : 'bg-danger' }}">
                                                                {{ $room->available_beds }} free
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @else
                                        <p class="text-muted small">No rooms on this floor.</p>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    @if($viewingListing->status === 'pending')
                    <button class="btn btn-success" wire:click="approve('{{ $viewingListing->id }}')" wire:loading.attr="disabled" wire:target="approve('{{ $viewingListing->id }}')">
                        <span wire:loading wire:target="approve('{{ $viewingListing->id }}')" class="ft-btn-spinner"></span>
                        <i class="bi bi-check-lg me-2" wire:loading.remove wire:target="approve('{{ $viewingListing->id }}')"></i>
                        <span wire:loading.remove wire:target="approve('{{ $viewingListing->id }}')">Approve</span>
                    </button>
                    <button class="btn btn-danger" wire:click="reject('{{ $viewingListing->id }}')" wire:loading.attr="disabled" wire:target="reject('{{ $viewingListing->id }}')">
                        <span wire:loading wire:target="reject('{{ $viewingListing->id }}')" class="ft-btn-spinner"></span>
                        <i class="bi bi-x-lg me-2" wire:loading.remove wire:target="reject('{{ $viewingListing->id }}')"></i>
                        <span wire:loading.remove wire:target="reject('{{ $viewingListing->id }}')">Reject</span>
                    </button>
                    @endif
                    <button class="btn btn-primary" wire:click="editListing('{{ $viewingListing->id }}')">
                        <i class="bi bi-pencil me-2"></i>Edit Listing
                    </button>
                    <button type="button" class="btn btn-outline-secondary" wire:click="$set('viewingId', null)">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
