<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'user_id',
        'delivery_date',
        'status',
        'total_price',
        'special_notes',
    ];

    /**
     * Mendefinisikan relasi: Satu Order DIMILIKI OLEH SATU User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mendefinisikan relasi: Satu Order MEMILIKI SATU Invoice.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    /**
     * Mendefinisikan relasi: Satu Order memiliki BANYAK Product.
     * Ini adalah kebalikan dari relasi di model Product.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_details')
                    ->withPivot('quantity', 'price_at_purchase');
    }

    /**
     * Relasi opsional: Satu Order memiliki BANYAK OrderDetail (baris item).
     * Ini berguna jika Anda ingin mengelola baris itemnya secara langsung.
     */
    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
