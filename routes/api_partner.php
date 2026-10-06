<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Partner\AuthController;

// Partner API Routes
// These routes are prefixed with /api/partner automatically by routes/api.php

Route::middleware('force.json')->group(function () {
    
    // Auth Routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    Route::middleware('api.auth:partner_api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::match(['put', 'post'], '/profile', [AuthController::class, 'updateProfile']);
        
        // KYC Routes
        Route::get('/kyc/requirements', [\App\Http\Controllers\Api\Partner\PartnerKycController::class, 'requirements']);
        Route::get('/kyc', [\App\Http\Controllers\Api\Partner\PartnerKycController::class, 'show']);
        Route::post('/kyc', [\App\Http\Controllers\Api\Partner\PartnerKycController::class, 'submit']);
        // Wallet Routes
        Route::get('/wallet/reserve-history', [\App\Http\Controllers\Api\Partner\WalletController::class, 'reserveHistory']);
        
        // Subscription Management
        Route::post('/subscriptions/{id}/approve-cancellation', [\App\Http\Controllers\Api\Partner\SubscriptionController::class, 'approveCancellation']);
        Route::post('/subscriptions/{id}/reject-cancellation', [\App\Http\Controllers\Api\Partner\SubscriptionController::class, 'rejectCancellation']);
    });
});
