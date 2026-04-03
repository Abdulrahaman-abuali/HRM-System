<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // إضافة حقل الراتب الأساسي
            $table->decimal('basic_salary', 10, 2)->default(0)->after('status');

            // إضافة حقول البدلات (نسب مئوية)
            $table->decimal('housing_percentage', 5, 2)->default(0)->after('basic_salary');
            $table->decimal('transport_percentage', 5, 2)->default(0)->after('housing_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'basic_salary',
                'housing_percentage',
                'transport_percentage'
            ]);
        });
    }
};
