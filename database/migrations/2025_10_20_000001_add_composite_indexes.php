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
        // ✅ Customers jadvali - company_id va is_active bo'yicha tez-tez qidiriladi
        Schema::table('customers', function (Blueprint $table) {
            if (!$this->indexExists('customers', 'customers_company_id_is_active_index')) {
                $table->index(['company_id', 'is_active'], 'customers_company_id_is_active_index');
            }
        });

        // ✅ Invoices jadvali - customer_id va status bo'yicha tez-tez qidiriladi
        Schema::table('invoices', function (Blueprint $table) {
            if (!$this->indexExists('invoices', 'invoices_customer_id_status_index')) {
                $table->index(['customer_id', 'status'], 'invoices_customer_id_status_index');
            }
        });

        // ✅ Payments jadvali - customer_id va confirmed bo'yicha tez-tez qidiriladi
        Schema::table('payments', function (Blueprint $table) {
            if (!$this->indexExists('payments', 'payments_customer_id_confirmed_index')) {
                $table->index(['customer_id', 'confirmed'], 'payments_customer_id_confirmed_index');
            }
        });

        // ✅ Meter readings - water_meter_id va confirmed bo'yicha
        Schema::table('meter_readings', function (Blueprint $table) {
            if (!$this->indexExists('meter_readings', 'meter_readings_water_meter_id_confirmed_index')) {
                $table->index(['water_meter_id', 'confirmed'], 'meter_readings_water_meter_id_confirmed_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if ($this->indexExists('customers', 'customers_company_id_is_active_index')) {
                $table->dropIndex('customers_company_id_is_active_index');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if ($this->indexExists('invoices', 'invoices_customer_id_status_index')) {
                $table->dropIndex('invoices_customer_id_status_index');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if ($this->indexExists('payments', 'payments_customer_id_confirmed_index')) {
                $table->dropIndex('payments_customer_id_confirmed_index');
            }
        });

        Schema::table('meter_readings', function (Blueprint $table) {
            if ($this->indexExists('meter_readings', 'meter_readings_water_meter_id_confirmed_index')) {
                $table->dropIndex('meter_readings_water_meter_id_confirmed_index');
            }
        });
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $indexes = $connection->select(
            "SHOW INDEX FROM {$table} WHERE Key_name = ?",
            [$indexName]
        );

        return count($indexes) > 0;
    }
};
