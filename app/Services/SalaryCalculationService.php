<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\Holiday;
use Carbon\Carbon;

class SalaryCalculationService
{
    /**
     * حساب أيام الغياب الفعلية لموظف في شهر معين
     * (مع مراعاة الإجازات الرسمية والأسبوعية والإجازات المصرح بها)
     */
    public function calculateActualAbsenceDays($employeeId, $year, $month)
    {
        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        // 1. أيام العمل في الشهر (بعد استبعاد الإجازات الأسبوعية والرسمية)
        $workDays = $this->getWorkDaysInRange($startDate, $endDate);

        // 2. أيام الحضور الفعلية (سجل فيها حضوراً)
        $attendedDays = AttendanceRecord::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('check_in')
            ->count();

        // 3. أيام الإجازة المصرح بها (من جدول leave_requests)
        $approvedLeaveDays = $this->getApprovedLeaveDays($employeeId, $startDate, $endDate);

        // 4. أيام الغياب الفعلية = أيام العمل - (حضور + إجازات مصرح بها)
        $actualAbsence = $workDays - ($attendedDays + $approvedLeaveDays);

        return max(0, $actualAbsence);
    }

    /**
     * حساب دقائق التأخير الفعلية لموظف في شهر معين
     * (بعد وقت السماح)
     */
    public function calculateActualLateMinutes($employeeId, $year, $month)
    {
        $workStartTime = config('attendance.work_start_time', '08:00');
        $graceMinutes = config('attendance.grace_minutes', 15);

        $startDate = Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        $attendanceRecords = AttendanceRecord::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->whereNotNull('check_in')
            ->get();

        $totalLateMinutes = 0;

        foreach ($attendanceRecords as $record) {
            $checkIn = Carbon::parse($record->check_in);
            $officialStart = Carbon::parse($workStartTime);

            $lateMinutes = $checkIn->diffInMinutes($officialStart, false);

            // فقط إذا تجاوز وقت السماح يتم احتساب التأخير
            if ($lateMinutes > $graceMinutes) {
                $totalLateMinutes += $lateMinutes;
            }
        }

        return $totalLateMinutes;
    }

    /**
     * حساب خصم الغياب والتأخير
     */
    public function calculateDeductions($basicSalary, $absenceDays, $lateMinutes)
    {
        $monthDays = config('attendance.month_days_for_salary', 30);
        $dailyRate = $basicSalary / $monthDays;
        $hourlyRate = $dailyRate / 8;
        $minuteRate = $hourlyRate / 60;

        return [
            'absence_deduction' => round($absenceDays * $dailyRate, 2),
            'late_deduction' => round($lateMinutes * $minuteRate, 2),
            'total_deduction' => round(($absenceDays * $dailyRate) + ($lateMinutes * $minuteRate), 2)
        ];
    }

    /**
     * حساب عدد أيام العمل في نطاق زمني (باستثناء الإجازات الأسبوعية والرسمية)
     */
    private function getWorkDaysInRange($startDate, $endDate)
    {
        $workDays = 0;
        $weekendDays = config('attendance.weekend_days', ['friday']);

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dayName = strtolower($date->format('l'));

            // تخطي أيام الإجازة الأسبوعية
            if (in_array($dayName, $weekendDays)) {
                continue;
            }

            // تخطي الإجازات الرسمية
            if (Holiday::where('date', $date->toDateString())->exists()) {
                continue;
            }

            $workDays++;
        }

        return $workDays;
    }

    /**
     * حساب عدد أيام الإجازة المصرح بها لموظف في نطاق زمني
     */
    private function getApprovedLeaveDays($employeeId, $startDate, $endDate)
    {
        $leaveRequests = LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function($q) use ($startDate, $endDate) {
                          $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                      });
            })
            ->get();

        $totalDays = 0;
        $weekendDays = config('attendance.weekend_days', ['friday']);

        foreach ($leaveRequests as $leave) {
            $leaveStart = max($leave->start_date, $startDate);
            $leaveEnd = min($leave->end_date, $endDate);

            // حساب عدد أيام العمل في فترة الإجازة (استبعاد الإجازات الأسبوعية)
            for ($date = Carbon::parse($leaveStart); $date <= Carbon::parse($leaveEnd); $date->addDay()) {
                $dayName = strtolower($date->format('l'));
                if (!in_array($dayName, $weekendDays)) {
                    $totalDays++;
                }
            }
        }

        return $totalDays;
    }
}
