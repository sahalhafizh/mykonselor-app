# TEST REPORT MyKonselor

Tanggal eksekusi: 29 Agustus 2026 (UTC)

## Ringkasan Status

| Area | Status | Bukti |
|---|---|---|
| `npm install` | DONE | Command selesai dengan exit code 0. |
| Frontend production build | DONE | Vite 5.4.21 membangun 61 module dan menghasilkan manifest/CSS/JS di `public/build`. |
| PHP source parse | DONE | 85 file PHP diparsing, 0 syntax failure. Ini static parse, bukan eksekusi Laravel. |
| Blade directive balance | DONE | 29 file Blade diperiksa, 0 ketidakseimbangan directive yang dideteksi. Ini static check, bukan render Laravel. |
| Final forbidden-pattern scan | DONE | Tidak ditemukan `href="#"`, emoji UI, em dash user-facing, input fakultas/prodi pada registrasi, atau dependency/import Tailwind pada source frontend. |
| CF/DASS regression byte check | DONE | `CertaintyFactorService.php` dan `Dass21Service.php` identik dengan source awal. |
| `composer install` | BLOCKED | Command gagal karena executable `composer` tidak tersedia di runtime. |
| `php artisan test` | BLOCKED | Command gagal karena executable `php` tidak tersedia di runtime. |
| Migration/seeder Laravel | NOT TESTED | Tidak dijalankan karena PHP tidak tersedia. Database lampiran diperiksa dan tidak berisi tabel/data aplikasi. |
| Login akun development | NOT TESTED | Tidak dapat diuji melalui runtime Laravel. Test case untuk kedua akun sudah ditambahkan tetapi belum dapat dieksekusi. |
| Manual browser/theme/responsive QA | NOT TESTED | Aplikasi Laravel tidak dapat dijalankan tanpa PHP. |

## Command yang Dijalankan

```text
npm install
npm run build
composer install --no-interaction
php artisan test
```

Hasil build frontend:

```text
vite v5.4.21 building for production
61 modules transformed
public/build/manifest.json
public/build/assets/app-BUGOemTT.css
public/build/assets/app-CsZVoOCA.js
Build completed successfully
```

Hasil command Laravel:

```text
composer: command not found
php: command not found
```

`php artisan migrate:fresh --seed` tidak dijalankan karena PHP tidak tersedia. Command tersebut juga tidak diarahkan ke database pengguna.

## Test Suite

Terdapat 40 method test dalam source:

- 18 unit tests.
- 22 feature tests.
- 8 file feature test baru untuk requirement Execution Prompt.

Test baru mencakup:

- `GuestAccessTest`
- `AuthenticatedNavigationTest`
- `InactiveUserLoginTest`
- `AdminAuthorizationTest`
- `RegistrationValidationTest`
- `ReferralAuthorizationTest`
- `AdminReferralManagementTest`
- `DevelopmentAccountsTest`

Seluruh test di atas berstatus **NOT TESTED** pada runtime ini karena PHPUnit membutuhkan PHP.

## Akun Development yang Dicakup Test

```text
Mahasiswa: 123456789012 / password
Admin:     000000000000 / password
```

Seeder mempertahankan kedua akun tersebut. `DevelopmentAccountsTest` menguji target redirect mahasiswa ke Dashboard dan admin ke Dashboard Admin, tetapi test belum dieksekusi pada runtime ini.

## Verifikasi Lanjutan yang Wajib

Pada environment dengan PHP 8.2+ dan Composer, jalankan:

```text
composer install
npm install
npm run build
php artisan migrate:fresh --seed
php artisan test
```

Gunakan database development/testing disposable untuk `migrate:fresh`. Setelah itu lakukan runtime QA mahasiswa/admin, persistence theme setelah refresh, update status referral, serta responsive check pada mobile, tablet, dan desktop.
