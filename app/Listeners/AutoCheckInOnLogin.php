<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\AttendanceRecord;

class AutoCheckInOnLogin
{
    public function handle(Login $event): void
    {
        // المستخدم الذي سجل دخول
        $user = $event->user;

        // لازم يكون مربوط بموظف
        $employee = $user->employee ?? null;
        if (!$employee) {
            return;
        }

        $today = now()->toDateString();

        // سجل اليوم (إن لم يوجد ينشأ)
        $record = AttendanceRecord::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => $today,
            ]
        );

        // إذا ما سجل حضور من قبل اليوم، سجله
        if (empty($record->check_in)) {
            $record->update([
                'check_in' => now()->format('H:i:s'),
            ]);
        }
    }
}
