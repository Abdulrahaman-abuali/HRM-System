<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobTitle;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\Salary;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * عرض قائمة الموظفين مع إمكانية البحث
     */
    public function index(Request $request)
    {
        $query = Employee::with(['department', 'jobTitle', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('user', function($u) use ($search) {
                      $u->where('email', 'like', "%{$search}%");
                  });
            });
        }

        $employees = $query->latest()->get();
        return view('employees.index', compact('employees'));
    }

    /**
     * عرض نموذج إضافة موظف جديد
     */
    public function create()
    {
        $departments = Department::all();
        $job_titles = JobTitle::all();
        $roles = Role::all();
       $managers = Employee::whereHas('user', function($query) {
        $query->whereHas('role', function($r) {
            $r->where('name', 'like', '%مدير%'); // سيبحث عن أي دور يحتوي كلمة مدير
        });
    })->get();

        return view('employees.create', compact('departments', 'job_titles', 'roles', 'managers'));
    }

    /**
     * حفظ الموظف وحساب المستخدم وسجل الراتب في عملية واحدة
     */
    public function store(Request $request)
{
    // 1. التحقق من البيانات الشاملة (أضفنا الحقول الجديدة هنا)
    $validated = $request->validate([
        'first_name'      => 'required|string|max:255',
        'last_name'       => 'required|string|max:255',
        'email'           => 'required|email|unique:users,email',
        'phone'           => 'required|string',
        'gender'          => 'required',
        'birth_date'      => 'required|date',
        'address'         => 'required|string|max:500', // العنوان
        'employment_type' => 'required|string',      // نوع التوظيف
        'manager_id'      => 'nullable|exists:employees,id', // المدير المباشر
        'department_id'   => 'required|exists:departments,id',
        'job_title_id'    => 'required|exists:job_titles,id',
        'hire_date'       => 'required|date',
        'password'        => 'required|min:8',
        'role_id'         => 'required|exists:roles,id',
        'basic_salary'    => 'required|numeric|min:0',
    ]);

    try {
        return DB::transaction(function () use ($validated, $request) {

            // 2. إنشاء حساب المستخدم (User)
            $user = User::create([
                'name'      => $validated['first_name'] . ' ' . $validated['last_name'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'role_id'   => $validated['role_id'],
                'is_active' => 1,
            ]);

            // 3. إنشاء ملف الموظف (Employee) - أضفنا الحقول الجديدة هنا
            $employee = Employee::create([
                'user_id'         => $user->id,
                'first_name'      => $validated['first_name'],
                'last_name'       => $validated['last_name'],
                'email'           => $validated['email'],
                'phone'           => $validated['phone'],
                'age'             => $request->age,
                'gender'          => $validated['gender'],
                'birth_date'      => $validated['birth_date'],
                'address'         => $validated['address'],         // الحقل الجديد
                'employment_type' => $validated['employment_type'], // الحقل الجديد
                'manager_id'      => $validated['manager_id'],      // الحقل الجديد
                'department_id'   => $validated['department_id'],
                'job_title_id'    => $validated['job_title_id'],
                'hire_date'       => $validated['hire_date'],
                'status'          => 'نشط',
            ]);

            // 4. إنشاء سجل الراتب الأول بالقيم الافتراضية
            Salary::create([
                'employee_id'          => $employee->id,
                'month'                => now()->format('Y-m'),
                'basic_salary'         => $validated['basic_salary'],
                'housing_percentage'   => 0,
                'transport_percentage' => 0,
                'health_percentage'    => 0,
                'tax_percentage'       => 0,
                'bonuses'              => 0,
                'loan_installments'    => 0,
                'penalties'            => 0,
                'status'               => 'معلق',
            ]);

            return redirect()->route('employees.index')->with('success', 'تمت إضافة الموظف وإنشاء سجل الراتب بنجاح');
        });
    } catch (\Exception $e) {
        return back()->withInput()->with('error', 'فشل الحفظ: ' . $e->getMessage());
    }
}
    /**
     * تحديث بيانات الموظف (بدون الراتب - التعديل المالي في صفحة الرواتب)
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $user = $employee->user;

        $validated = $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'phone'         => 'required|string',
            'gender'        => 'required',
            'birth_date'    => 'required|date',
            'address'         => 'required|string|max:500',         // الحقل الجديد
            'employment_type' => 'required|string',                // الحقل الجديد
            'manager_id'      => 'nullable|exists:employees,id',    // الحقل الجديد
            'department_id' => 'required|exists:departments,id',
            'job_title_id'  => 'required|exists:job_titles,id',
            'hire_date'     => 'required|date',
        ]);

        try {
            return DB::transaction(function () use ($validated, $request, $employee, $user) {
                // تحديث اسم المستخدم المرتبط
                if ($user) {
                    $user->update([
                        'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    ]);
                }

                // تحديث بيانات الموظف الشخصية والوظيفية
                $employee->update([
                    'first_name'    => $validated['first_name'],
                    'last_name'     => $validated['last_name'],
                    'phone'         => $validated['phone'],
                    'gender'        => $validated['gender'],
                    'birth_date'    => $validated['birth_date'],
                    'age'           => $request->age,
                    'address'         => $validated['address'],
                    'employment_type' => $validated['employment_type'],
                    'manager_id'      => $validated['manager_id'],
                    'department_id' => $validated['department_id'],
                    'job_title_id'  => $validated['job_title_id'],
                    'hire_date'     => $validated['hire_date'],
                ]);

                return redirect()->route('employees.index')->with('success', 'تم تحديث بيانات الموظف بنجاح');
            });
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    // ... (بقية الدوال: edit, show, destroy تبقى كما هي مع التأكد من حذف user عند حذف employee)
    public function destroy(Employee $employee)
    {
        DB::transaction(function () use ($employee) {
            if ($employee->user) {
                $employee->user->delete();
            }
            $employee->delete();
        });

        return redirect()->route('employees.index')->with('success', 'تم حذف الموظف وحسابه بنجاح');
    }
    /**
 * عرض صفحة تعديل بيانات الموظف
 */
    public function edit($id)
{
    // جلب الموظف الحالي
    $employee = Employee::findOrFail($id);

    // جلب الأقسام والمسميات
    $departments = Department::all();
    $job_titles = JobTitle::all();

    // السطر الأهم: جلب الموظفين بشرط أن المعرف لا يساوي معرف الموظف الحالي
   $managers = Employee::whereHas('user', function($query) {
        $query->whereHas('role', function($r) {
            $r->where('name', 'like', '%مدير%'); // سيبحث عن أي دور يحتوي كلمة مدير
        });
    })->get();

    return view('employees.edit', compact('employee', 'departments', 'job_titles', 'managers'));
}
    public function show($id)
{
    // جلب بيانات الموظف مع القسم التابع له
    $employee = \App\Models\Employee::with('department')->findOrFail($id);

    // توجيه المستخدم لصفحة العرض (تأكد من وجود هذا الملف)
    return view('employees.show', compact('employee'));
}
}
