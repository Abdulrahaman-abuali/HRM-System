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
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'rejected') DEFAULT 'pending'");
        }
    }

    public function down()
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE tasks MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending'");
        }
    }
};
