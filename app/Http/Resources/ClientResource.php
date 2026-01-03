<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,

            // Informasi Profil Bisnis B2B
            'company_name' => $this->company_name,
            'business_sector' => $this->business_sector,

            // Variabel Demografi Penelitian
            'citizenship' => $this->citizenship,
            'address' => $this->address,

            // Status Akun (Pending, Aktif, Non Aktif)
            'status' => $this->status,

            // Statistik Singkat Pesanan (Opsional, jika relasi dimuat)
            'total_orders_count' => $this->whenCounted('orders'),

            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
