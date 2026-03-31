<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;
use App\Models\User;

class LeaveRequestController extends Controller
{
    /**
     * عرض صفحة الإجازات للموظف (لوحة الموظف)
     */
    public function index()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'عذراً، حسابك غير مرتبط ببيانات موظف.');
        }

        $leaveTypes = LeaveType::all();
        $leaves = LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType')
            ->latest()
            ->get();

        return view('employees.leaves', compact('leaves', 'leaveTypes'));
    }

    /**
     * حفظ طلب إجازة جديد من قبل الموظف
     */
    public function store(Request $request)
    {
    $request->validate([
        'leave_type_id' => 'required|exists:leave_types,id',
        'start_date'    => 'required|date|after_or_equal:today',
        'end_date'      => 'required|date|after:start_date',
        'reason'        => 'nullable|string|max:1000',
    ]);

    $employee = Auth::user()->employee;

    // 1. إنشاء طلب الإجازة
    LeaveRequest::create([
        'employee_id'   => $employee->id,
        'leave_type_id' => $request->leave_type_id,
        'start_date'    => $request->start_date,
        'end_date'      => $request->end_date,
        'reason'        => $request->reason,
        'status'        => 'pending',
    ]);

    // 2. البحث عن المدير
    $admin = User::whereHas('role', function($q){
        $q->where('name', 'مدير النظام');
    })->first();

    // 3. إنشاء الإشعار (مع إضافة الحقول الإجبارية الجديدة)
    if ($admin) {
        Notification::create([
            'user_id'         => $admin->id, // حقل قديم (اختياري حسب جدولك)
            'notifiable_id'   => $admin->id, // إلزامي الآن
            'notifiable_type' => 'App\Models\User', // إلزامي الآن
            'title'           => 'طلب إجازة جديد 📅',
            'text'            => 'قام الموظف ' . $employee->first_name . ' بتقديم طلب إجازة جديد بانتظار موافقتك.',
            'type'            => 'reminder',
            'source'          => 'نظام الإجازات',
            'is_read'         => false,
        ]);
    }

    return back()->with('success', 'تم إرسال طلب الإجازة بنجاح.');
    }

    /**
     * عرض صفحة الإدارة (لوحة تحكم المدير)
     */
   public function adminIndex()
{
    $user = Auth::user();
    $employee = $user->employee; // جلب سجل الموظف الخاص بالمدير
    $role = $user->role?->name;

    // 1. ✨ جلب طلبات الإجازة الخاصة بالمدير نفسه (بصفته موظف)
    // هذا السطر هو المحرك للجدول الجديد "حالة طلباتي الأخيرة"
    $myRequests = LeaveRequest::where('employee_id', $employee->id ?? 0)
                                ->latest()
                                ->take(5)
                                ->get();

    // 2. بناء الاستعلام الأساسي لطلبات الموظفين (بصفته مديراً)
    $query = LeaveRequest::with(['employee.department', 'leaveType']);

    if ($role === 'مدير القسم') {
        $deptId = $employee->department_id ?? null;

        if ($deptId) {
            $query->whereHas('employee', function($q) use ($deptId, $employee) {
                // فلترة موظفي القسم "باستثناء" المدير نفسه لكي لا يعتمد إجازته بنفسه
                $q->where('department_id', $deptId)->where('id', '!=', $employee->id);
            });
        } else {
            // صمام أمان في حال عدم وجود قسم
            return view('dashbord.attendance', [
                'data' => collect([]),
                'myRequests' => $myRequests,
                'stats' => ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0],
                'employees' => collect([]),
                'allHistory' => collect([]),
                'date' => now()->toDateString()
            ])->with('error', 'لم يتم العثور على قسم مرتبط بحسابك.');
        }
    }

    // 3. جلب بيانات طلبات الموظفين (جدول الإدارة)
    $data = $query->latest()->get();

    // 4. تحديث الإحصائيات لموظفي القسم
    $stats = [
        'pending'  => $data->where('status', 'pending')->count(),
        'approved' => $data->where('status', 'approved')->count(),
        'rejected' => $data->where('status', 'rejected')->count(),
        'total'    => $data->count(),
    ];

    // 5. متغيرات صمام الأمان للـ Blade
    $employees = collect([]);
    $allHistory = collect([]);
    $date = now()->toDateString();

    // 6. إرسال كل شيء للـ View
    return view('dashbord.attendance', compact('data', 'stats', 'myRequests', 'date', 'employees', 'allHistory'));
}
    /**
     * تحديث حالة الطلب (موافقة / رفض) من قبل المدير
     */
   public function updateStatus(Request $request, $id)
{
    $leave = LeaveRequest::findOrFail($id);
    $employee = \App\Models\Employee::find($leave->employee_id);
    $user = Auth::user();
    $role = $user->role?->name;

    $request->validate([
        'status' => 'required|in:approved,rejected',
        'reject_reason' => 'required_if:status,rejected|nullable|string|max:500'
    ]);

    $newStatus = $request->status;
    $notificationText = "";

    // جلب النص الحالي المخزن في القاعدة
    $currentReason = $leave->reason ?? 'لا يوجد تبرير سابق';

    if ($newStatus == 'approved') {
        if ($role === 'مدير القسم') {
            $leave->update(['status' => 'pending_admin']);
            $msg = 'تمت الموافقة المبدئية بنجاح.';
            $notificationText = "وافق مدير القسم مبدئياً على إجازتك.";
        } else {
            $leave->update(['status' => 'approved']);
            $msg = 'تم الاعتماد النهائي ✅';
            $notificationText = "تمت الموافقة النهائية على طلب إجازتك.";
        }
    }
    else { // حالة الرفض
        // تحديد النص الجديد بناءً على الدور
        $newEntry = "🚫 رفض (" . $role . "): " . $request->reject_reason;

        /**
         * منطق ترتيب الأسباب:
         * إذا كان مدير النظام هو من يرفض، نضع سببه في البداية ليكون في الأعلى
         * ثم يليه ما كان موجوداً سابقاً (سبب مدير القسم + سبب الموظف)
         */
        $combinedReason = $newEntry . " [!] " . $currentReason;

        if ($role === 'مدير القسم') {
            $leave->update([
                'status' => 'rejected_by_dept',
                'reason' => $combinedReason
            ]);
            $msg = 'تم الرفض مبدئياً وحفظ التبرير.';
            $notificationText = "رفض رئيس القسم طلبك مبدئياً.";
        } else {
            $leave->update([
                'status' => 'rejected',
                'reason' => $combinedReason
            ]);
            $msg = 'تم الرفض النهائي للطلب ❌';
            $notificationText = "رفض مدير النظام طلبك نهائياً.";
        }
    }

    if ($employee && $employee->user_id) {
        \App\Models\Notification::create([
            'user_id'         => $employee->user_id,
            'notifiable_id'   => $employee->user_id,
            'notifiable_type' => 'App\Models\User',
            'title'           => str_contains($leave->status, 'approved') ? 'تحديث إجازة 📅' : 'رفض إجازة ❌',
            'text'            => $notificationText,
            'type'            => str_contains($leave->status, 'approved') ? 'success' : 'important',
            'source'          => 'نظام الإجازات',
        ]);
    }

    return back()->with('success', $msg);
}
}
