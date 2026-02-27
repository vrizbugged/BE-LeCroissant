<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductStoreRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true; // Izin utama biasanya diatur di middleware
    }

    /**
     * Aturan validasi untuk tambah produk baru.
     * [Ref Proposal: Menambah informasi produk dan harga grosir]
     */
    public function rules(): array
    {
        return [
            'nama_produk' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name'),
            ],
            'deskripsi' => 'required|string',
            'harga_grosir' => 'required|numeric|gt:0', // Sesuai kebutuhan harga B2B
            'min_order' => 'nullable|integer|min:1', // Minimal order per produk
            'gambar' => 'nullable', // Bisa file image atau URL string
            // Validasi file akan dilakukan di controller (image|mimes:jpeg,png,jpg|max:2048 jika file)
            'status' => 'required|in:Aktif,Non Aktif',
        ];
    }
}
