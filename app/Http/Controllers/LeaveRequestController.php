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

    return redirect()->route('leaves.index')->with('success', 'تم إرسال طلب الإجازة بنجاح.');
    }

    /**
     * عرض صفحة الإدارة (لوحة تحكم المدير)
     */
    public function adminIndex()
{
    $user = Auth::user();
    $role = $user->role?->name;

    // 1. بناء الاستعلام الأساسي مع العلاقات
    $query = LeaveRequest::with(['employee.department', 'leaveType']);

    if ($role === 'مدير القسم') {
        $deptId = $user->employee->department_id ?? null;

        if ($deptId) {
            $query->whereHas('employee', function($q) use ($deptId) {
                $q->where('department_id', $deptId);
            });
        } else {
            // في حال عدم وجود قسم، نرسل كل المتغيرات فارغة لمنع انهيار الـ Blade
            return view('dashbord.attendance', [
                'data' => collect([]),
                'stats' => ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0],
                'employees' => collect([]),
                'allHistory' => collect([]),
                'date' => now()->toDateString()
            ])->with('error', 'لم يتم العثور على قسم مرتبط بحسابك.');
        }
    }

    // 2. جلب البيانات وتسميتها $data لتتوافق مع ملف الـ Blade الخاص بك
    $data = $query->latest()->get();

    // 3. تحديث الإحصائيات
    $stats = [
        'pending'  => $data->where('status', 'pending')->count(),
        'approved' => $data->where('status', 'approved')->count(),
        'rejected' => $data->where('status', 'rejected')->count(),
        'total'    => $data->count(),
    ];

    // 4. متغيرات صمام الأمان (لمنع أخطاء الـ Blade المشتركة)
    $employees = collect([]);
    $allHistory = collect([]);
    $date = now()->toDateString();

    // 5. التعديل الجوهري: إرسال $data والمجموعات الأخرى ليعمل الجدول
    return view('dashbord.attendance', compact('data', 'stats', 'date', 'employees', 'allHistory'));
}
    /**
     * تحديث حالة الطلب (موافقة / رفض) من قبل المدير
     */
    public function updateStatus(Request $request, $id)
{
    // 1. جلب طلب الإجازة مع بيانات الموظف المرتبط به
    $leave = LeaveRequest::findOrFail($id);

    // جلب الموظف للحصول على الـ user_id الخاص به
    $employee = \App\Models\Employee::find($leave->employee_id);

    $request->validate([
        'status' => 'required|in:approved,rejected'
    ]);

    $leave->update(['status' => $request->status]);

    // 2. إرسال الإشعار باستخدام الـ user_id الموجود في جدول الموظفين
    if ($employee && $employee->user_id) {
        $isApproved = $request->status == 'approved';

        \App\Models\Notification::create([
            'user_id' => $employee->user_id, // هنا نستخدم الـ user_id الصحيح
            'title'   => $isApproved ? 'تمت الموافقة على إجازتك ✅' : 'تم رفض طلب الإجازة ❌',
            'text'    => $isApproved
                ? "تمت الموافقة على طلب إجازتك للفترة من {$leave->start_date} إلى {$leave->end_date}."
                : "نعتذر، تم رفض طلب إجازتك المقدم للفترة من {$leave->start_date}.",
            'type'    => $isApproved ? 'success' : 'important',
            'source'  => 'نظام الإجازات',
        ]);
    }

    $msg = $request->status == 'approved' ? 'تمت الموافقة وإرسال الإشعار' : 'تم الرفض وإرسال الإشعار';
    return back()->with('success', $msg);
}
}
