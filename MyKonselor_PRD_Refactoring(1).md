# PRD / WEB REFACTORING PLAN — MyKonselor
## Pengembangan dan Perombakan Kerangka Web Existing

> **Dokumen ini ditujukan sebagai master prompt/PRD untuk melanjutkan project Laravel MyKonselor yang sudah ada, bukan membuat project baru dari nol.**
>
> Fokus utama: mempertahankan kerangka, database, fitur, dan kode yang masih dapat digunakan; kemudian merombak UI/UX, logika perhitungan, alur user, alur admin, dan integrasi Bootstrap agar sesuai rancangan sistem.

---

## 1. IDENTITAS PROYEK

**Nama sistem:** MyKonselor  
**Jenis sistem:** Sistem Pakar Deteksi Dini Gangguan Kesehatan Mental Mahasiswa Berbasis Web  
**Metode:** Certainty Factor (CF) + DASS-21  
**Target utama:** Mahasiswa  
**Backend:** Laravel / PHP  
**Database:** MySQL  
**Frontend wajib:** HTML + CSS + JavaScript + Bootstrap  
**Environment:** XAMPP + Composer + VS Code

**Judul skripsi:**

> Implementasi Sistem Pakar Deteksi Dini Gangguan Kesehatan Mental Mahasiswa Berbasis Web Menggunakan Metode Certainty Factor (Studi Kasus Prodi TI UNPAM)

---

# 2. TUJUAN REFACTORING

Project `mykonselor-app` yang dilampirkan sudah memiliki kerangka aplikasi Laravel dan sejumlah fitur inti. Project **tidak boleh dianggap sebagai project kosong**.

Berdasarkan struktur archive yang ada, project telah memiliki antara lain:

- Controller user
- Controller assessment
- Controller artikel
- Controller admin
- Service `CertaintyFactorService`
- Livewire `SymptomWizard`
- Model `Assessment`
- Model `AssessmentAnswer`
- Model `AssessmentResult`
- Model `Disease`
- Model `DiseaseSymptom`
- Model `Symptom`
- Model `ReferralRequest`
- Model `RuleChangeLog`
- Migration untuk disease, symptom, rule, assessment, result, article, referral
- Seeder penyakit, gejala, rule, artikel, dan admin
- View user untuk assessment, result, history, referral
- View admin untuk dashboard, report, users, rules, articles
- Middleware admin
- Export laporan assessment

Karena itu, pendekatan pengembangan yang digunakan adalah:

**AUDIT → REFACTOR → RE-DESIGN → FIX LOGIC → TEST → POLISH**

Bukan:

**DELETE PROJECT → CREATE PROJECT BARU**

---

# 3. MASALAH UTAMA YANG HARUS DISELESAIKAN

## 3.1 Frontend masih menggunakan pendekatan Tailwind

Project existing memiliki dependency Tailwind pada `node_modules`.

### Ketentuan baru

Frontend harus menggunakan **Bootstrap** sebagai framework UI utama.

Tailwind tidak boleh lagi menjadi framework styling utama.

### Target

- Bootstrap 5.x
- Bootstrap Icons
- CSS custom hanya untuk branding dan komponen khusus
- JavaScript vanilla / Bootstrap JS
- Blade sebagai template utama
- Livewire tetap boleh digunakan untuk wizard apabila memang masih diperlukan

### Jangan dilakukan

- Jangan membuat seluruh tampilan kembali menggunakan utility class Tailwind.
- Jangan mencampur Tailwind dan Bootstrap untuk komponen yang sama.
- Jangan membuat desain baru yang tidak mengikuti PRD ini.

---

# 4. PRINSIP REFACTORING

## 4.1 Pertahankan

Pertahankan apabila masih valid:

- Laravel structure
- route yang masih relevan
- model
- migration
- relationship
- controller
- service
- seeder
- authentication
- middleware
- admin module
- assessment history
- article module
- referral module
- export report

## 4.2 Boleh diubah

Boleh refactor:

- Blade
- CSS
- JavaScript
- struktur komponen UI
- Livewire UI
- controller logic
- service logic
- route organization
- database field apabila memang diperlukan
- struktur result
- admin dashboard
- navigation
- assessment flow

## 4.3 Jangan mengubah tanpa alasan

Jangan mengubah:

- data master yang sudah ditentukan dalam dokumen rancangan
- 21 gejala DASS-21
- pembagian cluster DASS-21
- bobot CF user
- rumus CF
- aturan severity
- disclaimer
- aturan hasil berat

---

# 5. ARSITEKTUR FITUR

Sistem dibagi menjadi dua area utama:

```text
MYKONSELOR
│
├── PUBLIC / USER
│   ├── Landing Page
│   ├── Tentang Sistem
│   ├── Edukasi / Artikel
│   ├── Login
│   ├── Register
│   └── Skrining
│
├── USER AREA
│   ├── Dashboard
│   ├── Skrining Baru
│   ├── Hasil Skrining
│   ├── Riwayat Skrining
│   ├── Detail Riwayat
│   ├── Artikel / Edukasi
│   ├── Profil
│   └── Referral / Bantuan
│
└── ADMIN AREA
    ├── Dashboard
    ├── Data User
    ├── Data Gangguan
    ├── Data Gejala
    ├── Rule / CF
    ├── Riwayat / Laporan
    ├── Artikel
    ├── Referral
    └── Audit Log
```

---

# 6. ROLE USER

## 6.1 Guest

Guest dapat:

- melihat landing page
- membaca informasi sistem
- membaca artikel yang bersifat publik
- login
- register

Guest tidak dapat:

- menyimpan hasil skrining
- melihat history
- mengakses dashboard user
- mengakses admin

---

# 7. ROLE MAHASISWA / USER

User yang sudah login dapat:

1. Melihat dashboard.
2. Memulai skrining baru.
3. Menjawab 21 pertanyaan.
4. Melihat hasil CF.
5. Melihat hasil DASS-21.
6. Melihat tingkat keparahan.
7. Melihat rekomendasi sesuai kategori hasil.
8. Melihat riwayat.
9. Membuka detail hasil sebelumnya.
10. Membaca artikel edukasi.
11. Mengelola profil.
12. Mengakses referral jika diperlukan.

---

# 8. ROLE ADMIN

Admin memiliki akses ke:

## Dashboard

Tampilkan minimal:

- total user
- total skrining
- skrining hari ini
- distribusi hasil
- distribusi Stres
- distribusi Kecemasan
- distribusi Depresi
- aktivitas terbaru

## User Management

Admin dapat:

- melihat user
- mencari user
- filter
- melihat detail
- mengaktifkan/nonaktifkan user jika fitur tersedia
- melihat jumlah skrining user

## Master Gejala

CRUD:

