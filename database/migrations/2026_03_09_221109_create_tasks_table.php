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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id(); // رقم المهمة
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // الموظف المسؤول
            $table->string('title'); // اسم المهمة
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending'); // حالة المهمة
            $table->date('due_date')->nullable(); // موعد تسليم المهمة
            $table->timestamp('completed_at')->nullable(); // تاريخ إنهاء المهمة
            $table->timestamps(); // ستوفر لنا created_at (تاريخ الإنشاء) تلقائياً
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
