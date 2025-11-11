<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class CleanPhoneNumbers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:clean-phones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Telefon raqamlarini tozalash: (90) 123-45-67 -> 901234567';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Telefon raqamlarini tekshirilmoqda...');

        // Barcha mijozlarni olish
        $customers = Customer::withoutGlobalScopes()->whereNotNull('phone')->get();

        $this->info("✅ Jami mijozlar: " . Customer::withoutGlobalScopes()->count());
        $this->info("📞 Telefon bor: " . $customers->count());

        $updated = 0;
        $alreadyClean = 0;

        foreach ($customers as $customer) {
            $originalPhone = $customer->phone;

            // Faqat raqamlarni ajratib olamiz
            $cleanedPhone = preg_replace('/[^0-9]/', '', $originalPhone);

            // Agar o'zgardi bo'lsa
            if ($originalPhone !== $cleanedPhone && strlen($cleanedPhone) === 9) {
                // To'g'ridan-to'g'ri DB ga yozamiz (mutatorni bypass qilamiz)
                \DB::table('customers')
                    ->where('id', $customer->id)
                    ->update(['phone' => $cleanedPhone]);

                $updated++;
                $this->line("✏️  ID {$customer->id}: {$originalPhone} -> {$cleanedPhone}");
            } else {
                $alreadyClean++;
            }
        }

        $this->newLine();
        $this->info("✅ Tozalandi: {$updated} ta");
        $this->info("✅ Avvaldan toza: {$alreadyClean} ta");
        $this->info("🎉 Jarayon tugadi!");

        return 0;
    }
}
