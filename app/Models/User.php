<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    // ربط النموذج بجدول users
    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    // لا تنسى التأكد من وجود العلاقة في الأعلى
public function role()
{
    // المستخدم ينتمي إلى دور واحد (BelongsTo)
   return $this->belongsTo(Role::class, 'role_id');
}
public function employee()
{
    return $this->hasOne(\App\Models\Employee::class);
}
}
