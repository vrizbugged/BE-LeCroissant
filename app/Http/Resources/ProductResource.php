<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
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
            'nama_produk' => $this->nama_produk,
            'deskripsi' => $this->deskripsi,

            // Format harga grosir agar mudah dibaca di frontend [Ref Proposal: 107]
            'harga_grosir' => (float) $this->harga_grosir,
            'harga_formatted' => 'Rp ' . number_format($this->harga_grosir, 0, ',', '.'),

            // Manajemen stok sesuai ruang lingkup Admin [Ref Proposal: 106]
            'ketersediaan_stok' => (int) $this->ketersediaan_stok,

            // Mengubah path database menjadi URL lengkap agar gambar muncul di Next.js
            'gambar_url' => $this->gambar ? url(Storage::url($this->gambar)) : null,

            'status' => $this->status, // Aktif atau Non Aktif

            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
