<div>
    <div class="card">
        <div class="card-header d-flex flex-column flex-sm-row gap-3">
            <div class="search-box" style="width:100%; max-width:300px;">
                <i class="bi bi-search search-icon"></i>
                <input type="text" class="form-control" wire:model.live="search" placeholder="Search customer name or email...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Active Subscriptions</th>
                        <th>Joined</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $customer->avatar_url }}" class="rounded-circle" width="36" height="36">
                                    <div class="fw-600">{{ $customer->name }}</div>
                                </div>
                            </td>
                            <td>
                                <div><i class="bi bi-envelope text-muted me-1"></i>{{ $customer->email }}</div>
                                <div class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $customer->mobile }}</div>
                            </td>
                            <td>
                                @php $activeCount = 0; @endphp
                                @foreach($customer->subscriptions as $sub)
                                    @if($sub->status === 'active')
                                        @php $activeCount++; @endphp
                                        <div class="badge bg-primary text-white mb-1">
                                            {{ $sub->package->name ?? 'N/A' }}
                                        </div>
                                    @endif
                                @endforeach
                                @if($activeCount === 0)
                                    <span class="text-muted fs-12">No active subs</span>
                                @endif
                            </td>
                            <td>{{ $customer->created_at->format('d M, Y') }}</td>
                            <td class="text-end">
                                <button class="btn btn-icon btn-outline-secondary btn-sm" wire:click="viewCustomer('{{ $customer->id }}')" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted">No customers found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="card-footer bg-transparent border-top p-3">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    {{-- Customer Detail Modal (Active subscriptions as cards) --}}
    @if($viewingCustomer)
    <div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="$set('viewingId', null)">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $viewingCustomer->name }} — Active Subscriptions</h5>
                    <button type="button" class="btn-close" wire:click="$set('viewingId', null)"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $viewingCustomer->avatar_url }}" class="rounded-circle" width="48" height="48">
                            <div>
                                <div class="fw-700">{{ $viewingCustomer->name }}</div>
                                <div class="small text-muted">{{ $viewingCustomer->email }} · {{ $viewingCustomer->mobile }}</div>
                            </div>
                        </div>
                    </div>

                    @if($viewingCustomer->subscriptions->count())
                        <div class="row g-3">
                            @foreach($viewingCustomer->subscriptions as $s)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1">{{ $s->package->name ?? '—' }}</h6>
                                                <div class="small text-muted">{{ $s->package->listing->title ?? '—' }}</div>
                                            </div>
                                            <div>
                                                <span class="badge bg-success">Active</span>
                                            </div>
                                        </div>

                                        <div class="small text-muted mb-2">
                                            <div><strong>Starts:</strong> {{ optional($s->starts_at)->format('d M Y') }}</div>
                                            <div><strong>Expires:</strong> {{ optional($s->expires_at)->format('d M Y') }}</div>
                                        </div>

                                        <p class="mb-0">{{ $s->notes ?? '' }}</p>
                                    </div>
                                        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="text-muted">Payments: {{ $s->payments->count() }}</small>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <small class="text-muted">Price: {{ $s->amount ?? '-' }}</small>
                                                <button class="btn btn-sm btn-outline-primary" wire:click.prevent="viewSubscriptionPayments('{{ $s->id }}')">Payments</button>
                                            </div>
                                        </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small">No active subscriptions for this customer.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="$set('viewingId', null)">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Subscription Payments Modal --}}
    @if($viewingSubscription)
    <div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="closeViewingSubscription">
        <div class="modal-dialog modal-md modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payments for Subscription</h5>
                    <button type="button" class="btn-close" wire:click="closeViewingSubscription"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">Package: {{ $viewingSubscription->package->name ?? '-' }}</p>
                    @if($viewingSubscription->payments && $viewingSubscription->payments->count())
                        <ul class="list-group">
                            @foreach($viewingSubscription->payments as $p)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-600">{{ $p->transaction_id ?? 'Payment #' . $p->id }}</div>
                                        <div class="small text-muted">{{ optional($p->created_at)->format('d M Y H:i') }}</div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-600">{{ $p->amount ?? '-' }}</div>
                                        <div class="small text-muted">{{ ucfirst($p->status ?? 'n/a') }}</div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted small">No payments recorded.</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" wire:click="closeViewingSubscription">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
