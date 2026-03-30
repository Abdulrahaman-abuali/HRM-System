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
        Schema::table('notifications', function (Blueprint $table) {
        // إضافة الأعمدة التي يطلبها لارافيل لإدارة الإشعارات
        if (!Schema::hasColumn('notifications', 'notifiable_type')) {
            $table->string('notifiable_type')->after('id');
        }
        if (!Schema::hasColumn('notifications', 'notifiable_id')) {
            $table->unsignedBigInteger('notifiable_id')->after('notifiable_type');
        }

        // تأكد من وجود حقل البيانات وحقل تاريخ القراءة
        if (!Schema::hasColumn('notifications', 'data')) {
            $table->text('data')->nullable();
        }
        if (!Schema::hasColumn('notifications', 'read_at')) {
            $table->timestamp('read_at')->nullable();
        }
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            //
        });
    }
};
