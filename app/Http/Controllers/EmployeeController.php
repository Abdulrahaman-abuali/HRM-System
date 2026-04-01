<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobTitle;
use App\Models\Employee;
use App\Models\User;
use App\Models\Role;
use App\Models\Salary;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * عرض قائمة الموظفين مع البحث
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        // 1. بدء الاستعلام مع العلاقات المطلوبة
        $query = Employee::with(['department', 'jobTitle', 'user']);

        // 2. 🛡️ حماية البيانات: إذا كان المستخدم مدير قسم، يرى موظفي قسمه فقط
        if ($user->role?->name === 'مدير القسم') {
            // تأكد أن حساب المدير مرتبط بموظف وله قسم
            if ($user->employee && $user->employee->department_id) {
                $query->where('department_id', $user->employee->department_id);
            } else {
                // إذا لم يجد قسماً للمدير، نعيد قائمة فارغة لتجنب الأخطاء
                $employees = collect([]);
                return view('employees.index', compact('employees'))
                    ->with('error', 'حسابك الإداري غير مرتبط بقسم محدد.');
            }
        }

        // 3. منطق البحث (يعمل داخل نطاق القسم إذا كان مديراً للقسم)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($u) use ($search) {
                        $u->where('email', 'like', "%{$search}%");
                    });
            });
        }

        // 4. ✅ السطر الناقص: تنفيذ الاستعلام وإرساله للواجهة
        $employees = $query->latest()->paginate(15);

        return view('employees.index', compact('employees'));
    }
    /**
     * عرض نموذج إضافة موظف جديد
     */
    public function create()
    {
        // 🚫 منع مدير القسم من الوصول لصفحة الإضافة
        if (Auth::user()->role?->name === 'مدير القسم') {
            return redirect()->route('employees.index')->with('error', 'عذراً، مدير النظام فقط هو المخول بإضافة موظفين جدد.');
        }

        $departments = Department::all();
        $job_titles = JobTitle::all();
        $roles = Role::all();

        // جلب المديرين (من لديهم كلمة مدير في مسمى وظيفتهم)
        $managers = Employee::whereHas('jobTitle', function ($q) {
            $q->where('name', 'like', '%مدير%');
        })->get();

        return view('employees.create', compact('departments', 'job_titles', 'roles', 'managers'));
    }

    /**
     * حفظ الموظف الجديد
     */
    public function store(Request $request)
    {
        // 1. التحقق من البيانات (الآن الجميع يحتاج إيميل وكلمة مرور وصلاحية)
        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'profile_image'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'email'           => 'required|email|unique:users,email',
            'phone'           => 'required|string',
            'gender'          => 'required',
            'birth_date'      => 'required|date',
            'address'         => 'required|string|max:500',
            'employment_type' => 'required|string',
            'manager_id'      => 'nullable|exists:employees,id',
            'department_id'   => 'required|exists:departments,id',
            'job_title_id'    => 'required|exists:job_titles,id',
            'basic_salary'    => 'required|numeric|min:0',
            'password'        => 'required|min:8', // أصبحت إجبارية للجميع بناءً على قرارك الأخير
            'role_id'         => 'required|exists:roles,id', // أصبحت إجبارية للجميع
            'contract_start_date' => $request->employment_type === 'contract' ? 'required|date' : 'nullable',
            'contract_end_date'   => $request->employment_type === 'contract' ? 'required|date|after:contract_start_date' : 'nullable',
        ]);

        // 2. فحص وجود مدير للقسم (لمنع تكرار المديرين في القسم الواحد)
        $jobTitle = \App\Models\JobTitle::find($request->job_title_id);
        if ($jobTitle && str_contains($jobTitle->name, 'مدير')) {
            $exists = \App\Models\Employee::where('department_id', $request->department_id)
                ->whereHas('jobTitle', function ($q) {
                    $q->where('name', 'like', '%مدير%');
                })
                ->exists();

            if ($exists) {
                return back()->withInput()->with('error', 'عذراً، هذا القسم لديه مدير بالفعل!');
            }
        }

        try {
            return DB::transaction(function () use ($validated, $request) {

                // 3. إنشاء حساب المستخدم (User) للجميع (موظف أو متعاقد)
                // 3. إنشاء حساب المستخدم (User) للجميع (موظف أو متعاقد)
                $user = User::create([
                    'name'      => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email'     => $validated['email'],
                    'password'  => Hash::make($validated['password']),
                    'role_id'   => $validated['role_id'],
                    'is_active' => 1,
                    'must_change_password' => true, // ✅ إضافة هذا السطر
                ]);

                // 4. إنشاء ملف الموظف وربطه بالـ UserID
                $employee = Employee::create([
                    'user_id'         => $user->id, // إلزامي الآن ولن يكون NULL
                    'first_name'      => $validated['first_name'],
                    'last_name'       => $validated['last_name'],
                    'email'           => $validated['email'],
                    'phone'           => $validated['phone'],
                    'age'             => $request->age,
                    'gender'          => $validated['gender'],
                    'birth_date'      => $validated['birth_date'],
                    'address'         => $validated['address'],
                    'employment_type' => $validated['employment_type'],
                    'manager_id'      => $validated['manager_id'],
                    'department_id'   => $validated['department_id'],
                    'job_title_id'    => $validated['job_title_id'],
                    'contract_start_date' => $request->employment_type === 'contract' ? 'required|date' : 'nullable',
                    'contract_end_date'   => $request->employment_type === 'contract' ? 'required|date|after:contract_start_date' : 'nullable',
                    'hire_date'       => now()->format('Y-m-d'), // تاريخ اليوم تلقائياً
                    'status'          => 'نشط',
                    'basic_salary'    => $validated['basic_salary'],
                    'housing_percentage' => $request->input('housing_percentage', 0),
                    'transport_percentage' => $request->input('transport_percentage', 0),
                ]);

                // 5. معالجة وحفظ الصورة الشخصية (تسمية بالـ ID)
                if ($request->hasFile('profile_image')) {
                    $image = $request->file('profile_image');
                    $fileName = $employee->id . '.' . $image->getClientOriginalExtension();
                    $destinationPath = storage_path('app/employee_faces');

                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }

                    $image->move($destinationPath, $fileName);
                    $employee->update(['profile_image' => $fileName]);
                }

                // 6. إنشاء سجل الراتب الأول (معلق)
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

                // 7. تسجيل العملية في سجل النشاطات
                ActivityLog::create([
                    'type' => 'activity-icon-success',
                    'icon' => '✓',
                    'description' => 'تمت إضافة موظف جديد: <span class="activity-strong">' . $employee->first_name . ' ' . $employee->last_name . '</span>',
                    'user_id' => Auth::id()
                ]);

                return redirect()->route('employees.index')->with('success', 'تم حفظ الموظف وإنشاء حسابه بنجاح');
            });
        } catch (\Exception $e) {
            // في حال حدوث أي خطأ، نعود مع رسالة توضيحية
            return back()->withInput()->with('error', 'فشل الحفظ: ' . $e->getMessage());
        }
    }
    /**
     * عرض صفحة تعديل الموظف
     */
    public function edit($id)
    {
        // 1. جلب الموظف مع علاقاته (القسم والمسمى الوظيفي واليوزر)
        $employee = Employee::with(['department', 'jobTitle', 'user', 'salary'])->findOrFail($id);

        // 2. جلب البيانات الأساسية للقوائم المنسدلة
        $departments = Department::all();
        $job_titles = JobTitle::all();
        $roles = Role::all();

        // 3. جلب المديرين المتاحين (أي موظف مسماه الوظيفي يحتوي "مدير" وليس الموظف نفسه)
        $managers = Employee::where('id', '!=', $id)
            ->whereHas('jobTitle', function ($q) {
                $q->where('name', 'like', '%مدير%');
            })->get();

        // 4. التوجه لصفحة التعديل
        return view('employees.edit', compact('employee', 'departments', 'job_titles', 'managers', 'roles'));
    }
    /**
     * تحديث بيانات الموظف
     */
    public function update(Request $request, $id)
    {
        $employee = Employee::with(['salary', 'user'])->findOrFail($id);

        // حفظ البيانات القديمة للمقارنة
        $oldDepartmentId = $employee->department_id;
        $oldRoleId = $employee->user->role_id;
        $oldJobTitleId = $employee->job_title_id;

        $validated = $request->validate([
            'first_name'      => 'required|string|max:255',
            'last_name'       => 'required|string|max:255',
            'phone'           => 'required|string',
            'gender'          => 'required',
            'birth_date'      => 'required|date',
            'address'         => 'required|string|max:500',
            'employment_type' => 'required|string',
            'manager_id'      => 'nullable|exists:employees,id',
            'department_id'   => 'required|exists:departments,id',
            'job_title_id'    => 'required|exists:job_titles,id',
            'basic_salary'    => 'required|numeric|min:0',
            'profile_image'   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'contract_start_date' => $request->employment_type === 'contract' ? 'required|date' : 'nullable',
            'contract_end_date'   => $request->employment_type === 'contract' ? 'required|date|after:contract_start_date' : 'nullable',
            'role_id'         => 'required|exists:roles,id',
            // ✅ أضف هذين الحقلين للتحقق
            'housing_percentage' => 'nullable|numeric|min:0|max:100',
            'transport_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::transaction(function () use ($validated, $request, $employee, $oldDepartmentId, $oldRoleId, $oldJobTitleId) {

                // 1. تحديث بيانات الموظف الأساسية
                $employee->update([
                    'first_name'      => $validated['first_name'],
                    'last_name'       => $validated['last_name'],
                    'phone'           => $validated['phone'],
                    'age'             => $request->age,
                    'gender'          => $validated['gender'],
                    'birth_date'      => $validated['birth_date'],
                    'address'         => $validated['address'],
                    'employment_type' => $validated['employment_type'],
                    'manager_id'      => $validated['manager_id'],
                    'department_id'   => $validated['department_id'],
                    'job_title_id'    => $validated['job_title_id'],
                    'contract_start_date' => $request->employment_type == 'contract' ? $validated['contract_start_date'] : null,
                    'contract_end_date'   => $request->employment_type == 'contract' ? $validated['contract_end_date'] : null,
                    // ✅ أضف هذه الأسطر
                    'basic_salary'    => $validated['basic_salary'],
                    'housing_percentage' => $request->input('housing_percentage', $employee->housing_percentage ?? 10),
                    'transport_percentage' => $request->input('transport_percentage', $employee->transport_percentage ?? 5),
                ]);

                // 2. معالجة الصورة
                if ($request->hasFile('profile_image')) {
                    if ($employee->profile_image) {
                        $oldPath = storage_path('app/employee_faces/' . $employee->profile_image);
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $image = $request->file('profile_image');
                    $fileName = $employee->id . '.' . $image->getClientOriginalExtension();
                    $image->move(storage_path('app/employee_faces'), $fileName);
                    $employee->update(['profile_image' => $fileName]);
                }

                // 3. تحديث الراتب (آخر راتب للموظف)
                $lastSalary = Salary::where('employee_id', $employee->id)->latest()->first();
                if ($lastSalary) {
                    $lastSalary->update(['basic_salary' => $validated['basic_salary']]);
                } else {
                    \App\Models\Salary::create([
                        'employee_id'  => $employee->id,
                        'month'        => now()->format('Y-m'),
                        'basic_salary' => $validated['basic_salary'],
                        'status'       => 'معلق',
                    ]);
                }

                // 4. تحديث حساب المستخدم والصلاحية
                if ($employee->user) {
                    $employee->user->update([
                        'name'    => $validated['first_name'] . ' ' . $validated['last_name'],
                        'role_id' => $validated['role_id'],
                    ]);
                }

                // 5. التحقق من المسمى الوظيفي الجديد
                $newJobTitle = \App\Models\JobTitle::find($validated['job_title_id']);
                $isManagerNow = $newJobTitle && str_contains($newJobTitle->name, 'مدير');
                $wasManagerBefore = $oldJobTitleId && \App\Models\JobTitle::find($oldJobTitleId)?->name ?
                    str_contains(\App\Models\JobTitle::find($oldJobTitleId)->name, 'مدير') : false;

                // 6. منع وجود مديرين في نفس القسم
                if ($isManagerNow) {
                    $existingManager = \App\Models\Employee::where('department_id', $validated['department_id'])
                        ->where('id', '!=', $employee->id)
                        ->whereHas('jobTitle', function ($q) {
                            $q->where('name', 'like', '%مدير%');
                        })
                        ->exists();

                    if ($existingManager) {
                        throw new \Exception('هذا القسم لديه مدير بالفعل!');
                    }
                }

                // 7. الحالة 1: تمت إزالة صلاحية المدير (كان مديراً والآن ليس مديراً)
                if ($wasManagerBefore && !$isManagerNow) {
                    \App\Models\Employee::where('manager_id', $employee->id)
                        ->update(['manager_id' => null]);
                }

                // 8. الحالة 2: أصبح مديراً جديداً (لم يكن مديراً والآن أصبح مديراً)
                if (!$wasManagerBefore && $isManagerNow) {
                    \App\Models\Employee::where('department_id', $validated['department_id'])
                        ->where('id', '!=', $employee->id)
                        ->update(['manager_id' => $employee->id]);
                }

                // 9. الحالة 3: تغيير القسم لمدير قائم
                if ($wasManagerBefore && $isManagerNow && $oldDepartmentId != $validated['department_id']) {
                    \App\Models\Employee::where('manager_id', $employee->id)
                        ->where('department_id', $oldDepartmentId)
                        ->update(['manager_id' => null]);

                    \App\Models\Employee::where('department_id', $validated['department_id'])
                        ->where('id', '!=', $employee->id)
                        ->update(['manager_id' => $employee->id]);
                }
            });

            return redirect()->route('employees.index')->with('success', 'تم تحديث بيانات الموظف بنجاح');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'فشل التحديث: ' . $e->getMessage());
        }
    }

    /**
     * عرض تفاصيل الموظف
     */
    public function show($id)
    {
        $employee = Employee::with(['department', 'jobTitle', 'user', 'manager'])->findOrFail($id);
        return view('employees.show', compact('employee'));
    }

    /**
     * حذف الموظف
     */
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
     * دالة AJAX لجلب مديري القسم
     */
    public function getManagers($deptId)
    {
        // جلب الموظفين في هذا القسم الذين يملكون دور (مدير القسم) من خلال علاقة المستخدم
        $managers = Employee::where('department_id', $deptId)
            ->whereHas('user.role', function ($query) {
                $query->where('name', 'مدير القسم');
            })
            ->get();

        return response()->json($managers);
    }
}
