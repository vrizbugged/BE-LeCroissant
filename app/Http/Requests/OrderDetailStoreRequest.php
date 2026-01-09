<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderDetailStoreRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true; // Izin utama biasanya diatur di middleware
    }

    /**
     * Aturan validasi untuk tambah order detail baru.
     */
    public function rules(): array
    {
        return [
            'order_id' => [
                'required',
                'integer',
                Rule::exists('orders', 'id'),
            ],
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],
            'price_at_purchase' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }

    /**
     * Pesan validasi kustom (opsional).
     */
    public function messages(): array
    {
        return [
            'order_id.required' => 'Order ID wajib diisi',
            'order_id.exists' => 'Order tidak ditemukan',
            'product_id.required' => 'Product ID wajib diisi',
            'product_id.exists' => 'Product tidak ditemukan',
            'quantity.required' => 'Quantity wajib diisi',
            'quantity.min' => 'Quantity minimal 1',
            'price_at_purchase.required' => 'Harga saat pembelian wajib diisi',
            'price_at_purchase.min' => 'Harga tidak boleh negatif',
        ];
    }
}

