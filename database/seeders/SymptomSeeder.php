<?php

namespace Database\Seeders;

use App\Models\Symptom;
use Illuminate\Database\Seeder;

class SymptomSeeder extends Seeder
{
    public function run(): void
    {
        $symptoms = [
            'G01' => ['Saya merasa sulit untuk menenangkan diri.', 'stress'],
            'G02' => ['Saya menyadari mulut saya terasa kering.', 'anxiety'],
            'G03' => ['Saya sama sekali tidak bisa merasakan perasaan positif.', 'depression'],
            'G04' => ['Saya mengalami kesulitan bernapas (napas cepat/sesak padahal tidak beraktivitas berat).', 'anxiety'],
            'G05' => ['Saya merasa sulit untuk mulai bersemangat mengerjakan sesuatu.', 'depression'],
            'G06' => ['Saya cenderung bereaksi berlebihan terhadap suatu situasi.', 'stress'],
            'G07' => ['Saya merasakan tangan saya gemetar.', 'anxiety'],
            'G08' => ['Saya merasa banyak menghabiskan energi karena merasa gelisah/cemas.', 'stress'],
            'G09' => ['Saya khawatir akan situasi di mana saya bisa panik dan mempermalukan diri sendiri.', 'anxiety'],
            'G10' => ['Saya merasa tidak ada lagi hal yang bisa saya nantikan.', 'depression'],
            'G11' => ['Saya menyadari diri saya mudah merasa gelisah.', 'stress'],
            'G12' => ['Saya merasa sulit untuk bersantai.', 'stress'],
            'G13' => ['Saya merasa sedih dan murung.', 'depression'],
            'G14' => ['Saya tidak sabar/tersinggung ketika ada hal yang menghambat saya menyelesaikan pekerjaan.', 'stress'],
            'G15' => ['Saya merasa hampir panik.', 'anxiety'],
            'G16' => ['Saya tidak bisa merasa antusias terhadap hal apa pun.', 'depression'],
            'G17' => ['Saya merasa diri saya kurang berharga sebagai seseorang.', 'depression'],
            'G18' => ['Saya merasa mudah tersinggung akhir-akhir ini.', 'stress'],
            'G19' => ['Saya menyadari detak jantung saya terasa jelas padahal tidak sedang beraktivitas fisik.', 'anxiety'],
            'G20' => ['Saya merasa takut tanpa alasan yang jelas.', 'anxiety'],
            'G21' => ['Saya merasa hidup ini terasa tidak berarti.', 'depression'],
        ];

        foreach ($symptoms as $kode => [$deskripsi, $cluster]) {
            Symptom::updateOrCreate(['kode' => $kode], ['deskripsi' => $deskripsi, 'kategori' => $cluster]);
        }
    }
}
