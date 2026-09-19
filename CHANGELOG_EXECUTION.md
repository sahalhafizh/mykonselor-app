# CHANGELOG EXECUTION MyKonselor

## Audit Singkat Baseline

- Framework terkunci pada Laravel 12.66.0, PHP requirement `^8.2`, dan Livewire 4.4.0.
- Bootstrap dan Bootstrap Icons sudah menjadi dependency, sedangkan Tailwind tidak tercantum pada manifest frontend.
- Migration dasar tersusun dengan tabel `users` lebih dahulu dan database SQLite lampiran tidak berisi data aplikasi.
- Service Certainty Factor dan DASS-21 sudah terpusat serta sesuai dengan PRD. Kedua service dipertahankan tanpa perubahan byte.
- Gap utama terdapat pada login akun nonaktif, consent registrasi, authorization sesi akun, konsistensi theme/title/layout, branding, referral user, dan ketiadaan pengelolaan referral di admin.

## Perubahan

| Area | File | Perubahan | Alasan |
|---|---|---|---|
| Stabilitas | `.env.example` | Nama aplikasi dan locale disesuaikan menjadi MyKonselor/Indonesia. | Menyamakan konfigurasi instalasi ulang dengan identitas aplikasi. |
| Database | `database/migrations/2025_01_01_000011_add_consent_and_processing_fields.php` | Menambah `data_consent_at`, `processed_by`, `processed_at`, serta constraint unik hasil dan pengajuan per assessment. | Menyimpan consent, jejak proses admin, dan mencegah duplikasi data. |
| Authentication | `app/Http/Requests/Auth/LoginRequest.php` | Login hanya menerima akun berstatus aktif dan selalu memakai pesan gagal generik. | Mencegah akun nonaktif masuk tanpa membocorkan status akun. |
| Session Security | `app/Http/Middleware/EnsureUserIsActive.php`, `bootstrap/app.php`, `routes/web.php` | Sesi akun yang dinonaktifkan dihentikan dan seluruh route privat memakai middleware aktif. | Menutup akses sesi lama setelah status akun berubah. |
| Registration | `RegisteredUserController.php`, `register.blade.php`, `User.php` | Menghapus input fakultas/prodi, normalisasi nama/email/telepon, menyimpan consent timestamp, dan menetapkan Fakultas Ilmu Komputer/Teknik Informatika secara internal. | Memenuhi ruang lingkup penelitian dan mencegah manipulasi role/field internal. |
| Authorization | `ReferralRequestPolicy.php`, `AssessmentController.php`, `SymptomWizard.php` | Ownership assessment/referral diperiksa server-side, Livewire memverifikasi pemilik, dan referral hanya dapat dibuat dari hasil selesai. Existing `AssessmentPolicy` tetap digunakan. | Mencegah IDOR dan parameter tampering. |
| Navigation | `routes/web.php`, `components/layouts/app.blade.php` | Artikel menjadi publik; mahasiswa login tetap dapat membuka Beranda, Dashboard, Skrining, Riwayat, dan Artikel. | Menyesuaikan matriks akses dan alur navigasi PRD. |
| Theme | `resources/css/app.css`, `resources/js/theme.js`, `components/head.blade.php` | Sistem light/dark global, `data-theme` + `data-bs-theme`, localStorage, early application, dan styling komponen Bootstrap user/admin. | Menghilangkan flash tema dan memperbaiki kontras lintas komponen. |
| Layout | `components/layouts/app.blade.php`, `admin.blade.php`, `auth.blade.php`, `footer.blade.php` | Layout reusable, sticky footer, sidebar/offcanvas admin, navbar responsif, dan state navigasi aktif. | Konsistensi user/admin serta dukungan mobile. |
| Branding | `config/branding.php`, `components/brand.blade.php`, `components/head.blade.php`, `public/images/README.md` | Struktur logo/favikon resmi dibuat reusable dengan fallback aman. Favikon kosong lama dihapus. | Tidak menggunakan asset yang tidak terverifikasi. |
| Page Title | Seluruh layout dan Blade halaman | Format title dinormalisasi menjadi `MyKonselor - Nama Halaman`. | Konsistensi identitas pada browser tab. |
| Copy/UI | Seluruh Blade user utama dan `DiseaseSeeder.php` | Emoji formal, em dash, campuran sapaan, dan istilah user-facing `severity` diganti dengan Bootstrap Icons serta Bahasa Indonesia formal. | Tampilan lebih profesional dan konsisten. |
| Dashboard Mahasiswa | `DashboardController.php`, `dashboard.blade.php` | Hero baru, statistik, penjelasan 21 pertanyaan, estimasi, CTA lanjutkan, dan disclaimer selalu tampil. | Memenuhi requirement dashboard dan batasan skrining. |
| Wizard | `SymptomWizard.php`, `livewire/symptom-wizard.blade.php` | Step informasi, progress yang benar, loading state, copy formal, dan ownership guard. | Memperjelas alur 21 pertanyaan serta keamanan komponen. |
| Result/History | `assessment/result.blade.php`, `assessment/history.blade.php` | DASS dan CF ditampilkan terpisah, terminology dibenahi, empty state dan layout responsif ditambahkan. | Menjaga interpretasi metode dan pengalaman pengguna. |
| Referral User | `config/referrals.php`, `AssessmentController.php`, `assessment/referral.blade.php` | Empat opsi layanan dipusatkan, CTA hanya muncul jika kontak tersedia, `href="#"` dihapus, duplikasi pengajuan dicegah, dan status ditampilkan. | Tidak mengarang kontak dan memberi alur referral yang dapat digunakan. |
| Referral Admin | `Admin/ReferralRequestController.php`, `ReferralRequest.php`, `admin/referrals/*`, `admin/dashboard.blade.php` | Daftar, filter, detail, ringkasan hasil, catatan, badge pending, serta update status dan processor timestamp. | Admin tidak perlu membuka database untuk menindaklanjuti pengajuan. |
| Admin Safety | `UserManagementController.php` | Endpoint user management menolak target ber-role admin. | Mencegah modifikasi admin melalui manipulasi URL. |
| Reporting | `AssessmentReportExport.php`, view report/PDF | Istilah tingkat keparahan diperjelas dan empty state ditambahkan. | Konsistensi istilah hasil skrining. |
| Pagination | `AppServiceProvider.php` | Pagination Laravel memakai Bootstrap 5. | Mencegah markup Tailwind default pada pagination. |
| Testing | `tests/Feature/*` | Menambah test akses guest, navigasi, akun nonaktif, admin, registrasi, authorization referral, pengelolaan referral, dan akun development. | Menyediakan regression suite sesuai Execution Prompt. |
| Dokumen | `PRD_Revisi_Menyeluruh_MyKonselor.md` | Nama PRD dinormalisasi sesuai referensi Execution Prompt. | Menghilangkan mismatch nama dokumen dalam source. |

## Logika Penelitian

`CertaintyFactorService.php` dan `Dass21Service.php` tidak diubah. Mapping jawaban, 21 cluster gejala, faktor pengali, rumus combine, MB/MD, serta cutoff tetap mengikuti source dan PRD.
