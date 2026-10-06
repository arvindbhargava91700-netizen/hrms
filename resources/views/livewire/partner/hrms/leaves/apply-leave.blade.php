<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Apply for Leave</h5>
                </div>
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success d-flex align-items-center mb-4">
                            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                            <div>{{ session('message') }}</div>
                        </div>
                    @endif

                    <form wire:submit.prevent="submit">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Start Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="start_date" class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small text-muted">End Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="end_date" class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label small text-muted">Leave Type <span class="text-danger">*</span></label>
                                <select wire:model="leave_category_id" class="form-select @error('leave_category_id') is-invalid @enderror">
                                    <option value="">Select Leave Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->days }} Days yearly limit)</option>
                                    @endforeach
                                </select>
                                @error('leave_category_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label small text-muted">Reason for Leave <span class="text-danger">*</span></label>
                                <textarea wire:model="reason" rows="4" class="form-control @error('reason') is-invalid @enderror" placeholder="Please provide a brief reason for your leave request..."></textarea>
                                @error('reason') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                                    <div wire:loading.remove wire:target="submit">
                                        <i class="bi bi-send me-1"></i> Submit Application
                                    </div>
                                    <div wire:loading wire:target="submit">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Submitting...
                                    </div>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
