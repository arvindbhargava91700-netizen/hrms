<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="mb-1">Payroll Reports - {{ $year }}</h4>
            <p class="text-muted mb-0">Overview of your company's payroll expenses.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <div class="d-inline-block">
                <select wire:model.live="year" class="form-select">
                    @foreach($years as $yr)
                        <option value="{{ $yr }}">{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white h-100 position-relative overflow-hidden">
                <div class="position-absolute top-0 end-0 p-4 opacity-25">
                    <i class="bi bi-cash-stack" style="font-size: 6rem;"></i>
                </div>
                <div class="card-body p-4 position-relative z-index-1">
                    <p class="text-white-50 mb-1 fw-600 text-uppercase" style="letter-spacing: 1px;">Total Net Payroll Expense ({{ $year }})</p>
                    <h1 class="display-5 fw-600 mb-0">₹{{ number_format($totalAnnualExpense, 2) }}</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Monthly Expense Breakdown</h6>
                </div>
                <div class="card-body p-4">
                    <div style="height: 400px; position: relative;">
                        <!-- Using a simple CSS bar chart approach since we might not have Chart.js included globally -->
                        <div class="d-flex align-items-end h-100 w-100 pt-5 pb-3 border-bottom border-start px-2 position-relative" style="gap: 2%;">
                            
                            @php $maxVal = max(count(array_filter($netPays)) > 0 ? max($netPays) : 1, 1000); @endphp
                            
                            @foreach($months as $index => $month)
                                @php 
                                    $val = $netPays[$index];
                                    $heightPct = ($val / $maxVal) * 100;
                                @endphp
                                <div class="flex-fill d-flex flex-column align-items-center justify-content-end h-100 group position-relative" title="{{ $month }}: ₹{{ number_format($val, 2) }}">
                                    <!-- Tooltip/Label above bar -->
                                    <div class="text-center w-100 small text-muted fw-bold mb-2 opacity-0 opacity-100-hover transition-all" style="position: absolute; bottom: {{ $heightPct }}%; z-index: 10;">
                                        ₹{{ number_format($val > 0 ? $val/1000 : 0, 1) }}k
                                    </div>
                                    
                                    <!-- Bar -->
                                    <div class="w-100 bg-primary rounded-top transition-all" style="height: {{ max($heightPct, 1) }}%; opacity: {{ $val > 0 ? '0.85' : '0.1' }};"></div>
                                    
                                    <!-- Label below bar -->
                                    <div class="mt-2 text-center w-100 small fw-bold text-secondary" style="position: absolute; top: 100%;">
                                        {{ $month }}
                                    </div>
                                </div>
                            @endforeach
                            
                            <!-- Grid lines -->
                            <div class="position-absolute w-100 border-top" style="top: 25%; left: 0; opacity: 0.1; z-index: 0;"></div>
                            <div class="position-absolute w-100 border-top" style="top: 50%; left: 0; opacity: 0.1; z-index: 0;"></div>
                            <div class="position-absolute w-100 border-top" style="top: 75%; left: 0; opacity: 0.1; z-index: 0;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .opacity-0 { opacity: 0; }
    .group:hover .opacity-100-hover { opacity: 1 !important; }
    .transition-all { transition: all 0.3s ease; }
    .group:hover .bg-primary { opacity: 1 !important; }
</style>
