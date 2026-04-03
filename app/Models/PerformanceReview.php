<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $employee_id
 * @property string $review_period
 * @property int $score
 * @property string|null $comments
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Employee $employee
 */
class PerformanceReview extends Model
{
    // The table associated with the model (matches the migration)
    protected $table = 'performance_reviews';

    // The attributes that are mass assignable
    protected $fillable = [
        'employee_id',
        'review_period',
        'score',
        'comments',
    ];

    /**
     * Get the employee that owns the performance review.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
