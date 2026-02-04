<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $imageUrl = $this->getFirstMediaUrl('products');


        if ($imageUrl && $this->updated_at) {
            $timestamp = $this->updated_at->timestamp;
            $separator = strpos($imageUrl, '?') !== false ? '&' : '?';
            $imageUrl = $imageUrl . $separator . 't=' . $timestamp;
        }

        return [
            'id' => $this->id,

            // Mapping Data
            'nama_produk' => $this->name ?? 'Produk Tanpa Nama',
            'deskripsi'   => $this->description ?? '',

            // Harga
            'harga_grosir'=> (float) ($this->price_b2b ?? 0),
            'harga_formatted' => 'Rp ' . number_format((float) ($this->price_b2b ?? 0), 0, ',', '.'),

            // Minimal Order
            'min_order' => (int) ($this->min_order ?? 10),


            'image_url' => $imageUrl,
            'gambar_url' => $imageUrl,

            'status' => $this->status ?? 'Non Aktif',
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
