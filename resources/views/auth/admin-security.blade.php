<x-layouts.auth title="Keamanan Admin"><div class="card mk-auth-card w-100" style="max-width:32rem"><div class="card-body p-4">
<h1 class="h4">Keamanan akun admin</h1>
@if ($verified)<x-alert type="success" title="Verifikasi dua langkah aktif">Sesi ini telah diverifikasi.</x-alert><a href="{{ route('admin.dashboard') }}" class="btn btn-mk-primary">Kembali ke Dashboard</a>
@else
@if ($secret)<p class="small text-muted">Tambahkan akun di aplikasi Authenticator, pilih memasukkan kunci secara manual, lalu gunakan kunci berikut dengan jenis berbasis waktu. Simpan kunci secara privat.</p><code class="d-block p-3 border rounded text-break mb-3">{{ $secret }}</code><p class="small text-muted">Nama akun: MyKonselor ({{ $user->email }}). Kunci pengaturan berlaku 10 menit.</p>
@else<p class="small text-muted">Verifikasi kembali password dan kode Authenticator untuk membuka area admin.</p>@endif
@if ($errors->any())<x-alert type="danger" title="Verifikasi belum berhasil">{{ $errors->first() }}</x-alert>@endif
<form method="POST" action="{{ route('admin.security.confirm') }}">@csrf
<label class="form-label" for="security_password">Password admin</label><input id="security_password" name="current_password" type="password" autocomplete="current-password" required class="form-control mb-3">
<label class="form-label" for="security_code">Kode Authenticator</label><input id="security_code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required class="form-control mb-3"><button type="submit" class="btn btn-mk-primary w-100">Verifikasi dan Lanjutkan</button></form>
@endif
<p class="small text-muted mt-3">Jika perangkat hilang, pemulihan dilakukan oleh pengelola server setelah verifikasi identitas.</p><form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-secondary" type="submit">Keluar</button></form>
<p class="small mt-3 mb-0"><a href="{{ route('account.password.edit') }}">Ubah password admin</a></p></div></div>
</x-layouts.auth>
