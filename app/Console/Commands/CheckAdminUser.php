<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CheckAdminUser extends Command
{
    protected $signature = 'user:check {phone?}';
    protected $description = 'Userning ma\'lumotlarini tekshirish';

    public function handle()
    {
        $phone = $this->argument('phone');

        if (!$phone) {
            $phone = $this->ask('Telefon raqamni kiriting (9 ta raqam)');
        }

        $user = User::where('phone', $phone)->first();

        if (!$user) {
            $this->error("❌ Bunday telefon raqamli user topilmadi!");
            return 1;
        }

        $this->info("✅ User topildi!");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Login', $user->login ?? 'NULL'],
                ['Phone', $user->phone ?? 'NULL'],
                ['Email', $user->email ?? 'NULL'],
                ['Company ID', $user->company_id ?? 'NULL'],
                ['Company Name', $user->company->name ?? 'NULL'],
            ]
        );

        $this->info("\n🔐 Rollar:");
        $roles = $user->roles->pluck('name')->toArray();
        if (empty($roles)) {
            $this->warn("   ⚠️  ROL YO'Q!");
        } else {
            foreach ($roles as $role) {
                $this->line("   ✓ " . $role);
            }
        }

        $this->info("\n🔍 Tekshirish:");
        $this->line("   hasRole('admin'): " . ($user->hasRole('admin') ? '✅ true' : '❌ false'));
        $this->line("   hasRole('company_owner'): " . ($user->hasRole('company_owner') ? '✅ true' : '❌ false'));
        $this->line("   hasRole('employee'): " . ($user->hasRole('employee') ? '✅ true' : '❌ false'));

        return 0;
    }
}
