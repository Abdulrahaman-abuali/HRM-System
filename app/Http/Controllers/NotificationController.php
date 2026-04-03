<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Notification::where('user_id', $user->id);

        // الفلترة (توسيع الأنواع لتشمل الرواتب والحضور والمهام)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_read', $request->status == 'read' ? 1 : 0);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $notifications = $query->latest()->get();

        // تحديث الإحصائيات لتشمل التصنيفات الجديدة
        $stats = [
            'unread'    => Notification::where('user_id', $user->id)->where('is_read', 0)->count(),
            'important' => Notification::where('user_id', $user->id)->whereIn('type', ['important', 'warning'])->count(),
            'system'    => Notification::where('user_id', $user->id)->whereIn('type', ['system', 'success', 'info'])->count(),
            'total'     => Notification::where('user_id', $user->id)->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('dashbord.notifications', compact('notifications', 'stats'));
    }

    public function markAsRead($id) {
        Notification::where('id', $id)->where('user_id', Auth::id())->update(['is_read' => true]);
        return back()->with('success', 'تم تحديث الإشعار كمقروء');
    }

    public function markAllAsRead() {
        Notification::where('user_id', Auth::id())->update(['is_read' => true]);
        return back()->with('success', 'تم تحديد جميع الإشعارات كمقروءة');
    }

    public function destroy($id) {
        Notification::where('id', $id)->where('user_id', Auth::id())->delete();
        return back()->with('success', 'تم حذف الإشعار');
    }

    public function destroyAll() {
        Notification::where('user_id', Auth::id())->delete();
        return back()->with('success', 'تم حذف جميع الإشعارات');
    }

    public function sendGeneralNotification(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    // جلب جميع المستخدمين في النظام
    $users = \App\Models\User::all();

    foreach ($users as $user) {
        \App\Models\Notification::create([
            'user_id' => $user->id,
            'title'   => '📢 ' . $request->title,
            'text'    => $request->message,
            'type'    => 'important', // ليظهر بلون مميز
            'source'  => 'إعلان عام',
        ]);
    }

    return back()->with('success', 'تم إرسال الإشعار العام لجميع الموظفين بنجاح.');
}

/**
 * إرسال إشعار لموظف واحد (من صفحة الإشعارات)
 */
public function sendToEmployee(Request $request)
{
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'title' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    $employee = \App\Models\Employee::findOrFail($request->employee_id);

    if (!$employee->user_id) {
        return back()->with('error', 'هذا الموظف ليس لديه حساب مستخدم.');
    }

    \App\Models\Notification::create([
        'user_id' => $employee->user_id,
        'title'   => '📢 ' . $request->title,
        'text'    => $request->message,
        'type'    => 'important',
        'source'  => 'إشعار خاص',
        'is_read' => false,
    ]);

    return back()->with('success', 'تم إرسال الإشعار إلى ' . $employee->first_name . ' ' . $employee->last_name . ' بنجاح.');
}
/**
 * إرسال إشعار عام لقسم مدير القسم
 */
public function sendToDepartment(Request $request)
{
    $user = Auth::user();
    $departmentId = $user->employee->department_id ?? null;

    if (!$departmentId) {
        return back()->with('error', 'لا يمكنك إرسال إشعارات لأن حسابك غير مرتبط بقسم.');
    }

    $request->validate([
        'title' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    // جلب جميع الموظفين في نفس القسم
    $employees = \App\Models\Employee::where('department_id', $departmentId)->get();

    if ($employees->isEmpty()) {
        return back()->with('error', 'لا يوجد موظفين في قسمك لإرسال الإشعارات لهم.');
    }

    $sentCount = 0;
    foreach ($employees as $employee) {
        if ($employee->user_id) {
            \App\Models\Notification::create([
                'user_id' => $employee->user_id,
                'title'   => '📢 ' . $request->title,
                'text'    => $request->message,
                'type'    => 'important',
                'source'  => 'إعلان من مدير القسم',
                'is_read' => false,
            ]);
            $sentCount++;
        }
    }

    return back()->with('success', "تم إرسال الإشعار إلى {$sentCount} موظف في قسمك بنجاح.");
}

/**
 * إرسال إشعار لموظف في قسم مدير القسم
 */
public function sendToEmployeeInDepartment(Request $request)
{
    $user = Auth::user();
    $departmentId = $user->employee->department_id ?? null;

    if (!$departmentId) {
        return back()->with('error', 'لا يمكنك إرسال إشعارات لأن حسابك غير مرتبط بقسم.');
    }

    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'title' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    $employee = \App\Models\Employee::findOrFail($request->employee_id);

    // التأكد أن الموظف في نفس القسم
    if ($employee->department_id != $departmentId) {
        return back()->with('error', 'لا يمكنك إرسال إشعار لموظف خارج قسمك.');
    }

    if (!$employee->user_id) {
        return back()->with('error', 'هذا الموظف ليس لديه حساب مستخدم.');
    }

    \App\Models\Notification::create([
        'user_id' => $employee->user_id,
        'title'   => '📢 ' . $request->title,
        'text'    => $request->message,
        'type'    => 'important',
        'source'  => 'إشعار من مدير القسم',
        'is_read' => false,
    ]);

    return back()->with('success', 'تم إرسال الإشعار إلى ' . $employee->first_name . ' ' . $employee->last_name . ' بنجاح.');
}

public function checkNew(Request $request)
{
    $user = Auth::user();
    $lastCheck = $request->input('last_check');

    // إذا لم يكن هناك وقت حفظ مسبق، نستخدم الوقت الحالي ناقص 5 ثوانٍ لتجنب فقد الإشعارات
    if (!$lastCheck) {
        $lastCheck = Carbon::now()->subSeconds(5)->toDateTimeString();
    }

    // جلب الإشعارات الجديدة (غير مقروءة والتي تم إنشاؤها بعد آخر فحص)
    $newNotifications = Notification::where('user_id', $user->id)
        ->where('created_at', '>', $lastCheck)
        ->where('is_read', false)
        ->orderBy('created_at', 'desc')
        ->get();

    // الإحصائيات
    $unreadCount = Notification::where('user_id', $user->id)->where('is_read', false)->count();

    return response()->json([
        'new_count' => $newNotifications->count(),
        'new_notifications' => $newNotifications->map(function ($noti) {
            return [
                'id' => $noti->id,
                'title' => $noti->title,
                'text' => $noti->text,
                'type' => $noti->type,
                'source' => $noti->source,
                'created_at' => $noti->created_at->toDateTimeString(),
                'link' => $this->getLinkForNotification($noti) // دالة لتوليد رابط (اختياري)
            ];
        }),
        'unread_count' => $unreadCount,
        'last_check' => now()->toDateTimeString(),
    ]);
}

// دالة مساعدة لتحديد رابط الإشعار (مثلاً إذا كان مرتبطًا بإجازة أو راتب)
private function getLinkForNotification($notification)
{
    if ($notification->source == 'نظام الإجازات') {
        return route('attendance'); // تأكد من صحة الاسم
    } elseif ($notification->source == 'نظام الرواتب') {
        return route('payroll.index');
    }
    return '#';
}


}
