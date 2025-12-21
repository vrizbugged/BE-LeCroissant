<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang digunakan oleh model ini.
     */
    protected $table = 'order_details';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_at_purchase',
    ];

    /**
     * Mendefinisikan relasi: Satu OrderDetail DIMILIKI OLEH SATU Order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Mendefinisikan relasi: Satu OrderDetail merujuk ke SATU Product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
