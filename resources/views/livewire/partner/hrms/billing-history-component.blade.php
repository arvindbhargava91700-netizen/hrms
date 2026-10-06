<div>
    <div class="card card-custom mb-4 shadow-sm border-0 rounded-3">
        <div class="card-body p-5 text-center">
            <h4 class="fw-bold mb-3" style="color: #2c3e50;">Billing Profile</h4>
            <div class="text-muted mb-4 d-flex justify-content-center align-items-center gap-2">
                <i class="bi bi-building"></i> {{ auth()->user()->company_name ?? auth()->user()->name }}
                <span class="mx-2">|</span>
                <i class="bi bi-geo-alt"></i> {{ auth()->user()->address ?? 'N/A' }}
            </div>
            
            <div class="alert bg-light border text-start d-flex justify-content-between align-items-center rounded-3 p-3 mb-0">
                <div>
                    <i class="bi bi-info-circle text-muted me-2"></i>
                    If you're registered with ISD-GSTIN, kindly update your GSTIN accordingly.
                </div>
                <button class="btn btn-outline-primary btn-sm rounded-pill px-3">Update GSTIN / ISD-GSTIN</button>
            </div>
        </div>
    </div>

    <div class="card card-custom shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Billing History</h5>
                <div class="d-flex gap-2 mt-3">
                    <button wire:click="setStatusFilter('All')" class="btn rounded-pill btn-sm px-3 {{ $statusFilter === 'All' ? 'btn-primary active' : 'btn-outline-secondary' }}">All</button>
                    <button wire:click="setStatusFilter('Success')" class="btn rounded-pill btn-sm px-3 {{ $statusFilter === 'Success' ? 'btn-success active text-white' : 'btn-outline-secondary' }}">Success</button>
                    <button wire:click="setStatusFilter('Pending')" class="btn rounded-pill btn-sm px-3 {{ $statusFilter === 'Pending' ? 'btn-warning active text-white' : 'btn-outline-secondary' }}">Pending</button>
                    <button wire:click="setStatusFilter('Failed')" class="btn rounded-pill btn-sm px-3 {{ $statusFilter === 'Failed' ? 'btn-danger active text-white' : 'btn-outline-secondary' }}">Failed</button>
                </div>
            </div>
            @if(auth()->user()->isPartner() || auth()->user()->canAccess('jobpost_viewAny'))
            <div>
                <select wire:model.live="branch_id" class="form-select form-select-sm rounded-pill shadow-sm border text-muted">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-muted small">
                        <tr>
                            <th class="px-4 py-3 fw-medium">Date</th>
                            <th class="py-3 fw-medium">Branch</th>
                            <th class="py-3 fw-medium">Plan details</th>
                            <th class="py-3 fw-medium">Applies until</th>
                            <th class="py-3 fw-medium">Amount</th>
                            <th class="py-3 fw-medium">Status</th>
                            <th class="px-4 py-3 fw-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        @php
                            $job = \App\Models\JobPost::find($transaction->reference_id);
                        @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <div>{{ $transaction->created_at->format('d M Y') }}</div>
                                <div class="small text-muted">{{ $transaction->created_at->format('h:i:s A') }}</div>
                            </td>
                            <td class="py-3">
                                {{ $job && $job->branch ? $job->branch->name : 'N/A' }}
                            </td>
                            <td class="py-3 text-primary text-decoration-underline" style="cursor: pointer;">
                                {{ $job ? ($job->jobPlan->name ?? 'Job Credit Package') : 'Job Credit Package' }}
                            </td>
                            <td class="py-3">
                                <div>Paid on: {{ $transaction->created_at->format('M d, Y') }}</div>
                                @if($job && $job->expires_at)
                                <div class="small text-muted mt-1">Expires on: {{ \Carbon\Carbon::parse($job->expires_at)->format('M d, Y') }}</div>
                                @endif
                            </td>
                            <td class="py-3">₹ {{ number_format($transaction->total_amount ?? $transaction->amount, 2) }}</td>
                            <td class="py-3">
                                <span class="badge bg-{{ $transaction->status === 'success' || $transaction->status === 'completed' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'warning') }}-subtle text-{{ $transaction->status === 'success' || $transaction->status === 'completed' ? 'success' : ($transaction->status === 'failed' ? 'danger' : 'warning') }} rounded-pill px-3">
                                    {{ ucfirst($transaction->status === 'completed' ? 'success' : $transaction->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($transaction->status === 'success' || $transaction->status === 'completed')
                                <a href="{{ route('partner.invoices.receipt.show', ['type' => 'job_post', 'id' => $transaction->id]) }}" class="btn btn-sm text-success fw-medium d-flex align-items-center gap-1" target="_blank">
                                    <i class="bi bi-download"></i> Invoice
                                </a>
                                @else
                                <a href="#" class="btn btn-sm text-secondary fw-medium d-flex align-items-center gap-1">
                                    <i class="bi bi-headset"></i> Contact us
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-3"></i>
                                No billing history found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transactions->hasPages())
            <div class="px-4 py-3 border-top">
                {{ $transactions->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
