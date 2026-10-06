<div>
    <div class="card card-custom">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Applied Job List</h5>
        </div>
        <div class="card-body p-0">
            @if (session()->has('success'))
                <div class="alert alert-success m-3">
                    {{ session('success') }}
                </div>
            @endif

            <div class="p-3 bg-light border-bottom d-flex gap-3">
                <input type="text" wire:model.live="search" class="form-control form-control-sm w-25" placeholder="Search applicant, job title...">
                <select wire:model.live="statusFilter" class="form-select form-select-sm w-25">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\JobApplication::statuses() as $status)
                        <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Applicant</th>
                            <th>Job Post</th>
                            <th>Designation</th>
                            <th>Applied On</th>
                            <th>Resume</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                            <tr>
                                <td>{{ $app->applicant->name ?? 'N/A' }}</td>
                                <td>
                                    {{ $app->jobPost->job_title ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">{{ $app->jobPost->job_code ?? '' }}</small>
                                </td>
                                <td>{{ $app->designation }}</td>
                                <td>{{ $app->created_at ? $app->created_at->format('d-m-Y') : 'N/A' }}</td>
                                <td>
                                    @if($app->resume)
                                        <a href="{{ asset('public/storage/' . $app->resume) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> View</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $app->status === 'hired' ? 'success' : ($app->status === 'rejected' ? 'danger' : 'info') }}">
                                        {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                                    </span>
                                    @if($app->remark)
                                        <div class="small text-muted mt-1" style="max-width: 200px; white-space: normal;">
                                            <strong>Remark:</strong> {{ Str::limit($app->remark, 50) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if(auth()->user()->isPartner() || auth()->user()->canAccess('appliedjobpost_update_status'))
                                    <button class="btn btn-sm btn-primary" wire:click="openStatusModal({{ $app->id }})">
                                        Action
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No job applications found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $applications->links() }}
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    @if($isStatusModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Application Action</h5>
                    <button type="button" class="btn-close" wire:click="closeStatusModal"></button>
                </div>
                <form wire:submit.prevent="updateStatus">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select wire:model="newStatus" class="form-select" required>
                                <option value="">Select Status</option>
                                @foreach(\App\Models\JobApplication::statuses() as $status)
                                    <option value="{{ $status }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                            @error('newStatus') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remark</label>
                            <textarea wire:model="remark" class="form-control" rows="3" placeholder="Add remark/feedback..."></textarea>
                            @error('remark') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeStatusModal">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="updateStatus">
                            <span wire:loading.remove wire:target="updateStatus">Save</span>
                            <span wire:loading wire:target="updateStatus">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
