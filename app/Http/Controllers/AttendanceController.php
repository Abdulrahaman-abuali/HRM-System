<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return redirect()->dashboard()->with('error', 'بيانات الموظف غير مكتملة.');
        }

        $today = now()->toDateString();

        $todayRecord = AttendanceRecord::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        $records = AttendanceRecord::where('employee_id', $employee->id)
            ->latest('date')
            ->paginate(20);

        return view('dashbord.leave', compact('records', 'todayRecord'));
    }

    public function checkIn()
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return redirect()->route('leave')->with('error', 'حسابك غير مرتبط بموظف.');
        }

        $today = now()->toDateString();
        $now = now();

        $record = AttendanceRecord::firstOrCreate([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        if ($record->check_in) {
            return redirect()->route('leave')->with('error', 'تم تسجيل الحضور مسبقاً.');
        }

        // 1. تسجيل وقت الحضور
        $record->update(['check_in' => $now->format('H:i:s')]);

        // ⚠️ تم حذف سطر الـ event الخاص بـ Reverb لتجنب أخطاء الاتصال

        // 2. منطق إرسال إشعار التأخير (بعد الساعة 8:00 صباحاً مثلاً)
        $workStartTime = Carbon::parse('08:00:00');

        if ($now->greaterThan($workStartTime)) {
            // البحث عن مدير النظام أو مدير القسم المرتبط بالموظف
            $recipients = User::whereHas('role', function($q) {
                $q->whereIn('name', ['مدير النظام', 'مدير القسم']);
            })->where(function($query) use ($employee) {
                // إما مدير نظام (يرى الكل) أو مدير قسم ينتمي له الموظف
                $query->whereHas('role', fn($r) => $r->where('name', 'مدير النظام'))
                      ->orWhereHas('employee', fn($e) => $e->where('department_id', $employee->department_id));
            })->get();

            foreach ($recipients as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'notifiable_id' => $recipient->id,
                    'notifiable_type' => 'App\Models\User',
                    'title'   => 'تنبيه تأخير ⏰',
                    'text'    => 'سجل الموظف ' . $employee->full_name . ' حضوراً متأخراً الساعة ' . $now->format('H:i A'),
                    'type'    => 'warning',
                    'source'  => 'نظام الحضور',
                    'is_read' => false,
                ]);
            }
        }

        return redirect()->route('leave')->with('success', 'تم تسجيل الحضور بنجاح.');
    }

    public function checkOut()
    {
        $employee = Auth::user()->employee;
        if (!$employee) {
            return redirect()->route('leave')->with('error', 'حسابك غير مرتبط بموظف.');
        }

        $today = now()->toDateString();

        $record = AttendanceRecord::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        if (!$record || !$record->check_in) {
            return redirect()->route('leave')->with('error', 'يجب تسجيل الحضور أولاً.');
        }

        if ($record->check_out) {
            return redirect()->route('leave')->with('error', 'تم تسجيل الانصراف مسبقاً.');
        }

        $record->update(['check_out' => now()->format('H:i:s')]);

        // ⚠️ تم حذف سطر الـ event الخاص بـ Reverb

        return redirect()->route('leave')->with('success', 'تم تسجيل الانصراف بنجاح.');
    }
}
