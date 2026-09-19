<x-layouts.app title="Dashboard">
    @if (\App\Support\ResearchStudy::isResearch())
        <p class="small text-muted mb-3">Penelitian TI UNPAM semester 7–8. <a href="{{ route('research.participation') }}">Lihat persetujuan dan status peserta</a>. Setelah pengisian, Anda menerima tanda selesai; skor individual ditinjau oleh peneliti berwenang.</p>
    @endif
    <div class="mk-dashboard mk-fade-in">
        <div class="mk-dashboard-overview mb-4">
            <div class="mk-dashboard-greeting">
                <span class="mk-icon-tile"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span>
                <div>
                    <h1 class="h2 fw-bold mb-2">Halo, {{ auth()->user()->name }}</h1>
                    <p class="text-muted mb-0">Pantau kondisi kesehatan mental Anda melalui skrining awal yang tersedia di MyKonselor.</p>
                </div>
            </div>
            <div class="mk-dashboard-stats">
                <div class="mk-stat-card">
                    <p class="small text-muted mb-2"><i class="bi bi-clipboard2-check me-1" aria-hidden="true"></i> Total Skrining</p>
                    <p class="h3 fw-bold mb-0">{{ $totalAssessments }}</p>
                </div>
                <div class="mk-stat-card">
                    <p class="small text-muted mb-2"><i class="bi bi-calendar3 me-1" aria-hidden="true"></i> Skrining Terakhir</p>
                    <p class="fw-bold mb-0">{{ $lastAssessment?->completed_at?->copy()->timezone(config('app.display_timezone'))->translatedFormat('d M Y') ?? 'Belum ada' }}</p>
                </div>
            </div>
        </div>

        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                <div class="card mk-feature-card mk-dashboard-screening">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3 mb-3 mb-md-4">
                            <span class="mk-icon-tile flex-shrink-0"><i class="bi bi-ui-checks"></i></span>
                            <div>
                                <h2 class="h5 fw-bold mb-2">Skrining DASS-21</h2>
                                <p class="text-muted mb-0">Jawab 21 pertanyaan dalam sekitar lima menit untuk memperoleh gambaran awal Stres, Kecemasan, dan Depresi.</p>
                            </div>
                        </div>
                        <div class="alert alert-secondary small">
                            MyKonselor merupakan alat skrining awal. Hasil yang diberikan bukan diagnosis medis dan tidak menggantikan pemeriksaan, konsultasi, atau penanganan oleh psikolog maupun tenaga kesehatan profesional.
                        </div>
                        <a href="{{ route('assessment.create') }}" class="btn btn-mk-primary mk-mobile-full-button">
                            <i class="bi {{ $inProgress ? 'bi-play-circle' : 'bi-clipboard2-pulse' }} me-1"></i>
                            {{ $inProgress ? 'Lanjutkan Skrining' : 'Mulai Skrining' }}
                            <i class="bi bi-arrow-right ms-2" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card mk-quick-access">
                    <div class="card-body">
                        <h2 class="h6 fw-bold mb-3">Akses Cepat</h2>
                        <div class="list-group list-group-flush">
                            <a href="{{ route('assessment.history') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                <span class="mk-icon-tile"><i class="bi bi-clock-history" aria-hidden="true"></i></span><span><strong>Riwayat Skrining</strong><br><small class="text-muted">{{ \App\Support\ResearchStudy::isResearch() ? 'Lihat status pengisian terdahulu.' : 'Tinjau hasil terdahulu.' }}</small></span>
                                <i class="bi bi-arrow-up-right ms-auto" aria-hidden="true"></i>
                            </a>
                            <a href="{{ route('articles.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                <span class="mk-icon-tile"><i class="bi bi-journal-text" aria-hidden="true"></i></span><span><strong>Artikel Edukasi</strong><br><small class="text-muted">Baca materi kesehatan mental.</small></span>
                                <i class="bi bi-arrow-up-right ms-auto" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
