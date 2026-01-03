<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderStoreRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk pembuatan pesanan baru.
     * [Ref Proposal: 107 - Klien dapat melakukan pemesanan secara online]
     */
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_date' => 'required|date|after:today', // Memastikan pesanan tidak mendadak
            'notes' => 'nullable|string|max:500',
            'total_price' => 'required|numeric|min:0',
        ];
    }
}
