<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AIEvaluationService
{
    /**
     * حساب وتقييم الموظف باستخدام نماذج الذكاء الاصطناعي وإرجاع النتائج.
     *
     * @param Employee $employee
     * @param bool $requestTrainingRecommendation هل نطلب توصية التدريب في حال كان الأداء ضعيفاً أم نكتفي بالإنتاجية؟
     * @return array
     */
    public function evaluateEmployee(Employee $employee, $requestTrainingRecommendation = true)
    {
        // 1. Calculate AI Features
        
        // --- Tasks Metrics ---
        $completedTasks = $employee->tasks->where('status', 'completed');
        $tasks_completed = $completedTasks->count();
        
        $totalDurationDays = 0;
        $lateTasksCount = 0;

        foreach ($completedTasks as $task) {
            // Task duration in DAYS
            if ($task->created_at && $task->completed_at) {
                // diffInDays floors the value, so we use diffInMinutes / (24 * 60) for precision
                $minutes = $task->created_at->diffInMinutes(Carbon::parse($task->completed_at));
                $totalDurationDays += ($minutes / (24 * 60));
            }

            // Late tasks calculation
            if ($task->due_date && $task->completed_at) {
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
        $performance_score = $employee->performanceReviews->avg('score') ?? 0;

        // Prepare data for the API
        $evaluationData = [
            'tasks_completed' => round($tasks_completed, 2),
            'avg_task_duration' => round($avg_task_duration, 2),
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
        }

        // 4. Calculate Skill Level
        $skill_level = 'Junior';
        $status_label = 'مقبول'; // Default
        $rating_class = 'acceptable';

        if ($productivity_score > 80) {
            $skill_level = 'Senior';
            $status_label = 'ممتاز';
            $rating_class = 'excellent';
        } elseif ($productivity_score > 70) {
            $skill_level = 'Senior';
            $status_label = 'جيد جداً';
            $rating_class = 'very-good';
        } elseif ($productivity_score >= 60) {
            $skill_level = 'Mid';
            $status_label = 'مقبول';
            $rating_class = 'acceptable';
        } elseif ($productivity_score >= 40) {
            $skill_level = 'Mid';
            $status_label = 'ضعيف';
            $rating_class = 'weak';
        } else {
            $skill_level = 'Junior';
            $status_label = 'ضعيف';
            $rating_class = 'weak';
        }


        // 5. Training Recommendations
        $recommended_training = null;

        if ($requestTrainingRecommendation && $productivity_score < 60) {
            // Fetch Training Recommendation
            $recommendationPayload = array_merge($evaluationData, [
                'productivity_score' => round($productivity_score, 2),
                'department' => $employee->department ? $employee->department->name : 'Unknown',
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
        }

        return [
            'evaluationData' => $evaluationData,
            'productivity_score' => round($productivity_score, 2),
            'skill_level' => $skill_level,
            'status_label' => $status_label,
            'rating_class' => $rating_class,
            'recommended_training' => $recommended_training
        ];
    }
}
