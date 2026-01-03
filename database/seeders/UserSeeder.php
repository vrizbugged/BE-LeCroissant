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
        // Pastikan role sudah ada (dibuat oleh RoleAndPermissionSeeder)
        $adminRole = Role::where('name', 'Admin')->first();
        $anggotaRole = Role::where('name', 'Anggota')->first();

        if (!$adminRole) {
            $this->command->warn('Role "Admin" tidak ditemukan. Pastikan RoleAndPermissionSeeder sudah dijalankan terlebih dahulu.');
            return;
        }

        // Buat 1 Akun Admin
        $adminUser = User::factory()->create([
            'name' => 'Admin Le Croissant',
            'email' => 'admin@lecroissant.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'status' => 'Aktif',
        ]);

        // Assign role Admin menggunakan Spatie Permission
        $adminUser->assignRole($adminRole);

        // Buat 10 Akun Klien B2B
        if ($anggotaRole) {
            User::factory()->count(10)->create([
                'role' => 'klien_b2b',
                'status' => 'Aktif',
            ])->each(function ($user) use ($anggotaRole) {
                // Assign role Anggota untuk klien B2B
                $user->assignRole($anggotaRole);
            });
        } else {
            // Jika role Anggota belum ada, buat user tanpa assign role
            User::factory()->count(10)->create([
                'role' => 'klien_b2b',
                'status' => 'Aktif',
            ]);
        }
    }
}

