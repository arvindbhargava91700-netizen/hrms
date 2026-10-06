@if(isset($viewingListing) && $viewingListing->packages->count())
<div class="col-12">
    <div class="card border-0" style="background:#f8fafc;">
        <div class="card-body">
            <h6 class="fw-700 mb-3">
                <i class="bi bi-people-fill me-2 text-primary"></i>Subscriptions
                <span class="badge bg-primary ms-1">{{ $viewingListing->packages->sum(fn($p) => $p->subscriptions->count()) }}</span>
            </h6>

            @foreach($viewingListing->packages as $pkg)
                @if($pkg->subscriptions->count())
                <div class="mb-3">
                    <div class="fw-600 mb-2">
                        {{ $pkg->name }}
                        @if($pkg->room)
                            <small class="text-muted">- Room {{ $pkg->room->room_number }}</small>
                        @endif
                        <small class="text-muted">- {{ $pkg->duration_days }} days</small>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Starts</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pkg->subscriptions as $sub)
                                <tr>
                                    <td>{{ $sub->customer->name ?? '—' }}<div class="small text-muted">{{ $sub->customer->email ?? '' }}</div></td>
                                    <td><span class="badge badge-{{ $sub->status }}">{{ ucfirst($sub->status) }}</span></td>
                                    <td>{{ optional($sub->starts_at)->format('d M Y') }}</td>
                                    <td>{{ optional($sub->expires_at)->format('d M Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endif
