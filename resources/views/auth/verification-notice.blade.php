<x-layouts.app title="Verifikasi Identitas"><div class="card mx-auto" style="max-width:38rem"><div class="card-body p-4">
<h1 class="h4">Identitas menunggu verifikasi</h1><p class="text-muted">Pengelola perlu memeriksa kecocokan identitas mahasiswa sebelum Anda memulai skrining. Nama dan NIM tetap mengikuti data pendaftaran.</p>
@if(config('privacy.contact_email'))<a class="btn btn-mk-primary" href="mailto:{{ config('privacy.contact_email') }}">Hubungi Pengelola</a>@endif
<a class="btn btn-outline-secondary" href="{{ route('profile.show') }}">Lihat Profil</a></div></div></x-layouts.app>
