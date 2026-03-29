<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class Dashbord extends Controller
{
    public function index()
{
    // 1. الإحصائيات السريعة
    $stats = [
        'total_employees' => \App\Models\Employee::count(),
        'today_attendance' => \App\Models\AttendanceRecord::whereDate('created_at', today())->count(),
        'pending_leaves' => \App\Models\LeaveRequest::where('status', 'pending')->count(),
        'unread_notifications' => \Illuminate\Support\Facades\Auth::user()->unreadNotifications->count(),
    ];

    // 2. سجل الحضور اليوم
    $todayAttendance = \App\Models\AttendanceRecord::with('employee.department')
                        ->whereDate('created_at', today())
                        ->latest()
                        ->take(5)
                        ->get();

    // 3. آخر النشاطات (Log)
    $activities = \App\Models\ActivityLog::latest()->take(5)->get();

    // 4. أحدث طلبات الإجازة
    $latestLeaves = \App\Models\LeaveRequest::with('employee')->latest()->take(3)->get();

    return view('dashboard', compact('stats', 'todayAttendance', 'activities', 'latestLeaves'));
}
}
