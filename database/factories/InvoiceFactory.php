<?php

namespace Database\Factories;

use App\Models\Order; // <-- Tambahkan ini
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Definisikan state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Sama seperti Order, Invoice ini akan otomatis membuat Order baru
            'order_id' => Order::factory(),
            'invoice_number' => 'INV/' . date('Ymd') . '/' . fake()->unique()->randomNumber(5),
            'status' => fake()->randomElement(['unpaid', 'paid']),
            'due_date' => fake()->dateTimeBetween('+1 week', '+1 month'),
        ];
    }
}
