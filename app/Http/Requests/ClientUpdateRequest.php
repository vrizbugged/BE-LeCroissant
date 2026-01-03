<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientUpdateRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk update data klien.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                // Mengabaikan email milik user yang sedang diupdate
                Rule::unique('users', 'email')->ignore($this->route('client')),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'phone_number' => 'required|string|max:20',

            // Data Bisnis B2B [Ref Proposal: 109, 329]
            'company_name' => 'required|string|max:255',
            'business_sector' => 'required|in:Hotel,Restoran,Event Organizer,Perusahaan Lain',

            // Variabel Demografi [Ref Proposal: 338]
            'citizenship' => 'required|in:WNI,WNA',
            'address' => 'required|string',

            'status' => 'required|in:Pending,Aktif,Non Aktif',
        ];
    }
}
