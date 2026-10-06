<div>
    <div class="card card-custom">
        <div class="card-header">
            <h5 class="mb-0">Payment Status</h5>
        </div>
        <div class="card-body text-center py-5">
            @if($status === 'verifying')
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h4 class="mt-4">{{ $message }}</h4>
                <p class="text-muted">Please do not refresh or close this page.</p>
            @elseif($status === 'success')
                <div class="text-success mb-4">
                    <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
                </div>
                <h4 class="mb-3">{{ $message }}</h4>
                <a href="{{ route('partner.hrms.job-posts') }}" class="btn btn-primary mt-3">
                    Return to Job Postings
                </a>
            @else
                <div class="text-danger mb-4">
                    <i class="bi bi-x-circle-fill" style="font-size: 4rem;"></i>
                </div>
                <h4 class="mb-3">{{ $message }}</h4>
                <a href="{{ route('partner.hrms.job-posts') }}" class="btn btn-secondary mt-3">
                    Return to Job Postings
                </a>
            @endif
        </div>
    </div>
</div>
