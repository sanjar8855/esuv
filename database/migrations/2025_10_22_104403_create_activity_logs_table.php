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
        if (Schema::hasTable('activity_logs')) {
            return; // Jadval allaqachon mavjud
        }

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable(); // login, customer, invoice, payment, etc.
            $table->text('description'); // "Created customer", "Updated invoice", etc.
            $table->nullableMorphs('subject'); // Modelga bog'lanish (customer, invoice, etc.) - avtomatik index yaratadi
            $table->nullableMorphs('causer'); // Kim qilgan (user) - avtomatik index yaratadi
            $table->json('properties')->nullable(); // Qo'shimcha ma'lumotlar (old values, new values)
            $table->string('event')->nullable(); // created, updated, deleted, login, logout
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Index'lar (nullableMorphs allaqachon subject va causer uchun index yaratadi)
            $table->index('log_name');
            $table->index('event');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
