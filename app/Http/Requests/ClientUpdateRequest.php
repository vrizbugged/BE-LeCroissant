<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Client;

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
        $clientId = $this->route('client');
        $client = Client::find($clientId);
        $userIdToIgnore = $client ? $client->user_id : null;


        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',

                Rule::unique('users', 'email')->ignore($userIdToIgnore),
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
