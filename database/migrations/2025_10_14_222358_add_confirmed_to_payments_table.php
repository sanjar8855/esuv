<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // ✅ Tasdiqlanganmi ustuni
            $table->boolean('confirmed')->default(false)->after('status');

            // ✅ Kim tasdiqlagan
            $table->foreignId('confirmed_by')->nullable()->after('confirmed')->constrained('users')->onDelete('set null');

            // ✅ Qachon tasdiqlangan
            $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['confirmed', 'confirmed_by', 'confirmed_at']);
        });
    }
};
