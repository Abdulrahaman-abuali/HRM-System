<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property int $age
 * @property string $gender
 * @property string $birth_date
 * @property string|null $address
 * @property string $employment_type
 * @property int|null $manager_id
 * @property int|null $department_id
 * @property int|null $job_title_id
 * @property string $hire_date
 * @property string $status
 * @property decimal $basic_salary
 * @property decimal $housing_percentage
 * @property decimal $transport_percentage
 * @property string|null $face_encoding
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
  * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmploymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereFaceEncoding($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereHireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereJobTitleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereManagerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUserId($value)
 * @mixin \Eloquent
 */
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
        'face_encoding',
        'basic_salary',           // ✅ جديد
        'housing_percentage',     // ✅ جديد
        'transport_percentage',   // ✅ جديد
    ];

    protected $casts = [
        'face_encoding' => 'array',
        'basic_salary' => 'decimal:2',
        'housing_percentage' => 'decimal:2',
        'transport_percentage' => 'decimal:2',
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

    // --- علاقة المدير المباشر ---

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

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

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function salary()
    {
        return $this->hasOne(Salary::class, 'employee_id');
    }
}
