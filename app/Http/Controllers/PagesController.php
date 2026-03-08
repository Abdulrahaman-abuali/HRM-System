<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Routing\Controller;
use App\Models\Salary;

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

        // تحقق من حالة النشاط (إذا كان لديك حقل is_active)
        if (isset($user->is_active) && !$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'الحساب غير مفعل.']);
        }

        // --- منطق تسجيل الحضور التلقائي عند الدخول ---
        $employee = $user->employee;
        if ($employee) {
            $today = now()->toDateString();

            // جلب سجل اليوم أو إنشاؤه إذا لم يوجد
            $record = AttendanceRecord::firstOrCreate([
                'employee_id' => $employee->id,
                'date'        => $today,
            ]);

            // تحديث وقت الحضور فقط إذا كانت المرة الأولى للدخول اليوم
            if (!$record->check_in) {
                $record->update([
                    'check_in' => now()->format('H:i:s')
                ]);
            }
        }

        // التوجيه بناءً على الدور (Role)
        $roleName = $user->role?->name;

        if ($roleName === 'مدير النظام') {
            return redirect()->route('dashbord');
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
        return view("dashbord.index");
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
    $date = $request->input('date', now()->toDateString());

    // 1. إذا كان المستخدم موظف (يرى أرشيفه الشخصي فقط)
    if ($user->role?->name === 'موظف' && $employee) {
        $records = AttendanceRecord::where('employee_id', $employee->id)
                    ->orderByDesc('date')
                    ->paginate(20);

        // نرسل فقط $records للموظف لضمان عدم ظهور جداول المدير
        return view('dashbord.leave', compact('records', 'date'));
    }

    // 2. إذا كان المستخدم مديراً (يرى حالة اليوم للكل + الأرشيف العام)
    $employees = \App\Models\Employee::with(['department', 'attendanceRecords' => function($q) use ($date) {
        $q->where('date', $date);
    }])->get();

    // جلب الأرشيف العام لكل الموظفين بأسماءهم
    $allHistory = AttendanceRecord::with('employee')
                    ->orderByDesc('date')
                    ->paginate(15);

    $stats = [
        'total'   => $employees->count(),
        'present' => $employees->filter(fn($e) => $e->attendanceRecords->isNotEmpty())->count(),
        'absent'  => $employees->filter(fn($e) => $e->attendanceRecords->isEmpty())->count(),
    ];

    return view('dashbord.leave', compact('employees', 'allHistory', 'date', 'stats'));
}
    /**
     * صفحات النظام الأخرى (الإدارة)
     */
    public function showEmployeesPage() { return view('dashbord.2'); }
   public function showAttendancePage()
{
    // جلب البيانات مع العلاقات (Eloquent) لتعمل الأزرار
    $data = \App\Models\LeaveRequest::with(['employee', 'leaveType'])->latest()->get();

    // إرسال الإحصائيات المطلوبة للكروت العلوية في صفحة الإدارة
    $stats = [
        'pending'  => \App\Models\LeaveRequest::where('status', 'pending')->count(),
        'approved' => \App\Models\LeaveRequest::where('status', 'approved')->count(),
        'rejected' => \App\Models\LeaveRequest::where('status', 'rejected')->count(),
        'total'    => \App\Models\LeaveRequest::count(),
    ];

    $title = "إدارة طلبات الإجازات";

    // تأكد من توجيه العرض لملف الإدارة الأصلي وليس التقرير
    return view('dashbord.attendance', compact('data', 'stats', 'title'));
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
        $employeesQuery = \App\Models\Employee::with(['department', 'salaries' => function($q) use ($date) {
            $q->where('month', $date);
        }]);

        if ($search) {
            $employeesQuery->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%");
            });
        }
        if ($dept_id) { $employeesQuery->where('department_id', $dept_id); }
        if ($status) {
            $employeesQuery->whereHas('salaries', function($q) use ($date, $status) {
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
            ->with(['department', 'salaries' => function($q) use ($date) {
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
    public function showPerformancePage(){ return view('dashbord.performance'); }
    public function showReportsPage()    { return view('dashbord.reports'); }
    public function showNotificationsPage(){ return view('dashbord.notifications'); }

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
        $salary = \App\Models\Salary::findOrFail($id);
        $salary->update([
            'status' => 'مدفوع',
            'paid_at' => now()
        ]);

        return back()->with('success', 'تم تأكيد دفع الراتب بنجاح.');
    }

    /**
     * دفع جميع رواتب الشهر الظاهر
     */
    public function payAllSalaries(Request $request)
    {
        $date = $request->input('selected_month', now()->format('Y-m'));

        $updated = \App\Models\Salary::where('month', $date)
            ->where('status', '!=', 'مدفوع')
            ->update([
                'status' => 'مدفوع',
                'paid_at' => now()
            ]);

        return back()->with('success', "تم دفع رواتب ($updated) موظف بنجاح.");
    }
}
