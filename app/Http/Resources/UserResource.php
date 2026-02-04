<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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

            // Mengambil nama role pertama (Admin atau Klien B2B) - untuk backward compatibility
            'role' => $this->roles->first()->name ?? null,

            // Include roles array untuk frontend (untuk tampilan Users with Roles)
            // Always include roles, even if not eager loaded (Spatie Permission will load it automatically)
            'roles' => $this->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            })->values()->all(),

            // Informasi tambahan untuk Klien B2B [Ref Proposal: Ruang Lingkup Admin]
            'company_name' => $this->company_name,
            'business_sector' => $this->business_sector,
            'citizenship' => $this->citizenship,
            'phone_number' => $this->phone_number,
            'address' => $this->address,

            'status' => $this->status,

            // Include Client data jika ada (untuk auto-fill checkout form)
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                    'email' => $this->client->email,
                    'phone_number' => $this->client->phone_number,
                    'address' => $this->client->address,
                    'company_name' => $this->client->company_name,
                    'business_sector' => $this->client->business_sector,
                    'status' => $this->client->status,
                ];
            }),

            // Format tanggal yang rapi untuk Dashboard Shadcn
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
