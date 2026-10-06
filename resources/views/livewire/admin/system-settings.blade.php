<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">System Settings</h4>
            <p class="text-muted mb-0">Manage global system configurations</p>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form wire:submit.prevent="saveSettings">
                <h5 class="mb-3 border-bottom pb-2">Payment Settings</h5>
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Admin UPI ID <small class="text-muted">(For manual wallet recharges)</small></label>
                        <input type="text" class="form-control" wire:model="admin_upi_id" placeholder="e.g. admin@upi">
                        <div class="form-text">This UPI ID will be used to generate the QR code for customer wallet recharges.</div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
