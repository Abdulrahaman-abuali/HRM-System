<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Routing\Controller;

class FaceAttendanceController extends Controller
{
    /**
     * تسجيل حضور أو انصراف الموظف (موحد)
     */
    public function record(Request $request)
    {
        try {
            $request->validate([
                'employee_id' => 'required|integer|exists:employees,id',
                'type' => 'required|in:checkin,checkout'
            ]);

            $employeeId = $request->employee_id;
            $type = $request->type;
            $employee = Employee::find($employeeId);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'الموظف غير موجود في النظام'
                ], 404);
            }

            $today = Carbon::now()->format('Y-m-d');
            $now = Carbon::now()->format('H:i:s');
            $employeeName = $employee->first_name . ' ' . $employee->last_name;

            if ($type == 'checkin') {
                // ===== تسجيل حضور =====
                $existing = AttendanceRecord::where('employee_id', $employeeId)
                    ->where('date', $today)
                    ->first();

                if ($existing && $existing->check_in) {
                    return response()->json([
                        'success' => false,
                        'message' => 'تم تسجيل حضور ' . $employeeName . ' مسبقاً اليوم',
                        'employee_id' => $employee->id,
                        'employee_name' => $employeeName,
                        'check_in' => $existing->check_in,
                        'type' => 'checkin'
                    ]);
                }

                $attendance = AttendanceRecord::updateOrCreate(
                    ['employee_id' => $employeeId, 'date' => $today],
                    ['check_in' => $now]
                );
                // عند نجاح تسجيل البصمة

                return response()->json([
                    'success' => true,
                    'message' => 'تم تسجيل حضور ' . $employeeName . ' بنجاح',
                    'employee_id' => $employee->id,
                    'employee_name' => $employeeName,
                    'time' => $now,
                    'type' => 'checkin'
                ]);

            } else {
                // ===== تسجيل انصراف =====
                $attendance = AttendanceRecord::where('employee_id', $employeeId)
                    ->where('date', $today)
                    ->first();

                if (!$attendance) {
                    return response()->json([
                        'success' => false,
                        'message' => 'لا يوجد تسجيل حضور لليوم',
                        'type' => 'checkout'
                    ]);
                }

                if ($attendance->check_out) {
                    return response()->json([
                        'success' => false,
                        'message' => 'تم تسجيل انصراف ' . $employeeName . ' مسبقاً اليوم',
                        'employee_id' => $employee->id,
                        'employee_name' => $employeeName,
                        'check_out' => $attendance->check_out,
                        'type' => 'checkout'
                    ]);
                }

                $attendance->check_out = $now;
                $attendance->save();
                // عند نجاح تسجيل البصمة

                return response()->json([
                    'success' => true,
                    'message' => 'تم تسجيل انصراف ' . $employeeName . ' بنجاح',
                    'employee_id' => $employee->id,
                    'employee_name' => $employeeName,
                    'time' => $now,
                    'type' => 'checkout'
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * تصدير بيانات الوجه للموظفين
     */
    public function exportFaceData()
    {
        try {
            $employees = Employee::whereNotNull('face_encoding')
                ->select('id', 'first_name', 'last_name', 'face_encoding')
                ->get();

            $faceData = [];

            foreach ($employees as $employee) {
                $encoding = null;

                if (is_string($employee->face_encoding)) {
                    $encoding = json_decode($employee->face_encoding, true);
                } elseif (is_array($employee->face_encoding)) {
                    $encoding = $employee->face_encoding;
                }

                if ($encoding) {
                    $faceData[] = [
                        'employee_id' => $employee->id,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'encoding' => $encoding
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'count' => count($faceData),
                'data' => $faceData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * جلب قائمة الموظفين
     */
    public function getEmployeesList()
    {
        try {
            $employees = Employee::select('id', 'first_name', 'last_name')
                ->orderBy('id')
                ->get();

            return response()->json([
                'success' => true,
                'count' => $employees->count(),
                'employees' => $employees->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => $employee->first_name . ' ' . $employee->last_name
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * التحقق من صحة الخادم
     */
    public function health()
    {
        return response()->json([
            'success' => true,
            'status' => 'running',
            'message' => 'خادم نظام بصمة الوجه جاهز للعمل',
            'timestamp' => Carbon::now()->toDateTimeString()
        ]);
    }
    /**
 * جلب آخر تسجيلات الحضور (للتحديث التلقائي)
 */
    public function getLatestAttendance(Request $request)
    {
        try {
            $user = \Illuminate\Support\Facades\Auth::user();
            $role = $user->role?->name;

            // جلب التاريخ من الطلب (Request) أو استخدام تاريخ اليوم كافتراضي
            $selectedDate = $request->input('date', \Carbon\Carbon::now()->format('Y-m-d'));

            // 1. بناء الاستعلام مع علاقة الموظف
            $query = AttendanceRecord::with('employee')
                ->whereDate('date', $selectedDate);

            // 2. ✨ الفلترة الجوهرية لمدير القسم
            if ($role === 'مدير القسم') {
                $deptId = $user->employee->department_id ?? null;

                if ($deptId) {
                    // نجلب فقط السجلات التي ينتمي موظفوها لقسم هذا المدير
                    $query->whereHas('employee', function($q) use ($deptId) {
                        $q->where('department_id', $deptId);
                    });
                } else {
                    // إذا لم يكن للمدير قسم مرتب، نعيد بيانات فارغة للأمان
                    return response()->json(['success' => true, 'data' => [], 'count' => 0]);
                }
            }

            // 3. جلب البيانات وترتيبها
            $attendance = $query->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($record) {
                $workHours = '—';
                $delayTime = '—';
                $officialStartTime = '08:00:00'; // 🕒 وقت بداية الدوام الرسمي

                // 1. حساب ساعات العمل (إذا انصرف)
                if ($record->check_in && $record->check_out) {
                    $workHours = \Carbon\Carbon::parse($record->check_in)
                        ->diff(\Carbon\Carbon::parse($record->check_out))
                        ->format('%h س و %i د');
                }

                // 2. حساب التأخير (مقارنة الحضور بالوقت الرسمي)
                if ($record->check_in) {
                    $checkInTime = date('H:i:s', strtotime($record->check_in));
                    if ($checkInTime > $officialStartTime) {
                        $delay = \Carbon\Carbon::parse($officialStartTime)
                            ->diff(\Carbon\Carbon::parse($checkInTime));
                        $delayTime = $delay->format('%h س و %i د');
                    }
                }

                // 3. تحديد الحالة اللونية
                $status = $record->check_out ? 'مكتمل' : 'على رأس العمل';
                if ($delayTime !== '—') {
                    $status = 'متأخر (' . $delayTime . ')';
                }

                return [
                    'id' => $record->id,
                    'employee_name' => $record->employee->first_name . ' ' . $record->employee->last_name,
                    'check_in' => $record->check_in ? date('H:i:s', strtotime($record->check_in)) : '--',
                    'check_out' => $record->check_out ? date('H:i:s', strtotime($record->check_out)) : '--',
                    'delay' => $delayTime, // حقل جديد
                    'work_hours' => $workHours,
                    'status' => $status,
                    'is_late' => ($delayTime !== '—') // علامة لتمييز اللون في JS
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $attendance,
                'count' => $attendance->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في جلب البيانات' // نص عام للأمان، ويمكنك ترك $e->getMessage() للتطوير
            ], 500);
        }
    }
}
