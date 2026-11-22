<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeFinancialTransaction extends Model
{
    use HasFactory;

    protected $table = 'employee_financial_transactions';

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'float',
        'transaction_date' => 'date',
    ];

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class, 'employee_profile_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function getCategoryNameAttribute(): string
    {
        return match ($this->category) {
            'custody' => 'عهدة نقدية / مالية',
            'advance' => 'سلفة موظف',
            'expense_settlement' => 'تسوية مصاريف وفواتير',
            'salary' => 'راتب / أجر مستحق',
            'repayment' => 'سداد وتوريد نقدي',
            default => 'أخرى / تسوية',
        };
    }
}
