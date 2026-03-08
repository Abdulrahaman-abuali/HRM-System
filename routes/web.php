<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ReportsController;

// 1. المسارات العامة (قبل تسجيل الدخول)
Route::get('/', [PagesController::class, 'showLogin'])->name('home');
Route::get('/login', [PagesController::class, 'showLogin'])->name('login');
Route::post('/login', [PagesController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// 2. المسارات المحمية (تحتاج تسجيل دخول)
Route::middleware(['auth'])->group(function () {

    // --- مسارات مشتركة (يفتحها الموظف والمدير) ---
    Route::get('/salaries', [PagesController::class, 'showSalariesPage'])->name('salaries');

    // --- أ: مسارات الموظف فقط ---
    Route::middleware(['role:موظف'])->group(function () {
        Route::get('/employee/dashboard', [PagesController::class, 'showEmployeeDashboard'])->name('employee.dashboard');
        Route::get('/my-attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/my-leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
        Route::post('/my-leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
    });

    // --- ب: مسارات المدير فقط ---
    Route::middleware(['role:مدير النظام'])->group(function () {
        // لوحة التحكم الرئيسية
        Route::get('/dashbord', [PagesController::class, 'showDashboardPage'])->name('dashbord');
        Route::get('/employees-list', [PagesController::class, 'showEmployeesPage'])->name('employeers'); // تم تغيير الرابط لتجنب تعارض Resource

        // الحضور والإجازات
        Route::get('/leave-admin', [PagesController::class, 'showLeavePage'])->name('leave');
        Route::get('/attendance-admin', [PagesController::class, 'showAttendancePage'])->name('attendance');

        // الإدارة المتقدمة
        Route::get('/performance', [PagesController::class, 'showPerformancePage'])->name('performance');
        Route::get('/notifications', [PagesController::class, 'showNotificationsPage'])->name('notifications');

        // إدارة إجازات الموظفين (اعتماد/رفض)
        Route::get('/admin/leaves', [LeaveRequestController::class, 'adminIndex'])->name('admin.leaves.index');
        Route::post('/admin/leaves/{id}/status', [LeaveRequestController::class, 'updateStatus'])->name('admin.leaves.status');

        // إدارة الرواتب (تعديل ودفع)
        Route::put('/salaries/update', [PagesController::class, 'updateSalary'])->name('salaries.update');
        Route::post('/salaries/{id}/pay', [PagesController::class, 'paySalary'])->name('salaries.pay');
        Route::post('/salaries/pay-all', [PagesController::class, 'payAllSalaries'])->name('salaries.payAll');

        // نظام التقارير الموحد (تم الدمج هنا)
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        Route::post('/reports/generate', [ReportsController::class, 'generate'])->name('reports.generate');

        // إدارة البيانات الأساسية (CRUD)
        Route::resource('employees', EmployeeController::class);
        // تم الإبقاء على Resource فهو يغطي (index, create, store, show, edit, update, destroy)
        Route::resource('users', UserController::class)->except(['create', 'store', 'show']);
    });
});
