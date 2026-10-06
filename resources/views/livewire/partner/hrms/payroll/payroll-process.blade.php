<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Process Payroll</h5>
                </div>
                <div class="card-body">
                    @include('partials.hrms-filters', ['viewAnyPermission' => 'payroll_viewAny'])
                    
                    @if (session()->has('message'))
                        <div class="alert alert-success d-flex align-items-center mb-4 mt-3">
                            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                            <div>{{ session('message') }}</div>
                        </div>
                    @endif
                    
                    @if (session()->has('info'))
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="bi bi-info-circle-fill fs-5 me-2"></i>
                            <div>{{ session('info') }}</div>
                        </div>
                    @endif

                    <div class="alert bg-light border text-dark mb-4 p-3 rounded">
                        <h6 class="fw-bold"><i class="bi bi-lightbulb me-2 text-warning"></i> How it works</h6>
                        <p class="small mb-0">Select a month and year below to generate draft payslips for all your employees. This process will create pending drafts based on their basic salary. You can review and adjust deductions or bonuses for each employee in the "Payroll Drafts" screen before finalizing.</p>
                    </div>

                    <form wire:submit.prevent="processPayroll">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Select Month</label>
                                <select wire:model="month" class="form-select @error('month') is-invalid @enderror">
                                    @foreach($months as $num => $name)
                                        <option value="{{ $num }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('month') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Select Year</label>
                                <select wire:model="year" class="form-select @error('year') is-invalid @enderror">
                                    @foreach($years as $yr)
                                        <option value="{{ $yr }}">{{ $yr }}</option>
                                    @endforeach
                                </select>
                                @error('year') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                             @if(auth()->user()->canAccess('payroll_create'))
                            
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-primary px-4 py-2 fw-bold">
                                    <div wire:loading.remove wire:target="processPayroll">
                                        <i class="bi bi-gear-fill me-1"></i> Generate Drafts
                                    </div>
                                    <div wire:loading wire:target="processPayroll">
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Processing...
                                    </div>
                                </button>
                            </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
