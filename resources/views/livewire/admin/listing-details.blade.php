<div>
<style>
    .ld-hero {
        background: linear-gradient(to right, rgba(15,40,75,0.95) 0%, rgba(26,60,94,0.85) 35%, rgba(30,70,110,0.55) 65%, rgba(20,50,90,0.15) 100%),
                    url('/images/hero_banner.png') right center / cover no-repeat;
        border-radius: 16px; padding: 28px 32px; color: #fff;
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 24px; overflow: hidden; position: relative; min-height: 110px;
    }
    .ld-hero img.hero-thumb { width: 180px; height: 110px; object-fit: cover; border-radius: 12px; flex-shrink: 0; box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
    .ld-hero .hero-placeholder { width: 180px; height: 110px; border-radius: 12px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ld-filter-bar { background: #fff; border: 1px solid #e5e9f0; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
    .ld-legend { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
    .ld-legend-item { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #374151; }
    .ld-legend-dot { width: 12px; height: 12px; border-radius: 50%; flex-shrink: 0; }
    .ld-stats-card { background: #fff; border: 1px solid #e5e9f0; border-radius: 12px; padding: 14px 12px; display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 14px; color: #374151; }
    .ld-stats-card .stat-num { font-size: 24px; font-weight: 700; color: #1a3c5e; line-height: 1; }
    .ld-floor-block { background: #fff; border: 1px solid #e5e9f0; border-radius: 12px; margin-bottom: 16px; overflow: hidden; }
    .ld-floor-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; background: #f8f9fb; border-bottom: 1px solid #e5e9f0; cursor: pointer; user-select: none; }
    .ld-floor-header .floor-title { display: flex; align-items: center; gap: 10px; font-weight: 600; font-size: 15px; color: #1a3c5e; }
    .ld-floor-header .floor-count { font-size: 13px; color: #6b7280; font-weight: 400; }
    .ld-rooms-grid { display: flex; flex-wrap: wrap; gap: 10px; padding: 18px 20px; }
    .ld-room-chip { width: 80px; height: 70px; border-radius: 10px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; cursor: pointer; border: 2px solid transparent; transition: all 0.18s ease; }
    .ld-room-chip:hover { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,0.12); }
    .ld-room-chip.available { background: #f0fdf4; border-color: #bbf7d0; }
    .ld-room-chip.booked { background: #fff5f5; border-color: #fecaca; }
    .ld-room-chip.selected { box-shadow: 0 0 0 3px #3b82f6; }
    .ld-room-chip .room-num { font-weight: 700; font-size: 14px; color: #111827; }
    .ld-room-chip .room-status-dot { display: flex; align-items: center; gap: 4px; font-size: 11px; }
    .ld-room-chip .room-status-dot .dot { width: 7px; height: 7px; border-radius: 50%; }
    .dot-green { background: #22c55e; }
    .dot-red   { background: #ef4444; }
    .dot-gray  { background: #9ca3af; }
    .ld-detail-panel { background: #fff; border: 1px solid #e5e9f0; border-radius: 12px; overflow: hidden; }
    .ld-detail-panel .panel-img-placeholder { width: 100%; height: 150px; background: linear-gradient(135deg, #e5e9f0, #cfd8e8); display: flex; align-items: center; justify-content: center; }
    .ld-detail-panel .panel-body { padding: 16px; }
    .panel-info-row { display: flex; align-items: center; gap: 10px; font-size: 13px; color: #6b7280; padding: 5px 0; border-bottom: 1px solid #f3f4f6; }
    .panel-info-row:last-child { border-bottom: none; }
    .panel-info-row i { color: #3b82f6; width: 16px; text-align: center; }
    .panel-price { font-size: 18px; font-weight: 700; color: #1a3c5e; margin-top: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6; }
    @media (max-width: 991px) {
        .ld-hero { padding: 20px 18px; min-height: auto; border-radius: 12px; }
        .ld-hero img.hero-thumb, .ld-hero .hero-placeholder { width: 90px; height: 70px; border-radius: 8px; }
        .ld-filter-bar { padding: 12px 14px; gap: 10px; }
        .ld-legend { gap: 10px; }
        .ld-legend-item { font-size: 11px; }
        .ld-stats-card { padding: 10px 8px; gap: 6px; flex-direction: column; text-align: center; }
        .ld-stats-card .stat-num { font-size: 20px; }
        .ld-rooms-grid { padding: 12px; gap: 8px; }
        .ld-room-chip { width: 70px; height: 62px; }
        .mobile-panel-first { order: -1; margin-bottom: 16px; }
    }
</style>

    {{-- ── Hero Banner ─────────────────────────────────── --}}
    <div class="ld-hero">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('admin.listings') }}" class="btn btn-sm btn-light opacity-80" style="font-size:12px;">
                    <i class="bi bi-arrow-left me-1"></i>Back
                </a>
            </div>
            <h1 class="fw-bold fs-3 mb-1 mt-2">{{ $listing->title }}</h1>
            <p class="mb-2 opacity-75" style="font-size:14px;">Manage rooms, shifts, and real-time availability for this listing.</p>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge" style="background:rgba(255,255,255,0.25); font-size:12px;">
                    <i class="bi bi-tag me-1"></i>{{ $listing->category->name ?? 'N/A' }}
                </span>
                <span class="badge {{ $listing->status === 'approved' ? 'bg-success' : ($listing->status === 'pending' ? 'bg-warning text-dark' : 'bg-secondary') }}" style="font-size:12px;">
                    {{ ucfirst($listing->status) }}
                </span>
                @if($listing->partner)
                <span class="opacity-75" style="font-size:12px;"><i class="bi bi-person me-1"></i>{{ $listing->partner->name }}</span>
                @endif
                @if($listing->address)
                <span class="opacity-75" style="font-size:12px;"><i class="bi bi-geo-alt me-1"></i>{{ $listing->city }}, {{ $listing->state }}</span>
                @endif
            </div>
        </div>
        @if($listing->images->count())
            <img src="{{ $listing->images->first()->url ?? '' }}" alt="Listing" class="hero-thumb d-none d-md-block">
        @else
            <div class="hero-placeholder d-none d-md-flex">
                <i class="bi bi-building fs-1 opacity-50"></i>
            </div>
        @endif
    </div>

    {{-- ── Attendance Info Card (shown when category has attendance enabled) --}}
    @if($listing->category->has_attendance)
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #3b82f6 !important; background: linear-gradient(135deg, #eff6ff 0%, #f8faff 100%);">
        <div class="card-body py-3 px-4">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:34px;height:34px;background:#dbeafe;">
                    <i class="bi bi-person-check-fill text-primary" style="font-size:16px;"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark" style="font-size:15px;">Customer Attendance</div>
                    <div class="text-muted" style="font-size:12px;">Rules applied when customers mark attendance via app</div>
                </div>
                <span class="badge bg-success ms-auto">Enabled</span>
            </div>

            <div class="row g-3">

                {{-- GPS / Location --}}
                <div class="col-md-4">
                    <div class="rounded-3 p-3 h-100" style="background:#fff; border:1px solid #dbeafe;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-geo-alt-fill text-primary"></i>
                            <span class="fw-600" style="font-size:13px;">GPS Radius Gate</span>
                        </div>
                        <div class="mb-1" style="font-size:13px;">
                            <span class="badge bg-primary">100 m</span>
                            <span class="text-muted ms-1">radius required to punch-in / out</span>
                        </div>
                        @if($listing->lat && $listing->lng)
                        <div class="text-muted mt-2" style="font-size:11px;">
                            <i class="bi bi-pin-map me-1"></i>
                            {{ number_format((float)$listing->lat, 6) }}, {{ number_format((float)$listing->lng, 6) }}
                        </div>
                        <a href="https://maps.google.com/?q={{ $listing->lat }},{{ $listing->lng }}" target="_blank"
                           class="btn btn-sm btn-outline-primary mt-2" style="font-size:11px; padding:2px 10px;">
                            <i class="bi bi-map me-1"></i>View on Map
                        </a>
                        @else
                        <div class="alert alert-warning py-1 mt-2 mb-0" style="font-size:11px;">
                            <i class="bi bi-exclamation-triangle me-1"></i>No GPS coordinates set — customers cannot verify location.
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Attendance Window --}}
                <div class="col-md-4">
                    <div class="rounded-3 p-3 h-100" style="background:#fff; border:1px solid #dbeafe;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-clock-fill text-primary"></i>
                            <span class="fw-600" style="font-size:13px;">
                                @if($listing->category->has_shifts) Facility Hours @else Attendance Window @endif
                            </span>
                        </div>
                        @if($listing->opening_time && $listing->closing_time)
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:12px;">
                                <i class="bi bi-door-open me-1"></i>{{ \Carbon\Carbon::parse($listing->opening_time)->format('h:i A') }}
                            </span>
                            <i class="bi bi-arrow-right text-muted" style="font-size:11px;"></i>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size:12px;">
                                <i class="bi bi-door-closed me-1"></i>{{ \Carbon\Carbon::parse($listing->closing_time)->format('h:i A') }}
                            </span>
                        </div>
                        @if($listing->category->has_shifts)
                        <div class="text-muted mt-2" style="font-size:11px;">
                            <i class="bi bi-info-circle me-1"></i>Each shift has its own individual attendance window (see shifts below).
                        </div>
                        @else
                        <div class="text-muted mt-2" style="font-size:11px;">
                            <i class="bi bi-info-circle me-1"></i>Customers must punch-in within this window. Late minutes are calculated from opening time.
                        </div>
                        @endif
                        @else
                        <div class="alert alert-warning py-1 mb-0" style="font-size:11px;">
                            <i class="bi bi-exclamation-triangle me-1"></i>No opening/closing time set.
                            <a href="{{ route('admin.listing.edit', $listing->id) }}">Edit listing</a> to add.
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Shift Windows (Gym) or Late Policy (PG/Room) --}}
                <div class="col-md-4">
                    <div class="rounded-3 p-3 h-100" style="background:#fff; border:1px solid #dbeafe;">
                        @if($listing->category->has_shifts && $listing->shifts->count())
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-calendar3-week-fill text-primary"></i>
                            <span class="fw-600" style="font-size:13px;">Shift Windows</span>
                        </div>
                        <div class="d-flex flex-column gap-1">
                            @foreach($listing->shifts as $sh)
                            <div class="d-flex align-items-center justify-content-between" style="font-size:12px;">
                                <span class="fw-600 text-dark">{{ $sh->shift_label }}</span>
                                <span class="text-muted">{{ \Carbon\Carbon::parse($sh->start_time)->format('h:i A') }} – {{ \Carbon\Carbon::parse($sh->end_time)->format('h:i A') }}</span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-alarm-fill text-primary"></i>
                            <span class="fw-600" style="font-size:13px;">Late Policy</span>
                        </div>
                        <div style="font-size:12px; color:#374151;">
                            <div class="d-flex align-items-start gap-2 mb-1">
                                <i class="bi bi-clock-history text-warning mt-1"></i>
                                <span>Customers arriving after opening time are marked <strong>Late</strong> with exact minutes recorded.</span>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-x-circle text-danger mt-1"></i>
                                <span>Punch-in is <strong>blocked</strong> before opening time and after closing time.</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
    @endif

    {{-- ── Two-column layout: floors LEFT, panel RIGHT ─── --}}
    <div class="row g-4">

        {{-- LEFT: Floor list (first in DOM → left on desktop) --}}
        <div class="{{ $listing->category->has_rooms ? 'col-lg-8' : 'col-12' }}">

            @if($listing->floors->count())
            <div class="ld-filter-bar">
                <div class="d-flex align-items-center gap-3 flex-grow-1 flex-wrap">
                    <div>
                        <label class="form-label fw-500 mb-1" style="font-size:12px; color:#6b7280;">Select Floor</label>
                        <select id="floorFilter" class="form-select form-select-sm" style="min-width:150px;">
                            <option value="all">All Floors</option>
                            @foreach($listing->floors as $floor)
                            <option value="floor-{{ $floor->id }}">{{ $floor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="ld-legend">
                    <div class="ld-legend-item"><span class="ld-legend-dot dot-green"></span><div><div class="fw-600" style="font-size:12px;">Available</div><div style="font-size:11px;color:#9ca3af;">Room is free to book</div></div></div>
                    <div class="ld-legend-item"><span class="ld-legend-dot dot-red"></span><div><div class="fw-600" style="font-size:12px;">Booked</div><div style="font-size:11px;color:#9ca3af;">Room is already booked</div></div></div>
                    <div class="ld-legend-item"><span class="ld-legend-dot dot-gray"></span><div><div class="fw-600" style="font-size:12px;">Maintenance</div><div style="font-size:11px;color:#9ca3af;">Under maintenance</div></div></div>
                </div>
            </div>

            @php
                $totalRooms = $listing->floors->sum(fn($f) => $f->rooms->count());
                $bookedRooms = 0;
                foreach($listing->floors as $f) {
                    foreach($f->rooms as $r) {
                        $enrolled = $r->subscriptions->sum(fn($s) => max(1, (int)$s->beds_booked));
                        $cap = max(1, (int)$r->capacity);
                        if($enrolled >= $cap) $bookedRooms++;
                    }
                }
                $availableRooms = $totalRooms - $bookedRooms;
            @endphp
            <div class="row g-3 mb-4">
                <div class="col-4"><div class="ld-stats-card text-center"><i class="bi bi-building fs-4 text-primary"></i><div><div class="stat-num">{{ $totalRooms }}</div><div style="font-size:12px;color:#6b7280;">Total Rooms</div></div></div></div>
                <div class="col-4"><div class="ld-stats-card text-center"><i class="bi bi-check-circle fs-4 text-success"></i><div><div class="stat-num text-success">{{ $availableRooms }}</div><div style="font-size:12px;color:#6b7280;">Available</div></div></div></div>
                <div class="col-4"><div class="ld-stats-card text-center"><i class="bi bi-x-circle fs-4 text-danger"></i><div><div class="stat-num text-danger">{{ $bookedRooms }}</div><div style="font-size:12px;color:#6b7280;">Booked</div></div></div></div>
            </div>

            <h5 class="fw-bold mb-3" style="color:#1a3c5e;">Floor Wise Room Details</h5>

            @foreach($listing->floors as $floor)
            <div class="ld-floor-block floor-block" data-floor-id="floor-{{ $floor->id }}">
                <div class="ld-floor-header" onclick="toggleFloor(this)">
                    <div class="floor-title">
                        <i class="bi bi-building-fill text-primary"></i>
                        {{ $floor->name }}
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="floor-count">{{ $floor->rooms->count() }} Rooms</span>
                        <i class="bi bi-chevron-up toggle-icon"></i>
                    </div>
                </div>
                <div class="floor-body">
                    <div class="ld-rooms-grid">
                        @forelse($floor->rooms as $room)
                        @php
                            $roomEnrolled = $room->subscriptions->sum(fn($s) => max(1, (int)$s->beds_booked));
                            $capacity = max(1, (int)$room->capacity);
                            $isBooked = $roomEnrolled >= $capacity;
                            $imageUrl = $room->images->first()->url ?? '';
                            $priceText = $room->per_bed_rent > 0 ? '₹'.number_format($room->per_bed_rent).'/bed' : ($room->full_rent > 0 ? '₹'.number_format($room->full_rent).'/room' : '');
                        @endphp
                        <div class="ld-room-chip {{ $isBooked ? 'booked' : 'available' }}"
                             onclick="selectRoom(this, '{{ $room->room_number }}', '{{ $room->room_type ?? 'Standard' }}', {{ $capacity }}, {{ $roomEnrolled }}, '{{ $isBooked ? 'Booked' : 'Available' }}', '{{ $floor->name }}', '{{ $imageUrl }}', '{{ $priceText }}')"
                             title="{{ $room->room_number }} — {{ $isBooked ? 'Full' : ($roomEnrolled.'/'.$capacity.' beds booked') }}">
                            <div class="room-num">{{ $room->room_number }}</div>
                            <div class="room-status-dot">
                                <span class="dot {{ $isBooked ? 'dot-red' : 'dot-green' }}"></span>
                                <span style="font-size:11px; color:{{ $isBooked ? '#ef4444' : '#22c55e' }}; font-weight:500;">{{ $isBooked ? 'Booked' : 'Available' }}</span>
                            </div>
                        </div>
                        @empty
                        <p class="text-muted small py-2 px-1">No rooms on this floor.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
            @endif

            @if($listing->shifts->count())
            @php
                $totalMembers = $listing->shifts->sum('max_members');
                $totalEnrolled = 0;
                foreach($listing->shifts as $sh) {
                    $totalEnrolled += $sh->subscriptions->sum(fn($s) => max(1, (int)$s->beds_booked));
                }
            @endphp
            <div class="d-flex align-items-center gap-4 flex-wrap mb-3">
                <div class="ld-legend">
                    <div class="ld-legend-item"><span class="ld-legend-dot dot-green"></span><div><div class="fw-600" style="font-size:12px;">Available</div></div></div>
                    <div class="ld-legend-item"><span class="ld-legend-dot dot-red"></span><div><div class="fw-600" style="font-size:12px;">Full</div></div></div>
                </div>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-4"><div class="ld-stats-card justify-content-center text-center"><i class="bi bi-people fs-4 text-primary"></i><div><div class="stat-num">{{ $totalMembers }}</div><div style="font-size:12px;color:#6b7280;">Total Capacity</div></div></div></div>
                <div class="col-4"><div class="ld-stats-card justify-content-center text-center"><i class="bi bi-person-check fs-4 text-success"></i><div><div class="stat-num text-success">{{ $totalEnrolled }}</div><div style="font-size:12px;color:#6b7280;">Enrolled</div></div></div></div>
                <div class="col-4"><div class="ld-stats-card justify-content-center text-center"><i class="bi bi-person-dash fs-4 text-warning"></i><div><div class="stat-num text-warning">{{ max(0,$totalMembers-$totalEnrolled) }}</div><div style="font-size:12px;color:#6b7280;">Free Slots</div></div></div></div>
            </div>
            <h5 class="fw-bold mb-3" style="color:#1a3c5e;">Shift Wise Details</h5>
            @foreach($listing->shifts as $shift)
            @php
                $shiftEnrolled = $shift->subscriptions->sum(fn($s) => max(1, (int)$s->beds_booked));
                $shiftFree = max(0, $shift->max_members - $shiftEnrolled);
                $shiftFull = $shiftFree <= 0;
            @endphp
            <div class="ld-floor-block mb-3">
                <div class="ld-floor-header" onclick="toggleFloor(this)">
                    <div class="floor-title">
                        @php $icons = ['morning'=>'bi-sunrise','afternoon'=>'bi-sun','evening'=>'bi-sunset','night'=>'bi-moon-stars']; @endphp
                        <i class="bi {{ $icons[$shift->shift_name] ?? 'bi-clock' }} text-primary"></i>
                        {{ ucfirst($shift->shift_name) }} Shift
                        <span class="text-muted fw-normal" style="font-size:13px;">{{ $shift->start_time }} – {{ $shift->end_time }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $shiftFull ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">{{ $shiftEnrolled }}/{{ $shift->max_members }} Members</span>
                        <i class="bi bi-chevron-up toggle-icon"></i>
                    </div>
                </div>
                <div class="floor-body">
                    <div class="ld-rooms-grid">
                        @foreach($shift->subscriptions as $sub)
                            @for($i = 0; $i < max(1, (int)$sub->beds_booked); $i++)
                            <div class="ld-room-chip booked" title="Member: {{ $sub->customer->name ?? 'Guest' }}">
                                <i class="bi bi-person-fill" style="font-size:18px; color:#ef4444;"></i>
                                <div class="room-num" style="font-size:11px;">{{ explode(' ', $sub->customer->name ?? 'Guest')[0] }}</div>
                                <div class="room-status-dot"><span class="dot dot-red"></span></div>
                            </div>
                            @endfor
                        @endforeach
                        @for($i = 0; $i < $shiftFree; $i++)
                        <div class="ld-room-chip available">
                            <i class="bi bi-person" style="font-size:18px; color:#22c55e; opacity:0.6;"></i>
                            <div class="room-num" style="font-size:11px;">Free</div>
                            <div class="room-status-dot"><span class="dot dot-green"></span></div>
                        </div>
                        @endfor
                    </div>
                    <div class="px-4 pb-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted" style="font-size:13px;">Monthly Fee</span>
                            <span class="fw-bold fs-5" style="color:#1a3c5e;">₹{{ number_format($shift->fee) }}<span class="fw-normal text-muted fs-6">/mo</span></span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            @endif

            @if(!$listing->shifts->count() && !$listing->floors->count())
            <div class="text-center py-5">
                <i class="bi bi-box fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold text-muted">No Rooms or Shifts</h5>
                <p class="text-muted">This listing does not have rooms or shifts configured yet.</p>
            </div>
            @endif

            @if(!$listing->category->has_rooms)
            <div class="mt-4 text-end">
                <a href="{{ route('admin.listing.edit', $listing->id) }}" class="btn btn-primary px-4 py-2" style="border-radius:10px;">
                    <i class="bi bi-pencil me-2"></i>Edit Listing
                </a>
            </div>
            @endif
        </div>

        {{-- RIGHT: Room Detail Panel (second in DOM → right on desktop, top on mobile via CSS order) --}}
        @if($listing->category->has_rooms)
        <div class="col-lg-4 mobile-panel-first">
            <div style="position:sticky; top:90px;">
            <div class="ld-detail-panel" id="roomDetailPanel">
                <div class="panel-img-placeholder" id="panelImageWrap">
                    <img id="panelImg" src="" style="width:100%;height:100%;object-fit:cover;display:none;">
                    <i id="panelImgIcon" class="bi bi-building fs-1" style="color:#94a3b8;"></i>
                </div>
                <div class="panel-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <h5 class="fw-bold mb-0" style="color:#1a3c5e;" id="panelRoomName">Select a Room</h5>
                            <div class="text-muted" style="font-size:13px;" id="panelFloorName">Click any room to see details</div>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary" id="panelStatusBadge">—</span>
                    </div>
                    <div id="panelDetails">
                        <div class="panel-info-row"><i class="bi bi-layers"></i><span id="panelFloor">—</span></div>
                        <div class="panel-info-row"><i class="bi bi-door-open"></i><span id="panelType">—</span></div>
                        <div class="panel-info-row"><i class="bi bi-people"></i><span id="panelCapacity">—</span></div>
                        <div class="panel-info-row"><i class="bi bi-person-check"></i><span id="panelOccupied">—</span></div>
                    </div>
                    <div class="mt-3" id="panelBedsWrap" style="display:none;">
                        <div class="fw-600 mb-2" style="font-size:13px; color:#374151;">Bed Slots</div>
                        <div id="panelBedsGrid" class="d-flex flex-wrap gap-2"></div>
                    </div>
                    <div class="panel-price" id="panelPrice" style="display:none;"></div>
                    <div id="panelEmpty" class="text-center py-4">
                        <i class="bi bi-cursor fs-2 text-muted d-block mb-2"></i>
                        <p class="text-muted" style="font-size:13px;">Click a room chip on the left to see its details here.</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('admin.listing.edit', $listing->id) }}" class="btn btn-primary w-100 mt-3">
                <i class="bi bi-pencil me-2"></i>Edit Listing
            </a>
            </div>
        </div>
        @endif

    </div>
</div>

<script>
function toggleFloor(header) {
    const body = header.nextElementSibling;
    const icon = header.querySelector('.toggle-icon');
    if (body.style.display === 'none') {
        body.style.display = '';
        icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
    } else {
        body.style.display = 'none';
        icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
    }
}
function selectRoom(el, roomNum, roomType, capacity, occupied, status, floorName, imageUrl, priceText) {
    document.querySelectorAll('.ld-room-chip').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    const available = capacity - occupied;
    const isBooked = status === 'Booked';
    document.getElementById('panelRoomName').textContent = 'Room ' + roomNum;
    document.getElementById('panelFloorName').textContent = floorName;
    document.getElementById('panelStatusBadge').textContent = status;
    document.getElementById('panelStatusBadge').className = 'badge ' + (isBooked ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success');
    document.getElementById('panelFloor').textContent = floorName;
    document.getElementById('panelType').textContent = roomType + ' Room';
    document.getElementById('panelCapacity').textContent = capacity + ' Beds Total';
    document.getElementById('panelOccupied').textContent = occupied + ' Occupied, ' + available + ' Available';
    document.getElementById('panelEmpty').style.display = 'none';
    document.getElementById('panelBedsWrap').style.display = '';
    if (priceText) {
        document.getElementById('panelPrice').textContent = priceText;
        document.getElementById('panelPrice').style.display = '';
    } else {
        document.getElementById('panelPrice').style.display = 'none';
    }
    if (imageUrl) {
        document.getElementById('panelImg').src = imageUrl;
        document.getElementById('panelImg').style.display = 'block';
        document.getElementById('panelImgIcon').style.display = 'none';
        document.getElementById('panelImageWrap').style.background = 'none';
    } else {
        document.getElementById('panelImg').style.display = 'none';
        document.getElementById('panelImgIcon').style.display = '';
        document.getElementById('panelImageWrap').style.background = 'linear-gradient(135deg, #e5e9f0, #cfd8e8)';
    }
    const grid = document.getElementById('panelBedsGrid');
    grid.innerHTML = '';
    for (let i = 0; i < occupied; i++) {
        grid.innerHTML += `<div style="width:38px;height:38px;border-radius:8px;background:#fee2e2;border:2px solid #fca5a5;display:flex;align-items:center;justify-content:center;" title="Occupied"><i class="bi bi-person-fill" style="color:#ef4444;font-size:16px;"></i></div>`;
    }
    for (let i = 0; i < available; i++) {
        grid.innerHTML += `<div style="width:38px;height:38px;border-radius:8px;background:#f0fdf4;border:2px dashed #86efac;display:flex;align-items:center;justify-content:center;" title="Available"><i class="bi bi-person" style="color:#22c55e;font-size:16px;opacity:0.6;"></i></div>`;
    }
}
document.getElementById('floorFilter')?.addEventListener('change', function() {
    const val = this.value;
    document.querySelectorAll('.floor-block').forEach(block => {
        block.style.display = (val === 'all' || block.dataset.floorId === val) ? '' : 'none';
    });
});
document.addEventListener('DOMContentLoaded', function() {
    const firstChip = document.querySelector('.ld-room-chip[onclick]');
    if (firstChip) firstChip.click();
});
</script>
