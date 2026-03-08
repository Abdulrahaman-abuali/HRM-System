<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'إجازة سنوية'],
            ['name' => 'إجازة مرضية'],
            ['name' => 'إجازة اضطرارية'],
            ['name' => 'إجازة أمومة/أبوة'],
            ['name' => 'إجازة بدون راتب'],
        ];

        foreach ($types as $type) {
            LeaveType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
