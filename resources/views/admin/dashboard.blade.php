<x-layouts.admin title="Dashboard Admin">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 fw-bold mb-1">Dashboard Admin</h1><p class="text-muted mb-0">Ringkasan aktivitas skrining dan pengajuan rujukan.</p></div>
        @if ($pendingReferrals > 0)
            <a href="{{ route('admin.referrals.index', ['status' => 'pending']) }}" class="btn btn-warning"><i class="bi bi-person-heart me-1"></i> {{ $pendingReferrals }} pengajuan menunggu</a>
        @endif
    </div>

    <x-research-summary :summary="$summary" :metadata="$metadata" />
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl">
            <div class="card h-100"><div class="card-body">
                <p class="text-muted small mb-1">Total Mahasiswa Terdaftar</p>
                <p class="fs-3 fw-bold mb-0">{{ $totalUsers }}</p>
            </div></div>
        </div>
        <div class="col-6 col-xl">
            <div class="card h-100"><div class="card-body">
                <p class="text-muted small mb-1">Skrining Hari Ini</p>
                <p class="fs-3 fw-bold mb-0" style="color:var(--mk-primary)">{{ $totalScreeningsToday }}</p>
            </div></div>
        </div>
        <div class="col-6 col-xl">
            <div class="card h-100"><div class="card-body">
                <p class="text-muted small mb-1">Skrining Bulan Ini</p>
                <p class="fs-3 fw-bold mb-0">{{ $totalAssessmentsThisMonth }}</p>
            </div></div>
        </div>
        <div class="col-6 col-xl">
            <div class="card h-100"><div class="card-body">
                <p class="text-muted small mb-1">Perlu Perhatian (Berat)</p>
                <p class="fs-3 fw-bold mb-0 text-danger">{{ $attentionCount }}</p>
            </div></div>
        </div>
        <div class="col-6 col-xl">
            <a href="{{ route('admin.referrals.index', ['status' => 'pending']) }}" class="card h-100 text-decoration-none">
                <div class="card-body"><p class="text-muted small mb-1">Pengajuan Menunggu</p><p class="fs-3 fw-bold mb-0 text-warning">{{ $pendingReferrals }}</p></div>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach (['stress' => 'Stres', 'anxiety' => 'Kecemasan', 'depression' => 'Depresi'] as $key => $label)
            <div class="col-md-4">
                <div class="card h-100"><div class="card-body">
                    <p class="fw-semibold mb-3">Distribusi Gejala {{ $label }}</p>
                    @forelse (['normal', 'ringan', 'sedang', 'berat'] as $sev)
                        <div class="d-flex justify-content-between border-top py-2 small" style="border-color:var(--mk-border) !important">
                            <span class="text-muted">{{ ucfirst($sev) }}</span>
                            @php $count = $distribution[$key][$sev] ?? 0; @endphp
                            <span>{{ $count }} mahasiswa · {{ $summary['respondents'] ? number_format($count / $summary['respondents'] * 100, 1, ',', '.') : '0,0' }}%</span>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Belum ada data.</p>
                    @endforelse
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-body">
            <p class="fw-semibold mb-3">Responden dengan Gejala Kategori Berat</p>
            <p class="small text-muted">Maksimal 10 hasil terpilih terbaru ditampilkan; total responden terkait: {{ $attentionCount }}. Kategori skrining tidak menetapkan diagnosis.</p>
            @forelse ($needsAttention as $a)
                <div class="d-flex justify-content-between border-top py-2 small" style="border-color:var(--mk-border) !important">
                    <span>{{ $a->user?->name ?? 'Akun tidak tersedia' }} ({{ $a->user?->nim ?? '-'  }})</span>
                    <span class="text-danger">{{ $a->completed_at?->copy()->timezone(config('app.display_timezone'))->format('d/m/Y') }}</span>
                </div>
            @empty
                <p class="text-muted small mb-0">Tidak ada saat ini.</p>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
