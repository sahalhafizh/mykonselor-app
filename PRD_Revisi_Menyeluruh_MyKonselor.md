# PRD Revisi Menyeluruh MyKonselor

**Dokumen:** Product Requirements Document (PRD) Revisi UI/UX, Dark
Mode, Navigasi, Rujukan, Keamanan, dan Quality Assurance\
**Project:** MyKonselor\
**Platform:** Laravel + MySQL + Bootstrap 5 + Livewire/Vite\
**Status:** Siap dijadikan acuan revisi implementasi\
**Prioritas:** Tinggi\
**Tanggal audit:** 29 Agustus 2026

------------------------------------------------------------------------

## 1. Tujuan Revisi

Revisi ini bertujuan mempertahankan struktur dan fitur MyKonselor yang
sudah berjalan, tetapi meningkatkan kualitasnya agar lebih layak
digunakan sebagai aplikasi skripsi: profesional, konsisten, mudah
dipahami, responsif, aman, dan tidak terlihat seperti antarmuka yang
dihasilkan AI secara mentah.

Fokus revisi bukan membuat ulang konsep sistem dari nol. Implementasi
harus memperbaiki source yang ada tanpa mengubah logika utama Certainty
Factor (CF) dan DASS-21 yang sudah menjadi inti penelitian, kecuali
ditemukan bug yang terbukti memengaruhi hasil.

------------------------------------------------------------------------

## 2. Ringkasan Audit Source Saat Ini

Audit dilakukan terhadap source `MyKonselor_Clean_Rebuild(1).zip` dan
dump database `mykonselor.zip`.

### 2.1 Temuan yang sudah baik

1.  Frontend source sudah menggunakan Bootstrap 5.3.3 dan Bootstrap
    Icons.
2.  Tailwind tidak terdapat pada `package.json` versi source yang
    diaudit.
3.  Route `/` sudah bersifat publik sehingga secara backend pengguna
    yang sudah login sebenarnya tetap dapat mengakses landing page.
4.  Route fitur mahasiswa berada di middleware `auth`.
5.  Route admin dilindungi kombinasi middleware `auth` dan `admin`.
6.  `AssessmentPolicy` sudah mencegah mahasiswa membuka hasil assessment
    milik pengguna lain.
7.  Login sudah memiliki rate limiting.
8.  Validasi registrasi untuk nama dan NIM sudah cukup ketat:
    -   nama hanya huruf dan spasi;
    -   NIM tepat 12 digit;
    -   NIM unik.
9.  Source sudah memiliki unit/feature test untuk CF, DASS-21,
    kelengkapan assessment, dan proteksi akses hasil assessment.

### 2.2 Temuan yang wajib direvisi

1.  Dark mode hanya mengubah atribut custom `data-theme`, belum
    menyinkronkan `data-bs-theme="dark"` milik Bootstrap.
2.  Beberapa komponen Bootstrap dapat tetap memakai warna default light
    mode, menyebabkan teks, ikon, border, alert, tombol outline,
    dropdown, dan elemen lain tidak selalu kontras.
3.  Layout halaman mahasiswa belum menggunakan struktur sticky footer
    (`min-height: 100vh` + `main flex-grow`), sehingga pada halaman
    dengan sedikit konten seperti Riwayat, footer dapat terlihat terlalu
    tinggi.
4.  Navbar pengguna yang sudah login tidak menyediakan tautan eksplisit
    kembali ke Landing Page/Beranda.
5.  Logo/brand MyKonselor pada layout pengguna mengarah ke Dashboard,
    bukan Landing Page.
6.  Dashboard masih memakai emoji sebagai ikon utama dan quick menu
    sehingga tampil kurang formal.
7.  Landing page juga masih memiliki beberapa emoji dekoratif.
8.  Terdapat penggunaan em dash `—` pada teks user-facing sehingga gaya
    copy terasa tidak natural/konsisten.
9.  Card "Mulai Skrining" belum memiliki disclaimer yang cukup jelas
    bahwa hasil adalah skrining awal dan bukan diagnosis/pengganti
    psikolog.
10. Halaman rujukan masih memiliki layanan yang hanya berupa deskripsi.
    Bahkan tautan "Hubungi via WhatsApp" untuk Pusat Layanan Psikologi
    UIN masih `href="#"`.
11. Nomor/kontak empat layanan rujukan belum dimodelkan secara konsisten
    sebagai data yang dapat diklik.
12. `LoginRequest` belum memeriksa `status = aktif`, sehingga akun
    berstatus nonaktif berpotensi tetap dapat lolos `Auth::attempt`.
13. Form registrasi meminta persetujuan (`consent`) tetapi source yang
    diaudit belum menunjukkan penyimpanan timestamp persetujuan
    penelitian ke database.
14. Requirement lama tentang field `akun` tidak terdapat pada source
    clean yang diaudit; source saat ini memakai NIM untuk login dan
    email sebagai data akun. Jangan menambahkan field baru tanpa
    keputusan final karena dapat mengulang konflik migration sebelumnya.
15. Paket source clean yang diaudit merupakan overlay/rebuild dan tidak
    berisi Laravel base project lengkap (`artisan`, `config`, dll.),
    sehingga runtime end-to-end tidak dapat dijalankan langsung hanya
    dari ZIP tersebut.

