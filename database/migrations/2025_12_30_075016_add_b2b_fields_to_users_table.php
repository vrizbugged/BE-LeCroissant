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
        Schema::table('users', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('name');
            $table->enum('business_sector', ['Hotel', 'Restoran', 'Event Organizer', 'Perusahaan Lain'])->nullable()->after('company_name');
            $table->enum('citizenship', ['WNI', 'WNA'])->nullable()->after('business_sector');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'business_sector', 'citizenship']);
        });
    }
};
