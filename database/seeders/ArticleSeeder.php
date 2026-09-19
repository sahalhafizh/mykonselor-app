<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            ['judul' => 'Mengenal Perbedaan Stres, Cemas, dan Burnout', 'kategori' => 'Stres',
                'konten' => 'Stres, kecemasan, dan burnout sering dianggap sama padahal berbeda. Artikel ini membahas ciri khas masing-masing kondisi serta kapan mahasiswa perlu mulai waspada dan mencari bantuan.'],
            ['judul' => 'Teknik Pernapasan 4-7-8 untuk Meredakan Kecemasan', 'kategori' => 'Kecemasan',
                'konten' => 'Teknik pernapasan sederhana ini dapat membantu menenangkan sistem saraf dalam hitungan menit. Simak langkah-langkah praktisnya di sini.'],
            ['judul' => 'Tanda-Tanda Depresi yang Sering Diabaikan Mahasiswa', 'kategori' => 'Depresi',
                'konten' => 'Banyak mahasiswa menganggap kelelahan dan kehilangan motivasi sebagai hal biasa. Artikel ini membahas kapan gejala tersebut perlu mendapat perhatian lebih serius.'],
            ['judul' => 'Membangun Rutinitas Self-Care di Tengah Kesibukan Kuliah', 'kategori' => 'Self-care',
                'konten' => 'Self-care tidak harus mahal atau memakan waktu lama. Berikut kebiasaan kecil yang bisa diterapkan sehari-hari untuk menjaga kesehatan mental.'],
        ];

        foreach ($articles as $a) {
            Article::updateOrCreate(
                ['slug' => Str::slug($a['judul'])],
                ['judul' => $a['judul'], 'konten' => $a['konten'], 'kategori' => $a['kategori'],
                    'penulis' => 'Tim MyKonselor', 'status' => 'published', 'published_at' => now()]
            );
        }
    }
}
