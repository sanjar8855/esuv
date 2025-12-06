<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('saas_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->decimal('amount', 12, 2); // To'lov miqdori
            $table->date('payment_date'); // To'lov qilingan sana
            $table->string('payment_period', 7); // Qaysi oy uchun to'lov qilingani (Format: YYYY-MM)
            $table->string('payment_method')->nullable(); // To'lov usuli (naqd, click, payme...)
            $table->text('notes')->nullable(); // Admin uchun izohlar
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('saas_payments');
    }
};