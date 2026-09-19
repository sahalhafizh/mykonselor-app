<x-layouts.app title="Hasil Skrining">
    <div class="mx-auto" style="max-width:56rem">
        <div class="mb-4"><p class="small fw-bold text-uppercase mk-primary-text mb-2" style="letter-spacing:.08em">Hasil Skrining</p><h1 class="h3 fw-bold mb-0">Ringkasan Kondisi Anda</h1></div>

        <div class="alert alert-secondary d-flex gap-3 mb-4">
            <i class="bi bi-info-circle fs-5"></i>
            <div>Hasil ini merupakan skrining awal dan bukan diagnosis medis. Sistem tidak menggantikan pemeriksaan, konsultasi, atau penanganan oleh psikolog maupun tenaga kesehatan profesional.</div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card h-100"><div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-1">Tingkat Hasil DASS-21</h2>
                    <p class="small text-muted">Menggambarkan tingkat keparahan berdasarkan skor jawaban.</p>
                    @foreach ($result->clusters() as $cluster)
                        <div class="d-flex justify-content-between align-items-center border-top py-3">
                            <div><p class="fw-semibold mb-0">{{ $cluster['label'] }}</p><p class="small text-muted mb-0">Skor: {{ $cluster['score'] }}</p></div>
                            <span class="badge text-bg-{{ \App\Models\AssessmentResult::severityBadgeColor($cluster['severity']) }}">{{ ucfirst($cluster['severity']) }}</span>
                        </div>
                    @endforeach
                </div></div>
            </div>

            <div class="col-md-6">
                <div class="card h-100"><div class="card-body p-4">
                    <h2 class="h5 fw-bold mb-1">Hasil Certainty Factor</h2>
                    <p class="small text-muted">Menunjukkan tingkat keyakinan sistem untuk setiap kategori.</p>
                    @foreach ($result->clusters() as $cluster)
                        <div class="border-top py-3">
                            <div class="d-flex justify-content-between mb-2"><span class="fw-semibold">{{ $cluster['label'] }}</span><strong class="mk-primary-text">{{ $cluster['cf_percentage'] }}%</strong></div>
                            <div class="progress" role="progressbar" aria-label="CF {{ $cluster['label'] }}" aria-valuenow="{{ max($cluster['cf_percentage'], 0) }}" aria-valuemin="0" aria-valuemax="100" style="height:.5rem">
                                <div class="progress-bar" style="width:{{ min(max($cluster['cf_percentage'], 0), 100) }}%;background:var(--mk-primary)"></div>
                            </div>
                        </div>
                    @endforeach
                </div></div>
            </div>
        </div>

        <div class="card mb-4"><div class="card-body p-4">
            <span class="badge text-bg-{{ \App\Models\AssessmentResult::severityBadgeColor($result->highest_severity) }} mb-3">Tingkat keparahan tertinggi: {{ ucfirst($result->highest_severity) }}</span>
            @foreach ($result->clusters() as $key => $cluster)
                @if ($cluster['severity'] === $result->highest_severity && isset($diseases[$key]))
                    <h2 class="h6 fw-bold mb-1">{{ $diseases[$key]->nama }}</h2>
                    <p class="text-muted mb-3">{{ $diseases[$key]->panduanUntuk($result->highest_severity) }}</p>
                @endif
            @endforeach
            <div class="d-grid d-sm-flex gap-2">
                <a href="{{ route('assessment.referral', $assessment) }}" class="btn btn-mk-primary"><i class="bi bi-person-heart me-1"></i> Lihat Rujukan</a>
                <a href="{{ route('assessment.history') }}" class="btn btn-outline-secondary">Lihat Riwayat</a>
            </div>
        </div></div>

        <p class="small text-center text-muted mb-0">Skor DASS-21 dan persentase Certainty Factor memiliki fungsi berbeda dan tidak digabungkan menjadi satu angka.</p>
    </div>
</x-layouts.app>
