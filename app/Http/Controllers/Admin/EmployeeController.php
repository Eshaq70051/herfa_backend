<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Department;
use App\Models\EmployeeDepartment;
use App\Models\EmployeeFinancialTransaction;
use App\Models\EmployeeProfile;
use App\Models\PermissionGroup;
use App\Models\RequestAssignment;
use App\Models\RequestStatusHistory;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        $employees = EmployeeProfile::query()
            ->with(['user.roles', 'employeeDepartments.department', 'reportsToEmployee.user', 'financialTransactions'])
            ->latest('id')
            ->paginate(15);

        $users = User::query()
            ->whereNotIn('id', EmployeeProfile::query()->select('user_id'))
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'phone_encrypted']);

        $departments = Department::query()
            ->with(['managerEmployee.user', 'parentDepartment'])
            ->withCount('employeeDepartments')
            ->orderBy('name')
            ->get();

        $managers = EmployeeProfile::query()->with('user')->orderBy('employee_number')->get();
        $roles = Role::query()->where('is_active', true)->orderBy('name')->get();

        return view('admin.employees.index', compact('employees', 'users', 'departments', 'managers', 'roles'));
    }

    public function showEmployee($id): View
    {
        $employee = EmployeeProfile::query()
            ->with([
                'user.roles.permissions',
                'departments',
                'employeeDepartments.department',
                'reportsToEmployee.user',
                'financialTransactions.createdBy',
            ])
            ->findOrFail($id);

        $totalDebit = $employee->total_debit;
        $totalCredit = $employee->total_credit;
        $balance = $employee->balance;

        $roles = Role::query()->with('permissions')->where('is_active', true)->get();
        $permissionGroups = PermissionGroup::query()->with('permissions')->get();

        $assignedOrders = RequestAssignment::query()
            ->where('assigned_by', $employee->user_id)
            ->with(['request.service', 'craftsman'])
            ->latest('id')
            ->take(30)
            ->get();

        $statusChanges = RequestStatusHistory::query()
            ->where('changed_by', $employee->user_id)
            ->with(['request.service', 'oldStatus', 'newStatus'])
            ->latest('id')
            ->take(30)
            ->get();

        $activityLogs = AdminActivityLog::query()
            ->where('admin_id', $employee->user_id)
            ->latest('id')
            ->take(30)
            ->get();

        $departments = Department::query()->orderBy('name')->get();
        $managers = EmployeeProfile::query()->where('id', '!=', $employee->id)->with('user')->get();

        return view('admin.employees.show', compact(
            'employee',
            'totalDebit',
            'totalCredit',
            'balance',
            'roles',
            'permissionGroups',
            'assignedOrders',
            'statusChanges',
            'activityLogs',
            'departments',
            'managers'
        ));
    }

    public function storeEmployee(Request $request): RedirectResponse
    {
        $userMode = $request->input('user_mode', 'existing');

        $rules = [
            'user_mode' => 'required|in:existing,new',
            'employee_number' => 'required|string|max:30|unique:employee_profiles,employee_number',
            'job_title' => 'nullable|string|max:150',
            'reports_to_employee_id' => 'nullable|exists:employee_profiles,id',
            'employment_type' => 'required|in:FULL_TIME,PART_TIME,CONTRACT',
            'hire_date' => 'required|date',
            'termination_date' => 'nullable|date|after_or_equal:hire_date',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'nullable|exists:roles,id',
            'notes' => 'nullable|string',
        ];

        if ($userMode === 'new') {
            $rules['full_name'] = 'required|string|max:150';
            $rules['phone'] = 'required|string|max:20';
            $rules['email'] = 'nullable|email|max:150';
            $rules['password'] = 'required|string|min:6';
        } else {
            $rules['user_id'] = 'required|exists:users,id|unique:employee_profiles,user_id';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $userMode): void {
            if ($userMode === 'new') {
                $phoneHash = hash('sha256', $validated['phone']);
                if (User::where('phone_hash', $phoneHash)->exists()) {
                    abort(back()->withErrors(['phone' => 'رقم الهاتف مسجل مسبقاً في النظام']));
                }

                $user = User::create([
                    'full_name' => $validated['full_name'],
                    'phone' => $validated['phone'],
                    'email' => $validated['email'] ?? null,
                    'password' => Hash::make($validated['password']),
                    'role' => 'employee',
                    'account_status' => 'active',
                    'phone_verified_at' => now(),
                ]);
                $userId = $user->id;
            } else {
                $userId = $validated['user_id'];
                $user = User::findOrFail($userId);
            }

            if (! empty($validated['role_id'])) {
                $user->roles()->sync([$validated['role_id']]);
            }

            $departmentId = $validated['department_id'] ?? null;
            $reportsToId = $validated['reports_to_employee_id'] ?? null;

            $employee = EmployeeProfile::create([
                'user_id' => $userId,
                'employee_number' => $validated['employee_number'],
                'job_title' => $validated['job_title'] ?? null,
                'reports_to_employee_id' => $reportsToId,
                'employment_type' => $validated['employment_type'],
                'employment_status' => 'ACTIVE',
                'hire_date' => $validated['hire_date'],
                'termination_date' => $validated['termination_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($departmentId !== null) {
                EmployeeDepartment::create([
                    'employee_profile_id' => $employee->id,
                    'department_id' => $departmentId,
                    'is_primary' => true,
                    'assigned_at' => $validated['hire_date'],
                ]);
            }

            AdminActivityLog::create([
                'admin_id' => auth()->id(),
                'action' => 'employee_created',
                'description' => "قام المشرف بإضافة موظف جديد: {$user->full_name} (رقم وظيفي: {$employee->employee_number})",
                'entity_type' => 'EmployeeProfile',
                'entity_id' => $employee->id,
                'ip_address' => request()->ip(),
                'admin_role' => auth()->user()?->role ?? 'admin',
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'تم تسجيل الموظف وتحديد دوره الوظيفي بنجاح ✓');
    }

    public function updateEmployee(Request $request, $id): RedirectResponse
    {
        $employee = EmployeeProfile::with('user')->findOrFail($id);

        $validated = $request->validate([
            'employee_number' => 'required|string|max:30|unique:employee_profiles,employee_number,'.$id,
            'job_title' => 'nullable|string|max:150',
            'reports_to_employee_id' => 'nullable|exists:employee_profiles,id',
            'employment_type' => 'required|in:FULL_TIME,PART_TIME,CONTRACT',
            'employment_status' => 'required|in:ACTIVE,ON_LEAVE,TERMINATED',
            'hire_date' => 'required|date',
            'termination_date' => 'nullable|date|after_or_equal:hire_date',
            'department_id' => 'nullable|exists:departments,id',
            'role_id' => 'nullable|exists:roles,id',
            'notes' => 'nullable|string',
            'full_name' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
        ]);

        DB::transaction(function () use ($employee, $validated): void {
            $departmentId = $validated['department_id'] ?? null;
            $roleId = $validated['role_id'] ?? null;

            if ($employee->user) {
                $userData = [];
                if (! empty($validated['full_name'])) {
                    $userData['full_name'] = $validated['full_name'];
                }
                if (! empty($validated['phone'])) {
                    $userData['phone'] = $validated['phone'];
                }
                if (isset($validated['email'])) {
                    $userData['email'] = $validated['email'];
                }
                if (! empty($userData)) {
                    $employee->user->update($userData);
                }

                if ($roleId !== null) {
                    $employee->user->roles()->sync([$roleId]);
                }
            }

            $employee->update([
                'employee_number' => $validated['employee_number'],
                'job_title' => $validated['job_title'] ?? null,
                'reports_to_employee_id' => $validated['reports_to_employee_id'] ?? null,
                'employment_type' => $validated['employment_type'],
                'employment_status' => $validated['employment_status'],
                'hire_date' => $validated['hire_date'],
                'termination_date' => $validated['termination_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if ($departmentId !== null) {
                EmployeeDepartment::updateOrCreate(
                    ['employee_profile_id' => $employee->id, 'is_primary' => true],
                    ['department_id' => $departmentId, 'assigned_at' => now()->toDateString()]
                );
            }

            AdminActivityLog::create([
                'admin_id' => auth()->id(),
                'action' => 'employee_updated',
                'description' => "قام المشرف بتحديث بيانات الموظف: {$employee->user?->full_name} (#{$employee->employee_number})",
                'entity_type' => 'EmployeeProfile',
                'entity_id' => $employee->id,
                'ip_address' => request()->ip(),
                'admin_role' => auth()->user()?->role ?? 'admin',
                'created_at' => now(),
            ]);
        });

        return back()->with('success', 'تم تحديث بيانات الموظف والصلاحيات بنجاح ✓');
    }

    public function destroyEmployee($id): RedirectResponse
    {
        $employee = EmployeeProfile::with('user')->findOrFail($id);

        if ($employee->departments()->exists()) {
            return back()->withErrors(['msg' => 'لا يمكن حذف الموظف لأنه مسجل كمدير لقسم إداري. يرجى تغيير مدير القسم أولاً.']);
        }

        $empName = $employee->user?->full_name ?? $employee->employee_number;

        AdminActivityLog::create([
            'admin_id' => auth()->id(),
            'action' => 'employee_deleted',
            'description' => "قام المشرف بحذف الموظف: {$empName} (#{$employee->employee_number})",
            'entity_type' => 'EmployeeProfile',
            'entity_id' => $employee->id,
            'ip_address' => request()->ip(),
            'admin_role' => auth()->user()?->role ?? 'admin',
            'created_at' => now(),
        ]);

        EmployeeDepartment::where('employee_profile_id', $employee->id)->delete();
        $employee->financialTransactions()->delete();
        $employee->delete();

        return redirect()->route('admin.employees.index')->with('success', 'تم حذف الموظف بنجاح.');
    }

    public function storeFinancialTransaction(Request $request, $id): RedirectResponse
    {
        $employee = EmployeeProfile::with('user')->findOrFail($id);

        $validated = $request->validate([
            'transaction_type' => 'required|in:debit,credit',
            'category' => 'required|in:custody,advance,expense_settlement,salary,repayment,other',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'payment_method' => 'required|string|max:50',
            'reference_number' => 'nullable|string|max:50',
            'description' => 'required|string|max:500',
            'receipt_attachment' => 'nullable|image|max:5120',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('receipt_attachment')) {
            $path = $request->file('receipt_attachment')->store('employee_vouchers', 'public');
            $attachmentPath = 'storage/'.$path;
        }

        $refNumber = $validated['reference_number'] ?: ('VCH-'.time().'-'.rand(100, 999));

        $txn = EmployeeFinancialTransaction::create([
            'employee_profile_id' => $employee->id,
            'created_by_user_id' => auth()->id(),
            'transaction_type' => $validated['transaction_type'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'currency' => 'YER',
            'reference_number' => $refNumber,
            'transaction_date' => $validated['transaction_date'],
            'payment_method' => $validated['payment_method'],
            'receipt_attachment' => $attachmentPath,
            'description' => $validated['description'],
        ]);

        $typeArabic = $validated['transaction_type'] === 'debit' ? 'صرف عهدة/سلفة (مدين)' : 'تسوية مصاريف/سداد (دائن)';
        AdminActivityLog::create([
            'admin_id' => auth()->id(),
            'action' => 'employee_financial_voucher_created',
            'description' => "قام المشرف بإصدار سند {$typeArabic} برقم {$refNumber} للموظف {$employee->user?->full_name} بمبلغ ".number_format($validated['amount'], 2).' ر.ي',
            'entity_type' => 'EmployeeFinancialTransaction',
            'entity_id' => $txn->id,
            'ip_address' => request()->ip(),
            'admin_role' => auth()->user()?->role ?? 'admin',
            'created_at' => now(),
        ]);

        return back()->with('success', "تم تسجيل السند المالي بنجاح (#{$refNumber}) ✓");
    }

    public function updateEmployeePermissions(Request $request, $id): RedirectResponse
    {
        $employee = EmployeeProfile::with('user')->findOrFail($id);
        abort_unless($employee->user, 404, 'لا يوجد حساب مستخدم مرتبط بهذا الموظف');

        $validated = $request->validate([
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $employee->user->roles()->sync($validated['role_ids'] ?? []);

        AdminActivityLog::create([
            'admin_id' => auth()->id(),
            'action' => 'employee_roles_updated',
            'description' => "قام المدير بتحديث صلاحيات وأدوار الموظف {$employee->user->full_name}",
            'entity_type' => 'User',
            'entity_id' => $employee->user_id,
            'ip_address' => request()->ip(),
            'admin_role' => auth()->user()?->role ?? 'admin',
            'created_at' => now(),
        ]);

        return back()->with('success', 'تم حفظ وتحديث مصفوفة أدوار وصلاحيات الموظف بنجاح ✓');
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30|unique:departments,code',
            'description' => 'nullable|string',
            'parent_department_id' => 'nullable|exists:departments,id',
            'manager_employee_id' => 'nullable|exists:employee_profiles,id',
        ]);

        Department::create($validated);

        return back()->with('success', 'تم إنشاء القسم بنجاح.');
    }

    public function updateDepartment(Request $request, $id): RedirectResponse
    {
        $dept = Department::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:30|unique:departments,code,'.$id,
            'description' => 'nullable|string',
            'parent_department_id' => 'nullable|exists:departments,id|not_in:'.$id,
            'manager_employee_id' => 'nullable|exists:employee_profiles,id',
            'is_active' => 'required|boolean',
        ]);

        $dept->update($validated);

        return back()->with('success', 'تم تحديث بيانات القسم بنجاح.');
    }

    public function destroyDepartment($id): RedirectResponse
    {
        $dept = Department::findOrFail($id);

        if ($dept->employeeDepartments()->exists()) {
            return back()->withErrors(['msg' => 'لا يمكن حذف هذا القسم لوجود موظفين مرتبطين به.']);
        }

        Department::where('parent_department_id', $id)->update(['parent_department_id' => null]);
        $dept->delete();

        return back()->with('success', 'تم حذف القسم بنجاح.');
    }
}
