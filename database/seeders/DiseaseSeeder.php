<?php

namespace Database\Seeders;

use App\Models\Disease;
use Illuminate\Database\Seeder;

class DiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = [
            ['kode' => 'P01', 'nama' => 'Stres', 'cluster_key' => 'stress',
                'keterangan' => 'Respons tubuh dan pikiran terhadap tekanan atau tuntutan, misalnya beban akademik, tenggat tugas, atau penyesuaian rutinitas perkuliahan.',
                'normal_ringan' => 'Coba terapkan manajemen waktu sederhana (misalnya teknik Pomodoro), sisihkan waktu istirahat di antara jadwal kuliah, dan batasi asupan kafein berlebih. Jaga pola tidur tetap teratur.',
                'sedang' => 'Pada tahap ini, tips mandiri saja belum cukup. Anda disarankan menjadwalkan sesi konseling untuk mendiskusikan sumber tekanan bersama konselor agar penanganannya lebih terarah.',
                'berat' => 'Kondisi yang Anda alami memerlukan perhatian dan tidak perlu dihadapi sendiri. Segera hubungi layanan bantuan profesional melalui halaman rujukan.'],
            ['kode' => 'P02', 'nama' => 'Kecemasan', 'cluster_key' => 'anxiety',
                'keterangan' => 'Perasaan khawatir, tegang, atau gelisah yang berlebihan, kadang disertai gejala fisik seperti jantung berdebar atau sulit bernapas.',
                'normal_ringan' => 'Coba latihan pernapasan dalam, misalnya teknik 4-7-8, saat rasa cemas muncul. Anda juga dapat menuliskan kekhawatiran di jurnal untuk membantu menentukan hal yang perlu ditangani.',
                'sedang' => 'Tips relaksasi mandiri kemungkinan belum cukup. Pertimbangkan untuk menjadwalkan sesi konseling agar kecemasan yang Anda rasakan dapat ditinjau dan ditangani secara tepat.',
                'berat' => 'Kondisi yang Anda rasakan memerlukan perhatian profesional. Segera hubungi layanan bantuan melalui halaman rujukan.'],
            ['kode' => 'P03', 'nama' => 'Depresi', 'cluster_key' => 'depression',
                'keterangan' => 'Penurunan suasana hati yang menetap, kehilangan minat terhadap aktivitas yang biasanya disukai, dan perasaan tidak bersemangat dalam menjalani keseharian.',
                'normal_ringan' => 'Jaga rutinitas harian tetap konsisten, termasuk jam tidur dan jam makan, serta usahakan tetap terhubung dengan teman atau keluarga.',
                'sedang' => 'Pada tahap ini, penting untuk tidak hanya mengandalkan usaha mandiri. Anda disarankan menjadwalkan sesi konseling agar memperoleh pendampingan profesional yang tepat.',
                'berat' => 'Terima kasih telah mengenali kondisi Anda. Pendampingan profesional perlu segera dipertimbangkan. Silakan hubungi layanan bantuan melalui halaman rujukan.'],
        ];

        foreach ($diseases as $d) {
            Disease::updateOrCreate(
                ['kode' => $d['kode']],
                [
                    'nama' => $d['nama'], 'cluster_key' => $d['cluster_key'],
                    'keterangan_singkat' => $d['keterangan'],
                    'panduan_normal_ringan' => $d['normal_ringan'],
                    'panduan_sedang' => $d['sedang'],
                    'panduan_berat' => $d['berat'],
                ]
            );
        }
    }
}
