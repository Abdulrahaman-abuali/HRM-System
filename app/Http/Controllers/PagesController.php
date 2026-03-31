<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Routing\Controller;
use App\Models\Salary;
use App\Models\Notification;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\ActivityLog;

class PagesController extends Controller
{
    /**
     * عرض صفحة تسجيل الدخول
     */
    public function showLogin()
    {
        return view('login');
    }

    /**
     * معالجة عملية تسجيل الدخول + تسجيل الحضور تلقائياً
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة.']);
        }

        $request->session()->regenerate();
        $user = Auth::user();

        // تحقق من حالة النشاط
        if (isset($user->is_active) && !$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'الحساب غير مفعل.']);
        }

        // التوجيه بناءً على الدور (Role)
        $roleName = $user->role?->name;

        if ($roleName === 'مدير النظام') {
            return redirect()->route('dashbord');
        }

        if ($user->role->name === 'مدير القسم') {
            return redirect()->route('employees.dashboard_mangers'); // أو الصفحة التي تريدها أن تكون واجهته الرئيسية
        }

        if ($roleName === 'موظف') {
            return redirect()->route('employee.dashboard');
        }

        // في حال عدم وجود دور معروف
        Auth::logout();
        return back()->withErrors(['email' => 'عفواً، لا يمتلك هذا الحساب صلاحيات الوصول للنظام.']);
    }
    /**
     * لوحة تحكم المدير
     */
    public function showDashboardPage()
    {
        $user = Auth::user();

        $role = $user->role?->name;
        // إصلاح: عد الإشعارات غير المقروءة للمستخدم الحالي
        $unreadNotificationsCount = \App\Models\Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', get_class($user))
            ->where('is_read', 0)
            ->count();

        // 🛡️ إذا كان المستخدم "مدير قسم"
        if ($role === 'مدير القسم') {
            $deptId = $user->employee->department_id;

            $stats = [
                'total_employees'      => Employee::where('department_id', $deptId)->count(),
                'today_attendance'     => AttendanceRecord::whereDate('date', today())
                    ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
                    ->count(),
                'pending_leaves'       => LeaveRequest::where('status', 'pending')
                    ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
                    ->count(),
                'unread_notifications' => $unreadNotificationsCount,
            ];

            // جلب نشاطات وحضور القسم فقط
            $todayAttendance = AttendanceRecord::with('employee')
                ->whereDate('date', today())
                ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
                ->take(5)->get();

            $latestLeaves = LeaveRequest::with('employee')
                ->whereHas('employee', fn($q) => $q->where('department_id', $deptId))
                ->latest()->take(3)->get();

            // السجلات والنشاطات (يمكنك فلترتها أيضاً حسب القسم إذا كان جدول النشاطات يدعم ذلك)
            $activities = \App\Models\ActivityLog::latest()->take(5)->get();

            return view('employees.dashboard_mangers', compact('stats', 'todayAttendance', 'activities', 'latestLeaves'));
        }

        // 👑 إحصائيات مدير النظام (التي كانت لديك مسبقاً)
        $stats = [
            'total_employees'      => Employee::count(),
            'today_attendance'     => AttendanceRecord::whereDate('date', today())->count(),
            'pending_leaves'       => LeaveRequest::where('status', 'pending')->count(),
            'unread_notifications' => $unreadNotificationsCount,
        ];

        $todayAttendance = AttendanceRecord::with('employee')->whereDate('date', today())->take(5)->get();
        $activities = \App\Models\ActivityLog::latest()->take(5)->get();
        $latestLeaves = LeaveRequest::with('employee')->latest()->take(3)->get();

        return view('dashbord.index', compact('stats', 'todayAttendance', 'activities', 'latestLeaves'));
    }

    /**
     * لوحة تحكم الموظف
     */
    public function showEmployeeDashboard()
    {
        return view('employees.dashboard');
    }

