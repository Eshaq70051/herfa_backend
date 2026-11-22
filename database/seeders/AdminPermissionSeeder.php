<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminPermissionSeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const GROUPS = [
        'dashboard' => 'لوحة التحكم',
        'orders' => 'طلبات الخدمة',
        'craftsmen' => 'الحرفيون',
        'catalog' => 'الكتالوج',
        'stores' => 'المتاجر والخدمات الشريكة',
        'clients' => 'العملاء',
        'locations' => 'المواقع',
        'marketing' => 'التسويق',
        'financials' => 'المالية',
        'reviews' => 'التقييمات',
        'support' => 'الدعم الفني',
        'settings' => 'الإعدادات',
        'accounting' => 'المحاسبة',
        'suppliers' => 'الموردون والمشتريات',
        'employees' => 'الموارد البشرية',
        'requests' => 'إدارة الطلبات',
        'notifications' => 'الإشعارات',
        'users' => 'المستخدمون والأدوار',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $permissionIds = [];

            foreach (self::GROUPS as $code => $name) {
                $group = PermissionGroup::updateOrCreate(
                    ['code' => $code],
                    ['name' => $name, 'sort_order' => count($permissionIds)],
                );

                $actions = $code === 'dashboard'
                    ? ['view' => 'عرض']
                    : ['view' => 'عرض', 'manage' => 'إدارة'];

                foreach ($actions as $action => $actionName) {
                    $permission = Permission::updateOrCreate(
                        ['code' => "admin.{$code}.{$action}"],
                        [
                            'permission_group_id' => $group->id,
                            'name' => "{$actionName} {$name}",
                            'is_active' => true,
                        ],
                    );

                    $permissionIds[] = $permission->id;
                }
            }

            $administratorRole = Role::updateOrCreate(
                ['code' => 'super_admin'],
                [
                    'name' => 'مدير النظام',
                    'description' => 'صلاحية كاملة لإدارة النظام.',
                    'is_system' => true,
                    'is_active' => true,
                ],
            );
            $administratorRole->permissions()->sync($permissionIds);

            User::query()->get()->each(function (User $user) use ($administratorRole): void {
                if ($user->getAttribute('role') === 'admin') {
                    $user->roles()->syncWithoutDetaching([$administratorRole->id]);
                }
            });
        });
    }
}
