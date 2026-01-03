<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleUpdateRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk update role.
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'min:3',
                // Mengabaikan ID role yang sedang diedit agar tidak error saat klik 'Simpan' tanpa ubah nama
                Rule::unique('roles', 'name')->ignore($this->route('role')),
            ],
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
            'status' => 'required|in:Aktif,Non Aktif',
        ];
    }
}
