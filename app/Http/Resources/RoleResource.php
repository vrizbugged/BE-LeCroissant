<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
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

            // Mengambil semua nama permission dan menjadikannya array sederhana
            // Contoh: ['view products', 'edit products', 'verify orders']
            'permissions' => $this->permissions->pluck('name'),

            'status' => $this->status, // Field status Aktif/Non Aktif sesuai struktur Shine

            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