- kode gejala
- nama gejala
- kategori
- MB
- MD
- status aktif

## Master Gangguan

CRUD:

- kode
- nama gangguan
- deskripsi
- status

Minimal:

- Stres
- Kecemasan
- Depresi

## Rule Management

Admin dapat:

- melihat relasi gejala → gangguan
- melihat MB/MD
- mengubah rule jika mempunyai kewenangan
- melihat perubahan rule

Setiap perubahan rule harus dicatat pada audit log.

## Artikel

CRUD:

- judul
- slug
- thumbnail
- isi
- status publish
- tanggal publish

## Report

Admin dapat:

- melihat daftar assessment
- filter tanggal
- filter severity
- filter kategori
- melihat detail
- export report

---

# 9. UI/UX UTAMA

## 9.1 Karakter visual

Desain harus:

- modern
- profesional
- bersih
- ringan
- tidak terlihat seperti dashboard template generik
- cocok untuk mahasiswa
- mobile responsive
- accessible
- tidak menggunakan terlalu banyak warna

---

# 10. LIGHT MODE

Light Mode adalah default.

### Palet

```text
Background utama : White
Primary           : Cyan
Secondary         : Light Gray
Text              : Dark Gray
Border            : Light Gray
Card              : White
```

Gunakan Bootstrap variables/custom variables agar tema mudah dikontrol.

Contoh konsep:

```css
:root {
    --mk-primary: #06b6d4;
    --mk-bg: #ffffff;
    --mk-surface: #f8fafc;
    --mk-border: #e5e7eb;
    --mk-text: #1f2937;
}
```

Warna final boleh disesuaikan sedikit selama tetap berada dalam karakter putih + cyan + abu-abu.

---

# 11. DARK MODE

Dark Mode harus benar-benar mengubah tampilan, bukan sekadar membuat background menjadi hitam.

### Palet

```text
Background : Black / Near Black
Surface    : Dark Gray
Primary    : Dark Purple
Text       : White / Light Gray
Border     : Dark Gray
```

Contoh:

```css
[data-theme="dark"] {
    --mk-bg: #0b0b0f;
    --mk-surface: #18181b;
    --mk-border: #27272a;
    --mk-text: #f4f4f5;
    --mk-primary: #6d28d9;
}
```

---

# 12. THEME SWITCHER

Sediakan tombol:

```text
☀ Light
🌙 Dark
```

Theme harus:

- tersimpan di localStorage
- tidak kembali ke Light setiap reload
- bekerja pada seluruh halaman
- bekerja pada navbar
- bekerja pada card
- bekerja pada modal
- bekerja pada form
- bekerja pada table
- bekerja pada alert
- bekerja pada assessment wizard

---

# 13. LANDING PAGE

Landing page harus memiliki struktur:

1. Navbar
2. Hero section
3. Penjelasan singkat MyKonselor
4. Cara kerja sistem
5. Penjelasan skrining
6. Keunggulan
7. Edukasi / artikel terbaru
8. Disclaimer
9. CTA skrining
10. Footer

CTA utama:

> Mulai Skrining

CTA sekunder:

> Pelajari Sistem

Jangan membuat klaim bahwa sistem memberikan diagnosis medis.

---

# 14. DASHBOARD USER

Dashboard setelah login:

```text
Selamat datang, [Nama]

[ Mulai Skrining ]

Ringkasan
┌──────────────┐
│ Skrining     │
│ 5            │
└──────────────┘

┌──────────────┐
│ Terakhir     │
│ 14 Aug 2026  │
└──────────────┘

Riwayat Terakhir
---------------------------------
Tanggal | Hasil | Severity | Aksi
---------------------------------
```

Tambahkan CTA edukasi jika sesuai.

---

# 15. ALUR SKRINING

Skrining harus dibuat sebagai wizard.

## Step 1 — Informasi

Tampilkan:

- tujuan skrining
- estimasi waktu
- disclaimer
- tombol mulai

## Step 2 — Pertanyaan

21 pertanyaan.

Jawaban:

```text
Tidak Pernah
Kadang-kadang
Sering
Hampir Selalu
```

Untuk UI Bootstrap gunakan:

- `.form-check`
- radio card
- progress bar
- step indicator

Jangan gunakan input yang terlalu kecil.

---

# 16. PROGRESS ASSESSMENT

Tampilkan:

```text
Pertanyaan 8 dari 21

████████░░░░░░░░░
38%
```

Progress dihitung menggunakan JavaScript/Livewire.

User tidak boleh dapat submit jika ada pertanyaan yang belum dijawab.

---

# 17. DATA INPUT

Jawaban DASS:

```text
Tidak Pernah     = 0
Kadang-kadang    = 1
Sering           = 2
Hampir Selalu    = 3
```

Untuk CF User:

```text
Tidak Pernah     = 0.0
Kadang-kadang    = 0.4
Sering           = 0.8
Hampir Selalu    = 1.0
```

Satu pilihan user menghasilkan dua representasi:

```text
DASS score
+
CF user weight
```

Jangan menganggap kedua angka tersebut sama.

---

# 18. LOGIKA CERTAINTY FACTOR

## CF Pakar

```text
CF_pakar = MB - MD
```

Data pakar menggunakan:

```text
MB = 1 atau 0
MD = 1 atau 0
```

## CF Gejala

```text
CF_akhir = CF_pakar × CF_user
```

## Combine positif

```text
CF_combine =
CF_lama + CF_baru × (1 - CF_lama)
```

## Combine negatif

```text
CF_combine =
CF_lama + CF_baru × (1 + CF_lama)
```

## Combine campuran

```text
CF_combine =
(CF_lama + CF_baru)
/
(1 - min(|CF_lama|, |CF_baru|))
```

Implementasi rumus harus berada pada service/domain logic, bukan disebar ke Blade.

---

# 19. REFACTOR CERTAINTY FACTOR SERVICE

Project existing sudah memiliki:

```text
app/Services/CertaintyFactorService.php
```

Service ini harus menjadi pusat perhitungan CF.

Target:

```php
CertaintyFactorService
```

minimal memiliki tanggung jawab:

```text
calculateExpertCf()
calculateSymptomCf()
combinePositive()
combineNegative()
combineMixed()
calculateDiseaseCf()
calculateAllDiseaseCf()
```

Controller hanya:

```text
validate input
↓
ambil data
↓
panggil service
↓
simpan result
↓
redirect/view
```

Jangan menaruh rumus panjang langsung di controller.

---

# 20. LOGIKA DASS-21

Cluster wajib:

## Stres

```text
G01
G06
G08
G11
G12
G14
G18
```

## Kecemasan

```text
G02
G04
G07
G09
G15
G19
G20
```

## Depresi

```text
G03
G05
G10
G13
G16
G17
G21
```

