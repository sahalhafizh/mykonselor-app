# EXECUTION PROMPT — MyKonselor untuk ChatGPT Work

## Tujuan Dokumen

Dokumen ini bukan PRD baru. Fungsinya adalah **prompt eksekusi** yang mengubah PRD MyKonselor menjadi instruksi kerja yang tegas untuk ChatGPT Work.

Gunakan dokumen ini bersama:

1. `PRD_Revisi_Menyeluruh_MyKonselor.md` sebagai **source of truth requirement revisi**.
2. Source project Laravel MyKonselor terbaru sebagai **baseline implementasi**.
3. Dokumen rancangan penelitian MyKonselor sebagai **source of truth logika CF, DASS-21, istilah skrining, dan data penelitian**.
4. Dump database hanya sebagai referensi struktur/data development, bukan sebagai alasan untuk mengubah logika penelitian tanpa bukti.

Dokumen ini dibuat agar agent:
- tidak berhenti di audit;
- tidak hanya memberi saran;
- tidak mengklaim selesai tanpa testing;
- tidak membuat migration duplikat;
- tidak mengarang nomor kontak;
- tidak mengubah metode penelitian;
- tidak memperbaiki satu halaman tetapi merusak halaman lain;
- memberikan deliverable akhir yang dapat diperiksa.

---

# MASTER EXECUTION PROMPT

Salin seluruh bagian di bawah ini sebagai instruksi utama ke ChatGPT Work.

---

## PERAN DAN TARGET

Anda bertindak sebagai **senior Laravel engineer, security reviewer, Bootstrap UI engineer, dan QA engineer** untuk project skripsi bernama **MyKonselor**.

Tugas Anda adalah **mengaudit, memperbaiki, menguji, dan menyelesaikan source project Laravel existing** berdasarkan file:

`PRD_Revisi_Menyeluruh_MyKonselor.md`

PRD tersebut adalah requirement utama dan wajib dibaca sebelum mengubah source.

**Jangan membuat project baru dari nol.**
Gunakan project existing sebagai baseline dan pertahankan fitur/kode yang masih benar.

Tugas ini adalah tugas **EKSEKUSI**, bukan konsultasi.

Jangan berhenti setelah:
- menjelaskan masalah;
- membuat rekomendasi;
- membuat checklist;
- atau menunjukkan contoh kode.

Anda harus melakukan perubahan langsung pada source yang tersedia, selama tool dan akses file mengizinkan.

---

# 1. SOURCE OF TRUTH DAN PRIORITAS

Jika terdapat konflik, gunakan urutan prioritas berikut:

1. `PRD_Revisi_Menyeluruh_MyKonselor.md`
2. Dokumen rancangan penelitian / SRD MyKonselor
3. Data master penelitian yang sudah divalidasi
4. Source code existing
5. Preferensi implementasi Anda sendiri

Jangan mengubah requirement penelitian hanya karena source lama berbeda.

Khusus logika penelitian, **jangan mengubah**:
- 21 pertanyaan DASS-21;
- pembagian cluster Stres, Kecemasan, Depresi;
- bobot CF user;
- MB/MD pakar;
- rumus Certainty Factor;
- faktor pengali DASS-21;
- cutoff tingkat keparahan;
- prinsip bahwa hasil adalah skrining awal, bukan diagnosis klinis.

Jika menemukan konflik antara source dan dokumen penelitian, catat konflik tersebut dan perbaiki source mengikuti dokumen penelitian.

---

# 2. ATURAN KERJA WAJIB

## 2.1 Jangan merusak project existing

Sebelum menghapus atau mengganti file:
- cari pemakaian file tersebut;
- periksa route;
- controller;
- service;
- model;
- Livewire;
- Blade;
- test;
- migration;
- seeder.

Jangan melakukan rewrite besar jika refactor terarah sudah cukup.

## 2.2 Jangan gunakan Tailwind

Frontend final harus menggunakan:
- Bootstrap 5;
- Bootstrap Icons;
- CSS custom yang terstruktur;
- JavaScript/Vite sesuai project.

