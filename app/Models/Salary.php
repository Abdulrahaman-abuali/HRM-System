<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
