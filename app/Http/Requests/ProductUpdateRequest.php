<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk update produk.
     * [Ref Proposal: Mengubah informasi produk dan harga grosir]
     */
    public function rules(): array
    {
        // ... logika pengambilan ID sudah benar ...
        $routeParam = $this->route('product') ?? $this->route('id');
        $productId = $routeParam instanceof Product ? $routeParam->id : $routeParam;

        return [
            'nama_produk' => [
                'required',
                'string',
                'max:255',
                // Validasi unique ignore ID ini sudah benar
                Rule::unique('products', 'name')->ignore($productId),
            ],
            'deskripsi' => 'required|string',
            'harga_grosir' => 'required|numeric|min:0',
            'min_order' => 'nullable|integer|min:1', // Minimal order per produk

            // --- PERBAIKAN DI SINI ---
            // Ubah 'gambar' menjadi 'image' agar sesuai dengan Controller & Spatie
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // -------------------------

            'status' => 'required|in:Aktif,Non Aktif',
        ];
    }
}
