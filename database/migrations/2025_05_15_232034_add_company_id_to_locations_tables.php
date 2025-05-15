<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log; // Log yozish uchun

// Klass nomini fayl nomiga moslang, masalan: AddCompanyIdToLocationsTables
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tablesToModify = ['cities', 'neighborhoods', 'streets'];

        foreach ($tablesToModify as $tableName) {
            if (Schema::hasTable($tableName)) { // Jadval mavjudligini tekshirish
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'company_id')) { // Ustun hali qo'shilmagan bo'lsa
                        $table->foreignId('company_id')
                            ->nullable() // MUHIM: Avval nullable qilib qo'shamiz
                            ->after('id')  // 'id' ustunidan keyin joylashtirish (ixtiyoriy)
                            ->constrained('companies') // companies jadvaliga ishora qiladi
                            ->onDelete('cascade'); // Agar kompaniya o'chirilsa, unga tegishli joylashuvlar ham o'chiriladi
                        // Yoki ->onDelete('set null') agar null bo'lib qolishi kerak bo'lsa
                        Log::info("Added company_id to {$tableName} table.");
                    } else {
                        Log::info("company_id column already exists in {$tableName} table.");
                    }
                });
            } else {
                Log::warning("Table '{$tableName}' not found while trying to add company_id column.");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tablesToModify = ['cities', 'neighborhoods', 'streets'];

        foreach ($tablesToModify as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'company_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['company_id']); // Yoki $table->dropForeign('table_name_company_id_foreign');
                    $table->dropColumn('company_id');
                    Log::info("Dropped company_id from {$tableName} table.");
                });
            } else {
                Log::info("company_id column does not exist or table '{$tableName}' not found for dropping.");
            }
        }
    }
};
