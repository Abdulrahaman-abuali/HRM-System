<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->integer('absence_days')->default(0)->after('penalties');
            $table->decimal('absence_deduction', 10, 2)->default(0)->after('absence_days');
            $table->integer('late_minutes')->default(0)->after('absence_deduction');
            $table->decimal('late_deduction', 10, 2)->default(0)->after('late_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'absence_days',
                'absence_deduction',
                'late_minutes',
                'late_deduction'
            ]);
        });
    }
};
