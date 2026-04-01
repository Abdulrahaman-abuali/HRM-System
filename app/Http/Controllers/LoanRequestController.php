<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\LoanRequest;
use App\Models\Loan;
use App\Models\Employee;
use App\Models\User;
use App\Models\Notification;
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

    public function store(Request $request)
    {
        $employee = Auth::user()->employee;
        $userRole = Auth::user()->role?->name;

        // ✅ التحقق من وجود قرض نشط للموظف
        $activeLoan = \App\Models\Loan::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->exists();

        if ($activeLoan) {
            if ($userRole === 'مدير القسم') {
                return redirect()->route('employee.dashboard')
                    ->with('error', 'لا يمكنك تقديم طلب قرض جديد لأن لديك قرضاً نشطاً حالياً.');
            }
            return redirect()->route('requests.index')
                ->with('error', 'لا يمكنك تقديم طلب قرض جديد لأن لديك قرضاً نشطاً حالياً.');
        }

        // ✅ التحقق من وجود طلب قرض قيد الانتظار (pending) للموظف
        $pendingRequest = \App\Models\LoanRequest::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->exists();

        if ($pendingRequest) {
            if ($userRole === 'مدير القسم') {
                return redirect()->route('employee.dashboard')
                    ->with('error', 'لا يمكنك تقديم طلب قرض جديد لأن لديك طلباً قيد المراجعة حالياً.');
            }
            return redirect()->route('requests.index')
                ->with('error', 'لا يمكنك تقديم طلب قرض جديد لأن لديك طلباً قيد المراجعة حالياً.');
        }

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

        // ✅ إشعار لمدير النظام عند تقديم طلب قرض جديد
        $admin = User::whereHas('role', function ($q) {
            $q->where('name', 'مدير النظام');
        })->first();

        if ($admin) {
            Notification::create([
                'user_id'         => $admin->id,
                'notifiable_id'   => $admin->id,
                'notifiable_type' => 'App\Models\User',
                'title'           => 'طلب قرض جديد 💰',
                'text'            => 'قام الموظف ' . $employee->first_name . ' ' . $employee->last_name . ' بتقديم طلب قرض بمبلغ ' . number_format($request->amount, 2) . ' ريال بانتظار موافقتك.',
                'type'            => 'reminder',
                'source'          => 'نظام القروض',
                'is_read'         => false,
            ]);
        }

        // ✅ التوجيه حسب دور المستخدم
        if ($userRole === 'مدير القسم') {
            return redirect()->route('employee.dashboard')
                ->with('success', 'تم تقديم طلب القرض بنجاح، سيتم مراجعته من قبل إدارة الموارد البشرية.');
        }

        return redirect()->route('requests.index')
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
            'start_date' => now()->addMonth()->startOfMonth(),
            'status' => 'active',
            'notes' => 'تمت الموافقة على طلب رقم ' . $loanRequest->id,
        ]);

        // ✅ إشعار للموظف عند الموافقة على طلب القرض
        if ($loanRequest->employee && $loanRequest->employee->user_id) {
            Notification::create([
                'user_id'         => $loanRequest->employee->user_id,
                'notifiable_id'   => $loanRequest->employee->user_id,
                'notifiable_type' => 'App\Models\User',
                'title'           => 'موافقة على طلب قرض ✅',
                'text'            => 'تمت الموافقة على طلب القرض الخاص بك بمبلغ ' . number_format($loanRequest->amount, 2) . ' ريال. سيتم خصم القسط الشهري من راتبك.',
                'type'            => 'success',
                'source'          => 'نظام القروض',
                'is_read'         => false,
            ]);
        }

        return redirect()->route('attendance')
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

        // ✅ إشعار للموظف عند رفض طلب القرض
        if ($loanRequest->employee && $loanRequest->employee->user_id) {
            Notification::create([
                'user_id'         => $loanRequest->employee->user_id,
                'notifiable_id'   => $loanRequest->employee->user_id,
                'notifiable_type' => 'App\Models\User',
                'title'           => 'رفض طلب قرض ❌',
                'text'            => 'عذراً، تم رفض طلب القرض الخاص بك بمبلغ ' . number_format($loanRequest->amount, 2) . ' ريال. يمكنك التواصل مع إدارة الموارد البشرية لمعرفة السبب.',
                'type'            => 'important',
                'source'          => 'نظام القروض',
                'is_read'         => false,
            ]);
        }

        return redirect()->route('attendance')
            ->with('success', 'تم رفض طلب القرض.');
    }
}
