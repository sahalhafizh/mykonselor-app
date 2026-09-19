<x-layouts.admin title="Pengguna">
    <h1 class="h3 fw-bold mb-4">Manajemen Data Pengguna</h1>

    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <label class="visually-hidden" for="user-search">Cari NIM atau nama</label>
            <input id="user-search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIM atau nama..." class="form-control form-control-sm">
        </div>
        <div class="col-auto">
            <label class="visually-hidden" for="user-status">Status akun</label>
            <select id="user-status" name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="aktif" @selected(request('status')==='aktif')>Aktif</option>
                <option value="nonaktif" @selected(request('status')==='nonaktif')>Nonaktif</option>
            </select>
        </div>
        <div class="col-auto">
            <label class="visually-hidden" for="user-verification">Verifikasi identitas/peserta</label>
            <select id="user-verification" name="verification" class="form-select form-select-sm">
                <option value="">Semua Verifikasi</option>
                <option value="pending" @selected(request('verification') === 'pending')>Belum Diverifikasi</option>
                <option value="verified" @selected(request('verification') === 'verified')>Sudah Diverifikasi</option>
            </select>
        </div>
        @if (\App\Support\ResearchStudy::isResearch())
            <div class="col-auto">
                <label class="visually-hidden" for="user-semester">Semester penelitian</label>
                <select id="user-semester" name="semester" class="form-select form-select-sm">
                    <option value="">Semua Semester</option>
                    @foreach ([7, 8] as $semester)<option value="{{ $semester }}" @selected((string) request('semester') === (string) $semester)>Semester {{ $semester }}</option>@endforeach
                </select>
            </div>
        @endif
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-mk-primary">Cari</button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-surface">
                    <tr><th>NIM</th><th>Nama</th><th>No. Telepon</th><th>Skrining</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td class="text-muted">{{ $user->nim }}</td>
                            <td>{{ $user->name }}<small class="d-block text-muted">{{ $user->identity_verified_at ? 'Identitas terverifikasi' : 'Perlu verifikasi identitas' }}</small>
                                @if (\App\Support\ResearchStudy::isResearch())
                                    @php $participation = $user->researchParticipations->first(); @endphp
                                    <small class="d-block text-muted">Semester {{ $participation?->semester ?? 'belum diisi' }} · {{ $participation?->verified_at && \App\Support\ResearchStudy::hasCurrentConsent($participation) ? 'Peserta terverifikasi' : 'Peserta belum diverifikasi' }}</small>
                                @endif
                            </td>
                            <td class="text-muted">{{ $user->maskedPhone() }}</td>
                            <td class="text-muted">{{ $user->assessments_count }}</td>
                            <td>
                                <span class="badge {{ $user->status === 'aktif' ? 'text-bg-success' : 'text-bg-danger' }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-link p-0 me-2 text-decoration-none" style="color:var(--mk-primary)">Kelola Profil</a>
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline" data-confirm="Status akses pengguna ini akan diubah. Menonaktifkan akun akan mencabut sesi masuknya." data-confirm-title="Ubah status akun?" data-confirm-action="Ubah Status" data-confirm-tone="warning">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-link p-0 me-2" style="color:var(--mk-primary)">{{ $user->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="d-inline"
                                      data-confirm="Password {{ $user->name }} akan diganti dengan password sementara baru."
                                      data-confirm-password data-confirm-title="Reset password pengguna?" data-confirm-action="Reset Password" data-confirm-tone="warning">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-link p-0 text-muted">Reset Password</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
</x-layouts.admin>
