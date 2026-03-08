<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

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

        LeaveRequest::create([
            'employee_id'   => Auth::user()->employee->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'reason'        => $request->reason,
            'status'        => 'pending',
        ]);

        return redirect()->route('leaves.index')->with('success', 'تم إرسال طلب الإجازة بنجاح.');
    }

    /**
     * عرض صفحة الإدارة (لوحة تحكم المدير)
     */
    public function adminIndex()
    {
        $stats = [
            'pending'  => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
            'total'    => LeaveRequest::count(),
        ];

        $allLeaves = LeaveRequest::with(['employee', 'leaveType'])->latest()->get();

        return view('dashbord.leaves', compact('allLeaves', 'stats'));
    }

    /**
     * تحديث حالة الطلب (موافقة / رفض) من قبل المدير
     */
    public function updateStatus(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);

        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $leave->update(['status' => $request->status]);

        $msg = $request->status == 'approved' ? 'تمت الموافقة على الإجازة' : 'تم رفض طلب الإجازة';
        return back()->with('success', $msg);
    }
}
