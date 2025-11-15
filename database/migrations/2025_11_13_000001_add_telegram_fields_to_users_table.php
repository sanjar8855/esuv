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
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_username')->nullable()->after('password');
            $table->bigInteger('telegram_user_id')->nullable()->unique()->after('telegram_username');
            // phone field allaqachon 2025_04_28 migration'da qo'shilgan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telegram_username', 'telegram_user_id']);
            // phone field eski migration'da qolsin
        });
    }
};
