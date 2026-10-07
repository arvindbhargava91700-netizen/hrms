<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <?php
        $partnerId = auth()->check() ? (auth()->user()->isPartner() ? auth()->id() : (auth()->user()->parent_id ?: auth()->id())) : null;
        
        $getCompanySetting = function ($key) use ($partnerId) {
            $val = null;
            if ($partnerId) {
                $val = \App\Models\PartnerSetting::where('partner_id', $partnerId)->where('key', $key)->value('value');
            }
            if (!$val) {
                $val = \App\Models\PartnerSetting::whereNull('partner_id')->where('key', $key)->value('value');
            }
            if (!$val) {
                $val = \App\Models\PartnerSetting::where('key', $key)->latest()->value('value');
            }
            return $val;
        };

        $companyLogo = $getCompanySetting('company_logo');
        $companyFavicon = $getCompanySetting('company_favicon');
        $companyName = $getCompanySetting('company_name') ?: 'Feetrack';
    ?>
    <title><?php echo e($pageTitle ?? ($title ?? 'Dashboard')); ?> – <?php echo e($companyName); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($companyFavicon): ?>
        <link rel="icon" href="<?php echo e(asset('storage/' . $companyFavicon)); ?>">
    <?php else: ?>
        <link rel="icon" href="<?php echo e(asset('images/favicon.ico')); ?>" type="image/x-icon">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
     <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ── Global Livewire Loading Bar ─────────────────────── */
        #livewire-progress-bar {
            position: fixed;
            top: 0; left: 0;
            width: 0;
            height: 3px;
            background: linear-gradient(90deg, #2563EB, #7C3AED);
            z-index: 99999;
            transition: width 0.3s ease, opacity 0.3s ease;
            opacity: 0;
            box-shadow: 0 0 8px rgba(37,99,235,0.6);
        }
        #livewire-progress-bar.loading {
            opacity: 1;
            animation: progress-indeterminate 1.2s infinite linear;
        }
        @keyframes progress-indeterminate {
            0%   { width: 0%; left: 0; }
            50%  { width: 70%; left: 0; }
            100% { width: 100%; left: 0; }
        }

        /* ── Button loading state ────────────────────────────── */
        [wire\:loading][wire\:target],
        button[wire\:loading] {
            position: relative;
            pointer-events: none;
            opacity: 0.75;
        }
        .ft-btn-spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(255,255,255,0.5);
            border-top-color: #fff;
            border-radius: 50%;
            animation: ft-spin 0.65s linear infinite;
            margin-right: 6px;
            vertical-align: middle;
        }
        .ft-btn-spinner.dark {
            border-color: rgba(0,0,0,0.2);
            border-top-color: #374151;
        }
        @keyframes ft-spin {
            to { transform: rotate(360deg); }
        }

        /* ── Upgrade Button Animations in Sidebar Footer ── */
        @keyframes pulse-ring {
            0% { transform: scale(0.98); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.5); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(37, 99, 235, 0); }
            100% { transform: scale(0.98); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }
        @keyframes flash-icon {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.15); }
        }
        .sidebar-upgrade-compact {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff !important;
            border-radius: 8px;
            padding: 7px 12px;
            text-decoration: none !important;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.78rem;
            font-weight: 700;
            transition: all 0.2s ease;
            animation: pulse-ring 3s infinite;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 10px;
        }
        .sidebar-upgrade-compact:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            color: #ffffff !important;
        }
        .sidebar-upgrade-compact .up-badge {
            background: #ffffff;
            color: #1d4ed8;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 2px 7px;
            border-radius: 50rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.375rem !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }

        .sidebar {
            background-color: #05071e !important;
        }
    </style>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body>
    
    <div id="livewire-progress-bar"></div>

    
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand d-flex flex-column align-items-center justify-content-center p-4 text-center" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($companyLogo): ?>
                <img src="<?php echo e(asset('storage/' . $companyLogo)); ?>" alt="<?php echo e($companyName); ?>" style="width: 130px;max-height: 45px;object-fit: contain;filter: brightness(0) invert(1);">
            <?php else: ?>
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="<?php echo e($companyName); ?>" style="width: 130px; max-height: 45px; object-fit: contain; filter: brightness(0) invert(1);">
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="brand-role mt-2" style="font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; color: var(--primary);">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check()): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isAdmin()): ?>
                        Admin Panel
                    <?php elseif(auth()->user()->role === 'employee'): ?>
                        HRMS Panel
                    <?php elseif(auth()->user()->isPartner()): ?>
                        HRMS Panel
                    <?php else: ?>
                        Customer Panel
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <?php echo e($panelName ?? 'Panel'); ?>

                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <nav class="sidebar-nav" id="sidebar-nav">
            <?php echo $sidebarLinks ?? ''; ?>

        </nav>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check() && auth()->user()->isPartner()): ?>
                <a href="<?php echo e(route('partner.platform-plans')); ?>" class="sidebar-upgrade-compact" title="View Platform Plans">
                    <span class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-lightning-charge-fill text-warning" style="animation: flash-icon 1.5s infinite;"></i>
                        <span>Upgrade Plan</span>
                    </span>
                    <span class="up-badge">
                        Upgrade <i class="bi bi-arrow-right"></i>
                    </span>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="sidebar-footer">

            <form method="POST" action="<?php echo e(route('logout')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="nav-link w-100 border-0 bg-transparent text-start" style="color:#64748B;">
                    <i class="bi bi-box-arrow-left"></i> Sign Out
                </button>
            </form>
        </div>
    </aside>

    
    <div class="main-wrapper">
        
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-icon btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('open')">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-none d-lg-block">
                    <div class="topbar-title"><?php echo e($pageTitle ?? ''); ?></div>
                    <div class="topbar-subtitle"><?php echo e($pageSubtitle ?? ''); ?></div>
                </div>
                <div class="d-lg-none ms-2">
                    <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Feetrack" class="theme-adaptive-logo" style="height: 28px; object-fit: contain;">
                </div>
            </div>
            <div class="topbar-right gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check() && auth()->user()->isPartner()): ?>
                    <a href="<?php echo e(route('partner.wallet')); ?>" class="d-flex align-items-center gap-2 text-decoration-none px-3 py-1 rounded-pill bg-light border shadow-sm" style="color: #1e293b;">
                        <i class="bi bi-wallet2 text-primary fs-6"></i>
                        <span class="fw-bold" style="font-size: 14px;">₹<?php echo e(number_format(auth()->user()->wallet_balance, 2)); ?></span>
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('notification-bell');

