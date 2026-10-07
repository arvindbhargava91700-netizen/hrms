<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminExportController;
use App\Http\Controllers\Admin\CustomerExportController;
use App\Livewire\Partner\Hrms\JobPosts;
use App\Livewire\Partner\Hrms\JobPostPaymentCallback;
use App\Livewire\Partner\Hrms\AppliedJobs;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/test-mail', [AuthController::class, 'testMail'])->name('test.mail');

// ── Public / Auth routes ──────────────────────────────────────
Route::view('/support', 'support')->name('support');
Route::view('/privacy-policy', 'privacy-policy')->name('privacy.policy');
Route::view('/about-us', 'about-us')->name('about');
Route::view('/contact-us', 'contact-us')->name('contact');

Route::any('/payment/tpi/web/success', [\App\Http\Controllers\Api\PaymentController::class, 'tpiWebSuccess'])->name('payment.tpi.web.success');
Route::any('/payment/tpi/web/failure', [\App\Http\Controllers\Api\PaymentController::class, 'tpiWebFailure'])->name('payment.tpi.web.failure');

Route::middleware('guest')->middleware('secure.upload')->group(function () {
    Route::get('/',        fn() => redirect()->route('login'));
    Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',  [AuthController::class, 'login'])->name('login.post');
    
    // OTP routes for admin login
    Route::get('/login/otp', [AuthController::class, 'showOtpForm'])->name('login.otp');
    Route::post('/login/otp', [AuthController::class, 'verifyOtp'])->name('login.otp.verify');
    Route::post('/login/otp/resend', [AuthController::class, 'resendOtp'])->name('login.otp.resend');

    Route::get('/register',  \App\Livewire\Auth\Register::class)->name('register');

    Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])
        ->name('password.request');

    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])
        ->name('password.reset');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::post('/fcm-token', function (\Illuminate\Http\Request $request) {
    $request->validate(['fcm_token' => 'required|string']);
    $request->user()->update(['fcm_token' => $request->fcm_token]);
    return response()->json(['status' => 'success']);
})->name('fcm.token')->middleware('auth');