Jangan menambahkan:
- `tailwindcss`;
- `@tailwindcss/vite`;
- class utility Tailwind baru.

Jika dependency Tailwind lama masih tersisa dan tidak digunakan, bersihkan dengan aman setelah memastikan tidak ada view yang bergantung padanya.

## 2.3 Jangan mengarang data

Jangan mengarang:
- nomor WhatsApp;
- hotline;
- alamat;
- URL layanan;
- data ilmiah;
- nilai CF;
- cutoff;
- identitas institusi.

Untuk kontak rujukan:
- gunakan hanya kontak yang sudah tersedia di source/dokumen atau yang dapat diverifikasi dari sumber resmi;
- jika kontak WA resmi tidak tersedia, jangan membuat tombol WhatsApp palsu;
- lanjutkan semua pekerjaan lain dan tandai kontak tersebut sebagai blocker yang membutuhkan data resmi.

## 2.4 Jangan mengklaim testing yang tidak dilakukan

Jangan menulis:
- “semua test lulus”;
- “dark mode sudah sempurna”;
- “login berhasil”;
- “build berhasil”;

jika Anda tidak benar-benar menjalankan atau memverifikasinya.

Bedakan:
- **verified by automated test**;
- **verified by runtime/manual test**;
- **verified by static review**;
- **not testable in current environment**.

---

# 3. PRE-FLIGHT AUDIT

Sebelum modifikasi besar, lakukan audit singkat tetapi nyata terhadap:

- Laravel version;
- PHP requirement;
- `composer.json`;
- `package.json`;
- `vite.config.js`;
- `.env.example`;
- routes;
- middleware;
- policies;
- authentication;
- registration;
- migrations;
- seeders;
- models;
- services;
- Livewire;
- Blade layouts;
- CSS/theme;
- admin pages;
- user pages;
- referral flow;
- existing tests.

Buat daftar:
- file yang akan diubah;
- risiko utama;
- blocker nyata.

**Setelah audit, langsung lanjut implementasi.**
Jangan menunggu persetujuan pengguna untuk perubahan yang sudah jelas diwajibkan PRD.

Hanya minta klarifikasi apabila terdapat blocker yang benar-benar tidak dapat diselesaikan dari source, misalnya:
- asset logo UNPAM resmi tidak tersedia;
- nomor WhatsApp resmi tidak tersedia;
- source Laravel lengkap tidak tersedia;
- database target ternyata berisi data produksi yang berisiko terhapus.

Jika blocker hanya memengaruhi satu fitur, lanjutkan fitur lain terlebih dahulu.

---

# 4. PHASE 0 — STABILKAN PROJECT

Prioritas pertama adalah memastikan project dapat di-install ulang dengan bersih.

## Requirement

Pastikan:

```bash
composer install
npm install
npm run build
```

dapat berjalan pada environment yang kompatibel.

Untuk database development/test, pastikan migration dan seeder konsisten.

Target:

```bash
php artisan migrate:fresh --seed
```

harus berhasil **hanya pada database development/test yang aman untuk dihapus**.

### PERINGATAN

Jangan menjalankan `migrate:fresh` pada:
- production;
- database yang belum dipastikan disposable;
- database yang berisi data penting.

Jika status database tidak jelas:
- gunakan database testing terpisah;
- atau buat backup/snapshot sebelum tindakan destruktif.

## Wajib diperiksa

- migration `users` dibuat sebelum migration yang mengubah tabel `users`;
- tidak ada duplicate column;
- tidak ada migration duplikat;
- tidak ada dependency field yang hilang seperti `email_verified_at`;
- seeder user/admin berhasil;
- foreign key valid;
- migration referral valid;
- test database dapat dibangun dari nol.

---

# 5. PHASE 1 — SECURITY DAN AUTHORIZATION

Implementasikan requirement security PRD terlebih dahulu.

## 5.1 Login

Login mahasiswa/admin menggunakan identitas existing yang sudah ditetapkan project.

Akun development untuk testing:

### Mahasiswa

```text
NIM: 123456789012
Password: password
```

