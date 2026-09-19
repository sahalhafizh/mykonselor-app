@props(['summary', 'metadata'])
<div class="border rounded-3 p-3 mb-4">
    <h2 class="h6 fw-bold">Ringkasan Responden</h2>
    <p class="small text-muted mb-2">{{ $metadata['mode'] }} · {{ $metadata['study_code'] }} · {{ $metadata['from'] ?? 'Awal data' }} sampai {{ $metadata['to'] ?? 'Akhir data' }} (WIB)</p>
    <p class="small mb-2"><strong>{{ $summary['respondents'] }} mahasiswa unik</strong> dari {{ $summary['responses'] }} pengisian selesai yang memenuhi syarat. {{ $metadata['selection_label'] }} dalam rentang laporan. Jika waktunya sama, urutan ID digunakan sebagai pembeda.</p>
    @if (\App\Support\ResearchStudy::isResearch())<p class="small mb-2">Data dibatasi periode penelitian {{ $metadata['study_starts_on'] }} sampai {{ $metadata['study_ends_on'] }} (WIB). Semester 7: {{ $summary['semesters'][7] ?? 0 }} mahasiswa · Semester 8: {{ $summary['semesters'][8] ?? 0 }} mahasiswa.</p>@endif
    <p class="small text-muted mb-0">{{ $metadata['scope'] }} Persentase dihitung dari mahasiswa unik pada setiap skala; persentase CF tidak digunakan sebagai persentase responden.</p>
</div>
