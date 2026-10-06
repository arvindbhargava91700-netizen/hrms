<div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-value">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-person-check text-success"></i></div>
                <div>
                    <div class="stat-label">Active Customers</div>
                    <div class="stat-value">{{ number_format($stats['active']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-warning);">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-shield-exclamation text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending KYC</div>
                    <div class="stat-value">{{ number_format($stats['pending_kyc']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="{{ $stats['suspended_inactive'] > 0 ? 'border-left: 4px solid var(--bs-danger);' : 'border-left: 4px solid var(--bs-secondary);' }}">
                <div class="stat-icon bg-danger-soft"><i class="bi bi-person-dash text-danger"></i></div>
                <div>
                    <div class="stat-label">Suspended & Inactive</div>
                    <div class="stat-value">{{ number_format($stats['suspended_inactive']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header d-flex flex-column gap-3">
            <div class="row g-3 align-items-end w-100">
                <div class="col-lg-3">
                    <label class="form-label small text-muted">Search</label>
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Name, email, mobile">
                </div>
                <div class="col-lg-2">
                    <label class="form-label small text-muted">Status</label>
                    <select class="form-select" wire:model.live="statusFilter">
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label small text-muted">Email Verified</label>
                    <select class="form-select" wire:model.live="emailVerifiedFilter">
                        <option value="">All</option>
                        <option value="verified">Verified</option>
                        <option value="unverified">Unverified</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label small text-muted">Mobile Verified</label>
                    <select class="form-select" wire:model.live="mobileVerifiedFilter">
                        <option value="">All</option>
                        <option value="verified">Verified</option>
                        <option value="unverified">Unverified</option>
                    </select>
                </div>
                <div class="col-lg-2">
                    <label class="form-label small text-muted">Subscriptions</label>
                    <select class="form-select" wire:model.live="subscriptionFilter">
                        <option value="">All</option>
                        <option value="has_active">Has Active</option>
                        <option value="no_active">No Active</option>
                    </select>
                </div>
                <div class="col-lg-1">
                    <label class="form-label small text-muted">Sort</label>
                    <select class="form-select" wire:model.live="sortBy">
                        <option value="latest">Latest</option>
                        <option value="oldest">Oldest</option>
                        <option value="name">Name</option>
                        <option value="email">Email</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <a href="{{ $this->exportUrl }}" class="btn btn-outline-success">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Account</th>
                        <th>Wallet</th>
                        <th>Billing</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $customer->avatar_url }}" class="rounded-circle flex-shrink-0" width="36" height="36">
                                    <div>
                                        <div class="fw-600">{{ $customer->name }}</div>
                                        <div class="text-muted small">{{ $customer->role }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                @php
                                    $canViewContact = auth()->user()->can('admin_view_contact_info');
                                    $emailParts = explode('@', $customer->email);
                                    $maskedEmail = $canViewContact ? $customer->email : str_repeat('*', max(1, strlen($emailParts[0]))) . '@' . ($emailParts[1] ?? '');
                                    $maskedMobile = $canViewContact ? $customer->mobile : ($customer->mobile ? str_repeat('*', max(0, strlen($customer->mobile) - 4)) . substr($customer->mobile, -4) : null);
                                @endphp
                                <div class="text-nowrap"><i class="bi bi-envelope text-muted me-1"></i>{{ $maskedEmail }}</div>
                                <div class="text-muted text-nowrap"><i class="bi bi-telephone me-1"></i>{{ $maskedMobile ?? 'N/A' }}</div>
                            </td>
                            <td class="align-middle">
                                <div class="mb-1 d-flex gap-2 flex-wrap">
                                    <span class="badge-status badge-{{ $customer->status }}">{{ ucfirst($customer->status) }}</span>
                                    @php
                                        $kycStatus = $customer->customerKyc?->status ?? 'pending';
                                        $kycBadgeClass = match($kycStatus) {
                                            'approved' => 'badge-active',
                                            'rejected' => 'badge-suspended',
                                            default => 'badge-pending'
                                        };
                                    @endphp
                                    <span class="badge-status {{ $kycBadgeClass }}">KYC: {{ ucfirst($kycStatus) }}</span>
                                </div>
                                <div class="small text-muted mt-1">
                                    Email: {{ $customer->email_verified_at ? 'Verified' : 'Pending' }}<br>
                                    Mobile: {{ $customer->mobile_verified_at ? 'Verified' : 'Pending' }}
                                </div>
                            </td>
                            <td class="align-middle text-nowrap">
                                <span class="fw-600 text-info">₹{{ number_format($customer->wallet_balance ?? 0, 2) }}</span>
                            </td>
                            <td class="align-middle">
                                <div class="small"><strong>Subs:</strong> {{ $customer->subscriptions_count ?? 0 }}</div>
                                <div class="small"><strong>Active:</strong> {{ $customer->active_subscriptions_count ?? 0 }}</div>
                                <div class="small"><strong>Payments:</strong> {{ $customer->payments_count ?? 0 }}</div>
                            </td>
                            <td class="align-middle text-end d-print-none">
                                <div class="d-flex align-items-center justify-content-end gap-2 text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="viewDetails('{{ $customer->id }}')">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <button class="dropdown-item fw-500" wire:click="editCustomer('{{ $customer->id }}')">
                                                    <i class="bi bi-pencil me-2"></i>Edit Customer
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item fw-500 text-primary" wire:click="openWalletModal('{{ $customer->id }}')">
                                                    <i class="bi bi-wallet2 me-2"></i>Add to Wallet
                                                </button>
                                            </li>
                                            @if($customer->status === 'pending')
                                                <li>
                                                    <button class="dropdown-item text-success fw-500" wire:click="approveCustomer('{{ $customer->id }}')"
                                                        wire:loading.attr="disabled" wire:target="approveCustomer('{{ $customer->id }}')">
                                                        <i class="bi bi-check-circle me-2"></i>Approve Customer
                                                    </button>
                                                </li>
                                            @endif
                                            @if($customer->status === 'active')
                                                <li>
                                                    <button class="dropdown-item text-danger fw-500" wire:click="suspendCustomer('{{ $customer->id }}')"
                                                        wire:loading.attr="disabled" wire:target="suspendCustomer('{{ $customer->id }}')">
                                                        <i class="bi bi-slash-circle me-2"></i>Suspend Customer
                                                    </button>
                                                </li>
                                            @endif
                                            @if($customer->status === 'suspended')
                                                <li>
                                                    <button class="dropdown-item text-success fw-500" wire:click="approveCustomer('{{ $customer->id }}')"
                                                        wire:loading.attr="disabled" wire:target="approveCustomer('{{ $customer->id }}')">
                                                        <i class="bi bi-check-circle me-2"></i>Activate Customer
                                                    </button>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No customers found</td></tr>
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

    {{-- Edit Customer Modal --}}
    @if($showEditModal && $editingUser)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit="updateCustomer">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Customer Profile</h5>
                            <button type="button" class="btn-close" wire:click="closeEditModal()"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-500">Name</label>
                                <input type="text" class="form-control" wire:model="editName">
                                @error('editName') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-500">Email</label>
                                <input type="email" class="form-control" wire:model="editEmail">
                                @error('editEmail') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-500">Mobile</label>
                                <input type="text" class="form-control" wire:model="editMobile">
                                @error('editMobile') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-500">Status</label>
                                <select class="form-select" wire:model="editStatus">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="pending">Pending</option>
                                </select>
                                @error('editStatus') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3" x-data="{ showPassword: false }">
                                <label class="form-label fw-500">Password (Leave blank to keep current)</label>
                                <div class="input-group">
                                    <input x-bind:type="showPassword ? 'text' : 'password'" class="form-control" wire:model="editPassword">
                                    <button class="btn btn-outline-secondary" type="button" @click="showPassword = !showPassword">
                                        <i class="bi" x-bind:class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                                    </button>
                                </div>
                                @error('editPassword') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeEditModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="updateCustomer">
                                <span wire:loading.remove wire:target="updateCustomer">Save Changes</span>
                                <span wire:loading wire:target="updateCustomer">Saving...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    @endif

    <!-- Add to Wallet Modal -->
    @if($showWalletModal)
        <div class="modal fade show" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">Add to Wallet - {{ $walletUser?->name }}</h5>
                        <button type="button" class="btn-close" wire:click="closeWalletModal()"></button>
                    </div>
                    <form wire:submit.prevent="addWalletBalance">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-500">Amount (₹)</label>
                                <input type="number" step="0.01" class="form-control" wire:model="walletAmount" placeholder="e.g. 500">
                                @error('walletAmount') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-500">Details (Remarks)</label>
                                <textarea class="form-control" wire:model="walletDetails" rows="3" placeholder="Reason for adding balance"></textarea>
                                @error('walletDetails') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 pt-0">
                            <button type="button" class="btn btn-secondary" wire:click="closeWalletModal()">Cancel</button>
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="addWalletBalance">
                                <span wire:loading.remove wire:target="addWalletBalance">Add Balance</span>
                                <span wire:loading wire:target="addWalletBalance">Processing...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
