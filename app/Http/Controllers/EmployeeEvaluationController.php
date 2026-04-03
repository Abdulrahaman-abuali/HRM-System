<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmployeeEvaluationController extends Controller
{
    /**
     * Evaluate the employee, calculate AI metrics, and return the evaluation view.
     */
    public function evaluate($id)
    {
        // 1. Fetch Employee and related data
        $employee = Employee::with(['tasks', 'attendanceRecords', 'performanceReviews'])->findOrFail($id);
        
        // 2. Calculate AI Features
        
        // --- Tasks Metrics ---
        $completedTasks = $employee->tasks->where('status', 'completed');
        $tasks_completed = $completedTasks->count();
        
        $totalDurationDays = 0;
        $lateTasksCount = 0;

        foreach ($completedTasks as $task) {
            // Task duration in DAYS
            if ($task->created_at && $task->completed_at) {
                // Get difference in fractional days (or integer days)
                // Use diffInRealHours / 24 or diffInDays to be precise. 
                // diffInDays floors the value, so we'll use diffInMinutes / (24 * 60) for precision
                $minutes = $task->created_at->diffInMinutes(Carbon::parse($task->completed_at));
                $totalDurationDays += ($minutes / (24 * 60));
            }

            // Late tasks calculation
            if ($task->due_date && $task->completed_at) {
                // Compare start of the days to see if completion was after due date
                $dueDate = Carbon::parse($task->due_date)->startOfDay();
                $completedDate = Carbon::parse($task->completed_at)->startOfDay();
                if ($completedDate->gt($dueDate)) {
                    $lateTasksCount++;
                }
            }
        }

        $avg_task_duration = $tasks_completed > 0 ? ($totalDurationDays / $tasks_completed) : 0;
        $late_ratio = $tasks_completed > 0 ? ($lateTasksCount / $tasks_completed) : 0;

        // --- Work Hours Metric ---
        $totalWorkHours = 0;
        $validAttendanceDays = 0;

        foreach ($employee->attendanceRecords as $record) {
            if ($record->check_in && $record->check_out) {
                // Calculate hours worked in a day
                $hours = Carbon::parse($record->check_in)->diffInMinutes(Carbon::parse($record->check_out)) / 60;
                $totalWorkHours += $hours;
                $validAttendanceDays++;
            }
        }

        $avg_work_hours = $validAttendanceDays > 0 ? ($totalWorkHours / $validAttendanceDays) : 0;

        // --- Performance Score Metric ---
        // Get the average from PerformanceReview, default to 0
        $performance_score = $employee->performanceReviews->avg('score') ?? 0;

        // Prepare data for the API
        $evaluationData = [
            'tasks_completed' => round($tasks_completed, 2),
            'avg_task_duration' => round($avg_task_duration, 2), // Now in days
            'late_ratio' => round($late_ratio, 2),
            'avg_work_hours' => round($avg_work_hours, 2),
            'performance_score' => round($performance_score, 2),
        ];

        // 3. Productivity Prediction via Python API
        $productivity_score = 0;
        try {
            $response = Http::timeout(3)->post('http://127.0.0.1:5000/predict_productivity', $evaluationData);
            if ($response->successful()) {
                $productivity_score = $response->json('productivity_score', 0);
            } else {
                Log::warning('Python API Predict Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Python AI Service is down: ' . $e->getMessage());
            // Fail gracefully
        }

        // Dynamically compute the score (Will not store it currently as per requirement)

        // 4. Calculate Skill Level
        $skill_level = 'Junior';
        if ($productivity_score > 70) {
            $skill_level = 'Senior';
        } elseif ($productivity_score >= 40) {
            $skill_level = 'Mid';
        }

        // 5. Business Logic & Training Recommendations
        $status = 'Good';
        $recommended_training = null;

        if ($productivity_score < 60) {
            $status = 'Needs Improvement';
            
            // Fetch Training Recommendation
            $recommendationPayload = array_merge($evaluationData, [
                'productivity_score' => round($productivity_score, 2),
                'department' => $employee->department ? $employee->department->name : 'Unknown', // Pass string
                'skill_level' => $skill_level
            ]);

            try {
                $recResponse = Http::timeout(3)->post('http://127.0.0.1:5000/recommend_training', $recommendationPayload);
                if ($recResponse->successful()) {
                    $recommended_training = $recResponse->json('recommended_training', 'General Improvement Course');
                } else {
                    Log::warning('Python API Recommend Error: ' . $recResponse->body());
                }
            } catch (\Exception $e) {
                Log::error('Python AI Service is down (Training): ' . $e->getMessage());
            }
        } else {
            if ($productivity_score > 80) {
                $status = 'High Performer';
            }
        }

        // 6. Return Blade View
        return view('employee_evaluation', [
            'employee' => $employee,
            'evaluationData' => $evaluationData,
            'productivity_score' => round($productivity_score, 2),
            'skill_level' => $skill_level,
            'status' => $status,
            'recommended_training' => $recommended_training
        ]);
    }
}
