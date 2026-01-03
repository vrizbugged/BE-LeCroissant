<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                // Mengabaikan email milik user yang sedang diupdate
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'password' => 'nullable|string|min:8|confirmed', // Nullable agar tidak wajib ganti password
            'role' => 'required|exists:roles,name',

            // Field Spesifik Klien B2B [Ref: Demografi Kuesioner]
            'company_name' => 'required_if:role,klien_b2b|string|max:255',
            'business_sector' => 'required_if:role,klien_b2b|in:Hotel,Restoran,Event Organizer,Perusahaan Lain',
            'citizenship' => 'required_if:role,klien_b2b|in:WNI,WNA',
            'phone_number' => 'required|string|max:20',
            'status' => 'required|in:Pending,Aktif,Non Aktif',
        ];
    }
}
