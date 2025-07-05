<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('companies', function (Blueprint $table) {
            // Avvalgi 'plan' (enum) ustunini olib tashlash (agar kerak bo'lmasa)
            if (Schema::hasColumn('companies', 'plan')) {
                $table->dropColumn('plan');
            }
            // Yangi plan_id ustunini qo'shish
            $table->foreignId('plan_id')->nullable()->after('id')->constrained('plans')->onDelete('set null');
        });
    }
    public function down(): void {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            // Agar orqaga qaytarsa, avvalgi 'plan' (enum) ustunini qayta tiklash mumkin
            // $table->enum('plan', ['basic', 'premium'])->after('id');
        });
    }
};