------------------------------------------------------------------------

## 3. Prinsip Desain Final

### 3.1 Karakter visual

MyKonselor harus terasa:

-   akademis;
-   profesional;
-   bersih;
-   tenang;
-   modern;
-   tidak kekanak-kanakan;
-   tidak berlebihan dalam ilustrasi;
-   tidak menggunakan emoji sebagai ikon antarmuka utama.

Gunakan **Bootstrap Icons** untuk ikon fungsional dan dekoratif. Ikon
sebaiknya bergaya outline/line, ukuran konsisten, dan memiliki
`aria-hidden="true"` jika hanya dekoratif.

### 3.2 Bahasa

Bahasa tetap ramah untuk mahasiswa tetapi tidak terlalu percakapan.

Contoh yang dihindari:

> Bagaimana perasaanmu hari ini? Yuk cek kondisi kesehatan mentalmu.

Contoh yang lebih profesional:

> Pantau kondisi kesehatan mental Anda melalui skrining awal MyKonselor.

Untuk konsistensi akademis, pilih satu gaya sapaan utama. Rekomendasi:
gunakan **"Anda"** pada halaman informatif/formal dan hindari
pencampuran "kamu", "Anda", "mu", dan bahasa slang.

### 3.3 Larangan em dash

Seluruh teks user-facing harus diperiksa dan simbol:

`—`

diganti sesuai konteks dengan:

-   titik;
-   koma;
-   titik dua;
-   tanda kurung;
-   atau pemisahan menjadi dua kalimat.

Tidak perlu mengganti simbol yang berada di komentar teknis atau
dokumentasi internal bila tidak tampil ke pengguna.

Source saat ini masih memiliki em dash pada beberapa view seperti
landing page, footer, wizard, halaman rujukan, audit log admin, dan PDF
report.

------------------------------------------------------------------------

## 4. Requirement Dark Mode

### 4.1 Masalah saat ini

Theme script hanya menjalankan:

``` js
document.documentElement.setAttribute('data-theme', theme);
```

Bootstrap 5.3 memiliki color mode sendiri melalui `data-bs-theme`.
Akibatnya komponen Bootstrap tertentu tidak otomatis mengikuti dark
mode.

### 4.2 Requirement implementasi

Saat tema berubah, sistem WAJIB mengatur keduanya:

``` js
document.documentElement.setAttribute('data-theme', theme);
document.documentElement.setAttribute('data-bs-theme', theme);
```

Tema tersimpan di `localStorage` dan harus diterapkan seawal mungkin
agar tidak terjadi flash light mode ketika halaman dimuat.

### 4.3 Komponen yang wajib diuji

Dark mode harus diperiksa pada:

-   body/background;
-   navbar;
-   footer;
-   card;
-   modal;
-   dropdown;
-   offcanvas;
-   table;
-   pagination;
-   alert;
-   badge;
-   form-control;
-   form-select;
-   textarea;
-   placeholder;
-   input validation;
-   tombol primary;
-   tombol secondary;
-   tombol outline;
-   tombol danger;
-   navbar toggler;
-   Bootstrap Icons;
-   link;
-   muted text;
-   border;
-   Livewire answer card;
-   halaman PDF tidak perlu mengikuti dark mode.

### 4.4 Acceptance Criteria Dark Mode

-   Tidak ada teks terang di atas background terang.
-   Tidak ada teks gelap di atas background gelap.
-   Ikon selalu terlihat pada light dan dark mode.
-   Border card/input tetap terlihat tetapi tidak terlalu kontras.
-   State hover, active, focus, disabled, dan selected tetap terbaca.
-   Toggle tema konsisten di Landing, Dashboard, Skrining, Hasil,
    Riwayat, Artikel, Rujukan, dan halaman Admin.
-   Refresh halaman mempertahankan tema.
-   Navigasi antar halaman tidak mengembalikan tema secara tiba-tiba ke
    light.

------------------------------------------------------------------------

## 5. Layout dan Footer

### 5.1 Masalah

Footer halaman Riwayat dapat berada terlalu tinggi ketika konten
sedikit.

### 5.2 Requirement

Layout utama pengguna dan admin menggunakan pola:

``` css
body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

main {
    flex: 1 0 auto;
}

footer {
    margin-top: auto;
}
```

Implementasi harus disesuaikan agar tidak merusak halaman yang kontennya
panjang.

### 5.3 Acceptance Criteria

-   Footer berada di bawah viewport ketika konten pendek.
-   Footer turun secara natural ketika konten panjang.
-   Tidak ada ruang kosong berlebihan.
-   Halaman Riwayat dengan 0 data, 1 data, dan banyak data harus diuji.

------------------------------------------------------------------------

## 6. Navigasi Pengguna Login

### 6.1 Requirement

Mahasiswa yang sudah login **tetap boleh membuka Landing Page**.

Navbar authenticated minimal memiliki:

-   Beranda
-   Dashboard
-   Riwayat
-   Artikel
-   Toggle tema
-   Keluar

Brand `MyKonselor` direkomendasikan mengarah ke `route('welcome')`,
bukan selalu ke Dashboard.

Landing page ketika user sudah login tetap menampilkan tombol:

