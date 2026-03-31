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

        // جلب طلبات القروض للموظف
        $loanRequests = \App\Models\LoanRequest::where('employee_id', $employee->id)
            ->latest()
            ->get();

        return view('employees.leaves', compact('leaves', 'leaveTypes', 'loanRequests'));
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

        // 3. إنشاء الإشعار
        if ($admin) {
            Notification::create([
                'user_id'         => $admin->id,
                'notifiable_id'   => $admin->id,
                'notifiable_type' => 'App\Models\User',
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
        $employee = $user->employee;
        $role = $user->role?->name;

        // 1. طلبات الإجازة الخاصة بالمدير نفسه (لجدول "حالة طلباتي الأخيرة")
        $myRequests = LeaveRequest::where('employee_id', $employee->id ?? 0)
            ->latest()
            ->take(5)
            ->get();

        // 2. طلبات الإجازات للموظفين (بصفته مديراً)
        $leaveQuery = LeaveRequest::with(['employee.department', 'leaveType']);

        if ($role === 'مدير القسم') {
            $deptId = $employee->department_id ?? null;
            if ($deptId) {
                $leaveQuery->whereHas('employee', function($q) use ($deptId, $employee) {
                    $q->where('department_id', $deptId)
                      ->where('id', '!=', $employee->id); // استبعاد المدير نفسه
                });
            } else {
                // صمام أمان في حال عدم وجود قسم
                return view('dashbord.attendance', [
                    'leaveRequests' => collect([]),
                    'loanRequests' => collect([]),
                    'leaveStats' => ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0],
                    'loanStats' => ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0],
                    'myRequests' => $myRequests,
                ])->with('error', 'لم يتم العثور على قسم مرتبط بحسابك.');
            }
        }

        $leaveRequests = $leaveQuery->latest()->get();

        // 3. طلبات القروض
        $loanRequests = \App\Models\LoanRequest::with('employee')->latest()->get();

        // 4. إحصائيات الإجازات
        $leaveStats = [
            'pending'  => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            'total'    => LeaveRequest::count(),
        ];

        // 5. إحصائيات القروض
        $loanStats = [
            'pending'  => \App\Models\LoanRequest::where('status', 'pending')->count(),
            'approved' => \App\Models\LoanRequest::where('status', 'approved')->count(),
            'rejected' => \App\Models\LoanRequest::where('status', 'rejected')->count(),
            'total'    => \App\Models\LoanRequest::count(),
        ];

        return view('dashbord.attendance', compact('leaveRequests', 'loanRequests', 'leaveStats', 'loanStats', 'myRequests'));
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
        } else { // حالة الرفض
            $newEntry = "🚫 رفض (" . $role . "): " . $request->reject_reason;
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
