<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run()
    {

        DB::table('regions')->insert([
            ['name' => 'Region 1'],
            ['name' => 'Region 2'],
        ]);

        DB::table('cities')->insert([
            ['region_id' => 1, 'name' => 'City A'],
            ['region_id' => 2, 'name' => 'City B'],
        ]);

        DB::table('neighborhoods')->insert([
            ['city_id' => 1, 'name' => 'Neighborhood X'],
            ['city_id' => 2, 'name' => 'Neighborhood Y'],
        ]);

        DB::table('streets')->insert([
            ['neighborhood_id' => 1, 'name' => 'Street K 1'],
            ['neighborhood_id' => 1, 'name' => 'Street K 2'],
            ['neighborhood_id' => 2, 'name' => 'Street J'],
        ]);

        DB::table('companies')->insert([
            [
                'name' => 'company 1',
                'email' => 'info1@waterco.com',
                'phone' => '122000',
                'plan' => 'premium',
                'address' => '1',
                'is_active' => true,
            ],
        ]);

        DB::table('companies')->insert([
            [
                'name' => 'company 2',
                'email' => 'info2@waterco.com',
                'phone' => '123000',
                'plan' => 'premium',
                'address' => '1',
                'is_active' => true,
            ],
        ]);

        DB::table('customers')->insert([
            [
                'company_id' => 1,
                'street_id' => 1,
                'name' => 'test1',
                'phone' => '1001',
                'telegram_chat_id' => '1001',
                'address' => 'House 1, Street 2',
                'account_number' => 100001,
                'has_water_meter' => true,
                'family_members' => 4,
                'is_active' => true,
            ],
        ]);

        DB::table('customers')->insert([
            [
                'company_id' => 1,
                'street_id' => 2,
                'name' => 'test2',
                'phone' => '1002',
                'telegram_chat_id' => '1002',
                'address' => 'House 1, Street 2',
                'account_number' => 100002,
                'has_water_meter' => false,
                'family_members' => 4,
                'is_active' => true,
            ],
        ]);

        DB::table('customers')->insert([
            [
                'company_id' => 2,
                'street_id' => 3,
                'name' => 'test3',
                'phone' => '1003',
                'telegram_chat_id' => '1003',
                'address' => 'House 1, Street 2',
                'account_number' => 100003,
                'has_water_meter' => false,
                'family_members' => 4,
                'is_active' => true,
            ],
        ]);

        DB::table('water_meters')->insert([
            [
                'customer_id' => 1,
                'meter_number' => 5001,
                'last_reading_date' => Carbon::now()->subMonth(),
                'installation_date' => Carbon::now()->subYear(),
            ],
        ]);

        DB::table('meter_readings')->insert([
            [
                'water_meter_id' => 1,
                'reading' => 689,
                'reading_date' => Carbon::now()->subMonth()->startOfMonth(),
                'confirmed' => true
            ],
        ]);

        DB::table('meter_readings')->insert([
            [
                'water_meter_id' => 1,
                'reading' => 725,
                'reading_date' => Carbon::now()->startOfMonth(),
                'confirmed' => true
            ],
        ]);

        DB::table('tariffs')->insert([
            [
                'company_id' => 1,
                'name' => 'Standard Tariff',
                'price_per_m3' => 4000,
                'for_one_person' => 5000,
                'valid_from' => Carbon::now()->subYear(),
                'is_active' => true,
            ],
        ]);

        DB::table('invoices')->insert([
            [
                'customer_id' => 1,
                'tariff_id' => 1,
                'invoice_number' => '2025-100000',
                'billing_period' => '2025-01',
                'amount_due' => 15000,
                'due_date' => Carbon::now()->addMonth(),
                'status' => 'pending',
            ],
        ]);

        DB::table('payments')->insert([
            [
                'invoice_id' => 1,
                'customer_id' => 1,
                'amount' => 5000,
                'payment_date' => Carbon::now(),
                'payment_method' => 'card',
                'status' => 'completed',
            ],
        ]);

        DB::table('notifications')->insert([
            [
                'customer_id' => 1,
                'type' => 'reminder',
                'message' => 'Your bill is due soon.',
                'sent_at' => Carbon::now(),
            ],
        ]);

        $this->call(RolePermissionSeeder::class);

    }
}
