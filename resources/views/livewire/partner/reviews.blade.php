<div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0 fw-bold">Customer Reviews</h5>
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
                            <td style="max-width:350px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $review->comment }}">
                                {{ $review->comment ?? '-' }}
                            </td>
                            <td class="text-muted small">
                                {{ $review->created_at->format('d M Y') }}
                            </td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" wire:click="viewReview({{ $review->id }})">
                                    <i class="bi bi-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-chat-square-text fs-3 d-block mb-2"></i>
                                No reviews found for your listings.
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
                            <div class="text-muted small">{{ $viewingReview->customer->email ?? '' }}</div>
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
</div>
