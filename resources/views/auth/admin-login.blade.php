<x-layouts.auth title="Login Admin"><div class="card mk-auth-card w-100" style="max-width:28rem"><div class="card-body p-4 p-md-5">
<span class="mk-icon-tile mb-3"><i class="bi bi-shield-lock" aria-hidden="true"></i></span><h1 class="h4">Masuk sebagai admin</h1><p class="small text-muted">Gunakan email pengelola yang telah didaftarkan.</p>
@if ($errors->any())<x-alert type="danger" title="Login belum berhasil">{{ $errors->first() }}</x-alert>@endif
<form method="POST" action="{{ route('admin.login') }}">@csrf
<label class="form-label" for="admin_email">Email admin</label><input id="admin_email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required maxlength="255" class="form-control mb-3">
<label class="form-label" for="admin_password">Password</label><input id="admin_password" name="password" type="password" autocomplete="current-password" required class="form-control mb-4">
<button type="submit" class="btn btn-mk-primary w-100">Masuk</button></form><a href="{{ route('login') }}" class="small d-inline-block mt-3">Login mahasiswa</a>
</div></div></x-layouts.auth>
