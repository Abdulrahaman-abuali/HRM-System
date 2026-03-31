<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = [
        'employee_id',
        'amount',
        'monthly_installment',
        'total_months',
        'paid_months',
        'remaining_balance',
        'start_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
