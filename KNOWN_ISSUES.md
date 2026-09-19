# KNOWN ISSUES MyKonselor

## 1. Runtime PHP dan Composer Tidak Tersedia

**Status: BLOCKED**

Environment eksekusi tidak memiliki executable PHP maupun Composer. Dampaknya:

- migration dan seeder Laravel belum dapat dijalankan;
- 40 automated test belum dapat dieksekusi;
- login runtime mahasiswa/admin belum dapat diverifikasi;
- manual browser QA, termasuk dark mode dan responsive behavior, belum dapat diverifikasi dari aplikasi berjalan.

Source PHP telah melewati static parse dan frontend build berhasil, tetapi hasil tersebut tidak menggantikan test Laravel.

## 2. Asset Logo Resmi Universitas Pamulang Belum Ada di Source

**Status: BLOCKED**

Arsip awal tidak berisi asset logo UNPAM dan hanya memiliki `favicon.ico` kosong. Struktur branding telah disiapkan agar otomatis memakai:

```text
public/images/logo-unpam.png
```

Sampai file resmi diberikan, navbar memakai ikon akademik generik dan favicon tidak dideklarasikan. Source tidak mengambil logo dari sumber acak. Referensi halaman resmi tersedia di https://unpam.ac.id/logo-unpam/.

## 3. Kontak Resmi Pusat Layanan Psikologi UIN Belum Tersedia

**Status: BLOCKED**

Source awal menggunakan CTA `href="#"` tanpa nomor. CTA tersebut sudah dihapus. Kartu layanan tetap tampil dan mahasiswa dapat mengirim pengajuan melalui MyKonselor, tetapi tombol WhatsApp UIN baru boleh diaktifkan setelah nomor resmi diberikan dan diverifikasi.

Kontak Kementerian Kesehatan yang sudah ada pada source awal dipusatkan di `config/referrals.php`. Opsi BPJS dan Yayasan Pulih tetap ditampilkan tanpa membuat URL atau nomor baru.
