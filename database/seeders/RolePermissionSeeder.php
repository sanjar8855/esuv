<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ⚡ Avval barcha rollar va ruxsatnomalarni tozalash
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 🎯 Ruxsatnomalar (Permissions)
        $permissions = [
            'manage companies',
            'manage users',
            'manage customers',
            'manage invoices',
            'manage payments',
            'view audit logs',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 🎯 Rollar (Roles)
        $roles = [
            'admin' => Permission::all(), // Barcha ruxsatlar
            'company_owner' => Permission::whereIn('name', [
                'manage customers',
                'manage invoices',
                'manage payments',
                'view audit logs',
            ])->get(),
            'customer' => Permission::whereIn('name', [
                'manage invoices',
                'manage payments',
            ])->get(),
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::create(['name' => $roleName]);
            $role->syncPermissions($perms);
        }

        // 🚀 Test uchun Super Admin foydalanuvchisini yaratish
        $superAdmin = User::create([
            'company_id' => null,
            'name' => 'sanjar',
            'email' => 'sanjar@mail.ru',
            'password' => bcrypt('123456'), // Xavfsizlik uchun real loyihada mustahkam parol ishlating
        ]);
        $superAdmin->assignRole('admin');
    }
}
