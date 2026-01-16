<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create 
                            {--email=admin@lecroissant.com : Email untuk admin user}
                            {--password=admin123 : Password untuk admin user}
                            {--name=Admin Le Croissant : Nama untuk admin user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat atau update admin user untuk login';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        // Cek apakah admin sudah ada
        $adminUser = User::where('email', $email)->first();

        if ($adminUser) {
            if ($this->confirm("Admin dengan email {$email} sudah ada. Apakah Anda ingin mengupdate password?", true)) {
                $adminUser->password = Hash::make($password);
                $adminUser->name = $name;
                // Role dihapus - menggunakan Spatie Permission
                $adminUser->status = 'Aktif';
                $adminUser->save();

                // Pastikan role Admin sudah di-assign
                $adminRole = Role::where('name', 'Admin')->first();
                if ($adminRole && !$adminUser->hasRole('Admin')) {
                    $adminUser->assignRole($adminRole);
                }

                $this->info("✓ Admin user berhasil diupdate!");
                $this->line("Email: {$email}");
                $this->line("Password: {$password}");
                return 0;
            } else {
                $this->info("Operasi dibatalkan.");
                return 0;
            }
        }

        // Buat admin baru
        // Pastikan role Admin ada
        $adminRole = Role::where('name', 'Admin')->first();
        if (!$adminRole) {
            $this->error("Role 'Admin' tidak ditemukan. Jalankan seeder terlebih dahulu:");
            $this->line("php artisan db:seed --class=RoleAndPermissionSeeder");
            return 1;
        }

        $adminUser = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            // Role dihapus - menggunakan Spatie Permission
            'status' => 'Aktif',
        ]);

        $adminUser->assignRole($adminRole);

        $this->info("✓ Admin user berhasil dibuat!");
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");
        $this->newLine();
        $this->line("Anda sekarang bisa login dengan credentials di atas.");

        return 0;
    }
}
