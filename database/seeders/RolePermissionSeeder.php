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
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        Permission::truncate();
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 🎯 Ruxsatnomalar (Permissions)
//        $permissions = [
//            'dashboard',
//            'locations',
//            'companies',
//            'users',
//            'tariffs',
//            'customers',
//            'water_meters',
//            'meter_readings',
//            'invoices',
//            'payments',
//            'notifications',
//            'audit_logs',
//        ];

        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
            }
        }

        // 🎯 Rollar (Roles)
        $roles = [
            'admin' => Permission::all(), // Barcha ruxsatlar
            'company_owner' => Permission::whereIn('name', [
                'dashboard',
                'users',
                'tariffs',
                'customers',
                'water_meters',
                'meter_readings',
                'invoices',
                'payments',
                'notifications',
            ])->get(),
            'employee' => Permission::whereIn('name', [
                'tariffs',
                'customers',
                'water_meters',
                'meter_readings',
                'invoices',
                'payments',
                'notifications'
            ])->get(),
            'customer' => Permission::whereIn('name', [
                'customer_info'
            ])->get()
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

        $user = User::create([
            'company_id' => 1,
            'name' => 'User',
            'email' => 'user@mail.ru',
            'password' => bcrypt('123456'),
        ]);

        $user->assignRole('company_owner');
    }
}
