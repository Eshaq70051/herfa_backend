<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('account_status')) {
            $query->where('account_status', $request->account_status);
        }

        if ($request->filled('role_id')) {
            $query->whereHas('roles', fn ($q) => $q->where('roles.id', $request->role_id));
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $h = User::hashIdentifier($s);
            $query->where(function ($q) use ($s, $h) {
                $q->where('full_name', 'like', "%{$s}%")
                    ->orWhere('email_encrypted', 'like', "%{$s}%")
                    ->orWhere('phone_encrypted', 'like', "%{$s}%")
                    ->orWhere('email_hash', $h)
                    ->orWhere('phone_hash', $h);
            });
        }

        $users = $query->latest('id')->paginate(15);
        $roles = Role::where('is_active', 1)->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'account_status' => 'required|in:active,inactive,suspended',
        ]);

        $phoneHash = User::hashIdentifier($request->phone);
        $emailHash = $request->filled('email') ? User::hashIdentifier($request->email) : null;

        // التحقق من عدم تكرار الهاتف أو البريد عبر التجزئة الآمنة
        if (User::where('phone_hash', $phoneHash)->exists()) {
            return back()->withErrors(['phone' => 'رقم الهاتف هذا مسجل مسبقاً'])->withInput();
        }
        if ($emailHash && User::where('email_hash', $emailHash)->exists()) {
            return back()->withErrors(['email' => 'البريد الإلكتروني هذا مسجل مسبقاً'])->withInput();
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'phone_encrypted' => $request->phone,
            'phone_hash' => $phoneHash,
            'email_encrypted' => $request->email,
            'email_hash' => $emailHash,
            'password' => Hash::make($request->password),
            'account_status' => $request->account_status,
            'phone_verified_at' => now(),
            'email_verified_at' => $request->filled('email') ? now() : null,
        ]);

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $request->role_id,
            'assigned_by' => Auth::id(),
        ]);

        return back()->with('success', 'تمت إضافة المستخدم بنجاح وتعيين الدور الخاص به.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'account_status' => 'required|in:active,inactive,suspended',
        ]);

        $user = User::findOrFail($id);
        $user->account_status = $request->account_status;
        $user->save();

        return back()->with('success', 'تم تعديل حالة الحساب بنجاح.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'role_id' => 'required|exists:roles,id',
            'account_status' => 'required|in:active,inactive,suspended',
        ]);

        $user = User::findOrFail($id);
        $phoneHash = User::hashIdentifier($request->phone);
        $emailHash = $request->filled('email') ? User::hashIdentifier($request->email) : null;

        $user->full_name = $request->full_name;
        $user->phone_encrypted = $request->phone;
        $user->phone_hash = $phoneHash;
        $user->email_encrypted = $request->email;
        $user->email_hash = $emailHash;
        $user->account_status = $request->account_status;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        // تحديث الصلاحية والرتبة الإدارية للمستخدم
        UserRole::where('user_id', $user->id)->delete();
        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $request->role_id,
            'assigned_by' => Auth::id(),
        ]);

        return back()->with('success', 'تم تحديث بيانات المستخدم والدور بنجاح.');
    }

    public function destroy($id)
    {
        if ($id == Auth::id()) {
            return back()->withErrors(['msg' => 'لا يمكنك حذف حسابك الحالي أثناء تسجيل الدخول به.']);
        }

        $user = User::findOrFail($id);
        UserRole::where('user_id', $user->id)->delete();
        $user->delete();

        return back()->with('success', 'تم حذف حساب المستخدم بنجاح.');
    }

    public function roles()
    {
        $roles = Role::withCount('users')->with('permissions')->get();
        $permissionGroups = PermissionGroup::with('permissions')->get();

        return view('admin.users.roles', compact('roles', 'permissionGroups'));
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|unique:roles,code',
            'description' => 'nullable|string',
        ]);

        Role::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_system' => 0,
            'is_active' => 1,
        ]);

        return back()->with('success', 'تمت إضافة الدور الجديد بنجاح.');
    }

    public function updateRole(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        if ($role->is_system) {
            return back()->withErrors(['msg' => 'لا يمكن تعديل أدوار النظام الأساسية.']);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $role->update($request->only('name', 'description'));

        return back()->with('success', 'تم تعديل الدور بنجاح.');
    }

    public function destroyRole($id)
    {
        $role = Role::findOrFail($id);
        if ($role->is_system) {
            return back()->withErrors(['msg' => 'لا يمكن حذف أدوار النظام الأساسية.']);
        }
        if ($role->users()->count() > 0) {
            return back()->withErrors(['msg' => 'لا يمكن حذف هذا الدور لوجود مستخدمين معينين عليه.']);
        }

        $role->delete();

        return back()->with('success', 'تم حذف الدور بنجاح.');
    }

    public function updateRolePermissions(Request $request, int $id): RedirectResponse
    {
        $role = Role::findOrFail($id);
        if ($role->is_system) {
            return back()->withErrors(['msg' => 'لا يمكن تعديل صلاحيات أدوار النظام الأساسية.']);
        }

        $validated = $request->validate([
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['integer', 'distinct', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync($validated['permission_ids'] ?? []);

        return back()->with('success', 'تم تحديث صلاحيات الدور بنجاح.');
    }
}
