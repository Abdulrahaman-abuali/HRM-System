<?php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

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


}
