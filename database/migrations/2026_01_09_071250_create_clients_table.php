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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // Relasi ke User untuk autentikasi
            $table->string('name'); // Nama klien
            $table->string('email')->unique(); // Email klien
            $table->string('phone_number')->nullable(); // Nomor telepon
            $table->text('address')->nullable(); // Alamat
            
            // Informasi Profil Bisnis B2B
            $table->string('company_name')->nullable(); // Nama perusahaan
            $table->enum('business_sector', ['Hotel', 'Restoran', 'Event Organizer', 'Perusahaan Lain'])->nullable(); // Sektor bisnis
            $table->enum('citizenship', ['WNI', 'WNA'])->nullable(); // Kewarganegaraan
            
            // Status Akun (Pending, Aktif, Non Aktif)
            $table->enum('status', ['Pending', 'Aktif', 'Non Aktif'])->default('Pending');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
