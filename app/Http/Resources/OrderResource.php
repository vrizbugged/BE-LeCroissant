<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'order_number' => $this->order_number, // Misal: ORD-202310-001

            // Informasi Klien (Relasi ke User)
            'client_name' => $this->user->name ?? 'Klien Tidak Ditemukan',
            'company_name' => $this->user->company_name ?? '-',

            // Detail Harga [Ref Proposal: Ruang Lingkup Klien 107]
            'total_price' => (float) $this->total_price,
            'total_price_formatted' => 'Rp ' . number_format($this->total_price, 0, ',', '.'),

            // Status Pesanan [Ref Proposal: 108 - Verifikasi & Ubah Status]
            'status' => $this->status,
            'payment_status' => $this->payment_status,

            // Tanggal Pengiriman & Pemesanan
            'delivery_date' => $this->delivery_date ? $this->delivery_date->format('Y-m-d') : null,
            'order_date' => $this->created_at->format('Y-m-d H:i'),

            // Item Pesanan (Daftar Produk yang dibeli)
            // Menggunakan Resource lain di dalam Resource (Nested Resource)
            'items' => $this->whenLoaded('items', function() {
                return $this->items->map(function($item) {
                    return [
                        'product_name' => $item->product->nama_produk ?? 'Produk Dihapus',
                        'quantity' => $item->quantity,
                        'price_at_purchase' => (float) $item->price,
                        'subtotal' => (float) ($item->quantity * $item->price),
                    ];
                });
            }),

            'notes' => $this->notes,
            'admin_notes' => $this->admin_notes,
        ];
    }
}
