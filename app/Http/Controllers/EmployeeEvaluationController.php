<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Illuminate\Routing\Controller;

class EmployeeEvaluationController extends Controller
{
    /**
     * Evaluate the employee, calculate AI metrics, and return the evaluation view.
     */
    public function evaluate($id)
    {
        // 1. Fetch Employee and related data
        $employee = Employee::with(['tasks', 'attendanceRecords', 'performanceReviews'])->findOrFail($id);
        
        // 2. Delegate to AIEvaluationService
        $aiService = new \App\Services\AIEvaluationService();
        $result = $aiService->evaluateEmployee($employee, true);

        // 3. Return Blade View
        return view('employee_evaluation', [
            'employee' => $employee,
            'evaluationData' => $result['evaluationData'],
            'productivity_score' => $result['productivity_score'],
            'skill_level' => $result['skill_level'],
            'status' => $result['status_label'],
            'recommended_training' => $result['recommended_training']
        ]);
    }
}
