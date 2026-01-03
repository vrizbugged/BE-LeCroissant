<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Services\Contracts\ProductServiceInterface; // Pastikan Interface ini ada
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * @var ProductServiceInterface
     */
    protected $productService;

    /**
     * ProductController Constructor.
     * Menggunakan Dependency Injection untuk memisahkan logic.
     */
    public function __construct(ProductServiceInterface $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Menampilkan daftar produk (Katalog).
     * [Ref Proposal: Katalog produk untuk Klien B2B]
     */
    public function index(Request $request): JsonResponse
    {
        // Fitur Filter: Bisa ambil produk berdasarkan status (Aktif/Non Aktif)
        // Berguna jika Klien hanya boleh melihat produk 'Aktif'
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

        // Menggunakan ProductResource::collection untuk memformat harga & gambar
        return response()->json([
            'success' => true,
            'data' => ProductResource::collection($products)
        ]);
    }

    /**
     * Menambahkan produk baru.
     * [Ref Proposal: Admin mengelola produk dan harga grosir]
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        // Validasi gambar, harga numeric, dll sudah ditangani ProductStoreRequest
        $product = $this->productService->createProduct($request->validated());

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan produk'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan ke katalog',
            'data' => new ProductResource($product)
        ], 201);
    }

    /**
     * Menampilkan detail produk spesifik.
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
     * Memperbarui informasi produk.
     * [Ref Proposal: Update stok dan harga grosir]
     */
    public function update(ProductUpdateRequest $request, string $id): JsonResponse
    {
        $product = $this->productService->updateProduct($id, $request->validated());

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui produk atau produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Informasi produk berhasil diperbarui',
            'data' => new ProductResource($product)
        ]);
    }

    /**
     * Menghapus produk dari katalog.
     */
    public function destroy(string $id): JsonResponse
    {
        // Pengecekan logika bisnis (misal: jangan hapus jika ada di pesanan aktif)
        // sebaiknya dilakukan di dalam Service.
        $deleted = $this->productService->deleteProduct($id);

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan atau tidak bisa dihapus'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus dari katalog'
        ]);
    }
}
