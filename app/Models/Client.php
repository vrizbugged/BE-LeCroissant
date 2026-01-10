<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Client extends Model
{
    use HasFactory, LogsActivity;

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

    /**
     * Konfigurasi Activity Log untuk Client.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'company_name', 'business_sector'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Client #{$this->id} {$eventName} - {$this->company_name}");
    }
}

