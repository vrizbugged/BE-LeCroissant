<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderUpdateRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk pembaruan pesanan oleh Admin.
     * [Ref Proposal: 108 - Admin memverifikasi pesanan dan mengubah status]
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:Pending,Verifikasi,Proses,Selesai,Dibatalkan',
            'delivery_date' => 'sometimes|required|date',
            'admin_notes' => 'nullable|string|max:500',
            'payment_status' => 'required|in:Belum Bayar,Sudah Bayar',
        ];
    }
}
