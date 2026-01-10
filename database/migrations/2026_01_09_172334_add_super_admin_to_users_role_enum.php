<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: MySQL doesn't support adding values to ENUM directly.
     * We need to modify the entire ENUM definition.
     */
    public function up(): void
    {
        // For MySQL, we need to use raw SQL to modify ENUM
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'klien_b2b', 'super_admin') DEFAULT 'klien_b2b'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'klien_b2b') DEFAULT 'klien_b2b'");
    }
};