-   Dashboard
-   Mulai Skrining

dan tidak memaksa redirect otomatis ke Dashboard.

### 6.2 Security Boundary

Akses Landing Page oleh user login tidak boleh berarti halaman privat
menjadi publik.

Guest yang mengetik URL:

-   `/dashboard`
-   `/assessment`
-   `/history`
-   `/admin/...`

harus tetap ditolak/diarahkan ke login sesuai middleware.

Mahasiswa yang mengetik `/admin/...` harus mendapat 403 atau penolakan
yang sesuai.

------------------------------------------------------------------------

## 7. Revisi Dashboard Mahasiswa

### 7.1 Hero/Sambutan

Hapus emoji:

-   🧠
-   👋

Contoh final:

**Halo, Mahasiswa Contoh**

Subteks:

> Pantau kondisi kesehatan mental Anda melalui skrining awal yang
> tersedia di MyKonselor.

Gunakan ikon Bootstrap outline, misalnya `bi-person`, `bi-heart-pulse`,
atau ikon netral lain yang sesuai.

### 7.2 Quick Menu

Ganti:

-   📊 menjadi Bootstrap Icon yang sesuai, misalnya `bi-clock-history`;
-   📚 menjadi `bi-journal-text`;
-   🔒 menjadi `bi-shield-lock`.

Semua ikon harus berasal dari satu library yang sama agar visual
konsisten.

### 7.3 Card Mulai Skrining

Card harus memiliki:

1.  judul;
2.  deskripsi singkat;
3.  estimasi durasi;
4.  disclaimer;
5.  tombol CTA.

Copy yang direkomendasikan:

**Skrining Kesehatan Mental**

> Jawab 21 pertanyaan mengenai kondisi yang Anda rasakan dalam periode
> yang ditentukan. Proses ini membutuhkan waktu sekitar 5 menit.

Disclaimer:

> **Catatan:** MyKonselor merupakan alat skrining awal. Hasil yang
> diberikan bukan diagnosis medis dan tidak menggantikan pemeriksaan,
> konsultasi, atau penanganan oleh psikolog maupun tenaga kesehatan
> profesional.

CTA:

**Mulai Skrining**

Jika assessment belum selesai:

**Lanjutkan Skrining**

Disclaimer harus tetap tampil pada kedua kondisi.

------------------------------------------------------------------------

## 8. Revisi Landing Page

Landing page boleh tetap lebih komunikatif daripada dashboard, tetapi
harus konsisten dan profesional.

### Requirement

-   Hapus emoji dekoratif utama.
-   Ganti dengan Bootstrap Icons, CSS geometric decoration, atau
    ilustrasi non-emoji.
-   Hapus semua em dash pada copy.
-   Pertahankan penjelasan bahwa hasil bukan diagnosis.
-   Pastikan CTA user login menuju skrining tanpa harus logout/register
    ulang.
-   Tambahkan akses "Dashboard" bagi user login.
-   Navbar harus menyediakan navigasi yang masuk akal untuk guest dan
    authenticated user.

### Copy yang perlu ditinjau

Kalimat seperti:

> Kenali kondisi mentalmu, sebelum makin berat.

dapat dipertahankan jika gaya bahasa skripsi memang menginginkan tone
ramah, tetapi seluruh halaman harus konsisten. Jangan mencampur "kamu"
dan "Anda" tanpa alasan.

------------------------------------------------------------------------

## 9. Halaman Riwayat

### Requirement

-   Perbaiki sticky footer.
-   Pastikan card history memiliki hover/focus yang tetap terbaca di
    dark mode.
-   Severity badge harus memiliki kontras memadai.
-   Tanggal, skor, dan severity harus mudah dipindai.
-   Empty state harus profesional dan tidak terlalu kosong.
-   Tombol "Mulai Skrining Pertama" tetap tersedia jika belum ada
    riwayat.
-   Pagination wajib mengikuti dark mode.

------------------------------------------------------------------------

## 10. Halaman Rujukan dan Kontak WhatsApp

### 10.1 Masalah saat ini

Empat opsi yang tampil:

1.  BPJS Kesehatan / Puskesmas / RSUD Tangerang Selatan
2.  Layanan Sejiwa/Kemenkes
3.  Yayasan Pulih
4.  Pusat Layanan Psikologi UIN

belum memiliki pola tindakan yang konsisten. Sebagian hanya deskripsi
dan PLP UIN masih memakai `href="#"`.

### 10.2 Requirement UX

Setiap layanan yang memang memiliki nomor WhatsApp terverifikasi harus
memiliki tombol:

**Hubungi via WhatsApp**

Gunakan Bootstrap Icon:

`bi-whatsapp`

Tombol membuka WhatsApp pada tab baru menggunakan format nomor
internasional:

``` text
https://wa.me/62XXXXXXXXXXX
```

Nomor tidak boleh menggunakan:

-   spasi;
-   tanda `+`;
-   tanda `-`;
-   angka awal `0`.

### 10.3 Keamanan Link

Gunakan:

``` html
target="_blank"
rel="noopener noreferrer"
```

### 10.4 Data kontak

Nomor WA **tidak boleh dikarang** dan tidak boleh hanya disalin dari
sumber tidak resmi.

