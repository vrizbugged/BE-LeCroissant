<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Order extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'user_id',        // Relasi ke User (untuk backward compatibility)
        'client_id',      // Relasi ke Client (menggantikan user_id)
        'delivery_date',
        'status',
        'cancellation_reason', // Alasan pembatalan pesanan
        'total_price',
        'special_notes',
    ];

    /**
     * Mendefinisikan relasi: Satu Order DIMILIKI OLEH SATU Client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relasi ke User melalui Client (untuk backward compatibility).
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Client::class,
            'id',        // Foreign key di clients table
            'id',        // Foreign key di users table
            'client_id', // Local key di orders table
            'user_id'    // Local key di clients table
        );
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

    /**
     * Konfigurasi Activity Log untuk Order.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'total_price', 'delivery_date'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Order #{$this->id} {$eventName} - Status: {$this->status}");
    }

    /**
     * Konfigurasi Media Library untuk Order.
     * Collection 'payment_proofs' untuk menyimpan bukti pembayaran.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('payment_proofs')
            ->singleFile() // Hanya satu bukti pembayaran per order
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png', 'application/pdf']);
    }
}
