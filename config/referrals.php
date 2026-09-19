<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Layanan rujukan
    |--------------------------------------------------------------------------
    | Kanal resmi ditinjau 9 September 2026. Rincian bukti ada pada
    | CATATAN_DESAIN_DAN_AUDIT.md. Nomor WhatsApp lama yang belum terverifikasi
    | tidak dipublikasikan. Kanal null tidak boleh diganti dengan nomor tebakan.
    | WhatsApp PLP ditambahkan sesuai nomor dari pemilik aplikasi; nomor itu
    | tidak diambil atau ditebak dari halaman rujukan resmi di atas.
    */
    'services' => [
        [
            'name' => 'BPJS Kesehatan - Puskesmas / RSUD Tangerang Selatan',
            'icon' => 'bi-hospital',
            'description' => 'Layanan kesehatan jiwa dengan jaminan BPJS di fasilitas kesehatan terdekat sesuai prosedur rujukan.',
            'contact_label' => null,
            'contact_url' => null,
        ],
        [
            'name' => 'Healing119.id - Kementerian Kesehatan RI',
            'icon' => 'bi-telephone',
            'description' => 'Akses dukungan psikologis awal melalui 119 ekstensi 8. Pilihan panggilan dan chat tersedia melalui situs resmi Healing119.id.',
            'contact_label' => 'Buka Healing119.id',
            'contact_url' => 'https://www.healing119.id/',
        ],
        [
            'name' => 'Yayasan Pulih',
            'icon' => 'bi-chat-heart',
            'description' => 'Informasi kontak layanan ini belum terkonfirmasi. Untuk saat ini, pilih layanan lain yang memiliki kanal resmi.',
            'contact_label' => null,
            'contact_url' => null,
        ],
        [
            'name' => 'Pusat Layanan Psikologi UIN',
            'icon' => 'bi-building',
            'description' => 'Layanan psikologi dan konseling dari Fakultas Psikologi UIN Syarif Hidayatullah Jakarta. Informasi layanan dan kontak tersedia pada halaman resminya.',
            'contact_label' => 'Lihat layanan PLP UIN',
            'contact_url' => 'https://fpsi.uinjkt.ac.id/id/pusat-layanan-psikologi',
            'whatsapp_number' => '081288178208',
            'whatsapp_url' => 'https://wa.me/6281288178208',
        ],
    ],
];
