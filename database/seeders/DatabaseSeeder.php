<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan database seeders.
     */
    public function run(): void
    {
        // Jalankan seeders sesuai urutan dependensi
        $this->call([
            RoleAndPermissionSeeder::class, // 1. Buat role dan permission terlebih dahulu
            UserSeeder::class,             // 2. Buat user (membutuhkan role)
            ProductSeeder::class,          // 3. Buat produk
            ContentSeeder::class,          // 4. Buat konten statis
            OrderSeeder::class,            // 5. Buat order (membutuhkan user dan produk)
            InvoiceSeeder::class,          // 6. Buat invoice (membutuhkan order)
            // OrderDetailSeeder::class,   // Opsional: jika ingin membuat detail secara manual
        ]);
    }
}
