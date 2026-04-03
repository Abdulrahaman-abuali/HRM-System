<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    // سنقوم بتغيير العمود ليقبل القيمة الجديدة
    // ملاحظة: إذا كنت تستخدم MySQL، يفضل استخدام DB::statement لضمان التعديل
    DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'rejected') DEFAULT 'pending'");
}

public function down()
{
    // للعودة للحالة السابقة (حذف خيار الرفض)
    DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending'");
}
};
