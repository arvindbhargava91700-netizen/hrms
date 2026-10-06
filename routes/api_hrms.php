<?php

use App\Http\Controllers\Api\Hrms\AdvancePaymentController;
use App\Http\Controllers\Api\Hrms\AssetController;
use App\Http\Controllers\Api\Hrms\AttendanceController;
use App\Http\Controllers\Api\Hrms\AttritionController;
use App\Http\Controllers\Api\Hrms\AuthController;
use App\Http\Controllers\Api\Hrms\CommissionController;
use App\Http\Controllers\Api\Hrms\CommissionSettingController;
use App\Http\Controllers\Api\Hrms\CompanyDocumentController;
use App\Http\Controllers\Api\Hrms\CrmController;
use App\Http\Controllers\Api\Hrms\DailyWorkReportController;
use App\Http\Controllers\Api\Hrms\DashboardController;
use App\Http\Controllers\Api\Hrms\DocumentController;
use App\Http\Controllers\Api\Hrms\EmployeeCostController;
use App\Http\Controllers\Api\Hrms\ExitController;
use App\Http\Controllers\Api\Hrms\ExpenseController;
use App\Http\Controllers\Api\Hrms\GrievanceController;
use App\Http\Controllers\Api\Hrms\HrmsReportController;
use App\Http\Controllers\Api\Hrms\LeaveController;
use App\Http\Controllers\Api\Hrms\NoticeController;
use App\Http\Controllers\Api\Hrms\OrganizationController;
use App\Http\Controllers\Api\Hrms\PayrollController;
use App\Http\Controllers\Api\Hrms\PerformanceSettingController;
use App\Http\Controllers\Api\Hrms\PipController;
use App\Http\Controllers\Api\Hrms\ProbationController;
use App\Http\Controllers\Api\Hrms\ProcessNoteController;
use App\Http\Controllers\Api\Hrms\ProductController;
use App\Http\Controllers\Api\Hrms\RecoveryController;
use App\Http\Controllers\Api\Hrms\RecruitmentController;
use App\Http\Controllers\Api\Hrms\SalaryController;
use App\Http\Controllers\Api\Hrms\SettingController;
use App\Http\Controllers\Api\Hrms\StaffController;
use App\Http\Controllers\Api\Hrms\TaskController;
use App\Http\Controllers\Api\Hrms\TeamWorkReportController;
use App\Http\Controllers\Api\Hrms\TrainingController;
use App\Http\Controllers\Api\Hrms\JobPostController;
use Illuminate\Support\Facades\Route;

// HRMS API Routes
// These routes are prefixed with /api/hrms automatically by routes/api.php

