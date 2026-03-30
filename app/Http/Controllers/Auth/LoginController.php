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
    // تنفيذ عملية تسجيل الخروج من النظام وتدمير الجلسة فقط
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // تعديل رسالة النجاح لتناسب التغيير الجديد
    return redirect()->route('login')->with('success', 'تم تسجيل خروجك بنجاح.');
    }
}
