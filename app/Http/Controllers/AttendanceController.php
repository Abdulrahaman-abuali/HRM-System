<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\AttendanceRecord;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $employee = Auth::user()->employee;

        $today = now()->toDateString();

        $todayRecord = AttendanceRecord::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        $records = AttendanceRecord::where('employee_id', $employee->id)
            ->latest('date')
            ->paginate(20);

        return view('dashbord.leave', compact('records', 'todayRecord'));
    }

    public function checkIn()
{
    $employee = Auth::user()->employee;
    if (!$employee) {
        return redirect()->route('leave')->with('error', 'حسابك غير مرتبط بموظف.');
    }

    $today = now()->toDateString();

    $record = \App\Models\AttendanceRecord::firstOrCreate([
        'employee_id' => $employee->id,
        'date' => $today,
    ]);

    if ($record->check_in) {
        return redirect()->route('leave')->with('error', 'تم تسجيل الحضور مسبقاً.');
    }

    $record->update(['check_in' => now()->format('H:i:s')]);

    return redirect()->route('leave')->with('success', 'تم تسجيل الحضور بنجاح.');
}

public function checkOut()
{
    $employee = Auth::user()->employee;
    if (!$employee) {
        return redirect()->route('leave')->with('error', 'حسابك غير مرتبط بموظف.');
    }

    $today = now()->toDateString();

    $record = \App\Models\AttendanceRecord::where('employee_id', $employee->id)
        ->where('date', $today)
        ->first();

    if (!$record || !$record->check_in) {
        return redirect()->route('leave')->with('error', 'يجب تسجيل الحضور أولاً.');
    }

    if ($record->check_out) {
        return redirect()->route('leave')->with('error', 'تم تسجيل الانصراف مسبقاً.');
    }

    $record->update(['check_out' => now()->format('H:i:s')]);

    return redirect()->route('leave')->with('success', 'تم تسجيل الانصراف بنجاح.');
}
}
