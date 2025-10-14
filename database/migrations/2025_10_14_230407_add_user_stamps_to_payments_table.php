<?php
// database/migrations/2025_xx_xx_add_user_stamps_to_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // ✅ created_by va updated_by qo'shish
            if (!Schema::hasColumn('payments', 'created_by')) {
                $table->foreignId('created_by')
                    ->nullable()
                    ->after('confirmed_at')
                    ->constrained('users')
                    ->onDelete('set null');
            }

            if (!Schema::hasColumn('payments', 'updated_by')) {
                $table->foreignId('updated_by')
                    ->nullable()
                    ->after('created_by')
                    ->constrained('users')
                    ->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }

            if (Schema::hasColumn('payments', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
        });
    }
};