### Admin

```text
NIM: 000000000000
Password: password
```

Password ini hanya untuk development/testing.

## 5.2 Status akun

Akun dengan:

```text
status != aktif
```

tidak boleh dapat login walaupun password benar.

Pesan login gagal tidak boleh membocorkan apakah:
- NIM terdaftar;
- password salah;
- akun nonaktif.

Gunakan pesan generik yang aman.

## 5.3 Route Protection

Pastikan:

### Guest tidak boleh mengakses

- Dashboard;
- Skrining;
- Riwayat;
- Detail Hasil;
- Profil;
- halaman privat mahasiswa;
- semua `/admin/*`.

### Mahasiswa

- boleh membuka Landing Page;
- boleh membuka Dashboard;
- boleh membuka fitur miliknya;
- tidak boleh membuka admin;
- tidak boleh membuka assessment/result/referral milik mahasiswa lain.

### Admin

- boleh membuka area admin sesuai requirement;
- authorization tetap dilakukan server-side.

## 5.4 IDOR

Audit semua route yang memakai ID resource.

Jangan hanya:

```php
Model::findOrFail($id);
```

untuk resource milik user.

Gunakan:
- relationship authenticated user;
- Policy;
- Gate;
- authorization equivalent.

Lindungi:
- assessment;
- result;
- referral;
- export pribadi;
- resource user lain.

## 5.5 Registration

Form registrasi mahasiswa final:

- Nama Lengkap;
- NIM;
- No. Telepon;
- Email/data akun sesuai mekanisme source final;
- Password;
- Konfirmasi Password;
- Persetujuan penggunaan data penelitian.

**Jangan menampilkan Fakultas.**

Validasi:
- nama wajib, huruf + spasi;
- trim/normalisasi spasi;
- NIM tepat 12 digit;
- NIM unique;
- no telepon tervalidasi;
- password confirmation;
- consent wajib.

Jika informasi Fakultas masih dibutuhkan laporan:
- gunakan nilai internal `Fakultas Ilmu Komputer`;
- jangan minta user memilih fakultas.

Jika consent penelitian dipakai:
- simpan timestamp `data_consent_at` atau field existing yang setara.

Jangan menerima role/admin flag dari request registrasi.

---

# 6. PHASE 2 — THEME SYSTEM LIGHT/DARK

Ini berlaku untuk **USER DAN ADMIN**.

Jangan membuat patch CSS acak per halaman.

Bangun satu sistem theme global.

## Requirement inti

Saat theme dipilih:

```js
document.documentElement.setAttribute('data-theme', theme);
document.documentElement.setAttribute('data-bs-theme', theme);
```

Tema:
- disimpan ke localStorage;
- diterapkan sedini mungkin;
- tidak flash ke light mode setiap navigasi/refresh.

## Light Mode

Karakter:
- putih;
- cyan;
- abu-abu muda;
- teks gelap.

## Dark Mode

Karakter:
- hitam/near-black;
- abu-abu tua;
- ungu gelap sebagai accent;
- teks terang.

## Audit visual wajib

Periksa pada user dan admin:

- body;
- navbar;
- sidebar;
- footer;
- card;
- table;
- thead/tbody;
- hover table;
- modal;
- dropdown;
- offcanvas;
- alert;
- badge;
- pagination;
- input;
- select;
- textarea;
- placeholder;
- validation state;
- button;
- outline button;
- disabled button;
- links;
- muted text;
- icons;
- borders;
- chart/container jika ada;
- Livewire answer card;
- referral page;
- admin referral page.

### Acceptance

Tidak boleh ada:
- teks gelap di background gelap;
- teks terang di background terang;
- icon menghilang;
- button outline tidak terbaca;
- modal terang yang bertabrakan dengan dark layout;
- table header yang tidak sinkron.

---

# 7. PHASE 3 — LAYOUT, NAVIGASI, DAN BRANDING

## 7.1 Sticky Footer

Perbaiki layout agar footer tetap berada di bawah viewport saat konten pendek.

