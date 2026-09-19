<x-layouts.app title="Riwayat">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div><h1 class="h3 fw-bold mb-1">Riwayat Skrining</h1><p class="text-muted mb-0">{{ \App\Support\ResearchStudy::isResearch() ? 'Daftar pengisian yang telah selesai. Skor individual ditinjau oleh peneliti berwenang.' : 'Tinjau hasil skrining yang telah Anda selesaikan.' }}</p></div>
        <a href="{{ route('assessment.create') }}" class="btn btn-mk-primary"><i class="bi bi-plus-lg me-1"></i> Skrining Baru</a>
    </div>

    @forelse ($assessments as $assessment)
        @php $restricted = \App\Support\ResearchStudy::participantResultsHidden($assessment); $result = $restricted ? null : $assessment->result; @endphp
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <p class="small text-muted mb-0"><i class="bi bi-calendar3 me-1"></i> {{ $assessment->completed_at?->copy()->timezone(config('app.display_timezone'))->translatedFormat('d F Y, H:i') }} WIB</p>
                    @if ($result)
                        <span class="badge text-bg-{{ \App\Models\AssessmentResult::severityBadgeColor($result->highest_severity) }}">Tingkat tertinggi: {{ ucfirst($result->highest_severity) }}</span>
                    @endif
                </div>

                @if ($result)
                    <div class="row g-2 g-md-3 small mb-3 mk-history-metrics">
                        @foreach ($result->clusters() as $cluster)
                            <div class="col-4"><div class="bg-surface border rounded-3 p-2 p-md-3 h-100"><span class="text-muted">{{ $cluster['label'] }}</span><p class="fw-semibold mb-0">Skor {{ $cluster['score'] }} <span class="d-none d-sm-inline">&middot; {{ ucfirst($cluster['severity']) }}</span></p><span class="d-sm-none text-muted">{{ ucfirst($cluster['severity']) }}</span></div></div>
                        @endforeach
                    </div>
                    <a href="{{ route('assessment.result', $assessment) }}" class="btn btn-sm btn-outline-secondary">Lihat Detail <i class="bi bi-arrow-right ms-1"></i></a>
                @elseif ($restricted)
                    <p class="small mb-3">Pengisian selesai dan jawaban telah tersimpan.</p>
                    <a href="{{ route('assessment.result', $assessment) }}" class="btn btn-sm btn-outline-secondary">Lihat Tanda Selesai</a>
                    <a href="{{ route('assessment.referral', $assessment) }}" class="btn btn-sm btn-outline-secondary">Bantuan Profesional</a>
                @else
                    <p class="small text-muted mb-0">Hasil belum tersedia.</p>
                @endif
            </div>
        </div>
    @empty
        <div class="card text-center">
            <div class="card-body py-5">
                <span class="mk-icon-tile mb-3"><i class="bi bi-clock-history"></i></span>
                <h2 class="h5 fw-bold">Belum ada riwayat skrining.</h2>
                <p class="text-muted">Riwayat akan tampil di sini setelah pengisian pertama diselesaikan.</p>
                <a href="{{ route('assessment.create') }}" class="btn btn-mk-primary">Mulai Skrining Pertama</a>
            </div>
        </div>
    @endforelse

    <div class="mt-4">{{ $assessments->links() }}</div>
</x-layouts.app>
