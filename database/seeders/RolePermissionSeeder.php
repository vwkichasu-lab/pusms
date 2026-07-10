<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * @var array<int, string>
     */
    private array $permissions = [
        'view students',
        'create students',
        'update students',
        'delete students',
        'import students',
        'export students',
        'view scholarships',
        'manage scholarship programmes',
        'assign scholarships',
        'update scholarship status',
        'view renewals',
        'review renewals',
        'approve renewals',
        'send email',
        'send sms',
        'manage message templates',
        'view communication history',
        'retry failed messages',
        'view reports',
        'export reports',
        'manage academic settings',
        'manage users',
        'manage roles',
        'view audit logs',
        'manage system settings',
    ];

    /**
     * @var array<string, array<int, string>>
     */
    private array $rolePermissions = [
        'Super Administrator' => ['*'],
        'Scholarship Secretary' => [
            'view students',
            'create students',
            'update students',
            'delete students',
            'import students',
            'export students',
            'view scholarships',
            'assign scholarships',
            'update scholarship status',
            'view renewals',
            'review renewals',
            'send email',
            'send sms',
            'manage message templates',
            'view communication history',
            'retry failed messages',
            'view reports',
            'export reports',
        ],
        'Committee Chairman' => [
            'view students',
            'view scholarships',
            'view renewals',
            'review renewals',
            'approve renewals',
            'view communication history',
            'view reports',
            'export reports',
        ],
        'Committee Member' => [
            'view students',
            'view scholarships',
            'view renewals',
            'review renewals',
            'view reports',
        ],
        'Read-Only Officer' => [
            'view students',
            'view scholarships',
            'view renewals',
            'view communication history',
            'view reports',
        ],
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach ($this->rolePermissions as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);

            $role->syncPermissions($permissions === ['*'] ? $this->permissions : $permissions);
        }
    }
}
