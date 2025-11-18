<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            // Roles
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',
            // Permissions
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions',
            // Products & Plans
            'view products',
            'create products',
            'edit products',
            'delete products',
            // Vouchers
            'view vouchers',
            'create vouchers',
            'edit vouchers',
            'delete vouchers',
            // ISP/Hotspot
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            'view services',
            'create services',
            'edit services',
            'delete services',
            // Billing
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',
            // Reports
            'view reports',
            // Settings
            'manage settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }
}
