<?php

namespace App\Services\Interfaces;

interface ProductServiceInterface
{
    /**
     * Mengambil semua produk.
     *
     * @return mixed
     */
    public function getAllProducts();

    /**
     * Mengambil produk yang aktif.
     *
     * @return mixed
     */
    public function getActiveProducts();

    /**
     * Mengambil produk yang tidak aktif.
     *
     * @return mixed
     */
    public function getInactiveProducts();

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
