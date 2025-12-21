<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'order_id',
        'invoice_number',
        'status',
        'due_date',
    ];

    /**
     * Mendefinisikan relasi: Satu Invoice DIMILIKI OLEH SATU Order.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
