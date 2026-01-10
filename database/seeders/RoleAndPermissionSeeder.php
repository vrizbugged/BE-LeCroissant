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

        // --- 1. SUPER ADMIN (Owner) ---
        // Punya SEMUA permissions (Godmode, Activity Log, Role Management)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->syncPermissions($permissions);

        // --- 2. ADMIN (Staff Operasional) ---
        // Permission dibatasi: TIDAK BISA lihat log, TIDAK BISA ubah role
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions([
            // Godmode dihapus (biar aman, atau aktifkan jika Admin juga butuh akses dashboard penuh)
            // 'akses godmode',
            // Opsional: Aktifkan 'akses godmode' jika Admin butuh login ke dashboard yang sama dengan Super Admin,
            // tapi menu-menunya nanti disembunyikan via permission check di frontend.
            'akses godmode',

            // Middleware Permissions (TAPI TANPA 'mengelola roles')
            'mengelola users',
            'mengelola clients',
            'mengelola products',
            'mengelola orders',

            // User / Klien B2B (Staff boleh verifikasi/edit klien)
            'melihat user',
            'membuat user',
            'mengubah user',
            // 'menghapus user', // Sebaiknya Staff tidak bisa hapus user sembarangan

            // Produk (Pastry) - Full Akses
            'melihat produk',
            'membuat produk',
            'mengubah produk',
            'menghapus produk',

            // Pesanan (Order) - Full Akses
            'melihat pesanan',
            'mengubah pesanan',
            'mengubah status pesanan',
            'mengekspor pesanan',

            // Invoice & Laporan
            'melihat invoice',
            'mengekspor invoice',

            // Konten Web
            'melihat konten',
            'mengubah konten',

            // --- PENTING: Permission Activity Log & Roles DIHAPUS dari sini ---
            // Admin biasa TIDAK BOLEH melihat log atau mengubah role
        ]);

        // --- 3. ANGGOTA (Klien B2B) ---
        // Permission terbatas untuk belanja
        $anggota = Role::firstOrCreate(['name' => 'Anggota']);
        $anggota->syncPermissions([
            // Katalog
            'melihat produk',

            // Pesanan Sendiri
            'membuat pesanan',
            'melihat pesanan',

            // Invoice Sendiri
            'melihat invoice',

            // Akun Sendiri
            'melihat profil',
            'mengubah profil',
        ]);
    }
}
