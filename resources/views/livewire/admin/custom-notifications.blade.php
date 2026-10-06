<div>
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form wire:submit.prevent="sendNotification">
                        
                        <div class="mb-3">
                            <label class="form-label">Target Audience</label>
                            <select wire:model.live="targetAudience" class="form-select">
                                <option value="all">All Users (Customers & Partners)</option>
                                <option value="customers">Customers Only</option>
                                <option value="partners">Partners Only</option>
                            </select>
                            @error('targetAudience') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notification Title</label>
                            <input type="text" wire:model.live="title" class="form-control" placeholder="E.g., New Feature Alert!">
                            @error('title') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Message Body</label>
                            <textarea wire:model.live="message" class="form-control" rows="3" placeholder="Enter your notification message here..."></textarea>
                            @error('message') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image Attachment</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model.live="attachmentType" value="none" id="att_none">
                                    <label class="form-check-label" for="att_none">None (Message Only)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model.live="attachmentType" value="listing" id="att_listing">
                                    <label class="form-check-label" for="att_listing">Listing Image</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" wire:model.live="attachmentType" value="custom" id="att_custom">
                                    <label class="form-check-label" for="att_custom">Custom Upload</label>
                                </div>
                            </div>
                            @error('attachmentType') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @if($attachmentType === 'listing')
                            <div class="mb-3">
                                <label class="form-label">Select Listing</label>
                                <select wire:model="listingId" class="form-select">
                                    <option value="">-- Choose a Listing --</option>
                                    @foreach($listings as $listing)
                                        <option value="{{ $listing->id }}">{{ $listing->title }}</option>
                                    @endforeach
                                </select>
                                @error('listingId') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        @if($attachmentType === 'custom')
                            <div class="mb-3">
                                <label class="form-label">Upload Custom Image</label>
                                <input type="file" wire:model="customImage" class="form-control" accept="image/*">
                                @error('customImage') <span class="text-danger small">{{ $message }}</span> @enderror
                                
                                <div wire:loading wire:target="customImage" class="text-muted small mt-1">Uploading...</div>
                            </div>
                        @endif

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="sendNotification">
                                    <i class="bi bi-send me-1"></i> Send Notification
                                </span>
                                <span wire:loading wire:target="sendNotification">
                                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                    Sending...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Notification Preview -->
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <h6 class="mb-0 text-muted">Preview (Android Style)</h6>
                </div>
                <div class="card-body">
                    <div class="bg-white rounded p-3 shadow-sm" style="border: 1px solid #e0e0e0; font-family: Roboto, sans-serif;">
                        <div class="d-flex align-items-center mb-2">
                            <div class="bg-primary rounded-circle me-2" style="width: 20px; height: 20px;"></div>
                            <small class="text-muted fw-bold">FeeTrack App</small>
                            <small class="text-muted ms-auto">now</small>
                        </div>
                        
                        @if($attachmentType === 'custom' && $customImage)
                            <div class="mb-2 rounded overflow-hidden" style="height: 120px; background-color: #f8f9fa;">
                                <img src="{{ $customImage->temporaryUrl() }}" class="w-100 h-100" style="object-fit: cover;">
                            </div>
                        @elseif($attachmentType === 'listing' && $listingId)
                            <div class="mb-2 rounded overflow-hidden d-flex align-items-center justify-content-center" style="height: 120px; background-color: #e9ecef;">
                                <span class="text-muted small"><i class="bi bi-image me-1"></i> Listing Image</span>
                            </div>
                        @endif

                        <h6 class="mb-1 text-dark" style="font-size: 15px;">{{ $title ?: 'Notification Title' }}</h6>
                        <p class="mb-0 text-secondary" style="font-size: 14px; line-height: 1.4;">{{ $message ?: 'Your notification message will appear here.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
