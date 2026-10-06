<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Feetrack is a powerful Multi-Service Subscription Platform for partners and customers. Manage your services seamlessly.">
    <meta name="keywords" content="Feetrack, Multi-Service, Subscription Platform, Management, Partners, Customers">
    <meta property="og:title" content="Feetrack - Multi-Service Subscription Platform">
    <meta property="og:description" content="Feetrack is a powerful Multi-Service Subscription Platform for partners and customers. Manage your services seamlessly.">
    <meta property="og:type" content="website">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Feetrack' }} – Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card fade-in">
            <div class="auth-logo bg-transparent d-flex justify-content-center align-items-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Feetrack Logo" style="width: 160px; height: auto; max-height: 60px; object-fit: contain;" class="theme-adaptive-logo">
            </div>

            @if(session('success'))
                <div class="alert alert-success mb-3"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger mb-3">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @livewireScripts
</body>
</html>
