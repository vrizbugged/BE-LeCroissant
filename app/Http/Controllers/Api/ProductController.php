<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Services\Contracts\ProductServiceInterface;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Menampilkan daftar produk.
     */
    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');

        if ($status) {
            $products = $this->productService->getProductsByStatus($status);
        } else {
            $products = $this->productService->getAllProducts();
        }

        if ($products->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada produk yang tersedia',
                'data' => []
            ], 200);
        }

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
        ]);
    }

    /**
     * Menampilkan produk paling banyak dipesan.
     */
    public function mostOrdered(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 3); // Default 3 produk

        // Query produk dengan total quantity yang dipesan
        $products = \App\Models\Product::query()
            ->select('products.*')
            ->selectRaw('COALESCE(SUM(order_details.quantity), 0) as total_ordered')
            ->leftJoin('order_details', 'products.id', '=', 'order_details.product_id')
            ->where('products.status', 'Aktif')
            ->groupBy('products.id')
            ->orderByDesc('total_ordered')
            ->orderByDesc('products.created_at')
            ->limit($limit)
            ->get();

        // Jika tidak ada produk yang pernah dipesan atau hasil kosong, ambil produk aktif terbaru
        if ($products->isEmpty()) {
            $products = \App\Models\Product::where('status', 'Aktif')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
        ]);
    }

    /**
     * Menambahkan produk baru.
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Mapping Data
        $data = [
            'name' => $validated['nama_produk'],
            'description' => $validated['deskripsi'],
            'price_b2b' => $validated['harga_grosir'],
            'stock' => $validated['ketersediaan_stok'],
            'status' => $validated['status'] ?? 'Aktif',
        ];

        // 1. Simpan Data Produk Dulu
        $product = $this->productService->createProduct($data);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan produk'
            ], 400);
        }

        // 2. Baru Simpan Gambar (Jika ada)
        // Spatie akan otomatis menyimpan ke folder & database
        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')
                ->toMediaCollection('products');
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => new ProductResource($product->fresh())
        ], 201);
    }

    /**
     * Detail Produk.
     */
    public function show(string $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new ProductResource($product)
        ]);
    }

    /**
     * Update Produk (Logika Diperbaiki).
     */
    public function update(ProductUpdateRequest $request, string $id): JsonResponse
    {
        // 1. Validasi Request
        // Note: Pastikan di ProductUpdateRequest rules 'image' adalah 'nullable|image'
        $validated = $request->validated();

        // 2. Mapping Data (Hanya field yang dikirim)
        $data = [];
        if (isset($validated['nama_produk'])) $data['name'] = $validated['nama_produk'];
        if (isset($validated['deskripsi'])) $data['description'] = $validated['deskripsi'];
        if (isset($validated['harga_grosir'])) $data['price_b2b'] = $validated['harga_grosir'];
        if (isset($validated['ketersediaan_stok'])) $data['stock'] = $validated['ketersediaan_stok'];
        if (isset($validated['status'])) $data['status'] = $validated['status'];

        // 3. Update Data Text Dulu via Service
        // Service harus mengembalikan Model Product yang sudah diupdate
        $updatedProduct = $this->productService->updateProduct($id, $data);

        if (!$updatedProduct) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update: Produk tidak ditemukan atau error database'
            ], 404);
        }

        // 4. Update Gambar (Spatie)
        // Logic ini aman walau Frontend kirim POST dengan _method: PUT
        if ($request->hasFile('image')) {
            // Hapus gambar lama otomatis (karena .singleFile()) & simpan yang baru
            $updatedProduct->addMediaFromRequest('image')
                ->toMediaCollection('products');
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            // Gunakan fresh() untuk memastikan URL gambar terbaru termuat
            'data' => new ProductResource($updatedProduct->fresh())
        ]);
    }

    /**
     * Hapus Produk.
     */
    public function destroy(string $id): JsonResponse
    {
        $deleted = $this->productService->deleteProduct($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus produk'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
