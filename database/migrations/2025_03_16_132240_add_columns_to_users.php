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
            $table->string('rank')->nullable()->after('password');
            $table->string('files')->nullable()->after('rank');
            $table->date('work_start')->nullable()->after('files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rank');
            $table->dropColumn('files');
            $table->dropColumn('work_start');
        });
    }
};