Route::middleware('force.json')->group(function () {
    // Auth (Public)
    Route::post('/login', [AuthController::class, 'login']);

    // Protected Routes (JWT)
    Route::middleware('api.auth:hrms_api')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::match(['put', 'post'], '/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        // account Delete
        Route::delete('/account/destroy', [AuthController::class, 'destroyAccount']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Attendance
        Route::get('/attendance/checklists', [AttendanceController::class, 'checklists']);
        Route::post('/attendance/submit-checklist', [AttendanceController::class, 'submitChecklist']);
        Route::post('/attendance/punch-in', [AttendanceController::class, 'punchIn']);
        Route::post('/attendance/punch-out', [AttendanceController::class, 'punchOut']);
        Route::get('/attendance/today', [AttendanceController::class, 'today']);
        Route::get('/attendance/history', [AttendanceController::class, 'history']);
        Route::get('/attendance/calendar', [AttendanceController::class, 'calendar']);
        Route::get('/attendance/override', [AttendanceController::class, 'override']);
        Route::post('/attendance/override', [AttendanceController::class, 'saveOverride']);
        Route::get('/attendance/overrides', [AttendanceController::class, 'overrides']);
        Route::get('/attendance/settings', [AttendanceController::class, 'settings']);
        Route::get('/attendance/{id}', [AttendanceController::class, 'show']);

        // Team Attendance
        Route::get('/team-attendance/employees', [AttendanceController::class, 'teamEmployees']);
        Route::get('/team-attendance/today', [AttendanceController::class, 'teamToday']);
        Route::get('/team-attendance/history', [AttendanceController::class, 'teamHistory']);

        // Salary Management
        Route::get('/salary-structures', [SalaryController::class, 'index']);
        Route::get('/payroll/salary-structures', [SalaryController::class, 'index']);
        Route::get('/employees/{id}/salary-structure', [SalaryController::class, 'getSalaryStructure']);
        Route::post('/employees/{id}/salary-structure', [SalaryController::class, 'updateSalaryStructure']);

        // Leaves
        Route::get('/leaves/categories', [LeaveController::class, 'categories']);
        Route::get('/leaves', [LeaveController::class, 'index']);
        Route::post('/leaves/apply', [LeaveController::class, 'apply']);
        Route::get('/team-leaves', [LeaveController::class, 'teamLeaves']);
        Route::post('/team-leaves/{id}/status', [LeaveController::class, 'updateStatus']);

        // Tasks
        Route::get('/tasks', [TaskController::class, 'index']);
        Route::get('/tasks/assignable-users', [TaskController::class, 'assignableUsers']);
        Route::put('/tasks/{id}', [TaskController::class, 'update']);
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
        Route::post('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
        Route::get('/team-tasks', [TaskController::class, 'teamTasks']);
        Route::post('/team-tasks', [TaskController::class, 'store']);
        Route::get('/tasks/{id}/remarks', [TaskController::class, 'viewRemarks']);
        Route::post('/tasks/{id}/remarks', [TaskController::class, 'addRemark']);

        // Notices
        Route::get('/notices', [NoticeController::class, 'index']);
        Route::post('/notices', [NoticeController::class, 'store']);
        Route::get('/notices/{id}', [NoticeController::class, 'show']);
        Route::put('/notices/{id}', [NoticeController::class, 'update']);
        Route::delete('/notices/{id}', [NoticeController::class, 'destroy']);

        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::get('/expenses/{id}', [ExpenseController::class, 'show']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::put('/expenses/{id}', [ExpenseController::class, 'update']);
        Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy']);
        Route::get('/team-expenses', [ExpenseController::class, 'teamExpenses']);
        Route::post('/team-expenses/{id}/status', [ExpenseController::class, 'updateStatus']);

        // Staff & Organization
        Route::get('/staff', [StaffController::class, 'index']);
        Route::get('/staff/{id}', [StaffController::class, 'show']);
        Route::post('/staff', [StaffController::class, 'store']);
        Route::put('/staff/{id}', [StaffController::class, 'update']);
        Route::delete('/staff/{id}', [StaffController::class, 'destroy']);

        // Attrition & Employee Exit
        Route::get('/attrition', [AttritionController::class, 'index']);
        Route::get('/attrition/export', [AttritionController::class, 'exportCsv']);
        Route::get('/attrition/exit-reasons', [AttritionController::class, 'exitReasons']);
        Route::post('/attrition/exit-reasons', [AttritionController::class, 'storeExitReason']);
        Route::put('/attrition/exit-reasons/{id}', [AttritionController::class, 'updateExitReason']);
        Route::delete('/attrition/exit-reasons/{id}', [AttritionController::class, 'destroyExitReason']);
        Route::get('/attrition/{id}', [AttritionController::class, 'show']);
        Route::post('/attrition', [AttritionController::class, 'store']);
        Route::put('/attrition/{id}', [AttritionController::class, 'update']);
        Route::delete('/attrition/{id}', [AttritionController::class, 'destroy']);

        // Employee Cost Analytics & Reporting
        Route::get('/employee-cost', [EmployeeCostController::class, 'index']);
        Route::get('/employee-cost/export', [EmployeeCostController::class, 'exportCsv']);

        // Training Management & Analytics
        Route::get('/training/dashboard', [TrainingController::class, 'dashboard']);
        Route::get('/training/export', [TrainingController::class, 'exportCsv']);
        Route::apiResource('trainings', TrainingController::class);

        // Job Postings - Apna Job Flow
        Route::get('/job-templates', [JobPostController::class, 'templates']);
        Route::get('/job-templates/{id}', [JobPostController::class, 'templateDetail']); // Single template with dynamic fields
        Route::get('/job-plans', [JobPostController::class, 'plans']);
        Route::post('/job-posts/draft', [JobPostController::class, 'draft']);
        Route::post('/job-posts/{id}/publish', [JobPostController::class, 'publish']);
        Route::get('/job-posts-history', [JobPostController::class, 'history']);
        Route::post('/job-posts/{id}/close', [JobPostController::class, 'closeJob']);

        // Legacy Job Postings
        Route::get('/job-posts', [JobPostController::class, 'index']);
        Route::post('/job-posts', [JobPostController::class, 'store']);
        Route::get('/job-posts/{id}', [JobPostController::class, 'show']);
        Route::put('/job-posts/{id}', [JobPostController::class, 'update']);
        Route::delete('/job-posts/{id}', [JobPostController::class, 'destroy']);

        // Applied Jobs
        Route::get('/applied-jobs', [JobPostController::class, 'applications']);
        Route::put('/applied-jobs/{id}/status', [JobPostController::class, 'updateApplicationStatus']);

        // Master Training Programs
        Route::get('/training/programs', [TrainingController::class, 'getPrograms']);
        Route::post('/training/programs', [TrainingController::class, 'storeProgram']);
        Route::get('/training/programs/{id}', [TrainingController::class, 'showProgram']);
        Route::put('/training/programs/{id}', [TrainingController::class, 'updateProgram']);
        Route::delete('/training/programs/{id}', [TrainingController::class, 'destroyProgram']);

        // Training Assignments
        Route::post('/training/assignments', [TrainingController::class, 'assignEmployees']);
        Route::get('/training/assignments/{id}', [TrainingController::class, 'showAssignment']);
        Route::post('/training/assignments/{id}/complete', [TrainingController::class, 'markCompleted']);
        Route::delete('/training/assignments/{id}', [TrainingController::class, 'destroyAssignment']);

        // Training Attendance Logs
        Route::post('/training/attendance', [TrainingController::class, 'logAttendance']);
        Route::get('/training/assignments/{id}/attendance', [TrainingController::class, 'getAttendanceLogs']);

        // Training Assessment Logs
        Route::post('/training/assessments', [TrainingController::class, 'saveAssessment']);
        Route::get('/training/assignments/{id}/assessments', [TrainingController::class, 'getAssessmentLogs']);

        // Assets Management
        Route::get('/assets', [AssetController::class, 'index']);
        Route::get('/assets/generate-code', [AssetController::class, 'generateCode']);
        Route::get('/assets/export', [AssetController::class, 'exportCsv']);
        Route::get('/assets/{id}', [AssetController::class, 'show']);
        Route::post('/assets', [AssetController::class, 'store']);
        Route::put('/assets/{id}', [AssetController::class, 'update']);
        Route::delete('/assets/{id}', [AssetController::class, 'destroy']);

        Route::get('/permissions', [OrganizationController::class, 'getPermissions']);
        Route::get('/roles', [OrganizationController::class, 'getRoles']);
        Route::get('/roles/{id}', [OrganizationController::class, 'getRole']);
        Route::post('/roles', [OrganizationController::class, 'createRole']);
        Route::put('/roles/{id}', [OrganizationController::class, 'updateRole']);
        Route::delete('/roles/{id}', [OrganizationController::class, 'deleteRole']);

        Route::get('/departments', [OrganizationController::class, 'getDepartments']);
        Route::post('/departments', [OrganizationController::class, 'createDepartment']);
        Route::put('/departments/{id}', [OrganizationController::class, 'updateDepartment']);
        Route::delete('/departments/{id}', [OrganizationController::class, 'deleteDepartment']);

        Route::get('/branches', [OrganizationController::class, 'getBranches']);
        Route::post('/branches', [OrganizationController::class, 'createBranch']);
        Route::put('/branches/{id}', [OrganizationController::class, 'updateBranch']);
        Route::delete('/branches/{id}', [OrganizationController::class, 'deleteBranch']);

        Route::get('/work-shifts', [OrganizationController::class, 'getWorkShifts']);
        Route::post('/work-shifts', [OrganizationController::class, 'createWorkShift']);
        Route::put('/work-shifts/{id}', [OrganizationController::class, 'updateWorkShift']);
        Route::delete('/work-shifts/{id}', [OrganizationController::class, 'deleteWorkShift']);

        // Products
        Route::get('/product-categories', [ProductController::class, 'getCategories']);
        Route::post('/product-categories', [ProductController::class, 'createCategory']);
        Route::put('/product-categories/{id}', [ProductController::class, 'updateCategory']);
        Route::delete('/product-categories/{id}', [ProductController::class, 'deleteCategory']);

        Route::get('/products', [ProductController::class, 'getProducts']);
        Route::post('/products', [ProductController::class, 'createProduct']);
        Route::put('/products/{id}', [ProductController::class, 'updateProduct']);
        Route::delete('/products/{id}', [ProductController::class, 'deleteProduct']);

        // CRM & Sales
        Route::get('/leads', [CrmController::class, 'getLeads']);
        Route::post('/leads', [CrmController::class, 'createLead']);
        Route::put('/leads/{id}', [CrmController::class, 'updateLead']);
        Route::delete('/leads/{id}', [CrmController::class, 'deleteLead']);
        Route::put('/leads/{id}/status', [CrmController::class, 'updateLeadStatus']);

        Route::get('/customer-visits', [CrmController::class, 'getCustomerVisits']);
        Route::post('/customer-visits', [CrmController::class, 'createCustomerVisit']);
        Route::put('/customer-visits/{id}', [CrmController::class, 'updateCustomerVisit']);
        Route::delete('/customer-visits/{id}', [CrmController::class, 'deleteCustomerVisit']);

        Route::get('/lead-orders/calculate', [CrmController::class, 'calculateOrder']);
        Route::get('/lead-orders/tabs', [CrmController::class, 'getOrderTabs']);
        Route::get('/lead-orders', [CrmController::class, 'getOrders']);
        Route::post('/lead-orders', [CrmController::class, 'createOrder']);
        Route::get('/lead-orders/{id}', [CrmController::class, 'showOrder']);
        Route::put('/lead-orders/{id}', [CrmController::class, 'updateOrder']);
        Route::delete('/lead-orders/{id}', [CrmController::class, 'deleteOrder']);
        Route::post('/lead-orders/{id}/comments', [CrmController::class, 'addStageComment']);
        Route::post('/lead-orders/{id}/approve', [CrmController::class, 'approveOrder']);
        Route::post('/lead-orders/{id}/reject', [CrmController::class, 'rejectOrder']);
        Route::get('/new-lead-order', [CrmController::class, 'newLeadOrder']);

        // =========================== Recovery Controller ==============================//
        Route::get('/lead-orders-recovery', [RecoveryController::class, 'index']);
        Route::get('/lead-orders-recovery/{orderId}', [RecoveryController::class, 'show']);
        Route::post('/lead-orders-recovery/{orderId}/pay', [RecoveryController::class, 'recordPayment']);
        Route::get('/lead-orders-recovery-history', [RecoveryController::class, 'history']);
        Route::get('/lead-orders-recovery-history/{paymentId}', [RecoveryController::class, 'showPayment']);

        // ========================================== Setting Api==================================//
        // HRMS Settings
        Route::get('/settings/holidays', [SettingController::class, 'holidays']);
        Route::post('/settings/holidays', [SettingController::class, 'storeHoliday']);
        Route::delete('/settings/holidays/{id}', [SettingController::class, 'deleteHoliday']);
        Route::put('/settings/holidays/{id}', [SettingController::class, 'updateHoliday']);

        // Commission Levels
        Route::get('/settings/commission-levels', [SettingController::class, 'commissionLevels']);
        Route::post('/settings/commission-levels', [SettingController::class, 'storeCommissionLevel']);
        Route::put('/settings/commission-levels/{id}', [SettingController::class, 'updateCommissionLevel']);
        Route::delete('/settings/commission-levels/{id}', [SettingController::class, 'deleteCommissionLevel']);

        // Commission TDS Rate
        Route::get('/settings/commission-tds', [SettingController::class, 'getCommissionTds']);
        Route::post('/settings/commission-tds', [SettingController::class, 'updateCommissionTds']);

        // Pipeline Config
        Route::get('/settings/pipeline-stages', [SettingController::class, 'pipelineStages']);
        Route::post('/settings/pipeline-stages', [SettingController::class, 'storePipelineStage']);
        Route::put('/settings/pipeline-stages/{id}', [SettingController::class, 'updatePipelineStage']);
        Route::delete('/settings/pipeline-stages/{id}', [SettingController::class, 'deletePipelineStage']);

        // Attendance Checklist
        Route::get('/settings/attendance-checklists', [SettingController::class, 'attendanceChecklists']);
        Route::post('/settings/attendance-checklists', [SettingController::class, 'storeAttendanceChecklist']);
        Route::put('/settings/attendance-checklists/{id}/toggle', [SettingController::class, 'toggleAttendanceChecklist']);
        Route::delete('/settings/attendance-checklists/{id}', [SettingController::class, 'deleteAttendanceChecklist']);

        // Leave Categories
        Route::get('/settings/leave-categories', [SettingController::class, 'leaveCategories']);
        Route::post('/settings/leave-categories', [SettingController::class, 'storeLeaveCategory']);
        Route::put('/settings/leave-categories/{id}', [SettingController::class, 'updateLeaveCategory']);
        Route::delete('/settings/leave-categories/{id}', [SettingController::class, 'deleteLeaveCategory']);

        // Expense Categories
        Route::get('/settings/expense-categories', [SettingController::class, 'expenseCategories']);
        Route::post('/settings/expense-categories', [SettingController::class, 'storeExpenseCategory']);
        Route::put('/settings/expense-categories/{id}', [SettingController::class, 'updateExpenseCategory']);
        Route::delete('/settings/expense-categories/{id}', [SettingController::class, 'deleteExpenseCategory']);

        Route::get('/settings/task-statuses', [SettingController::class, 'taskStatuses']);
        Route::post('/settings/task-statuses', [SettingController::class, 'storeTaskStatus']);
        Route::put('/settings/task-statuses/{id}', [SettingController::class, 'updateTaskStatus']);
        Route::delete('/settings/task-statuses/{id}', [SettingController::class, 'deleteTaskStatus']);

        // Payslip Config
        Route::get('/settings/payslip-config', [SettingController::class, 'getPayslipConfig']);
        Route::post('/settings/payslip-config', [SettingController::class, 'updatePayslipConfig']);

        // Performance Score Configuration
        Route::get('/settings/performance/global', [PerformanceSettingController::class, 'getGlobalPerformance']);
        Route::post('/settings/performance/global', [PerformanceSettingController::class, 'updateGlobalPerformance']);
        Route::get('/settings/performance/staff', [PerformanceSettingController::class, 'staffList']);
        Route::get('/settings/performance/staff/{id}', [PerformanceSettingController::class, 'staffShow']);
        Route::post('/settings/performance/staff/{id}', [PerformanceSettingController::class, 'updateStaffPerformance']);
        Route::post('/settings/performance/staff/{id}/reset', [PerformanceSettingController::class, 'resetStaffPerformance']);
        Route::post('/settings/performance/reset-all', [PerformanceSettingController::class, 'resetAllStaff']);

        // ========================================== Setting Api==================================//

        // Commission Process
        Route::post('/commissions/process', [CommissionSettingController::class, 'processCommissions']);
        Route::get('/commissions/history', [CommissionSettingController::class, 'history']);

        // Targets & Commissions
        Route::get('/my-targets', [CommissionController::class, 'getTargets']);
        Route::get('/commissions/history', [CommissionController::class, 'getHistory']);

        // Payroll & Advances
        // Route::get('/payroll/my', [SalaryController::class, 'getMyPayroll']);

        Route::get('/advance-payments', [AdvancePaymentController::class, 'index']);
        Route::post('/advance-payments', [AdvancePaymentController::class, 'store']);

        // Payroll & Advances
        Route::get('/payroll/my', [SalaryController::class, 'getMyPayroll']);
        Route::post('/payroll/process', [PayrollController::class, 'process']);
        Route::get('/payroll/drafts', [PayrollController::class, 'drafts']);
        Route::get('/payroll/{id}', [PayrollController::class, 'show']);
        Route::post('/payroll/{id}/adjust', [PayrollController::class, 'adjust']);
        Route::post('/payroll/{id}/approve', [PayrollController::class, 'approve']);

        // Advance Payments
        Route::get('/advance-reports', [PayrollController::class, 'reports']);

        // Listings (read-only)
        Route::get('/departments-listing', [OrganizationController::class, 'departmentListing']);
        Route::get('/employees-listing', [OrganizationController::class, 'employeesListing']);
        Route::get('/branches-listing', [OrganizationController::class, 'branchesListing']);

        // HRMS Reports
        Route::get('/reports/branches', [HrmsReportController::class, 'reportBranches']);
        Route::get('/reports/teams', [HrmsReportController::class, 'reportTeams']);
        Route::get('/reports/staff', [HrmsReportController::class, 'staffReport']);
        Route::get('/reports/staff/export', [HrmsReportController::class, 'staffReportExport']);
        Route::get('/reports/attendance', [HrmsReportController::class, 'attendanceReport']);
        Route::get('/reports/attendance/export', [HrmsReportController::class, 'attendanceReportExport']);
        Route::get('/reports/leaves', [HrmsReportController::class, 'leaveReport']);
        Route::get('/reports/leaves/export', [HrmsReportController::class, 'leaveReportExport']);
        Route::get('/reports/expenses', [HrmsReportController::class, 'expenseReport']);
        Route::get('/reports/expenses/export', [HrmsReportController::class, 'expenseReportExport']);
        Route::get('/reports/tasks', [HrmsReportController::class, 'taskReport']);
        Route::get('/reports/tasks/export', [HrmsReportController::class, 'taskReportExport']);
        Route::get('/reports/leads', [HrmsReportController::class, 'leadReport']);
        Route::get('/reports/leads/export', [HrmsReportController::class, 'leadReportExport']);
        Route::get('/reports/orders', [HrmsReportController::class, 'orderReport']);
        Route::get('/reports/orders/export', [HrmsReportController::class, 'orderReportExport']);
        Route::get('/reports/payroll', [HrmsReportController::class, 'payrollReport']);
        Route::get('/reports/payroll/export', [HrmsReportController::class, 'payrollReportExport']);
        Route::get('/reports/recovery', [HrmsReportController::class, 'recoveryReport']);
        Route::get('/reports/recovery/export', [HrmsReportController::class, 'recoveryReportExport']);
        Route::get('/reports/notice', [HrmsReportController::class, 'noticeReport']);
        Route::get('/reports/notice/export', [HrmsReportController::class, 'noticeReportExport']);
        Route::get('/reports/product-category', [HrmsReportController::class, 'productCategoryReport']);
        Route::get('/reports/product-category/export', [HrmsReportController::class, 'productCategoryReportExport']);
        Route::get('/reports/product', [HrmsReportController::class, 'productReport']);
        Route::get('/reports/product/export', [HrmsReportController::class, 'productReportExport']);
        Route::get('/reports/salary-management', [HrmsReportController::class, 'salaryManagementReport']);
        Route::get('/reports/salary-management/export', [HrmsReportController::class, 'salaryManagementReportExport']);
        Route::get('/reports/commissions', [HrmsReportController::class, 'commissionsReport']);
        Route::get('/reports/commissions/export', [HrmsReportController::class, 'commissionsReportExport']);
        Route::get('/reports/pip', [HrmsReportController::class, 'pipReport']);
        Route::get('/reports/pip/export', [HrmsReportController::class, 'pipReportExport']);
        Route::get('/reports/recruitment', [HrmsReportController::class, 'recruitmentReport']);
        Route::get('/reports/recruitment/export', [HrmsReportController::class, 'recruitmentReportExport']);
        Route::get('/reports/probation', [HrmsReportController::class, 'probationReport']);
        Route::get('/reports/probation/export', [HrmsReportController::class, 'probationReportExport']);
        Route::get('/reports/resignation-exit', [HrmsReportController::class, 'exitReport']);
        Route::get('/reports/resignation-exit/export', [HrmsReportController::class, 'exitReportExport']);
        Route::get('/reports/documents', [HrmsReportController::class, 'documentReport']);
        Route::get('/reports/documents/export', [HrmsReportController::class, 'documentReportExport']);
        Route::get('/reports/grievance-discipline', [HrmsReportController::class, 'grievanceReport']);
        Route::get('/reports/grievance-discipline/export', [HrmsReportController::class, 'grievanceReportExport']);
        Route::get('/reports/attrition', [HrmsReportController::class, 'attritionReport']);
        Route::get('/reports/attrition/export', [HrmsReportController::class, 'attritionReportExport']);
        Route::get('/reports/exit-reasons', [HrmsReportController::class, 'exitReasonsReport']);
        Route::get('/reports/exit-reasons/export', [HrmsReportController::class, 'exitReasonsReportExport']);
        Route::get('/reports/employee-cost', [HrmsReportController::class, 'employeeCostReport']);
        Route::get('/reports/employee-cost/export', [HrmsReportController::class, 'employeeCostReportExport']);
        Route::get('/reports/training', [HrmsReportController::class, 'trainingReport']);
        Route::get('/reports/training/export', [HrmsReportController::class, 'trainingReportExport']);
        Route::get('/reports/assets', [HrmsReportController::class, 'assetReport']);
        Route::get('/reports/assets/export', [HrmsReportController::class, 'assetReportExport']);

        // PIP APIs
        Route::get('/pips', [PipController::class, 'index']);
        Route::get('/pips/{id}', [PipController::class, 'show']);
        Route::post('/pips', [PipController::class, 'store']);
        Route::put('/pips/{id}', [PipController::class, 'update']);
        Route::delete('/pips/{id}', [PipController::class, 'destroy']);

        // Recruitment APIs
        Route::get('/recruitments', [RecruitmentController::class, 'index']);
        Route::get('/recruitments/{id}', [RecruitmentController::class, 'show']);
        Route::post('/recruitments', [RecruitmentController::class, 'store']);
        Route::put('/recruitments/{id}', [RecruitmentController::class, 'update']);
        Route::delete('/recruitments/{id}', [RecruitmentController::class, 'destroy']);

        // Probation APIs
        Route::get('/probations', [ProbationController::class, 'index']);
        Route::get('/probations/{id}', [ProbationController::class, 'show']);
        Route::post('/probations', [ProbationController::class, 'store']);
        Route::put('/probations/{id}', [ProbationController::class, 'update']);
        Route::delete('/probations/{id}', [ProbationController::class, 'destroy']);

        // Resignation & Exit APIs
        Route::get('/exits', [ExitController::class, 'index']);
        Route::get('/exits/{id}', [ExitController::class, 'show']);
        Route::post('/exits', [ExitController::class, 'store']);
        Route::put('/exits/{id}', [ExitController::class, 'update']);
        Route::delete('/exits/{id}', [ExitController::class, 'destroy']);

        // Employee Documents & KYC APIs
        Route::get('/documents', [DocumentController::class, 'index']);
        Route::get('/documents/{id}', [DocumentController::class, 'show']);
        Route::post('/documents', [DocumentController::class, 'store']);
        Route::match(['put', 'post'], '/documents/{id}', [DocumentController::class, 'update']);
        Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);

        // Grievance & Discipline APIs
        Route::get('/grievances', [GrievanceController::class, 'index']);
        Route::get('/grievances/{id}', [GrievanceController::class, 'show']);
        Route::post('/grievances', [GrievanceController::class, 'store']);
        Route::match(['put', 'post'], '/grievances/{id}', [GrievanceController::class, 'update']);
        Route::delete('/grievances/{id}', [GrievanceController::class, 'destroy']);

        // Company Documents
        Route::get('/company-documents', [CompanyDocumentController::class, 'index']);

        // Process Notes
        Route::get('/process-notes', [ProcessNoteController::class, 'index']);

        // Daily Work Reports (Employee)
        Route::get('/daily-work-reports', [DailyWorkReportController::class, 'index']);
        Route::get('/daily-work-reports/pending-tasks', [DailyWorkReportController::class, 'pendingTasks']);
        Route::get('/daily-work-reports/{id}', [DailyWorkReportController::class, 'show']);
        Route::post('/daily-work-reports', [DailyWorkReportController::class, 'store']);

        // Team Work Reports (Manager/Partner)
        Route::get('/team-work-reports', [TeamWorkReportController::class, 'index']);
        Route::post('/team-work-reports/{id}/approve', [TeamWorkReportController::class, 'approve']);
        Route::post('/team-work-reports/{id}/reject', [TeamWorkReportController::class, 'reject']);

    });
});
