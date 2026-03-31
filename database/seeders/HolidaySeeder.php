<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        $year = date('Y'); // السنة الحالية

        $holidays = [
            [
                'name' => '21 سبتمبر (ثورة 21 سبتمبر)',
                'date' => $year . '-09-21',
                'year' => $year,
                'days_count' => 1,
                'notes' => 'إجازة رسمية'
            ],
            [
                'name' => '26 سبتمبر (ثورة 26 سبتمبر)',
                'date' => $year . '-09-26',
                'year' => $year,
                'days_count' => 1,
                'notes' => 'إجازة رسمية'
            ],
            [
                'name' => '14 أكتوبر (ثورة 14 أكتوبر)',
                'date' => $year . '-10-14',
                'year' => $year,
                'days_count' => 1,
                'notes' => 'إجازة رسمية'
            ],
            [
                'name' => '30 نوفمبر (عيد الاستقلال)',
                'date' => $year . '-11-30',
                'year' => $year,
                'days_count' => 1,
                'notes' => 'إجازة رسمية'
            ],
        ];

        foreach ($holidays as $holiday) {
            // تجنب التكرار (إذا كان التاريخ موجوداً لا نضيفه مرة أخرى)
            $exists = Holiday::where('date', $holiday['date'])->exists();
            if (!$exists) {
                Holiday::create($holiday);
            }
        }
    }
}
