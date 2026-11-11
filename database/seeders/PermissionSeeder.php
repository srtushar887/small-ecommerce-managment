<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view-dashboard',

            'view-shop',
            'create-shop',
            'edit-shop',
            'delete-shop',

            'view-product',
            'create-product',
            'edit-product',
            'delete-product',

            'view-shop-owner',
            'create-shop-owner',
            'edit-shop-owner',
            'delete-shop-owner',

            'view-catalog',
            'assign-catalog-product',

            'view-my-shop-product',
            'edit-my-shop-product',
            'delete-my-shop-product',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'web'],
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        $admin = Role::where('name', 'admin')->first();
        $admin?->syncPermissions(Permission::all());

        $shopOwner = Role::where('name', 'shop-owner')->first();
        $shopOwner?->syncPermissions([
            'view-dashboard',
            'view-product',
            'create-product',
            'view-catalog',
            'assign-catalog-product',
            'view-my-shop-product',
            'edit-my-shop-product',
            'delete-my-shop-product',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
