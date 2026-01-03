<?php

namespace App\Repositories\Contracts;

interface ProductRepositoryInterface
{
    /**
     * Mengambil semua produk.
     * * @return mixed
     */
    public function getAllProducts();

    /**
     * Mengambil produk berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getProductById($id);

    /**
     * Mengambil produk berdasarkan nama.
     *
     * @param string $name
     * @return mixed
     */
    public function getProductByName($name);

    /**
     * Mengambil produk berdasarkan status.
     *
     * @param string $status
     * @return mixed
     */
    public function getProductsByStatus($status);

    /**
     * Membuat produk baru.
     *
     * @param array $data
     * @return mixed
     */
    public function createProduct(array $data);

    /**
     * Memperbarui produk berdasarkan ID.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateProduct($id, array $data);

    /**
     * Menghapus produk berdasarkan ID.
     *
     * @param int $id
     * @return mixed
     */
    public function deleteProduct($id);
}