Total setiap cluster:

```text
sum jawaban × 2
```

---

# 21. CUT-OFF DASS-21

## Stres

```text
0–14   Normal
15–18  Ringan
19–25  Sedang
26–33  Berat
```

## Kecemasan

```text
0–7    Normal
8–9    Ringan
10–14  Sedang
15–19  Berat
```

## Depresi

```text
0–9    Normal
10–13  Ringan
14–20  Sedang
21–27  Berat
```

Gunakan data ini persis dari rancangan. Jangan mengganti cutoff dengan tabel lain tanpa instruksi baru.

---

# 22. PEMISAHAN CF DAN DASS

Penting:

**CF dan DASS bukan dua metode yang dijumlahkan menjadi satu angka.**

Output harus dipisahkan:

```text
CF
├── Stres      : xx%
├── Kecemasan  : xx%
└── Depresi    : xx%

DASS-21
├── Stres      : xx / level
├── Kecemasan  : xx / level
└── Depresi    : xx / level
```

CF digunakan untuk keyakinan sistem terhadap gangguan.

DASS-21 digunakan untuk tingkat keparahan.

Jangan:

```text
CF + DASS = diagnosis
```

---

# 23. PENENTUAN HASIL

Halaman result harus menampilkan kedua perspektif tersebut.

Contoh:

```text
HASIL SKRINING

Disclaimer:
Hasil ini merupakan skrining awal dan bukan diagnosis mutlak.

----------------------------------

TINGKAT HASIL DASS-21

Stres
Score: 18
Kategori: Ringan

Kecemasan
Score: 6
Kategori: Normal

Depresi
Score: 8
Kategori: Normal

----------------------------------

HASIL CERTAINTY FACTOR

Stres      72%
Kecemasan  18%
Depresi    10%
```

---

# 24. ATURAN OUTPUT BERDASARKAN SEVERITY

## Normal / Ringan

Tampilkan:

- hasil
- penjelasan singkat
- psychoeducation
- tips relaksasi
- manajemen waktu
- sleep hygiene
- artikel terkait

## Sedang

Jangan tampilkan solusi mandiri sebagai solusi utama.

Tampilkan:

- hasil
- penjelasan
- anjuran menjadwalkan konseling
- CTA referral/konsultasi

## Berat

Jangan tampilkan:

- "cukup istirahat"
- "coba berpikir positif"
- tips mandiri sebagai penanganan utama

Tampilkan:

- pesan empati
- anjuran mendapatkan bantuan profesional
- tombol referral
- informasi jalur bantuan

---

# 25. DISCLAIMER

Disclaimer wajib muncul pada hasil.

Minimal:

> Hasil ini merupakan skrining awal dan bukan diagnosis mutlak. Sistem tidak menggantikan pemeriksaan atau diagnosis dari tenaga profesional.

Disclaimer harus terlihat jelas tetapi tidak menakutkan.

---

# 26. REFERRAL PAGE

Halaman referral wajib tersedia.

Isi minimal:

1. BPJS Kesehatan — Puskesmas / RSUD Tangerang Selatan
2. Hotline Kemenkes RI / layanan yang ditentukan dalam rancangan
3. Yayasan Pulih
4. Tombol WhatsApp Pusat Layanan Psikologi UIN

**Catatan implementasi:**

Nomor telepon, URL, WhatsApp URL, alamat, dan status layanan harus disimpan sebagai konfigurasi/data yang dapat diubah admin, bukan hard-code di banyak Blade.

Jangan membuat nomor/URL baru berdasarkan asumsi.

---

# 27. RIWAYAT USER

History menampilkan:

```text
Tanggal
Stres
Kecemasan
Depresi
Severity tertinggi
Status
Aksi
```

Aksi:

```text
Lihat Detail
```

Jika diperlukan:

```text
Download / Print
```

User hanya boleh melihat data miliknya sendiri.

---

# 28. DATABASE

Project existing sudah memiliki tabel:

```text
users
diseases
symptoms
disease_symptom
rule_change_logs
assessments
assessment_answers
assessment_results
articles
referral_requests
```

Pertahankan struktur tersebut apabila sudah memenuhi kebutuhan.

Relasi target:

```text
User
 │
 └── hasMany Assessments
             │
             ├── hasMany AssessmentAnswers
             │
             └── hasOne / hasMany AssessmentResults
```

```text
Disease
 │
 └── belongsToMany Symptoms
```

```text
Symptom
 │
 └── belongsToMany Diseases
```

---

# 29. DATA RESULT

Result idealnya menyimpan snapshot perhitungan agar hasil lama tidak berubah ketika rule admin berubah.

Minimal pertimbangkan:

```text
assessment_id
stress_cf
anxiety_cf
depression_cf
stress_score
anxiety_score
depression_score
stress_severity
anxiety_severity
depression_severity
highest_severity
calculation_version
```

Jika project existing sudah memiliki field serupa, gunakan field existing dan lakukan migration hanya jika memang diperlukan.

---

# 30. SNAPSHOT JAWABAN

Assessment answer harus menyimpan minimal:

```text
assessment_id
symptom_id
answer_value
cf_user
dass_score
```

Tujuannya agar hasil historis tetap dapat diaudit.

---

# 31. ADMIN RULE AUDIT

Setiap perubahan:

```text
MB
MD
kategori
relasi gejala-gangguan
status
```

harus dicatat.

Minimal:

```text
admin_id
rule_id / disease_symptom_id
old_value
new_value
action
created_at
```

---

# 32. SEED DATA WAJIB

21 gejala berikut wajib dipertahankan:

```text
G01 Stres
G02 Kecemasan
G03 Depresi
G04 Kecemasan
G05 Depresi
G06 Stres
G07 Kecemasan
G08 Stres
G09 Kecemasan
G10 Depresi
G11 Stres
G12 Stres
G13 Depresi
G14 Stres
G15 Kecemasan
G16 Depresi
G17 Depresi
G18 Stres
G19 Kecemasan
G20 Kecemasan
G21 Depresi
```

MB/MD juga wajib mempertahankan data rancangan:

```text
G02 = MB 0 / MD 1
G05 = MB 0 / MD 1

Gejala lainnya:
MB 1 / MD 0
```

Teks lengkap 21 gejala harus mengikuti file rancangan `rancangan-web-MyKonselor.md`.

---

# 33. BOOTSTRAP MIGRATION

## Tujuan

Migrasi dari Tailwind ke Bootstrap tanpa merusak Laravel.

### Langkah

