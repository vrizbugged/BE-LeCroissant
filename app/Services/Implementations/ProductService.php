<?php

namespace App\Services\Implementations;

use Illuminate\Support\Facades\Cache;
use App\Services\Contracts\ProductServiceInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductService implements ProductServiceInterface
{
    protected $repository;

    const PRODUCTS_ALL_CACHE_KEY = 'products_all';
    const PRODUCTS_ACTIVE_CACHE_KEY = 'products_active';
    const PRODUCTS_INACTIVE_CACHE_KEY = 'products_inactive';

    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Mengambil semua produk.
     *
     * @return mixed
     */
    public function getAllProducts()
    {
        return Cache::remember(self::PRODUCTS_ALL_CACHE_KEY, 3600, function () {
            return $this->repository->getAllProducts();
        });
    }

    /**
     * Mengambil produk yang aktif.
     *
     * @return mixed
     */
    public function getActiveProducts()
    {
        return Cache::remember(self::PRODUCTS_ACTIVE_CACHE_KEY, 3600, function () {
            return $this->repository->getProductsByStatus('Aktif');
        });
    }

    /**
     * Mengambil produk yang tidak aktif.
     *
     * @return mixed
     */
    public function getInactiveProducts()
    {
        return Cache::remember(self::PRODUCTS_INACTIVE_CACHE_KEY, 3600, function () {
            return $this->repository->getProductsByStatus('Non Aktif');
        });
    }

    /**
     * Mengambil produk berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getProductById($id)
    {
        return $this->repository->getProductById($id);
    }

    /**
     * Mengambil produk berdasarkan nama.
     *
     * @param string $name
     * @return mixed
     */
    public function getProductByName($name)
    {
        return $this->repository->getProductByName($name);
    }

    /**
     * Membuat produk baru.
     *
     * @param array $data
     * @return mixed
     */
    public function createProduct(array $data)
    {
        $product = $this->repository->createProduct($data);
        if ($product) {
            $this->clearCache();
        }
        return $product;
    }

    /**
     * Memperbarui produk berdasarkan ID.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateProduct($id, array $data)
    {
        $product = $this->repository->updateProduct($id, $data);
        if ($product) {
            $this->clearCache();
        }
        return $product;
    }

    /**
     * Menghapus produk berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function deleteProduct($id)
    {
        $result = $this->repository->deleteProduct($id);
        if ($result) {
            $this->clearCache();
        }
        return $result;
    }

    /**
     * Memperbarui status produk berdasarkan ID.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateStatus($id, array $data)
    {
        $product = $this->repository->updateProduct($id, $data);
        if ($product) {
            $this->clearCache();
        }
        return $product;
    }

    /**
     * Membersihkan cache.
     *
     * @return void
     */
    protected function clearCache()
    {
        Cache::forget(self::PRODUCTS_ALL_CACHE_KEY);
        Cache::forget(self::PRODUCTS_ACTIVE_CACHE_KEY);
        Cache::forget(self::PRODUCTS_INACTIVE_CACHE_KEY);
    }
}
