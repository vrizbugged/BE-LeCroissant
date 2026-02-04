<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',

            // Field Tambahan untuk Klien B2B sesuai Proposal TA
            // Menggunakan nama role dari Spatie Permission: 'Client'
            'company_name' => 'required_if:role,Client|string|max:255',
            'business_sector' => 'required_if:role,Client|in:Hotel,Restoran,Event Organizer,Perusahaan Lain',
            'citizenship' => 'required_if:role,Client|in:WNI,WNA',
            'phone_number' => 'required|string|max:20',
            'status' => 'required|in:Pending,Aktif,Non Aktif',
        ];
    }
}