$__keyOuter = $__key ?? null;

$__key = null;
$__componentSlots = [];

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-1253824113-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key, $__componentSlots);

echo $__html;

unset($__html);
unset($__key);
$__key = $__keyOuter;
unset($__keyOuter);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
?>
                <button class="btn btn-icon btn-outline-secondary border-0" id="themeToggle" title="Toggle theme">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <div class="dropdown">
                    <img src="<?php echo e(auth()->user()->avatar_url); ?>" alt="<?php echo e(auth()->user()->name); ?>" class="topbar-avatar" data-bs-toggle="dropdown" style="object-fit: cover;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo e(urlencode(auth()->user()->name)); ?>&color=7F9CF5&background=EBF4FF';">
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius:12px;min-width:220px;">
                        <li class="px-3 py-2 border-bottom">
                            <div class="fw-bold fs-14 text-dark"><?php echo e(auth()->user()->name); ?></div>
                            <div class="text-muted fs-12"><?php echo e(auth()->user()->email); ?></div>
                            <div class="badge bg-primary bg-opacity-10 text-primary mt-1 small"><?php echo e(ucfirst(auth()->user()->role)); ?></div>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="<?php echo e(route('partner.profile')); ?>">
                                <i class="bi bi-person-circle text-primary fs-6"></i> My Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider m-0"></li>
                        <li>
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                                    <i class="bi bi-box-arrow-left"></i> Sign Out
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success mx-4 mt-4 fade-in d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill"></i><?php echo e(session('success')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('error')): ?>
            <div class="alert alert-danger mx-4 mt-4 fade-in d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-circle-fill"></i><?php echo e(session('error')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <main class="page-content fade-in">
            <?php echo e($slot); ?>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    <script>
        function initSelect2() {
            $('.select2-searchable').each(function() {
                if (!$(this).data('select2')) {
                    $(this).select2({
                        width: '100%',
                        dropdownParent: $(this).parent()
                    }).on('change', function (e) {
                        // Trigger native change event so Livewire picks it up
                        this.dispatchEvent(new Event('change'));
                    });
                }
            });
        }
        document.addEventListener('livewire:initialized', () => {
            initSelect2();
        });
        document.addEventListener('livewire:navigated', () => {
            initSelect2();
        });
        // Handle DOM updates by Livewire
        Livewire.hook('morph.updated', ({ el, component }) => {
            initSelect2();
        });
    </script>
    <script>
        // Theme toggle
        const html = document.documentElement;
        const btn = document.getElementById('themeToggle');
        
        // Force light mode
        html.setAttribute('data-theme', 'light');
        btn.querySelector('i').className = 'bi bi-moon-stars';

        btn.addEventListener('click', () => {
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            btn.querySelector('i').className = next === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
        });

        // ── Global Livewire Loading Hooks ──────────────────────────
        const progressBar = document.getElementById('livewire-progress-bar');

        document.addEventListener('livewire:navigate', () => progressBar.classList.add('loading'));

        // Intercept ALL Livewire requests
        if (window.Livewire) {
            Livewire.hook('request', ({ component, commit, respond, succeed, fail }) => {
                progressBar.classList.add('loading');
                respond(() => progressBar.classList.remove('loading'));
            });
        }

        // Also handle via DOM mutation for buttons with wire:loading
        document.addEventListener('livewire:request-prechecks', () => progressBar.classList.add('loading'));
        document.addEventListener('livewire:response', () => progressBar.classList.remove('loading'));

        // Inject spinner into every Livewire submit/click button automatically
        function injectSpinners() {
            document.querySelectorAll(
                'button[wire\\:submit], button[wire\\:click], form[wire\\:submit] button[type="submit"], form[wire\\:submit] button:not([type="button"]), button[type="submit"][wire\\:target]'
            ).forEach(btn => {
                if (btn.dataset.ftSpinner) return; // already processed
                btn.dataset.ftSpinner = '1';

                const isDark = getComputedStyle(btn).backgroundColor === 'rgba(0, 0, 0, 0)'
                    || btn.classList.contains('btn-outline-secondary')
                    || btn.classList.contains('btn-outline-danger')
                    || btn.classList.contains('btn-outline-primary');

                // Create spinner element (hidden by default)
                const spinner = document.createElement('span');
                spinner.className = 'ft-btn-spinner' + (isDark ? ' dark' : '');
                spinner.style.display = 'none';
                spinner.setAttribute('data-ft-spin', '');
                btn.prepend(spinner);
            });
        }

        // Show/hide spinners on Livewire network activity
        function setSpinnersVisible(visible) {
            document.querySelectorAll('[data-ft-spin]').forEach(s => {
                s.style.display = visible ? 'inline-block' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', injectSpinners);
        document.addEventListener('livewire:initialized', injectSpinners);
        document.addEventListener('livewire:update', injectSpinners);

        // Wire into Livewire lifecycle
        document.addEventListener('livewire:request', () => {
            progressBar.classList.add('loading');
        });
        document.addEventListener('livewire:response', () => {
            progressBar.classList.remove('loading');
            // Re-inject for newly rendered buttons
            setTimeout(injectSpinners, 100);
        });
        // ── Firebase Web Push Notifications ──────────────────────────
        // Replace with your actual Firebase config
        const firebaseConfig = {
            apiKey: "<?php echo e(env('FIREBASE_API_KEY', 'YOUR_API_KEY')); ?>",
            authDomain: "<?php echo e(env('FIREBASE_AUTH_DOMAIN', 'YOUR_AUTH_DOMAIN')); ?>",
            projectId: "<?php echo e(env('FIREBASE_PROJECT_ID', 'YOUR_PROJECT_ID')); ?>",
            storageBucket: "<?php echo e(env('FIREBASE_STORAGE_BUCKET', 'YOUR_STORAGE_BUCKET')); ?>",
            messagingSenderId: "<?php echo e(env('FIREBASE_MESSAGING_SENDER_ID', 'YOUR_MESSAGING_SENDER_ID')); ?>",
            appId: "<?php echo e(env('FIREBASE_APP_ID', 'YOUR_APP_ID')); ?>",
            measurementId: "<?php echo e(env('FIREBASE_MEASUREMENT_ID', 'YOUR_MEASUREMENT_ID')); ?>"
        };

        if (firebaseConfig.apiKey !== 'YOUR_API_KEY') {
            // Dynamically load Firebase SDK
            const scriptApp = document.createElement('script');
            scriptApp.src = "https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js";
            document.head.appendChild(scriptApp);

            scriptApp.onload = () => {
                const scriptMessaging = document.createElement('script');
                scriptMessaging.src = "https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js";
                document.head.appendChild(scriptMessaging);

                scriptMessaging.onload = () => {
                    firebase.initializeApp(firebaseConfig);
                    const messaging = firebase.messaging();

                    // Request permission and get token
                    Notification.requestPermission().then((permission) => {
                        if (permission === 'granted') {
                            const vapidKey = "<?php echo e(env('FIREBASE_VAPID_KEY', '')); ?>";
                            const tokenOptions = vapidKey ? { vapidKey: vapidKey } : {};
                            
                            messaging.getToken(tokenOptions)
                                .then((currentToken) => {
                                    if (currentToken) {
                                        // Send the token to your server
                                        fetch('<?php echo e(route("fcm.token")); ?>', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                            },
                                            body: JSON.stringify({ fcm_token: currentToken })
                                        });
                                    }
                                }).catch((err) => console.log('An error occurred while retrieving token. ', err));
                        }
                    });

                    // Handle incoming messages while app is in foreground
                    messaging.onMessage((payload) => {
                        console.log('Message received. ', payload);
                        // Force Livewire NotificationBell to refresh
                        if (window.Livewire) {
                            Livewire.dispatch('refreshNotifications');
                        }
                    });
                };
            };
        }
    </script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/layouts/app.blade.php ENDPATH**/ ?>