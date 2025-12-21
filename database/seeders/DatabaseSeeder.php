<?php

namespace Database\Seeders;

// Tambahkan model-model ini di bagian atas
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Content;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Penting untuk password

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan database seeders.
     */
    public function run(): void
    {
        // 1. Buat 1 Akun Admin
        // Kita buat secara spesifik agar kita tahu email & password-nya
        User::factory()->create([
            'name' => 'Admin Le Croissant',
            'email' => 'admin@lecroissant.com',
            'password' => Hash::make('admin123'), // Password: admin123
            'role' => 'admin',
        ]);

        // 2. Buat 10 Akun Klien B2B palsu
        // Kita simpan hasilnya di variabel untuk dipakai nanti
        $klienUsers = User::factory()->count(10)->create([
            'role' => 'klien_b2b',
        ]);

        // 3. Buat 20 Produk palsu
        // Kita simpan hasilnya di variabel untuk dipakai nanti
        $products = Product::factory()->count(20)->create();

        // 4. Buat 2 Halaman Konten Statis
        Content::factory()->create([
            'slug' => 'tentang-kami',
            'title' => 'Tentang Kami',
            'body' => 'Ini adalah halaman yang menjelaskan tentang Le Croissant...',
        ]);
        Content::factory()->create([
            'slug' => 'kontak',
            'title' => 'Hubungi Kami',
            'body' => 'Informasi kontak Le Croissant ada di sini...',
        ]);


        // 5. Buat 30 Order palsu (bagian terpenting)
        Order::factory()
            ->count(30) // Buat 30 order
            ->create([
                // Ganti user_id default dengan salah satu ID dari Klien B2B acak
                'user_id' => $klienUsers->random()->id,
            ])
            ->each(function ($order) use ($products) {
                // Untuk setiap 1 order yang baru dibuat...

                // Ambil 1 sampai 3 produk acak dari koleksi produk kita
                $productsToAttach = $products->random(rand(1, 3));
                $totalPrice = 0;

                // "Tempelkan" (attach) produk-produk ini ke order
                foreach ($productsToAttach as $product) {
                    $quantity = rand(5, 20); // Kuantitas B2B (agak banyak)
                    $priceAtPurchase = $product->price_b2b;

                    // Ini adalah cara mengisi tabel pivot 'order_details'
                    $order->products()->attach($product->id, [
                        'quantity' => $quantity,
                        'price_at_purchase' => $priceAtPurchase
                    ]);

                    // Hitung total harga
                    $totalPrice += $priceAtPurchase * $quantity;
                }

                // Simpan total harga ke order
                $order->update(['total_price' => $totalPrice]);

                // Buatkan 1 invoice untuk order ini
                Invoice::factory()->create([
                    'order_id' => $order->id, // Tautkan ke order_id yang benar
                    'status' => 'unpaid',
                ]);
            });
    }
}