Gunakan pola flex layout reusable, bukan margin manual pada halaman Riwayat.

Uji:
- Riwayat kosong;
- satu riwayat;
- banyak riwayat.

## 7.2 Mahasiswa Login Tetap Bisa ke Landing Page

Authenticated user harus dapat berpindah:

```text
Landing Page
↔ Dashboard
↔ Riwayat
↔ Artikel
↔ Skrining
```

Navbar mahasiswa minimal:

- Beranda;
- Dashboard;
- Riwayat;
- Artikel;
- Theme Toggle;
- Keluar.

Brand MyKonselor sebaiknya menuju Landing Page.

Guest tetap tidak boleh membuka route privat.

## 7.3 Browser Page Title

Gunakan mekanisme reusable.

Format wajib:

```text
MyKonselor - Nama Halaman
```

Contoh:

- MyKonselor - Beranda
- MyKonselor - Login
- MyKonselor - Registrasi
- MyKonselor - Dashboard
- MyKonselor - Skrining
- MyKonselor - Hasil Skrining
- MyKonselor - Riwayat
- MyKonselor - Artikel
- MyKonselor - Rujukan
- MyKonselor - Dashboard Admin
- MyKonselor - Pengguna
- MyKonselor - Aturan Sistem Pakar
- MyKonselor - Audit Log
- MyKonselor - Laporan
- MyKonselor - Pengajuan Rujukan

Jangan membuat variasi seperti:

```text
Dashboard - MyKonselor
```

## 7.4 Logo UNPAM + MyKonselor

Gunakan asset logo UNPAM resmi yang tersedia di project atau yang diberikan pengguna.

Brand navbar:

```text
[Logo UNPAM] MyKonselor
```

Requirement:
- proporsional;
- tidak stretch;
- responsive;
- alt text;
- terlihat di light/dark;
- konsisten guest/user/admin;
- asset lokal.

Jika logo resmi belum tersedia:
- jangan mengambil logo tidak terverifikasi secara sembarangan;
- implementasikan struktur branding dan tandai kebutuhan asset resmi.

## 7.5 Favicon

Gunakan favicon berbasis asset resmi UNPAM yang tersedia/diizinkan.

Tidak boleh memakai favicon default Laravel/Vite.

---

# 8. PHASE 4 — PROFESSIONAL UI/COPY

## 8.1 Hapus Emoji UI

Hapus penggunaan emoji sebagai icon antarmuka formal.

Contoh yang harus diganti:
- otak emoji;
- waving hand;
- chart emoji;
- book emoji;
- lock emoji;
- emoji dekorasi landing page.

Gunakan Bootstrap Icons outline/line yang konsisten.

## 8.2 Hapus AI-Looking Copy

Audit:

```text
resources/views/**/*.blade.php
```

Cari:
- em dash `—`;
- penggunaan `---` sebagai pengganti em dash user-facing;
- kalimat terlalu generik;
- campuran “kamu” dan “Anda”;
- slang;
- emoji;
- istilah “severity” untuk user;
- kata “diagnosis” pada konteks skrining.

Gunakan copy profesional, natural, ringkas.

Preferensi utama:
- gunakan “Anda” pada UI formal;
- gunakan “tingkat keparahan”, bukan `severity`;
- gunakan “hasil skrining”, bukan “diagnosis”.

Jangan mengganti simbol di source teknis jika tidak tampil di UI.

---

# 9. PHASE 5 — DASHBOARD MAHASISWA

Revisi hero:

**Halo, [Nama Mahasiswa]**

Subtext yang disarankan:

> Pantau kondisi kesehatan mental Anda melalui skrining awal yang tersedia di MyKonselor.

Gunakan Bootstrap Icon outline.

## Card Skrining

Card harus menjelaskan:
- 21 pertanyaan;
- estimasi waktu;
- fungsi skrining;
- batasan hasil.

Disclaimer wajib:

> MyKonselor merupakan alat skrining awal. Hasil yang diberikan bukan diagnosis medis dan tidak menggantikan pemeriksaan, konsultasi, atau penanganan oleh psikolog maupun tenaga kesehatan profesional.

