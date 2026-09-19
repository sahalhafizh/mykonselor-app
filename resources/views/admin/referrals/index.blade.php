<x-layouts.admin title="Pengajuan Rujukan">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 fw-bold mb-1">Pengajuan Rujukan</h1><p class="text-muted mb-0">Tinjau dan perbarui tindak lanjut pengajuan mahasiswa.</p></div>
        <span class="badge text-bg-warning fs-6">{{ $pendingCount }} menunggu</span>
    </div>

    <form method="GET" class="row g-2 mb-4 mk-filter-form">
        <div class="col-12 col-sm-6 col-lg-4"><label for="search" class="visually-hidden">Cari mahasiswa</label><input id="search" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIM" class="form-control"></div>
        <div class="col-8 col-sm-4 col-lg-3"><label for="status" class="visually-hidden">Status</label><select id="status" name="status" class="form-select"><option value="">Semua status</option>@foreach (\App\Models\ReferralRequest::STATUS_LABELS as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-4 col-sm-auto"><button type="submit" class="btn btn-mk-primary w-100">Filter</button></div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Mahasiswa</th><th>Waktu</th><th>Hasil Terkait</th><th>Catatan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($referrals as $referral)
                        @php $result = $referral->assessment?->result; @endphp
                        <tr>
                            <td><strong>{{ $referral->user?->name ?? 'Akun tidak tersedia' }}</strong><br><small class="text-muted">{{ $referral->user?->nim ?? '-' }}</small></td>
                            <td class="text-muted text-nowrap">{{ $referral->created_at->copy()->timezone(config('app.display_timezone'))->format('d/m/Y H:i') }} WIB</td>
                            <td class="small">
                                @if ($result)
                                    Stres {{ ucfirst($result->stress_severity) }}, Kecemasan {{ ucfirst($result->anxiety_severity) }}, Depresi {{ ucfirst($result->depression_severity) }}
                                @else
                                    <span class="text-muted">Hasil tidak tersedia</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ \Illuminate\Support\Str::limit($referral->catatan ?: 'Tidak ada catatan', 80) }}</td>
                            <td><span class="badge {{ $referral->statusBadgeClass() }}">{{ $referral->statusLabel() }}</span></td>
                            <td><a href="{{ route('admin.referrals.show', $referral) }}" class="btn btn-sm btn-outline-secondary">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Data pengajuan belum tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $referrals->links() }}</div>
</x-layouts.admin>
