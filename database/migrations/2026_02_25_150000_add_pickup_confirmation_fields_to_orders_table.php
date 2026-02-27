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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('completed_by')->nullable()->after('status');
            $table->timestamp('admin_completed_at')->nullable()->after('completed_by');
            $table->timestamp('client_picked_up_at')->nullable()->after('admin_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['completed_by', 'admin_completed_at', 'client_picked_up_at']);
        });
    }
};

