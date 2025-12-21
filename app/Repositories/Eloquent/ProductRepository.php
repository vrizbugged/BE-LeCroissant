<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Interfaces\ProductRepositoryInterface; // Disesuaikan dengan folder Interfaces kita

class ProductRepository implements ProductRepositoryInterface
{
    protected $model;

    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    /**
     * Mengambil semua produk.
     *
     * @return mixed
     */
    public function getAllProducts()
    {
        // Kita hapus ->with(...) karena Product belum punya relasi khusus saat ini
        return $this->model->all();
    }

    /**
     * Mengambil produk berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getProductById($id)
    {
        try {
            // Mengambil produk berdasarkan ID, handle jika tidak ditemukan
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error("Product with ID {$id} not found.");
            return null;
        }
    }

    /**
     * Mengambil produk berdasarkan nama.
     *
     * @param string $name
     * @return mixed
     */
    public function getProductByName($name)
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Mengambil produk berdasarkan status (Opsional jika ada kolom status).
     *
     * @param string $status
     * @return mixed
     */
    public function getProductsByStatus($status)
    {
        // Pastikan tabel products memiliki kolom 'status' jika ingin menggunakan ini
        return $this->model->where('status', $status)->get();
    }

    /**
     * Membuat produk baru.
     *
     * @param array $data
     * @return mixed
     */
    public function createProduct(array $data)
    {
        try {
            $product = $this->model->create($data);
            // $product->load(...); // Dihapus sementara karena belum ada relasi
            return $product;
        } catch (\Exception $e) {
            Log::error("Failed to create product: {$e->getMessage()}");
            return null;
        }
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
        $product = $this->findProduct($id);

        if ($product) {
            try {
                $product->update($data);
                // $product->load(...); // Dihapus sementara karena belum ada relasi
                return $product;
            } catch (\Exception $e) {
                Log::error("Failed to update product with ID {$id}: {$e->getMessage()}");
                return null;
            }
        }
        return null;
    }

    /**
     * Menghapus produk berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function deleteProduct($id)
    {
        $product = $this->findProduct($id);

        if ($product) {
            try {
                $product->delete();
                return true;
            } catch (\Exception $e) {
                Log::error("Failed to delete product with ID {$id}: {$e->getMessage()}");
                return false;
            }
        }
        return false;
    }

    /**
     * Helper method untuk menemukan produk berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    protected function findProduct($id)
    {
        try {
            return $this->model->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error("Product with ID {$id} not found.");
            return null;
        }
    }
}
