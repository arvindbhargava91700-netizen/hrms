<div>
    <div class="row justify-content-center mt-5">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-5 text-center">
                    
                    @if($status === 'verifying')
                        <div class="spinner-border text-primary mb-4" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h4 class="fw-bold">Verifying Payment</h4>
                        <p class="text-muted">{{ $message }}</p>
                    @elseif($status === 'success')
                        <div class="text-success mb-4">
                            <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="fw-bold text-success">Recharge Successful</h4>
                        <p class="text-muted">{{ $message }}</p>
                        <a href="{{ route('partner.wallet') }}" class="btn btn-primary mt-3 px-4 rounded-pill">
                            <i class="bi bi-wallet2 me-1"></i> Return to Wallet
                        </a>
                    @else
                        <div class="text-danger mb-4">
                            <i class="bi bi-x-circle-fill" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="fw-bold text-danger">Recharge Failed</h4>
                        <p class="text-muted">{{ $message }}</p>
                        <a href="{{ route('partner.wallet') }}" class="btn btn-outline-secondary mt-3 px-4 rounded-pill">
                            <i class="bi bi-arrow-left me-1"></i> Back to Wallet
                        </a>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
