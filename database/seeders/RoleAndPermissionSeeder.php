<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define entities
        $entities = [
            'user',
            'role',
            'produk',
            'pesanan',
            'klien',
            'konten',
            'invoice',
            'profil',
        ];

        // Define actions
        $actions = [
            'membuat',
            'melihat',
            'mengubah',
            'menghapus',
            'mengekspor',
        ];

        // Create permissions for each entity
        $permissions = [];
        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                $permissionName = "{$action} {$entity}";
                $permissions[] = $permissionName;
                Permission::firstOrCreate(['name' => $permissionName]);
            }
        }

        // Create special permission for accessing godmode/admin area
        $godmodePermission = 'akses godmode';
        Permission::firstOrCreate(['name' => $godmodePermission]);
        $permissions[] = $godmodePermission;

        // Create special permissions for activity log and log viewer
        $activityLogPermission = 'melihat activity log';
        Permission::firstOrCreate(['name' => $activityLogPermission]);
        $permissions[] = $activityLogPermission;

        $logViewerPermission = 'melihat log viewer';
        Permission::firstOrCreate(['name' => $logViewerPermission]);
        $permissions[] = $logViewerPermission;

        // Permission untuk export activity log
        $exportActivityLogPermission = 'mengekspor activity log';
        Permission::firstOrCreate(['name' => $exportActivityLogPermission]);
        $permissions[] = $exportActivityLogPermission;

        // Tambahkan permission untuk "mengelola" (untuk route middleware)
        $managePermissions = [
            'mengelola users',
            'mengelola roles',
            'mengelola clients',
            'mengelola products',
            'mengelola orders',
        ];

        foreach ($managePermissions as $managePermission) {
            Permission::firstOrCreate(['name' => $managePermission]);
            $permissions[] = $managePermission;
        }

        // Tambahkan permission khusus yang mungkin tidak ter-cover oleh loop
        $specialPermissions = [
            'mengubah status pesanan', // Permission khusus untuk update status order
        ];

        foreach ($specialPermissions as $specialPermission) {
            Permission::firstOrCreate(['name' => $specialPermission]);
            $permissions[] = $specialPermission;
        }

        // Create or get Super Admin role and assign all permissions (including akses godmode)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions($permissions);

        // Create or get Admin role and assign specific permissions
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions([
            // Godmode access permission (required to access admin area)
            'akses godmode',

            // Permission untuk route middleware (mengelola)
            'mengelola users',
            'mengelola roles',
            'mengelola clients',
            'mengelola products',
            'mengelola orders',

            // User / Klien B2B
            'melihat user',
            'membuat user',      // Admin mungkin perlu mendaftarkan klien manual
            'mengubah user',     // Edit data klien jika ada kesalahan
            'menghapus user',    // Hapus/Ban klien bermasalah

            // Produk (Pastry)
            'melihat produk',
            'membuat produk',    // Menambah menu baru
            'mengubah produk',   // Update harga/stok
            'menghapus produk',  // Hapus menu

            // Pesanan (Order)
            'melihat pesanan',
            'mengubah pesanan',  // Update pesanan
            'mengubah status pesanan', // Update status pesanan khusus
            'mengekspor pesanan', // Untuk laporan bulanan

            // Invoice & Laporan
            'melihat invoice',
            'mengekspor invoice',    // Mencetak laporan keuangan

            // Konten Web (Profil Bisnis)
            'melihat konten',
            'mengubah konten',       // Edit halaman "Tentang Kami" / "Kontak"

            // Activity Log permissions
            'melihat activity log',
            'mengekspor activity log',

            // Log Viewer permissions
            'melihat log viewer',
        ]);

        // Create or get Anggota (Pelanggan) role and assign limited permissions
        $anggota = Role::firstOrCreate(['name' => 'Anggota']);
        $anggota->syncPermissions([
            // Akses Produk (Katalog) - [Ref Proposal: 101]
            'melihat produk',   // Klien BISA melihat daftar produk & harga
            // PENTING: Klien TIDAK BOLEH 'membuat', 'mengubah', atau 'menghapus' produk

            // Akses Pesanan (Order) - [Ref Proposal: 102, 103]
            'membuat pesanan',    // Inti fitur: Melakukan pemesanan
            'melihat pesanan',    // Melihat riwayat pesanan MEREKA SENDIRI
            // PENTING: Klien TIDAK BOLEH 'mengubah status pesanan' (itu tugas Admin)

            // Akses Faktur (Invoice) - [Ref Proposal: 104]
            'melihat invoice',  // Melihat tagihan pesanan mereka

            // Manajemen Akun Sendiri
            'melihat profil',
            'mengubah profil',

        ]);
    }
}
