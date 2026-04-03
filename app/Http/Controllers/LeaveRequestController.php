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

        // جلب القروض النشطة للموظف (اختياري)
        $activeLoans = \App\Models\Loan::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->get();

        return view('employees.leaves', compact('leaves', 'leaveTypes', 'loanRequests', 'activeLoans'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after:start_date',
            'reason'        => 'nullable|string|max:1000',
        ]);

        $employee = Auth::user()->employee;
        $userRole = Auth::user()->role?->name;

        LeaveRequest::create([
            'employee_id'   => $employee->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'reason'        => $request->reason,
            'status'        => 'pending',
        ]);

        // إشعار لمدير النظام
        $admin = User::whereHas('role', function ($q) {
            $q->where('name', 'مدير النظام');
        })->first();

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

        // ✅ التوجيه حسب دور المستخدم
        if ($userRole === 'مدير القسم') {
            return redirect()->route('employee.dashboard')
                ->with('success', 'تم إرسال طلب الإجازة بنجاح.');
        }

        return redirect()->route('requests.index')
            ->with('success', 'تم إرسال طلب الإجازة بنجاح.');
    }
    public function adminIndex()
    {
        $user = Auth::user();
        $role = $user->role?->name;

        // 1. طلبات الإجازات
        $leaveQuery = LeaveRequest::with(['employee.department', 'leaveType']);

        // 2. طلبات القروض (لن تظهر لمدير القسم)
        $loanRequests = collect([]);

        if ($role === 'مدير القسم') {
            $deptId = $user->employee->department_id ?? null;
            if ($deptId) {
                $leaveQuery->whereHas('employee', function ($q) use ($deptId, $user) {
                    $q->where('department_id', $deptId)
                        ->where('user_id', '!=', $user->id);
                });
            } else {
                $leaveRequests = collect([]);
                $leaveStats = [
                    'pending'  => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'total'    => 0,
                ];
                $loanStats = [
                    'pending'  => 0,
                    'approved' => 0,
                    'rejected' => 0,
                    'total'    => 0,
                ];
                $title = "إدارة الطلبات";
                return view('dashbord.attendance', compact('leaveRequests', 'loanRequests', 'leaveStats', 'loanStats', 'title'));
            }
        } else {
            // ✅ مدير النظام يرى الطلبات التي تحتاج إلى تدخل
            // (جديد، موافقة مبدئية، رفض مبدئي)
            $leaveQuery->whereIn('status', ['pending', 'pending_admin', 'rejected_by_dept']);
            $loanRequests = \App\Models\LoanRequest::with('employee')->latest()->get();
        }

        $leaveRequests = $leaveQuery->latest()->get();

        // إحصائيات الإجازات
        $leaveStats = [
            'pending'  => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            'total'    => LeaveRequest::count(),
        ];

        // إحصائيات القروض (لمدير النظام فقط)
        $loanStats = [
            'pending'  => \App\Models\LoanRequest::where('status', 'pending')->count(),
            'approved' => \App\Models\LoanRequest::where('status', 'approved')->count(),
            'rejected' => \App\Models\LoanRequest::where('status', 'rejected')->count(),
            'total'    => \App\Models\LoanRequest::count(),
        ];

        $title = "إدارة الطلبات";

        return view('dashbord.attendance', compact('leaveRequests', 'loanRequests', 'leaveStats', 'loanStats', 'title'));
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
        $currentReason = $leave->reason ?? 'لا يوجد تبرير سابق';

        // ✅ التحقق من الدور
        $isDepartmentManager = ($role === 'مدير القسم');
        $isSystemAdmin = ($role === 'مدير النظام');

        if ($newStatus == 'approved') {
            if ($isDepartmentManager) {
                // مدير القسم يوافق مبدئياً
                $leave->status = 'pending_admin';
                $msg = 'تمت الموافقة المبدئية بنجاح.';
                $notificationText = "وافق مدير القسم مبدئياً على إجازتك.";
            } else {
                // مدير النظام يوافق نهائياً
                $leave->status = 'approved';
                $msg = 'تم الاعتماد النهائي ✅';
                $notificationText = "تمت الموافقة النهائية على طلب إجازتك.";
            }
            $leave->save();

            if ($employee && $employee->user_id) {
                Notification::create([
                    'user_id'         => $employee->user_id,
                    'notifiable_id'   => $employee->user_id,
                    'notifiable_type' => 'App\Models\User',
                    'title'           => 'تحديث إجازة 📅',
                    'text'            => $notificationText,
                    'type'            => 'success',
                    'source'          => 'نظام الإجازات',
                ]);
            }

            return back()->with('success', $msg);
        }

        // حالة الرفض
        if ($newStatus == 'rejected') {
            $newEntry = "🚫 رفض (" . ($isDepartmentManager ? 'مبدئي' : 'نهائي') . " - " . $role . "): " . $request->reject_reason;
            $combinedReason = $newEntry . " [!] " . $currentReason;

            if ($isDepartmentManager) {
                // مدير القسم يرفض مبدئياً فقط
                $leave->status = 'rejected_by_dept';
                $msg = 'تم الرفض المبدئي بنجاح، بانتظار اعتماد مدير النظام.';
                $notificationText = "رفض رئيس القسم طلبك مبدئياً، بانتظار اعتماد مدير النظام.";
            } else {
                // مدير النظام يرفض نهائياً
                $leave->status = 'rejected';
                $msg = 'تم اعتماد الرفض النهائي للطلب ❌';
                $notificationText = "تم اعتماد رفض طلب إجازتك نهائياً.";
            }
            $leave->reason = $combinedReason;
            $leave->save();

            if ($employee && $employee->user_id) {
                Notification::create([
                    'user_id'         => $employee->user_id,
                    'notifiable_id'   => $employee->user_id,
                    'notifiable_type' => 'App\Models\User',
                    'title'           => 'تحديث طلب إجازة ❌',
                    'text'            => $notificationText,
                    'type'            => 'important',
                    'source'          => 'نظام الإجازات',
                ]);
            }

            return back()->with('success', $msg);
        }

        return back()->with('error', 'حالة غير معروفة.');
    }
}
