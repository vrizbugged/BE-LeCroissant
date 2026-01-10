<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk Order.
     */
    public function run(): void
    {
        // Ambil semua client (yang sudah dibuat oleh ClientSeeder)
        $clients = Client::all();
        
        // Ambil semua produk
        $products = Product::all();

        // Pastikan ada client dan produk sebelum membuat order
        if ($clients->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Tidak ada client atau produk. Pastikan ClientSeeder dan ProductSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        // Buat 30 Order
        Order::factory()
            ->count(30)
            ->create([
                'client_id' => $clients->random()->id,
            ])
            ->each(function ($order) use ($products) {
                // Untuk setiap order, tambahkan 1-3 produk
                $productsToAttach = $products->random(rand(1, 3));
                $totalPrice = 0;

                foreach ($productsToAttach as $product) {
                    $quantity = rand(5, 20); // Kuantitas B2B (agak banyak)
                    $priceAtPurchase = $product->price_b2b;

                    // Attach produk ke order melalui pivot table
                    $order->products()->attach($product->id, [
                        'quantity' => $quantity,
                        'price_at_purchase' => $priceAtPurchase
                    ]);

                    // Hitung total harga
                    $totalPrice += $priceAtPurchase * $quantity;
                }

                // Update total harga order
                $order->update(['total_price' => $totalPrice]);
            });
    }
}

