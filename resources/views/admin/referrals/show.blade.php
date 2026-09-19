<x-layouts.admin title="Pengajuan Rujukan">
    <a href="{{ route('admin.referrals.index') }}" class="d-inline-flex align-items-center gap-2 small text-muted mb-3"><i class="bi bi-arrow-left"></i> Kembali ke daftar</a>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 fw-bold mb-1">Detail Pengajuan Rujukan</h1><p class="text-muted mb-0">Diajukan {{ $referralRequest->created_at->copy()->timezone(config('app.display_timezone'))->translatedFormat('d F Y, H:i') }} WIB</p></div>
        <span class="badge {{ $referralRequest->statusBadgeClass() }} fs-6">{{ $referralRequest->statusLabel() }}</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-4"><div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Mahasiswa</h2>
                <dl class="row mb-0"><dt class="col-sm-4">Nama</dt><dd class="col-sm-8">{{ $referralRequest->user?->name ?? 'Akun tidak tersedia' }}</dd><dt class="col-sm-4">NIM</dt><dd class="col-sm-8">{{ $referralRequest->user?->nim ?? '-' }}</dd><dt class="col-sm-4">No. Telepon</dt><dd class="col-sm-8">{{ $referralRequest->user?->no_telp ?: '-' }}</dd></dl>
            </div></div>

            <div class="card"><div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Hasil Skrining Terkait</h2>
                @if ($referralRequest->assessment?->result)
                    @php $result = $referralRequest->assessment->result; @endphp
                    <div class="row g-3">
                        @foreach ($result->clusters() as $cluster)
                            <div class="col-md-4"><div class="bg-surface border rounded-3 p-3 h-100"><strong>{{ $cluster['label'] }}</strong><div class="small text-muted mt-1">Skor {{ $cluster['score'] }}<br>{{ ucfirst($cluster['severity']) }}<br>CF {{ $cluster['cf_percentage'] }}%</div></div></div>
                        @endforeach
                    </div>
                    <p class="small text-muted mt-3 mb-0">Assessment #{{ $referralRequest->assessment_id }} &middot; Tingkat keparahan tertinggi: {{ ucfirst($result->highest_severity) }}</p>
                @else
                    <p class="text-muted mb-0">Hasil terkait tidak tersedia.</p>
                @endif
            </div></div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-4"><div class="card-body p-4"><h2 class="h5 fw-bold mb-3">Catatan Mahasiswa</h2><p class="text-muted mb-0" style="white-space:pre-wrap">{{ $referralRequest->catatan ?: 'Tidak ada catatan tambahan.' }}</p></div></div>
            <div class="card"><div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Perbarui Status</h2>
                <form method="POST" action="{{ route('admin.referrals.update', $referralRequest) }}">
                    @csrf @method('PATCH')
                    <label for="referral-status" class="form-label">Status pengajuan</label>
                    <select id="referral-status" name="status" class="form-select mb-3" required>
                        @foreach (\App\Models\ReferralRequest::STATUS_LABELS as $value => $label)<option value="{{ $value }}" @selected(old('status', $referralRequest->status) === $value)>{{ $label }}</option>@endforeach
                    </select>
                    <button type="submit" class="btn btn-mk-primary">Simpan Status</button>
                </form>
                @if ($referralRequest->processed_at)
                    <p class="small text-muted mt-3 mb-0">Terakhir diproses oleh {{ $referralRequest->processedBy?->name ?? 'admin' }} pada {{ $referralRequest->processed_at->copy()->timezone(config('app.display_timezone'))->format('d/m/Y H:i') }} WIB.</p>
                @endif
            </div></div>
        </div>
    </div>
</x-layouts.admin>
