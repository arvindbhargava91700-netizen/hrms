<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} – Feetrack</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
    @livewireStyles
    @stack('styles')
</head>
<body>
    {{-- ── Sidebar ───────────────────────────── --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand d-flex flex-column align-items-center justify-content-center p-4 text-center" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
            <img src="{{ asset('images/logo.png') }}" alt="Feetrack" style="width: 130px; max-height: 45px; object-fit: contain; filter: brightness(0) invert(1);">
            <div class="brand-role mt-2" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--primary);">
                {{ $panelName ?? 'Admin Panel' }}
            </div>
        </div>

        <nav class="sidebar-nav">
            {!! $sidebarLinks ?? '' !!}
        </nav>

        <div class="sidebar-footer">
            @if(auth()->check() && auth()->user()->isPartner())
                <a href="{{ route('partner.platform-plans') }}" class="sidebar-upgrade-compact" title="View Platform Plans">
                    <span class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-lightning-charge-fill text-warning" style="animation: flash-icon 1.5s infinite;"></i>
                        <span>Upgrade Plan</span>
                    </span>
                    <span class="up-badge">
                        Upgrade <i class="bi bi-arrow-right"></i>
                    </span>
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start" style="color:#64748B;">
                    <i class="bi bi-box-arrow-left"></i> Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main wrapper ──────────────────────── --}}
    <div class="main-wrapper">
        {{-- Topbar --}}
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-icon btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-lg-block">
                    <div class="topbar-title">{{ $pageTitle ?? '' }}</div>
                    <div class="topbar-subtitle">{{ $pageSubtitle ?? '' }}</div>
                </div>
                <div class="d-lg-none ms-2">
                    <img src="{{ asset('images/logo.png') }}" alt="Feetrack" class="theme-adaptive-logo" style="height: 28px; object-fit: contain;">
                </div>
            </div>
            <div class="topbar-right">
                <button class="btn btn-icon btn-outline-secondary" id="themeToggle" title="Toggle theme">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <div class="dropdown">
                    <img src="{{ auth()->user()->avatar_url }}" alt="avatar" class="topbar-avatar" data-bs-toggle="dropdown">
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius:12px;min-width:200px;">
                        <li class="px-3 py-2">
                            <div class="fw-600 fs-14">{{ auth()->user()->name }}</div>
                            <div class="text-muted fs-12">{{ auth()->user()->email }}</div>
                        </li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                                    <i class="bi bi-box-arrow-right"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="page-content">
            {{ $slot }}
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