CTA:
- `Mulai Skrining`;
- `Lanjutkan Skrining` jika assessment belum selesai.

Jangan menyembunyikan disclaimer ketika assessment sedang in-progress.

---

# 10. PHASE 6 — REFERRAL USER

Empat opsi referral existing harus tampil dengan struktur professional.

Untuk layanan yang memiliki nomor WA **resmi dan terverifikasi**:
- tampilkan tombol `Hubungi via WhatsApp`;
- gunakan `bi-whatsapp`;
- gunakan format `wa.me`;
- `target="_blank"`;
- `rel="noopener noreferrer"`.

Tidak boleh ada:

```html
href="#"
```

untuk CTA kontak.

Jika sebuah layanan hanya memiliki:
- telepon;
- website;
- hotline;

gunakan kanal resmi tersebut.

Jangan memalsukan WhatsApp hanya agar semua card punya tombol yang sama.

## Data model/config

Hindari hard-code kontak tersebar di banyak Blade.

Gunakan:
- config terpusat;
- service;
- atau master data referral di database.

---

# 11. PHASE 7 — ADMIN REFERRAL MANAGEMENT

Tambahkan menu:

**Pengajuan Rujukan**

Admin tidak boleh membuka phpMyAdmin/database untuk membaca pengajuan.

## Dashboard Admin

Tampilkan:
- jumlah pengajuan pending/menunggu;
- indikator pengajuan baru yang mudah terlihat.

## Daftar Pengajuan

Minimal:
- nama mahasiswa;
- NIM;
- waktu pengajuan;
- assessment terkait;
- ringkasan hasil terkait;
- catatan mahasiswa;
- status;
- detail.

## Status

Gunakan mapping yang jelas, misalnya:

```text
pending -> Menunggu
processing -> Diproses
completed -> Selesai
rejected -> Ditolak
```

Gunakan status yang sesuai struktur existing; jangan membuat duplikasi enum bila model sudah memiliki skema lain yang cukup.

Admin dapat:
- membuka detail;
- mengubah status;
- menyimpan perubahan.

Jika feasible:
- simpan `processed_by`;
- `processed_at`;
- audit trail status.

Mahasiswa tidak boleh mengakses UI pengelolaan referral admin.

Dark mode halaman ini wajib berfungsi penuh.

---

# 12. PHASE 8 — LOGIKA CF DAN DASS-21

Jangan refactor rumus hanya karena ingin lebih ringkas.

Pastikan regression test tetap lulus.

## CF User

```text
Tidak Pernah = 0.0
Kadang-kadang = 0.4
Sering = 0.8
Hampir Selalu = 1.0
```

## DASS score

```text
Tidak Pernah = 0
Kadang-kadang = 1
Sering = 2
Hampir Selalu = 3
```

Kedua nilai tersebut tidak boleh dianggap sama.

CF dan DASS tidak boleh dijumlahkan menjadi satu skor diagnosis.

Output:
- CF sebagai tingkat keyakinan sistem;
- DASS sebagai skor/tingkat keparahan;
- hasil selalu disebut skrining awal.

---

# 13. AUTOMATED TESTING

Jalankan test existing terlebih dahulu.

Kemudian tambah/perbaiki test minimal untuk:

```text
GuestAccessTest
AuthenticatedNavigationTest
InactiveUserLoginTest
AdminAuthorizationTest
RegistrationValidationTest
ReferralAuthorizationTest
AdminReferralManagementTest
```

Test minimal:

## Guest
- tidak dapat dashboard;
- tidak dapat assessment;
- tidak dapat history;
- tidak dapat result privat;
- tidak dapat admin.

## Mahasiswa
- login valid berhasil;
- dapat Landing Page setelah login;
- dapat Dashboard;
- admin route ditolak;
- assessment milik user lain ditolak;
- referral milik user lain ditolak.

## Admin
- login berhasil;
- admin page dapat diakses;
- referral management dapat diakses;
- update referral status persisten.

