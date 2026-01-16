<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash; // <-- Pastikan ini ada
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Kata sandi default yang akan digunakan.
     */
    protected static ?string $password;

    /**
     * Definisikan state default model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Diubah agar lebih cocok untuk B2B
            'name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'), // Diganti ke Hash::make()

            // ---- KOLOM BARU KITA ----
            'phone_number' => fake()->phoneNumber(),
            'address' => fake()->address(),
            // 'role' dihapus - menggunakan Spatie Permission roles relationship
            'status' => 'Aktif', // Default status adalah Aktif
            // -------------------------

            'remember_token' => Str::random(10),

            // ---- KOLOM BAWAAN ANDA (JANGAN DIHAPUS) ----
            'two_factor_secret' => Str::random(10),
            'two_factor_recovery_codes' => Str::random(10),
            'two_factor_confirmed_at' => now(),
        ];
    }

    /**
     * Tunjukkan bahwa alamat email model harus belum diverifikasi.
     * (Biarkan fungsi ini ada)
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Tunjukkan bahwa model tidak memiliki konfigurasi autentikasi dua faktor.
     * (Biarkan fungsi ini ada)
     */
    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }
}