1. Identifikasi import Tailwind.
2. Identifikasi class Tailwind pada Blade.
3. Identifikasi CSS custom yang bergantung pada Tailwind.
4. Identifikasi komponen UI yang dibuat menggunakan utility Tailwind.
5. Install/load Bootstrap.
6. Buat theme variables MyKonselor.
7. Refactor layout.
8. Refactor halaman user.
9. Refactor halaman admin.
10. Hapus ketergantungan Tailwind setelah seluruh view aman.

---

# 34. STRUKTUR FRONTEND YANG DISARANKAN

```text
resources/
├── css/
│   ├── app.css
│   ├── theme.css
│   ├── components.css
│   ├── user.css
│   └── admin.css
│
└── js/
    ├── app.js
    ├── theme.js
    ├── assessment.js
    └── admin.js
```

Tidak wajib persis seperti ini jika struktur existing sudah lebih baik.

---

# 35. LAYOUT BLADE

Gunakan layout reusable:

```text
components/layouts/app.blade.php
```

Pisahkan:

```text
Public Layout
User Layout
Admin Layout
Auth Layout
```

Jika project existing menggunakan satu layout, boleh direfactor menjadi beberapa layout.

---

# 36. KOMPONEN UI YANG HARUS REUSABLE

Buat komponen untuk:

- navbar
- sidebar
- theme toggle
- button
- alert
- card
- stat card
- badge severity
- modal
- pagination
- empty state
- loading state
- confirmation dialog
- progress assessment

Tujuan:

Jangan copy-paste HTML yang sama di banyak halaman.

---

# 37. SEVERITY BADGE

Gunakan warna Bootstrap yang mudah dibaca.

Contoh:

```text
Normal  → success
Ringan  → info
Sedang  → warning
Berat   → danger
```

Untuk Dark Mode, pastikan contrast tetap aman.

---

# 38. ADMIN UI

Admin harus terasa berbeda dari user tetapi tetap satu brand.

Layout:

```text
Sidebar
│
├── Dashboard
├── User
├── Gejala
├── Gangguan
├── Rule
├── Assessment
├── Artikel
├── Referral
└── Audit Log

Topbar
├── Search
├── Theme
└── Profile
```

Sidebar harus responsive.

Pada mobile:

```text
Sidebar → offcanvas
```

Gunakan Bootstrap Offcanvas.

---

# 39. ADMIN DASHBOARD

Gunakan:

- Bootstrap Cards
- responsive tables
- chart library hanya jika benar-benar dibutuhkan
- filter
- pagination

Jangan membuat dashboard terlalu padat.

Prioritas:

```text
KPI → Chart → Activity → Table
```

---

# 40. ARTICLE / EDUKASI

User dapat:

- melihat artikel
- mencari
- membuka detail

Admin dapat:

- create
- edit
- delete
- publish/unpublish

Artikel tidak boleh digunakan sebagai pengganti diagnosis atau treatment profesional.

---

# 41. VALIDASI

Assessment:

```text
21 pertanyaan wajib dijawab.
```

Server-side validation wajib dilakukan meskipun frontend sudah melakukan validation.

Jangan mempercayai nilai:

```text
CF
score
severity
```

yang dikirim client.

Server harus menghitung ulang semuanya.

---

# 42. SECURITY

Wajib:

- CSRF
- authentication
- authorization
- admin middleware
- validation
- mass assignment protection
- ownership check
- sanitasi artikel
- rate limiting untuk endpoint sensitif bila diperlukan

User tidak boleh dapat:

```text
GET /assessment/{id}
```

milik user lain.

Admin tidak boleh dapat mengubah rule tanpa audit log.

---

# 43. ROUTING

Pisahkan route:

```text
Public
Auth
User
Admin
```

Contoh konsep:

```php
Route::middleware('auth')->group(function () {
    // user routes
});

Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        // admin routes
    });
```

Gunakan route name yang konsisten.

---

# 44. CONTROLLER

Target controller:

```text
DashboardController
AssessmentController
ArticleController
ProfileController

Admin/
├── DashboardController
├── UserManagementController
├── RuleManagementController
├── ReportController
└── ArticleController
```

Jika controller existing sudah sesuai, refactor seperlunya.

---

# 45. DIAGNOSIS CONTROLLER

Controller assessment/diagnosis harus:

```text
1. Validate request
2. Load 21 symptoms
3. Validate all question codes
4. Convert answer → CF User
5. Convert answer → DASS score
6. Calculate CF per symptom
7. Group CF by disease
8. Combine CF
9. Group DASS by cluster
10. Multiply DASS × 2
11. Determine severity
12. Store assessment
13. Store answers
14. Store result snapshot
15. Redirect to result
```

---

# 46. SERVICE ARCHITECTURE

Disarankan:

```text
app/Services/
├── CertaintyFactorService.php
├── Dass21Service.php
└── AssessmentService.php
```

### CertaintyFactorService

Mengurus CF.

### Dass21Service

Mengurus:

- mapping question
- score
- cluster
- multiplier
- severity

### AssessmentService

Mengorkestrasi seluruh proses.

Controller menjadi tipis.

---

# 47. DASS21 SERVICE

Contoh struktur:

```php
class Dass21Service
{
    protected array $clusters = [
        'stress' => [
            'G01', 'G06', 'G08',
            'G11', 'G12', 'G14', 'G18'
        ],
        'anxiety' => [
            'G02', 'G04', 'G07',
            'G09', 'G15', 'G19', 'G20'
        ],
        'depression' => [
            'G03', 'G05', 'G10',
            'G13', 'G16', 'G17', 'G21'
        ],
    ];
}
```

Mapping severity jangan ditaruh di Blade.

---

# 48. TESTING WAJIB

## Unit Test

Test:

- CF expert
- CF user
- CF single
- CF combine positive
- CF combine negative
- CF combine mixed
- DASS cluster
- DASS multiplier
- severity cutoff

## Feature Test

Test:

- login
- register
- assessment
- result
- history
- referral
- admin
- rule update
- article CRUD

---

# 49. TEST CASE CF

Minimal:

```text
MB = 1
MD = 0
CF expert = 1
CF user = 0.8
CF final = 0.8
```

Case negatif:

```text
MB = 0
MD = 1
CF expert = -1
CF user = 0.8
CF final = -0.8
```

---

# 50. TEST CASE DASS

Contoh:

Jika 7 pertanyaan Stres memiliki nilai:

```text
1, 1, 1, 1, 1, 1, 1
```

Maka:

```text
raw = 7
final = 7 × 2
      = 14
severity = Normal
```

---

# 51. RESULT PAGE RESPONSIVE

Desktop:

```text
┌─────────────────────────────────────────┐
│ Disclaimer                              │
├───────────────────┬─────────────────────┤
│ DASS Result       │ CF Result           │
├───────────────────┼─────────────────────┤
│ Stres             │ Stres xx%           │
│ Kecemasan         │ Kecemasan xx%       │
│ Depresi           │ Depresi xx%         │
└───────────────────┴─────────────────────┘
```

