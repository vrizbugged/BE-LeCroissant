<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk Content.
     */
    public function run(): void
    {
        // Buat 2 Halaman Konten Statis
        Content::factory()->create([
            'slug' => 'tentang-kami',
            'title' => 'Tentang Kami',
            'body' => 'Ini adalah halaman yang menjelaskan tentang Le Croissant...',
        ]);

        Content::factory()->create([
            'slug' => 'kontak',
            'title' => 'Hubungi Kami',
            'body' => 'Informasi kontak Le Croissant ada di sini...',
        ]);
    }
}

