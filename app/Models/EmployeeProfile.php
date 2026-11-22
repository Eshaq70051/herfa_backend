<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $table = 'employee_profiles';

    protected $guarded = ['id'];

    public function reportsToEmployee()
    {
        return $this->belongsTo(EmployeeProfile::class, 'reports_to_employee_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'manager_employee_id', 'id');
    }

    public function employeeDepartments()
    {
        return $this->hasMany(EmployeeDepartment::class, 'employee_profile_id', 'id');
    }

    public function financialTransactions()
    {
        return $this->hasMany(EmployeeFinancialTransaction::class, 'employee_profile_id', 'id');
    }

    public function getTotalDebitAttribute(): float
    {
        return (float) $this->financialTransactions()->where('transaction_type', 'debit')->sum('amount');
    }

    public function getTotalCreditAttribute(): float
    {
        return (float) $this->financialTransactions()->where('transaction_type', 'credit')->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return $this->total_debit - $this->total_credit;
    }
}
