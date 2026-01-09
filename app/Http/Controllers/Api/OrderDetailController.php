<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\OrderDetailResource;
use App\Http\Requests\OrderDetailStoreRequest;
use App\Http\Requests\OrderDetailUpdateRequest;
use App\Services\Contracts\OrderDetailServiceInterface;
use Illuminate\Http\JsonResponse;

class OrderDetailController extends Controller
{
    /**
     * @var OrderDetailServiceInterface
     */
    protected $orderDetailService;

    /**
     * OrderDetailController Constructor.
     * Dependency Injection untuk memisahkan logika bisnis.
     */
    public function __construct(OrderDetailServiceInterface $orderDetailService)
    {
        $this->orderDetailService = $orderDetailService;
    }

    /**
     * Menampilkan daftar order detail.
     */
    public function index(Request $request): JsonResponse
    {
        // Filter opsional berdasarkan order_id atau product_id
        $orderId = $request->query('order_id');
        $productId = $request->query('product_id');

        if ($orderId) {
            $orderDetails = $this->orderDetailService->getOrderDetailsByOrderId($orderId);
        } elseif ($productId) {
            $orderDetails = $this->orderDetailService->getOrderDetailsByProductId($productId);
        } else {
            $orderDetails = $this->orderDetailService->getAllOrderDetails();
        }

        if ($orderDetails->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada order detail',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => OrderDetailResource::collection($orderDetails)
        ]);
    }

    /**
     * Menambahkan order detail baru.
     */
    public function store(OrderDetailStoreRequest $request): JsonResponse
    {
        $orderDetail = $this->orderDetailService->createOrderDetail($request->validated());

        if (!$orderDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan order detail'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order detail berhasil ditambahkan',
            'data' => new OrderDetailResource($orderDetail)
        ], 201);
    }

    /**
     * Menampilkan detail order detail spesifik.
     */
    public function show(string $id): JsonResponse
    {
        $orderDetail = $this->orderDetailService->getOrderDetailById($id);

        if (!$orderDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Order detail tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new OrderDetailResource($orderDetail)
        ]);
    }

    /**
     * Memperbarui informasi order detail.
     */
    public function update(OrderDetailUpdateRequest $request, string $id): JsonResponse
    {
        $orderDetail = $this->orderDetailService->updateOrderDetail($id, $request->validated());

        if (!$orderDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui order detail atau order detail tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order detail berhasil diperbarui',
            'data' => new OrderDetailResource($orderDetail)
        ]);
    }

    /**
     * Menghapus order detail.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->orderDetailService->deleteOrderDetail($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Order detail tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order detail berhasil dihapus'
        ]);
    }

    /**
     * Membuat multiple order details sekaligus.
     * Endpoint khusus untuk membuat banyak item dalam satu request.
     */
    public function storeMany(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.order_id' => 'required|integer|exists:orders,id',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price_at_purchase' => 'required|numeric|min:0',
        ]);

        $orderDetails = $this->orderDetailService->createManyOrderDetails($request->input('items'));

        if ($orderDetails->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan order details'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order details berhasil ditambahkan',
            'data' => OrderDetailResource::collection($orderDetails)
        ], 201);
    }
}

