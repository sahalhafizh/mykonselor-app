<x-layouts.auth title="Registrasi">
    <div class="card shadow-sm w-100 mk-auth-card" style="max-width:32rem">
        <div class="card-body p-4 p-md-5">
            <div class="mk-auth-heading mb-4">
                <span class="mk-icon-tile mb-3"><i class="bi bi-person-plus"></i></span>
                <h1 class="h4 fw-bold mb-1">Registrasi Mahasiswa</h1>
                <p class="text-muted small mb-0">{{ \App\Support\ResearchStudy::isResearch() ? 'Pendaftaran peserta penelitian TI UNPAM semester 7–8.' : 'Buat akun untuk menyimpan hasil dan riwayat skrining Anda.' }}</p>
            </div>

            @if ($errors->any())
                <x-alert type="danger" title="Periksa data pendaftaran" class="mb-3" tabindex="-1" data-validation-summary>
                    <ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </x-alert>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" maxlength="100" aria-describedby="name-hint" autocomplete="name" required class="form-control">
                    <div id="name-hint" class="form-text">Gunakan huruf dan spasi sesuai identitas Anda.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="nim">NIM</label>
                    <input id="nim" type="text" inputmode="numeric" name="nim" value="{{ old('nim') }}"
                           minlength="12" maxlength="12" pattern="[0-9]{12}" aria-describedby="nim-hint" autocomplete="username" required class="form-control">
                    <div id="nim-hint" class="form-text">NIM harus terdiri dari tepat 12 digit.</div>
                </div>
                <div class="mb-3">
                    @php($selectedSemester = filter_var(old('semester'), FILTER_VALIDATE_INT))
                    <label for="semester" class="form-label">Semester saat mendaftar</label>
                    <select id="semester" name="semester" class="form-select @error('semester') is-invalid @enderror" required
                            @error('semester') aria-invalid="true" aria-describedby="semester-error" @enderror>
                        <option value="" disabled @selected(! in_array($selectedSemester, config('research.semesters'), true))>Pilih semester Anda</option>
                        @foreach (config('research.semesters') as $semester)
                            <option value="{{ $semester }}" @selected($selectedSemester === $semester)>Semester {{ $semester }}</option>
                        @endforeach
                    </select>
                    @error('semester')<div id="semester-error" class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="no_telp">No. Telepon</label>
                    <input id="no_telp" type="tel" inputmode="numeric" name="no_telp" value="{{ old('no_telp') }}"
                           maxlength="20" autocomplete="tel" placeholder="081234567890" required class="form-control">
                </div>
                @if (\App\Support\ResearchStudy::isResearch())
                    <x-research-consent />
                    <div class="form-check mk-clickable-check mb-3">
                        <input id="research-consent" type="checkbox" name="research_consent" value="1" required class="form-check-input" @checked(old('research_consent'))>
                        <label for="research-consent" class="form-check-label small">Saya memenuhi kriteria peserta, telah membaca informasi penelitian, dan bersedia berpartisipasi secara sukarela.</label>
                    </div>
                @endif
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required class="form-control">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label" for="password">Password</label>
                        <input id="password" type="password" name="password" autocomplete="new-password" minlength="12" maxlength="72" aria-describedby="password-requirements" required class="form-control">
                            <p id="password-requirements" class="form-text">Minimal 12 karakter, dengan huruf besar, huruf kecil, dan angka.</p>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required class="form-control">
                    </div>
                </div>

                <div class="alert alert-secondary small">
                    <p class="mb-2">
                        <strong>Persetujuan penggunaan data:</strong> Data yang Anda berikan digunakan untuk fungsi
                        MyKonselor dan keperluan penelitian sesuai informasi pada
                        <a href="{{ route('legal.privacy') }}" target="_blank" rel="noopener noreferrer">Kebijakan Privasi</a>.
                    </p>
                    <div class="form-check mk-clickable-check">
                        <input type="checkbox" name="consent" value="1" class="form-check-input" id="consent" required {{ old('consent') ? 'checked' : '' }}>
                        <label class="form-check-label" for="consent">
                            Saya menyatakan data yang saya masukkan benar dan menyetujui penggunaannya untuk keperluan penelitian.
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-mk-primary w-100">Daftar</button>
            </form>

            <p class="text-muted small text-center mt-4 mb-0">
                Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
            </p>
        </div>
    </div>
</x-layouts.auth>
