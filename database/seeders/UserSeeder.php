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
        $clientRole = Role::where('name', 'Client')->first();

        // --- [BARU] Buat 1 Akun Super Admin (Owner) ---
        // Akun ini yang nanti punya akses Godmode & Activity Log
        $superUser = User::updateOrCreate(
            ['email' => 'super@lecroissant.com'],
            [
                'name' => 'Super Owner',
                'password' => Hash::make('password123'), // Password Super Admin
                // Role dihapus - menggunakan Spatie Permission
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
                // Role dihapus - menggunakan Spatie Permission
                'status' => 'Aktif',
            ]
        );

        if ($adminRole && !$adminUser->hasRole($adminRole)) {
            $adminUser->assignRole($adminRole);
        }

        // --- [LAMA] Buat 10 Akun Klien B2B (Logika Factory Tetap) ---
        // Menggunakan Spatie Permission role relationship
        $existingKlienCount = User::whereHas('roles', function ($query) use ($clientRole) {
            if ($clientRole) {
                $query->where('name', $clientRole->name);
            }
        })->count();
        $neededKlienCount = max(0, 10 - $existingKlienCount);

        if ($neededKlienCount > 0) {
            if ($clientRole) {
                User::factory()->count($neededKlienCount)->create([
                    // Role dihapus - menggunakan Spatie Permission
                    'status' => 'Aktif',
                ])->each(function ($user) use ($clientRole) {
                    if (!$user->hasRole($clientRole)) {
                        $user->assignRole($clientRole);
                    }
                });
            } else {
                User::factory()->count($neededKlienCount)->create([
                    // Role dihapus - menggunakan Spatie Permission
                    'status' => 'Aktif',
                ]);
            }
        } else {
            $this->command->info("Sudah ada {$existingKlienCount} user dengan role Client. Tidak perlu membuat user baru.");
        }
    }
}