Sebelum implementasi final, empat kontak harus diverifikasi terhadap
sumber resmi/otoritatif. Bila sebuah layanan tidak mempunyai WhatsApp
resmi, jangan membuat tombol WhatsApp palsu; tampilkan kanal resmi yang
benar, misalnya telepon atau website.

### 10.5 Struktur data

Hindari hard-code empat layanan langsung di Blade.

Rekomendasi minimal:

``` php
[
    'nama' => '...',
    'deskripsi' => '...',
    'whatsapp' => '62...',
    'telepon' => '...',
    'website' => '...',
    'aktif' => true,
]
```

Lebih baik lagi dibuat master data layanan rujukan di database bila
admin perlu mengubah kontak tanpa edit source.

### 10.6 Acceptance Criteria

-   Tidak ada `href="#"` untuk tombol kontak.
-   Setiap CTA memiliki tujuan valid.
-   Klik WhatsApp membuka chat ke nomor yang benar.
-   Mobile dan desktop diuji.
-   Tombol tetap terbaca pada dark mode.
-   Informasi darurat dibedakan secara visual dari layanan konseling
    biasa.

------------------------------------------------------------------------

## 11. Konsistensi Copy dan "AI-Looking Text"

Lakukan content audit seluruh:

`resources/views/**/*.blade.php`

### Wajib diperbaiki

-   em dash berlebihan;
-   emoji;
-   kalimat terlalu generik;
-   pengulangan disclaimer dengan redaksi berbeda-beda;
-   campuran bahasa formal dan slang;
-   istilah "diagnosis" jika konteks seharusnya "skrining";
-   penggunaan kata "severity" di UI pengguna.

Contoh:

> Berdasarkan hasil skrining kamu --- severity tertinggi: Berat

ubah menjadi:

> Berdasarkan hasil skrining, tingkat keparahan tertinggi yang
> teridentifikasi adalah **Berat**.

Istilah teknis seperti `severity` boleh dipakai di backend/source,
tetapi UI pengguna sebaiknya memakai **tingkat keparahan**.

------------------------------------------------------------------------

## 12. Security dan Authorization

### 12.1 Status akun

Saat ini autentikasi memakai NIM + password tetapi belum terlihat
memblokir `status = nonaktif`.

Login harus gagal bila:

``` text
status != aktif
```

Acceptance Criteria:

-   user aktif + password benar: login berhasil;
-   user nonaktif + password benar: login ditolak;
-   pesan error tidak membocorkan apakah NIM terdaftar;
-   rate limiter tetap aktif.

### 12.2 Proteksi route

Pertahankan:

-   middleware `auth` untuk halaman privat;
-   middleware `admin` untuk admin;
-   policy ownership untuk assessment.

Tambahkan test otomatis untuk:

-   guest tidak dapat membuka dashboard;
-   guest tidak dapat membuka assessment;
-   guest tidak dapat membuka history;
-   guest tidak dapat membuka result langsung;
-   mahasiswa tidak dapat membuka admin;
-   mahasiswa A tidak dapat membuka assessment mahasiswa B;
-   admin dapat membuka halaman admin;
-   logout menginvalidasi session.

### 12.3 Registrasi

Pertahankan:

-   nama hanya huruf/spasi;
-   NIM tepat 12 digit;
-   NIM unik;
-   nomor telepon tervalidasi;
-   password confirmation;
-   consent wajib.

Tambahkan:

-   normalisasi spasi pada nama;
-   trim input;
-   validasi NIM juga pada login (`digits:12`);
-   password policy yang eksplisit;
-   pencatatan waktu consent jika consent penelitian memang menjadi
    requirement.

### 12.4 Consent penelitian

Karena form menyatakan data digunakan untuk kebutuhan penelitian, sistem
sebaiknya menyimpan bukti persetujuan, minimal:

-   `data_consent_at`;
-   versi teks persetujuan bila diperlukan penelitian;
-   timestamp.

Jangan hanya memvalidasi checkbox lalu membuang informasinya.

### 12.5 CSRF dan IDOR

Pertahankan CSRF Laravel pada form POST/PATCH/DELETE.

Semua resource yang terkait user harus dicek ownership/authorization di
backend, bukan hanya disembunyikan dari navbar.

------------------------------------------------------------------------

## 13. Integritas Database dan Migration

Ini prioritas tinggi karena pada proses setup sebelumnya ditemukan
migration duplikat/hilang.

### Requirement

Satu fresh install harus berhasil hanya dengan:

``` bash
php artisan migrate:fresh --seed
```

tanpa:

-   menghapus migration manual;
-   rename migration manual;
-   menambahkan kolom satu per satu setelah error;
-   duplicate column;
-   missing `users` table.

### Acceptance Criteria

-   tabel `users` dibuat sebelum migration yang mengubah `users`;
-   `email_verified_at` tersedia jika dipakai seeder/model;
-   field user tidak dibuat dua kali;
-   semua seeder selesai;
-   akun testing admin dan mahasiswa berhasil dibuat;
-   `php artisan migrate:fresh --seed` selesai tanpa FAIL.

------------------------------------------------------------------------

## 14. Akun Testing

Akun yang didefinisikan oleh seeder source:

