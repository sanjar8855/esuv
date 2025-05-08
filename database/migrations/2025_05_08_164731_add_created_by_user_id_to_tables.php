<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log; // Log uchun

// Klass nomini fayl nomiga moslang (masalan, AddCreatedByUserIdToTables)
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // O'sha jadvallar ro'yxati
        $tables = ['customers', 'water_meters', 'meter_readings', 'invoices', 'payments', 'notifications'];

        foreach ($tables as $tableName) {
            // Agar jadval mavjud bo'lsa davom etamiz
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName){
                    // Agar bu ustun hali qo'shilmagan bo'lsa...
                    if (!Schema::hasColumn($tableName, 'created_by_user_id')) {
                        // created_by ustunini updated_by dan keyin qo'shamiz (agar u mavjud bo'lsa)
                        $afterColumn = Schema::hasColumn($tableName, 'updated_by_user_id')
                            ? 'updated_by_user_id'
                            : (Schema::hasColumn($tableName, 'id') ? 'id' : null); // Yoki id dan keyin

                        $table->foreignId('created_by_user_id')
                            ->nullable()
                            ->after($afterColumn) // Mavjud ustundan keyin joylashtirish
                            ->constrained('users') // users jadvalidagi id ga ishora qiladi
                            ->onDelete('set null'); // Agar user o'chirilsa, null bo'lib qoladi
                    }
                });
            } else {
                Log::warning("Table '{$tableName}' not found while trying to add created_by_user_id column.");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['customers', 'water_meters', 'meter_readings', 'invoices', 'payments', 'notifications'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName){
                    // Ustun mavjudligini tekshirib o'chiramiz
                    if (Schema::hasColumn($tableName, 'created_by_user_id')) {
                        try {
                            // Avval foreign key constraintni o'chirishga harakat qilamiz
                            // Nomini bilmasak, ustun nomini massivda beramiz
                            // $table->dropForeign(['created_by_user_id']);
                            // Yoki Laravel 9+ da:
                            $table->dropConstrainedForeignId('created_by_user_id');
                        } catch (\Exception $e) {
                            // Constraint topilmasa yoki boshqa xato bo'lsa, faqat ustunni o'chiramiz
                            Log::warning("Could not drop foreign key constraint for created_by_user_id on table {$tableName}. Attempting to drop column directly. Error: " . $e->getMessage());
                            $table->dropColumn('created_by_user_id');
                        }
                    }
                });
            }
        }
    }
};
