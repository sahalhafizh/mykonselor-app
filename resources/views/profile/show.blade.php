<x-layouts.app title="Profil Saya">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <p class="mk-section-eyebrow mb-1">Pengaturan akun</p>
            <h1 class="h3 fw-bold mb-1">Profil Saya</h1>
            <p class="text-muted mb-0">Kelola informasi profil dan keamanan akun Anda.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary align-self-start">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="mk-icon-tile"><i class="bi bi-person-vcard" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="h5 fw-bold mb-1">Informasi Profil</h2>
                            <p class="small text-muted mb-0">Anda dapat memperbarui email dan nomor telepon.</p>
                        </div>
                    </div>

                    @if ($errors->getBag('profileUpdate')->any())
                        <x-alert type="danger" title="Profil belum diperbarui" class="mb-3" tabindex="-1" data-validation-summary>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->getBag('profileUpdate')->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </x-alert>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label class="form-label" for="profile_name">Nama Lengkap</label>
                            <input id="profile_name" type="text" value="{{ $user->name }}" readonly
                                   aria-describedby="profile_name_hint" class="form-control bg-surface">
                            <p id="profile_name_hint" class="form-text">Nama terdaftar bersifat tetap dan tidak dapat diubah melalui profil.</p>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="profile_nim">NIM</label>
                                <input id="profile_nim" type="text" value="{{ $user->nim }}" readonly class="form-control bg-surface">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="profile_phone">Nomor Telepon</label>
                                <input id="profile_phone" type="tel" inputmode="numeric" name="no_telp"
                                       value="{{ old('no_telp', $user->no_telp) }}" autocomplete="tel" required class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="profile_email">Email</label>
                            <input id="profile_email" type="email" name="email" value="{{ old('email', $user->email) }}"
                                   autocomplete="email" required class="form-control">
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="profile_faculty">Fakultas</label>
                                <input id="profile_faculty" type="text" value="{{ $user->fakultas }}" readonly class="form-control bg-surface">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="profile_program">Program Studi</label>
                                <input id="profile_program" type="text" value="{{ $user->program_studi }}" readonly class="form-control bg-surface">
                            </div>
                        </div>

                        @if (\App\Support\ResearchStudy::isResearch())
                            <div class="border rounded-3 p-3 mb-4 small">
                                <p class="mb-1">Semester penelitian: <strong>{{ $participation?->semester ?? 'Belum diisi' }}</strong></p>
                                <a href="{{ route('research.participation') }}">Lihat persetujuan dan status peserta</a>
                                <p class="text-muted mb-0 mt-1">Hubungi pengelola bila data akademik perlu dikoreksi.</p>
                            </div>
                        @endif

                        <button type="submit" class="btn btn-mk-primary">
                            <i class="bi bi-check2-circle me-1" aria-hidden="true"></i> Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="mk-icon-tile"><i class="bi bi-shield-lock" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="h5 fw-bold mb-1">Ubah Password</h2>
                            <p class="small text-muted mb-0">Masukkan password lama untuk verifikasi.</p>
                        </div>
                    </div>

                    @if ($errors->getBag('passwordUpdate')->any())
                        <x-alert type="danger" title="Password belum diperbarui" class="mb-3" tabindex="-1" data-validation-summary>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->getBag('passwordUpdate')->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </x-alert>
                    @endif

                    <form method="POST" action="{{ route('profile.password.update') }}" data-confirm="Sesi masuk di perangkat lain akan dicabut setelah password diperbarui." data-confirm-title="Perbarui password?" data-confirm-action="Perbarui Password" data-confirm-tone="info">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label" for="current_password">Password Lama</label>
                            <input id="current_password" type="password" name="current_password" autocomplete="current-password" required class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="new_password">Password Baru</label>
                            <input id="new_password" type="password" name="password" autocomplete="new-password" minlength="12" maxlength="72" aria-describedby="password-requirements" required class="form-control">
                            <p id="password-requirements" class="form-text">Minimal 12 karakter, dengan huruf besar, huruf kecil, dan angka.</p>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="new_password_confirmation">Konfirmasi Password Baru</label>
                            <input id="new_password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required class="form-control">
                        </div>
                        <button type="submit" class="btn btn-outline-secondary">Perbarui Password</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-layouts.app>
