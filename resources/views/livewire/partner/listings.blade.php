<div>

    <div class="card mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="mb-0">My Listings</h5>

            @if(auth()->user()->isPartner() || auth()->user()->canAccess('listing_create'))
            <button class="btn btn-primary" wire:click="openCreate">

                <i class="bi bi-plus-lg me-2"></i>New Listing

            </button>
            @endif

        </div>

        @if(session('error'))

            <div class="alert alert-danger mb-0 m-3">

                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}

            </div>

        @endif

        <div class="table-responsive">

            <table class="table table-feetrack mb-0">

                <thead>

                    <tr>

                        <th>Title</th>

                        <th>Category</th>

                        <th>Address</th>

                        <th>Status</th>

                        <th class="text-end">Actions</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($listings as $listing)

                        <tr>

                            <td class="fw-600">{{ $listing->title }}</td>

                            <td>

                                <span class="badge bg-light text-dark border mb-1 d-inline-block">

                                    @php $catIcon = $listing->category->icon ?? 'bi-tag'; @endphp
                                    @if(\Illuminate\Support\Str::startsWith($catIcon, 'bi-'))
                                        <i class="bi {{ $catIcon }} me-1"></i>
                                    @else
                                        <img src="{{ asset('storage/' . $catIcon) }}" alt="icon" style="width: 18px; height: 18px; vertical-align: text-bottom; object-fit: contain;" class="me-1">
                                    @endif

                                    {{ $listing->category->name ?? 'N/A' }}

                                </span>

                                @if($listing->gender_type)

                                    <br>

                                    <span class="badge bg-info text-white">

                                        @if($listing->gender_type === 'boys') 🏠 Boys PG

                                        @elseif($listing->gender_type === 'girls') 🏠 Girls PG

                                        @elseif($listing->gender_type === 'co-living') 🏠 Co-Living PG

                                        @endif

                                    </span>

                                @endif

                            </td>

                            <td class="text-muted fs-14" style="max-width:200px;">

                                <div class="text-truncate">{{ $listing->address }}</div>

                            </td>

                            <td>

                                <span class="badge-status badge-{{ $listing->status }}">{{ $listing->status }}</span>

                            </td>

                            <td class="text-end">

                                <button class="btn btn-icon btn-outline-info btn-sm ms-1"
                                    wire:click="viewListing('{{ $listing->id }}')" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <a href="{{ route('partner.listing.details', $listing->id) }}" class="btn btn-icon btn-outline-success btn-sm ms-1" title="Capacity Map">
                                    <i class="bi bi-map"></i>
                                </a>

                                @if(in_array($listing->status, ['draft', 'approved', 'rejected', 'pending']) && (auth()->user()->isPartner() || auth()->user()->canAccess('listing_update')))
                                <button class="btn btn-icon btn-outline-primary btn-sm"
                                    wire:click="editListing('{{ $listing->id }}')" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                @endif

                                @if($listing->status === 'draft' && (auth()->user()->isPartner() || auth()->user()->canAccess('listing_delete')))
                                <button class="btn btn-icon btn-outline-danger btn-sm ms-1"
                                    wire:click="deleteListing('{{ $listing->id }}')"
                                    wire:confirm="Delete this draft listing?"
                                    title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5" class="text-center py-5 text-muted">

                                <i class="bi bi-shop fs-2 d-block mb-2"></i>

                                No listings found. Click "New Listing" to get started.

                            </td>

                        </tr>

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



    {{-- ── Listing Detail Modal ──────────────────────────────────── --}}

    @if($viewingListing)

    <div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="$set('viewingId', null)">

        <div class="modal-dialog modal-xl modal-dialog-scrollable">

            <div class="modal-content">



                {{-- Header --}}

                <div class="modal-header">

                    <div>

                        <h5 class="modal-title mb-1">{{ $viewingListing->title }}</h5>

                        <div class="text-muted small">

                            @php $viewCatIcon = $viewingListing->category->icon ?? 'bi-tag'; @endphp
                            @if(\Illuminate\Support\Str::startsWith($viewCatIcon, 'bi-'))
                                <i class="bi {{ $viewCatIcon }} me-1"></i>
                            @else
                                <img src="{{ asset('storage/' . $viewCatIcon) }}" alt="icon" style="width: 18px; height: 18px; vertical-align: text-bottom; object-fit: contain;" class="me-1">
                            @endif

                            {{ $viewingListing->category->name ?? '' }}

                            @if($viewingListing->gender_type)

                                &nbsp;·&nbsp;

                                <span class="badge bg-info text-white">

                                    @if($viewingListing->gender_type === 'boys') 🏠 Boys PG

                                    @elseif($viewingListing->gender_type === 'girls') 🏠 Girls PG

                                    @elseif($viewingListing->gender_type === 'co-living') 🏠 Co-Living PG

                                    @endif

                                </span>

                            @endif

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



                    {{-- Images Carousel --}}

                    @if($viewingListing->images->count())

                    <div class="mb-4">

                        <div class="d-flex gap-2 flex-wrap">

                            @foreach($viewingListing->images as $img)

                            <img src="{{ $img->url }}" alt="{{ $img->caption }}"

                                class="rounded-3 shadow-sm"

                                style="height:120px;width:120px;object-fit:cover;">

                            @endforeach

                        </div>

                    </div>

                    @endif



                    {{-- Description --}}

                    @if($viewingListing->description)

                    <div class="alert alert-info mb-4 py-3">

                        <i class="bi bi-info-circle me-2"></i>{{ $viewingListing->description }}

                    </div>

                    @endif



                    <div class="card border-0 mb-4 bg-white shadow-sm rounded-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-4 text-dark" style="font-size: 1.1rem;">
                                <i class="bi bi-geo-alt me-2 text-primary"></i>Listing Location & Details
                            </h6>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
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
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="bg-secondary bg-opacity-10 text-secondary rounded d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                            <i class="bi bi-tags fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small fw-semibold text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 0.7rem;">Search Keywords</div>
                                            <div class="d-flex flex-wrap gap-2">
                                                @if($viewingListing->search_keywords)
                                                    @foreach(explode(',', $viewingListing->search_keywords) as $kw)
                                                        <span class="badge bg-light text-dark border px-2 py-1">{{ trim($kw) }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted small">None added</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    {{-- ── Attendance Section ─────────────────────────── --}}
                    @if($viewingListing->category->has_attendance)
                    <div class="card border-0 mb-4" style="background: linear-gradient(135deg,#eff6ff,#f8faff); border-left: 3px solid #3b82f6 !important;">
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
                                        <div style="font-size:12px;">
                                            <span class="badge bg-primary">100 m</span>
                                            <span class="text-muted ms-1">radius required</span>
                                        </div>
                                        @if($viewingListing->lat && $viewingListing->lng)
                                        <div class="text-muted mt-2" style="font-size:11px;">
                                            <i class="bi bi-pin-map me-1"></i>{{ number_format((float)$viewingListing->lat,6) }}, {{ number_format((float)$viewingListing->lng,6) }}
                                        </div>
                                        <a href="https://maps.google.com/?q={{ $viewingListing->lat }},{{ $viewingListing->lng }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 px-2 py-0" style="font-size:11px;">
                                            <i class="bi bi-map me-1"></i>View on Map
                                        </a>
                                        @else
                                        <div class="text-warning mt-2" style="font-size:11px;"><i class="bi bi-exclamation-triangle me-1"></i>No GPS set</div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Time Window --}}
                                <div class="col-md-4">
                                    <div class="rounded-3 p-3 h-100" style="background:#fff;border:1px solid #dbeafe;">
                                        <div class="fw-600 mb-2" style="font-size:13px;"><i class="bi bi-clock-fill text-primary me-1"></i>
                                            @if($viewingListing->category->has_shifts) Attendance Window @else Opening / Closing @endif
                                        </div>
                                        @if($viewingListing->category->has_shifts)
                                            {{-- Shift-based: show each shift window --}}
                                            @if($viewingListing->shifts->count())
                                            <div class="d-flex flex-column gap-1">
                                                @foreach($viewingListing->shifts as $sh)
                                                <div class="d-flex align-items-center justify-content-between" style="font-size:12px;">
                                                    <span class="fw-600 text-dark text-capitalize">{{ $sh->shift_name }}</span>
                                                    <span class="text-muted">{{ \Carbon\Carbon::parse($sh->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($sh->end_time)->format('h:i A') }}</span>
                                                </div>
                                                @endforeach
                                            </div>
                                            @else
                                            <div class="text-muted" style="font-size:12px;">No shifts yet — add from <strong>Shifts</strong> tab.</div>
                                            @endif
                                            <div class="text-muted mt-2" style="font-size:11px;"><i class="bi bi-info-circle me-1"></i>Each customer is gated to their booked shift window.</div>
                                        @else
                                            @if($viewingListing->opening_time && $viewingListing->closing_time)
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:12px;">
                                                    <i class="bi bi-door-open me-1"></i>{{ \Carbon\Carbon::parse($viewingListing->opening_time)->format('h:i A') }}
                                                </span>
                                                <i class="bi bi-arrow-right text-muted" style="font-size:11px;"></i>
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:12px;">
                                                    <i class="bi bi-door-closed me-1"></i>{{ \Carbon\Carbon::parse($viewingListing->closing_time)->format('h:i A') }}
                                                </span>
                                            </div>
                                            @else
                                            <div class="text-warning" style="font-size:11px;"><i class="bi bi-exclamation-triangle me-1"></i>Opening/Closing time not set. <a href="{{ route('partner.listing.edit', $viewingListing->id) }}">Edit</a></div>
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
                                                <span>Customers arriving <strong>after</strong> the window start are marked <strong class="text-danger">Late</strong> with exact minutes recorded.</span>
                                            </div>
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi bi-x-circle text-danger mt-1 flex-shrink-0"></i>
                                                <span>Punch-in is <strong>blocked</strong> outside the allowed time window.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    @endif



                    <div class="row g-4">



                        {{-- ── GYM: Shifts ── --}}

                        @if($viewingListing->shifts->count())

                        <div class="col-12">

                            <div class="card border-0" style="background:#f8fafc;">

                                <div class="card-body">

                                    <h6 class="fw-700 mb-3">

                                        <i class="bi bi-clock-history me-2 text-primary"></i>Shifts

                                    </h6>

                                    <table class="table table-sm bg-white border">

                                        <thead>

                                            <tr>

                                                <th>Shift</th>

                                                <th>Time</th>

                                                <th>Capacity</th>

                                                <th>Fee</th>

                                            </tr>

                                        </thead>

                                        <tbody>

                                            @foreach($viewingListing->shifts as $shift)

                                            <tr>

                                                <td class="text-capitalize">{{ $shift->shift_name }}</td>

                                                <td>{{ $shift->start_time }} – {{ $shift->end_time }}</td>

                                                <td>{{ $shift->max_members }} members</td>

                                                <td>₹{{ number_format($shift->fee) }}</td>

                                            </tr>

                                            @endforeach

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>

                        @endif



                        {{-- ── GYM: Trainers ── --}}

                        @if($viewingListing->trainers->count())

                        <div class="col-12">

                            <div class="card border-0" style="background:#f8fafc;">

                                <div class="card-body">

                                    <h6 class="fw-700 mb-3">

                                        <i class="bi bi-person-badge me-2 text-primary"></i>Trainers

                                        <span class="badge bg-primary ms-1">{{ $viewingListing->trainers->count() }}</span>

                                    </h6>

                                    <div class="row g-3">

                                        @foreach($viewingListing->trainers as $trainer)

                                        <div class="col-6 col-md-3">

                                            <div class="card border shadow-none text-center h-100">

                                                <div class="card-body py-3">

                                                    <div class="mb-3">

                                                        @php

                                                            $tPhotos = json_decode($trainer->photo, true);

                                                            if (!is_array($tPhotos)) $tPhotos = [$trainer->photo];

                                                            $tPhotos = array_filter($tPhotos); // Remove empty values

                                                        @endphp

                                                        @if(count($tPhotos) > 0)

                                                            <div id="carouselTrainer{{ $trainer->id }}" class="carousel slide" data-bs-ride="carousel">

                                                                <div class="carousel-inner rounded" style="height: 120px;">

                                                                    @foreach($tPhotos as $i => $p)

                                                                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }} h-100">

                                                                            <img src="{{ asset('storage/' . $p) }}" class="d-block w-100 h-100" style="object-fit:cover;" alt="Trainer Photo">

                                                                        </div>

                                                                    @endforeach

                                                                </div>

                                                                @if(count($tPhotos) > 1)

                                                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselTrainer{{ $trainer->id }}" data-bs-slide="prev">

                                                                        <span class="carousel-control-prev-icon" aria-hidden="true" style="filter: invert(1) grayscale(100); width: 20px; height: 20px;"></span>

                                                                        <span class="visually-hidden">Previous</span>

                                                                    </button>

                                                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselTrainer{{ $trainer->id }}" data-bs-slide="next">

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

                                                    <div class="text-muted" style="font-size:11px;">

                                                        {{ $trainer->specialization ?? 'General Fitness' }}

                                                    </div>

                                                    <span class="badge bg-secondary-subtle text-secondary mt-1" style="font-size:10px;">

                                                        {{ $trainer->experience_years }} yr exp

                                                    </span>

                                                </div>

                                            </div>

                                        </div>

                                        @endforeach

                                    </div>

                                </div>

                            </div>

                        </div>

                        @endif



                        {{-- ── PG/ROOM: Floors & Rooms ── --}}

                        @if($viewingListing->floors->count())

                        <div class="col-12">

                            <div class="card border-0" style="background:#f8fafc;">

                                <div class="card-body">

                                    <h6 class="fw-700 mb-3">

                                        <i class="bi bi-layers me-2 text-primary"></i>Floors & Rooms

                                    </h6>

                                    @foreach($viewingListing->floors as $floor)

                                    <div class="mb-4">

                                        <div class="d-flex align-items-center gap-2 mb-2">

                                            <span class="badge bg-primary">{{ $floor->name }}</span>

                                            <span class="text-muted small">{{ $floor->rooms->count() }} rooms</span>

                                        </div>

                                        @if($floor->rooms->count())

                                        <div class="table-responsive">

                                            <table class="table table-sm table-bordered bg-white mb-0">

                                                <thead class="table-light">

                                                    <tr>

                                                        <th>Room #</th>

                                                        <th>Type</th>

                                                        <th>Seats</th>

                                                        <th>Photos</th>

                                                        <th>Available Beds</th>

                                                    </tr>

                                                </thead>

                                                <tbody>

                                                    @foreach($floor->rooms as $room)

                                                    <tr>

                                                        <td class="fw-700">{{ $room->room_number }}</td>

                                                        <td>

                                                            <span class="badge bg-secondary-subtle text-secondary text-capitalize">

                                                                {{ $room->room_type ?? 'N/A' }}

                                                            </span>

                                                        </td>

                                                        <td>{{ $room->capacity }}</td>

                                                        <td>

                                                            <div class="d-flex flex-wrap gap-1">

                                                                @foreach($room->images as $img)

                                                                <img src="{{ $img->url }}" class="rounded border" style="width:50px;height:50px;object-fit:cover;" alt="Room Photo">

                                                                @endforeach

                                                                @if($room->images->count() === 0)

                                                                <span class="text-muted small">No photos</span>

                                                                @endif

                                                            </div>

                                                        </td>

                                                        <td>

                                                            <span class="badge {{ $room->available_beds > 0 ? 'bg-success' : 'bg-danger' }}">

                                                                {{ $room->available_beds }} / {{ $room->capacity }} free

                                                            </span>

                                                        </td>

                                                    </tr>

                                                    @endforeach

                                                </tbody>

                                            </table>

                                        </div>

                                        @else

                                        <p class="text-muted small">No rooms added to this floor yet.</p>

                                        @endif

                                    </div>

                                    @endforeach

                                </div>

                            </div>

                        </div>

                        @endif



                        {{-- ── Custom Field Details ── --}}

                        @if($viewingListing->meta->count())

                        <div class="col-12">

                            <div class="card border-0 h-100" style="background:#f8fafc;">

                                <div class="card-body">

                                    <h6 class="fw-700 mb-3">

                                        <i class="bi bi-list-check me-2 text-primary"></i>Details

                                    </h6>

                                    <table class="table table-sm mb-0">

                                        @foreach($viewingListing->meta as $m)

                                        <tr>

                                            <td class="text-muted fw-600" style="width:50%">

                                                {{ $m->customField?->label }}

                                            </td>

                                            <td class="fw-500">

                                                @if($m->value === '1') <span class="badge bg-success">Yes</span>

                                                @elseif($m->value === '0') <span class="badge bg-danger">No</span>

                                                @else {{ $m->value ?: '—' }}

                                                @endif

                                            </td>

                                        </tr>

                                        @endforeach

                                    </table>

                                </div>

                            </div>

                        </div>

                        @endif



                        {{-- ── Packages ── --}}
                        <div class="col-12">
                            <div class="card border-0 h-100" style="background:#f8fafc;">
                                <div class="card-body">
                                    <h6 class="fw-700 mb-3">
                                        <i class="bi bi-card-checklist me-2 text-primary"></i>Packages
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
                                                    <button class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px;" data-bs-toggle="collapse" data-bs-target="#partner-pkg-{{ $pkg->id }}">
                                                        View
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="collapse" id="partner-pkg-{{ $pkg->id }}">
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



                    </div>{{-- end row --}}

                </div>



                {{-- Footer --}}

                <div class="modal-footer gap-2">

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



    {{-- ── Category Picker Modal ──────────────────────────────────── --}}

    @if($showCategoryPicker)

    <div class="modal d-block" style="background:rgba(0,0,0,0.5);">

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header border-0 pb-0">

                    <h5 class="modal-title">Choose Service Type</h5>

                    <button type="button" class="btn-close" wire:click="$set('showCategoryPicker', false)"></button>

                </div>

                <div class="modal-body">

                    <p class="text-muted small mb-3">Select a category — different categories have tailored forms.</p>

                    <style>
                        .category-card-3d {
                            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                            transform-style: preserve-3d;
                            perspective: 1000px;
                        }
                        .category-card-3d:hover {
                            transform: translateY(-5px) rotateX(5deg) rotateY(-5deg);
                            box-shadow: 0 15px 25px rgba(0,0,0,0.1) !important;
                            border-color: var(--bs-primary) !important;
                        }
                        .category-card-3d .cat-img-3d, .category-card-3d .cat-icon-3d {
                            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                        }
                        .category-card-3d:hover .cat-img-3d {
                            transform: translateZ(30px) scale(1.15);
                            filter: drop-shadow(0 12px 10px rgba(0,0,0,0.15));
                        }
                        .category-card-3d:hover .cat-icon-3d {
                            transform: translateZ(30px) scale(1.15);
                            text-shadow: 0 12px 10px rgba(0,0,0,0.15);
                        }
                    </style>
                    <div class="row g-2 mb-4">

                        @foreach($categories as $cat)

                        <div class="col-6">

                            <div class="card border cursor-pointer category-card-3d {{ $pickedCategoryId == $cat->id ? 'border-primary bg-primary-subtle' : '' }}"

                                wire:click="$set('pickedCategoryId', '{{ $cat->id }}')"

                                style="cursor:pointer;">

                                <div class="card-body text-center py-3">

                                    @php $modalCatIcon = $cat->icon ?? 'bi-grid'; @endphp
                                    @if(\Illuminate\Support\Str::startsWith($modalCatIcon, 'bi-'))
                                        <i class="bi {{ $modalCatIcon }} text-primary d-block mb-2 cat-icon-3d" style="font-size: 2rem;"></i>
                                    @else
                                        <img src="{{ asset('storage/' . $modalCatIcon) }}" alt="icon" style="height: 40px; width: auto; object-fit: contain;" class="d-block mb-2 mx-auto cat-img-3d">
                                    @endif

                                    <div class="fw-600 small">{{ $cat->name }}</div>

                                    @if($cat->has_shifts || $cat->has_trainers)

                                        <div class="badge bg-warning text-dark mt-1" style="font-size:10px;">Shifts & Staff</div>

                                    @elseif($cat->has_rooms)

                                        <div class="badge bg-info text-white mt-1" style="font-size:10px;">Floors & Rooms</div>

                                    @endif

                                </div>

                            </div>

                        </div>

                        @endforeach

                    </div>

                    @error('pickedCategoryId') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                </div>

                <div class="modal-footer border-0 pt-0">

                    <button type="button" class="btn btn-outline-secondary" wire:click="$set('showCategoryPicker', false)">Cancel</button>

                    <button type="button" class="btn btn-primary" wire:click="proceedWithCategory">

                        <i class="bi bi-arrow-right me-2"></i>Continue

                    </button>

                </div>

            </div>

        </div>

    </div>

    @endif



</div>

