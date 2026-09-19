<x-layouts.admin title="Laporan">
    <h1 class="h3 fw-bold mb-4">Laporan & Ekspor Data</h1>
    <x-research-summary :summary="$summary" :metadata="$metadata" />
    @if (\App\Support\ResearchStudy::isResearch() && ! \App\Support\ResearchStudy::isReady())<x-alert type="warning" title="Penelitian belum dikonfigurasi">Lengkapi kode, periode, dan pengesahan protokol sebelum menerima peserta. Data demo/legacy tidak masuk laporan ini.</x-alert>@endif

    <p class="small text-muted">Pilih rentang tanggal untuk mengekspor. Maksimal {{ config('security.export_max_rows') }} baris per ekspor.</p>
    @if ($errors->any())<x-alert type="danger" title="Ekspor belum dapat diproses">{{ $errors->first() }}</x-alert>@endif
    <form method="GET" class="row g-2 align-items-end mb-4">
        <div class="col-auto">
            <label for="report_from" class="form-label small text-muted mb-1">Dari Tanggal</label>
            <input id="report_from" type="date" name="from" value="{{ request('from', $metadata['from']) }}" class="form-control form-control-sm">
        </div>
        <div class="col-auto">
            <label for="report_to" class="form-label small text-muted mb-1">Sampai Tanggal</label>
            <input id="report_to" type="date" name="to" value="{{ request('to', $metadata['to']) }}" class="form-control form-control-sm">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-mk-primary">Filter</button>
        </div>
        <div class="col-auto">
            <button type="submit" formaction="{{ route('admin.reports.export-excel') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-file-earmark-excel"></i> Ekspor Excel
            </button>
        </div>
        <div class="col-auto">
            <button type="submit" formaction="{{ route('admin.reports.export-pdf') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-file-earmark-pdf"></i> Ekspor PDF
            </button>
        </div>
        <div class="col-12"><div class="form-check mk-clickable-check"><input id="export_anonymized" type="checkbox" name="anonymized" value="1" class="form-check-input" @checked(request()->boolean('anonymized'))><label for="export_anonymized" class="form-check-label small">Samarkan identitas pada file ekspor penelitian</label></div></div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-surface">
                    <tr><th>NIM</th><th>Nama</th><th>Semester</th><th>Tanggal</th><th>Stres</th><th>Kecemasan</th><th>Depresi</th><th>Tingkat Tertinggi</th></tr>
                </thead>
                <tbody>
                    @forelse ($assessments as $a)
                        @php $r = $a->result; @endphp
                        <tr>
                            <td class="text-muted">{{ $a->user?->nim ?? '-'  }}</td>
                            <td>{{ $a->user?->name ?? 'Akun tidak tersedia' }}</td>
                            <td>{{ $a->research_semester ?? '—' }}</td>
                            <td class="text-muted">{{ $a->completed_at?->copy()->timezone(config('app.display_timezone'))->format('d/m/Y') }}</td>
                            <td>{{ $r?->stress_score }} &middot; {{ ucfirst($r?->stress_severity ?? '-') }}</td>
                            <td>{{ $r?->anxiety_score }} &middot; {{ ucfirst($r?->anxiety_severity ?? '-') }}</td>
                            <td>{{ $r?->depression_score }} &middot; {{ ucfirst($r?->depression_severity ?? '-') }}</td>
                            <td class="fw-semibold" style="color:var(--mk-primary)">{{ ucfirst($r?->highest_severity ?? '-') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">Data laporan belum tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $assessments->links() }}</div>
</x-layouts.admin>
