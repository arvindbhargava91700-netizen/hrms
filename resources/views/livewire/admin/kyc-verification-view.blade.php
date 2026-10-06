<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ $doc->partner->name }}</h5>
                    <div class="text-muted small">{{ \App\Helpers\AdminHelper::maskContact('email', $doc->partner->email) }}</div>
                </div>
                <span class="badge-status badge-{{ $doc->status }}">{{ ucfirst($doc->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="text-muted small">Submitted At</div>
                        <div class="fw-600">{{ ($doc->submitted_at ?? $doc->created_at)->format('d M, Y H:i') }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Reviewed At</div>
                        <div class="fw-600">{{ $doc->reviewed_at?->format('d M, Y H:i') ?? 'Not reviewed yet' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted small">Partner Status</div>
                        <div class="fw-600">{{ ucfirst($doc->partner->status) }}</div>
                    </div>
                </div>

                <h6 class="fw-700 mb-3">Submitted Details</h6>
                @php($items = $doc->submission_summary)
                <div class="row g-3">
                    @forelse($items as $key => $item)
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted small mb-1">{{ $item['label'] ?? ucfirst(str_replace('_', ' ', $key)) }}</div>
                                @if(($item['type'] ?? 'text') === 'document')
                                    @if(!empty($item['value']))
                                        <div class="mb-2"><strong>Number:</strong> {{ $item['value'] }}</div>
                                    @endif

                                    @if(!empty($item['files']))
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($item['files'] as $side => $path)
                                                <a href="{{ $doc->getStoredFileUrl($path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                    View {{ ucfirst($side) }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <div class="fw-600">{{ $item['value'] ?? 'N/A' }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-muted">No submission data found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body d-grid gap-2">
                @if($doc->status === 'pending')
                    <button class="btn btn-success" wire:click="approve">
                        <i class="bi bi-check-lg me-1"></i> Approve KYC
                    </button>
                    <button class="btn btn-danger" wire:click="reject">
                        <i class="bi bi-x-lg me-1"></i> Reject KYC
                    </button>
                @else
                    <div class="text-muted">This KYC has already been reviewed.</div>
                @endif
                <a href="{{ route('admin.kyc') }}" class="btn btn-outline-secondary">Back to KYC List</a>
            </div>
        </div>
    </div>
</div>