Mobile:

Semua card menjadi stacked.

---

# 52. EMPTY STATE

History kosong:

> Belum ada riwayat skrining.

CTA:

> Mulai Skrining Pertama

Artikel kosong:

> Belum ada artikel tersedia.

Admin table kosong:

> Data belum tersedia.

---

# 53. LOADING STATE

Saat assessment diproses:

```text
Menghitung hasil skrining...
```

Gunakan Bootstrap spinner.

Jangan menampilkan hasil parsial sebelum server selesai menghitung.

---

# 54. ERROR HANDLING

Jika assessment gagal:

```text
Maaf, hasil skrining belum dapat diproses.
Silakan coba kembali.
```

Jangan tampilkan stack trace kepada user.

Log error ke Laravel log.

---

# 55. DATA INTEGRITY

Pastikan:

```text
assessment_answers.assessment_id
assessment_answers.symptom_id
```

menggunakan foreign key.

Gunakan transaction:

```text
DB::transaction()
```

untuk proses:

```text
assessment
+
answers
+
result
```

Jika salah satu gagal, seluruh proses rollback.

---

# 56. REFACTOR PLAN PER TAHAP

## PHASE 1 — Audit

- [ ] Audit route
- [ ] Audit controller
- [ ] Audit model
- [ ] Audit migration
- [ ] Audit seeder
- [ ] Audit Blade
- [ ] Audit Livewire
- [ ] Audit CSS
- [ ] Audit JavaScript
- [ ] Audit Tailwind dependency
- [ ] Audit Bootstrap availability

## PHASE 2 — Logic

- [ ] Refactor CF service
- [ ] Buat/refactor DASS21 service
- [ ] Buat/refactor Assessment service
- [ ] Validasi 21 jawaban
- [ ] Pisahkan CF dan DASS
- [ ] Simpan snapshot result
- [ ] Tambahkan calculation version

## PHASE 3 — Bootstrap Migration

- [ ] Setup Bootstrap
- [ ] Theme variables
- [ ] Base layout
- [ ] Navbar
- [ ] Sidebar
- [ ] Card
- [ ] Form
- [ ] Table
- [ ] Modal
- [ ] Alert
- [ ] Badge
- [ ] Progress
- [ ] Offcanvas

## PHASE 4 — User UI

- [ ] Landing
- [ ] Auth
- [ ] Dashboard
- [ ] Assessment
- [ ] Result
- [ ] History
- [ ] Article
- [ ] Profile
- [ ] Referral

## PHASE 5 — Admin UI

- [ ] Dashboard
- [ ] User
- [ ] Gejala
- [ ] Gangguan
- [ ] Rule
- [ ] Assessment
- [ ] Article
- [ ] Report
- [ ] Audit Log

## PHASE 6 — Dark Mode

- [ ] Theme switcher
- [ ] localStorage
- [ ] user pages
- [ ] admin pages
- [ ] form
- [ ] table
- [ ] modal
- [ ] chart

## PHASE 7 — Testing

- [ ] Unit test CF
- [ ] Unit test DASS
- [ ] Feature test assessment
- [ ] Authorization test
- [ ] History ownership test
- [ ] Admin test
- [ ] Responsive test
- [ ] Dark mode test

---

# 57. PRIORITAS PENGERJAAN

Urutan pengerjaan yang disarankan:

```text
P0 — Logic correctness
P0 — Database integrity
P0 — Assessment flow
P0 — Result correctness

P1 — Bootstrap migration
P1 — User UI
P1 — Admin UI
P1 — Dark mode

P2 — Article
P2 — Reporting
P2 — Referral management
P2 — UX polish

P3 — Additional enhancement
```

**Jangan menghabiskan waktu mempercantik UI sebelum logika CF + DASS benar.**

---

# 58. DEFINITION OF DONE

Project dianggap selesai apabila:

### Technology

- [ ] Laravel berjalan
- [ ] MySQL berjalan
- [ ] Bootstrap digunakan sebagai framework frontend
- [ ] Tailwind tidak lagi menjadi framework UI utama

### User

- [ ] Register
- [ ] Login
- [ ] Dashboard
- [ ] Skrining
- [ ] Result
- [ ] History
- [ ] Article
- [ ] Profile
- [ ] Referral

### Admin

- [ ] Dashboard
- [ ] User management
- [ ] Gejala
- [ ] Gangguan
- [ ] Rule
- [ ] Assessment/report
- [ ] Article
- [ ] Audit log

### Calculation

- [ ] CF expert benar
- [ ] CF user benar
- [ ] CF symptom benar
- [ ] CF combine benar
- [ ] DASS cluster benar
- [ ] DASS × 2 benar
- [ ] Cutoff benar
- [ ] CF dan DASS tidak tercampur
- [ ] Result disimpan sebagai snapshot

### UX

- [ ] Responsive
- [ ] Light mode
- [ ] Dark mode
- [ ] Theme tersimpan
- [ ] Form mudah digunakan
- [ ] Assessment progress jelas
- [ ] Error handling jelas

### Safety

- [ ] Disclaimer
- [ ] Tidak mengklaim diagnosis
- [ ] Tidak memberikan self-help sebagai solusi utama pada kondisi sedang/berat
- [ ] Referral page
- [ ] Ownership history aman

---

# 59. ATURAN PENTING UNTUK AI / DEVELOPER

Saat melanjutkan project ini:

1. **Jangan membuat Laravel project baru.**
2. **Gunakan source code existing sebagai baseline.**
3. Sebelum menghapus file, periksa apakah file digunakan route/controller/view lain.
4. Jangan menghapus fitur existing hanya karena UI-nya jelek.
5. Jangan mengubah rumus CF tanpa alasan dan instruksi.
6. Jangan mengganti cutoff DASS-21 yang sudah ditentukan.
7. Jangan menghitung CF di JavaScript sebagai sumber kebenaran.
8. Server harus selalu menghitung ulang hasil.
9. Jangan menggabungkan CF dan DASS menjadi satu skor.
10. Jangan menampilkan diagnosis medis.
11. Jangan menggunakan Tailwind sebagai framework UI.
12. Bootstrap menjadi standar seluruh UI.
13. Gunakan reusable Blade component.
14. Gunakan service untuk logic perhitungan.
15. Gunakan transaction untuk penyimpanan assessment.
16. Pastikan user hanya dapat mengakses assessment miliknya.
17. Semua perubahan rule admin harus diaudit.
18. Jangan hard-code informasi referral yang kemungkinan berubah di banyak file.
19. Jika ada konflik antara kode existing dan dokumen PRD, prioritaskan **data/rancangan skripsi yang diberikan**, tetapi tandai konflik tersebut sebelum mengubahnya.
20. Jika requirement belum dijelaskan, jangan mengarang data ilmiah baru.

