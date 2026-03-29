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
       Schema::create('activity_logs', function (Blueprint $table) {
        $table->id();
        $table->string('type'); // مثل: success, info, warning
        $table->string('icon'); // مثل: ✓, ℹ, !
        $table->text('description'); // نص النشاط
        $table->unsignedBigInteger('user_id')->nullable(); // المستخدم الذي قام بالعملية
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
