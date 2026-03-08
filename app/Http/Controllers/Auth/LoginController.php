<?php

namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRecord;

class LoginController extends Controller
{
    /**
     * إظهار نموذج تسجيل الدخول
     * ملاحظة: قمنا بتوجيه هذا المسار في web.php إلى PagesController@showLogin
     * لذا يمكنك الإبقاء عليها هنا كاحتياط أو حذفها.
     */
    public function showLoginForm()
    {
        return view('login');
    }

    /**
     * معالجة تسجيل الدخول
     * ملاحظة: اعتمدنا PagesController@login لمعالجة الدخول مع تسجيل الحضور
     */

    /**
     * تسجيل الخروج + تسجيل الانصراف تلقائياً
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // --- تسجيل الانصراف تلقائياً عند الضغط على تسجيل الخروج ---
        if ($user && $user->employee) {
            $today = now()->toDateString();

            // البحث عن سجل حضور اليوم الخاص بهذا الموظف
            $record = AttendanceRecord::where('employee_id', $user->employee->id)
                ->where('date', $today)
                ->first();

            // تحديث وقت الانصراف فقط إذا كان الموظف مسجل دخول ولم يسجل انصرافه بعد
            if ($record && $record->check_in && !$record->check_out) {
                $record->update([
                    'check_out' => now()->format('H:i:s')
                ]);
            }
        }
        // -------------------------------------------------------

        // تنفيذ عملية تسجيل الخروج من النظام وتدمير الجلسة
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'تم تسجيل خروجك وانصرافك بنجاح.');
    }
}
