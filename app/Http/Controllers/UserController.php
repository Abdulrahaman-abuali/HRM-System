<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // عرض قائمة المستخدمين
    public function index(Request $request)
    {
        // جلب المستخدمين مع علاقاتهم (الدور والموظف)
        $query = User::with(['role', 'employee']);

        // 1. البحث بالاسم أو البريد
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // 2. فلترة بالدور (باستخدام اسم الدور)
        if ($request->filled('role')) {
            $query->whereHas('role', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // 3. فلترة بالحالة (البحث في جدول الموظفين)
        if ($request->filled('status')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        $users = $query->latest()->get();
        return view('users.index', compact('users'));
    }

    // عرض نموذج إنشاء مستخدم جديد
    public function create()
    {
        $roles = \App\Models\Role::all();
        $departments = \App\Models\Department::all();
        $job_titles = \App\Models\JobTitle::all();

        return view('users.create_user', compact('roles', 'departments', 'job_titles'));
    }

    // حفظ مستخدم جديد
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string',
            'age' => 'required|integer',
            'gender' => 'required|string',
            'birth_date' => 'required|date',
            'hire_date' => 'required|date',
            'status' => 'required|in:نشط,غير نشط', // التحقق من القيم المسموحة
            'department_id' => 'nullable|exists:departments,id',
            'job_title_id' => 'nullable|exists:job_titles,id',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        // إنشاء المستخدم مع مزامنة status و is_active
        $user = User::create([
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $validated['role_id'],
            'status' => $validated['status'], // ✅ حفظ النص
            'is_active' => ($validated['status'] == 'نشط') ? 1 : 0, // ✅ مزامنة is_active
        ]);

        // إنشاء الموظف وربطه بالمستخدم
        Employee::create([
            'user_id' => $user->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'department_id' => $validated['department_id'],
            'job_title_id' => $validated['job_title_id'],
            'hire_date' => $validated['hire_date'],
            'status' => $validated['status'], // ✅ نفس النص
        ]);

        return redirect()->route('users.index')
            ->with('success', 'تم إنشاء المستخدم وربطه بملف موظف بنجاح');
    }

    // عرض نموذج تعديل مستخدم
    public function edit(\App\Models\User $user)
    {
        $roles = \App\Models\Role::all();
        return view('users.edit_user', compact('user', 'roles'));
    }

    /**
     * تحديث بيانات المستخدم والصلاحيات مع مزامنة كاملة
     */
    public function update(Request $request, User $user)
    {
        // التحقق من صحة البيانات
        $data = $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:نشط,غير نشط,مغلق', // التحقق من القيم المسموحة
        ]);

        // تجهيز مصفوفة التحديث للمستخدم
        $updateData = [
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'status' => $data['status'], // حفظ النص في users.status
            'is_active' => ($data['status'] == 'نشط') ? 1 : 0, // ✅ مزامنة is_active
        ];

        // تحديث كلمة المرور إذا تم إدخالها
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($data['password']);
        }

        // تحديث المستخدم
        $user->update($updateData);

        // تحديث بيانات الموظف المرتبط (مزامنة)
        $employee = \App\Models\Employee::where('user_id', $user->id)->first();
        if ($employee) {
            $employee->update([
                'email' => $data['email'],
                'status' => $data['status'], // ✅ نص: "نشط" أو "غير نشط"
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'تم تعديل بيانات الدخول والصلاحيات بنجاح');
    }

    // حذف المستخدم
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'تم حذف حساب الدخول بنجاح (سجل الموظف ما زال موجوداً)');
    }
}
