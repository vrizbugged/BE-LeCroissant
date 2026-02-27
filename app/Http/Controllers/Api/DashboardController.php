<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     */
    public function stats(Request $request): JsonResponse
    {
        // Total pendapatan (dari order yang selesai)
        $totalRevenue = Order::where('status', 'selesai')
            ->sum('total_price');


        // Pesanan pending = hanya menunggu konfirmasi admin
        $pendingOrders = Order::where('status', 'menunggu_konfirmasi')
            ->count();

        // Produk aktif (akomodasi variasi status lama: Aktif / Active)
        $activeProducts = Product::query()
            ->where(function ($query) {
                $query->whereRaw('LOWER(status) = ?', ['aktif'])
                    ->orWhereRaw('LOWER(status) = ?', ['active']);
            })
            ->count();

        // Total klien B2B aktif dari tabel clients
        $totalClients = Client::query()
            ->where('status', 'Aktif')
            ->whereDoesntHave('user.roles', function ($query) {
                $query->whereIn('name', ['Admin', 'Super Admin']);
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_pendapatan' => (float) $totalRevenue,
                'pesanan_pending' => $pendingOrders,
                'produk_aktif' => $activeProducts,
                'total_klien_b2b' => $totalClients,
            ],
        ]);
    }

    /**
     * Get chart data for dashboard
     * Returns revenue and orders data for the last 6 months
     */
    public function chartData(Request $request): JsonResponse
    {
        $months = [];
        $revenueData = [];
        $ordersData = [];

        // Get data for last 6 months
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $monthName = $date->format('M Y');
            $months[] = $monthName;

            // Revenue for completed orders in this month
            $revenue = Order::where('status', 'selesai')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_price');
            $revenueData[] = (float) $revenue;

            // Total orders in this month
            $orders = Order::whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            $ordersData[] = $orders;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'months' => $months,
                'revenue' => $revenueData,
                'orders' => $ordersData,
            ],
        ]);
    }
}
