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

/*
|--------------------------------------------------------------------------
| 1. المسارات العامة (بدون تسجيل دخول)
|--------------------------------------------------------------------------
*/
Route::get('/', [PagesController::class, 'showLogin'])->name('home');
Route::get('/login', [PagesController::class, 'showLogin'])->name('login');
Route::post('/login', [PagesController::class, 'login'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// مسارات نظام بصمة الوجه (API) - للأجهزة الخارجية

// مجموعة مسارات نظام البصمة
Route::prefix('face-attendance')->group(function () {
    Route::get('/face-data', [FaceAttendanceController::class, 'exportFaceData']);
    Route::get('/health', [FaceAttendanceController::class, 'health']);
    Route::get('/employees', [FaceAttendanceController::class, 'getEmployeesList']);
    Route::post('/record', [FaceAttendanceController::class, 'record']);
    Route::get('/latest', [FaceAttendanceController::class, 'getLatestAttendance']);
=======

    // تسجيل الحضور - يستقبل employee_id من جهاز البصمة
    Route::post('/checkin', [FaceAttendanceController::class, 'checkin']);

    // تصدير بيانات الوجه للموظفين (لجهاز البصمة)
    Route::get('/face-data', [FaceAttendanceController::class, 'exportFaceData']);

    // جلب قائمة الموظفين (لجهاز البصمة)
    Route::get('/employees', [FaceAttendanceController::class, 'getEmployeesList']);

    // التحقق من صحة الخادم (لجهاز البصمة)
    Route::get('/health', [FaceAttendanceController::class, 'health']);
>>>>>>> 8876741d866c562058daa052e0913edc3accfcdc
});

/*

/*
|--------------------------------------------------------------------------
| 2. المسارات المحمية (تتطلب تسجيل دخول)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // --- مسارات مشتركة لجميع الموظفين والمديرين ---
    Route::get('/salaries', [PagesController::class, 'showSalariesPage'])->name('salaries');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::delete('/notifications/destroy-all', [NotificationController::class, 'destroyAll'])->name('notifications.destroyAll');

    /*
    |--------------------------------------------------------------------------
    | أ: مسارات "الموظف" فقط
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:موظف'])->group(function () {
        Route::get('/employee/dashboard', [PagesController::class, 'showEmployeeDashboard'])->name('employee.dashboard');
        Route::get('/my-attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('/my-leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
        Route::post('/my-leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
        Route::get('/my-tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::patch('/tasks/{id}/status', [TaskController::class, 'updateStatus'])->name('tasks.updateStatus');
    });

    /*
    |--------------------------------------------------------------------------
    | ب: مسارات الإدارة المشتركة (مدير النظام + مدير القسم)
    |--------------------------------------------------------------------------
    | ملاحظة: مدير القسم هنا يملك صلاحية العرض والمتابعة فقط
    */
    Route::middleware(['role:مدير النظام,مدير القسم'])->group(function () {
        // لوحات التحكم والتقارير
        Route::get('/dashbord', [PagesController::class, 'showDashboardPage'])->name('dashbord');
        Route::get('/leave-admin', [PagesController::class, 'showLeavePage'])->name('leave');
        Route::get('/attendance-admin', [PagesController::class, 'showAttendancePage'])->name('attendance');

        // إدارة الموظفين (عرض فقط)
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');


        // إدارة الإجازات والمهام (عرض واعتماد)
        Route::get('/admin/leaves', [LeaveRequestController::class, 'adminIndex'])->name('admin.leaves.index');
        Route::post('/admin/leaves/{id}/status', [LeaveRequestController::class, 'updateStatus'])->name('admin.leaves.status');
        Route::get('/admin/tasks', [TaskController::class, 'adminIndex'])->name('tasks.admin');

        // أدوات الربط الديناميكي
        Route::get('/get-managers/{departmentId}', [EmployeeController::class, 'getManagers']);
    });

    /*
    |--------------------------------------------------------------------------
    | ج: مسارات "مدير النظام" حصراً (التحكم الكامل)
    |--------------------------------------------------------------------------
    | لا يمكن لمدير القسم الدخول لهذه المسارات نهائياً
    */
    Route::middleware(['role:مدير النظام'])->group(function () {
        // العمليات الحساسة للموظفين (CRUD)
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

        // إدارة الرواتب المتقدمة
        Route::put('/salaries/update', [PagesController::class, 'updateSalary'])->name('salaries.update');
        Route::post('/salaries/{id}/pay', [PagesController::class, 'paySalary'])->name('salaries.pay');
        Route::post('/salaries/pay-all', [PagesController::class, 'payAllSalaries'])->name('salaries.payAll');

        // التقارير والنشاطات الإدارية
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        Route::post('/reports/generate', [ReportsController::class, 'generate'])->name('reports.generate');
        Route::get('/performance', [PagesController::class, 'showPerformancePage'])->name('performance');

        // إدارة المستخدمين والصلاحيات والمهام
        Route::resource('users', UserController::class)->except(['create', 'store', 'show']);
        Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
        Route::delete('/tasks/{id}', [TaskController::class, 'destroy'])->name('tasks.destroy');

        // مسارات عامة أخرى
        Route::post('/notifications/send-general', [NotificationController::class, 'sendGeneralNotification'])->name('notifications.sendGeneral');
    });

});
