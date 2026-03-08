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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->integer('age');
            $table->string('gender');
            $table->date('birth_date');

            // --- الحقول الجديدة المضافة ---
            $table->string('address')->nullable(); // العنوان السكني
            $table->string('employment_type')->default('full-time'); // نوع التوظيف (دوام كامل، جزئي، الخ)
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete(); // المدير المباشر (ربط ذاتي بنفس الجدول)
            // ------------------------------

            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('job_title_id')->nullable()->constrained('job_titles')->nullOnDelete();
            $table->date('hire_date');
            $table->string('status')->default('active'); // الحالة (نشط بشكل افتراضي)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
