<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyOrderHistorySeeder extends Seeder
{
    /**
     * Jalankan seeder untuk membuat order dummy dengan status berbeda
     * untuk melihat riwayat transaksi di my-transactions page.
     */
    public function run(): void
    {
        // Ambil semua client (atau bisa juga ambil client tertentu)
        $clients = Client::with('user')->get();
        
        // Ambil semua produk
        $products = Product::all();

        // Pastikan ada client dan produk sebelum membuat order
        if ($clients->isEmpty() || $products->isEmpty()) {
            $this->command->warn('Tidak ada client atau produk. Pastikan ClientSeeder dan ProductSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        // Status yang akan digunakan
        $statuses = ['menunggu_konfirmasi', 'diproses', 'selesai', 'dibatalkan'];
        
        // Buat 8-10 order dummy untuk setiap client (atau client pertama jika ingin fokus)
        $targetClient = $clients->first(); // Ambil client pertama, atau bisa loop semua client
        
        if (!$targetClient || !$targetClient->user) {
            $this->command->warn('Client tidak memiliki user. Pastikan ClientSeeder sudah dijalankan.');
            return;
        }

        $this->command->info("Membuat order dummy untuk client: {$targetClient->name} (ID: {$targetClient->id})");

        // Buat 10 order dengan status dan tanggal berbeda
        for ($i = 0; $i < 10; $i++) {
            // Pilih status secara berurutan atau random
            $status = $statuses[$i % count($statuses)];
            
            // Buat tanggal created_at yang berbeda (semakin lama semakin ke belakang)
            // Order pertama (i=0) adalah yang terbaru, order terakhir (i=9) adalah yang paling lama
            $daysAgo = 30 - ($i * 3); // 30 hari lalu sampai sekarang, dengan interval 3 hari
            $createdAt = now()->subDays($daysAgo);
            $updatedAt = $createdAt->copy()->addHours(rand(1, 24)); // Update sedikit setelah created
            
            // Buat order
            $order = Order::create([
                'user_id' => $targetClient->user_id,
                'client_id' => $targetClient->id,
                'delivery_date' => $createdAt->copy()->addDays(7)->format('Y-m-d'),
                'status' => $status,
                'total_price' => 0, // Akan diupdate setelah attach produk
                'special_notes' => $i % 3 === 0 ? fake()->sentence() : null, // Beberapa order punya catatan
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            // Attach produk ke order (1-3 produk per order)
            $productsToAttach = $products->random(rand(1, 3));
            $totalPrice = 0;

            foreach ($productsToAttach as $product) {
                // Quantity minimal 10 untuk B2B
                $quantity = rand(10, 30);
                $priceAtPurchase = $product->price_b2b;

                // Attach produk ke order melalui pivot table
                $order->products()->attach($product->id, [
                    'quantity' => $quantity,
                    'price_at_purchase' => $priceAtPurchase
                ]);

                // Hitung total harga
                $totalPrice += $priceAtPurchase * $quantity;
            }

            // Update total price
            $order->update(['total_price' => $totalPrice]);
            
            $this->command->info("  - Order #{$order->id} dibuat dengan status: {$status}, tanggal: {$createdAt->format('Y-m-d')}, total: Rp " . number_format($totalPrice, 0, ',', '.'));
        }

        $this->command->info("Berhasil membuat 10 order dummy untuk riwayat transaksi.");
    }
}
