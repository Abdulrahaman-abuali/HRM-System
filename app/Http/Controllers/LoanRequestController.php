<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use App\Models\Loan;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LoanRequestController extends Controller
{
    /**
     * عرض طلبات القروض للموظف (صفحته الشخصية)
     */
    public function index()
    {
        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'حسابك غير مرتبط بموظف');
        }

        $requests = LoanRequest::where('employee_id', $employee->id)
            ->latest()
            ->get();

        return view('loans.my_requests', compact('requests'));
    }

    /**
     * عرض نموذج تقديم طلب قرض
     */
    public function create()
    {
        return view('loans.create');
    }

    /**
     * حفظ طلب قرض جديد
     */
    public function store(Request $request)
    {
        $employee = Auth::user()->employee;

        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'months' => 'required|integer|min:1|max:24',
            'reason' => 'nullable|string|max:1000',
        ]);

        $monthlyInstallment = $request->amount / $request->months;

        LoanRequest::create([
            'employee_id' => $employee->id,
            'amount' => $request->amount,
            'months' => $request->months,
            'monthly_installment' => round($monthlyInstallment, 2),
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return redirect()->route('loans.my-requests')
            ->with('success', 'تم تقديم طلب القرض بنجاح، سيتم مراجعته من قبل إدارة الموارد البشرية.');
    }

    /**
     * عرض طلبات القروض لمدير النظام
     */
    public function adminIndex()
    {
        $requests = LoanRequest::with('employee')
            ->latest()
            ->get();

        return view('loans.admin_requests', compact('requests'));
    }

    /**
     * الموافقة على طلب قرض
     */
    public function approve($id)
    {
        $loanRequest = LoanRequest::findOrFail($id);

        if ($loanRequest->status !== 'pending') {
            return back()->with('error', 'تم معالجة هذا الطلب مسبقاً');
        }

        // تحديث حالة الطلب
        $loanRequest->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // إنشاء سجل القرض في جدول loans
        Loan::create([
            'employee_id' => $loanRequest->employee_id,
            'amount' => $loanRequest->amount,
            'monthly_installment' => $loanRequest->monthly_installment,
            'total_months' => $loanRequest->months,
            'paid_months' => 0,
            'remaining_balance' => $loanRequest->amount,
            'start_date' => now()->addMonth()->startOfMonth(), // يبدأ من الشهر القادم
            'status' => 'active',
            'notes' => 'تمت الموافقة على طلب رقم ' . $loanRequest->id,
        ]);

        return redirect()->route('loans.admin-requests')
            ->with('success', 'تمت الموافقة على طلب القرض وإنشاء سجل القرض بنجاح.');
    }

    /**
     * رفض طلب قرض
     */
    public function reject($id)
    {
        $loanRequest = LoanRequest::findOrFail($id);

        if ($loanRequest->status !== 'pending') {
            return back()->with('error', 'تم معالجة هذا الطلب مسبقاً');
        }

        $loanRequest->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('loans.admin-requests')
            ->with('success', 'تم رفض طلب القرض.');
    }
}