## Registration
- nama angka/simbol ditolak;
- NIM bukan 12 digit ditolak;
- NIM duplicate ditolak;
- consent wajib;
- fakultas tidak diwajibkan;
- normal registration berhasil.

## Inactive User
- password benar tetapi status nonaktif -> login gagal.

## CF/DASS
- semua unit test existing tetap lulus.

---

# 14. MANUAL / RUNTIME QA

Jika environment dapat menjalankan browser/app, lakukan test end-to-end.

## A. Mahasiswa

Login:

```text
123456789012
password
```

Uji:
1. login;
2. Dashboard;
3. Landing Page setelah login;
4. balik ke Dashboard;
5. toggle theme;
6. refresh;
7. Riwayat;
8. Artikel;
9. Skrining;
10. Hasil;
11. Referral;
12. logout.

## B. Admin

Login:

```text
000000000000
password
```

Uji:
1. Dashboard Admin;
2. dark mode;
3. User Management;
4. Rules;
5. Audit Log;
6. Artikel;
7. Laporan;
8. Pengajuan Rujukan;
9. detail referral;
10. update status;
11. refresh;
12. logout.

## C. Theme QA

Uji light/dark pada semua halaman, bukan hanya landing/dashboard.

## D. Responsive QA

Minimal:
- mobile;
- tablet;
- desktop.

Jika browser/runtime tidak tersedia:
- jangan mengklaim manual QA selesai;
- lakukan automated/static checks semaksimal mungkin;
- laporkan limitation.

---

# 15. COMMAND GATE

Sebelum menyatakan selesai, jalankan yang relevan dan aman:

```bash
composer install
npm install
npm run build
php artisan test
```

Untuk fresh database test hanya pada database disposable/testing:

```bash
php artisan migrate:fresh --seed
```

Jika project memakai command test lain, jalankan juga bila relevan.

Lakukan pencarian akhir terhadap source untuk mendeteksi:

```text
—
href="#"
tailwind
@tailwindcss
emoji user-facing
```

Jangan menghapus occurrence teknis yang legitimate secara buta. Review konteks hasil pencarian.

---

# 16. DEFINITION OF DONE

Jangan menyatakan project selesai sampai seluruh item yang dapat diuji di environment saat ini memenuhi kriteria berikut:

- Bootstrap aktif;
- Tailwind tidak menjadi dependency/UI framework;
- frontend build sukses;
- migration/seeder development sukses;
- automated tests lulus;
- akun mahasiswa dapat login;
- akun admin dapat login;
- user nonaktif tidak dapat login;
- guest tidak dapat membuka page privat;
- mahasiswa tidak dapat membuka admin;
- ownership assessment/referral aman;
- authenticated mahasiswa tetap dapat membuka Landing Page;
- navbar user memiliki Beranda;
- dark mode user konsisten;
- dark mode admin konsisten;
- footer Riwayat benar;
- dashboard tanpa emoji;
- Bootstrap Icons konsisten;
- card skrining memiliki disclaimer;
- copy user-facing profesional;
- em dash berlebihan dibersihkan;
- field Fakultas tidak tampil/tidak diwajibkan di registrasi;
- page title konsisten `MyKonselor - Nama Halaman`;
- logo UNPAM + MyKonselor konsisten;
- favicon tersedia jika asset resmi tersedia;
- admin dapat melihat pengajuan referral tanpa database manual;
- admin dapat mengubah status referral;
- tidak ada CTA `href="#"`;
- link WhatsApp hanya memakai nomor resmi/terverifikasi;
- CF/DASS tidak berubah secara tidak sah;
- hasil disebut skrining, bukan diagnosis.

Jika ada item yang tidak bisa dituntaskan karena data/asset resmi tidak tersedia, tandai sebagai **BLOCKED**, bukan **DONE**.

---

# 17. DELIVERABLE WAJIB

Di akhir pekerjaan, berikan:

## 17.1 Source yang sudah direvisi

Jangan hanya memberi snippet.

Jika bekerja pada folder/repository:
- simpan perubahan langsung di source.

