<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderDetailSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk OrderDetail.
     * 
     * Catatan: Seeder ini opsional karena OrderDetail biasanya dibuat
     * melalui relasi Order-Product. Gunakan seeder ini jika Anda ingin
     * membuat OrderDetail secara manual tanpa melalui relasi.
     */
    public function run(): void
    {
        // Ambil semua order dan produk
        $orders = Order::all();
        $products = Product::all();

        if ($orders->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Tidak ada order atau produk. Pastikan OrderSeeder dan ProductSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        // Untuk setiap order yang belum memiliki detail, tambahkan detail
        $orders->each(function ($order) use ($products) {
            // Cek apakah order sudah memiliki detail
            if ($order->details()->count() === 0) {
                // Ambil 1-3 produk acak
                $productsToAdd = $products->random(rand(1, 3));
                
                foreach ($productsToAdd as $product) {
                    OrderDetail::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => rand(5, 20),
                        'price_at_purchase' => $product->price_b2b,
                    ]);
                }
            }
        });
    }
}

