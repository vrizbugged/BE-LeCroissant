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
        $routeParam = $this->route('product') ?? $this->route('id');

        // Pastikan kita mendapatkan ID (integer/string), bukan Object Model utuh
        $productId = $routeParam instanceof Product ? $routeParam->id : $routeParam;

        return [
            'nama_produk' => [
                'required',
                'string',
                'max:255',
                // Sekarang $productId dijamin ada isinya, validasi unique akan benar mengabaikan data ini
                Rule::unique('products', 'name')->ignore($productId),
            ],
            'deskripsi' => 'required|string',
            'harga_grosir' => 'required|numeric|min:0',
            'ketersediaan_stok' => 'required|integer|min:0',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Nullable karena gambar tidak selalu diubah
            'status' => 'required|in:Aktif,Non Aktif',
        ];
    }
}