Jika bekerja dari ZIP:
- kembalikan ZIP project hasil revisi.

## 17.2 `CHANGELOG_EXECUTION.md`

Berisi tabel:

```text
Area | File | Perubahan | Alasan
```

## 17.3 `TEST_REPORT.md`

Berisi:

- command yang dijalankan;
- hasil;
- jumlah test;
- failure bila ada;
- manual QA yang dilakukan;
- hal yang tidak dapat diuji;
- akun testing yang digunakan.

## 17.4 `KNOWN_ISSUES.md`

Hanya jika masih ada blocker/masalah.

Jangan menyembunyikan known issue.

## 17.5 Ringkasan akhir

Laporkan:

1. apa yang sudah selesai;
2. apa yang berubah;
3. file utama yang diubah;
4. hasil test;
5. blocker;
6. hal yang masih membutuhkan user, misalnya logo resmi atau nomor WA resmi.

---

# 18. FORMAT STATUS

Gunakan status ini secara konsisten:

```text
DONE
PARTIAL
BLOCKED
NOT TESTED
FAILED
```

Jangan gunakan kata “selesai” untuk item `PARTIAL`, `BLOCKED`, atau `NOT TESTED`.

---

# 19. LARANGAN KHUSUS

Jangan:

1. membuat project Laravel baru;
2. mengganti Bootstrap dengan Tailwind;
3. menambah migration duplikat;
4. menambah kolom yang sudah ada tanpa audit;
5. menghapus middleware/policy;
6. mengubah CF/DASS tanpa bukti bug;
7. mengarang nomor WA;
8. memakai `href="#"` untuk fitur final;
9. menggunakan emoji sebagai ikon formal;
10. menggunakan istilah diagnosis pada hasil skrining;
11. menyatakan test lulus jika tidak dijalankan;
12. menjalankan destructive migration pada database yang tidak dipastikan aman;
13. menghapus data penelitian;
14. mengubah seed data penelitian tanpa requirement;
15. berhenti setelah audit tanpa implementasi.

---

# 20. INSTRUKSI PENUTUP UNTUK AGENT

Mulai dengan membaca PRD dan source lengkap.

Lakukan pekerjaan secara bertahap tetapi **jangan meminta persetujuan untuk setiap langkah kecil**.

Gunakan judgment engineering yang aman.

Jika terdapat error:
1. cari akar masalah;
2. perbaiki source;
3. jalankan ulang test terkait;
4. lakukan regression test.

Jangan menambal error dengan workaround sementara jika akar masalah dapat diperbaiki.

Prioritaskan reusable fix pada:
- layout;
- theme;
- component;
- service;
- middleware;
- policy;
- validation;

daripada patch per halaman.

Tujuan akhirnya adalah menghasilkan **MyKonselor yang profesional, konsisten, aman, dapat dibangun ulang dari source, dan layak dipresentasikan sebagai aplikasi skripsi**.

---

# PROMPT PENDEK UNTUK MEMULAI WORK

Jika semua file sudah tersedia di Work, Anda dapat memulai dengan prompt berikut:

> Buka dan baca `PRD_Revisi_Menyeluruh_MyKonselor.md` dan `EXECUTION_PROMPT_MyKonselor_GPT_Work.md`, lalu audit source Laravel MyKonselor yang saya lampirkan. Setelah audit singkat, langsung eksekusi seluruh revisi berdasarkan urutan prioritas dalam Execution Prompt. Jangan membuat project baru, jangan menambah Tailwind, jangan mengubah logika CF/DASS tanpa bukti bug, jangan mengarang kontak rujukan, dan jangan berhenti pada rekomendasi saja. Kerjakan perubahan langsung pada source, jalankan test/build yang relevan, uji akun mahasiswa `123456789012 / password` dan admin `000000000000 / password` jika runtime tersedia, lalu hasilkan source revisi beserta `CHANGELOG_EXECUTION.md`, `TEST_REPORT.md`, dan `KNOWN_ISSUES.md` bila masih ada blocker. Jangan menyatakan DONE untuk sesuatu yang belum benar-benar diuji.
