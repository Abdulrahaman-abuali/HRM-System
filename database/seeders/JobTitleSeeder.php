<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JobTitle;

class JobTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

    if (JobTitle::count() === 0) {
        JobTitle::insert([
            ['name' => 'محاسب'],
            ['name' => 'مبرمج'],
            ['name' => 'مدير'],

        ]);

    }
    }
}
