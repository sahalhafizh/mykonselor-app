<x-layouts.auth title="Kebijakan Privasi"><article class="card w-100" style="max-width:46rem"><div class="card-body p-4 p-md-5">
<h1 class="h3">Kebijakan Privasi</h1><p class="small text-muted">Versi {{ config('privacy.version') }} · Diperbarui {{ config('privacy.updated_at') }}</p>
<h2 class="h5 mt-4">Pengelola dan permintaan terkait data</h2>
@if(config('privacy.operator') && config('privacy.contact_email'))
<p>Pengelola: <strong>{{ config('privacy.operator') }}</strong>. Permintaan koreksi, akses, penghentian penggunaan, atau penghapusan data dapat disampaikan ke <a href="mailto:{{ config('privacy.contact_email') }}">{{ config('privacy.contact_email') }}</a>.</p>
<p>Pengelola akan memverifikasi identitas pemohon sebelum memberikan atau mengubah data. Jangan mengirim password atau kode Authenticator. Penghapusan data diproses oleh pengelola; pengguna tidak dapat menghapus akun sendiri.</p>
@else
<p>Informasi pengelola dan kanal permintaan data belum ditetapkan. Konfigurasi ini harus dilengkapi sebelum menerima data pengguna umum.</p>
@endif
<h2 class="h5 mt-4">Data yang dikumpulkan</h2><p>Nama, NIM, email, nomor telepon, semester penelitian, waktu dan versi persetujuan, status verifikasi peserta, jawaban skrining, hasil perhitungan, serta pengajuan rujukan. Fakultas dan program studi mengikuti lingkup penelitian Teknik Informatika, Fakultas Ilmu Komputer.</p>
<h2 class="h5 mt-4">Tujuan dan akses</h2><p>Data digunakan untuk skrining awal, riwayat pribadi, pengelolaan rujukan, dan penelitian akademik sesuai ruang lingkup yang disetujui. Hasil bukan diagnosis medis. @if (\App\Support\ResearchStudy::isResearch()) Penelitian ditujukan kepada mahasiswa Teknik Informatika UNPAM semester 7–8 yang telah memberi persetujuan dan diverifikasi pengelola. Skor dan interpretasi individual hanya tersedia bagi peneliti/admin berwenang. Peserta dapat melihat status pengisian dan mengakses bantuan profesional. Partisipasi bersifat sukarela; penghentian penggunaan data dapat diminta melalui pengelola. @else Pada demo lokal, hasil simulasi dapat dilihat oleh pemilik akun dan admin untuk demonstrasi. Gunakan hanya data fiktif. @endif Laporan penelitian yang tidak memerlukan identitas menggunakan keluaran tersamarkan.</p>
<h2 class="h5 mt-4">Keamanan dan layanan eksternal</h2><p>Password disimpan sebagai hash dan nomor telepon dienkripsi oleh aplikasi. Pengelola membatasi akses database, sesi, ekspor, dan backup. Ketika Anda membuka WhatsApp atau situs rujukan, layanan tersebut berlaku menurut kebijakan penyedianya; aplikasi tidak otomatis mengirim jawaban skrining melalui tautan itu.</p>
<h2 class="h5 mt-4">Masa simpan dan penghapusan</h2>
@if(config('privacy.retention_days') > 0 && config('privacy.backup_retention_days') > 0)
<p>Pengelola meninjau data akun yang tidak aktif selama {{ config('privacy.retention_days') }} hari untuk penghapusan atau anonimisasi sesuai kebutuhan penelitian yang disetujui. Salinan backup memiliki masa simpan maksimal {{ config('privacy.backup_retention_days') }} hari. Jika pemulihan backup dilakukan, daftar permintaan penghapusan perlu diterapkan kembali.</p>
@else
<p>Masa simpan data dan backup sedang menunggu penetapan pengelola. Penggunaan publik belum dapat dimulai sebelum ketentuan ini disahkan.</p>
@endif
<p>Menonaktifkan atau mengarsipkan akun tidak otomatis memusnahkan seluruh data. Penghapusan permanen diproses terpisah oleh pengelola setelah verifikasi. Ekspor tersamarkan memakai kode responden dan merupakan pseudonimisasi, bukan anonimisasi permanen. File ini tetap perlu dibatasi aksesnya.</p>
<a class="btn btn-outline-secondary mt-3" href="{{ route('welcome') }}">Kembali ke Beranda</a>
</div></article></x-layouts.auth>
