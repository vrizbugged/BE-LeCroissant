<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $isSuperAdmin = $user && $user->hasRole('Super Admin');

        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                // Mengabaikan email milik user yang sedang diupdate
                Rule::unique('users', 'email')->ignore($user),
            ],
            'password' => 'nullable|string|min:8|confirmed', // Nullable agar tidak wajib ganti password
            'role' => [
                'sometimes',
                'string',
                Rule::exists('roles', 'name'),
                function ($attribute, $value, $fail) use ($isSuperAdmin) {
                    // Validasi: role "Super Admin" hanya bisa di-assign oleh super admin
                    if ($value === 'Super Admin' && !$isSuperAdmin && !auth()->user()?->hasRole('Super Admin')) {
                        $fail('Only Super Admin can assign Super Admin role.');
                    }
                },
            ],

            // Field Spesifik Klien B2B [Ref: Demografi Kuesioner]
            'company_name' => 'sometimes|nullable|string|max:255',
            'business_sector' => 'sometimes|nullable|in:Hotel,Restoran,Event Organizer,Perusahaan Lain',
            'citizenship' => 'sometimes|nullable|in:WNI,WNA',
            'phone_number' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:Aktif,Non Aktif',
        ];
    }
}