    /**
     * صفحة سجل الحضور والانصراف (للموظف والمدير)
     */
    public function showLeavePage(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        $role = $user->role?->name;
        $date = $request->input('date', now()->toDateString());

        // 1. إذا كان المستخدم موظف (يرى أرشيفه الشخصي فقط)
        if ($role === 'موظف' && $employee) {
            $records = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
                ->orderByDesc('date')
                ->paginate(20);

            return view('dashbord.leave', compact('records', 'date'));
        }

        /* -----------------------------------------------------------
       2. إذا كان مديراً (نظام أو قسم)
    ----------------------------------------------------------- */

        // بناء استعلام الموظفين
        $employeesQuery = \App\Models\Employee::with(['department', 'attendanceRecords' => function ($q) use ($date) {
            $q->where('date', $date);
        }]);

        // بناء استعلام الأرشيف
        $historyQuery = \App\Models\AttendanceRecord::with('employee.department');

        // ✨ إضافة شرط القسم إذا كان المستخدم "مدير قسم"
        if ($role === 'مدير القسم') {
            $deptId = $employee->department_id ?? null;

            if ($deptId) {
                // فلترة الموظفين حسب القسم
                $employeesQuery->where('department_id', $deptId);

                // فلترة الأرشيف حسب قسم الموظف
                $historyQuery->whereHas('employee', function ($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                });
            }
        }

        // تنفيذ الاستعلامات
        $employees = $employeesQuery->get();
        $allHistory = $historyQuery->orderByDesc('date')->paginate(15);

        // حساب الإحصائيات (ستكون دقيقة حسب القسم للمدير، أو شاملة للمدير العام)
        $stats = [
            'total'   => $employees->count(),
            'present' => $employees->filter(fn($e) => $e->attendanceRecords->isNotEmpty())->count(),
            'absent'  => $employees->filter(fn($e) => $e->attendanceRecords->isEmpty())->count(),
        ];

        $allLeaves = collect([]);


        return view('dashbord.leave', compact('employees', 'allHistory', 'date', 'stats', 'allLeaves'));
    }
    /**
     * صفحات النظام الأخرى (الإدارة)
     */
    public function showEmployeesPage()
    {
        return view('dashbord.2');
    }
public function showAttendancePage()
{
    // طلبات الإجازات
    $leaveRequests = \App\Models\LeaveRequest::with(['employee', 'leaveType'])->latest()->get();

    // طلبات القروض
    $loanRequests = \App\Models\LoanRequest::with('employee')->latest()->get();

    // إحصائيات الإجازات
    $leaveStats = [
        'pending'  => \App\Models\LeaveRequest::where('status', 'pending')->count(),
        'approved' => \App\Models\LeaveRequest::where('status', 'approved')->count(),
        'rejected' => \App\Models\LeaveRequest::where('status', 'rejected')->count(),
        'total'    => \App\Models\LeaveRequest::count(),
    ];

    // إحصائيات القروض
    $loanStats = [
        'pending'  => \App\Models\LoanRequest::where('status', 'pending')->count(),
        'approved' => \App\Models\LoanRequest::where('status', 'approved')->count(),
        'rejected' => \App\Models\LoanRequest::where('status', 'rejected')->count(),
        'total'    => \App\Models\LoanRequest::count(),
    ];

    $title = "إدارة الطلبات";

    return view('dashbord.attendance', compact('leaveRequests', 'loanRequests', 'leaveStats', 'loanStats', 'title'));
}
    public function showSalariesPage(Request $request)
    {
        $user = Auth::user();
        $date = $request->input('selected_month', now()->format('Y-m'));
        $search = $request->input('search');
        $status = $request->input('status');
        $dept_id = $request->input('department_id');

        // جلب الأقسام (تستخدم في القائمة المنسدلة للمدير)
        $departments = \App\Models\Department::all();

        // --- المنطق الجديد: التفرقة بين المدير والموظف ---

        if ($user->role?->name === 'مدير النظام') {
            // 1. المدير: يرى الجميع مع إمكانية البحث والفلترة
            $employeesQuery = \App\Models\Employee::with(['department', 'salaries' => function ($q) use ($date) {
                $q->where('month', $date);
            }]);

            if ($search) {
                $employeesQuery->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%");
                });
            }
            if ($dept_id) {
                $employeesQuery->where('department_id', $dept_id);
            }
            if ($status) {
                $employeesQuery->whereHas('salaries', function ($q) use ($date, $status) {
                    $q->where('month', $date)->where('status', $status);
                });
            }

