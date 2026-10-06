<div>
    <div class="row g-3 mb-4">
        @foreach([
            ['id' => '', 'label' => 'Total Requests', 'icon' => 'bi-calendar-event', 'color' => 'primary'],
            ['id' => 'pending', 'label' => 'Pending', 'icon' => 'bi-hourglass-split', 'color' => 'warning'],
            ['id' => 'accepted', 'label' => 'Accepted', 'icon' => 'bi-check-circle', 'color' => 'success'],
            ['id' => 'rejected', 'label' => 'Rejected', 'icon' => 'bi-x-circle', 'color' => 'danger'],
            ['id' => 'cancelled', 'label' => 'Cancelled', 'icon' => 'bi-slash-circle', 'color' => 'secondary'],
        ] as $stat)
            <div class="col-6 col-md">
                <div class="card h-100 border-0 shadow-sm" style="cursor: pointer; {{ $statusFilter === $stat['id'] ? 'border-bottom: 3px solid var(--bs-'.$stat['color'].') !important;' : '' }}" wire:click="$set('statusFilter', '{{ $stat['id'] }}')">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="text-{{ $stat['color'] }} bg-{{ $stat['color'] }} bg-opacity-10 rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi {{ $stat['icon'] }} fs-5"></i>
                        </div>
                        <div>
                            <div class="text-muted small fw-500">{{ $stat['label'] }}</div>
                            <div class="fs-4 fw-bold lh-1 mt-1">{{ $counts[$stat['id'] ?: 'total'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header d-flex flex-column flex-sm-row gap-3 align-items-sm-center">
            <h6 class="mb-0 me-auto">All Visit Requests</h6>
            <select class="form-select" wire:model.live="statusFilter" style="width:100%; max-width:200px;">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2 m-3 mb-0">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            @if($visits->count())
                <table class="table table-feetrack mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Partner</th>
                            <th>Listing</th>
                            <th class="text-nowrap">Visit Date & Time</th>
                            <th>Note</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visits as $v)
                            <tr>
                                <td class="align-middle text-muted small"><span title="{{ $v->id }}">{{ substr($v->id, 0, 8) }}</span></td>
                                <td class="align-middle">
                                    <div class="fw-600 text-nowrap">{{ $v->customer->name }}</div>
                                    <div class="text-muted small text-nowrap">
                                        <i class="bi bi-telephone me-1"></i>{{ \App\Helpers\AdminHelper::maskContact('mobile', $v->customer->mobile) }}
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <div class="fw-600 text-nowrap">{{ $v->partner->name }}</div>
                                    <div class="text-muted small text-nowrap">
                                        <i class="bi bi-telephone me-1"></i>{{ \App\Helpers\AdminHelper::maskContact('mobile', $v->partner->mobile) }}
                                    </div>
                                </td>
                                <td class="align-middle">
                                    <div class="fw-500">{{ $v->listing->title }}</div>
                                </td>
                                <td class="align-middle text-nowrap">
                                    <div><i class="bi bi-calendar3 me-1 text-muted"></i>{{ $v->visit_date }}</div>
                                    <div class="text-muted small"><i class="bi bi-clock me-1"></i>{{ $v->visit_time }}</div>
                                </td>
                                <td class="align-middle">
                                    <span class="text-muted small">{{ $v->note ?: '—' }}</span>
                                </td>
                                <td class="align-middle">
                                    <span class="badge-status badge-{{ $v->status }}">{{ ucfirst($v->status) }}</span>
                                </td>
                                <td class="align-middle text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <button class="dropdown-item text-success fw-500"
                                                    onclick="let note = prompt('Enter an acceptance note (optional):'); if(note !== null) @this.call('updateStatus', '{{ $v->id }}', 'accepted', note)">
                                                    <i class="bi bi-check-circle me-2"></i>Accept
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item text-danger fw-500"
                                                    onclick="let note = prompt('Enter a rejection reason (optional):'); if(note !== null) @this.call('updateStatus', '{{ $v->id }}', 'rejected', note)">
                                                    <i class="bi bi-x-circle me-2"></i>Reject
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-secondary fw-500"
                                                    onclick="let note = prompt('Enter a cancellation reason (optional):'); if(note !== null) @this.call('updateStatus', '{{ $v->id }}', 'cancelled', note)">
                                                    <i class="bi bi-slash-circle me-2"></i>Cancel
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="card-footer bg-transparent border-top p-3">
                    {{ $visits->links() }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                    No visit requests yet.
                </div>
            @endif
        </div>
    </div>
</div>
