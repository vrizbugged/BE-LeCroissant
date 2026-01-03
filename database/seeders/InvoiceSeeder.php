<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk Invoice.
     */
    public function run(): void
    {
        // Ambil semua order yang belum memiliki invoice
        $orders = Order::doesntHave('invoice')->get();

        if ($orders->isEmpty()) {
            $this->command->warn('Tidak ada order tanpa invoice. Pastikan OrderSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        // Buat invoice untuk setiap order yang belum memiliki invoice
        $orders->each(function ($order) {
            Invoice::factory()->create([
                'order_id' => $order->id,
                'status' => 'unpaid',
            ]);
        });
    }
}

