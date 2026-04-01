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
        $request->validate([
            'module'    => 'required|string',
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);

        $module = $request->module;
        $from   = $request->from_date;
        $to     = $request->to_date;
        $deptId = $request->department_id;
        $stats  = ['total' => 0];
        $financials = [];
        $monthlyPayroll = []; // للتقرير السنوي
        $period = Carbon::parse($from)->format('Y-m-d') . ' - ' . Carbon::parse($to)->format('Y-m-d');
        $subtitle = '';

        switch ($module) {

            case 'annual_summary':
                $year = Carbon::parse($from)->year;
                $startDate = Carbon::parse($from)->startOfYear();
                $endDate   = Carbon::parse($to)->endOfYear();

                $query = Employee::with(['department', 'jobTitle', 'manager', 'user.role', 'salaries']);
                if ($deptId) {
                    $query->where('department_id', $deptId);
                }
                $employees = $query->get();

                // إحصائيات عامة
                $totalEmployees = $employees->count();
                $activeEmployees = $employees->where('status', 'active')->count();
                $inactiveEmployees = $employees->where('status', 'inactive')->count();

                // إجمالي الرواتب خلال السنة
                $totalPayroll = Salary::whereBetween('month', [$startDate->format('Y-m'), $endDate->format('Y-m')])
                    ->when($deptId, function ($q) use ($deptId) {
                        $q->whereHas('employee', fn($q2) => $q2->where('department_id', $deptId));
                    })
                    ->sum('net_salary');

                // معدل الدوران
                $leftCount = $employees->where('status', 'inactive')->count();
                $turnoverRate = ($totalEmployees > 0) ? round(($leftCount / $totalEmployees) * 100, 1) : 0;

                // المنضمون خلال السنة
                $newHires = $employees->filter(function ($emp) use ($startDate, $endDate) {
                    return $emp->hire_date && Carbon::parse($emp->hire_date)->between($startDate, $endDate);
                })->count();
                $hiringRate = ($totalEmployees > 0) ? round(($newHires / $totalEmployees) * 100, 1) : 0;

                // رصيد الإجازات (يفترض وجود حقل leave_balance في جدول employees)
                $leaveBalance = $employees->sum('leave_balance');

                // متوسط العمر (يفترض وجود حقل birth_date أو age)
                $avgAge = $employees->avg('age') ?: 0;

                // عدد الجنسيات (يفترض وجود حقل nationality)
                $nationalitiesCount = $employees->unique('nationality')->count();

                // توزيع الجنسين (يفترض وجود حقل gender)
                $maleCount = $employees->where('gender', 'male')->count();
                $femaleCount = $employees->where('gender', 'female')->count();

                // بيانات الرسم البياني للرواتب الشهرية
                for ($i = 1; $i <= 12; $i++) {
                    $month = $startDate->copy()->month($i)->format('Y-m');
                    $monthlyPayroll[] = Salary::where('month', $month)
                        ->when($deptId, function ($q) use ($deptId) {
                            $q->whereHas('employee', fn($q2) => $q2->where('department_id', $deptId));
                        })
                        ->sum('net_salary');
                }

                $stats = [
                    'total'            => $totalEmployees,
                    'active'           => $activeEmployees,
                    'inactive'         => $inactiveEmployees,
                    'turnover_rate'    => $turnoverRate,
                    'left_count'       => $leftCount,
                    'hiring_rate'      => $hiringRate,
                    'new_hires'        => $newHires,
                    'leave_balance'    => $leaveBalance,
                    'avg_age'          => round($avgAge),
                    'nationalities_count' => $nationalitiesCount,
                    'male_count'       => $maleCount,
                    'female_count'     => $femaleCount,
                ];

                $financials = [
                    'total_payroll' => $totalPayroll,
                ];

                $data = $employees;
                $view = 'dashbord.annual';
                $title = "التقرير السنوي الشامل لعام $year";
                $subtitle = 'تحليل الأداء المالي والإداري';
                break;

            case 'single_employee':
                $name = $request->employee_name;
                $type = $request->report_type;

                $employee = Employee::where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$name}%")
                    ->with(['department', 'jobTitle', 'manager', 'salaries'])
                    ->first();

                if (!$employee) {
                    return back()->with('error', 'عذراً، لم يتم العثور على الموظف.');
                }

                $data = $employee;

                if ($type == 'financial') {
                    $view = 'employees.financial_report';
                    $title = "الكشف المالي للموظف: " . $employee->first_name . ' ' . $employee->last_name;
                    $subtitle = 'سجل المستحقات والخصومات';
                } else {
                    $view = 'employees.single_report';
                    $title = "بيانات الموظف: " . $employee->first_name . ' ' . $employee->last_name;
                    $subtitle = 'الملف الوظيفي والشخصي';
                }
                break;

            case 'employees':
                $query = Employee::with(['department', 'jobTitle', 'user.role']);

                if ($deptId) {
                    $query->where('department_id', $deptId);
                }

                $data = $query->get()->map(function ($employee) {
                    // حساب المدير الفعلي
                    $actualManager = null;
                    if ($employee->manager_id) {
                        $manager = Employee::with('user.role')->find($employee->manager_id);
                        if ($manager && $manager->user && $manager->user->role && $manager->user->role->name === 'مدير القسم') {
                            $actualManager = $manager;
                        }
                    }
                    if (!$actualManager && $employee->department_id) {
                        $departmentManager = Employee::where('department_id', $employee->department_id)
                            ->whereHas('user.role', function ($q) { $q->where('name', 'مدير القسم'); })
                            ->first();
                        if ($departmentManager) { $actualManager = $departmentManager; }
                    }

                    $employee->actual_manager = $actualManager;
                    $employee->is_actual_manager = ($employee->user && $employee->user->role && $employee->user->role->name === 'مدير القسم');
                    return $employee;
                });

                $view = 'employees.report';
                $title = "تقرير بيانات الموظفين الشامل";
                $subtitle = 'قائمة الموظفين مع معلومات الاتصال والهيكل الإداري';
                $stats['total'] = $data->count();
                break;

            case 'attendance':
                $query = DB::table('attendance_records')
                    ->join('employees', 'attendance_records.employee_id', '=', 'employees.id')
                    ->whereBetween('attendance_records.date', [$from, $to]);

                if ($deptId) {
                    $query->where('employees.department_id', $deptId);
                }

                $data = $query->select('attendance_records.*', 'employees.first_name as employee_name')
                    ->orderBy('attendance_records.date', 'desc')
                    ->get();

                $title = "تقرير الحضور والانصراف";
                $subtitle = 'سجل الحضور والغياب والتأخير';
                $view = 'dashbord.attendance_report';
                break;

            case 'payroll':
                $startMonth = Carbon::parse($from)->format('Y-m');
                $endMonth   = Carbon::parse($to)->format('Y-m');

                $query = Salary::with(['employee.department'])
                               ->whereBetween('month', [$startMonth, $endMonth]);

                if ($deptId) {
                    $query->whereHas('employee', function($q) use ($deptId) {
                        $q->where('department_id', $deptId);
                    });
                }

                $data = $query->get();
                $view = 'dashbord.payroll';  // استخدام ملف payroll الجديد
                $title = "تقرير الرواتب والمالية التفصيلي";
                $subtitle = 'كشف الرواتب الشامل مع تفاصيل البدلات والخصومات';
                break;

            default:
                return back()->with('error', 'عذراً، الوحدة المختارة غير صالحة.');
        }

        // معالجة تصدير PDF (إذا أردت الاحتفاظ بها)
        if ($request->has('export_pdf')) {
            $pdf = Pdf::loadView($view, compact('data', 'from', 'to', 'title', 'stats', 'financials', 'monthlyPayroll', 'period', 'subtitle'))
                ->setPaper('a4', 'landscape');
            return $pdf->download($title . '.pdf');
        }

        // معالجة تصدير Excel (HTML to Excel)
        if ($request->has('export_excel')) {
            $headers = [
                'Content-Type'        => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="' . str_replace(['/', '\\', ':'], '-', $title) . '.xls"',
            ];
            return response()->view($view, compact('data', 'from', 'to', 'title', 'stats', 'financials', 'monthlyPayroll', 'period', 'subtitle'))
                             ->withHeaders($headers);
        }

        // العرض العادي في المتصفح
        return view($view, compact('data', 'from', 'to', 'title', 'stats', 'financials', 'monthlyPayroll', 'period', 'subtitle'));
    }
}
