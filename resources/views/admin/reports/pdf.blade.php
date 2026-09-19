<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1F2937; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p.subtitle { color: #6B7280; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #E5E7EB; padding: 6px 8px; text-align: left; }
        th { background: #F3F5F7; }
    </style>
</head>
<body>
    <h1>Laporan Skrining MyKonselor</h1>
    <p class="subtitle">
        Periode: {{ $from?->copy()->timezone(config('app.display_timezone'))->format('d/m/Y') ?? 'Semua' }} sampai {{ $to?->copy()->timezone(config('app.display_timezone'))->format('d/m/Y') ?? 'Semua' }} &middot;
        Dicetak: {{ now(config('app.display_timezone'))->format('d/m/Y H:i') }} WIB
    </p>

    @isset($metadata)
        <p>{{ $metadata['mode'] }} · {{ $metadata['study_code'] }} · {{ $metadata['selection_label'] }} dalam rentang {{ $metadata['from'] ?? 'awal data' }} sampai {{ $metadata['to'] ?? 'akhir data' }} (WIB).</p>
        @if ($metadata['study_starts_on'])<p>Data dibatasi periode penelitian {{ $metadata['study_starts_on'] }} sampai {{ $metadata['study_ends_on'] }} (WIB).</p>@endif
        <p>{{ $summary['respondents'] }} mahasiswa unik dari {{ $summary['responses'] }} pengisian selesai yang memenuhi syarat. {{ $metadata['scope'] }}</p>
        <table style="margin-bottom:16px"><thead><tr><th>Gejala</th><th>Kategori</th><th>Mahasiswa</th><th>Persentase responden</th></tr></thead><tbody>
            @foreach (['stress' => 'Stres', 'anxiety' => 'Kecemasan', 'depression' => 'Depresi'] as $key => $label)
                @foreach (['normal', 'ringan', 'sedang', 'berat'] as $severity)
                    @php $count = $summary['distribution'][$key][$severity] ?? 0; @endphp
                    <tr><td>{{ $label }}</td><td>{{ ucfirst($severity) }}</td><td>{{ $count }}</td><td>{{ $summary['respondents'] ? number_format($count / $summary['respondents'] * 100, 1, ',', '.') : '0,0' }}%</td></tr>
                @endforeach
            @endforeach
        </tbody></table>
    @endisset
    <table>
        <thead>
            <tr><th>{{ $anonymized ? 'Kode Responden' : 'NIM' }}</th><th>Nama</th><th>Semester</th><th>Tanggal</th><th>Stres</th><th>Kecemasan</th><th>Depresi</th><th>Tingkat Tertinggi</th></tr>
        </thead>
        <tbody>
            @foreach ($assessments as $a)
                @php $r = $a->result; @endphp
                <tr>
                    <td>{{ $anonymized ? \App\Exports\AssessmentReportExport::respondentCode($a->user_id) : ($a->user?->nim ?? '-') }}</td>
                    <td>{{ $anonymized ? 'Dirahasiakan' : ($a->user?->name ?? 'Akun tidak tersedia') }}</td>
                    <td>{{ $a->research_semester ?? '—' }}</td>
                    <td>{{ $a->completed_at?->copy()->timezone(config('app.display_timezone'))->format('d/m/Y') }}</td>
                    <td>{{ $r?->stress_score ?? '-' }} ({{ ucfirst($r?->stress_severity ?? '-') }})</td>
                    <td>{{ $r?->anxiety_score ?? '-' }} ({{ ucfirst($r?->anxiety_severity ?? '-') }})</td>
                    <td>{{ $r?->depression_score ?? '-' }} ({{ ucfirst($r?->depression_severity ?? '-') }})</td>
                    <td>{{ ucfirst($r?->highest_severity ?? '-') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
