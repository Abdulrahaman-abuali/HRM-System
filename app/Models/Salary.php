<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $employee_id
 * @property string $month
 * @property numeric $basic_salary
 * @property numeric $housing_percentage
 * @property numeric $transport_percentage
 * @property numeric $bonuses
 * @property numeric $health_percentage
 * @property numeric $tax_percentage
 * @property numeric $loan_installments
 * @property numeric $penalties
 * @property int $absence_days
 * @property numeric $absence_deduction
 * @property int $late_minutes
 * @property numeric $late_deduction
 * @property numeric $net_salary
 * @property string $status
 * @property string|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 */
class Salary extends Model
{
    use HasFactory;

    // الحقول المسموح بتعبئتها
    protected $fillable = [
        'employee_id',
        'month',
        'basic_salary',
        'housing_percentage',
        'transport_percentage',
        'bonuses',
        'health_percentage',
        'tax_percentage',
        'loan_installments',
        'penalties',
        'absence_days',        // جديد
        'absence_deduction',   // جديد
        'late_minutes',        // جديد
        'late_deduction',      // جديد
        'net_salary',
        'status',
        'paid_at'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    // علاقة الراتب بالموظف
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // منطق الحساب التلقائي للرواتب
    protected static function booted()
    {
        // دالة موحدة للحساب تستخدم عند الإنشاء أو التحديث
        $calculateNet = function ($salary) {
            $basic = $salary->basic_salary;

            // 1. حساب الاستحقاقات (Earnings)
            $housingAmount = $basic * (($salary->housing_percentage ?? 0) / 100);
            $transportAmount = $basic * (($salary->transport_percentage ?? 0) / 100);
            $totalEarnings = $basic + $housingAmount + $transportAmount + ($salary->bonuses ?? 0);

            // 2. حساب خصم الغياب والتأخير (إذا لم يتم حسابهما مسبقاً)
            $absenceDeduction = $salary->absence_deduction ?? 0;
            $lateDeduction = $salary->late_deduction ?? 0;

            // إذا كان absence_days موجود ولكن absence_deduction لم يحسب بعد
            if (($salary->absence_days ?? 0) > 0 && $absenceDeduction == 0) {
                $dailyRate = $basic / 30;
                $absenceDeduction = $salary->absence_days * $dailyRate;
                $salary->absence_deduction = round($absenceDeduction, 2);
            }

            // إذا كان late_minutes موجود ولكن late_deduction لم يحسب بعد
            if (($salary->late_minutes ?? 0) > 0 && $lateDeduction == 0) {
                $dailyRate = $basic / 30;
                $hourlyRate = $dailyRate / 8;
                $minuteRate = $hourlyRate / 60;
                $lateDeduction = $salary->late_minutes * $minuteRate;
                $salary->late_deduction = round($lateDeduction, 2);
            }

            // 3. حساب الاستقطاعات (Deductions)
            $healthAmount = $basic * (($salary->health_percentage ?? 0) / 100);
            $taxAmount = $basic * (($salary->tax_percentage ?? 0) / 100);
            $totalDeductions = $healthAmount + $taxAmount
                             + ($salary->loan_installments ?? 0)
                             + ($salary->penalties ?? 0)
                             + $absenceDeduction
                             + $lateDeduction;

            // 4. النتيجة النهائية
            $salary->net_salary = round($totalEarnings - $totalDeductions, 2);
        };

        static::creating(function ($salary) use ($calculateNet) {
            $calculateNet($salary);
        });

        static::updating(function ($salary) use ($calculateNet) {
            $calculateNet($salary);
        });
    }
}
