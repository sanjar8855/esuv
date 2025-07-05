<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlansTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('plans')->insert([
            [
                'name' => 'Demo',
                'price' => 0,
                'customer_limit' => 10,
                'description' => 'Tizimni sinab ko\'rish uchun bepul reja. 10 tagacha mijoz qo\'shish mumkin.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Standard',
                'price' => 100000, // Misol uchun, oylik narx
                'customer_limit' => 500,
                'description' => 'Kichik va o\'rta kompaniyalar uchun standart reja.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium',
                'price' => 300000,
                'customer_limit' => 2000,
                'description' => 'Katta kompaniyalar uchun barcha imkoniyatlarga ega reja.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}