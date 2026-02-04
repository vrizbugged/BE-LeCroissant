<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class Product extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'name',
        'description',
        'price_b2b',
        'min_order',
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

    /**
     * Konfigurasi Activity Log untuk Product.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'price_b2b', 'min_order', 'status'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Product {$eventName} - {$this->name}");
    }

    /**
     * Konfigurasi Media Library untuk Product.
     * Collection 'products' untuk menyimpan gambar produk.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('products')
            ->singleFile() // Hanya satu gambar per produk, gambar otomatis tertimpa saat update
            ->acceptsMimeTypes(['image/jpeg', 'image/jpg', 'image/png']);
    }
}