### Mahasiswa

-   NIM: `123456789012`
-   Password: `password`

### Admin

-   NIM: `000000000000`
-   Password: `password`

Password default hanya untuk development/testing dan harus diganti
sebelum deployment nyata.

------------------------------------------------------------------------

## 15. Test Plan End-to-End

### A. Guest

1.  Buka landing page.
2.  Ubah light/dark mode.
3.  Buka Login.
4.  Buka Register.
5.  Coba akses `/dashboard` langsung.
6.  Coba akses `/assessment` langsung.
7.  Coba akses `/history` langsung.
8.  Coba akses `/admin/dashboard` langsung.

**Expected:** halaman publik terbuka; halaman privat tidak dapat diakses
tanpa autentikasi.

### B. Login Mahasiswa

Gunakan:

`123456789012 / password`

Uji:

1.  login berhasil;
2.  diarahkan ke Dashboard;
3.  klik Beranda kembali ke Landing Page;
4.  dari Landing Page kembali ke Dashboard;
5.  Artikel dan Riwayat tetap dapat dibuka;
6.  theme tidak berubah saat berpindah halaman;
7.  akses `/admin/dashboard` ditolak;
8.  logout berhasil.

### C. Dashboard

Uji:

-   tidak ada emoji;
-   semua ikon Bootstrap outline;
-   nama user tampil benar;
-   card skrining memiliki disclaimer;
-   CTA benar;
-   dark mode tidak menghilangkan ikon/teks.

### D. Skrining

Uji:

-   21 pertanyaan tampil;
-   jawaban tersimpan;
-   back/next berfungsi;
-   refresh tidak merusak state;
-   assessment tidak dapat difinalisasi jika jawaban belum lengkap;
-   hasil dapat dihitung setelah lengkap;
-   copy tidak menyebut hasil sebagai diagnosis medis.

### E. Hasil

Uji:

-   CF tampil;
-   skor DASS-21 tampil;
-   severity Stres/Kecemasan/Depresi tampil;
-   disclaimer tampil;
-   rekomendasi sesuai kondisi;
-   tombol rujukan muncul pada kondisi yang sesuai;
-   dark mode aman.

### F. Riwayat

Uji:

-   empty state;
-   satu riwayat;
-   banyak riwayat;
-   pagination;
-   buka detail hasil;
-   footer selalu di bawah.

### G. Rujukan

Uji:

-   empat layanan tampil;
-   setiap nomor WA yang valid memiliki tombol;
-   tidak ada `href="#"`;
-   link WA membuka nomor yang benar;
-   link eksternal memakai `noopener noreferrer`;
-   pengajuan konseling tersimpan;
-   user tidak dapat mengajukan referral untuk assessment user lain.

### H. Admin

Gunakan:

`000000000000 / password`

Uji:

-   login admin;
-   dashboard admin;
-   users;
-   toggle status;
-   reset password;
-   rules;
-   audit log;
-   articles;
-   reports;
-   export;
-   dark mode seluruh halaman;
-   admin tetap dapat mengakses landing page bila diperlukan.

### I. User Nonaktif

1.  Admin nonaktifkan akun mahasiswa.
2.  Logout.
3.  Coba login menggunakan password benar.

**Expected:** login ditolak.

------------------------------------------------------------------------

## 16. Automated Test yang Harus Ditambahkan

Selain test CF/DASS yang sudah ada, tambahkan feature tests:

``` text
GuestAccessTest
AuthenticatedNavigationTest
InactiveUserLoginTest
AdminAuthorizationTest
RegistrationValidationTest
ReferralAuthorizationTest
ThemeMarkupTest (opsional)
```

Minimal assertions:

-   guest redirect login;
-   mahasiswa mendapat 403 pada admin;
-   admin mendapat 200 pada admin;
-   NIM bukan 12 digit ditolak;
-   nama berangka/simbol ditolak;
-   NIM duplikat ditolak;
-   consent kosong ditolak;
-   inactive user gagal login;
-   assessment ownership terlindungi;
-   referral ownership terlindungi.

------------------------------------------------------------------------

## 17. Urutan Implementasi

### P0 - Critical

1.  Stabilkan migration/fresh install.
2.  Perbaiki login user nonaktif.
3.  Pastikan authorization guest/user/admin.
4.  Pastikan ownership assessment/referral.
5.  Pastikan logic CF dan DASS tetap lulus test.

### P1 - High

6.  Perbaiki dark mode secara global.
7.  Perbaiki sticky footer.
8.  Tambahkan navigasi Beranda untuk authenticated user.
9.  Ganti emoji dengan Bootstrap Icons.
10. Tambahkan disclaimer Dashboard.
11. Perbaiki seluruh tombol/link rujukan.
12. Bersihkan em dash dan copy yang terasa AI-generated.

### P2 - Medium

13. Konsistensi bahasa.
14. Konsistensi spacing, typography, badge, alert, button.
15. Empty state dan hover/focus state.
16. Accessibility keyboard/focus.
17. Responsive testing.

### P3 - Final QA

