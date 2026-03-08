<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        // تحقق أولاً إن الأقسام موجودة لتجنب التكرار
        if (Department::count() === 0) {
            Department::insert([
                ['name' => 'الموارد البشرية'],
                ['name' => 'تقنية المعلومات'],
            ]);
        }
    }
}
