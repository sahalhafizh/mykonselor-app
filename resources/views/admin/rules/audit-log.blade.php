<x-layouts.admin title="Audit Log">
    <a href="{{ route('admin.rules.edit', $disease) }}" class="text-muted small mb-3 d-inline-block">&larr; Kembali</a>
    <h1 class="h3 fw-bold mb-4">Riwayat Perubahan: {{ $disease->nama }}</h1>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-surface">
                    <tr><th>Tanggal</th><th>Gejala / Tindakan</th><th>Diubah Oleh</th><th>MB (lama &rarr; baru)</th><th>MD (lama &rarr; baru)</th></tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-muted">{{ $log->created_at->copy()->timezone(config('app.display_timezone'))->format('d/m/Y H:i') }} WIB</td>
                            <td>{{ $log->symptom_code }} / {{ ['created' => 'Ditambahkan', 'updated' => 'Diubah', 'deleted' => 'Dihapus'][$log->action] }}</td>
                            <td class="text-muted">{{ $log->actor?->name ?? '-' }}</td>
                            <td>{{ $log->before_values['mb'] ?? '-' }} &rarr; {{ $log->after_values['mb'] ?? '-' }}</td>
                            <td>{{ $log->before_values['md'] ?? '-' }} &rarr; {{ $log->after_values['md'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada perubahan tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $logs->links() }}</div>
</x-layouts.admin>
