<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('tasks', function (Blueprint $table) {
        // إضافة حقل سبب الرفض (يسمح بأن يكون فارغاً)
        if (!Schema::hasColumn('tasks', 'rejection_reason')) {
            $table->text('rejection_reason')->nullable()->after('status');
        }

        // إضافة حقل معرف من أنشأ المهمة (لتمييز مدير النظام عن مدير القسم)
        if (!Schema::hasColumn('tasks', 'added_by')) {
            $table->unsignedBigInteger('added_by')->nullable()->after('employee_id');

            // ربط الحقل بجدول المستخدمين لضمان صحة البيانات
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
        }
    });
}

public function down()
{
    Schema::table('tasks', function (Blueprint $table) {
        $table->dropColumn(['rejection_reason', 'added_by']);
    });
}
};
