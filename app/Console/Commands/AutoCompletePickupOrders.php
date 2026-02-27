<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class AutoCompletePickupOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:auto-complete-pickup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto complete orders after 2 days in siap_di_pickup status if client has not confirmed pickup';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $threshold = now()->subDays(2);

        $updatedCount = Order::query()
            ->where('status', 'siap_di_pickup')
            ->whereNull('client_picked_up_at')
            ->whereNotNull('pickup_ready_at')
            ->where('pickup_ready_at', '<=', $threshold)
            ->update([
                'status' => 'selesai',
                'completed_by' => 'system',
                'admin_completed_at' => now(),
                'updated_at' => now(),
            ]);

        $this->info("Auto completed {$updatedCount} order(s).");

        return self::SUCCESS;
    }
}

