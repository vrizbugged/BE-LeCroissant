<?php

namespace App\Services\Implementations;

use App\Models\OrderDetail;
use App\Services\Contracts\OrderDetailServiceInterface;
use App\Repositories\Contracts\OrderDetailRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OrderDetailService implements OrderDetailServiceInterface
{
    /**
     * @var OrderDetailRepositoryInterface
     */
    protected $repository;

    /**
     * OrderDetailService constructor.
     *
     * @param OrderDetailRepositoryInterface $repository
     */
    public function __construct(OrderDetailRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Mengambil semua order detail.
     * 
     * @return Collection
     */
    public function getAllOrderDetails(): Collection
    {
        return $this->repository->getAllOrderDetails();
    }

    /**
     * Mengambil order detail berdasarkan ID.
     * 
     * @param int $id
     * @return OrderDetail|null
     */
    public function getOrderDetailById($id): ?OrderDetail
    {
        return $this->repository->getOrderDetailById($id);
    }

    /**
     * Mengambil order detail berdasarkan order_id.
     * 
     * @param int $orderId
     * @return Collection
     */
    public function getOrderDetailsByOrderId($orderId): Collection
    {
        return $this->repository->getOrderDetailsByOrderId($orderId);
    }

    /**
     * Mengambil order detail berdasarkan product_id.
     * 
     * @param int $productId
     * @return Collection
     */
    public function getOrderDetailsByProductId($productId): Collection
    {
        return $this->repository->getOrderDetailsByProductId($productId);
    }

    /**
     * Membuat order detail baru.
     * 
     * @param array $data
     * @return OrderDetail|null
     */
    public function createOrderDetail(array $data): ?OrderDetail
    {
        // Validasi tambahan jika diperlukan
        // Misalnya: cek apakah order dan product ada
        
        return $this->repository->create($data);
    }

    /**
     * Membuat multiple order details sekaligus.
     * 
     * @param array $items Array of order detail data
     * @return Collection
     */
    public function createManyOrderDetails(array $items): Collection
    {
        return $this->repository->createMany($items);
    }

    /**
     * Memperbarui order detail berdasarkan ID.
     * 
     * @param int $id
     * @param array $data
     * @return OrderDetail|null
     */
    public function updateOrderDetail($id, array $data): ?OrderDetail
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Menghapus order detail berdasarkan ID.
     * 
     * @param int $id
     * @return bool
     */
    public function deleteOrderDetail($id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Menghapus semua order detail berdasarkan order_id.
     * 
     * @param int $orderId
     * @return bool
     */
    public function deleteOrderDetailsByOrderId($orderId): bool
    {
        return $this->repository->deleteByOrderId($orderId);
    }
}

