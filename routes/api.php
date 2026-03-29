<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FaceAttendanceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| هذه المسارات مخصصة لنظام بصمة الوجه
| Base URL: /api
|
*/

// مجموعة مسارات نظام البصمة
Route::prefix('face-attendance')->group(function () {

    // تسجيل الحضور - يستقبل employee_id من جهاز البصمة
    Route::post('/checkin', [FaceAttendanceController::class, 'checkin']);

    // تصدير بيانات الوجه للموظفين (لجهاز البصمة)
    Route::get('/face-data', [FaceAttendanceController::class, 'exportFaceData']);

    // جلب قائمة الموظفين (لجهاز البصمة)
    Route::get('/employees', [FaceAttendanceController::class, 'getEmployeesList']);

    // التحقق من صحة الخادم (لجهاز البصمة)
    Route::get('/health', [FaceAttendanceController::class, 'health']);
});
