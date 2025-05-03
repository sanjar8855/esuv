<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['customers', 'water_meters', 'meter_readings', 'invoices', 'payments', 'notifications'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName){
                // 'updated_by_user_id' ustunini qo'shish
                // Agar 'id' ustuni mavjud bo'lsa (ko'pchilikda bor), undan keyin qo'shamiz
                $afterColumn = Schema::hasColumn($tableName, 'id') ? 'id' : null;
                $table->foreignId('updated_by_user_id')
                    ->nullable()
                    ->after($afterColumn) // 'id' dan keyin joylashtirish (ixtiyoriy)
                    ->constrained('users') // users jadvalidagi id ga ishora qiladi
                    ->onDelete('set null'); // Agar user o'chirilsa, null bo'lib qoladi
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['customers', 'water_meters', 'meter_readings', 'invoices', 'payments', 'notifications'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Ustunni va tashqi kalitni o'chirish
                // Nomini to'g'ri yozish muhim (table_column_foreign formatida bo'lishi mumkin)
                // $table->dropForeign(['updated_by_user_id']); // Yoki $table->dropForeign('table_name_updated_by_user_id_foreign');
                $table->dropConstrainedForeignId('updated_by_user_id'); // Laravel 9+
                // $table->dropColumn('updated_by_user_id'); // dropConstrainedForeignId buni ham bajaradi
            });
        }
    }
};
