<div>
    <div class="row justify-content-center mt-5">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5 text-center">
                    
                    @if($status === 'verifying')
                        <div class="mb-4">
                            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <h4 class="text-primary fw-bold mb-3">Verifying Subscription</h4>
                        <p class="text-muted mb-0">{{ $message }}</p>
                    
                    @elseif($status === 'success')
                        <div class="mb-4">
                            <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-check-lg" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <h4 class="text-success fw-bold mb-3">Subscription Successful</h4>
                        <p class="text-muted mb-4">{{ $message }}</p>
                        <a href="{{ route('partner.platform-plans') }}" class="btn btn-outline-primary rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i> Back to Plans
                        </a>
                        
                    @else
                        <div class="mb-4">
                            <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-x-lg" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <h4 class="text-danger fw-bold mb-3">Subscription Failed</h4>
                        <p class="text-muted mb-4">{{ $message }}</p>
                        <a href="{{ route('partner.platform-plans') }}" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="bi bi-arrow-left me-2"></i> Back to Plans
                        </a>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
