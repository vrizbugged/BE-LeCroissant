<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'user_id',           // Relasi ke User untuk autentikasi
        'name',              // Nama klien
        'email',             // Email klien (bisa sama dengan User atau berbeda)
        'phone_number',      // Nomor telepon
        'address',            // Alamat
        'company_name',      // Nama perusahaan
        'business_sector',   // Sektor bisnis (Hotel, Restoran, Event Organizer, Perusahaan Lain)
        'citizenship',       // Kewarganegaraan (WNI/WNA)
        'status',            // Status (Pending, Aktif, Non Aktif)
    ];

    /**
     * Relasi: Satu Client DIMILIKI OLEH SATU User (untuk autentikasi).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi: Satu Client MEMILIKI BANYAK Order.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}

