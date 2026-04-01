<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FaceAttendanceController;
use App\Http\Controllers\LoanRequestController;

/*
|--------------------------------------------------------------------------
| 1. المسارات العامة (بدون تسجيل دخول)
|--------------------------------------------------------------------------
*/

Route::get('/', [PagesController::class, 'showLogin'])->name('home');
Route::get('/login', [PagesController::class, 'showLogin'])->name('login');
Route::post('/login', [PagesController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// مسارات نظام بصمة الوجه
Route::prefix('face-attendance')->group(function () {
    Route::get('/face-data', [FaceAttendanceController::class, 'exportFaceData']);
    Route::get('/health', [FaceAttendanceController::class, 'health']);
    Route::get('/employees', [FaceAttendanceController::class, 'getEmployeesList']);
    Route::post('/record', [FaceAttendanceController::class, 'record']);
    Route::get('/latest', [FaceAttendanceController::class, 'getLatestAttendance']);
});

/*
|--------------------------------------------------------------------------
| 2. المسارات المحمية (تتطلب تسجيل دخول)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // --- مسارات مشتركة ---
    Route::get('/salaries', [PagesController::class, 'showSalariesPage'])->name('salaries');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::delete('/notifications/destroy-all', [NotificationController::class, 'destroyAll'])->name('notifications.deleteAll');
    Route::get('/check-new-notifications', [NotificationController::class, 'checkNew'])->name('notifications.check');

    /*
    |--------------------------------------------------------------------------
    | مسارات الموظف ومدير القسم (صفحة الطلبات الموحدة)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:موظف,مدير القسم'])->group(function () {
        Route::get('/employee/dashboard', [PagesController::class, 'showEmployeeDashboard'])->name('employee.dashboard');
        Route::get('/my-attendance', [AttendanceController::class, 'index'])->name('attendance.index');

        // صفحة الطلبات الموحدة (عرض)
        Route::get('/my-requests', [LeaveRequestController::class, 'index'])->name('requests.index');

        // تقديم طلبات
        Route::post('/my-requests/leave', [LeaveRequestController::class, 'store'])->name('leaves.store');
        Route::post('/my-requests/loan', [LoanRequestController::class, 'store'])->name('loans.store');

        Route::get('/my-tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::patch('/dashbord/tasks/{id}/update-status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    });

    /*
    |--------------------------------------------------------------------------
    | مسارات الإدارة المشتركة (مدير النظام + مدير القسم)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:مدير النظام,مدير القسم'])->group(function () {
        Route::get('/dashbord', [PagesController::class, 'showDashboardPage'])->name('dashbord');
        Route::get('/manager/dashboard', [PagesController::class, 'showDashboardPage'])->name('employees.dashboard_mangers');
        Route::get('/leave-admin', [PagesController::class, 'showLeavePage'])->name('leave');

        // صفحة إدارة الطلبات الموحدة (إجازات + قروض)
        Route::get('/attendance-admin', [LeaveRequestController::class, 'adminIndex'])->name('attendance');

        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/admin/tasks', [TaskController::class, 'adminIndex'])->name('tasks.admin');
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('/get-managers/{departmentId}', [EmployeeController::class, 'getManagers']);
    });

    /*
    |--------------------------------------------------------------------------
    | مسارات "مدير النظام" حصراً
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:مدير النظام'])->group(function () {
        // إدارة الموظفين
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

        // إدارة الرواتب
        Route::put('/salaries/update', [PagesController::class, 'updateSalary'])->name('salaries.update');
        Route::post('/salaries/{id}/pay', [PagesController::class, 'paySalary'])->name('salaries.pay');
        Route::post('/salaries/pay-all', [PagesController::class, 'payAllSalaries'])->name('salaries.payAll');
        Route::post('/salaries/generate', [PagesController::class, 'generateMonthlySalaries'])->name('salaries.generate');
        Route::get('/activity-log', [PagesController::class, 'showActivityLogPage'])->name('activity.log');
        // التقارير
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        Route::match(['get', 'post'], '/reports/generate', [ReportsController::class, 'generate'])->name('reports.generate');
        Route::get('/performance', [PagesController::class, 'showPerformancePage'])->name('performance');

        // إدارة المستخدمين والمهام
        Route::resource('users', UserController::class)->except(['create', 'store', 'show']);
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::post('/admin/leaves/{id}/status', [LeaveRequestController::class, 'updateStatus'])->name('admin.leaves.status');

        // ✅ مسارات الموافقة على طلبات القروض
        Route::post('/loans/{id}/approve', [LoanRequestController::class, 'approve'])->name('loans.approve');
        Route::post('/loans/{id}/reject', [LoanRequestController::class, 'reject'])->name('loans.reject');

        // مسارات عامة
        Route::post('/notifications/send-general', [NotificationController::class, 'sendGeneralNotification'])->name('notifications.sendGeneral');

    });
});
