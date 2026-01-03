<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    /**
     * Get all B2B orders with client relationship
     * [Ref Proposal: Melihat semua pesanan B2B yang masuk]
     * @return Collection
     */
    public function getAllOrders();

    /**
     * Get order by id with items and client details
     * @param int $id
     * @return Order|null
     */
    public function getOrderById($id);

    /**
     * Get orders by specific client
     * [Ref Proposal: Klien dapat melihat riwayat pesanan]
     * @param int $clientId
     * @return Collection
     */
    public function getOrdersByClientId($clientId);

    /**
     * Get orders by status (Pending, Proses, Selesai)
     * [Ref Proposal: Mengubah status pesanan]
     * @param string $status
     * @return Collection
     */
    public function getOrdersByStatus($status);

    /**
     * Create new B2B order record
     * [Ref Proposal: Melakukan pemesanan secara online]
     * @param array $data
     * @return Order
     */
    public function createOrder(array $data);

    /**
     * Update order status or information
     * [Ref Proposal: Memverifikasi pesanan B2B]
     * @param int $id
     * @param array $data
     * @return Order|null
     */
    public function updateOrder($id, array $data);

    /**
     * Get order statistics for reports
     * [Ref Proposal: Mencetak laporan ringkasan pesanan]
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getOrderStats($startDate, $endDate);
}
