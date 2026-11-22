<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminPermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name')->nullable();
            $table->string('phone_encrypted')->nullable();
            $table->string('phone_hash')->nullable();
            $table->string('email_encrypted')->nullable();
            $table->string('email_hash')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->default('customer');
            $table->string('account_status')->default('active');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('permission_groups', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('permission_group_id');
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('permission_id');
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
        });
    }

    public function test_admin_route_forbids_authenticated_users_without_permission(): void
    {
        $user = User::create([
            'full_name' => 'Regular user',
            'phone' => '0500000001',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)->get('/admin/users/roles');

        $response->assertForbidden();
    }

    public function test_employee_routes_require_the_employee_view_permission(): void
    {
        $user = User::create([
            'full_name' => 'Regular user',
            'phone' => '0500000004',
            'password' => 'password',
            'role' => 'customer',
        ]);

        $this->actingAs($user)
            ->get('/admin/employees')
            ->assertForbidden();
    }

    public function test_admin_route_allows_users_with_the_matching_role_permission(): void
    {
        $group = PermissionGroup::create([
            'name' => 'Users',
            'code' => 'users',
        ]);
        $permission = Permission::create([
            'permission_group_id' => $group->id,
            'name' => 'View users',
            'code' => 'admin.users.view',
        ]);
        $role = Role::create([
            'name' => 'Support',
            'code' => 'support',
        ]);
        $role->permissions()->attach($permission);
        $user = User::create([
            'full_name' => 'Support user',
            'phone' => '0500000002',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->get('/admin/users/roles');

        $response->assertOk();
        $response->assertViewIs('admin.users.roles');
        $response->assertDontSee('إضافة دور جديد');

        $this->actingAs($user)
            ->post('/admin/users/roles', [])
            ->assertForbidden();
    }

    public function test_admin_route_allows_role_managers_to_reach_role_validation(): void
    {
        $group = PermissionGroup::create([
            'name' => 'Users',
            'code' => 'users',
        ]);
        $permission = Permission::create([
            'permission_group_id' => $group->id,
            'name' => 'Manage users',
            'code' => 'admin.users.manage',
        ]);
        $role = Role::create([
            'name' => 'Role manager',
            'code' => 'role_manager',
        ]);
        $role->permissions()->attach($permission);
        $user = User::create([
            'full_name' => 'Role manager',
            'phone' => '0500000003',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)->post('/admin/users/roles', []);

        $response->assertRedirectBackWithErrors(['name', 'code']);
    }

    public function test_user_without_dashboard_permission_redirects_to_first_permitted_route(): void
    {
        $group = PermissionGroup::create([
            'name' => 'Accounting',
            'code' => 'accounting',
        ]);
        $permission = Permission::create([
            'permission_group_id' => $group->id,
            'name' => 'View Accounting',
            'code' => 'admin.accounting.view',
        ]);
        $role = Role::create([
            'name' => 'Accountant',
            'code' => 'accountant',
        ]);
        $role->permissions()->attach($permission);
        $user = User::create([
            'full_name' => 'Accountant User',
            'phone' => '0500000005',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $user->roles()->attach($role);

        // Visiting dashboard without dashboard.view permission should redirect to accounting.accounts
        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect(route('admin.accounting.accounts'));
    }

    public function test_forbidden_routes_return_403_for_unauthorized_user(): void
    {
        $group = PermissionGroup::create([
            'name' => 'Accounting',
            'code' => 'accounting',
        ]);
        $permission = Permission::create([
            'permission_group_id' => $group->id,
            'name' => 'View Accounting',
            'code' => 'admin.accounting.view',
        ]);
        $role = Role::create([
            'name' => 'Accountant',
            'code' => 'accountant',
        ]);
        $role->permissions()->attach($permission);
        $user = User::create([
            'full_name' => 'Accountant User',
            'phone' => '0500000006',
            'password' => 'password',
            'role' => 'customer',
        ]);
        $user->roles()->attach($role);

        // Accessing settings without permission must be forbidden
        $this->actingAs($user)->get('/admin/settings')->assertForbidden();
    }
}
