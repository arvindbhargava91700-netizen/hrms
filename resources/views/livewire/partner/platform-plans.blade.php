<div>
    <style>
        /* ─── Premium Platform Plans UI ─── */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        .plans-wrap { font-family: 'Inter', sans-serif; }

        /* ── Current Plan Banner ── */
        .active-plan-banner {
            background: linear-gradient(135deg, #1d4ed8 0%, #0f172a 100%);
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 32px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(37,99,235,0.25);
        }
        .active-plan-banner::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(255,255,255,0.10) 0%, transparent 70%);
            border-radius: 50%;
        }
        .active-plan-banner::after {
            content: '';
            position: absolute;
            bottom: -40px; left: 30%;
            width: 160px; height: 160px;
            background: radial-gradient(circle, rgba(99,179,237,0.12) 0%, transparent 70%);
            border-radius: 50%;
        }
        .pending-plan-banner {
            background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
            border-radius: 20px;
            padding: 28px 32px;
            margin-bottom: 32px;
            box-shadow: 0 12px 40px rgba(245,158,11,0.25);
        }

        /* ── Package Cards ── */
        .plan-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 24px;
            padding: 32px 26px;
            transition: all 0.35s cubic-bezier(0.4,0,0.2,1);
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }
        .plan-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #06b6d4);
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 24px 24px 0 0;
        }
        .plan-card:hover {
            transform: translateY(-8px);
            border-color: #2563eb;
            box-shadow: 0 24px 60px rgba(37,99,235,0.15);
        }
        .plan-card:hover::before { opacity: 1; }
        .plan-card.active-plan {
            border: 2px solid #2563eb;
            box-shadow: 0 12px 40px rgba(37,99,235,0.12);
        }
        .plan-card.active-plan::before { opacity: 1; }

        .plan-badge-trial {
            position: absolute;
            top: 18px; right: 18px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 999px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(16,185,129,0.3);
        }

        .plan-name {
            font-size: 1.15rem;
            font-weight: 800;
            color: #2563eb;
            margin-bottom: 4px;
            letter-spacing: -0.3px;
        }
        .plan-price-row {
            display: flex;
            align-items: baseline;
            gap: 3px;
            padding: 18px 0;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 20px;
        }
        .plan-currency { font-size: 1.5rem; font-weight: 700; color: #0f172a; }
        .plan-price    { font-size: 3.4rem; font-weight: 900; color: #0f172a; line-height: 1; letter-spacing: -3px; }
        .plan-period   { font-size: 0.8rem; color: #94a3b8; font-weight: 500; margin-left: 4px; }

        .plan-features {
            list-style: none;
            padding: 0; margin: 0 0 24px;
            flex-grow: 1;
        }
        .plan-features li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 0;
            font-size: 0.83rem;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid #f8fafc;
        }
        .plan-features li:last-child { border-bottom: none; }
        .plan-features li .feat-icon {
            width: 26px; height: 26px;
            border-radius: 7px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.75rem;
            flex-shrink: 0;
        }
        .feat-icon.blue   { background: #dbeafe; color: #2563eb; }
        .feat-icon.green  { background: #d1fae5; color: #059669; }
        .feat-icon.orange { background: #fef3c7; color: #d97706; }
        .feat-icon.purple { background: #ede9fe; color: #7c3aed; }
        .feat-icon.cyan   { background: #cffafe; color: #0891b2; }

        .btn-plan-outline {
            border: 2px solid #2563eb;
            color: #2563eb;
            background: transparent;
            border-radius: 50px;
            padding: 10px 20px;
            font-weight: 700;
            font-size: 0.875rem;
            width: 100%;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
        }
        .btn-plan-outline:hover {
            background: #2563eb;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37,99,235,0.3);
        }
        .btn-plan-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 11px 20px;
            font-weight: 700;
            font-size: 0.875rem;
            width: 100%;
            transition: all 0.25s ease;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 6px 20px rgba(37,99,235,0.35);
        }
        .btn-plan-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(37,99,235,0.45);
        }
        .btn-plan-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 11px 20px;
            font-weight: 700;
            width: 100%;
            font-family: 'Inter', sans-serif;
        }
        .btn-plan-disabled {
            background: #f1f5f9;
            color: #94a3b8;
            border: 1.5px solid #e2e8f0;
            border-radius: 50px;
            padding: 11px 20px;
            font-weight: 600;
            width: 100%;
            cursor: not-allowed;
            font-family: 'Inter', sans-serif;
        }

        /* ── Detail Modal ── */
        .pkg-modal-header {
            background: linear-gradient(135deg, #2563eb 0%, #0f172a 100%);
            padding: 28px 28px 24px;
            position: relative;
            overflow: hidden;
        }
        .pkg-modal-header::before {
            content: '';
            position: absolute;
            top: -50px; right: -50px;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .pkg-icon-box {
            width: 52px; height: 52px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
        }
        .pkg-modal-body { background: #f8fafc; }

        .modal-section {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 14px;
        }
        .modal-section-label {
            font-size: 0.68rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #94a3b8;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .modal-section-label i { font-size: 0.8rem; }

        .fee-box-online {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 1.5px solid #bbf7d0;
            border-radius: 12px;
            padding: 16px;
        }
        .fee-box-offline {
            background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
            border: 1.5px solid #fde68a;
            border-radius: 12px;
            padding: 16px;
        }
        .fee-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .fee-amount { font-size: 1.6rem; font-weight: 900; letter-spacing: -1px; line-height: 1.1; }
        .fee-sub    { font-size: 0.72rem; font-weight: 500; margin-top: 2px; opacity: 0.8; }
        .fee-tier-row { font-size: 0.72rem; font-weight: 600; color: #166534; padding: 2px 0; }

        .quota-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 16px;
            text-align: center;
        }
        .quota-value { font-size: 1.3rem; font-weight: 800; color: #0f172a; }
        .quota-label { font-size: 0.72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }

        .module-chip {
            display: inline-flex; align-items: center; gap: 6px;
            background: #eff6ff; border: 1px solid #bfdbfe;
            color: #1d4ed8; border-radius: 999px;
            padding: 5px 14px; font-size: 0.79rem; font-weight: 700;
        }
        .gw-tag {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 10px; padding: 6px 14px;
            font-size: 0.79rem; font-weight: 600; color: #334155;
        }
        .notif-on  { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; border-radius: 8px; padding: 4px 12px; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; }
        .notif-off { background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; border-radius: 8px; padding: 4px 12px; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }

        .no-plan-alert {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
            border: 1.5px solid #fde68a;
            border-radius: 14px;
            padding: 16px 20px;
            color: #92400e;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 28px;
            display: flex; align-items: center; gap: 10px;
        }
    </style>

    <div class="plans-wrap">

        @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Pending Banner --}}
        @if($pendingSubscription)
            <div class="pending-plan-banner d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div class="position-relative" style="z-index:2;">
                    <p class="text-white fw-bold mb-0" style="font-size:0.72rem; letter-spacing:1px; text-transform:uppercase; opacity:0.7;">Pending Approval</p>
                    <h4 class="text-white fw-bold mb-1">{{ $pendingSubscription->package->name }}</h4>
                    <p class="text-white mb-0 small" style="opacity:0.75;">Your request is under review. We'll notify you once it's approved.</p>
                </div>
                <div class="d-flex gap-2 position-relative" style="z-index:2;">
                    <button wire:click="cancelPendingSubscription" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-bold" style="font-size:0.85rem;">
                        <i class="bi bi-x-circle me-1"></i> Cancel Request
                    </button>
                    <span class="badge bg-white text-warning px-3 py-2 rounded-pill fw-bold d-flex align-items-center" style="font-size:0.85rem;">
                        <i class="bi bi-hourglass-split me-1"></i> Pending
                    </span>
                </div>
            </div>
        @endif

        {{-- Active Plan Banner --}}
        @if($activeSubscription)
            <div class="active-plan-banner d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div class="position-relative" style="z-index:2;">
                    <p class="text-white fw-bold mb-1" style="font-size:0.7rem; letter-spacing:1.2px; text-transform:uppercase; opacity:0.65;">Current Active Plan</p>
                    <h3 class="text-white fw-bold mb-1" style="font-size:1.8rem; letter-spacing:-0.5px;">{{ $activeSubscription->package->name }}</h3>
                    <p class="mb-0 small" style="color:rgba(255,255,255,0.65);">
                        @if($activeSubscription->expires_at)
                            Expires on <span class="text-white fw-bold">{{ $activeSubscription->expires_at->format('d M Y') }}</span>
                            &nbsp;&bull;&nbsp; {{ intval(now()->diffInDays($activeSubscription->expires_at, false)) }} days left
                        @else
                            <span class="text-white fw-bold">Lifetime Validity</span>
                        @endif
                    </p>
                </div>
                <div class="position-relative" style="z-index:2;">
                    <span class="badge bg-white text-primary px-4 py-2 rounded-pill fw-bold shadow-sm" style="font-size:0.85rem;">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i> Active
                    </span>
                </div>
            </div>
        @else
            <div class="no-plan-alert">
                <i class="bi bi-info-circle-fill fs-5"></i>
                <span>You don't have an active subscription. Choose a plan below to unlock full platform access.</span>
            </div>
        @endif

        {{-- Section Header --}}
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h5 class="fw-800 mb-1" style="font-size:1.2rem; color:#0f172a; font-weight:800;">Available Packages</h5>
                <p class="mb-0 small" style="color:#64748b;">Choose the right plan to grow your business</p>
            </div>
        </div>

        {{-- Plans Grid --}}
        <div class="row g-4 mb-5 justify-content-center">
            @foreach($packages as $package)
                @php $isActive = $activeSubscription && $activeSubscription->partner_package_id == $package->id; @endphp
                <div class="col-xl-3 col-lg-4 col-md-6 col-12 d-flex">
                    <div class="plan-card w-100 {{ $isActive ? 'active-plan' : '' }}">

                        @if($package->is_free_trial)
                            <div class="plan-badge-trial">Free Trial</div>
                        @endif

                        <div class="plan-name">{{ $package->name }}</div>

                        <div class="plan-price-row">
                            <span class="plan-currency">₹</span>
                            <span class="plan-price">{{ number_format($package->price, 0) }}</span>
                            <span class="plan-period">/ {{ $package->duration_days == 0 ? 'lifetime' : $package->duration_days . ' days' }}</span>
                        </div>

                        <ul class="plan-features">
                            <li>
                                <span class="feat-icon blue"><i class="bi bi-clock-history"></i></span>
                                {{ $package->duration_days == 0 ? 'Lifetime Access' : 'Valid for ' . $package->duration_days . ' Days' }}
                            </li>
                            <li>
                                <span class="feat-icon green"><i class="bi bi-wifi"></i></span>
                                Online: {{ $package->commission_value }}{{ $package->commission_type == 'percent' ? '%' : ' ₹' }} Platform Fee (with GST)
                            </li>
                            <li>
                                <span class="feat-icon orange"><i class="bi bi-cash-coin"></i></span>
                                Offline: {{ $package->offline_commission_value ?? 0 }}{{ ($package->offline_commission_type ?? 'fixed') == 'percent' ? '%' : ' ₹' }} Platform Fee (with GST)
                            </li>
                            <li>
                                <span class="feat-icon cyan"><i class="bi bi-grid"></i></span>
                                {{ $package->category_limit ? 'Up to ' . $package->category_limit . ' Categories' : 'Unlimited Categories' }}
                            </li>
                            <li>
                                <span class="feat-icon blue"><i class="bi bi-card-list"></i></span>
                                {{ $package->listing_limit ? 'Up to ' . $package->listing_limit . ' Listings' : 'Unlimited Listings' }}
                            </li>
                            @if($package->systemModules && $package->systemModules->count() > 0)
                                <li>
                                    <span class="feat-icon purple"><i class="bi bi-puzzle"></i></span>
                                    {{ $package->systemModules->count() }} Premium Module{{ $package->systemModules->count() > 1 ? 's' : '' }} Included
                                </li>
                            @endif
                        </ul>

                        <div class="mt-auto d-flex flex-column gap-2">
                            <button class="btn-plan-outline"
                                    wire:click="viewPackageDetails('{{ $package->id }}')"
                                    wire:loading.attr="disabled">
                                <i class="bi bi-eye me-1"></i> View Details
                            </button>

                            @if($pendingSubscription)
                                <button class="btn-plan-disabled" disabled>
                                    <i class="bi bi-hourglass-split me-1"></i> Request Pending
                                </button>
                            @elseif($isActive && $package->duration_days == 0)
                                <button class="btn-plan-success" disabled style="opacity:1; cursor:default;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Current Plan
                                </button>
                            @elseif($isActive)
                                @php
                                    $isExpired = $activeSubscription->expires_at && $activeSubscription->expires_at->endOfDay()->isPast();
                                @endphp
                                @if($isExpired)
                                    <button class="btn-plan-primary" data-bs-toggle="modal" data-bs-target="#viewPackageModal" wire:click="viewPackageDetails('{{ $package->id }}')">
                                        <i class="bi bi-arrow-repeat me-1"></i> Renew Plan
                                    </button>
                                @else
                                    <button class="btn-plan-disabled" disabled title="Plan is currently active">
                                        <i class="bi bi-arrow-repeat me-1"></i> Renew Plan
                                    </button>
                                @endif
                            @else
                                <button class="btn-plan-primary" data-bs-toggle="modal" data-bs-target="#viewPackageModal" wire:click="viewPackageDetails('{{ $package->id }}')">
                                    <i class="bi bi-lightning-charge-fill me-1"></i> Subscribe Now
                                </button>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{-- ─── Package Detail Modal ─── --}}
        <div class="modal fade" id="viewPackageModal" tabindex="-1" wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                    @if($viewPackage)
                    @php
                        $pkg = $viewPackage;
                        $pkgIsActive = $activeSubscription && $activeSubscription->partner_package_id == $pkg->id;
                        $gwLabels = [
                            'upi_autopay'  => ['label'=>'UPI AutoPay',     'icon'=>'bi-phone-vibrate', 'color'=>'#16a34a'],
                            'enach'        => ['label'=>'eNACH Auto Debit','icon'=>'bi-bank',          'color'=>'#2563eb'],
                            'netbanking'   => ['label'=>'Net Banking',      'icon'=>'bi-globe',         'color'=>'#0891b2'],
                            'credit_card'  => ['label'=>'Credit Card',      'icon'=>'bi-credit-card',   'color'=>'#dc2626'],
                            'debit_card'   => ['label'=>'Debit Card',       'icon'=>'bi-credit-card-2-front','color'=>'#d97706'],
                            'wallet'       => ['label'=>'Digital Wallet',   'icon'=>'bi-wallet2',       'color'=>'#7c3aed'],
                            'cash'         => ['label'=>'Cash / Offline',   'icon'=>'bi-cash-coin',     'color'=>'#64748b'],
                        ];
                    @endphp

                    {{-- Modal Header --}}
                    <div class="pkg-modal-header">
                        <div class="d-flex align-items-center justify-content-between position-relative" style="z-index:2;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="pkg-icon-box">
                                    <i class="bi bi-box-seam text-white fs-4"></i>
                                </div>
                                <div>
                                    <p class="text-white mb-0" style="font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; opacity:0.65;">Package Details</p>
                                    <h4 class="text-white fw-bold mb-1" style="letter-spacing:-0.3px;">{{ $pkg->name }}</h4>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="badge bg-white text-primary rounded-pill px-3 py-1" style="font-size:0.78rem; font-weight:700;">
                                            ₹{{ number_format($pkg->price, 0) }} / {{ $pkg->duration_days == 0 ? 'Lifetime' : $pkg->duration_days . ' Days' }}
                                        </span>
                                        @if($pkg->is_free_trial)
                                            <span class="badge bg-success rounded-pill px-3 py-1" style="font-size:0.78rem; font-weight:700;">Free Trial</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="modal-body p-4 pkg-modal-body" style="max-height: 72vh; overflow-y: auto;">

                        {{-- Platform Fee --}}
                        <div class="modal-section">
                            <p class="modal-section-label">
                                <i class="bi bi-percent"></i> Platform Fee (with GST)
                            </p>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="fee-box-online">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-wifi text-success"></i>
                                            <span class="fee-label text-success">Online Payment</span>
                                        </div>
                                        <div class="fee-amount" style="color:#15803d;">
                                            {{ $pkg->commission_value }}{{ $pkg->commission_type == 'percent' ? '%' : ' ₹' }}
                                        </div>
                                        <div class="fee-sub text-success">base online rate</div>
                                        @if(!empty($pkg->commission_ranges))
                                            <div class="border-top border-success border-opacity-25 pt-2 mt-2">
                                                <div style="font-size:0.7rem;" class="fw-bold text-success mb-1">Tiered Ranges:</div>
                                                @foreach($pkg->commission_ranges as $r)
                                                    <div class="fee-tier-row">
                                                        ₹{{ $r['min_amount'] ?? 0 }} – {{ !empty($r['max_amount']) ? '₹'.$r['max_amount'] : 'Above' }}:
                                                        <strong>{{ $r['value'] }}{{ ($r['type'] ?? 'percent') === 'percent' ? '%' : '₹' }}</strong>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="fee-box-offline">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <i class="bi bi-cash-coin text-warning"></i>
                                            <span class="fee-label text-warning">Offline / Cash</span>
                                        </div>
                                        <div class="fee-amount" style="color:#b45309;">
                                            {{ $pkg->offline_commission_value ?? 0 }}{{ ($pkg->offline_commission_type ?? 'fixed') == 'percent' ? '%' : ' ₹' }}
                                        </div>
                                        <div class="fee-sub" style="color:#92400e;">base offline rate</div>
                                        @if(!empty($pkg->offline_commission_ranges))
                                            <div class="border-top border-warning border-opacity-25 pt-2 mt-2">
                                                <div style="font-size:0.7rem; color:#92400e;" class="fw-bold mb-1">Tiered Ranges:</div>
                                                @foreach($pkg->offline_commission_ranges as $r)
                                                    <div style="font-size:0.72rem; color:#92400e; font-weight:600; padding:2px 0;">
                                                        ₹{{ $r['min_amount'] ?? 0 }} – {{ !empty($r['max_amount']) ? '₹'.$r['max_amount'] : 'Above' }}:
                                                        <strong>{{ $r['value'] }}{{ ($r['type'] ?? 'percent') === 'percent' ? '%' : '₹' }}</strong>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Usage Quotas --}}
                        <div class="modal-section">
                            <p class="modal-section-label">
                                <i class="bi bi-sliders"></i> Usage Quotas
                            </p>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="quota-item">
                                        <div class="quota-value">{{ $pkg->category_limit ?: '∞' }}</div>
                                        <div class="quota-label">{{ $pkg->category_limit ? '' : 'Unlimited ' }}Categories</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="quota-item">
                                        <div class="quota-value">{{ $pkg->listing_limit ?: '∞' }}</div>
                                        <div class="quota-label">{{ $pkg->listing_limit ? '' : 'Unlimited ' }}Listings</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Included Modules --}}
                        <div class="modal-section">
                            <p class="modal-section-label">
                                <i class="bi bi-puzzle"></i> Included Modules
                            </p>
                            @if($pkg->systemModules && $pkg->systemModules->count() > 0)
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($pkg->systemModules as $mod)
                                        <span class="module-chip">
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            {{ $mod->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-info-circle me-1"></i> All core modules included — no restrictions.
                                </p>
                            @endif
                        </div>

                        {{-- Supported Payment Gateways --}}
                        @if(!empty($pkg->payment_gateways) && count($pkg->payment_gateways) > 0)
                        <div class="modal-section">
                            <p class="modal-section-label">
                                <i class="bi bi-credit-card-2-front"></i> Supported Payment Gateways
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($pkg->payment_gateways as $gw)
                                    @if(isset($gwLabels[$gw]))
                                        <span class="gw-tag">
                                            <i class="bi {{ $gwLabels[$gw]['icon'] }}" style="color:{{ $gwLabels[$gw]['color'] }};"></i>
                                            {{ $gwLabels[$gw]['label'] }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Notification Channels --}}
                        <div class="modal-section mb-0">
                            <p class="modal-section-label">
                                <i class="bi bi-bell"></i> Notification Channels
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="{{ $pkg->email_notification ? 'notif-on' : 'notif-off' }}">
                                    <i class="bi bi-envelope{{ $pkg->email_notification ? '-check-fill' : '' }}"></i> Email
                                </span>
                                <span class="{{ $pkg->app_notification ? 'notif-on' : 'notif-off' }}">
                                    <i class="bi bi-bell{{ $pkg->app_notification ? '-fill' : '' }}"></i> App Push
                                </span>
                                <span class="{{ $pkg->sms_notification ? 'notif-on' : 'notif-off' }}">
                                    <i class="bi bi-chat-dots{{ $pkg->sms_notification ? '-fill' : '' }}"></i> SMS
                                </span>
                                <span class="{{ $pkg->whatsapp_notification ? 'notif-on' : 'notif-off' }}">
                                    <i class="bi bi-whatsapp"></i> WhatsApp
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 p-4 pt-0">
                        <div class="w-100">
                            @if($pendingSubscription)
                                <button class="btn btn-secondary rounded-pill px-4 fw-bold w-100" disabled>Request Pending</button>
                            @elseif($pkgIsActive && $pkg->duration_days == 0)
                                <button class="btn btn-success rounded-pill px-4 fw-bold w-100" disabled>
                                    <i class="bi bi-check-circle-fill me-1"></i> Current Plan
                                </button>
                            @elseif($pkgIsActive && !($activeSubscription->expires_at && $activeSubscription->expires_at->endOfDay()->isPast()))
                                <button class="btn btn-outline-secondary rounded-pill px-4 fw-bold w-100" disabled>
                                    <i class="bi bi-arrow-repeat me-1"></i> Renew Plan (Currently Active)
                                </button>
                            @else
                                @php
                                    $payable = (float) $pkg->price;
                                    $walletBal = (float) auth()->user()->wallet_balance;
                                    $walletUsed = $this->useWallet ? min($payable, $walletBal) : 0;
                                    $onlinePayable = $payable - $walletUsed;
                                @endphp

                                <div class="mb-3 text-end w-100 p-3 bg-light rounded border border-light-subtle shadow-sm">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted fw-bold">Package Price:</span>
                                        <span class="fw-bold fs-5 text-dark">₹{{ number_format($payable, 2) }}</span>
                                    </div>
                                    @if($walletBal > 0)
                                    <div class="d-flex justify-content-between mb-2 align-items-center">
                                        <div class="form-check form-switch text-start m-0 p-0 d-flex align-items-center gap-2">
                                            <input class="form-check-input m-0" type="checkbox" role="switch" id="useWalletSwitch" wire:model.live="useWallet" style="cursor:pointer; width:40px; height:20px;">
                                            <label class="form-check-label fw-bold text-muted" for="useWalletSwitch" style="cursor:pointer; font-size:0.9rem;">
                                                Use Wallet Balance (₹{{ number_format($walletBal, 2) }})
                                            </label>
                                        </div>
                                        @if($walletUsed > 0)
                                            <span class="fw-bold fs-5 text-success">- ₹{{ number_format($walletUsed, 2) }}</span>
                                        @else
                                            <span class="fw-bold fs-5 text-muted">- ₹0.00</span>
                                        @endif
                                    </div>
                                    @endif
                                    <div class="d-flex justify-content-between pt-2 mt-2 border-top border-secondary border-opacity-25">
                                        <span class="text-dark fw-bolder fs-5">Amount to Pay Online:</span>
                                        <span class="text-primary fw-bolder fs-4">₹{{ number_format($onlinePayable, 2) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 w-100">
                                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary rounded-pill px-4 fw-bold shadow flex-grow-1"
                                            style="background: linear-gradient(135deg,#2563eb,#1d4ed8); border:none;"
                                            data-bs-dismiss="modal"
                                            wire:click="processSubscription('{{ $pkg->id }}')">
                                        @if($onlinePayable > 0)
                                            <i class="bi bi-credit-card me-1"></i> Proceed to Pay ₹{{ number_format($onlinePayable, 2) }}
                                        @else
                                            <i class="bi bi-check-circle-fill me-1"></i> Confirm Subscription
                                        @endif
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>

                    @else
                        <div class="modal-body p-5 text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- /.plans-wrap --}}

    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('show-package-modal', () => {
                let el = document.getElementById('viewPackageModal');
                if (el) {
                    let m = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
                    m.show();
                }
            });
        });
    </script>
    @endpush
</div>
