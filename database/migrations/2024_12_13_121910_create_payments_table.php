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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->decimal('amount', 10, 2);  // Menambahkan jumlah pembayaran
            $table->string('method');
            $table->string('virtual_account')->nullable();
            $table->string('payment_proof')->nullable();  // Untuk bukti pembayaran
            $table->timestamp('payment_deadline')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'expired'])->default('pending');
            $table->text('payment_details')->nullable();  // Untuk menyimpan detail tambahan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
