<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleStoreRequest extends FormRequest
{
    /**
     * Tentukan apakah user diizinkan melakukan request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk tambah role baru.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:50|min:3|unique:roles,name',
            'permissions' => 'required|array', // Harus berupa array permission
            'permissions.*' => 'exists:permissions,name', // Setiap isi array harus ada di tabel permissions
            'status' => 'required|in:Aktif,Non Aktif',
        ];
    }
}
