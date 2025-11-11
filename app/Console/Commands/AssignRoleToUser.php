<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignRoleToUser extends Command
{
    protected $signature = 'user:assign-role {phone?} {role?}';
    protected $description = 'Userga rol berish';

    public function handle()
    {
        // Telefon raqamni olish
        $phone = $this->argument('phone');
        if (!$phone) {
            $phone = $this->ask('Telefon raqamni kiriting (9 ta raqam)');
        }

        // Userni topish
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            $this->error("❌ Bunday telefon raqamli user topilmadi!");
            return 1;
        }

        $this->info("✅ User topildi: {$user->name} (ID: {$user->id})");

        // Mavjud rollarni ko'rsatish
        $this->info("\n📋 Hozirgi rollar:");
        $currentRoles = $user->roles->pluck('name')->toArray();
        if (empty($currentRoles)) {
            $this->warn("   ⚠️  Hech qanday rol yo'q");
        } else {
            foreach ($currentRoles as $role) {
                $this->line("   ✓ " . $role);
            }
        }

        // Rolni olish
        $roleName = $this->argument('role');
        if (!$roleName) {
            $availableRoles = Role::pluck('name')->toArray();
            $this->info("\n🔐 Mavjud rollar:");
            foreach ($availableRoles as $role) {
                $this->line("   • " . $role);
            }
            $roleName = $this->choice('Qaysi rolni bermoqchisiz?', $availableRoles);
        }

        // Rolni tekshirish
        $role = Role::where('name', $roleName)->first();
        if (!$role) {
            $this->error("❌ Bunday rol topilmadi!");
            return 1;
        }

        // Rolni berish
        if ($user->hasRole($roleName)) {
            $this->warn("⚠️  User allaqachon '{$roleName}' roli bilan!");
        } else {
            $user->assignRole($roleName);
            $this->info("✅ '{$roleName}' roli berildi!");
        }

        // Yakuniy natija
        $this->info("\n📊 Yangilangan rollar:");
        $updatedRoles = $user->fresh()->roles->pluck('name')->toArray();
        foreach ($updatedRoles as $role) {
            $this->line("   ✓ " . $role);
        }

        return 0;
    }
}
