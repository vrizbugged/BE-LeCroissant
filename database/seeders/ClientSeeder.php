<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk Client.
     */
    public function run(): void
    {
        // Ambil semua user dengan role klien_b2b
        $klienUsers = User::where('role', 'klien_b2b')->get();

        if ($klienUsers->isEmpty()) {
            $this->command->warn('Tidak ada user dengan role klien_b2b. Pastikan UserSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        // Buat Client untuk setiap user klien_b2b
        foreach ($klienUsers as $user) {
            Client::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'address' => $user->address,
                    'company_name' => $user->company_name,
                    'business_sector' => $user->business_sector ?? 'Perusahaan Lain',
                    'citizenship' => $user->citizenship ?? 'WNI',
                    'status' => 'Aktif', // Set status aktif untuk klien yang sudah dibuat
                ]
            );
        }

        $this->command->info("Berhasil membuat " . $klienUsers->count() . " client dari user klien_b2b.");
    }
}

