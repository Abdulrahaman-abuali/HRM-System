<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->string('name');              // اسم الإجازة
            $table->date('date');                // التاريخ الميلادي
            $table->integer('year');             // السنة الميلادية
            $table->integer('days_count')->default(1); // عدد الأيام
            $table->text('notes')->nullable();    // ملاحظات
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
