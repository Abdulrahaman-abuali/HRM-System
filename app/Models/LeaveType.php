<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    // السماح بتعبئة اسم نوع الإجازة
    protected $fillable = ['name'];

    /**
     * العلاقة: نوع الإجازة الواحد له العديد من طلبات الإجازة
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
