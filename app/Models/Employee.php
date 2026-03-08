<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'age',
        'gender',
        'birth_date',
        'address',
        'employment_type',
        'manager_id',
        'department_id',
        'job_title_id',
        'hire_date',
        'status',
    ];

    // --- العلاقات الأساسية ---

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function jobTitle()
    {
        return $this->belongsTo(JobTitle::class, 'job_title_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // --- علاقة المدير المباشر (جديدة) ---

    /**
     * جلب بيانات المدير المباشر لهذا الموظف
     */
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * جلب قائمة الموظفين الذين يشرف عليهم هذا الموظف
     */
    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    // --- علاقات السجلات ---

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }

    /**
     * دالة مساعدة للحصول على الاسم الكامل
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
