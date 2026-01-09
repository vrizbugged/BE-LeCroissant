<?php

namespace App\Repositories\Contracts;

use App\Models\OrderDetail;
use Illuminate\Database\Eloquent\Collection;

interface OrderDetailRepositoryInterface
{
    /**
     * Mengambil semua order detail.
     * 
     * @return Collection
     */
    public function getAllOrderDetails();

    /**
     * Mengambil order detail berdasarkan ID.
     * 
     * @param int $id
     * @return OrderDetail|null
     */
    public function getOrderDetailById($id);

    /**
     * Mengambil order detail berdasarkan order_id.
     * 
     * @param int $orderId
     * @return Collection
     */
    public function getOrderDetailsByOrderId($orderId);

    /**
     * Mengambil order detail berdasarkan product_id.
     * 
     * @param int $productId
     * @return Collection
     */
    public function getOrderDetailsByProductId($productId);

    /**
     * Membuat order detail baru.
     * 
     * @param array $data
     * @return OrderDetail|null
     */
    public function create(array $data);

    /**
     * Membuat multiple order details sekaligus.
     * 
     * @param array $items Array of order detail data
     * @return Collection
     */
    public function createMany(array $items);

    /**
     * Memperbarui order detail berdasarkan ID.
     * 
     * @param int $id
     * @param array $data
     * @return OrderDetail|null
     */
    public function update($id, array $data);

    /**
     * Menghapus order detail berdasarkan ID.
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id);

    /**
     * Menghapus semua order detail berdasarkan order_id.
     * 
     * @param int $orderId
     * @return bool
     */
    public function deleteByOrderId($orderId);
}