            $employees = $employeesQuery->get();

            // إحصائيات عامة للمدير
            $stats = [
                'total_salaries'   => \App\Models\Salary::where('month', $date)->sum('net_salary'),
                'paid_salaries'    => \App\Models\Salary::where('month', $date)->where('status', 'مدفوع')->sum('net_salary'),
                'pending_salaries' => \App\Models\Salary::where('month', $date)->where('status', 'معلق')->sum('net_salary'),
                'average_salary'   => \App\Models\Salary::where('month', $date)->avg('net_salary') ?? 0,
            ];
        } else {
            // 2. الموظف: يجلب سجله هو فقط لهذا الشهر
            $employees = \App\Models\Employee::where('user_id', $user->id)
                ->with(['department', 'salaries' => function ($q) use ($date) {
                    $q->where('month', $date);
                }])->get();

            // إحصائيات الموظف (يرى راتبه الشخصي فقط في البطاقة الأولى)
            $mySalary = $employees->first()?->salaries->first();
            $stats = [
                'total_salaries'   => $mySalary->net_salary ?? 0,
                'paid_salaries'    => ($mySalary && $mySalary->status == 'مدفوع') ? $mySalary->net_salary : 0,
                'pending_salaries' => ($mySalary && $mySalary->status == 'معلق') ? $mySalary->net_salary : 0,
                'average_salary'   => $mySalary->net_salary ?? 0,
            ];
        }

        return view('dashbord.salaries', compact('employees', 'stats', 'date', 'search', 'departments'));
    }
    public function showPerformancePage()
    {
        return view('dashbord.performance');
    }
    public function showReportsPage()
    {
        return view('dashbord.reports');
    }
    // public function showNotificationsPage()
    // {
    //     $stats = [
    //         'unread' => 0,
    //         'important' => 0,
    //         'system' => 0,
    //         'total' => 0,
    //      ];
    //       $notifications = Notification::latest()->get();
    //     return view('dashbord.notifications', compact('stats','notifications'));
    // }

    public function showUsersPage()
    {
        $users = User::with('role')->get();
        return view('dashbord.users', compact('users'));
    }

    public function updateSalary(Request $request)
    {
        // منع أي شخص غير المدير من استخدام هذه الدالة
        if (Auth::user()->role?->name !== 'مدير النظام') {
            return back()->with('error', 'عفواً، لا تملك صلاحية تعديل الرواتب.');
        }

        try {
            if ($request->filled('salary_id')) {
                $salary = Salary::findOrFail($request->salary_id);

                $salary->basic_salary = $request->input('basic_salary') ?? $salary->basic_salary;
                $salary->housing_percentage = $request->input('housing_percentage') ?? 0;
                $salary->transport_percentage = $request->input('transport_percentage') ?? 0;
                $salary->health_percentage = $request->input('health_percentage') ?? 0;
                $salary->tax_percentage = $request->input('tax_percentage') ?? 0;
                $salary->bonuses = $request->input('bonuses') ?? 0;
                $salary->loan_installments = $request->input('loan_installments') ?? 0;
                $salary->penalties = $request->input('penalties') ?? 0;

                $salary->save();
                return back()->with('success', 'تم تحديث الراتب بنجاح.');
            } else {
                // التعديل العام للكل
                $month = $request->input('selected_month', now()->format('Y-m'));
                $salaries = Salary::where('month', $month)->get();

                foreach ($salaries as $s) {
                    if ($request->filled('housing_percentage')) $s->housing_percentage = $request->housing_percentage;
                    if ($request->filled('transport_percentage')) $s->transport_percentage = $request->transport_percentage;
                    if ($request->filled('health_percentage')) $s->health_percentage = $request->health_percentage;
                    if ($request->filled('tax_percentage')) $s->tax_percentage = $request->tax_percentage;
                    if ($request->filled('bonuses')) $s->bonuses = $request->bonuses;
                    if ($request->filled('penalties')) $s->penalties = $request->penalties;
                    $s->save();
                }
                return back()->with('success', 'تم تطبيق التعديلات العامة.');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'خطأ في قاعدة البيانات: ' . $e->getMessage());
        }
    }
    /**
     * دفع راتب موظف واحد
     */
    public function paySalary($id)
    {
        // 1. جلب سجل الراتب مع بيانات الموظف والمدير
        $salary = \App\Models\Salary::findOrFail($id);
        $employee = $salary->employee;
        $admin = \App\Models\User::whereHas('role', function ($q) {
            $q->where('name', 'مدير النظام');
        })->first();

        if ($employee) {
            // أ: إشعار للموظف (كما في الصورة)
            \App\Models\Notification::create([
                'user_id' => $employee->user_id,
                'title'   => 'إيداع راتب 💰',
                'text'    => 'تم إيداع راتب شهر ' . now()->translatedFormat('F') . ' في حسابك. يمكنك مراجعة القسيمة الآن.',
                'type'    => 'success',
                'source'  => 'النظام المالي',
            ]);

            // ب: إشعار للمدير (باسم الموظف)
            if ($admin) {
                \App\Models\Notification::create([
                    'user_id' => $admin->id,
                    'title'   => 'تأكيد صرف راتب ✅',
                    'text'    => 'تم إيداع راتب شهر ' . now()->translatedFormat('F') . ' في حساب الموظف ' . $employee->first_name . ' ' . $employee->last_name,
                    'type'    => 'system',
                    'source'  => 'النظام المالي',
                ]);
            }
        }

        // تحديث حالة الراتب
        $salary->update([
            'status' => 'مدفوع',
            'paid_at' => now()
        ]);

        // تحديث حالة القروض بعد دفع الراتب
        $activeLoans = \App\Models\Loan::where('employee_id', $employee->id)
            ->where('status', 'active')
            ->get();

        foreach ($activeLoans as $loan) {
            $loan->paid_months++;
            $loan->remaining_balance -= $loan->monthly_installment;

            if ($loan->paid_months >= $loan->total_months) {
                $loan->status = 'completed';
            }
            $loan->save();
        }
        return back()->with('success', 'تم تأكيد دفع الراتب بنجاح.');
    }

    /**
     * دفع جميع رواتب الشهر الظاهر
     */
    public function payAllSalaries(Request $request)
    {
        $date = $request->input('selected_month', now()->format('Y-m'));

        // جلب الرواتب التي لم تُدفع بعد لهذا الشهر
        $salariesToPay = \App\Models\Salary::where('month', $date)
            ->where('status', '!=', 'مدفوع')
            ->with('employee')
            ->get();

        if ($salariesToPay->isEmpty()) {
            return back()->with('error', 'لا توجد رواتب مستحقة للدفع لهذا الشهر.');
        }

        foreach ($salariesToPay as $salary) {
            // تحديث السجل
            $salary->update([
                'status' => 'مدفوع',
                'paid_at' => now()
            ]);

            // تحديث حالة القروض بعد دفع الراتب
            $activeLoans = \App\Models\Loan::where('employee_id', $salary->employee_id)
                ->where('status', 'active')
                ->get();

            foreach ($activeLoans as $loan) {
                $loan->paid_months++;
                $loan->remaining_balance -= $loan->monthly_installment;

                if ($loan->paid_months >= $loan->total_months) {
                    $loan->status = 'completed';
                }
                $loan->save();
            }
            // إرسال إشعار لكل موظف
            if ($salary->employee) {
                \App\Models\Notification::create([
                    'user_id' => $salary->employee->user_id,
                    'title'   => 'إيداع راتب 💰',
                    'text'    => 'تم إيداع راتب شهر ' . now()->translatedFormat('F') . ' في حسابك. يمكنك مراجعة القسيمة الآن.',
                    'type'    => 'success',
                    'source'  => 'النظام المالي',
                ]);
            }
        }

        // إرسال إشعار واحد نهائي للمدير
        $admin = \App\Models\User::whereHas('role', function ($q) {
            $q->where('name', 'مدير النظام');
        })->first();

        if ($admin) {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title'   => 'إتمام صرف الرواتب 📑',
                'text'    => 'تم إيداع راتب شهر ' . now()->translatedFormat('F') . ' لجميع الموظفين بنجاح.',
                'type'    => 'success',
                'source'  => 'النظام المالي',
            ]);
        }

        $count = $salariesToPay->count();
        return back()->with('success', "تم دفع رواتب ($count) موظف بنجاح وإرسال الإشعارات.");
    }
    /**
     * توليد رواتب الشهر تلقائياً (لجميع الموظفين النشطين)
     */

    public function generateMonthlySalaries(Request $request)
    {
        // منع أي شخص غير المدير من استخدام هذه الدالة
        if (Auth::user()->role?->name !== 'مدير النظام') {
            return back()->with('error', 'عفواً، لا تملك صلاحية توليد الرواتب.');
        }

        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $selectedMonth = $request->input('selected_month', now()->format('Y-m'));
        $monthString = sprintf('%d-%02d', $year, $month);

        // التحقق من عدم وجود رواتب لهذا الشهر مسبقاً
        $existingCount = Salary::where('month', $monthString)->count();
        if ($existingCount > 0) {
            return redirect()->route('salaries', ['selected_month' => $selectedMonth])->with('error', 'رواتب شهر ' . $monthString . ' موجودة مسبقاً. قم بحذفها أولاً إذا أردت إعادة التوليد.');
        }

        // جلب جميع الموظفين النشطين
        $employees = Employee::where('status', 'نشط')->get();

        if ($employees->isEmpty()) {
            return redirect()->route('salaries', ['selected_month' => $selectedMonth])->with('error', 'لا يوجد موظفين نشطين لتوليد رواتبهم.');
        }

        // إنشاء خدمة الحساب
        $calculationService = new \App\Services\SalaryCalculationService();

        $generatedCount = 0;
        $errors = [];

        foreach ($employees as $employee) {
            try {
                // القيم من جدول الموظف
                $basicSalary = $employee->basic_salary ?? 5000;
                $housingPercentage = $employee->housing_percentage ?? 10;
                $transportPercentage = $employee->transport_percentage ?? 5;

                // ✅ التأمينات الاجتماعية (6% ثابتة)
                $healthPercentage = config('salary.health_percentage', 6);

                // ✅ ضريبة الدخل (نسب تصاعدية حسب القانون اليمني)
                $monthlyTax = $this->calculateIncomeTax($basicSalary);

                // تحويل الضريبة إلى نسبة مئوية للتخزين في tax_percentage
                $taxPercentage = ($basicSalary > 0) ? ($monthlyTax / $basicSalary) * 100 : 0;

                // حساب أيام الغياب الفعلية
                $absenceDays = $calculationService->calculateActualAbsenceDays($employee->id, $year, $month);

                // حساب دقائق التأخير الفعلية
                $lateMinutes = $calculationService->calculateActualLateMinutes($employee->id, $year, $month);

                // حساب الخصومات (الغياب والتأخير)
                $deductions = $calculationService->calculateDeductions($basicSalary, $absenceDays, $lateMinutes);
                // حساب إجمالي أقساط القروض النشطة للموظف
                $startOfMonth = sprintf('%d-%02d-01', $year, $month);

                $totalLoanInstallment = \App\Models\Loan::where('employee_id', $employee->id)
                    ->where('status', 'active')
                    ->where('start_date', '<=', $startOfMonth)
                    ->sum('monthly_installment');
                // إنشاء سجل الراتب
                Salary::create([
                    'employee_id' => $employee->id,
                    'month' => $monthString,
                    'basic_salary' => $basicSalary,
                    'housing_percentage' => $housingPercentage,
                    'transport_percentage' => $transportPercentage,
                    'bonuses' => 0,
                    'health_percentage' => $healthPercentage,      // تأمينات 6%
                    'tax_percentage' => $taxPercentage,            // ضريبة كنسبة مئوية
                    'loan_installments' => $totalLoanInstallment,
                    'penalties' => 0,
                    'absence_days' => $absenceDays,
                    'absence_deduction' => $deductions['absence_deduction'],
                    'late_minutes' => $lateMinutes,
                    'late_deduction' => $deductions['late_deduction'],
                    'status' => 'معلق',
                ]);

                $generatedCount++;
            } catch (\Exception $e) {
                $errors[] = "خطأ في حساب راتب الموظف ID: {$employee->id} - " . $e->getMessage();
            }
        }

        if ($generatedCount > 0) {
            // تسجيل النشاط
            ActivityLog::create([
                'type' => 'activity-icon-success',
                'icon' => '💰',
                'description' => 'تم توليد رواتب شهر ' . $monthString . ' تلقائياً لـ ' . $generatedCount . ' موظف',
                'user_id' => Auth::id()
            ]);

            $message = "تم توليد رواتب شهر {$monthString} بنجاح لـ {$generatedCount} موظف.";

            if (count($errors) > 0) {
                $message .= " ولكن حدثت بعض الأخطاء: " . implode(', ', $errors);
            }

            return redirect()->route('salaries', ['selected_month' => $selectedMonth])->with('success', $message);
        }

        return redirect()->route('salaries', ['selected_month' => $selectedMonth])->with('error', 'فشل توليد الرواتب. ' . implode(', ', $errors));
    }
    /**
     * حساب ضريبة الدخل حسب الشرائح اليمنية مع الإعفاء الشخصي
     *
     * الإعفاء السنوي: 120,000 ريال للموظف نفسه
     * الشرائح:
     * - أول 120,000 ريال (بعد الإعفاء): 0%
     * - من 120,001 إلى 240,000: 10%
     * - أكثر من 240,000: 15%
     *
     * @param float $monthlySalary الراتب الشهري
     * @return float قيمة الضريبة الشهرية
     */
    private function calculateIncomeTax($monthlySalary)
    {
        $annualSalary = $monthlySalary * 12;

        // الإعفاء الشخصي السنوي
        $personalExemption = 120000;

        // الوعاء الخاضع للضريبة بعد خصم الإعفاء
        $taxableIncome = max(0, $annualSalary - $personalExemption);

        // إذا كان الوعاء صفراً
        if ($taxableIncome <= 0) {
            return 0;
        }

        // الشريحة الأولى: 0% على أول 120,000 من الوعاء
        if ($taxableIncome <= 120000) {
            return 0; // معفى
        }

        // الشريحة الثانية: 10% على أول 120,000 بعد الإعفاء
        if ($taxableIncome <= 240000) {
            $annualTax = 120000 * 0.10;
            return round($annualTax / 12, 2);
        }

        // الشريحة الثالثة: 10% على أول 120,000 + 15% على الباقي
        $taxOnSecondBracket = 12000; // 120,000 × 10%
        $remaining = $taxableIncome - 240000;
        $annualTax = $taxOnSecondBracket + ($remaining * 0.15);

        return round($annualTax / 12, 2);
    }
}
