<div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Status Cards -->
    <div class="row g-3 mb-4">
        <div class="col">
            <div class="card bg-primary text-white h-100 shadow-sm border-0">
                <div class="card-body py-3">
                    <h6 class="card-title text-white-50 mb-1">Total</h6>
                    <h3 class="mb-0 fw-bold">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-warning text-dark h-100 shadow-sm border-0">
                <div class="card-body py-3">
                    <h6 class="card-title text-dark-50 mb-1" style="opacity: 0.7;">Pending</h6>
                    <h3 class="mb-0 fw-bold">{{ $stats['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-success text-white h-100 shadow-sm border-0">
                <div class="card-body py-3">
                    <h6 class="card-title text-white-50 mb-1">Active</h6>
                    <h3 class="mb-0 fw-bold">{{ $stats['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-danger text-white h-100 shadow-sm border-0">
                <div class="card-body py-3">
                    <h6 class="card-title text-white-50 mb-1">Expired</h6>
                    <h3 class="mb-0 fw-bold">{{ $stats['expired'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card bg-secondary text-white h-100 shadow-sm border-0">
                <div class="card-body py-3">
                    <h6 class="card-title text-white-50 mb-1">Cancelled/Rejected</h6>
                    <h3 class="mb-0 fw-bold">{{ $stats['cancelled'] + $stats['rejected'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Search by Partner Name or Email..." wire:model.live="search">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="expired">Expired</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="expiresFilter">
                        <option value="">Any Expiration</option>
                        <option value="today">Expiring Today</option>
                        <option value="3days">Expiring Next 3 Days</option>
                        <option value="week">Expiring Next 1 Week</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-center gap-2">
                    <button class="btn btn-primary w-100" wire:click="export"><i class="bi bi-download"></i></button>
                    <button class="btn btn-success w-100 text-nowrap" wire:click="openAddModal"><i class="bi bi-plus-lg"></i> Add New</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-feetrack mb-0">
                    <thead>
                        <tr>
                            <th>Partner</th>
                            <th>Package</th>
                            <th>Amount</th>
                            <th>Requested On</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $sub)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $sub->partner->name ?? 'Unknown' }}</div>
                                    <div class="small text-muted">{{ \App\Helpers\AdminHelper::maskContact('email', $sub->partner->email ?? '' ) }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $sub->package->name ?? 'Unknown' }}</div>
                                    <div class="small text-muted">
                                        {{ $sub->package->duration_days == 0 ? 'Lifetime' : $sub->package->duration_days . ' Days' }}
                                    </div>
                                </td>
                                <td>₹{{ number_format($sub->package->price ?? 0, 2) }}</td>
                                <td>{{ $sub->created_at->format('d M, Y h:i A') }}</td>
                                <td>
                                    @if($sub->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($sub->status === 'active' && $sub->expires_at && $sub->expires_at < now())
                                        <span class="badge bg-danger">Expired</span>
                                    @elseif($sub->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($sub->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @elseif($sub->status === 'expired')
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span class="badge bg-secondary">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary me-1" wire:click="viewDetails('{{ $sub->id }}')">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    @if($sub->status === 'pending')
                                        <button class="btn btn-sm btn-success me-1" 
                                            wire:click="approve('{{ $sub->id }}')"
                                            wire:confirm="Are you sure you want to approve this request?">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger" 
                                            wire:click="reject('{{ $sub->id }}')"
                                            wire:confirm="Are you sure you want to reject this request? The amount will be refunded to their wallet.">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No subscriptions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $subscriptions->links() }}
        </div>
    </div>

    <!-- Add Subscription Modal -->
    @if($showAddModal)
    <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.6); backdrop-filter: blur(2px);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <form wire:submit.prevent="addSubscription">
                    <div class="modal-header bg-light border-bottom pb-3 pt-4 px-4">
                        <h5 class="modal-title fw-bold text-dark">
                            <i class="bi bi-shield-check text-primary me-2"></i> Manually Add Subscription
                        </h5>
                        <button type="button" class="btn-close shadow-none" wire:click="$set('showAddModal', false)"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <!-- Left Column: Partner Selection -->
                            <div class="col-md-5">
                                <h6 class="fw-bold mb-3 text-secondary small text-uppercase letter-spacing-1">1. Select Partner</h6>
                                
                                @if(!$newPartnerId)
                                    <div class="position-relative">
                                        <div class="input-group input-group-sm mb-2 shadow-sm">
                                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                            <input type="text" class="form-control border-start-0 ps-0 shadow-none" placeholder="Search name, email or mobile..." wire:model.live="partnerSearch">
                                        </div>
                                        @if(count($partnerSearchResults) > 0)
                                            <div class="list-group position-absolute w-100 shadow rounded border-0" style="z-index: 1050; max-height: 250px; overflow-y: auto;">
                                                @foreach($partnerSearchResults as $p)
                                                    <button type="button" class="list-group-item list-group-item-action py-2 px-3 border-bottom" wire:click="selectPartner('{{ $p->id }}')">
                                                        <div class="fw-bold text-dark mb-1">{{ $p->name }}</div>
                                                        <div class="small text-muted" style="font-size: 0.75rem;"><i class="bi bi-envelope"></i> {{ $p->email }}</div>
                                                        <div class="small text-muted" style="font-size: 0.75rem;"><i class="bi bi-telephone"></i> {{ $p->mobile }}</div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    @php $selectedPartner = $this->selectedPartner; @endphp
                                    @if($selectedPartner)
                                    <div class="card bg-light border-0 shadow-sm rounded-3">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-0 fw-bold text-dark">{{ $selectedPartner->name }}</h6>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-white text-danger border shadow-sm py-0 px-2 rounded-circle" wire:click="$set('newPartnerId', '')" title="Remove"><i class="bi bi-x"></i></button>
                                            </div>
                                            <div class="small text-muted mb-1" style="font-size: 0.8rem;"><i class="bi bi-envelope text-secondary"></i> {{ $selectedPartner->email }}</div>
                                            <div class="small text-muted mb-3" style="font-size: 0.8rem;"><i class="bi bi-telephone text-secondary"></i> {{ $selectedPartner->mobile }}</div>
                                            
                                            <div class="bg-white p-2 rounded border mb-3">
                                                <div class="small text-muted mb-1" style="font-size: 0.75rem; text-transform: uppercase;">Wallet Balance</div>
                                                <h5 class="mb-0 {{ $selectedPartner->wallet_balance > 0 ? 'text-success' : 'text-danger' }} fw-bold">₹{{ number_format($selectedPartner->wallet_balance, 2) }}</h5>
                                            </div>
                                            
                                            @if($selectedPartner->current_subscription)
                                                <div class="alert alert-info py-2 px-3 mb-0 border-0 rounded-3" style="font-size: 0.8rem;">
                                                    <div class="fw-bold mb-1"><i class="bi bi-star-fill text-warning me-1"></i> Current Package</div>
                                                    <div class="text-dark">{{ $selectedPartner->current_subscription->package->name }}</div>
                                                    <div class="text-muted mt-1" style="font-size: 0.75rem;">Expires: {{ $selectedPartner->current_subscription->expires_at ? \Carbon\Carbon::parse($selectedPartner->current_subscription->expires_at)->format('d M Y') : 'Lifetime' }}</div>
                                                </div>
                                            @else
                                                <div class="alert alert-secondary py-2 px-3 mb-0 border-0 rounded-3 text-center" style="font-size: 0.8rem;">
                                                    <i class="bi bi-info-circle me-1"></i> No active package.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                @endif
                                @error('newPartnerId') <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div> @enderror
                            </div>
                            
                            <!-- Right Column: Package & Payment -->
                            <div class="col-md-7">
                                <h6 class="fw-bold mb-3 text-secondary small text-uppercase letter-spacing-1">2. Package & Payment</h6>
                                
                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-bold mb-2">Select Package</label>
                                    <div class="row g-2">
                                        @foreach($allPackages as $pkg)
                                            <div class="col-sm-6">
                                                <div class="card h-100 cursor-pointer transition-all {{ $newPackageId == $pkg->id ? 'border-primary bg-primary text-white shadow' : 'border-secondary-subtle hover-shadow bg-light' }}" wire:click="selectPackage('{{ $pkg->id }}', {{ $pkg->price }})" style="cursor: pointer;">
                                                    <div class="card-body p-3 text-center position-relative">
                                                        @if($newPackageId == $pkg->id)
                                                            <div class="position-absolute top-0 end-0 p-2">
                                                                <i class="bi bi-check-circle-fill text-white"></i>
                                                            </div>
                                                        @endif
                                                        <h6 class="mb-1 fw-bold {{ $newPackageId == $pkg->id ? 'text-white' : 'text-dark' }}">{{ $pkg->name }}</h6>
                                                        <div class="fw-bold fs-5 mb-1">₹{{ number_format($pkg->price, 2) }}</div>
                                                        <div class="badge {{ $newPackageId == $pkg->id ? 'bg-white text-primary' : 'bg-white text-dark border' }} fw-normal">{{ $pkg->duration_days == 0 ? 'Lifetime' : $pkg->duration_days . ' Days' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('newPackageId') <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div> @enderror
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label text-muted small fw-bold">Amount (₹)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light">₹</span>
                                            <input type="number" step="0.01" class="form-control" wire:model="newAmount" required placeholder="0.00">
                                        </div>
                                        @error('newAmount') <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label text-muted small fw-bold">Payment Method</label>
                                        <select class="form-select form-select-sm" wire:model="newPaymentMethod" required>
                                            <option value="manual">Manual (No Wallet Deduction)</option>
                                            <option value="wallet">Deduct from Partner Wallet</option>
                                        </select>
                                        @error('newPaymentMethod') <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div> @enderror
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="form-label text-muted small fw-bold">Reference / Note</label>
                                    <textarea class="form-control form-control-sm bg-light" wire:model="newReference" rows="2" placeholder="Optional notes for this assignment..."></textarea>
                                    @error('newReference') <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top py-3 px-4">
                        <button type="button" class="btn btn-white border shadow-sm px-4" wire:click="$set('showAddModal', false)">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="addSubscription"><i class="bi bi-lightning-charge"></i> Activate Subscription</span>
                            <span wire:loading wire:target="addSubscription"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- View Subscription Details Modal -->
    @if($viewSubscriptionId && $viewSubscriptionData)
    <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.6); backdrop-filter: blur(2px);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg overflow-hidden">
                <div class="modal-header bg-light border-bottom pb-3 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-card-heading text-primary me-2"></i> Subscription Details
                    </h5>
                    <button type="button" class="btn-close shadow-none" wire:click="closeViewDetails"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <!-- Partner Info -->
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold mb-3 text-secondary small text-uppercase letter-spacing-1">Partner Information</h6>
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 50px; height: 50px; font-size: 1.2rem;">
                                    {{ strtoupper(substr($viewSubscriptionData->partner->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $viewSubscriptionData->partner->name ?? 'Unknown Partner' }}</h6>
                                    <div class="small text-muted">ID: {{ $viewSubscriptionData->partner_id }}</div>
                                </div>
                            </div>
                            <div class="small mb-2"><i class="bi bi-envelope text-muted me-2"></i> {{ $viewSubscriptionData->partner->email ?? 'N/A' }}</div>
                            <div class="small mb-2"><i class="bi bi-telephone text-muted me-2"></i> {{ $viewSubscriptionData->partner->mobile ?? 'N/A' }}</div>
                            <div class="small mb-2"><i class="bi bi-wallet2 text-muted me-2"></i> Wallet Balance: <span class="fw-bold text-success">₹{{ number_format($viewSubscriptionData->partner->wallet_balance ?? 0, 2) }}</span></div>
                        </div>

                        <!-- Package & Payment Info -->
                        <div class="col-md-6">
                            <h6 class="fw-bold mb-3 text-secondary small text-uppercase letter-spacing-1">Plan & Payment Details</h6>
                            
                            <div class="card bg-light border-0 shadow-sm rounded-3 mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0 fw-bold text-dark">{{ $viewSubscriptionData->package->name ?? 'Unknown Package' }}</h6>
                                        <span class="badge bg-primary rounded-pill">{{ $viewSubscriptionData->package->duration_days == 0 ? 'Lifetime' : $viewSubscriptionData->package->duration_days . ' Days' }}</span>
                                    </div>
                                    <div class="fw-bold text-success fs-5 mb-2">₹{{ number_format($viewSubscriptionData->package->price ?? 0, 2) }}</div>
                                    
                                    <div class="row g-2 small text-muted">
                                        <div class="col-6">
                                            <div><strong>Starts:</strong></div>
                                            <div>{{ $viewSubscriptionData->starts_at ? \Carbon\Carbon::parse($viewSubscriptionData->starts_at)->format('d M, Y') : 'N/A' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <div><strong>Expires:</strong></div>
                                            <div>{{ $viewSubscriptionData->expires_at ? \Carbon\Carbon::parse($viewSubscriptionData->expires_at)->format('d M, Y') : 'Lifetime' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-2">
                                <span class="text-muted small fw-bold">Status</span>
                                <span>
                                    @if($viewSubscriptionData->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($viewSubscriptionData->status === 'active' && $viewSubscriptionData->expires_at && $viewSubscriptionData->expires_at < now())
                                        <span class="badge bg-danger">Expired</span>
                                    @elseif($viewSubscriptionData->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($viewSubscriptionData->status === 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @elseif($viewSubscriptionData->status === 'expired')
                                        <span class="badge bg-danger">Expired</span>
                                    @else
                                        <span class="badge bg-secondary">Cancelled</span>
                                    @endif
                                </span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small fw-bold">Created At</span>
                                <span class="small">{{ $viewSubscriptionData->created_at->format('d M, Y h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-3 px-4">
                    <button type="button" class="btn btn-secondary px-4 shadow-sm" wire:click="closeViewDetails">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
