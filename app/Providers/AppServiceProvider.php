<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

// (Repositories)
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Repositories\Eloquent\PermissionRepository;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Repositories\Eloquent\ClientRepository;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Contracts\OrderDetailRepositoryInterface;
use App\Repositories\Eloquent\OrderDetailRepository;

// (Services)
use App\Services\Contracts\UserServiceInterface;
use App\Services\Implementations\UserService;
use App\Services\Contracts\RoleServiceInterface;
use App\Services\Implementations\RoleService;
use App\Services\Contracts\PermissionServiceInterface;
use App\Services\Implementations\PermissionService;
use App\Services\Contracts\ProductServiceInterface;
use App\Services\Implementations\ProductService;
use App\Services\Contracts\ClientServiceInterface;
use App\Services\Implementations\ClientService;
use App\Services\Contracts\OrderServiceInterface;
use App\Services\Implementations\OrderService;
use App\Services\Contracts\OrderDetailServiceInterface;
use App\Services\Implementations\OrderDetailService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // =================================================================
        // 1. MANAJEMEN USER & OTENTIKASI
        // =================================================================

        // Binding User
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);

        // Binding Role
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);

        // Binding Permission (Jika menggunakan Permission Repository terpisah)
        // Jika belum ada filenya, bagian ini bisa dikomentari dulu
        // $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        // $this->app->bind(PermissionServiceInterface::class, PermissionService::class);


        // =================================================================
        // 2. MANAJEMEN BISNIS & PRODUK (LE CROISSANT CORE)
        // =================================================================

        // Binding Client (Klien B2B)
        $this->app->bind(ClientRepositoryInterface::class, ClientRepository::class);
        $this->app->bind(ClientServiceInterface::class, ClientService::class);

        // Binding Product (Katalog Pastry)
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);

        // Binding Order (Transaksi & Pesanan)
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderServiceInterface::class, OrderService::class);

        // Binding OrderDetail (Rincian Item Pesanan)
        $this->app->bind(OrderDetailRepositoryInterface::class, OrderDetailRepository::class);
        $this->app->bind(OrderDetailServiceInterface::class, OrderDetailService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mengatasi masalah panjang string index pada MySQL versi lama
        Schema::defaultStringLength(191);
    }
}
