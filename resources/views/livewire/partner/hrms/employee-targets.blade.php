<div class="container-fluid py-4">

    {{-- Month/Year Switcher --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-0">My Targets & Commissions</h4>
            <p class="text-muted mb-0 small">Track your business performance and estimated earnings</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button wire:click="prevMonth" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px;padding:0;line-height:1;">
                <i class="bi bi-chevron-left"></i>
            </button>
            <span class="fw-bold px-2" style="min-width:130px;text-align:center;">
                {{ date('F', mktime(0,0,0,$currentMonth,1)) }} {{ $currentYear }}
            </span>
            <button wire:click="nextMonth" class="btn btn-sm btn-outline-secondary rounded-circle" style="width:32px;height:32px;padding:0;line-height:1;">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-tabs mb-4 border-bottom" style="gap:0.3rem;">
        <li class="nav-item">
            <a class="nav-link fw-semibold {{ $activeTab === 'my_target' ? 'active' : 'text-muted' }}"
               wire:click.prevent="switchTab('my_target')" href="#">
                <i class="bi bi-bullseye me-1"></i> My Target
            </a>
        </li>
        @if(count($teamData) > 0)
        <li class="nav-item">
            <a class="nav-link fw-semibold {{ $activeTab === 'team_target' ? 'active' : 'text-muted' }}"
               wire:click.prevent="switchTab('team_target')" href="#">
                <i class="bi bi-people me-1"></i> Team Targets
            </a>
        </li>
        @endif
    </ul>

    {{-- ======================== MY TARGET TAB ======================== --}}
    @if($activeTab === 'my_target')
    @php
        $metrics   = $myData['metrics'];
        $structure = $myData['structure'];
        $history   = $myData['history'];

        $monthlyTarget    = (float)($structure->monthly_target ?? 0);
        $newBusiness      = (float)($metrics['new_business'] ?? 0);
        $recoveryBusiness = (float)($metrics['recovery_business'] ?? 0);
        $baseComm         = (float)($metrics['base_commission'] ?? 0);
        $recoveryComm     = (float)($metrics['recovery_commission'] ?? 0);
        $totalComm        = $baseComm + $recoveryComm;
        $targetPct        = $monthlyTarget > 0 ? min(100, ($newBusiness / $monthlyTarget) * 100) : 0;
        $targetAchieved   = $monthlyTarget > 0 && $newBusiness >= $monthlyTarget;
        $barColor         = $targetAchieved ? '#198754' : ($targetPct >= 50 ? '#0d6efd' : '#ffc107');
    @endphp

    @if(!$structure)
        {{-- No salary structure --}}
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
            <i class="bi bi-exclamation-circle text-warning" style="font-size:3rem;"></i>
            <h5 class="mt-3 fw-bold">No Salary Structure Found</h5>
            <p class="text-muted">Please ask your manager to set up your salary structure.</p>
        </div>
    @else

 {{-- Top KPI Cards --}}
<div class="row g-3 mb-4">

    {{-- Monthly Target --}}
    <div class="col-xl col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"
             style="border-left:4px solid #6366f1 !important; background:linear-gradient(135deg,#6366f108,#fff);">

            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:#6366f115;">
                        <i class="bi bi-bullseye text-primary fs-5"></i>
                    </div>

                    <div class="text-muted small fw-semibold text-uppercase"
                         style="letter-spacing:.5px;">
                        Monthly Target
                    </div>
                </div>

                <div class="fw-bold"
                     style="font-size:1.6rem;color:#1e1b4b;">
                    ₹{{ number_format($monthlyTarget, 0) }}
                </div>

                <div class="text-muted small mt-1">
                    {{ date('F Y', mktime(0,0,0,$currentMonth,1,$currentYear)) }}
                </div>

            </div>
        </div>
    </div>


    {{-- Business Achieved --}}
    <div class="col-xl col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"
             style="border-left:4px solid {{ $barColor }} !important; background:linear-gradient(135deg,{{ $barColor }}10,#fff);">

            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:{{ $barColor }}18;">
                        <i class="bi bi-graph-up-arrow fs-5"
                           style="color:{{ $barColor }};"></i>
                    </div>

                    <div class="text-muted small fw-semibold text-uppercase"
                         style="letter-spacing:.5px;">
                        New Business
                    </div>
                </div>

                <div class="fw-bold"
                     style="font-size:1.6rem;color:#1e1b4b;">
                    ₹{{ number_format($newBusiness, 0) }}
                </div>

                <div class="mt-2">

                    <div class="progress"
                         style="height:6px;border-radius:3px;">

                        <div class="progress-bar"
                             role="progressbar"
                             style="width:{{ min($targetPct, 100) }}%;background:{{ $barColor }};"
                             aria-valuenow="{{ $targetPct }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>

                    </div>

                    <div class="d-flex justify-content-between mt-1">

                        <small style="color:{{ $barColor }};font-weight:600;">
                            {{ number_format($targetPct, 1) }}% achieved
                        </small>

                        @if($targetAchieved)

                            <small class="text-success fw-bold">
                                <i class="bi bi-check-circle-fill"></i>
                                Target Hit!
                            </small>

                        @else

                            <small class="text-muted">
                                ₹{{ number_format(max(0, $monthlyTarget - $newBusiness), 0) }}
                                remaining
                            </small>

                        @endif

                    </div>

                </div>

            </div>
        </div>
    </div>


    {{-- Recovery Business --}}
    <div class="col-xl col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"
             style="border-left:4px solid #06b6d4 !important; background:linear-gradient(135deg,#06b6d410,#fff);">

            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-2">

                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:#06b6d418;">
                        <i class="bi bi-arrow-return-left fs-5 text-info"></i>
                    </div>

                    <div class="text-muted small fw-semibold text-uppercase"
                         style="letter-spacing:.5px;">
                        Recovery Business
                    </div>

                </div>

                <div class="fw-bold"
                     style="font-size:1.6rem;color:#1e1b4b;">
                    ₹{{ number_format($recoveryBusiness, 0) }}
                </div>

                <div class="text-muted small mt-1">

                    @if($targetAchieved && $recoveryBusiness > 0)

                        <span class="text-info">
                            <i class="bi bi-check-circle me-1"></i>
                            Recovery commission eligible
                        </span>

                    @elseif($recoveryBusiness > 0)

                        <span class="text-warning">
                            <i class="bi bi-lock me-1"></i>
                            Hit target to unlock recovery comm.
                        </span>

                    @else

                        No recovery business this month

                    @endif

                </div>

            </div>
        </div>
    </div>


    {{-- Total Commission --}}
    <div class="col-xl col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"
             style="border-left:4px solid #10b981 !important; background:linear-gradient(135deg,#10b98110,#fff);">

            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-2">

                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width:44px;height:44px;background:#10b98118;">
                        <i class="bi bi-cash-coin fs-5 text-success"></i>
                    </div>

                    <div class="text-muted small fw-semibold text-uppercase"
                         style="letter-spacing:.5px;">
                        Total Commission
                    </div>

                </div>

                <div class="fw-bold text-success"
                     style="font-size:1.6rem;">
                    ₹{{ number_format($totalComm, 0) }}
                </div>

                <div class="text-muted small mt-1">
                    Target + Recovery commission
                </div>

            </div>
        </div>
    </div>


    {{-- Merchant Target --}}
    @php
        $merchantTarget = (int) ($stats['target'] ?? 0);
        $merchantWonLeads = (int) ($stats['won_leads'] ?? 0);
        $merchantPercentage = max(0, (float) ($stats['percentage'] ?? 0));

        $merchantProgress = min($merchantPercentage, 100);

        $merchantColor = $merchantPercentage >= 100
            ? '#198754'
            : ($merchantPercentage >= 50
                ? '#0d6efd'
                : '#f59e0b');
    @endphp

    <div class="col-xl col-md-6">
        <div class="card border-0 shadow-sm rounded-4 h-100"
             style="border-left:4px solid {{ $merchantColor }} !important; background:linear-gradient(135deg,{{ $merchantColor }}10,#fff);">

            <div class="card-body p-4">

                <div class="d-flex align-items-center gap-3 mb-2">

                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="
                            width:44px;
                            height:44px;
                            background:{{ $merchantColor }}18;
                         ">

                        <i class="bi bi-shop fs-5"
                           style="color:{{ $merchantColor }};"></i>

                    </div>

                    <div class="text-muted small fw-semibold text-uppercase"
                         style="letter-spacing:.5px;">
                        Merchant Target
                    </div>

                </div>

                @if($merchantTarget > 0)

                    <div class="fw-bold"
                         style="font-size:1.6rem;color:#1e1b4b;">
                        {{ $merchantWonLeads }}
                        <span style="font-size:13px;font-weight:500;color:#6c757d;">
                            / {{ $merchantTarget }}
                        </span>
                    </div>

                    <div class="text-muted small mt-1">
                        Won Leads / Target
                    </div>

                    <div class="mt-2">

                        <div class="progress"
                             style="height:6px;border-radius:3px;">

                            <div class="progress-bar"
                                 role="progressbar"
                                 style="
                                    width:{{ $merchantProgress }}%;
                                    background:{{ $merchantColor }};
                                 "
                                 aria-valuenow="{{ $merchantPercentage }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>

                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-1">

                            <small style="
                                color:{{ $merchantColor }};
                                font-weight:600;
                            ">
                                {{ number_format($merchantPercentage, 1) }}%
                            </small>

                            @if($merchantPercentage >= 100)

                                <small class="text-success fw-bold">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Target Hit!
                                </small>

                            @else

                                <small class="text-muted">
                                    {{ max(0, $merchantTarget - $merchantWonLeads) }}
                                    remaining
                                </small>

                            @endif

                        </div>

                    </div>

                @else

                    <div class="fw-bold"
                         style="font-size:1.6rem;color:#1e1b4b;">
                        0
                    </div>

                    <div class="text-muted small mt-1">
                        No merchant target set
                    </div>

                @endif

            </div>
        </div>
    </div>

</div>

{{-- Progress + Commission Detail --}}
<div class="row g-4 mb-4">

    {{-- ========================================================= --}}
    {{-- LEFT : TARGET + MERCHANT PROGRESS --}}
    {{-- ========================================================= --}}
    <div class="col-xl-6">

        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">

            {{-- Card Header --}}
            <div class="card-header bg-white border-0 px-4 pt-4 pb-2">

                <div class="d-flex justify-content-between align-items-center">

                    <div class="d-flex align-items-center gap-2">

                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="
                                width:38px;
                                height:38px;
                                background:#6366f115;
                             ">

                            <i class="bi bi-bar-chart-line text-primary"></i>

                        </div>

                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                Target Progress
                            </h6>

                            <small class="text-muted">
                                {{ date('F Y', mktime(0,0,0,$currentMonth,1,$currentYear)) }}
                            </small>
                        </div>

                    </div>

                    @if($targetAchieved)

                        <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Target Achieved
                        </span>

                    @else

                        <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-3 py-2">
                            <i class="bi bi-hourglass-split me-1"></i>
                            In Progress
                        </span>

                    @endif

                </div>

            </div>


            <div class="card-body px-4 pb-4 pt-3">


                {{-- ================================================= --}}
                {{-- NEW BUSINESS TARGET --}}
                {{-- ================================================= --}}

                <div class="p-3 rounded-4 mb-4"
                     style="
                        background:linear-gradient(135deg,#f8fafc,#ffffff);
                        border:1px solid #eef2f7;
                     ">

                    <div class="d-flex align-items-center gap-4">

                        {{-- SVG Ring --}}
                        <div style="
                            position:relative;
                            width:105px;
                            height:105px;
                            flex-shrink:0;
                        ">

                            <svg viewBox="0 0 36 36"
                                 style="
                                    width:105px;
                                    height:105px;
                                    transform:rotate(-90deg);
                                 ">

                                <circle
                                    cx="18"
                                    cy="18"
                                    r="15.9"
                                    fill="none"
                                    stroke="#edf1f5"
                                    stroke-width="3.5"
                                />

                                <circle
                                    cx="18"
                                    cy="18"
                                    r="15.9"
                                    fill="none"
                                    stroke="{{ $barColor }}"
                                    stroke-width="3.5"
                                    stroke-dasharray="{{ min($targetPct,100) }} {{ 100 - min($targetPct,100) }}"
                                    stroke-linecap="round"
                                />

                            </svg>

                            <div style="
                                position:absolute;
                                inset:0;
                                display:flex;
                                flex-direction:column;
                                align-items:center;
                                justify-content:center;
                            ">

                                <div class="fw-bold"
                                     style="
                                        font-size:1.2rem;
                                        line-height:1;
                                        color:{{ $barColor }};
                                     ">

                                    {{ number_format($targetPct, 0) }}%

                                </div>

                                <div style="
                                    font-size:10px;
                                    color:#94a3b8;
                                    margin-top:4px;
                                ">
                                    achieved
                                </div>

                            </div>

                        </div>


                        {{-- Business Information --}}
                        <div class="flex-grow-1">

                            <div class="text-muted small fw-semibold mb-1">
                                NEW BUSINESS
                            </div>

                            <div class="fw-bold text-dark fs-4">
                                ₹{{ number_format($newBusiness, 0) }}
                            </div>

                            <div class="text-muted small mb-3">
                                of ₹{{ number_format($monthlyTarget, 0) }} target
                            </div>


                            {{-- Progress --}}
                            <div class="progress mb-2"
                                 style="
                                    height:8px;
                                    border-radius:10px;
                                    background:#eef2f7;
                                 ">

                                <div class="progress-bar"
                                     style="
                                        width:{{ min($targetPct,100) }}%;
                                        background:{{ $barColor }};
                                        border-radius:10px;
                                     ">
                                </div>

                            </div>


                            <div class="d-flex justify-content-between">

                                <small style="
                                    color:{{ $barColor }};
                                    font-weight:600;
                                ">
                                    {{ number_format($targetPct,1) }}% achieved
                                </small>

                                @if(!$targetAchieved)

                                    <small class="text-danger fw-semibold">
                                        ₹{{ number_format(max(0,$monthlyTarget-$newBusiness),0) }}
                                        remaining
                                    </small>

                                @else

                                    <small class="text-success fw-semibold">
                                        +₹{{ number_format($newBusiness-$monthlyTarget,0) }}
                                        surplus
                                    </small>

                                @endif

                            </div>

                            

                        </div>
                        

                    </div>
                    <br>
                       @if($targetAchieved)

                    <div class="alert alert-success border-0 rounded-3 mb-0 py-2 px-3 d-flex align-items-center gap-2"
                         style="background:#ecfdf5;">

                        <i class="bi bi-trophy-fill text-success"></i>

                        <span class="fw-semibold small text-success">
                            Congratulations! You hit your target this month.
                        </span>

                    </div>

                @elseif($monthlyTarget > 0)

                    <div class="alert alert-warning border-0 rounded-3 mb-0 py-2 px-3 d-flex align-items-center gap-2">

                        <i class="bi bi-lightning-charge-fill text-warning"></i>

                        <span class="fw-semibold small">
                            ₹{{ number_format(max(0,$monthlyTarget-$newBusiness),0) }}
                            more needed to hit your target!
                        </span>

                    </div>

                @endif


                    {{-- Recovery Business --}}
                    @if($recoveryBusiness > 0)

                        <div class="border-top mt-3 pt-3">

                            <div class="d-flex justify-content-between align-items-center">

                                <div class="d-flex align-items-center gap-2">

                                    <div class="rounded-2 d-flex align-items-center justify-content-center"
                                         style="
                                            width:32px;
                                            height:32px;
                                            background:#cffafe;
                                         ">

                                        <i class="bi bi-arrow-return-left text-info"></i>

                                    </div>

                                    <div>

                                        <div class="small fw-semibold text-dark">
                                            Recovery Business
                                        </div>

                                        <small class="text-muted">
                                            Additional recovery
                                        </small>

                                    </div>

                                </div>

                                <div class="text-end">

                                    <div class="fw-bold text-info">
                                        ₹{{ number_format($recoveryBusiness,2) }}
                                    </div>

                                </div>

                            </div>

                        </div>

                    @endif

                </div>



                {{-- ================================================= --}}
                {{-- MERCHANT PROGRESS - GREEN CARD --}}
                {{-- ================================================= --}}

                @php

                    $merchantTarget = (int) ($stats['target'] ?? 0);
                    $merchantWonLeads = (int) ($stats['won_leads'] ?? 0);
                    $merchantPercentage = max(
                        0,
                        (float) ($stats['percentage'] ?? 0)
                    );

                    $merchantGraphPct = min(
                        $merchantPercentage,
                        100
                    );

                    $merchantColor = $merchantPercentage >= 100
                        ? '#198754'
                        : '#20a464';

                @endphp


                <div class="rounded-4 p-3 mb-4"
                     style="
                        background:linear-gradient(
                            135deg,
                            #ecfdf5,
                            #f7fffb
                        );
                        border:1px solid #d1fae5;
                     ">

                    {{-- Merchant Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div class="d-flex align-items-center gap-2">

                            <div class="rounded-3 d-flex align-items-center justify-content-center"
                                 style="
                                    width:38px;
                                    height:38px;
                                    background:#10b98118;
                                 ">

                                <i class="bi bi-shop text-success fs-5"></i>

                            </div>

                            <div>

                                <div class="fw-bold text-dark">
                                    Merchant Progress
                                </div>

                                <small class="text-muted">
                                    Monthly merchant target
                                </small>

                            </div>

                        </div>


                        @if($merchantTarget > 0)

                            @if($merchantPercentage >= 100)

                                <span class="badge rounded-pill bg-success text-white px-3 py-2">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    Achieved
                                </span>

                            @else

                                <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2">
                                    {{ number_format($merchantPercentage,1) }}%
                                </span>

                            @endif

                        @endif

                    </div>


                    @if($merchantTarget > 0)

                        <div class="d-flex align-items-center gap-4">

                            {{-- Merchant Ring --}}
                            <div style="
                                position:relative;
                                width:95px;
                                height:95px;
                                flex-shrink:0;
                            ">

                                <svg viewBox="0 0 36 36"
                                     style="
                                        width:95px;
                                        height:95px;
                                        transform:rotate(-90deg);
                                     ">

                                    <circle
                                        cx="18"
                                        cy="18"
                                        r="15.9"
                                        fill="none"
                                        stroke="#dff5e9"
                                        stroke-width="3.5"
                                    />

                                    <circle
                                        cx="18"
                                        cy="18"
                                        r="15.9"
                                        fill="none"
                                        stroke="{{ $merchantColor }}"
                                        stroke-width="3.5"
                                        stroke-dasharray="{{ $merchantGraphPct }} {{ 100-$merchantGraphPct }}"
                                        stroke-linecap="round"
                                    />

                                </svg>


                                <div style="
                                    position:absolute;
                                    inset:0;
                                    display:flex;
                                    flex-direction:column;
                                    align-items:center;
                                    justify-content:center;
                                ">

                                    <div class="fw-bold"
                                         style="
                                            font-size:1.1rem;
                                            line-height:1;
                                            color:{{ $merchantColor }};
                                         ">

                                        {{ number_format($merchantPercentage,0) }}%

                                    </div>

                                    <div style="
                                        font-size:9px;
                                        color:#6b7280;
                                        margin-top:4px;
                                    ">
                                        achieved
                                    </div>

                                </div>

                            </div>


                            {{-- Merchant Details --}}
                            <div class="flex-grow-1">

                                <div class="d-flex justify-content-between align-items-end mb-1">

                                    <div>

                                        <div class="small text-muted">
                                            WON LEADS
                                        </div>

                                        <div class="fw-bold text-dark fs-5">
                                            {{ $merchantWonLeads }}
                                        </div>

                                    </div>

                                    <div class="text-end">

                                        <div class="small text-muted">
                                            TARGET
                                        </div>

                                        <div class="fw-bold text-success">
                                            {{ $merchantTarget }}
                                        </div>

                                    </div>

                                </div>


                                {{-- Merchant Progress Bar --}}
                                <div class="progress mt-2"
                                     style="
                                        height:9px;
                                        border-radius:10px;
                                        background:#dff5e9;
                                     ">

                                    <div class="progress-bar"
                                         style="
                                            width:{{ $merchantGraphPct }}%;
                                            background:{{ $merchantColor }};
                                            border-radius:10px;
                                         "
                                         role="progressbar">

                                    </div>

                                </div>


                                <div class="d-flex justify-content-between mt-2">

                                    <small style="
                                        color:{{ $merchantColor }};
                                        font-weight:600;
                                    ">

                                        {{ number_format($merchantPercentage,1) }}%
                                        completed

                                    </small>


                                    @if($merchantPercentage >= 100)

                                        <small class="text-success fw-semibold">

                                            <i class="bi bi-trophy-fill me-1"></i>
                                            Target Hit!

                                        </small>

                                    @else

                                        <small class="text-muted">

                                            {{ max(0,$merchantTarget-$merchantWonLeads) }}
                                            remaining

                                        </small>

                                    @endif

                                </div>

                            </div>

                        </div>
                         <br>
                       @if($targetAchieved)

                    <div class="alert alert-success border-0 rounded-3 mb-0 py-2 px-3 d-flex align-items-center gap-2"
                         style="background:#ecfdf5;">

                        <i class="bi bi-trophy-fill text-success"></i>

                        <span class="fw-semibold small text-success">
                            Congratulations! You hit your target this month.
                        </span>

                    </div>

                @elseif($monthlyTarget > 0)

                    <div class="alert alert-success  border-0 rounded-3 mb-0 py-2 px-3 d-flex align-items-center gap-2">

                        <i class="bi bi-lightning-charge-fill text-success "></i>

                        <span class="fw-semibold small">
                            {{ max(0,$merchantTarget-$merchantWonLeads) }}
                            more needed to hit your target!
                        </span>

                    </div>

                @endif


                    @else

                        <div class="text-center py-3">

                            <i class="bi bi-shop text-success"
                               style="font-size:2rem;"></i>

                            <div class="fw-semibold text-dark mt-2">
                                No Merchant Target Set
                            </div>

                            <small class="text-muted">
                                Contact your manager to set a merchant target.
                            </small>

                        </div>

                    @endif

                </div>



                {{-- ================================================= --}}
                {{-- STATUS --}}
                {{-- ================================================= --}}

             

            </div>

        </div>

    </div>



    {{-- ========================================================= --}}
    {{-- RIGHT : COMMISSION BREAKDOWN --}}
    {{-- ========================================================= --}}
    <div class="col-xl-6">

        <div class="card border-0 shadow-sm rounded-4 h-100">

            {{-- Header --}}
            <div class="card-header bg-white border-0 px-4 pt-4 pb-2">

                <div class="d-flex align-items-center gap-2">

                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="
                            width:38px;
                            height:38px;
                            background:#10b98115;
                         ">

                        <i class="bi bi-cash-stack text-success fs-5"></i>

                    </div>

                    <div>

                        <h6 class="fw-bold mb-0 text-dark">
                            Earnings Breakdown
                        </h6>

                        <small class="text-muted">
                            Commission summary
                        </small>

                    </div>

                </div>

            </div>


            <div class="card-body p-4">

                <div class="d-flex flex-column gap-3">


                    {{-- Target Commission --}}
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-4"
                         style="
                            background:{{ $baseComm > 0 ? '#ecfdf5' : '#f8fafc' }};
                            border:1px solid {{ $baseComm > 0 ? '#d1fae5' : '#f1f5f9' }};
                         ">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-3 d-flex align-items-center justify-content-center"
                                 style="
                                    width:40px;
                                    height:40px;
                                    background:#d1fae5;
                                 ">

                                <i class="bi bi-graph-up text-success"></i>

                            </div>

                            <div>

                                <div class="fw-semibold small text-dark">
                                    Target Commission
                                </div>

                                <div class="text-muted mt-1"
                                     style="font-size:11px;">

                                    {{ $structure->commission_percent ?? 0 }}%
                                    on business above target

                                </div>

                            </div>

                        </div>

                        <div class="fw-bold {{ $baseComm > 0 ? 'text-success' : 'text-muted' }}">

                            ₹{{ number_format($baseComm,2) }}

                        </div>

                    </div>


                    {{-- Merchant Commission --}}
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-4" 
                        style=" 
                            background:{{ $recoveryComm > 0 ? '#ecfeff' : '#f8fafc' }}; 
                            border:1px solid {{ $recoveryComm > 0 ? '#cffafe' : '#f1f5f9' }}; 
                        ">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-3 d-flex align-items-center justify-content-center" 
                                style=" 
                                    width:40px; 
                                    height:40px; 
                                    background:#cffafe; 
                                ">

                                <i class="bi bi-shop text-info"></i>

                            </div>

                            <div>

                                <div class="fw-semibold small text-dark">
                                   Marchant Target
                                </div>

                                <div class="text-muted mt-1" style="font-size:11px;">
                                     {{$merchantTarget }}
                                    on recovery
                                </div>

                            </div>

                        </div>

                        <div class="fw-bold text-info">
                            {{ $merchantWonLeads }}
                        </div>

                    </div>
                    
                    {{-- Recovery Commission --}}
                    <div class="d-flex justify-content-between align-items-center p-3 rounded-4"
                         style="
                            background:{{ $recoveryComm > 0 ? '#ecfeff' : '#f8fafc' }};
                            border:1px solid {{ $recoveryComm > 0 ? '#cffafe' : '#f1f5f9' }};
                         ">

                        <div class="d-flex align-items-center gap-3">

                            <div class="rounded-3 d-flex align-items-center justify-content-center"
                                 style="
                                    width:40px;
                                    height:40px;
                                    background:#cffafe;
                                 ">

                                <i class="bi bi-arrow-return-left text-info"></i>

                            </div>

                            <div>

                                <div class="fw-semibold small text-dark">
                                    Recovery Commission
                                </div>

                                <div class="text-muted mt-1"
                                     style="font-size:11px;">

                                    {{ $structure->recovery_percent ?? 0 }}%
                                    on recovery

                                </div>

                            </div>

                        </div>

                        <div class="fw-bold {{ $recoveryComm > 0 ? 'text-info' : 'text-muted' }}">

                            ₹{{ number_format($recoveryComm,2) }}

                        </div>

                    </div>


                    {{-- Total Commission --}}
                    <div class="rounded-4 p-3"
                         style="
                            background:linear-gradient(
                                135deg,
                                #ecfdf5,
                                #eef2ff
                            );
                            border:1px solid #d1fae5;
                         ">

                        <div class="d-flex justify-content-between align-items-center">

                            <div class="d-flex align-items-center gap-3">

                                <div class="rounded-3 d-flex align-items-center justify-content-center"
                                     style="
                                        width:42px;
                                        height:42px;
                                        background:#10b98120;
                                     ">

                                    <i class="bi bi-wallet2 text-success fs-5"></i>

                                </div>

                                <div>

                                    <div class="fw-bold text-dark">
                                        Total Commission Earned
                                    </div>

                                    <div class="text-muted"
                                         style="font-size:11px;">

                                        Target + Recovery commission

                                    </div>

                                </div>

                            </div>


                            <div class="fw-bold text-success fs-4">

                                ₹{{ number_format($totalComm,2) }}

                            </div>

                        </div>

                    </div>


                    {{-- Info --}}
                    <div class="rounded-4 px-3 py-3 d-flex align-items-start gap-2"
                         style="
                            background:#eff6ff;
                            color:#2563eb;
                            font-size:11px;
                            line-height:1.5;
                         ">

                        <i class="bi bi-info-circle-fill fs-5"></i>

                        <div>

                            Commission
                            <strong>
                                {{ $structure->commission_percent ?? 0 }}%
                            </strong>
                            is calculated only on the business amount that
                            <strong>exceeds</strong> your
                            ₹{{ number_format($monthlyTarget,0) }}
                            target.

                        </div>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>

    {{-- Payout History --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center rounded-top-4">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Commission Payout History</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 border-0 text-muted fw-semibold small">Month</th>
                            <th class="border-0 text-muted fw-semibold small">Target</th>
                            <th class="border-0 text-muted fw-semibold small">Target Hit?</th>
                            <th class="border-0 text-muted fw-semibold small">Commission</th>
                            <th class="border-0 text-muted fw-semibold small">Recovery Comm.</th>
                            <th class="border-0 text-muted fw-semibold small">Total Payout</th>
                            <th class="border-0 text-muted fw-semibold small">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $h)
                        <tr>
                            <td class="ps-4 border-0 py-3 fw-bold">{{ date('F', mktime(0,0,0,$h->month,1)) }} {{ $h->year }}</td>
                            <td class="border-0 py-3">₹{{ number_format($h->target_amount, 2) }}</td>
                            <td class="border-0 py-3">
                                @if($h->calculation_details['target_achieved'] ?? false)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">✓ Yes</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2">✗ No</span>
                                @endif
                            </td>
                            <td class="border-0 py-3 text-success fw-semibold">₹{{ number_format($h->commission_earned, 2) }}</td>
                            <td class="border-0 py-3 text-info fw-semibold">₹{{ number_format($h->recovery_earned, 2) }}</td>
                            <td class="border-0 py-3 fw-bold text-dark">₹{{ number_format($h->total_payout, 2) }}</td>
                            <td class="border-0 py-3">
                                @if($h->status === 'paid')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">Paid</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-light"></i>
                                No payout history found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @endif {{-- end $structure check --}}
    @endif {{-- end my_target tab --}}


    {{-- ======================== TEAM TARGET TAB ======================== --}}
    @if($activeTab === 'team_target' && count($teamData) > 0)
    <div class="row g-4 mb-4">
        @foreach($teamData as $data)
        @php
            $tm        = $data['user'];
            $tMetrics  = $data['metrics'];
            $tStruct   = $data['structure'];
            $tTarget   = $tStruct->monthly_target ?? 0;
            $tBusiness = $tMetrics['new_business'] ?? 0;
            $tRecovery = $tMetrics['recovery_business'] ?? 0;
            $tPct      = $tTarget > 0 ? min(100, ($tBusiness / $tTarget) * 100) : 0;
            $tHit      = $tTarget > 0 && $tBusiness >= $tTarget;
            $tColor    = $tHit ? '#198754' : ($tPct >= 50 ? '#0d6efd' : '#ffc107');
            $tComm     = ($tMetrics['base_commission'] ?? 0) + ($tMetrics['recovery_commission'] ?? 0);
            $tEarning  = ($tStruct->basic_salary ?? 0) + $tComm;
        @endphp
        <div class="col-xl-6 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    {{-- Header --}}
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $tm->avatar_url }}" alt="{{ $tm->name }}" class="rounded-circle" width="48" height="48" style="object-fit:cover;">
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark">{{ $tm->name }}</div>
                            <div class="text-muted small">{{ $tm->designation?->name ?? 'Employee' }} · {{ $tm->employee_code }}</div>
                        </div>
                        @if($tHit)
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1" style="font-size:11px;"><i class="bi bi-trophy me-1"></i>Target Hit</span>
                        @else
                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1" style="font-size:11px;"><i class="bi bi-hourglass-split me-1"></i>In Progress</span>
                        @endif
                    </div>

                    {{-- Progress --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small text-muted fw-semibold">Business Achieved</span>
                            <span class="small fw-bold" style="color:{{ $tColor }};">{{ number_format($tPct, 1) }}%</span>
                        </div>
                        <div class="progress mb-1" style="height:10px;border-radius:5px;">
                            <div class="progress-bar" style="width:{{ $tPct }}%;background:{{ $tColor }};border-radius:5px;" role="progressbar"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">₹{{ number_format($tBusiness, 0) }} achieved</small>
                            <small class="text-muted">Target: ₹{{ number_format($tTarget, 0) }}</small>
                        </div>
                    </div>

                    {{-- Mini stats --}}
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-light text-center">
                                <div style="font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase;">Base Salary</div>
                                <div class="fw-bold text-dark" style="font-size:12px;">₹{{ number_format($tStruct->basic_salary ?? 0, 0) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-success bg-opacity-10 text-center">
                                <div style="font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase;">Commission</div>
                                <div class="fw-bold text-success" style="font-size:12px;">₹{{ number_format($tComm, 0) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-primary bg-opacity-10 text-center">
                                <div style="font-size:10px;color:#94a3b8;font-weight:600;text-transform:uppercase;">Est. Earning</div>
                                <div class="fw-bold text-primary" style="font-size:12px;">₹{{ number_format($tEarning, 0) }}</div>
                            </div>
                        </div>
                    </div>

                    @if($tRecovery > 0)
                    <div class="mt-2 d-flex align-items-center gap-2 small text-info">
                        <i class="bi bi-arrow-return-left"></i>
                        <span>Recovery: ₹{{ number_format($tRecovery, 0) }}
                            @if(!$tHit)<span class="text-warning">(locked)</span>@endif
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

@push('scripts')
@endpush
