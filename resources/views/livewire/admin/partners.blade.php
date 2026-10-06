<div>
@include('partials.report-styles')
<div id="print-area">
    <div class="report-print-header mb-3">
        <h4 class="fw-bold mb-0">Partners Report</h4>
        <p class="text-muted small mb-0">Generated: {{ now()->format('d M Y, h:i A') }}</p><hr>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-people"></i></div>
                <div>
                    <div class="stat-label">Total Partners</div>
                    <div class="stat-value">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-person-check text-success"></i></div>
                <div>
                    <div class="stat-label">Active Partners</div>
                    <div class="stat-value">{{ number_format($stats['active']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-danger);">
                <div class="stat-icon bg-danger-soft"><i class="bi bi-person-slash text-danger"></i></div>
                <div>
                    <div class="stat-label">Inactive/Suspended</div>
                    <div class="stat-value">{{ number_format($stats['inactive'] + $stats['suspended']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card" style="{{ $stats['pendingKyc'] > 0 ? 'border-left: 4px solid var(--bs-warning);' : '' }}">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-file-earmark-person text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending KYC</div>
                    <div class="stat-value">{{ number_format($stats['pendingKyc']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex flex-column flex-lg-row gap-3 align-items-lg-center">
            <div class="d-flex flex-column flex-md-row gap-3 flex-grow-1">
                <div class="search-box" style="width:100%; max-width:350px;">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Search partners...">
                </div>
                <div class="d-flex flex-column flex-sm-row gap-3 flex-grow-1" style="max-width:400px;">
                    <select class="form-select flex-grow-1" wire:model.live="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                    <select class="form-select flex-grow-1" wire:model.live="categoryFilter">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex-shrink-0 ms-lg-auto d-flex gap-2">
                <a href="{{ $this->exportUrl }}" class="btn btn-outline-success text-nowrap w-100 d-sm-inline-block" style="max-width: max-content;">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
                <button class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Contact</th>
                        <th>Categories</th>
                        <th class="text-nowrap">Registered On</th>
                        <th>Wallet</th>
                        <th>Status</th>
                        <th class="text-end d-print-none">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partners as $partner)
                        <tr>
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $partner->avatar_url }}" class="rounded-circle flex-shrink-0" width="36" height="36">
                                    <div class="fw-600">{{ $partner->name }}</div>
                                </div>
                            </td>
                            <td class="align-middle">
                                @php
                                    $canViewContact = auth()->user()->can('admin_view_contact_info');
                                    $emailParts = explode('@', $partner->email);
                                    $maskedEmail = $canViewContact ? $partner->email : str_repeat('*', max(1, strlen($emailParts[0]))) . '@' . ($emailParts[1] ?? '');
                                    $maskedMobile = $canViewContact ? $partner->mobile : ($partner->mobile ? str_repeat('*', max(0, strlen($partner->mobile) - 4)) . substr($partner->mobile, -4) : null);
                                @endphp
                                <div class="text-nowrap"><i class="bi bi-envelope text-muted me-1"></i>{{ $maskedEmail }}</div>
                                <div class="text-muted text-nowrap"><i class="bi bi-telephone me-1"></i>{{ $maskedMobile ?? 'N/A' }}</div>
                            </td>
                            <td class="align-middle">
                                @php
                                    $partnerCategories = $partner->listings->map(fn($l) => $l->category?->name)->filter()->unique();
                                @endphp
                                @if($partnerCategories->isNotEmpty())
                                    <div class="d-flex flex-wrap gap-1" style="max-width:260px;">
                                        @foreach($partnerCategories as $catName)
                                            <span class="badge bg-secondary text-white">{{ $catName }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted small">None</span>
                                @endif
                            </td>
                            <td class="align-middle text-nowrap">{{ $partner->created_at->format('d M, Y') }}</td>
                            <td class="align-middle text-nowrap">
                                <span class="fw-600 text-success">₹{{ number_format($partner->wallet_balance ?? 0, 2) }}</span>
                            </td>
                            <td class="align-middle">
                                <div class="d-flex flex-column gap-1 align-items-start">
                                    <span class="badge-status badge-{{ $partner->status }}">{{ ucfirst($partner->status) }}</span>
                                    @php
                                        $kycStatus = $partner->kycDocument?->status ?? 'pending';
                                        $kycBadgeClass = match($kycStatus) {
                                            'approved' => 'badge-active',
                                            'rejected' => 'badge-suspended',
                                            default => 'badge-pending'
                                        };
                                    @endphp
                                    <span class="badge-status {{ $kycBadgeClass }}">KYC: {{ ucfirst($kycStatus) }}</span>
                                </div>
                            </td>
                            <td class="align-middle text-end d-print-none">
                                <div class="d-flex align-items-center justify-content-end gap-2 text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="viewDetails('{{ $partner->id }}')">
                                        <i class="bi bi-eye"></i> View
                                    </button>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li>
                                                <button class="dropdown-item fw-500" wire:click="editPartner('{{ $partner->id }}')">
                                                    <i class="bi bi-pencil me-2"></i>Edit Partner
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item fw-500 text-primary" wire:click="openWalletModal('{{ $partner->id }}')">
                                                    <i class="bi bi-wallet2 me-2"></i>Add to Wallet
                                                </button>
                                            </li>
                                            @if($partner->status === 'pending')
                                                <li>
                                                    <button class="dropdown-item text-success fw-500" wire:click="approvePartner('{{ $partner->id }}')"
                                                        wire:loading.attr="disabled" wire:target="approvePartner('{{ $partner->id }}')">
                                                        <i class="bi bi-check-circle me-2"></i>Approve Partner
                                                    </button>
                                                </li>
                                            @endif
                                            @if($partner->status === 'active')
                                                <li>
                                                    <button class="dropdown-item text-danger fw-500" wire:click="suspendPartner('{{ $partner->id }}')"
                                                        wire:loading.attr="disabled" wire:target="suspendPartner('{{ $partner->id }}')">
                                                        <i class="bi bi-slash-circle me-2"></i>Suspend Partner
                                                    </button>
                                                </li>
                                            @endif
                                            @if($partner->status === 'suspended')
                                                <li>
                                                    <button class="dropdown-item text-success fw-500" wire:click="approvePartner('{{ $partner->id }}')"
                                                        wire:loading.attr="disabled" wire:target="approvePartner('{{ $partner->id }}')">
                                                        <i class="bi bi-check-circle me-2"></i>Activate Partner
                                                    </button>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-4 text-muted">No partners found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($partners->hasPages())
            <div class="card-footer bg-transparent border-top p-3 d-print-none">
                {{ $partners->links() }}
            </div>
        @endif
    </div>
</div>

    {{-- Partner Details Modal --}}
    @if($showModal && $selectedPartner)
        <div class="modal fade show d-block d-print-none" style="background-color: rgba(0,0,0,0.5);" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Partner Details</h5>
                        <button type="button" class="btn-close" wire:click="closeModal()"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-4">
                            <div class="col-auto">
                                <img src="{{ $selectedPartner->avatar_url }}" class="rounded-circle" width="80" height="80">
                            </div>
                            <div class="col">
                                <h5 class="mb-1">{{ $selectedPartner->name }}</h5>
                                <div class="mb-2">
                                    <span class="badge-status badge-{{ $selectedPartner->status }}">{{ ucfirst($selectedPartner->status) }}</span>
                                </div>
                                <div class="text-muted small">
                                    <div><i class="bi bi-envelope me-2"></i>{{ \App\Helpers\AdminHelper::maskContact('email', $selectedPartner->email) }}</div>
                                    <div><i class="bi bi-telephone me-2"></i>{{ \App\Helpers\AdminHelper::maskContact('mobile', $selectedPartner->mobile) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="text-muted small mb-3"><i class="bi bi-calendar-check me-1"></i> Registered On: <span class="fw-bold text-dark">{{ $selectedPartner->created_at->format('d M, Y') }}</span></div>
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <a href="{{ route('admin.listings', ['partner' => $selectedPartner->id]) }}" class="text-decoration-none">
                                        <div class="card bg-light border-0 h-100 transition shadow-sm">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-shop fs-4 text-primary mb-1"></i>
                                                <div class="text-muted small mb-1">Listings</div>
                                                <div class="fw-bold fs-5 text-dark">{{ $selectedPartner->listings_count ?? 0 }}</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-4">
                                    <a href="{{ route('admin.visits', ['partner' => $selectedPartner->id]) }}" class="text-decoration-none">
                                        <div class="card bg-light border-0 h-100 transition shadow-sm">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-calendar-event fs-4 text-success mb-1"></i>
                                                <div class="text-muted small mb-1">Visits</div>
                                                <div class="fw-bold fs-5 text-dark">{{ $selectedPartner->visit_bookings_count ?? 0 }}</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-sm-4">
                                    <a href="{{ route('admin.subscriptions', ['partner' => $selectedPartner->id]) }}" class="text-decoration-none">
                                        <div class="card bg-light border-0 h-100 transition shadow-sm">
                                            <div class="card-body text-center py-3">
                                                <i class="bi bi-card-checklist fs-4 text-warning mb-1"></i>
                                                <div class="text-muted small mb-1">Subscriptions</div>
                                                <div class="fw-bold fs-5 text-dark">{{ $partnerSubscriptionsCount ?? 0 }}</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 bg-light">
                            <div class="card-header bg-transparent">
                                <h6 class="mb-0">KYC Status</h6>
                            </div>
                            <div class="card-body">
                                @if($selectedPartner->kycDocument)
                                    <div class="row g-3 mb-3">
                                        <div class="col-sm-6">
                                            <div class="text-muted small mb-1">KYC Status</div>
                                            <div class="fw-600">
                                                <span class="badge-status badge-{{ $selectedPartner->kycDocument->status ?? 'pending' }}">
                                                    {{ ucfirst($selectedPartner->kycDocument->status ?? 'pending') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="text-muted small mb-1">Submitted On</div>
                                            <div class="fw-600">{{ ($selectedPartner->kycDocument->submitted_at ?? $selectedPartner->kycDocument->created_at)->format('d M, Y H:i') }}</div>
                                        </div>
                                        @if($selectedPartner->kycDocument->reviewed_at)
                                            <div class="col-sm-6">
                                                <div class="text-muted small mb-1">Reviewed On</div>
                                                <div class="fw-600">{{ $selectedPartner->kycDocument->reviewed_at->format('d M, Y H:i') }}</div>
                                            </div>
                                        @endif
                                        <div class="col-sm-6">
                                            <div class="text-muted small mb-1">Full Review</div>
                                            <a href="{{ route('admin.kyc.view', $selectedPartner->kycDocument->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye me-1"></i>Open Full KYC
                                            </a>
                                        </div>
                                    </div>

                                    <div class="border rounded p-3 bg-white">
                                        <div class="fw-600 mb-3">KYC Summary</div>
                                        @php($kycItems = $selectedPartner->kycDocument->submission_summary)
                                        <div class="row g-3">
                                            @foreach($kycItems as $key => $item)
                                                <div class="col-md-6">
                                                    <div class="small text-muted">{{ $item['label'] ?? ucfirst(str_replace('_', ' ', $key)) }}</div>
                                                    @if(($item['type'] ?? 'text') === 'document')
                                                        <div class="fw-600">
                                                            @if(!empty($item['value']))
                                                                <div>Number: {{ $item['value'] }}</div>
                                                            @endif
                                                            @if(!empty($item['files']))
                                                                <div class="d-flex flex-wrap gap-2 mt-2">
                                                                    @foreach($item['files'] as $side => $path)
                                                                        <a href="{{ $selectedPartner->kycDocument->getStoredFileUrl($path) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                            View {{ ucfirst($side) }}
                                                                        </a>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="fw-600">{{ $item['value'] ?? 'N/A' }}</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <div class="text-muted">No KYC document submitted yet.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            @if($selectedPartner->status === 'pending')
                                <button type="button" class="btn btn-success"
                                    wire:click="approvePartner('{{ $selectedPartner->id }}')">
                                    <i class="bi bi-check-circle me-1"></i>Approve Partner
                                </button>
                            @endif
                            @if($selectedPartner->status === 'active')
                                <button type="button" class="btn btn-danger"
                                    wire:click="suspendPartner('{{ $selectedPartner->id }}')">
                                    <i class="bi bi-slash-circle me-1"></i>Suspend Partner
                                </button>
                            @endif
                            @if($selectedPartner->status === 'suspended')
                                <button type="button" class="btn btn-success"
                                    wire:click="approvePartner('{{ $selectedPartner->id }}')">
                                    <i class="bi bi-check-circle me-1"></i>Activate Partner
                                </button>
                            @endif
                        </div>
                        <button type="button" class="btn btn-secondary" wire:click="closeModal()">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Partner Modal --}}
    @if($showEditModal && $editingUser)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form wire:submit="updatePartner">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Partner Profile</h5>
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
                            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="updatePartner">
                                <span wire:loading.remove wire:target="updatePartner">Save Changes</span>
                                <span wire:loading wire:target="updatePartner">Saving...</span>
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
