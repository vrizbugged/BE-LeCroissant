<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    protected $model;

    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    public function getAllOrders(): Collection
    {
        return $this->model->with(['user', 'items.product'])->latest()->get();
    }

    public function getOrderById($id): ?Order
    {
        $order = $this->model->with(['user', 'items.product'])->find($id);
        return $order instanceof Order ? $order : null;
    }

    public function getOrdersByClientId($clientId): Collection
    {
        return $this->model->where('user_id', $clientId)->with('items.product')->latest()->get();
    }

    public function getOrdersByStatus($status): Collection
    {
        return $this->model->where('status', $status)->with('user')->get();
    }

    public function createOrder(array $data): Order
    {
        return $this->model->create($data);
    }

    public function updateOrder($id, array $data): ?Order
    {
        $order = $this->getOrderById($id);
        if (!$order) return null;

        $order->update($data);
        return $order;
    }

    public function getOrderStats($startDate, $endDate): array
    {
        $orders = $this->model->whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->where('status', 'Selesai')->sum('total_price'),
            'pending_count' => $orders->where('status', 'Pending')->count(),
            'processing_count' => $orders->where('status', 'Proses')->count()
        ];
    }
}
