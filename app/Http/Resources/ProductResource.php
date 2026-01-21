<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Ambil gambar dari Spatie Media Library
        $imageUrl = $this->getFirstMediaUrl('products');

        // Tambahkan Cache Busting dengan timestamp updated_at
        if ($imageUrl && $this->updated_at) {
            // Tambahkan query string ?t= dengan timestamp untuk cache busting
            $timestamp = $this->updated_at->timestamp;
            $separator = strpos($imageUrl, '?') !== false ? '&' : '?';
            $imageUrl = $imageUrl . $separator . 't=' . $timestamp;
        }

        return [
            'id' => $this->id,

            // Mapping Data (Kanan: Database -> Kiri: Frontend)
            'nama_produk' => $this->name ?? 'Produk Tanpa Nama',
            'deskripsi'   => $this->description ?? '',

            // Harga (Penting untuk Cart & Shop)
            'harga_grosir'=> (float) ($this->price_b2b ?? 0),
            'harga_formatted' => 'Rp ' . number_format((float) ($this->price_b2b ?? 0), 0, ',', '.'),

            // Stok
            'ketersediaan_stok' => (int) ($this->stock ?? 0),

            // Gambar (Penting untuk Shop)
            'image_url' => $imageUrl,
            // Kita kirim 'gambar_url' juga sebagai cadangan jika frontend pakai nama lama
            'gambar_url' => $imageUrl,

            'status' => $this->status ?? 'Non Aktif',
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
