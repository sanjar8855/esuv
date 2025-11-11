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
        // RecordUserStamps trait ishlatadigan barcha jadvallar
        $tables = [
            'customers',
            'water_meters',
            'meter_readings',
            'payments',
            'invoices',
            'companies',
            'streets',
            'neighborhoods',
            'cities',
            'regions',
            'tariffs',
        ];

        foreach ($tables as $tableName) {
            // Agar jadval mavjud bo'lsa va ustunlar yo'q bo'lsa
            if (Schema::hasTable($tableName) && !Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('created_by')->nullable()->after('updated_at')->constrained('users')->nullOnDelete();
                    $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'customers',
            'water_meters',
            'meter_readings',
            'payments',
            'invoices',
            'companies',
            'streets',
            'neighborhoods',
            'cities',
            'regions',
            'tariffs',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'created_by')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['created_by']);
                    $table->dropForeign(['updated_by']);
                    $table->dropColumn(['created_by', 'updated_by']);
                });
            }
        }
    }
};
