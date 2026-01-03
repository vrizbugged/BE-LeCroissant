<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk Product.
     */
    public function run(): void
    {
        // Buat 20 Produk
        Product::factory()->count(20)->create();
    }
}

