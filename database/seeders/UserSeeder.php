<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk User.
     */
    public function run(): void
    {
        // 1. Ambil Role dari Database (Termasuk Super Admin)
        $superAdminRole = Role::where('name', 'Super Admin')->first();
        $adminRole = Role::where('name', 'Admin')->first();
        $anggotaRole = Role::where('name', 'Anggota')->first();

        // --- [BARU] Buat 1 Akun Super Admin (Owner) ---
        // Akun ini yang nanti punya akses Godmode & Activity Log
        $superUser = User::updateOrCreate(
            ['email' => 'super@lecroissant.com'],
            [
                'name' => 'Super Owner',
                'password' => Hash::make('password123'), // Password Super Admin
                'role' => 'super_admin', // Label string di DB
                'status' => 'Aktif',
            ]
        );

        // Assign role Super Admin
        if ($superAdminRole && !$superUser->hasRole($superAdminRole)) {
            $superUser->assignRole($superAdminRole);
        }

        // --- [LAMA] Buat 1 Akun Admin (Staff Operasional) ---
        // Kodingan lama Anda tetap di sini
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@lecroissant.com'],
            [
                'name' => 'Admin Staff',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'Aktif',
            ]
        );

        if ($adminRole && !$adminUser->hasRole($adminRole)) {
            $adminUser->assignRole($adminRole);
        }

        // --- [LAMA] Buat 10 Akun Klien B2B (Logika Factory Tetap) ---
        $existingKlienCount = User::where('role', 'klien_b2b')->count();
        $neededKlienCount = max(0, 10 - $existingKlienCount);

        if ($neededKlienCount > 0) {
            if ($anggotaRole) {
                User::factory()->count($neededKlienCount)->create([
                    'role' => 'klien_b2b',
                    'status' => 'Aktif',
                ])->each(function ($user) use ($anggotaRole) {
                    if (!$user->hasRole($anggotaRole)) {
                        $user->assignRole($anggotaRole);
                    }
                });
            } else {
                User::factory()->count($neededKlienCount)->create([
                    'role' => 'klien_b2b',
                    'status' => 'Aktif',
                ]);
            }
        } else {
            $this->command->info("Sudah ada {$existingKlienCount} user dengan role klien_b2b. Tidak perlu membuat user baru.");
        }
    }
}
