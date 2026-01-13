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
        $validated = $request->validated();

        $imageUrl = null;
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $request->validate([
                'gambar' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Simpan file fisik
            $path = $file->store('products', 'public');

            // --- PERBAIKAN DI SINI ---
            // Gunakan helper url() untuk mengubah 'products/img.jpg'
            // menjadi 'http://localhost:8000/storage/products/img.jpg'
            $imageUrl = url('storage/' . $path);
            // -------------------------

        } elseif ($request->filled('gambar') && filter_var($request->input('gambar'), FILTER_VALIDATE_URL)) {
            $imageUrl = $request->input('gambar');
        }

        $data = [
            'name' => $validated['nama_produk'],
            'description' => $validated['deskripsi'],
            'price_b2b' => $validated['harga_grosir'],
            'stock' => $validated['ketersediaan_stok'],
            'image_url' => $imageUrl, // Sekarang isinya link lengkap
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
            $file = $request->file('gambar');
            $request->validate([
                'gambar' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Ambil data produk lama untuk hapus gambar lama
            $product = $this->productService->getProductById($id);

            // --- PERBAIKAN LOGIKA HAPUS GAMBAR LAMA ---
            if ($product && $product->image_url) {
                // Cek apakah ini gambar dari server sendiri (bukan link google/eksternal)
                if (str_contains($product->image_url, url('storage/'))) {
                    // Kita harus ubah URL lengkap kembali menjadi path relatif agar bisa dihapus
                    // Contoh: http://localhost:8000/storage/products/abc.jpg -> products/abc.jpg
                    $oldPath = str_replace(url('storage/') . '/', '', $product->image_url);
                    Storage::disk('public')->delete($oldPath);
                }
            }
            // ------------------------------------------

            // Simpan file baru
            $path = $file->store('products', 'public');

            // --- PERBAIKAN SIMPAN URL BARU ---
            $data['image_url'] = url('storage/' . $path);
            // ---------------------------------

        } elseif ($request->filled('gambar') && filter_var($request->input('gambar'), FILTER_VALIDATE_URL)) {
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
