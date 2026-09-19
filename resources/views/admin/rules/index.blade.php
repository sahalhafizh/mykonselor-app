<x-layouts.admin title="Aturan Sistem Pakar">
    <h1 class="h3 fw-bold mb-2">Manajemen Aturan & Bobot Pakar</h1>
    <p class="text-muted mb-4">Kelola relasi gejala-klaster beserta nilai Measure of Belief (MB) dan Measure of Disbelief (MD).</p>

    <div class="row g-3">
        @foreach ($diseases as $disease)
            <div class="col-md-4">
                <a href="{{ route('admin.rules.edit', $disease) }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-surface text-muted border">{{ $disease->kode }}</span>
                                <span class="text-muted small">{{ $disease->symptoms_count }} gejala</span>
                            </div>
                            <p class="fw-semibold mb-0" style="color:var(--mk-text)">{{ $disease->nama }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</x-layouts.admin>