---

# 60. OUTPUT YANG DIHARAPKAN DARI DEVELOPER

Pengembangan harus menghasilkan:

```text
Existing Laravel App
        │
        ├── Bootstrap UI
        ├── Light/Dark Theme
        ├── Correct CF Engine
        ├── Correct DASS-21 Engine
        ├── User Dashboard
        ├── Assessment Wizard
        ├── Result & History
        ├── Referral
        ├── Admin Dashboard
        ├── Rule Management
        ├── Article Management
        ├── Reporting
        └── Audit Log
```

Tujuan akhirnya bukan sekadar:

> "Web bisa dibuka."

Tetapi:

> **Web existing MyKonselor berhasil direfactor menjadi sistem skrining yang konsisten dengan rancangan skripsi, menggunakan Bootstrap, memiliki UI/UX profesional, logika CF + DASS-21 yang benar, fitur user dan admin yang lengkap, serta aman digunakan sebagai media skrining awal.**


---

# 61. SECURITY & AUTHORIZATION HARDENING

Bagian ini adalah requirement **WAJIB**, bukan fitur tambahan.

Tujuan utama:

> User hanya boleh mengakses resource sesuai status login dan role-nya. Validasi tidak boleh hanya dilakukan di frontend atau hanya dengan menyembunyikan link.

Security harus diterapkan pada:

```text
Route
↓
Middleware
↓
Authorization / Policy
↓
Controller
↓
Validation
↓
Database
```

Frontend hanya berfungsi sebagai UX. **Server adalah sumber kebenaran.**

---

# 62. REGISTRATION FLOW

Pada halaman Login harus tersedia tombol:

```text
Belum punya akun? Daftar
```

Tombol mengarah ke:

```text
/register
```

Form registration minimal memiliki:

```text
Nama Lengkap *
NIM *
No. Telepon *
Akun *
Password *
Konfirmasi Password *
```

Jika project existing sudah mempunyai definisi field `akun`, gunakan struktur existing tersebut. Jangan membuat dua sistem identitas login yang berbeda.

---

# 63. CATATAN PADA FORM REGISTRASI

Form registration wajib menampilkan catatan/persetujuan yang jelas, misalnya:

> **Catatan:** Mohon mengisi data dengan benar dan sesuai keadaan sebenarnya. Data yang diberikan akan digunakan untuk keperluan penelitian dan pengelolaan layanan MyKonselor sesuai kebutuhan sistem. Data tidak boleh digunakan di luar tujuan yang telah ditentukan dan harus dikelola secara bertanggung jawab.

Jika diperlukan, tambahkan checkbox:

```text
[ ] Saya menyatakan bahwa data yang saya masukkan benar
    dan menyetujui penggunaannya untuk keperluan penelitian
    sesuai informasi yang diberikan.
```

Checkbox wajib dicentang sebelum registration diproses.

**Catatan penting:** teks final persetujuan penelitian harus disesuaikan dengan dokumen/ketentuan penelitian yang sebenarnya. Jangan membuat klaim legal atau kebijakan privasi yang belum ditetapkan.

---

# 64. VALIDASI NAMA LENGKAP

Field:

```text
nama
```

Ketentuan:

- wajib diisi
- minimal 2 karakter
- tidak boleh angka
- tidak boleh simbol
- tidak boleh emoji
- boleh menggunakan spasi antar kata
- boleh menggunakan huruf alfabet
- validasi dilakukan server-side

Contoh valid:

```text
Abdullah Hafizh Sahal
Budi Santoso
Siti Nurhaliza
```

Contoh invalid:

```text
Abdullah123
Budi_ Santoso
Budi@Santoso
123456
```

Gunakan validasi Laravel yang sesuai, misalnya konsep:

```php
'regex:/^[\pL\s]+$/u'
```

Jangan hanya menggunakan JavaScript.

---

# 65. VALIDASI NIM

NIM wajib:

```text
12 digit angka
```

Ketentuan:

- wajib diisi
- tepat 12 karakter
- hanya angka
- tidak boleh kurang dari 12
- tidak boleh lebih dari 12
- tidak boleh mengandung spasi
- tidak boleh mengandung huruf
- harus unique di database

Konsep validation:

```php
'required',
'string',
'size:12',
'regex:/^[0-9]{12}$/',
'unique:users,nim'
```

NIM harus memiliki unique index pada database.

Jangan hanya mengandalkan validation Laravel karena race condition masih memungkinkan. Database juga harus memiliki:

```text
UNIQUE(nim)
```

---

# 66. VALIDASI NOMOR TELEPON

No. telepon:

- wajib
- hanya angka dengan format yang diperbolehkan
- panjang harus dibatasi
- normalisasi format dilakukan sebelum disimpan
- tidak boleh menyimpan karakter sembarangan
- unique jika requirement penelitian mengharuskannya

Contoh format yang dapat diterima:

```text
081234567890
6281234567890
```

Sistem harus menentukan **satu format canonical** untuk penyimpanan.

Jangan menyimpan:

```text
08xx-xxxx-xxxx
+62 (812) xxxx
```

dengan format berbeda-beda apabila nomor akan digunakan untuk pencocokan data.

Jika nomor telepon tidak harus unique, jangan membuat unique constraint hanya berdasarkan asumsi.

---

# 67. FIELD AKUN

Field `akun` harus memiliki aturan yang jelas dan konsisten dengan sistem login existing.

Jika `akun` berfungsi sebagai username:

```text
- wajib
- unique
- tidak boleh mengandung spasi
- hanya karakter yang diizinkan
- case handling harus konsisten
```

Jika `akun` sebenarnya adalah email:

```text
- wajib
- format email valid
- unique
- normalized lowercase
```

**Jangan membuat dua mekanisme login yang berbeda.**

Saat refactoring, audit terlebih dahulu bagaimana project existing menggunakan field `akun`/`email`, lalu pertahankan mekanisme yang paling konsisten.

---

# 68. PASSWORD POLICY

Password registration wajib:

- required
- confirmation wajib cocok
- minimal panjang yang layak
- disimpan menggunakan hashing Laravel
- tidak pernah disimpan sebagai plaintext
- tidak pernah ditampilkan kembali
- tidak pernah dimasukkan ke log

Gunakan mekanisme hashing Laravel:

```php
Hash::make($password)
```

Jangan:

```php
md5()
sha1()
base64_encode()
```

untuk menyimpan password.

---

# 69. MASS ASSIGNMENT PROTECTION

Model User wajib menggunakan:

```text
$fillable
```

atau:

```text
$guarded
```

secara benar.

Jangan menerima seluruh request:

```php
User::create($request->all());
```