18. `php artisan test`.
19. `php artisan migrate:fresh --seed`.
20. `npm run build`.
21. Uji manual akun mahasiswa.
22. Uji manual akun admin.
23. Uji mobile/tablet/desktop.
24. Uji light/dark seluruh page.
25. Pastikan tidak ada em dash/emoji user-facing yang tidak disengaja.
26. Pastikan tidak ada dead link atau `href="#"`.

------------------------------------------------------------------------

## 18. Definition of Done

Revisi dianggap selesai hanya jika:

-   project memakai Bootstrap, bukan Tailwind;
-   build frontend sukses;
-   migration + seeder sukses dari database kosong;
-   seluruh automated test lulus;
-   login mahasiswa dan admin berhasil;
-   akun nonaktif tidak dapat login;
-   user login dapat bolak-balik Landing Page dan Dashboard;
-   guest tidak dapat mengakses halaman privat melalui URL;
-   mahasiswa tidak dapat mengakses admin;
-   dark mode konsisten pada seluruh halaman;
-   footer Riwayat berada pada posisi yang benar;
-   dashboard tidak menggunakan emoji sebagai ikon;
-   disclaimer skrining tersedia dan jelas;
-   semua copy user-facing telah diaudit;
-   simbol em dash tidak digunakan secara berlebihan;
-   opsi rujukan memiliki CTA valid sesuai kanal resmi;
-   tidak ada link placeholder;
-   hasil skrining selalu dinyatakan sebagai skrining awal, bukan
    diagnosis;
-   seluruh perubahan tidak merusak perhitungan CF/DASS-21.

------------------------------------------------------------------------

## 19. Catatan Hasil Audit Testing Saat Dokumen Dibuat

### Dapat diverifikasi dari source

**PASS secara statis:**

-   `/` adalah route publik.
-   route mahasiswa memakai middleware `auth`.
-   route admin memakai middleware `auth` + `admin`.
-   policy assessment membatasi akses berdasarkan pemilik/admin.
-   login memiliki rate limiting.
-   registrasi membatasi nama ke huruf/spasi.
-   registrasi membatasi NIM tepat 12 digit.
-   source memiliki test CF, DASS-21, kelengkapan jawaban, dan IDOR
    assessment.
-   akun testing mahasiswa/admin didefinisikan oleh seeder.

**FAIL / perlu revisi:**

-   login belum memasukkan status akun sebagai syarat autentikasi.
-   dark mode belum menyinkronkan Bootstrap `data-bs-theme`.
-   layout belum menjamin sticky footer.
-   navbar authenticated belum memiliki menu Beranda.
-   dashboard dan beberapa halaman masih menggunakan emoji.
-   em dash masih ditemukan di sejumlah view.
-   PLP UIN masih menggunakan `href="#"`.
-   layanan rujukan belum memiliki struktur CTA kontak yang konsisten.
-   consent belum terlihat disimpan sebagai timestamp persetujuan.
-   clean ZIP yang diaudit tidak merupakan Laravel runtime lengkap
    sehingga login browser dengan kredensial di atas tidak dapat
    dieksekusi end-to-end langsung dari paket itu saja.

### Catatan QA

Jangan mengklaim "semua testing lulus" hanya karena source terlihat
benar. Setelah implementasi revisi diterapkan ke project Laravel
lengkap, wajib jalankan test otomatis dan test browser sesuai Bagian 15.

------------------------------------------------------------------------

## 20. Instruksi untuk AI/Developer yang Mengerjakan Revisi

1.  Jangan mengganti arsitektur Laravel yang sudah ada tanpa kebutuhan.
2.  Jangan mengubah rumus CF/DASS hanya untuk merapikan kode.
3.  Jangan menambah Tailwind.
4.  Gunakan Bootstrap 5 dan Bootstrap Icons.
5.  Jangan membuat nomor WhatsApp rujukan.
6.  Jangan menghapus security middleware/policy.
7.  Jangan membuat migration duplikat.
8.  Jangan hard-code style light mode yang merusak dark mode.
9.  Jangan menggunakan emoji sebagai ikon UI formal.
10. Jangan mengubah istilah "skrining" menjadi "diagnosis".
11. Setiap perubahan harus diuji pada guest, mahasiswa, dan admin.
12. Prioritaskan perbaikan source reusable (layout, theme, component)
    daripada patch CSS per halaman.
13. Jangan menganggap UI selesai hanya karena tampak benar di light
    mode.
14. Jangan meninggalkan placeholder link, TODO user-facing, atau tombol
    tanpa fungsi.
15. Setelah selesai, laporkan file yang diubah dan hasil test secara
    eksplisit.

---

## 21. Tambahan Revisi Admin, Registrasi, Page Title, dan Branding Institusi

Bagian ini merupakan requirement tambahan dan menjadi bagian resmi dari master PRD MyKonselor.

### 21.1 Perbaikan Dark Mode pada Seluruh Halaman Admin

Dark mode wajib diperbaiki tidak hanya pada halaman mahasiswa, tetapi juga pada seluruh antarmuka admin. Implementasi tidak boleh menggunakan patch warna per halaman yang saling bertabrakan. Gunakan sistem theme global yang konsisten dan sinkronkan custom theme dengan Bootstrap 5.3 melalui `data-bs-theme`.

Halaman dan komponen admin yang wajib diaudit meliputi:

