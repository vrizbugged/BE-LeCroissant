<?php

namespace App\Repositories\Eloquent;

use App\Models\OrderDetail;
use App\Repositories\Contracts\OrderDetailRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderDetailRepository implements OrderDetailRepositoryInterface
{
    /**
     * @var OrderDetail
     */
    protected $model;

    /**
     * OrderDetailRepository constructor.
     *
     * @param OrderDetail $model
     */
    public function __construct(OrderDetail $model)
    {
        $this->model = $model;
    }

    /**
     * Mengambil semua order detail.
     * 
     * @return Collection
     */
    public function getAllOrderDetails(): Collection
    {
        return $this->model->with(['order', 'product'])->get();
    }

    /**
     * Mengambil order detail berdasarkan ID.
     * 
     * @param int $id
     * @return OrderDetail|null
     */
    public function getOrderDetailById($id): ?OrderDetail
    {
        try {
            return $this->model->with(['order', 'product'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::error("OrderDetail with ID {$id} not found.");
            return null;
        }
    }

    /**
     * Mengambil order detail berdasarkan order_id.
     * 
     * @param int $orderId
     * @return Collection
     */
    public function getOrderDetailsByOrderId($orderId): Collection
    {
        return $this->model->where('order_id', $orderId)
                           ->with(['order', 'product'])
                           ->get();
    }

    /**
     * Mengambil order detail berdasarkan product_id.
     * 
     * @param int $productId
     * @return Collection
     */
    public function getOrderDetailsByProductId($productId): Collection
    {
        return $this->model->where('product_id', $productId)
                           ->with(['order', 'product'])
                           ->get();
    }

    /**
     * Membuat order detail baru.
     * 
     * @param array $data
     * @return OrderDetail|null
     */
    public function create(array $data): ?OrderDetail
    {
        try {
            $orderDetail = $this->model->create($data);
            $orderDetail->load(['order', 'product']);
            return $orderDetail;
        } catch (\Exception $e) {
            Log::error("Failed to create order detail: {$e->getMessage()}", [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Membuat multiple order details sekaligus.
     * 
     * @param array $items Array of order detail data
     * @return Collection
     */
    public function createMany(array $items): Collection
    {
        try {
            $orderDetails = collect();
            
            foreach ($items as $item) {
                $orderDetail = $this->model->create($item);
                $orderDetail->load(['order', 'product']);
                $orderDetails->push($orderDetail);
            }
            
            return $orderDetails;
        } catch (\Exception $e) {
            Log::error("Failed to create multiple order details: {$e->getMessage()}", [
                'items_count' => count($items),
                'trace' => $e->getTraceAsString()
            ]);
            return collect();
        }
    }

    /**
     * Memperbarui order detail berdasarkan ID.
     * 
     * @param int $id
     * @param array $data
     * @return OrderDetail|null
     */
    public function update($id, array $data): ?OrderDetail
    {
        $orderDetail = $this->getOrderDetailById($id);
        
        if (!$orderDetail) {
            return null;
        }

        try {
            $orderDetail->update($data);
            $orderDetail->load(['order', 'product']);
            return $orderDetail;
        } catch (\Exception $e) {
            Log::error("Failed to update order detail with ID {$id}: {$e->getMessage()}", [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Menghapus order detail berdasarkan ID.
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id): bool
    {
        $orderDetail = $this->getOrderDetailById($id);
        
        if (!$orderDetail) {
            return false;
        }

        try {
            $orderDetail->delete();
            return true;
        } catch (\Exception $e) {
            Log::error("Failed to delete order detail with ID {$id}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Menghapus semua order detail berdasarkan order_id.
     * 
     * @param int $orderId
     * @return bool
     */
    public function deleteByOrderId($orderId): bool
    {
        try {
            $deleted = $this->model->where('order_id', $orderId)->delete();
            return $deleted > 0;
        } catch (\Exception $e) {
            Log::error("Failed to delete order details by order_id {$orderId}: {$e->getMessage()}", [
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}

