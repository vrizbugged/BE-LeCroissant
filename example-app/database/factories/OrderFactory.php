<?php

namespace Database\Factories;

use App\Models\User; // <-- Tambahkan ini
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Definisikan state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // "User::factory()" adalah "magic"
            // Ini memberitahu Laravel: "Buatkan saya User baru untuk order ini"
            'user_id' => User::factory(),
            'delivery_date' => fake()->dateTimeBetween('+1 day', '+1 week'),
            'status' => fake()->randomElement(['menunggu_konfirmasi', 'diproses', 'selesai']),
            'total_price' => fake()->numberBetween(100000, 1000000),
            'special_notes' => fake()->sentence(),
        ];
    }
}
