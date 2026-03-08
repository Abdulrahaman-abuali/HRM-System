<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();

            // ربط الراتب بالموظف
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('month'); // مثال: 2026-02

            // --- 1. الراتب الأساسي (مبلغ ثابت) ---
            $table->decimal('basic_salary', 10, 2);

            // --- 2. الاستحقاقات (Earnings) - بنسب مئوية من الواجهة ---
            $table->decimal('housing_percentage', 5, 2)->default(0);    // نسبة بدل السكن
            $table->decimal('transport_percentage', 5, 2)->default(0);  // نسبة بدل المواصلات
            $table->decimal('bonuses', 10, 2)->default(0);             // مكافآت (مبلغ ثابت)

            // --- 3. الاستقطاعات (Deductions) - بنسب مئوية من الواجهة ---
            $table->decimal('health_percentage', 5, 2)->default(0);     // نسبة التأمين الصحي
            $table->decimal('tax_percentage', 5, 2)->default(0);        // نسبة الضرائب
            $table->decimal('loan_installments', 10, 2)->default(0);    // أقساط قروض (مبلغ ثابت)
            $table->decimal('penalties', 10, 2)->default(0);            // جزاءات (مبلغ ثابت)

            // --- 4. النتائج النهائية ---
            $table->decimal('net_salary', 10, 2); // يتم حسابه برمجياً قبل الحفظ

            $table->enum('status', ['معلق', 'مدفوع'])->default('معلق');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
