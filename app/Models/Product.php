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
    ];

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
