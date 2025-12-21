<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Definisikan state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Membuat nama produk palsu seperti "Croissant Coklat Lezat"
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(2),
            // Harga B2B palsu antara 10,000 dan 100,000
            'price_b2b' => fake()->numberBetween(10000, 100000),
            'stock' => fake()->numberBetween(50, 200),
            'image_url' => 'https://via.placeholder.com/640x480.png?text=Le+Croissant+Product',
        ];
    }
}
