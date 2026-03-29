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
 * @property numeric $net_salary
 * @property string $status
 * @property string|null $paid_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereBasicSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereBonuses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereHealthPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereHousingPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereLoanInstallments($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereNetSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary wherePenalties($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereTaxPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereTransportPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salary whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Salary extends Model
{
    use HasFactory;

    // الحقول المسموح بتعبئتها (يجب أن تطابق ملف الميجريشن الجديد)
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
        'net_salary',
        'status',
        'paid_at'
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

            // 2. حساب الاستقطاعات (Deductions)
            $healthAmount = $basic * (($salary->health_percentage ?? 0) / 100);
            $taxAmount = $basic * (($salary->tax_percentage ?? 0) / 100);
            $totalDeductions = $healthAmount + $taxAmount + ($salary->loan_installments ?? 0) + ($salary->penalties ?? 0);

            // 3. النتيجة النهائية
            $salary->net_salary = $totalEarnings - $totalDeductions;
        };

        static::creating(function ($salary) use ($calculateNet) {
            $calculateNet($salary);
        });

        static::updating(function ($salary) use ($calculateNet) {
            $calculateNet($salary);
        });
    }
}
