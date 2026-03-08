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
                $data = Employee::with('department')->get();
                $view = 'employees.report'; // تأكد من وجود ملف dashbord/employees.blade.php
                $title = "تقرير بيانات الموظفين الشامل";
                $stats['total'] = $data->count();
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
