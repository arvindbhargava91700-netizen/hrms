<div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0 fw-bold">Reviews List</h5>
                <div style="width:300px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Search by customer or listing..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Customer</th>
                            <th>Listing</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                        <tr>
                            <td>
                                @if($review->customer)
                                    <div class="fw-bold">{{ $review->customer->name }}</div>
                                    <div class="text-muted small">{{ $review->customer->phone ?? $review->customer->email }}</div>
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                            </td>
                            <td>
                                @if($review->listing)
                                    <div class="fw-bold text-primary">{{ $review->listing->title }}</div>
                                @else
                                    <span class="text-muted">Deleted Listing</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-warning">
                                    @for($i=1; $i<=5; $i++)
                                        <i class="bi bi-star{{ $i <= $review->rating ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                            </td>
                            <td style="max-width:300px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $review->comment }}">
                                {{ $review->comment ?? '-' }}
                            </td>
                            <td class="text-muted small">
                                {{ $review->created_at->format('d M Y') }}
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-info" wire:click="viewReview({{ $review->id }})" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" wire:click="editReview({{ $review->id }})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" wire:click="deleteReview({{ $review->id }})" wire:confirm="Are you sure you want to delete this review?" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-chat-square-text fs-3 d-block mb-2"></i>
                                No reviews found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $reviews->links() }}
            </div>
        </div>
    </div>

    {{-- Edit Review Modal --}}
    <div class="modal fade" id="editReviewModal" tabindex="-1" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <form wire:submit.prevent="updateReview">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Edit Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Rating (1-5)</label>
                            <select class="form-select" wire:model="editRating">
                                <option value="5">5 - Excellent</option>
                                <option value="4">4 - Good</option>
                                <option value="3">3 - Average</option>
                                <option value="2">2 - Poor</option>
                                <option value="1">1 - Terrible</option>
                            </select>
                            @error('editRating') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comment</label>
                            <textarea class="form-control" rows="4" wire:model="editComment"></textarea>
                            @error('editComment') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Save Changes</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Review Details Modal --}}
    @if($viewingReviewId && $viewingReview)
    <div class="modal d-block" style="background:rgba(0,0,0,0.55);" wire:click.self="closeView">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold">Review Details</h5>
                    <button type="button" class="btn-close" wire:click="closeView"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-person fs-3 text-secondary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $viewingReview->customer->name ?? 'Unknown Customer' }}</h6>
                            <div class="text-muted small">{{ \App\Helpers\AdminHelper::maskContact('email', $viewingReview->customer->email ?? '' ) }}</div>
                            <div class="text-muted small">{{ $viewingReview->customer->phone ?? '' }}</div>
                        </div>
                    </div>

                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body py-2">
                            <div class="small text-muted mb-1">Listing</div>
                            <div class="fw-semibold text-primary">{{ $viewingReview->listing->title ?? 'Deleted Listing' }}</div>
                        </div>
                    </div>

                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div class="text-warning fs-5">
                            @for($i=1; $i<=5; $i++)
                                <i class="bi bi-star{{ $i <= $viewingReview->rating ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        <div class="text-muted small">
                            {{ $viewingReview->created_at->format('d M Y, h:i A') }}
                        </div>
                    </div>

                    <div class="bg-white border rounded p-3 text-dark">
                        {{ $viewingReview->comment ?: 'No comment provided.' }}
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" wire:click="closeView">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            const editModal = new bootstrap.Modal(document.getElementById('editReviewModal'));
            @this.on('show-edit-review-modal', () => {
                editModal.show();
            });
            @this.on('hide-edit-review-modal', () => {
                editModal.hide();
            });
        });
    </script>
</div>
