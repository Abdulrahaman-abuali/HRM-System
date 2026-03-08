<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * العلاقة: السجل ينتمي لموظف واحد
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
