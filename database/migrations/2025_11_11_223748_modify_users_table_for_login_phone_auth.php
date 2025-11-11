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
            // Login ustunini qo'shish (unique)
            $table->string('login', 50)->unique()->nullable()->after('name');

            // Email'ni nullable qilish va unique'ni olib tashlash
            $table->string('email')->nullable()->change();
        });

        // Email unique constraint'ni olib tashlash
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Login ustunini olib tashlash
            $table->dropUnique(['login']);
            $table->dropColumn('login');

            // Email'ni qayta majburiy qilish
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });
    }
};
