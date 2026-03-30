<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\AttendanceRecord;

class AutoCheckOutOnLogout
{
    public function handle(Logout $event): void
    {
        // $user = $event->user;

        // $employee = $user->employee ?? null;
        // if (!$employee) {
        //     return;
        // }

        // $today = now()->toDateString();

        // $record = AttendanceRecord::where('employee_id', $employee->id)
        //     ->where('date', $today)
        //     ->first();

        // // لا تسجل انصراف إلا إذا فيه حضور
        // if (!$record || empty($record->check_in)) {
        //     return;
        // }

        // // لا تكرر الانصراف
        // if (!empty($record->check_out)) {
        //     return;
        // }

        // $record->update([
        //     'check_out' => now()->format('H:i:s'),
        // ]);
    }
}
