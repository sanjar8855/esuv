<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Masalan, "Demo", "Standard", "Premium"
            $table->decimal('price', 10, 2)->default(0); // Oylik to'lov narxi
            $table->integer('customer_limit')->default(10); // Maksimal mijozlar soni
            $table->text('description')->nullable(); // Tarif tavsifi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('plans');
    }
};