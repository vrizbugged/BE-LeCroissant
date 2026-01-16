<?php

namespace App\Models;

// Import-import ini kemungkinan sudah ada di file asli Anda
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Fortify\TwoFactorAuthenticatable; // <-- PENTING

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    // Trait ini adalah gabungan dari bawaan Laravel dan starter kit
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;
    use HasRoles;


    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number', // <-- Kolom baru Anda
        'address',      // <-- Kolom baru Anda
        // 'role' removed - menggunakan Spatie Permission roles relationship
        'status',       // <-- Status user (Aktif/Non Aktif)
        // Field B2B sudah dipindah ke model Client terpisah
    ];

    /**
     * Atribut yang harus disembunyikan untuk serialisasi.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes', // <-- Tambahan dari starter kit
        'two_factor_secret',         // <-- Tambahan dari starter kit
    ];

    /**
     * Dapatkan tipe data (casts) untuk atribut.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime', // <-- Tambahan dari starter kit
        ];
    }


    /**
     * Relasi ke Client (jika user adalah klien B2B).
     */
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    /**
     * Relasi ke Order melalui Client (untuk backward compatibility).
     */
    public function orders()
    {
        return $this->hasManyThrough(Order::class, Client::class, 'user_id', 'client_id', 'id', 'id');
    }
}
