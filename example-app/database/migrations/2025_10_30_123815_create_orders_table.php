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
       Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Relasi ke Klien B2B
            $table->date('delivery_date'); // Tanggal pengiriman
            $table->string('status')->default('menunggu_konfirmasi'); // Status pesanan [cite: 103, 108]
            $table->decimal('total_price', 15, 2)->nullable(); // Total harga (bisa di-kalkulasi)
            $table->text('special_notes')->nullable(); // Catatan khusus
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
