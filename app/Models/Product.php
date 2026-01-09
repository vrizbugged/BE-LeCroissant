<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'name',
        'description',
        'price_b2b',
        'stock',
        'image_url',
        'status',
    ];
    
    /**
     * Accessor untuk nama_produk (mapping ke name)
     */
    public function getNamaProdukAttribute()
    {
        return $this->name;
    }
    
    /**
     * Accessor untuk deskripsi (mapping ke description)
     */
    public function getDeskripsiAttribute()
    {
        return $this->description;
    }
    
    /**
     * Accessor untuk harga_grosir (mapping ke price_b2b)
     */
    public function getHargaGrosirAttribute()
    {
        return $this->price_b2b;
    }
    
    /**
     * Accessor untuk ketersediaan_stok (mapping ke stock)
     */
    public function getKetersediaanStokAttribute()
    {
        return $this->stock;
    }
    
    /**
     * Accessor untuk gambar (mapping ke image_url)
     */
    public function getGambarAttribute()
    {
        return $this->image_url;
    }

    /**
     * Mendefinisikan relasi: Satu Product bisa ada di BANYAK Order.
     * Kita menggunakan 'order_details' sebagai tabel pivot.
     * withPivot() digunakan agar kita bisa mengambil data 'quantity' & 'price_at_purchase'
     */
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_details')
                    ->withPivot('quantity', 'price_at_purchase');
    }
}
