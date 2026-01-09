<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Services\Contracts\ProductServiceInterface; // Pastikan Interface ini ada
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

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
        $validated = $request->validated();
        
        // Handle gambar: bisa file upload atau URL string
        $imageUrl = null;
        if ($request->hasFile('gambar')) {
            // File upload - validasi file
            $file = $request->file('gambar');
            $request->validate([
                'gambar' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);
            $path = $file->store('products', 'public'); // Simpan di storage/app/public/products
            $imageUrl = $path; // Simpan path relatif untuk digunakan dengan Storage::url()
        } elseif ($request->filled('gambar') && filter_var($request->input('gambar'), FILTER_VALIDATE_URL)) {
            // URL string
            $imageUrl = $request->input('gambar');
        }
        
        // Map field dari request ke field database
        $data = [
            'name' => $validated['nama_produk'],
            'description' => $validated['deskripsi'],
            'price_b2b' => $validated['harga_grosir'],
            'stock' => $validated['ketersediaan_stok'],
            'image_url' => $imageUrl,
            'status' => $validated['status'] ?? 'Aktif',
        ];
        
        $product = $this->productService->createProduct($data);

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
        $validated = $request->validated();
        
        // Map field dari request ke field database
        $data = [];
        if (isset($validated['nama_produk'])) {
            $data['name'] = $validated['nama_produk'];
        }
        if (isset($validated['deskripsi'])) {
            $data['description'] = $validated['deskripsi'];
        }
        if (isset($validated['harga_grosir'])) {
            $data['price_b2b'] = $validated['harga_grosir'];
        }
        if (isset($validated['ketersediaan_stok'])) {
            $data['stock'] = $validated['ketersediaan_stok'];
        }
        
        // Handle gambar: bisa file upload atau URL string
        if ($request->hasFile('gambar')) {
            // File upload - validasi file
            $file = $request->file('gambar');
            $request->validate([
                'gambar' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);
            
            // Hapus file lama jika ada
            $product = $this->productService->getProductById($id);
            if ($product && $product->image_url && !filter_var($product->image_url, FILTER_VALIDATE_URL)) {
                // Hanya hapus jika bukan URL eksternal
                Storage::disk('public')->delete($product->image_url);
            }
            
            // Simpan file baru
            $path = $file->store('products', 'public');
            $data['image_url'] = $path;
        } elseif ($request->filled('gambar') && filter_var($request->input('gambar'), FILTER_VALIDATE_URL)) {
            // URL string
            $data['image_url'] = $request->input('gambar');
        }
        
        if (isset($validated['status'])) {
            $data['status'] = $validated['status'];
        }
        
        $product = $this->productService->updateProduct($id, $data);

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
