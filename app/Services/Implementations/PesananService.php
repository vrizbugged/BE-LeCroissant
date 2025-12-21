<?php

namespace App\Services;

use App\Services\Interfaces\OrderServiceInterface;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Carbon\Carbon;

class OrderService implements OrderServiceInterface
{
    protected $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getAllOrders()
    {
        return $this->orderRepository->getAllOrders();
    }

    public function getOrderDetails($id)
    {
        return $this->orderRepository->getOrderById($id);
    }

    public function getClientOrderHistory($clientId)
    {
        return $this->orderRepository->getOrdersByClientId($clientId);
    }

    public function placeOrder(array $data)
    {
        // Logika bisnis: Set status awal ke 'Pending'
        $data['status'] = 'Pending';
        $data['order_date'] = Carbon::now()->format('Y-m-d');

        return $this->orderRepository->createOrder($data);
    }

    public function updateOrderStatus($id, $status)
    {
        return $this->orderRepository->updateOrder($id, ['status' => $status]);
    }

    public function getOrderSummaryReport($startDate, $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return $this->orderRepository->getOrderStats($start, $end);
    }
}
