<x-layouts.auth title="Verifikasi Admin"><div class="card mk-auth-card w-100" style="max-width:28rem"><div class="card-body p-4">
<h1 class="h4">Verifikasi dua langkah</h1><p class="small text-muted">Masukkan kode dari aplikasi Authenticator Anda.</p>
@if ($errors->any())<x-alert type="danger" title="Verifikasi belum berhasil">{{ $errors->first() }}</x-alert>@endif
<form method="POST" action="{{ route('admin.challenge.verify') }}">@csrf<label class="form-label" for="otp_code">Kode enam digit</label><input id="otp_code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" required class="form-control mb-3"><button type="submit" class="btn btn-mk-primary w-100">Verifikasi</button></form>
<a href="{{ route('admin.login') }}" class="small d-inline-block mt-3">Kembali ke login admin</a>
</div></div></x-layouts.auth>
