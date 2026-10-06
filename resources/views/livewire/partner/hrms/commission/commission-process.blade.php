<div>
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-1">Process Commissions</h4>
            <p class="text-muted">Calculate and generate monthly commission payouts for your team.</p>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session()->has('info'))
        <div class="alert alert-info border-0 shadow-sm alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form wire:submit.prevent="processCommissions">
                <div class="row align-items-end g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Select Month</label>
                        <select class="form-select bg-light border-0" wire:model="month" required>
                            @foreach($months as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Select Year</label>
                        <select class="form-select bg-light border-0" wire:model="year" required>
                            @foreach($years as $y)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100 fw-bold d-flex align-items-center justify-content-center" style="height: 38px;">
                            <span wire:loading.remove wire:target="processCommissions">
                                <i class="bi bi-gear-fill me-2"></i> Generate Commissions
                            </span>
                            <span wire:loading wire:target="processCommissions">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="alert alert-light mt-4 mb-0 border">
                <h6 class="fw-bold"><i class="bi bi-info-circle me-2 text-primary"></i>How it works:</h6>
                <ul class="mb-0 text-muted small">
                    <li>This will calculate both direct commissions (based on assigned monthly targets) and hierarchy (upline) commissions.</li>
                    <li>Commissions are generated completely independently from the payroll/salary process.</li>
                    <li>Once generated, you can view the records in the <strong>Commission History</strong> tab.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