// ── Partner & Staff routes ────────────────────────────────────────────
Route::middleware(['auth', 'role:partner,employee,super_admin,manager,admin'])->prefix('workspace')->name('partner.')->group(function () {
    Route::get('/kyc',          \App\Livewire\Partner\KycUpload::class)->name('kyc');
    Route::get('/tpi-kyc',      \App\Livewire\Partner\TpiKyc::class)->name('tpi-kyc');

        Route::get('/dashboard',    \App\Livewire\Partner\Dashboard::class)->name('dashboard');
        Route::get('/profile',      \App\Livewire\Profile::class)->name('profile');
        Route::get('/profile-admin', \App\Livewire\Profile::class)->name('admin.profile');
        Route::get('/platform-plans',\App\Livewire\Partner\PlatformPlans::class)->name('platform-plans');
        Route::any('/platform-plans/callback', \App\Livewire\Partner\SubscriptionCallback::class)->name('subscription.callback');
        Route::get('/subscription-history',\App\Livewire\Partner\PlatformSubscriptionHistory::class)->name('subscription-history');
        Route::get('/payments',     \App\Livewire\Partner\Payments::class)->name('payments');
        Route::get('/invoices',     \App\Livewire\Partner\Invoices::class)->name('invoices');
        Route::get('/invoices/{type}/{id}/receipt', [\App\Http\Controllers\InvoiceReceiptController::class, 'show'])->name('invoices.receipt.show');
        Route::get('/invoices/{type}/{id}/receipt/download', [\App\Http\Controllers\InvoiceReceiptController::class, 'download'])->name('invoices.receipt.download');
        Route::get('/wallet',       \App\Livewire\Partner\Wallet::class)->name('wallet');
        Route::any('/wallet/recharge/callback', \App\Livewire\Partner\RechargeCallback::class)->name('recharge.callback');
        Route::get('/transaction-history', \App\Livewire\Partner\TransactionHistory::class)->name('transaction-history');
        Route::get('/reserve-histories', \App\Livewire\Partner\ReserveHistories::class)->name('reserve-histories');
        
        // Core feature routes that require an active subscription
        Route::middleware('requires_subscription')->group(function () {
            Route::get('/listings',     \App\Livewire\Partner\Listings::class)->name('listings');
            Route::get('/reviews',      \App\Livewire\Partner\Reviews::class)->name('reviews');
            Route::get('/packages',     \App\Livewire\Partner\Packages::class)->name('packages');
            Route::get('/customers',    \App\Livewire\Partner\Customers::class)->name('customers');
            Route::get('/bookings',     \App\Livewire\Partner\Bookings::class)->name('bookings');
            Route::get('/subscriptions',\App\Livewire\Partner\Subscriptions::class)->name('subscriptions');
            Route::get('/coupons',      \App\Livewire\Partner\Coupons::class)->name('coupons');
            
            // Unified listing forms
            Route::get('/listings/new',                 \App\Livewire\Partner\ManageListing::class)->name('listing.create');
            Route::get('/listings/{id}',                \App\Livewire\Partner\ListingDetails::class)->name('listing.details');
            Route::get('/listings/{id}/edit',           \App\Livewire\Partner\ManageListing::class)->name('listing.edit');
            // Partner Visit bookings UI
            Route::get('/visits', \App\Livewire\Partner\Visits::class)->name('visits');

            // Partner HRMS Module Routes
            // Process Notes & Company Documents: Accessible to all employees, not restricted by HRMS module permission
            Route::get('/hrms/process-notes', \App\Livewire\Partner\Hrms\ProcessNotes::class)->name('hrms.process-notes');
            Route::get('/hrms/company-documents', \App\Livewire\Partner\Hrms\CompanyDocuments::class)->name('hrms.company-documents');

            Route::prefix('hrms')->name('hrms.')->middleware('requires_module:hrms')->group(function () {
                Route::get('/dashboard', \App\Livewire\Partner\Hrms\Dashboard::class)->name('dashboard');
                Route::get('/roles', \App\Livewire\Partner\Hrms\Roles::class)->name('roles');
                Route::get('/staff', \App\Livewire\Partner\Hrms\Staff::class)->name('staff');
                Route::get('/staff/{id}/profile', \App\Livewire\Partner\Hrms\StaffProfile::class)->name('staff.profile');
                Route::get('/departments', \App\Livewire\Partner\Hrms\Departments::class)->name('departments');
                Route::get('/branches', \App\Livewire\Partner\Hrms\Branches::class)->name('branches');
                Route::get('/work-shifts', \App\Livewire\Partner\Hrms\WorkShifts::class)->name('work-shifts');
                Route::get('/tasks', \App\Livewire\Partner\Hrms\Tasks::class)->name('tasks');
                Route::get('/expenses', \App\Livewire\Partner\Hrms\Expenses::class)->name('expenses');
                Route::get('/expenses/file/{id}', function ($id) {
                    $expense = \App\Models\Expense::findOrFail($id);
                    if (!$expense->upload_file) {
                        abort(404, 'No proof file attached.');
                    }
                    $filePath = $expense->upload_file;
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($filePath)) {
                        return \Illuminate\Support\Facades\Storage::disk('public')->response($filePath);
                    }
                    $publicPath = public_path('storage/' . $filePath);
                    if (file_exists($publicPath)) {
                        return response()->file($publicPath);
                    }
                    if (file_exists(storage_path('app/' . $filePath))) {
                        return response()->file(storage_path('app/' . $filePath));
                    }
                    abort(404, 'File not found on storage.');
                })->name('expenses.file');
                Route::get('/notices', \App\Livewire\Partner\Hrms\Notices::class)->name('notices');
                Route::get('/product-categories', \App\Livewire\Partner\Hrms\ProductCategories::class)->name('product-categories');
                Route::get('/products', \App\Livewire\Partner\Hrms\Products::class)->name('products');
                Route::get('/documents', \App\Livewire\Partner\Hrms\EmployeeDocuments::class)->name('documents');
                Route::get('/grievance-discipline', \App\Livewire\Partner\Hrms\EmployeeGrievances::class)->name('grievances');
                Route::get('/settings', \App\Livewire\Partner\Hrms\Settings::class)->name('settings');
                Route::get('/process-notes', \App\Livewire\Partner\Hrms\ProcessNotes::class)->name('process-notes');
                Route::get('/my-targets', \App\Livewire\Partner\Hrms\EmployeeTargets::class)->name('my-targets');
                Route::get('/pipeline-settings', \App\Livewire\Partner\Hrms\PipelineSettings::class)->name('pipeline-settings');
                
                // Daily Work Reports
               
                Route::get('/team-reports', \App\Livewire\Partner\Hrms\DailyWorkReports::class)->name('team-reports');
                
                //neeraj added 
                Route::get('/pip', \App\Livewire\Partner\Hrms\EmployeePips::class)->name('pip');
                Route::get('/recruitment', \App\Livewire\Partner\Hrms\Recruitment::class)->name('recruitment');
                  Route::get('/job-posts', \App\Livewire\Partner\Hrms\JobPostings\JobPostsListComponent::class)->name('job-posts');
                  Route::get('/job-posts/create', \App\Livewire\Partner\Hrms\JobPostings\JobPostFormComponent::class)->name('job-posts.create');
                  Route::get('/job-posts/{id}/edit', \App\Livewire\Partner\Hrms\JobPostings\JobPostFormComponent::class)->name('job-posts.edit');
                  Route::get('/job-posts/{jobPost:job_code}/candidates', \App\Livewire\Partner\Hrms\JobPostings\JobCandidatesComponent::class)->name('job-posts.candidates');
                  Route::get('/job-posts/billing-history', \App\Livewire\Partner\Hrms\BillingHistoryComponent::class)->name('job-posts.billing');
                Route::any('/job-posts/payment-callback', JobPostPaymentCallback::class)->name('job-post.payment.callback');
                  Route::get('/applied-jobs', AppliedJobs::class)->name('applied-jobs');
                Route::get('/probation', \App\Livewire\Partner\Hrms\EmployeeProbations::class)->name('probation');
                Route::get('/resignation-exit', \App\Livewire\Partner\Hrms\EmployeeExits::class)->name('resignation-exit');

                Route::get('/attrition', \App\Livewire\Partner\Hrms\Attrition::class)->name('attrition');
                Route::get('/exit-reasons', \App\Livewire\Partner\Hrms\ExitReasons::class)->name('exit-reasons');
                Route::get('/employee-cost', \App\Livewire\Partner\Hrms\EmployeeCost::class)->name('employee-cost');
                Route::get('/training', \App\Livewire\Partner\Hrms\Training::class)->name('training');
                Route::get('/assets', \App\Livewire\Partner\Hrms\Assets::class)->name('assets');
                
                // CRM & Sales

                Route::get('/leads', \App\Livewire\Partner\Hrms\Leads\Index::class)->name('leads.index');
                Route::get('/customer-visits', \App\Livewire\Partner\Hrms\CustomerVisits\Index::class)->name('customer-visits');
                Route::get('/lead-orders', \App\Livewire\Partner\Hrms\LeadOrders\Index::class)->name('lead-orders');
                Route::get('/lead-orders/recovery', \App\Livewire\Partner\Hrms\LeadOrders\Recovery::class)->name('lead-orders.recovery');
                Route::get('/lead-orders/recovery-history', \App\Livewire\Partner\Hrms\LeadOrders\RecoveryHistory::class)->name('lead-orders.recovery-history');
                
                // Reports route has been moved to separate module
                // Attendance
                Route::get('/attendance/mark', \App\Livewire\Partner\Hrms\Attendance\MarkAttendance::class)->name('attendance.mark');
                Route::get('/attendance/override', \App\Livewire\Partner\Hrms\Attendance\ManualAttendanceOverride::class)->name('attendance.override');
                Route::get('/attendance/manage', \App\Livewire\Partner\Hrms\Attendance\EmployeeAttendance::class)->name('attendance.manage');
                Route::get('/attendance/settings', \App\Livewire\Partner\Hrms\Attendance\AttendanceSettings::class)->name('attendance.settings');
                Route::get('/attendance/calendar', \App\Livewire\Partner\Hrms\Attendance\AttendanceCalendar::class)->name('attendance.calendar');
                
                // Leaves
                Route::get('/leaves/categories', \App\Livewire\Partner\Hrms\LeaveCategories::class)->name('leaves.categories');
                Route::get('/leaves/apply', \App\Livewire\Partner\Hrms\Leaves\ApplyLeave::class)->name('leaves.apply');
                Route::get('/leaves/my-requests', \App\Livewire\Partner\Hrms\Leaves\LeaveRequests::class)->name('leaves.my-requests');
                Route::get('/leaves/approvals', \App\Livewire\Partner\Hrms\Leaves\LeaveApprovals::class)->name('leaves.approvals');
                Route::get('/leaves/balance', \App\Livewire\Partner\Hrms\Leaves\LeaveBalance::class)->name('leaves.balance');
                Route::get('/leaves/calendar', \App\Livewire\Partner\Hrms\Leaves\LeaveCalendar::class)->name('leaves.calendar');
                
                // Commissions (New Section separated from Payroll)
                Route::get('/commissions/process', \App\Livewire\Partner\Hrms\Commission\CommissionProcess::class)->name('commission.process');
                Route::get('/commissions/history', \App\Livewire\Partner\Hrms\Commission\CommissionHistory::class)->name('commission.history');

                // Payroll
                Route::get('/payroll/my', \App\Livewire\Partner\Hrms\Payroll\MyPayroll::class)->name('payroll.my');
                Route::get('/payroll/payslip/{id}/download', [\App\Http\Controllers\PayslipController::class, 'download'])->name('payslip.download');
                Route::get('/payroll/process', \App\Livewire\Partner\Hrms\Payroll\PayrollProcess::class)->name('payroll.process');
                Route::get('/payroll/drafts', \App\Livewire\Partner\Hrms\Payroll\PayrollDrafts::class)->name('payroll.drafts');
                Route::get('/payroll/approval', \App\Livewire\Partner\Hrms\Payroll\PayrollApproval::class)->name('payroll.approval');
                Route::get('/payroll/salary', \App\Livewire\Partner\Hrms\Payroll\SalaryManagement::class)->name('payroll.salary');
                Route::get('/payroll/reports', \App\Livewire\Partner\Hrms\Payroll\PayrollReports::class)->name('payroll.reports');

              
                
                // Advances
                Route::get('/advance-payments', \App\Livewire\Partner\Hrms\AdvancePayments::class)->name('advance-payments');

                // HRMS Reports
                Route::prefix('report')->name('report.')->group(function () {
                    Route::get('/performance', \App\Livewire\Partner\Reports\Hrms\PerformanceReport::class)->name('performance');
                    Route::get('/performance-profile/{id}', \App\Livewire\Partner\Reports\Hrms\PerformanceProfile::class)->name('performance-profile');
                    Route::get('/staff', \App\Livewire\Partner\Reports\Hrms\StaffReport::class)->name('staff');
                    Route::get('/attendance', \App\Livewire\Partner\Reports\Hrms\AttendanceReport::class)->name('attendance');
                    Route::get('/leaves', \App\Livewire\Partner\Reports\Hrms\LeaveReport::class)->name('leaves');
                    Route::get('/expenses', \App\Livewire\Partner\Reports\Hrms\ExpenseReport::class)->name('expenses');
                    Route::get('/tasks', \App\Livewire\Partner\Reports\Hrms\TaskReport::class)->name('tasks');
                    Route::get('/leads', \App\Livewire\Partner\Reports\Hrms\LeadReport::class)->name('leads');
                    Route::get('/orders', \App\Livewire\Partner\Reports\Hrms\OrderReport::class)->name('orders');
                    Route::get('/payroll', \App\Livewire\Partner\Reports\Hrms\PayrollReport::class)->name('payroll');
                    Route::get('/recovery', \App\Livewire\Partner\Reports\Hrms\RecoveryReport::class)->name('recovery');
                    Route::get('/notice', \App\Livewire\Partner\Reports\Hrms\NoticeReport::class)->name('notice');
                    Route::get('/product-category', \App\Livewire\Partner\Reports\Hrms\ProductCategoryReport::class)->name('product-category');
                    Route::get('/product', \App\Livewire\Partner\Reports\Hrms\ProductReport::class)->name('product');
                    Route::get('/salary-management', \App\Livewire\Partner\Reports\Hrms\SalaryManagementReport::class)->name('salary-management');
                    Route::get('/commissions', \App\Livewire\Partner\Reports\Hrms\CommissionsReport::class)->name('commissions');

                      //neeraj added
                Route::get('/pip', \App\Livewire\Partner\Reports\Hrms\PipReport::class)->name('pip');
                    Route::get('/recruitment', \App\Livewire\Partner\Reports\Hrms\RecruitmentReport::class)->name('recruitment');
                    Route::get('/probation', \App\Livewire\Partner\Reports\Hrms\ProbationReport::class)->name('probation');
                    Route::get('/resignation-exit', \App\Livewire\Partner\Reports\Hrms\ExitReport::class)->name('resignation-exit');
                    Route::get('/documents', \App\Livewire\Partner\Reports\Hrms\DocumentReport::class)->name('documents');
                    Route::get('/grievance-discipline', \App\Livewire\Partner\Reports\Hrms\GrievanceReport::class)->name('grievances');
                    

                    Route::get('/attrition', \App\Livewire\Partner\Reports\Hrms\AttritionReport::class)->name('attrition');
                    Route::get('/exit-reasons', \App\Livewire\Partner\Reports\Hrms\ExitReasonsReport::class)->name('exit-reasons');
                    Route::get('/employee-cost', \App\Livewire\Partner\Reports\Hrms\EmployeeCostReport::class)->name('employee-cost');
                    Route::get('/training', \App\Livewire\Partner\Reports\Hrms\TrainingReport::class)->name('training');
                    Route::get('/assets', \App\Livewire\Partner\Reports\Hrms\AssetReport::class)->name('assets');
                });

            });


            Route::prefix('reports')->name('reports.')->group(function () {
                
                // HRMS Additional Reports
                
                // New Reports
                Route::get('/reviews', \App\Livewire\Partner\Reports\ReviewReport::class)->name('reviews');
                Route::get('/visit-requests', \App\Livewire\Partner\Reports\VisitRequestReport::class)->name('visit-requests');
                Route::get('/listings', \App\Livewire\Partner\Reports\ListingReport::class)->name('listings');
                Route::get('/bookings', \App\Livewire\Partner\Reports\BookingReport::class)->name('bookings');
                Route::get('/customers', \App\Livewire\Partner\Reports\CustomerReport::class)->name('customers');
                Route::get('/partner-subscriptions', \App\Livewire\Partner\Reports\PartnerSubscriptionReport::class)->name('partner-subscriptions');
                Route::get('/collections', \App\Livewire\Partner\Reports\CollectionReport::class)->name('collections');
                Route::get('/customer-subscriptions', \App\Livewire\Partner\Reports\CustomerSubscriptionReport::class)->name('customer-subscriptions');
                Route::get('/wallet-history', \App\Livewire\Partner\Reports\WalletHistoryReport::class)->name('wallet-history');
                Route::get('/payment-histories', \App\Livewire\Partner\Reports\PaymentHistoryReport::class)->name('payment-histories');
                Route::get('/listing-packages', \App\Livewire\Partner\Reports\ListingPackageReport::class)->name('listing-packages');
                Route::get('/occupancy', \App\Livewire\Partner\Reports\OccupancyReport::class)->name('occupancy');
                Route::get('/business', \App\Livewire\Partner\Reports\BusinessReport::class)->name('business');
            });
        });
    });

// ── Customer routes (Mobile API Only) ───────────────────────────
// Customers do not have web access. API routes are defined in routes/api.php