Gunakan hanya field yang telah divalidasi:

```php
$validated = $request->validated();
```

Kemudian mapping field secara eksplisit.

Tujuannya mencegah user mengirim field tersembunyi seperti:

```text
role=admin
is_admin=1
email_verified_at=...
```

melalui request manual.

---

# 70. ROLE AUTHORIZATION

Role user minimal:

```text
user
admin
```

Jangan menentukan role berdasarkan:

```text
URL
hidden input
query parameter
session dari client
```

Role harus berasal dari database/session server yang terpercaya.

Contoh:

```text
/users        → user
/admin/*      → admin
```

Tetapi URL prefix saja **bukan security**.

Security harus berasal dari middleware authorization.

---

# 71. ADMIN ROUTE PROTECTION

Semua route admin harus menggunakan:

```text
auth
+
admin authorization
```

Contoh konsep:

```php
Route::prefix('admin')
    ->middleware(['auth', 'admin'])
    ->group(function () {
        // admin routes
    });
```

Jika user biasa mencoba:

```text
/admin
/admin/users
/admin/rules
/admin/articles
```

maka request harus ditolak.

Jangan hanya menyembunyikan menu admin dari navbar.

---

# 72. USER ROUTE PROTECTION

Semua halaman privat user harus dilindungi:

```text
auth middleware
```

Contoh:

```text
/dashboard
/assessment
/assessment/history
/assessment/result/*
/profile
/referral
```

Guest yang mengakses URL tersebut harus diarahkan ke login atau menerima response unauthorized sesuai desain aplikasi.

---

# 73. IDOR / OWNERSHIP PROTECTION

Ini **WAJIB**.

Jangan hanya melakukan:

```php
Assessment::findOrFail($id);
```

karena user dapat mencoba ID milik user lain.

Gunakan ownership check.

Konsep:

```php
$assessment = auth()->user()
    ->assessments()
    ->findOrFail($id);
```

atau gunakan Policy:

```text
AssessmentPolicy
```

Dengan demikian:

```text
User A
GET /assessment/15
```

tidak boleh melihat assessment User B.

Hal yang sama berlaku untuk:

- result
- history
- referral request
- profile
- export
- file
- data penelitian

---

# 74. POLICY & GATE

Gunakan Laravel Policy untuk resource yang memiliki ownership atau role.

Minimal pertimbangkan:

```text
AssessmentPolicy
AssessmentResultPolicy
ReferralRequestPolicy
ArticlePolicy
RulePolicy
UserPolicy
```

Contoh konsep:

```text
Assessment:
- view
- create
- delete jika memang diperbolehkan

Rule:
- view admin
- update admin

User:
- view sendiri
- update sendiri
```

Admin tidak otomatis boleh melakukan semua hal kecuali policy memang mengizinkannya.

---

# 75. URL MANIPULATION TEST

Sistem harus diuji dengan mencoba mengubah URL secara manual.

Contoh:

```text
/login
/register

/dashboard
/dashboard/999

/assessment/1
/assessment/999

/admin
/admin/users
/admin/rules
```

Test minimal:

### Guest

Tidak boleh:

```text
/dashboard
/assessment/*
/history
/profile
/admin/*
```

### User biasa

Boleh:

```text
/dashboard
/assessment
/history
/profile
```

Tidak boleh:

```text
/admin/*
```

dan tidak boleh membuka resource user lain.

### Admin

Boleh mengakses area admin sesuai permission.

---

# 76. AUTHENTICATION SECURITY

Login harus menggunakan Laravel authentication mechanism.

Wajib:

- session regeneration setelah login
- logout menggunakan POST jika desain memungkinkan
- session invalidation saat logout
- session regeneration
- CSRF protection
- password hashing
- rate limiting login

Jangan membuat authentication sendiri dengan:

```text
$_SESSION
localStorage sebagai sumber autentikasi
cookie buatan sendiri
```

---

# 77. LOGIN RATE LIMITING

Login harus memiliki pembatasan percobaan agar tidak mudah diserang brute force.

Implementasikan rate limiter Laravel pada endpoint login.

Contoh konsep:

```text
maksimal beberapa percobaan
dalam periode tertentu
berdasarkan username/IP
```

Jangan mengungkapkan apakah akun tertentu benar-benar terdaftar.

Pesan login gagal sebaiknya generik:

> Akun atau password yang Anda masukkan tidak sesuai.

Bukan:

> NIM tersebut terdaftar tetapi password salah.

---

# 78. REGISTRATION ABUSE PROTECTION

Registration juga perlu protection:

- rate limiting
- validation
- unique NIM
- unique akun/email
- CSRF
- server-side validation

Jangan membuat endpoint registration dapat dipanggil tanpa batas.

---

# 79. CSRF

Semua form POST/PUT/PATCH/DELETE wajib menggunakan CSRF protection Laravel.

Blade:

```blade
@csrf
```

Jangan mematikan CSRF middleware untuk mempermudah development.

---

# 80. MASS REQUEST / PARAMETER TAMPERING

Jangan mempercayai:

```text
role
user_id
assessment_id
result_id
severity
cf_score
dass_score
```

yang dikirim client.

Contoh:

User tidak boleh mengirim:

```text
severity=Normal
cf_score=100
```

untuk mengubah hasil.

Semua:

```text
CF
DASS
severity
highest severity
```

harus dihitung ulang server-side.

---

# 81. ASSESSMENT DATA TAMPERING

Saat submit assessment, server hanya menerima:

```text
symptom code / question id
answer value
```

Server kemudian menentukan:

```text
CF User
DASS Score
CF Expert
CF Final
CF Combine
Severity
```

Client tidak boleh menentukan hasil akhir.

---

# 82. VALIDASI ANSWER ASSESSMENT

Jawaban hanya boleh:

```text
0
1
2
3
```

Server harus menolak:

```text
-1
4
100
abc
null
array
```

Selain itu:

- semua 21 pertanyaan wajib ada
- tidak boleh ada duplicate question
- tidak boleh ada question ID yang tidak terdaftar
- tidak boleh ada symptom code asing
- assessment harus dimiliki user yang sedang login

---

# 83. PREVENT DUPLICATE SUBMISSION

User dapat tidak sengaja menekan submit berkali-kali.

Gunakan protection:

```text
button disabled
+
loading state
+
server-side transaction
+
idempotency / duplicate prevention jika diperlukan
```

Jangan hanya mengandalkan disabled button.

---

# 84. DATABASE CONSTRAINT

Security tidak boleh hanya berada di Laravel.

Database juga harus memiliki constraint untuk data penting.

Minimal:

```text
users.nim UNIQUE
users.akun UNIQUE jika akun memang unique
foreign keys
NOT NULL untuk field wajib
```