- Dashboard Admin;
- Kelola Pengguna;
- Kelola Aturan/Rule CF;
- Audit Log;
- Kelola Artikel;
- Laporan;
- Pengajuan Rujukan;
- tabel dan header tabel;
- form-control, select, textarea, dan placeholder;
- modal dan dialog konfirmasi;
- dropdown;
- pagination;
- badge status;
- alert;
- card statistik;
- navbar/sidebar;
- Bootstrap Icons;
- tombol primary, secondary, danger, dan outline;
- hover, focus, active, selected, dan disabled state.

**Acceptance Criteria:**

- Tidak ada teks atau ikon yang hilang karena warna foreground dan background terlalu mirip.
- Seluruh card admin berubah ke warna yang sesuai ketika dark mode aktif.
- Tabel tetap mudah dibaca, termasuk header, row, border, hover, dan action button.
- Badge status tetap memiliki kontras yang jelas.
- Modal, dropdown, pagination, dan form mengikuti tema aktif.
- Tema tetap konsisten setelah refresh dan perpindahan halaman admin.

### 21.2 Fitur Admin untuk Pengajuan Rujukan Mahasiswa

Admin tidak boleh diwajibkan membuka database secara manual untuk mengetahui adanya pengajuan rujukan dari mahasiswa.

Tambahkan menu admin bernama **Pengajuan Rujukan**.

Dashboard Admin juga harus menampilkan indikator jumlah pengajuan yang masih menunggu penanganan agar admin dapat mengetahui adanya permintaan baru tanpa membuka database.

Data minimal yang ditampilkan pada daftar pengajuan:

- nama mahasiswa;
- NIM;
- tanggal/waktu pengajuan;
- assessment/hasil skrining yang terkait;
- ringkasan tingkat hasil skrining yang relevan;
- catatan mahasiswa;
- status pengajuan;
- aksi untuk membuka detail.

Status pengajuan direkomendasikan menggunakan alur yang jelas, misalnya:

- `pending` -> **Menunggu**;
- `processing` -> **Diproses**;
- `completed` -> **Selesai**;
- `rejected` -> **Ditolak** bila memang diperlukan dalam proses bisnis.

Admin harus dapat membuka detail pengajuan dan memperbarui status tanpa mengedit database secara manual. Setiap perubahan status harus tersimpan di database beserta timestamp perubahan. Bila memungkinkan, simpan juga admin yang melakukan perubahan untuk kebutuhan audit.

**Acceptance Criteria:**

- Pengajuan yang dibuat mahasiswa langsung tersedia pada menu admin.
- Jumlah pengajuan menunggu terlihat di Dashboard Admin.
- Admin dapat melihat detail tanpa phpMyAdmin/akses database manual.
- Admin dapat memperbarui status.
- Perubahan status persisten setelah refresh.
- Mahasiswa tidak dapat membuka halaman pengelolaan pengajuan milik admin.
- Dark mode halaman Pengajuan Rujukan berfungsi penuh.

### 21.3 Registrasi Tidak Meminta Fakultas

Field **Fakultas** harus dihapus dari form registrasi mahasiswa karena ruang lingkup pengujian/penelitian MyKonselor ditujukan pada Fakultas Ilmu Komputer. Responden tidak perlu memilih atau mengetik fakultas sendiri.

Penghapusan harus dilakukan secara menyeluruh, bukan hanya menyembunyikan input pada Blade.

Developer wajib memeriksa dan menyesuaikan:

- form registrasi;
- request/controller validation;
- model/fillable bila terkait;
- halaman profil bila field tersebut ditampilkan;
- seeder/factory;
- automated test;
- laporan/export;
- filter admin;
- migration/database bila memang field tidak lagi diperlukan;
- seluruh copy yang meminta pengguna memilih fakultas.

Jika informasi fakultas masih diperlukan sebagai metadata penelitian atau laporan, nilai dapat ditetapkan secara internal sebagai **Fakultas Ilmu Komputer** tanpa meminta input mahasiswa. Jangan menambahkan field baru atau migration duplikat hanya untuk perubahan ini.

### 21.4 Konsistensi Judul pada Browser Tab

Seluruh halaman wajib menggunakan format judul browser yang konsisten:

`MyKonselor - Nama Halaman`

Contoh:

- `MyKonselor - Beranda`
- `MyKonselor - Login`
- `MyKonselor - Registrasi`
- `MyKonselor - Dashboard`
- `MyKonselor - Skrining`
- `MyKonselor - Hasil Skrining`
- `MyKonselor - Riwayat`
- `MyKonselor - Artikel`
- `MyKonselor - Rujukan`
- `MyKonselor - Dashboard Admin`
- `MyKonselor - Pengguna`
- `MyKonselor - Aturan Sistem Pakar`
- `MyKonselor - Audit Log`
- `MyKonselor - Laporan`
- `MyKonselor - Pengajuan Rujukan`

Gunakan satu mekanisme reusable pada layout Blade, misalnya melalui `@yield('title')`, component property, atau mekanisme Laravel yang setara. Jangan hard-code `<title>` secara tidak konsisten di setiap halaman.

**Acceptance Criteria:**

