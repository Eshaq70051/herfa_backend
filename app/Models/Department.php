<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $guarded = ['id'];

    public function managerEmployee()
    {
        return $this->belongsTo(EmployeeProfile::class, 'manager_employee_id', 'id');
    }

    public function parentDepartment()
    {
        return $this->belongsTo(Department::class, 'parent_department_id', 'id');
    }

    public function employeeDepartments()
    {
        return $this->hasMany(EmployeeDepartment::class, 'department_id', 'id');
    }
}