Jangan membuat unique constraint pada nomor telepon kecuali requirement memang mengharuskannya.

---

# 85. INFORMATION DISCLOSURE

Jangan menampilkan kepada user:

- SQL error
- stack trace
- filesystem path
- database credentials
- `.env`
- exception detail
- internal model information

Production:

```env
APP_DEBUG=false
```

Development boleh:

```env
APP_DEBUG=true
```

tetapi jangan pernah commit secret `.env`.

---

# 86. SECRET MANAGEMENT

Jangan menyimpan:

```text
DB password
APP_KEY
API key
WhatsApp secret
third-party credential
```

langsung dalam source code.

Gunakan:

```text
.env
```

dan konfigurasi Laravel.

Pastikan:

```text
.env
```

tidak masuk Git repository.

---

# 87. XSS PROTECTION

Artikel admin dapat mengandung rich text.

Jika HTML diperbolehkan:

- gunakan sanitization
- whitelist tag
- jangan render HTML mentah dari user tanpa sanitization

Hindari:

```blade
{!! $article->content !!}
```

tanpa sanitization.

Untuk text biasa:

```blade
{{ $article->content }}
```

lebih aman.

---

# 88. FILE UPLOAD SECURITY

Jika admin dapat upload thumbnail artikel:

Wajib:

- validasi MIME/type
- validasi extension
- validasi ukuran
- random filename
- simpan pada storage yang benar
- jangan mempercayai filename user
- jangan mengizinkan executable upload

Contoh file yang diperbolehkan:

```text
jpg
jpeg
png
webp
```

Jangan izinkan:

```text
php
phtml
phar
exe
js
```

sebagai executable upload.

---

# 89. AUTHORIZED EXPORT

Export assessment hanya boleh dilakukan:

```text
admin
```

atau:

```text
user → hanya data miliknya
```

Jangan membuat:

```text
/export/assessment/{id}
```

yang dapat digunakan untuk mengambil data user lain.

---

# 90. PRIVACY DATA PENELITIAN

Karena data user digunakan untuk penelitian, sistem harus memperlakukan:

```text
Nama
NIM
No. Telepon
Akun
Riwayat skrining
Hasil skrining
```

sebagai data yang harus dilindungi.

Prinsip:

- jangan tampilkan NIM lengkap pada halaman publik
- jangan tampilkan nomor telepon pada halaman publik
- jangan masukkan data pribadi ke URL jika tidak diperlukan
- batasi akses admin
- jangan expose data user melalui API tanpa authorization
- jangan menggunakan data penelitian untuk tujuan lain tanpa dasar/ketentuan yang sesuai

Untuk tabel admin, gunakan masking jika full value tidak diperlukan.

Contoh:

```text
NIM:
********1234

No. Telepon:
0812******90
```

Admin yang memang membutuhkan full data dapat memperoleh sesuai permission.

---

# 91. AUDIT LOG SECURITY

Catat aktivitas sensitif:

```text
Login berhasil
Login gagal
Logout
Registration
Perubahan profile
Perubahan rule
Perubahan data master
Export report
Perubahan artikel
Perubahan role
```

Minimal log:

```text
user_id
action
target_type
target_id
IP address jika memang diperlukan
user_agent jika memang diperlukan
timestamp
```

Jangan menyimpan password atau data sensitif yang tidak diperlukan di audit log.

---

# 92. ADMIN ROLE ESCALATION

User tidak boleh dapat mengubah dirinya menjadi admin dengan request:

```text
role=admin
```

Pastikan:

- role tidak ada di registration request
- role tidak ada di normal profile update
- role hanya dapat diubah melalui admin-authorized action
- perubahan role dicatat audit log

---

# 93. PROFILE UPDATE SECURITY

User hanya boleh mengubah data profilnya sendiri.

Tidak boleh mengirim:

```text
user_id=another-user-id
```

untuk mengubah akun orang lain.

Gunakan:

```php
auth()->user()
```

sebagai sumber identitas.

---

# 94. PASSWORD CHANGE

Jika fitur ubah password tersedia:

```text
Password lama
Password baru
Konfirmasi password baru
```

Wajib:

- verify password lama
- hash password baru
- invalidate/revoke session lain jika kebijakan aplikasi menerapkannya
- jangan log password

---

# 95. DELETE / DESTRUCTIVE ACTION

Untuk:

```text
delete user
delete article
delete symptom
delete rule
```

gunakan:

- authorization
- CSRF
- confirmation
- server-side validation
- audit log

Jangan melakukan delete hanya berdasarkan:

```text
?id=123
```

tanpa authorization.

---

# 96. SECURITY TEST CHECKLIST

## Authentication

- [ ] Guest tidak bisa membuka dashboard
- [ ] Guest tidak bisa membuka assessment
- [ ] Guest tidak bisa membuka history
- [ ] Guest tidak bisa membuka profile
- [ ] Guest tidak bisa membuka admin
- [ ] Login rate limit aktif
- [ ] Session aman
- [ ] Logout bekerja
- [ ] CSRF aktif

## Registration

- [ ] Nama wajib
- [ ] Nama hanya huruf + spasi
- [ ] NIM tepat 12 digit
- [ ] NIM unique
- [ ] Nomor telepon tervalidasi
- [ ] Akun unique
- [ ] Password aman
- [ ] Password confirmation
- [ ] Consent wajib
- [ ] Rate limit registration
- [ ] Tidak bisa set role=admin

## Authorization

- [ ] User tidak bisa menjadi admin melalui URL
- [ ] User tidak bisa membuka admin route
- [ ] User tidak bisa melihat assessment user lain
- [ ] User tidak bisa melihat result user lain
- [ ] User tidak bisa export data user lain
- [ ] Policy diterapkan
- [ ] Admin route menggunakan middleware

## Data Protection

- [ ] APP_DEBUG=false production
- [ ] `.env` tidak committed
- [ ] Password hashed
- [ ] SQL error tidak tampil ke user
- [ ] XSS protection
- [ ] Upload validation
- [ ] NIM/phone tidak bocor ke halaman publik
- [ ] Audit log tidak menyimpan password

---

# 97. SECURITY DEFINITION OF DONE

Security dianggap selesai apabila:

```text
Authentication
        +
Authorization
        +
Ownership Check
        +
Validation
        +
Database Constraints
        +
CSRF
        +
Rate Limiting
        +
Audit Log
        +
Privacy Protection
        +
Secure Error Handling
```

semuanya sudah diterapkan.

**Jangan menganggap aplikasi aman hanya karena user harus login.**

Login adalah authentication.

Pembatasan apa yang boleh dilakukan user adalah authorization.

Pembatasan data siapa yang boleh dilihat adalah ownership/access control.

Ketiganya wajib diterapkan.
