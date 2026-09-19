<x-layouts.auth title="Perbarui Password">
<div class="card mk-auth-card w-100" style="max-width:28rem"><div class="card-body p-4">
<h1 class="h4">Perbarui password Anda</h1><p class="text-muted small">Buat password pribadi sebelum melanjutkan ke data skrining.</p>
@if ($errors->any())<x-alert type="danger" title="Periksa kembali">{{ $errors->first() }}</x-alert>@endif
<form method="POST" action="{{ route('account.password.update') }}">@csrf @method('PUT')
<label class="form-label" for="temporary_password">Password saat ini</label><input id="temporary_password" type="password" name="current_password" required autocomplete="current-password" class="form-control mb-3">
<label class="form-label" for="required_password">Password baru</label><input id="required_password" type="password" name="password" required minlength="12" maxlength="72" autocomplete="new-password" class="form-control mb-2"><p class="small text-muted">Minimal 12 karakter: huruf besar, huruf kecil, dan angka.</p>
<label class="form-label" for="required_confirmation">Konfirmasi password</label><input id="required_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-control mb-3">
<button class="btn btn-mk-primary w-100" type="submit">Simpan Password</button></form>
<form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf<button class="btn btn-outline-secondary w-100" type="submit">Keluar</button></form>
</div></div></x-layouts.auth>
