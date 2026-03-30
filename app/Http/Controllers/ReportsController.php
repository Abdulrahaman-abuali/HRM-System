<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    public function index()
    {
        return view('dashbord.reports');
    }

    public function generate(Request $request)
    {
        // 1. التحقق من صحة البيانات المرسلة
        $request->validate([
            'module'    => 'required|string',
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);

        $module = $request->module;
        $from   = $request->from_date;
        $to     = $request->to_date;
        $stats  = ['total' => 0];

        switch ($module) {

            case 'annual_summary': // هذه القيمة يجب أن تطابق الـ value في الـ select أو الـ hidden input
                $year = Carbon::parse($from)->year;

                // جلب البيانات مع العلاقات لضمان ظهور الأقسام والمدراء
                $data = Employee::with(['department', 'jobTitle', 'manager'])->get();

                $view = 'dashbord.annual'; // المسار للملف الذي أنشأناه
                $title = "التقرير السنوي الشامل لعام " . $year;

                $stats = [
                    'total' => $data->count(),
                    'active' => $data->where('status', 'نشط')->count(),
                ];
                break;

            case 'single_employee':
                $name = $request->employee_name;
                $type = $request->report_type; // 'personal' أو 'financial'

                $employee = Employee::where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$name}%")
                    ->with(['department', 'jobTitle', 'manager', 'salaries'])
                    ->first();

                if (!$employee) {
                    return back()->with('error', 'عذراً، لم يتم العثور على الموظف.');
                }

                $data = $employee;

                // تحديد ملف العرض بناءً على الخيار
                if ($type == 'financial') {
                    $view = 'employees.financial_report'; // ملف جديد للكشف المالي
                    $title = "الكشف المالي للموظف: " . $employee->first_name;
                } else {
                    $view = 'employees.single_report'; // الملف الذي صممناه للبيانات الشخصية
                    $title = "بيانات الموظف: " . $employee->first_name;
                }
                break;
            // إضافة معالجة بيانات الموظفين لحل مشكلة "الوحدة غير صالحة"
            case 'employees':
                // ✅ الطريقة الجديدة: جلب الموظفين مع المدير الفعلي حسب الصلاحية
                $data = Employee::with(['department', 'jobTitle', 'user.role'])
                    ->get()
                    ->map(function ($employee) {
                        // تحديد المدير الفعلي بناءً على الصلاحية (Role) وليس المسمى الوظيفي
                        $actualManager = null;

                        // 1. إذا كان الموظف لديه manager_id محدد
                        if ($employee->manager_id) {
                            $manager = Employee::with('user.role')->find($employee->manager_id);

                            // تحقق إذا كان المدير المخزن لديه صلاحية "مدير القسم"
                            if (
                                $manager && $manager->user && $manager->user->role &&
                                $manager->user->role->name === 'مدير القسم'
                            ) {
                                $actualManager = $manager;
                            }
                        }

                        // 2. إذا لم يتم العثور على مدير صالح، ابحث عن مدير القسم الفعلي في نفس القسم
                        if (!$actualManager && $employee->department_id) {
                            $departmentManager = Employee::where('department_id', $employee->department_id)
                                ->whereHas('user.role', function ($q) {
                                    $q->where('name', 'مدير القسم');
                                })
                                ->with(['user.role', 'jobTitle'])
                                ->first();

                            if ($departmentManager) {
                                $actualManager = $departmentManager;
                            }
                        }

                        // إضافة المدير الفعلي إلى بيانات الموظف
                        $employee->actual_manager = $actualManager;

                        // إضافة معلومات إضافية للتقرير
                        $employee->has_actual_manager_role = $actualManager ? true : false;
                        $employee->is_actual_manager = ($employee->user && $employee->user->role &&
                            $employee->user->role->name === 'مدير القسم');

                        return $employee;
                    });

                $view = 'employees.report';
                $title = "تقرير بيانات الموظفين الشامل";
                $stats['total'] = $data->count();
                $stats['active'] = $data->where('status', 'نشط')->count();
                $stats['actual_managers'] = $data->where('is_actual_manager', true)->count();
                break;

            case 'attendance':
                $data = DB::table('attendance_records')
                    ->join('employees', 'attendance_records.employee_id', '=', 'employees.id')
                    ->whereBetween('attendance_records.date', [$from, $to])
                    ->select('attendance_records.*', 'employees.first_name as employee_name')
                    ->orderBy('attendance_records.date', 'desc')
                    ->get();

                foreach ($data as $row) {
                    $row->start_date = $row->date;
                    $row->status = $row->status ?? 'حاضر';
                }

                $title = "تقرير الحضور والانصراف";
                $view = 'dashbord.attendance_report';
                break;

            case 'leaves':
                $data = DB::table('leave_requests')
                    ->join('employees', 'leave_requests.employee_id', '=', 'employees.id')
                    ->whereBetween('leave_requests.start_date', [$from, $to])
                    ->select('leave_requests.*', 'employees.first_name as employee_name')
                    ->orderBy('leave_requests.start_date', 'desc')
                    ->get();

                foreach ($data as $row) {
                    $row->date = $row->start_date;
                    $row->check_in = '-';
                    $row->check_out = '-';
                }

                $title = "تقرير طلبات الإجازات";
                $view = 'dashbord.attendance_report';
                break;

            case 'payroll':
                $startMonth = Carbon::parse($from)->format('Y-m');
                $endMonth   = Carbon::parse($to)->format('Y-m');
                $data = Salary::with('employee')->whereBetween('month', [$startMonth, $endMonth])->get();
                $view = 'dashbord.payroll';
                $title = "تقرير الرواتب والمالية";
                break;

            default:
                // في حال كانت القيمة المرسلة لا تطابق الحالات السابقة
                return back()->with('error', 'عذراً، الوحدة المختارة غير صالحة أو لم يتم برمجتها بعد.');
        }

        // معالجة تصدير PDF إذا تم تحديد الخيار في النموذج
        if ($request->has('export_pdf')) {
            $pdf = Pdf::loadView($view, compact('data', 'from', 'to', 'title', 'stats'))
                ->setPaper('a4', 'landscape');
            return $pdf->download($title . '.pdf');
        }

        // تمرير كافة المتغيرات لضمان عدم ظهور خطأ Undefined variable $from
        return view($view, compact('data', 'from', 'to', 'title', 'stats'));
    }
}