- Tidak ada tab yang hanya bertuliskan `MyKonselor` jika halaman memiliki nama spesifik.
- Tidak ada halaman yang memakai urutan berbeda seperti `Dashboard - MyKonselor`.
- Guest, mahasiswa, dan admin mengikuti format yang sama.
- Error page yang dikustomisasi juga sebaiknya mengikuti pola branding jika tersedia.

### 21.5 Logo Universitas Pamulang dan Branding MyKonselor

Identitas MyKonselor harus terlihat sebagai aplikasi akademik yang terkait dengan Universitas Pamulang.

Pada area brand/logo website, tampilkan identitas **MyKonselor** bersama logo resmi Universitas Pamulang dengan komposisi yang bersih dan tidak berlebihan.

Requirement:

- gunakan file logo UNPAM resmi yang disediakan/diizinkan untuk project;
- simpan asset secara lokal di folder public project, bukan hotlink ke situs eksternal;
- logo harus tajam pada desktop dan mobile;
- ukuran logo tidak boleh mendominasi navbar;
- tulisan `MyKonselor` tetap menjadi nama produk utama;
- gunakan alt text yang sesuai;
- logo tidak boleh terdistorsi karena width/height yang salah;
- tampilan logo harus tetap baik pada light dan dark mode;
- periksa navbar guest, mahasiswa, dan admin agar branding konsisten.

Contoh struktur visual yang direkomendasikan:

`[Logo UNPAM] MyKonselor`

atau komposisi setara yang tetap sederhana dan profesional.

### 21.6 Favicon / Ikon Browser Tab

Tambahkan favicon yang menggunakan logo UNPAM atau versi ikon yang disiapkan secara resmi untuk kebutuhan project. Favicon harus tampil bersama page title pada browser tab sehingga identitas aplikasi lebih profesional.

Requirement:

- favicon tersedia dari asset lokal;
- gunakan format web yang sesuai seperti `.ico`, `.png`, atau format favicon lain yang kompatibel;
- hindari gambar beresolusi terlalu rendah;
- favicon digunakan konsisten pada halaman guest, mahasiswa, dan admin;
- tidak boleh kembali menggunakan favicon default Laravel/Vite/browser.

### 21.7 Penambahan Test Plan Admin dan Branding

Tambahkan pengujian berikut pada QA final:

**Admin Dark Mode**

1. Login sebagai admin.
2. Aktifkan dark mode.
3. Buka seluruh menu admin satu per satu.
4. Uji tabel, form, modal, pagination, dropdown, badge, card, icon, dan tombol.
5. Refresh halaman.
6. Berpindah antar menu.

**Expected:** tidak ada elemen yang kehilangan kontras dan tema tetap tersimpan.

**Pengajuan Rujukan**

1. Login mahasiswa.
2. Buat pengajuan rujukan dari assessment yang valid.
3. Logout.
4. Login admin.
5. Pastikan jumlah pengajuan baru tampil pada dashboard.
6. Buka menu Pengajuan Rujukan.
7. Buka detail pengajuan.
8. Ubah status menjadi Diproses.
9. Refresh halaman.
10. Pastikan status tetap tersimpan.

**Registrasi**

1. Buka halaman Registrasi.
2. Pastikan tidak terdapat field Fakultas.
3. Registrasikan mahasiswa menggunakan data valid.
4. Pastikan proses tidak gagal karena backend masih mewajibkan fakultas.

**Page Title**

Buka seluruh halaman utama dan pastikan browser tab mengikuti pola `MyKonselor - Nama Halaman`.

**Branding**

- logo UNPAM tampil proporsional;
- nama MyKonselor terbaca jelas;
- favicon tampil;
- logo tidak rusak pada mobile;
- logo tetap terlihat pada dark mode.

### 21.8 Tambahan Definition of Done

Selain Definition of Done sebelumnya, revisi belum dianggap selesai apabila salah satu kondisi berikut masih terjadi:

- dark mode admin masih memiliki card, teks, ikon, tabel, form, atau modal yang tidak terbaca;
- admin masih harus membuka database untuk membaca pengajuan rujukan;
- pengajuan rujukan tidak memiliki status yang dapat dikelola dari UI admin;
- field Fakultas masih muncul atau masih diwajibkan ketika registrasi;
- judul browser tab tidak mengikuti format `MyKonselor - Nama Halaman`;
- branding navbar belum menyertakan logo UNPAM sesuai asset resmi project;
- favicon masih menggunakan icon default atau tidak tersedia;
- branding berbeda secara tidak perlu antara guest, mahasiswa, dan admin.

### 21.9 Prioritas Implementasi Tambahan

Tambahkan ke urutan implementasi:

**P1 - High**

- perbaiki dark mode seluruh area admin;
- buat halaman Pengajuan Rujukan untuk admin;
- tampilkan indikator pengajuan pending di Dashboard Admin;
- hapus requirement Fakultas dari registrasi;
- standardisasi seluruh page title;
- implementasikan branding logo UNPAM + MyKonselor;
- implementasikan favicon.

**P3 - Final QA**

- lakukan regression test seluruh menu admin pada light dan dark mode;
- buat pengajuan rujukan menggunakan akun mahasiswa testing lalu proses menggunakan akun admin testing;
- verifikasi registrasi berhasil tanpa field Fakultas;
- periksa title dan favicon pada seluruh halaman utama.
