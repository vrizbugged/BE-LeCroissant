<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
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
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => (int) $this->quantity,
            'price_at_purchase' => (float) $this->price_at_purchase,
            'price_at_purchase_formatted' => 'Rp ' . number_format($this->price_at_purchase, 0, ',', '.'),
            
            // Subtotal untuk item ini (quantity * price_at_purchase)
            'subtotal' => (float) ($this->quantity * $this->price_at_purchase),
            'subtotal_formatted' => 'Rp ' . number_format($this->quantity * $this->price_at_purchase, 0, ',', '.'),

            // Relasi Order (jika dimuat)
            'order' => $this->whenLoaded('order', function () {
                return [
                    'id' => $this->order->id,
                    'status' => $this->order->status,
                    'delivery_date' => $this->order->delivery_date,
                    'total_price' => $this->order->total_price,
                ];
            }),

            // Relasi Product (jika dimuat)
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'nama_produk' => $this->product->nama_produk,
                    'harga_grosir' => (float) $this->product->harga_grosir,
                    'harga_grosir_formatted' => 'Rp ' . number_format($this->product->harga_grosir, 0, ',', '.'),
                ];
            }),

            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}

