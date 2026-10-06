<div>
    <div class="d-flex justify-content-end gap-2 mb-4 flex-wrap">
        <select class="form-select" wire:model.live="dateRange" style="width:200px;">
            <option value="today">Today</option>
            <option value="this_week">This Week</option>
            <option value="this_month">This Month</option>
            <option value="this_year">This Year</option>
        </select>
        <a href="{{ $this->exportUrl }}" class="btn btn-outline-success">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="stat-card border-primary" style="border-left-width: 4px;">
                <div class="stat-icon bg-success-soft"><i class="bi bi-wallet2"></i></div>
                <div>
                    <div class="stat-label">Collected Revenue</div>
                    <div class="stat-value text-success">₹{{ number_format($revenue) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card border-info" style="border-left-width: 4px;">
                <div class="stat-icon bg-info-soft"><i class="bi bi-person-plus"></i></div>
                <div>
                    <div class="stat-label">New Subscriptions</div>
                    <div class="stat-value text-info">{{ number_format($newSubscriptions) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5>Revenue Trend</h5>
        </div>
        <div class="card-body">
            @if(empty($trendValues) || collect($trendValues)->sum() === 0)
                <div class="text-center py-5 text-muted">No revenue data available for this period.</div>
            @else
                <div class="d-flex align-items-end gap-2" style="height: 300px; padding-top:20px; overflow-x:auto;">
                    @php $max = max($trendValues) ?: 1; @endphp
                    @foreach($trendValues as $index => $value)
                        @php
                            $height = $max > 0 ? max(($value / $max) * 220, $value > 0 ? 12 : 2) : 2;
                            $label = $trendLabels[$index] ?? '';
                        @endphp
                        <div class="flex-shrink-0 d-flex flex-column align-items-center justify-content-end"
                             style="width: 60px; height: 260px;">
                            <div class="w-100 bg-primary rounded-top"
                                 style="height: {{ $height }}px; min-height:4px; opacity:0.85; transition:opacity 0.2s;"
                                 title="{{ $label }}: ₹{{ number_format($value) }}"
                                 onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.85"></div>
                            <small class="text-muted mt-2 text-center text-truncate w-100">{{ $label }}</small>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
