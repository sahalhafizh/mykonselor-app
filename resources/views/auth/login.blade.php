<x-layouts.auth title="Login">
    <div class="card shadow-sm w-100 mk-auth-card" style="max-width:26rem">
        <div class="card-body p-4 p-md-5">
            <div class="mk-auth-heading mb-4">
                <span class="mk-icon-tile mb-3"><i class="bi bi-person-check"></i></span>
                <h1 class="h4 fw-bold mb-1">Masuk ke MyKonselor</h1>
                <p class="text-muted small mb-0">Gunakan NIM dan password akun Anda.</p>
            </div>

            @if ($errors->any())
                <x-alert type="danger" title="Login belum berhasil" class="mb-3" tabindex="-1" data-validation-summary>{{ $errors->first() }}</x-alert>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="nim">NIM</label>
                    <input id="nim" type="text" inputmode="numeric" name="nim" value="{{ old('nim') }}"
                           minlength="12" maxlength="12" pattern="[0-9]{12}" autocomplete="username" required class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required class="form-control">
                </div>
                <div class="form-check mk-clickable-check mb-4">
                    <input id="remember" type="checkbox" name="remember" value="1" class="form-check-input">
                    <label class="form-check-label small" for="remember">Pertahankan sesi masuk</label>
                </div>
                <button type="submit" class="btn btn-mk-primary w-100">Masuk</button>
            </form>

            <p class="text-muted small text-center mt-4 mb-0">
                Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
            </p>
        </div>
    </div>
</x-layouts.auth>
