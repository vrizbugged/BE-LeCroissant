<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Menampilkan semua order (Admin).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['client', 'client.user', 'products', 'invoice'])
            ->orderByDesc('created_at');

        // Filter by status
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Filter by user
        if ($userId = $request->query('user_id')) {
            $query->where('user_id', $userId);
        }

        $orders = $query->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Menampilkan order milik user yang sedang login.
     */
    public function myOrders(Request $request): JsonResponse
    {
        // Get client for the logged in user
        $client = \App\Models\Client::where('user_id', $request->user()->id)->first();
        
        if (!$client) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'current_page' => 1,
                    'per_page' => 15,
                    'total' => 0,
                    'last_page' => 1,
                ],
            ]);
        }
        
        $orders = Order::with(['client', 'client.user', 'products', 'invoice'])
            ->where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->paginate($request->query('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Membuat order baru (Klien).
     */
    public function store(Request $request): JsonResponse
    {
        $minPurchaseQuantity = 10;
        
        $validated = $request->validate([
            'delivery_date' => ['nullable', 'date', 'after:today'],
            'special_notes' => ['nullable', 'string', 'max:500'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:' . $minPurchaseQuantity],
        ], [
            'products.*.quantity.min' => 'Minimal pembelian adalah ' . $minPurchaseQuantity . ' unit per produk',
        ]);
        
        // Set default delivery_date jika tidak dikirim (7 hari dari sekarang)
        if (!isset($validated['delivery_date'])) {
            $validated['delivery_date'] = now()->addDays(7)->format('Y-m-d');
        }

        // Get or create client for the user
        $client = \App\Models\Client::where('user_id', $request->user()->id)->first();
        if (!$client) {
            // Create client if doesn't exist
            $client = \App\Models\Client::create([
                'user_id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'phone_number' => $request->user()->phone_number,
                'address' => $request->user()->address,
                'company_name' => $request->user()->company_name,
                'business_sector' => $request->user()->business_sector ?? 'Perusahaan Lain',
                'citizenship' => $request->user()->citizenship ?? 'WNI',
                'status' => 'Aktif',
            ]);
        }
        
        // Validasi stock tersedia sebelum membuat order
        $productsToValidate = [];
        foreach ($validated['products'] as $product) {
            $productModel = \App\Models\Product::findOrFail($product['id']);
            
            // Validasi stock tersedia
            if ($productModel->stock < $product['quantity']) {
                return response()->json([
                    'success' => false,
                    'message' => "Stok produk '{$productModel->name}' tidak mencukupi. Stok tersedia: {$productModel->stock} unit, yang diminta: {$product['quantity']} unit",
                ], 422);
            }
            
            $productsToValidate[] = [
                'model' => $productModel,
                'data' => $product,
            ];
        }
        
        // Gunakan DB transaction untuk memastikan atomicity
        try {
            DB::beginTransaction();
            
            // Buat order
            $order = Order::create([
                'client_id' => $client->id,
                'delivery_date' => $validated['delivery_date'],
                'special_notes' => $validated['special_notes'] ?? null,
                'status' => 'menunggu_konfirmasi',
                'total_price' => 0,
            ]);

            // Attach products, hitung total, dan update stock
            $totalPrice = 0;
            foreach ($productsToValidate as $item) {
                $productModel = $item['model'];
                $product = $item['data'];
                $priceAtPurchase = $productModel->price_b2b;

                // Attach product ke order
                $order->products()->attach($product['id'], [
                    'quantity' => $product['quantity'],
                    'price_at_purchase' => $priceAtPurchase,
                ]);

                // Update stock (decrease)
                $productModel->decrement('stock', $product['quantity']);

                $totalPrice += $priceAtPurchase * $product['quantity'];
            }

            // Update total price
            $order->update(['total_price' => $totalPrice]);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil dibuat',
                'data' => $order->load(['client', 'client.user', 'products']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Menampilkan detail order.
     */
    public function show(string $id): JsonResponse
    {
        $order = Order::with(['client', 'client.user', 'products', 'invoice'])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Memperbarui order (Admin).
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'delivery_date' => ['sometimes', 'date'],
            'special_notes' => ['sometimes', 'nullable', 'string', 'max:500'],
            'status' => ['sometimes', 'in:menunggu_konfirmasi,diproses,selesai,dibatalkan'],
        ]);

        $order->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil diperbarui',
            'data' => $order->fresh()->load(['client', 'client.user', 'products', 'invoice']),
        ]);
    }

    /**
     * Menghapus order.
     */
    public function destroy(string $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        // Hapus relasi products (pivot table)
        $order->products()->detach();

        // Hapus order
        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order berhasil dihapus',
        ]);
    }

    /**
     * Update status order (shortcut).
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan',
            ], 404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:menunggu_konfirmasi,diproses,selesai,dibatalkan'],
        ]);

        $order->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status order berhasil diperbarui',
            'data' => $order->fresh()->load(['user', 'products', 'invoice']),
        ]);
    }

    /**
     * Laporan order.
     */
    public function report(Request $request): JsonResponse
    {
        $startDate = $request->query('start_date', date('Y-m-01'));
        $endDate = $request->query('end_date', date('Y-m-d'));

        $orders = Order::whereBetween('created_at', [$startDate, $endDate . ' 23:59:59'])
            ->with(['client', 'client.user', 'products'])
            ->get();

        $totalRevenue = $orders->sum('total_price');
        $totalOrders = $orders->count();
        $completedOrders = $orders->where('status', 'selesai')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'total_orders' => $totalOrders,
                    'completed_orders' => $completedOrders,
                    'total_revenue' => $totalRevenue,
                ],
                'orders' => $orders,
            ],
        ]);
    }
}

