<div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary-soft"><i class="bi bi-file-earmark-text text-primary"></i></div>
                <div>
                    <div class="stat-label">Total Submissions</div>
                    <div class="stat-value">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-warning);">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-clock-history text-warning"></i></div>
                <div>
                    <div class="stat-label">Pending KYC</div>
                    <div class="stat-value">{{ number_format($stats['pending']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="border-left: 4px solid var(--bs-success);">
                <div class="stat-icon bg-success-soft"><i class="bi bi-check-circle text-success"></i></div>
                <div>
                    <div class="stat-label">Approved KYC</div>
                    <div class="stat-value">{{ number_format($stats['approved']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card" style="{{ $stats['rejected'] > 0 ? 'border-left: 4px solid var(--bs-danger);' : '' }}">
                <div class="stat-icon bg-danger-soft"><i class="bi bi-x-circle text-danger"></i></div>
                <div>
                    <div class="stat-label">Rejected KYC</div>
                    <div class="stat-value">{{ number_format($stats['rejected']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex flex-column flex-sm-row gap-3 align-items-sm-center">
            <input type="text" class="form-control" wire:model.live="search"
                placeholder="Search partner..." style="width:100%; max-width:240px;">
            <select class="form-select" wire:model.live="statusFilter" style="width:100%; max-width:200px;">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <div class="ms-sm-auto">
                <a href="{{ $this->exportUrl }}" class="btn btn-outline-success text-nowrap">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-feetrack mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Partner</th>
                        <th>Basic Info</th>
                        <th>Status</th>
                        <th class="text-nowrap">Submitted At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docs as $doc)
                        <tr>
                            <td class="align-middle">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="{{ $doc->partner->avatar_url }}" class="rounded-circle flex-shrink-0" width="36" height="36">
                                    <div>
                                        <div class="fw-600">{{ $doc->partner->name }}</div>
                                        <div class="text-muted small">{{ \App\Helpers\AdminHelper::maskContact('email', $doc->partner->email) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="align-middle">
                                @php($items = $doc->submission_summary)
                                <div class="d-flex flex-wrap gap-1" style="max-width:280px;">
                                    @foreach($items as $key => $item)
                                        <span class="badge bg-light text-dark border">
                                            {{ $item['label'] ?? ucfirst(str_replace('_', ' ', $key)) }}
                                        </span>
                                    @endforeach
                                </div>
                                <div class="text-muted small mt-1">Open details to review documents.</div>
                            </td>
                            <td class="align-middle">
                                <span class="badge-status badge-{{ $doc->status }}">{{ ucfirst($doc->status) }}</span>
                            </td>
                            <td class="align-middle text-nowrap">
                                {{ ($doc->submitted_at ?? $doc->created_at)->format('d M, Y H:i') }}
                            </td>
                            <td class="align-middle text-end">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <a href="{{ route('admin.kyc.view', $doc->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    @if($doc->status === 'pending')
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li>
                                                    <button class="dropdown-item text-success fw-500"
                                                        wire:click="approve('{{ $doc->id }}')">
                                                        <i class="bi bi-check-circle me-2"></i>Approve KYC
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger fw-500"
                                                        wire:click="reject('{{ $doc->id }}')">
                                                        <i class="bi bi-x-circle me-2"></i>Reject KYC
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <span class="text-muted small text-nowrap">
                                            <i class="bi bi-clock me-1"></i>{{ $doc->reviewed_at?->format('d M, Y') }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No KYC documents found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($docs->hasPages())
            <div class="card-footer bg-transparent border-top p-3">
                {{ $docs->links() }}
            </div>
        @endif
    </div>
</div>
