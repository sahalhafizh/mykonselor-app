<x-layouts.admin title="Kelola Profil Pengguna">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Kelola Profil Pengguna</h1>
            <p class="text-muted mb-0">Perbarui informasi profil mahasiswa tanpa mengubah data akademik atau hak akses.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary align-self-start">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> Kembali
        </a>
    </div>

    <div class="card shadow-sm" style="max-width:52rem">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="mk-icon-tile"><i class="bi bi-person-gear" aria-hidden="true"></i></span>
                <div>
                    <h2 class="h5 fw-bold mb-1">{{ $user->name }}</h2>
                    <span class="badge {{ $user->status === 'aktif' ? 'text-bg-success' : 'text-bg-danger' }}">{{ ucfirst($user->status) }}</span>
                </div>
            </div>

            @if ($errors->any())
                <x-alert type="danger" title="Profil belum diperbarui" class="mb-3">
                    <ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </x-alert>
            @endif

            <div class="border rounded p-3 mb-4">
                <p class="small mb-2">Identitas: <strong>{{ $user->identity_verified_at ? 'Sudah diverifikasi' : 'Belum diverifikasi' }}</strong></p>
                @if (\App\Support\ResearchStudy::isResearch())
                    <p class="small mb-2">Peserta TI UNPAM semester <strong>{{ $participation?->semester ?? 'belum diisi' }}</strong> · {{ $participation?->verified_at && \App\Support\ResearchStudy::hasCurrentConsent($participation) ? 'Kelayakan terverifikasi' : 'Perlu pemeriksaan peserta' }}</p>
                    @if ($participation)
                        <p class="small text-muted">Persetujuan {{ $participation->consent_version }}, {{ $participation->consented_at->copy()->timezone(config('app.display_timezone'))->format('d/m/Y H:i') }} WIB. Cocokkan dengan daftar/bukti mahasiswa yang sah, termasuk semester saat penelitian; NIM saja tidak menentukan semester aktif.</p>
                        <form method="POST" action="{{ route('admin.users.verify', $user) }}" data-confirm="Pastikan nama, NIM, program studi, dan semester telah dicocokkan dengan bukti peserta yang sah." data-confirm-title="Verifikasi peserta?" data-confirm-action="Verifikasi" data-confirm-password>
                            @csrf @method('PATCH')
                            <div class="form-check mk-clickable-check mb-2"><input id="eligibility-confirmed" type="checkbox" name="eligibility_confirmed" value="1" required class="form-check-input"><label for="eligibility-confirmed" class="form-check-label small">Saya telah memeriksa bahwa mahasiswa ini merupakan peserta TI UNPAM semester {{ $participation->semester }}.</label></div>
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Verifikasi Peserta</button>
                        </form>
                        @if ($participation->verified_at)
                            <form method="POST" action="{{ route('admin.users.research.revoke', $user) }}" class="mt-2" data-confirm="Pengisian peserta dihentikan dan datanya dikeluarkan dari rekap sampai diverifikasi kembali." data-confirm-title="Cabut verifikasi peserta?" data-confirm-action="Cabut Verifikasi" data-confirm-password>
                                @csrf @method('PATCH')<button type="submit" class="btn btn-sm btn-outline-danger">Cabut Verifikasi Peserta</button>
                            </form>
                        @endif
                    @else
                        <p class="small text-muted mb-0">Mahasiswa perlu melengkapi persetujuan penelitian dan semester melalui akunnya.</p>
                    @endif
                @elseif (! $user->identity_verified_at)
                <form method="POST" action="{{ route('admin.users.verify', $user) }}" data-confirm="Pastikan nama dan NIM sudah cocok dengan bukti mahasiswa atau daftar responden yang sah." data-confirm-title="Verifikasi identitas?" data-confirm-action="Sudah Diperiksa" data-confirm-password>@csrf @method('PATCH')
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Tandai Sudah Diverifikasi</button>
                </form>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="admin_profile_name">Nama Lengkap</label>
                        <input id="admin_profile_name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="admin_profile_nim">NIM</label>
                        <input id="admin_profile_nim" type="text" value="{{ $user->nim }}" readonly class="form-control bg-surface">
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="admin_profile_email">Email</label>
                        <input id="admin_profile_email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="admin_profile_phone">Nomor Telepon</label>
                        <input id="admin_profile_phone" type="tel" inputmode="numeric" name="no_telp"
                               value="{{ old('no_telp', $user->no_telp) }}" required class="form-control">
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="admin_profile_faculty">Fakultas</label>
                        <input id="admin_profile_faculty" type="text" value="{{ $user->fakultas }}" readonly class="form-control bg-surface">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="admin_profile_program">Program Studi</label>
                        <input id="admin_profile_program" type="text" value="{{ $user->program_studi }}" readonly class="form-control bg-surface">
                    </div>
                </div>

                <button type="submit" class="btn btn-mk-primary">
                    <i class="bi bi-check2-circle me-1" aria-hidden="true"></i> Simpan Profil
                </button>
            </form>
        </div>
    </div>
</x-layouts.admin>
