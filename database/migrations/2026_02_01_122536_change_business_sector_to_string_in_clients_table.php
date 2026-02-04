<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mengubah enum business_sector menjadi string untuk fleksibilitas
        DB::statement('ALTER TABLE `clients` MODIFY `business_sector` VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke enum (opsional, jika perlu rollback)
        DB::statement("ALTER TABLE `clients` MODIFY `business_sector` ENUM('Hotel', 'Restoran', 'Event Organizer', 'Perusahaan Lain') NULL");
    }
};
