<?php

namespace App\Services\Contracts;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderServiceInterface
{
    public function getAllOrders();
    public function getOrderDetails($id);
    public function getClientOrderHistory($clientId);
    public function placeOrder(array $data);

    /**
     * Verify and Update Order Status
     * [Ref Proposal: Mengubah status pesanan: konfirmasi, proses, selesai]
     */
    public function updateOrderStatus($id, $status);

    /**
     * Generate Summary Report
     * [Ref Proposal: Mencetak laporan ringkasan transaksi B2B]
     */
    public function getOrderSummaryReport($startDate, $endDate);
}
