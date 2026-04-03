<?php

use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\JobTitle;
use App\Models\Task;
use App\Models\AttendanceRecord;
use App\Models\PerformanceReview;
use App\Models\Role;
use Illuminate\Support\Carbon;

$department = Department::firstOrCreate(['name' => 'IT Department']);
$jobTitle = JobTitle::firstOrCreate(['name' => 'Software Engineer']);
$role = Role::firstOrCreate(['name' => 'موظف']);

$user = User::firstOrCreate(
    ['email' => 'test@example.com'],
    ['name' => 'Test Employee', 'password' => bcrypt('password')]
);
// Attach role if there's a method or just keep it simple

$employee = Employee::firstOrCreate(
    ['email' => 'test@example.com'],
    [
        'user_id' => $user->id,
        'first_name' => 'Test',
        'last_name' => 'Employee',
        'phone' => '123456789',
        'age' => 28,
        'gender' => 'Male',
        'birth_date' => '1996-01-01',
        'employment_type' => 'Full-time',
        'department_id' => $department->id,
        'job_title_id' => $jobTitle->id,
        'hire_date' => '2023-01-01',
        'status' => '1',
    ]
);

// Add 2 tasks
if ($employee->tasks()->count() == 0) {
    $t1 = Task::create([
        'employee_id' => $employee->id,
        'title' => 'Task 1',
        'status' => 'completed',
        'due_date' => Carbon::now()->subDays(2),
        'completed_at' => Carbon::now(),
    ]);
    $t1->created_at = Carbon::now()->subDays(5);
    $t1->save();

    $t2 = Task::create([
        'employee_id' => $employee->id,
        'title' => 'Task 2',
        'status' => 'completed',
        'due_date' => Carbon::now()->addDays(2),
        'completed_at' => Carbon::now(),
    ]);
    $t2->created_at = Carbon::now()->subDays(3);
    $t2->save();
}

// Add 2 attendance records
if ($employee->attendanceRecords()->count() == 0) {
    AttendanceRecord::create([
        'employee_id' => $employee->id,
        'date' => Carbon::now()->subDays(1),
        'check_in' => '08:00:00',
        'check_out' => '16:00:00',
    ]);
    AttendanceRecord::create([
        'employee_id' => $employee->id,
        'date' => Carbon::now(),
        'check_in' => '09:00:00',
        'check_out' => '17:00:00',
    ]);
}

// Add Performance Review
if ($employee->performanceReviews()->count() == 0) {
    PerformanceReview::create([
        'employee_id' => $employee->id,
        'review_period' => '2026-Q1',
        'score' => 75,
    ]);
}

echo "Seeded Test Employee ID: " . $employee->id . "\n";
