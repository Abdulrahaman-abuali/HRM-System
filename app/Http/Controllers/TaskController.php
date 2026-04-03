<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class TaskController extends Controller
{
    /**
     * عرض صفحة المهام للموظف (مهامي فقط)
     */
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'عذراً، حسابك غير مرتبط ببيانات موظف.');
        }

        $role = $user->role?->name;

        // تحديث: مدير النظام ومدير القسم يستخدمان نفس العرض مع اختلاف البيانات
        if ($role === 'مدير القسم' || $role === 'مدير النظام') {
            if ($role === 'مدير النظام') {
                $tasks = Task::with('employee')->latest()->get();
                $departmentEmployees = Employee::all();
            } else {
                // مدير القسم يرى مهامه الشخصية + مهام جميع الموظفين في قسمه
                $tasks = Task::whereHas('employee', function($query) use ($employee) {
                    $query->where('department_id', $employee->department_id);
                })->latest()->get();

                $departmentEmployees = Employee::where('department_id', $employee->department_id)
                    ->where('id', '!=', $employee->id)
                    ->get();
            }

            return view('dashbord.tasks', compact('tasks', 'departmentEmployees'));
        }

        // الموظف العادي يرى مهامه فقط
        $tasks = Task::where('employee_id', $employee->id)->latest()->get();
        return view('dashbord.tasks', compact('tasks'));
    }

    /**
     * عرض صفحة الإدارة للمدير (جميع المهام + الإحصائيات)
     */
    public function adminIndex()
    {
        $stats = [
            'pending'     => Task::where('status', 'pending')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'completed'   => Task::where('status', 'completed')->count(),
            'total'       => Task::count(),
        ];

        $allTasks = Task::with('employee')->latest()->get();
        $employees = Employee::all();

        return view('dashbord.tasks', [
            'tasks' => $allTasks,
            'stats' => $stats,
            'employees' => $employees
        ]);
    }

    /**
     * حفظ مهمة جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|max:255',
            'employee_id' => 'required|exists:employees,id',
            'due_date'    => 'required|date',
        ]);

        $user = Auth::user();
        $role = $user->role?->name;
        $adminEmployee = $user->employee;

        if ($role === 'مدير القسم') {
            $targetEmployee = Employee::findOrFail($request->employee_id);
            if ($adminEmployee->department_id !== $targetEmployee->department_id) {
                return back()->with('error', '❌ لا يمكنك إسناد مهام لموظفين خارج قسمك!');
            }
        }

        // إنشاء المهمة مع التأكد من تخزين added_by
        $task = Task::create([
            'title'       => $request->title,
            'employee_id' => $request->employee_id,
            'due_date'    => $request->due_date,
            'status'      => 'pending',
            'added_by'    => $user->id,
        ]);

        $employee = Employee::find($request->employee_id);
        if ($employee && $employee->user) {
            Notification::create([
                'user_id' => $employee->user->id,
                'title'   => 'مهمة جديدة 📝',
                'text'    => 'تم إسناد مهمة جديدة لك بعنوان: ' . $task->title,
                'type'    => 'info',
                'source'  => 'إدارة المهام',
            ]);
        }

        return back()->with('success', 'تم إضافة المهمة وإسنادها بنجاح 🚀');
    }

    /**
     * تحديث حالة المهمة (تصحيح لدعم الرفض والـ 404)
     */
    public function updateStatus(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $employee = $task->employee;

        // تحديث الـ Validation ليشمل الحالة "rejected"
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,rejected',
            'rejection_reason' => 'required_if:status,rejected|nullable|string'
        ]);

        // جلب الشخص الذي أرسل المهمة لإشعاره بالتحديث
        $sender = User::find($task->added_by);

        if ($sender && $employee) {
            $statusMessages = [
                'in_progress' => ['title' => 'بدء تنفيذ مهمة 🏗️', 'text' => 'بدأ العمل على: ', 'type' => 'info'],
                'completed'   => ['title' => 'اكتمال مهمة ✅', 'text' => 'أتم بنجاح: ', 'type' => 'success'],
                'rejected'    => ['title' => 'رفض مهمة ❌', 'text' => 'رفض المهمة: ', 'type' => 'warning'],
            ];

            if (isset($statusMessages[$request->status]) && $task->status !== $request->status) {
                Notification::create([
                    'user_id' => $sender->id,
                    'title'   => $statusMessages[$request->status]['title'],
                    'text'    => 'الموظف ' . $employee->first_name . ' ' . $statusMessages[$request->status]['text'] . $task->title,
                    'type'    => $statusMessages[$request->status]['type'],
                    'source'  => 'إدارة المهام',
                ]);
            }
        }

        // تحديث الحالة والبيانات الإضافية
        $task->status = $request->status;
        if ($request->status == 'completed') {
            $task->completed_at = Carbon::now();
            $task->rejection_reason = null;
        } elseif ($request->status == 'rejected') {
            $task->rejection_reason = $request->rejection_reason;
            $task->completed_at = null;
        } else {
            $task->completed_at = null;
        }

        $task->save();

        return back()->with('success', 'تم تحديث حالة المهمة بنجاح ✅');
    }

    /**
     * حذف المهمة (مع حماية صلاحيات مدير القسم)
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $user = Auth::user();

        // حماية: مدير النظام يحذف الكل، مدير القسم يحذف مهامه فقط
        if ($user->role?->name === 'مدير النظام' || $task->added_by == $user->id) {
            $task->delete();
            return back()->with('success', 'تم حذف المهمة بنجاح 🗑️');
        }

        return back()->with('error', '❌ غير مصرح لك بحذف هذه المهمة لأنها من الإدارة العليا.');
    }
}
