<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderDetailUpdateRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true; // Izin utama biasanya diatur di middleware
    }

    /**
     * Aturan validasi untuk update order detail.
     */
    public function rules(): array
    {
        return [
            'order_id' => [
                'sometimes',
                'integer',
                Rule::exists('orders', 'id'),
            ],
            'product_id' => [
                'sometimes',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'quantity' => [
                'sometimes',
                'integer',
                'min:1',
            ],
            'price_at_purchase' => [
                'sometimes',
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
            'order_id.exists' => 'Order tidak ditemukan',
            'product_id.exists' => 'Product tidak ditemukan',
            'quantity.min' => 'Quantity minimal 1',
            'price_at_purchase.min' => 'Harga tidak boleh negatif',
        ];
    }
}

