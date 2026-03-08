<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Salary;

class SalarySeeder extends Seeder
{
    public function run(): void
    {
        // جلب جميع الموظفين المسجلين في النظام
        $employees = Employee::all();

        foreach ($employees as $employee) {
            Salary::create([
                'employee_id' => $employee->id,
                'month'       => now()->format('Y-m'), // الشهر الحالي
                'basic_salary' => 500.00, // راتب أساسي افتراضي لشركة البرمجيات الصغيرة

                // إدخال النسب المئوية للاستحقاقات
                'housing_percentage'   => 10.00, // 10% بدل سكن
                'transport_percentage' => 5.00,  // 5% بدل مواصلات
                'bonuses'              => 50.00, // مكافأة مالية ثابتة

                // إدخال النسب المئوية للاستقطاعات
                'health_percentage' => 3.00, // 3% تأمين صحي
                'tax_percentage'    => 2.00, // 2% ضرائب
                'loan_installments' => 0,
                'penalties'         => 0,

                // تنويع الحالات لاختبار الفلاتر والألوان في الواجهة
                'status'  => $employee->id % 2 == 0 ? 'مدفوع' : 'معلق',
                'paid_at' => $employee->id % 2 == 0 ? now() : null,
            ]);
        }
    }
}
