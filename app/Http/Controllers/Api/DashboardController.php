<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
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

        // Pesanan pending (menunggu konfirmasi)
        $pendingOrders = Order::where('status', 'menunggu_konfirmasi')
            ->count();

        // Produk aktif (asumsi produk dengan stock > 0 adalah aktif)
        // Jika ada field status di products, gunakan itu
        $activeProducts = Product::where('stock', '>', 0)
            ->count();

        // Total klien B2B (users dengan role Anggota yang status Aktif)
        // Menggunakan Spatie Permission role relationship
        $totalClients = User::whereHas('roles', function ($query) {
                $query->where('name', 'Anggota');
            })
            ->where('status', 'Aktif')
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

