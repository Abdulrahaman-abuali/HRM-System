<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Employee;
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
        $employee = Auth::user()->employee;

        if (!$employee) {
            return back()->with('error', 'عذراً، حسابك غير مرتبط ببيانات موظف.');
        }

        // جلب المهام الخاصة بهذا الموظف فقط
        $tasks = Task::where('employee_id', $employee->id)
            ->latest()
            ->get();

        // نرسل البيانات لصفحة الموظف (يمكنك استخدام نفس الملف أو ملف منفصل)
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

        // جلب كل المهام مع بيانات الموظفين للمدير
        $allTasks = Task::with('employee')->latest()->get();

        // جلب الموظفين لغرض الإسناد في المودال
        $employees = Employee::all();

        return view('dashbord.tasks', [
            'tasks' => $allTasks,
            'stats' => $stats,
            'employees' => $employees
        ]);
    }

    /**
     * حفظ مهمة جديدة (من قبل المدير)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|max:255',
            'employee_id' => 'required|exists:employees,id',
            'due_date'    => 'required|date',
        ]);


        $task = Task::create([
            'title'       => $request->title,
            'employee_id' => $request->employee_id,
            'due_date'    => $request->due_date,
            'status'      => 'pending',
        ]);

        $employee = Employee::find($request->employee_id);

        Notification::create([
            'user_id' => $employee ? $employee->user->id : null, // يرسل للموظف
            'title'   => 'مهمة جديدة 📝',
            'text'    => 'لديك مهمة جديدة بعنوان: ' . $task->title,
            'type'    => 'info',
            'source'  => 'إدارة المهام',
        ]);

        return back()->with('success', 'تم إضافة المهمة وإسنادها بنجاح 🚀');
    }

    /**
     * تحديث حالة المهمة (من قبل الموظف أو المدير)
     */
    public function updateStatus(Request $request, $id)
    {

    $task = Task::findOrFail($id);
    $employee = $task->employee;

    $request->validate([
        'status' => 'required|in:pending,in_progress,completed'
    ]);

    // جلب مدير النظام ديناميكياً بدلاً من وضع الرقم 1
    $admin = \App\Models\User::whereHas('role', function($q){
        $q->where('name', 'مدير النظام');
    })->first();

    if ($admin && $employee) {
        // حالة 1: الموظف بدأ العمل (in_progress)
        if ($request->status === 'in_progress' && $task->status !== 'in_progress') {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title'   => 'بدء تنفيذ مهمة 🏗️',
                'text'    => 'بدأ الموظف ' . $employee->first_name . ' بالعمل على مهمة: ' . $task->title,
                'type'    => 'info',
                'source'  => 'إدارة المهام',
            ]);
        }

        // حالة 2: الموظف أنهى العمل (completed)
        if ($request->status === 'completed' && $task->status !== 'completed') {
            \App\Models\Notification::create([
                'user_id' => $admin->id,
                'title'   => 'اكتمال مهمة ✅',
                'text'    => 'أتم الموظف ' . $employee->first_name . ' المهمة المسندة إليه: ' . $task->title,
                'type'    => 'success',
                'source'  => 'إدارة المهام',
            ]);
        }
    }

    // تحديث الحالة وتاريخ الانتهاء
    $task->status = $request->status;
    if ($request->status == 'completed') {
        $task->completed_at = \Carbon\Carbon::now();
    } else {
        $task->completed_at = null;
    }

    $task->save();

    return back()->with('success', 'تم تحديث حالة المهمة بنجاح ✅');
}
    /**
     * حذف المهمة (من قبل المدير)
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return back()->with('success', 'تم حذف المهمة بنجاح 🗑️');
    }
}
