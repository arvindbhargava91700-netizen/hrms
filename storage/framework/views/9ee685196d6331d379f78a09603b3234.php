<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['title' => 'Feetrack', 'maxWidth' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['title' => 'Feetrack', 'maxWidth' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="description" content="Feetrack is a powerful Multi-Service Subscription Platform for partners and customers. Manage your services seamlessly.">
    <meta name="keywords" content="Feetrack, Multi-Service, Subscription Platform, Management, Partners, Customers">
    <meta property="og:title" content="Feetrack - Multi-Service Subscription Platform">
    <meta property="og:description" content="Feetrack is a powerful Multi-Service Subscription Platform for partners and customers. Manage your services seamlessly.">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#2563eb">
    <title><?php echo e($title ?? 'Feetrack'); ?> – Feetrack</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
    <link rel="icon" href="<?php echo e(asset('images/favicon.ico')); ?>" type="image/x-icon">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card fade-in" style="<?php echo e($maxWidth ? "max-width: {$maxWidth} !important;" : ''); ?>">
            <div class="auth-logo bg-transparent d-flex justify-content-center align-items-center mb-4">
                <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Feetrack Logo" style="width: 160px; height: auto; max-height: 60px; object-fit: contain;" class="theme-adaptive-logo">
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?php echo e($errors->first()); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php echo e($slot); ?>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\life_infotech\hrms\resources\views/components/layouts/auth.blade.php ENDPATH**/ ?>