<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CustomerKycController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\JobPostController;
use App\Http\Controllers\Api\JobPostReferralController;
use App\Http\Controllers\Api\ListingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PartnerDashboardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\InvoiceReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware('force.json')->group(function () {

    // ── Auth (Public) ─────────────────────────────────────────────
    Route::post('/register/request-otp', [AuthController::class, 'registerRequestOtp']);
    Route::post('/register/resend-otp', [AuthController::class, 'resendRegisterOtp']);
    Route::post('/register/verify', [AuthController::class, 'registerVerify']);
    Route::post('/login/request-otp', [AuthController::class, 'loginRequestOtp']);
    Route::post('/login/verify', [AuthController::class, 'loginVerify']);

    // ── Browse (Public) ───────────────────────────────────────────
    Route::get('/app-banners', [ListingController::class, 'appBanners']);
    Route::get('/categories', [ListingController::class, 'categories']);
    Route::get('/listings', [ListingController::class, 'listings']);

    // ── Listing Detail APIs (Public) ──────────────────────────────
    Route::prefix('listings/{id}')->group(function () {
        Route::get('/banners', [ListingController::class, 'banners']);    // banner image list
        Route::get('/profile', [ListingController::class, 'profile']);    // full profile + distance
        Route::get('/facilities', [ListingController::class, 'facilities']); // custom fields as facilities
        Route::get('/staff', [ListingController::class, 'staff']);      // trainers / staff
        Route::get('/staff/{staffId}', [ListingController::class, 'staffProfile']); // trainer / staff profile
        Route::get('/branches', [ListingController::class, 'branches']);   // other branches
        Route::get('/reviews', [ListingController::class, 'reviews']);    // customer reviews
        Route::get('/durations', [ListingController::class, 'durations']); // available plan durations
        Route::get('/plans', [ListingController::class, 'plans']);      // plans + batch times
        Route::get('/floors', [ListingController::class, 'floors']);     // available floors
        Route::get('/rooms', [ListingController::class, 'rooms']);      // available rooms
        Route::get('/rooms/{roomId}', [ListingController::class, 'roomDetails']); // room details with packages
    });

    // ── Coupons (Public) ─────────────────────────────────────────
    Route::get('/coupons', [CouponController::class, 'index']);       // list available coupons
    Route::post('/coupons/apply', [CouponController::class, 'apply']);       // apply coupon → billing preview

    // ── Billing Preview (Public) ──────────────────────────────────
    Route::get('/bookings/billing-preview', [BookingController::class, 'billingPreview']);

    // ── TPI Pay Webhooks / Callbacks (Public) ──────────────────────
    Route::any('/payment/tpi/success', [PaymentController::class, 'tpiSuccess'])->name('payment.tpi.success');
    Route::any('/payment/tpi/failure', [PaymentController::class, 'tpiFailure'])->name('payment.tpi.failure');

    // ─────────────────────────────────────────────────────────────
    // Protected Routes (JWT)
    // ─────────────────────────────────────────────────────────────
    Route::middleware('api.auth')->group(function () {

        // Auth
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::match(['put', 'post'], '/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::put('/fcm-token', [AuthController::class, 'updateFcmToken']);

        // ── Dashboard ───────────────────────────────────────────
        Route::get('/dashboard/recent-bookings', [DashboardController::class, 'recentBookings']);
        Route::get('/dashboard/recent-transactions', [DashboardController::class, 'recentTransactions']);

        // Subscriptions, Payments, and Attendance don't need KYC?
        // Wait, Subscriptions, Payments might be needed. Let's move them out of the group.

        // ── Customer: Subscriptions ───────────────────────────────
        Route::get('/subscriptions', [SubscriptionController::class, 'index']);
        Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show']);
        Route::get('/subscriptions/cancellations/history', [SubscriptionController::class, 'cancellationHistory']);
        Route::post('/subscriptions/{id}/request-cancellation', [SubscriptionController::class, 'requestCancellation']);
        Route::post('/subscriptions/{id}/renew', [SubscriptionController::class, 'renewOnline']);
        Route::post('/subscriptions/{id}/pay-scheduled', [SubscriptionController::class, 'payScheduledAmount']);

        // ── Payments ──────────────────────────────────────────────
        Route::post('/payments/initiate', [PaymentController::class, 'initiatePayment']);
        Route::post('/payments/verify', [PaymentController::class, 'verifyPayment']);
        Route::get('/payments/history', [PaymentController::class, 'history']);
        Route::post('/payments/autopay/initiate', [PaymentController::class, 'initiateAutopay']);
        Route::post('/payments/autopay/verify', [PaymentController::class, 'verifyAutopay']);

        // ── Attendance ────────────────────────────────────────────
        Route::post('/attendance/punch-in', [AttendanceController::class, 'punchIn']);   // punch in
        Route::post('/attendance/punch-out', [AttendanceController::class, 'punchOut']);  // punch out
        Route::get('/attendance/today', [AttendanceController::class, 'today']);     // today's status
        Route::get('/attendance', [AttendanceController::class, 'history']);   // full history

        // Reviews (authenticated)
        Route::post('/listings/{id}/reviews', [ListingController::class, 'submitReview']);

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        // Wallet
        Route::get('/wallet/summary', [WalletController::class, 'summary']);
        Route::get('/wallet/reserve-history', [WalletController::class, 'reserveHistory']);
        Route::get('/wallet/history', [WalletController::class, 'history']);
        Route::post('/wallet/recharge', [WalletController::class, 'rechargeOnline']);
        Route::post('/wallet/withdraw', [WalletController::class, 'requestWithdrawal']);
        Route::get('/wallet/withdrawals', [WalletController::class, 'withdrawalHistory']);

        Route::delete('/notifications/delete', [NotificationController::class, 'destroyAll']);
        Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
        Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

        // ── Transactions ───────────────────────────────────────────
        Route::get('/transactions', [TransactionController::class, 'listTransactions']);
        Route::get('/transactions/{id}', [TransactionController::class, 'transactionDetails']);

        // ── Job Postings & Referrals ───────────────────────────────
        Route::get('/job-posts', [JobPostController::class, 'index']);
        Route::get('/job-posts/{id}', [JobPostController::class, 'show']);
        Route::post('/shared-referrals', [JobPostController::class, 'shareReferral']);
        Route::get('/shared-referrals/{code}', [JobPostController::class, 'getReferralPost']);

        // ── Job Applications & Status ────────────────────────────
        Route::get('/job-applications', [JobPostController::class, 'applications']);
         Route::get('/job-applications/{id}', [JobPostController::class, 'applicationsDetail']);
        Route::post('/job-applications-apply', [JobPostController::class, 'apply']);
        Route::post('/job-applications/{id}/status', [JobPostController::class, 'updateApplicationStatus']);
        
        // Route::get('/my-referrals', [JobPostReferralController::class, 'index']);
        // Route::post('/job-posts/refer', [JobPostReferralController::class, 'store']);

        // ── Invoices ──────────────────────────────────────────────
        Route::get('/invoices/{type}/{id}/download', [InvoiceReceiptController::class, 'download']);

        // ── Customer KYC Profile ──────────────────────────────────
        Route::get('/kyc', [CustomerKycController::class, 'show']);
        Route::post('/kyc', [CustomerKycController::class, 'submit']);

        // ── Candidate Profile ─────────────────────────────────────
        Route::get('/candidate-profile', [\App\Http\Controllers\Api\CandidateProfileController::class, 'show']);
        Route::post('/candidate-profile', [\App\Http\Controllers\Api\CandidateProfileController::class, 'update']);

        Route::middleware('customer.kyc.approved')->group(function () {
            // ── Customer: Bookings ────────────────────────────────────
            Route::get('/bookings', [BookingController::class, 'index']);      // my bookings list
            Route::post('/bookings', [BookingController::class, 'store']);      // create booking
            Route::get('/bookings/{id}', [BookingController::class, 'show']);       // booking detail + billing
            Route::post('/bookings/{id}/verify-otp', [BookingController::class, 'verifyOtp']); // verify OTP
            Route::post('/bookings/{id}/pay', [BookingController::class, 'pay']); // generate payment link later
            Route::post('/bookings/{id}/resend-otp', [BookingController::class, 'resendOtp']); // resend booking OTP
            Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancel']);      // cancel booking

            // ── Visit Bookings (Customer) ───────────────────────────
            Route::get('/visits', [VisitController::class, 'index']);
            Route::post('/visits', [VisitController::class, 'store']);
            Route::get('/visits/{id}', [VisitController::class, 'show']);
            Route::put('/visits/{id}', [VisitController::class, 'update']);
            Route::delete('/visits/{id}', [VisitController::class, 'cancel']);
        });

        // destroy account
        Route::delete('account/destroy', [AuthController::class, 'destroyAccount']);
    });

    Route::fallback(fn () => response()->json([
        'status' => 'error',
        'message' => 'API endpoint not found.',
    ], 404));
});

// ── Partner API ──────────────────────────────────────────────────
Route::prefix('partner')->group(base_path('routes/api_partner.php'));

// ── HRMS API ─────────────────────────────────────────────────────
Route::prefix('hrms')->group(base_path('routes/api_hrms.php'));
