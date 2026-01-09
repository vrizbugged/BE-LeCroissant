<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah orders.user_id menjadi orders.client_id untuk relasi yang lebih jelas.
     */
    public function up(): void
    {
        // Tambahkan kolom client_id baru
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('client_id')->nullable()->after('id')->constrained('clients')->onDelete('cascade');
        });

        // Migrasi data: untuk setiap order dengan user yang memiliki role 'klien_b2b',
        // cari atau buat client yang sesuai, lalu update client_id
        // Note: Migration ini mengasumsikan bahwa client sudah dibuat sebelumnya
        // Jika belum ada, perlu dibuat manual atau melalui seeder
        
        // Setelah data dimigrasi (jika ada), hapus kolom user_id lama
        // Untuk sementara, kita biarkan user_id tetap ada untuk backward compatibility
        // Bisa dihapus nanti setelah semua data sudah dimigrasi
        
        // Schema::table('orders', function (Blueprint $table) {
        //     $table->dropForeign(['user_id']);
        //     $table->dropColumn('user_id');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus kolom client_id
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn('client_id');
        });
    }
};
