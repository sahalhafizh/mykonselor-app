<x-layouts.admin title="Aturan Sistem Pakar">
    <a href="{{ route('admin.rules.index') }}" class="text-muted small mb-3 d-inline-block">&larr; Kembali</a>

    <h1 class="h3 fw-bold mb-1">{{ $disease->nama }}</h1>
    <p class="text-muted mb-4">
        Kode: {{ $disease->kode }} &middot;
        <a href="{{ route('admin.rules.audit', $disease) }}" style="color:var(--mk-primary)">Lihat riwayat perubahan</a>
    </p>

    <form method="POST" action="{{ route('admin.rules.update', $disease) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-surface">
                        <tr>
                            <th>Aktif</th><th>Kode</th><th>Gejala</th>
                            <th style="width:7rem">MB</th><th style="width:7rem">MD</th><th style="width:6rem">CF Pakar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allSymptoms as $symptom)
                            @php
                                $existingRow = $existing->get($symptom->id);
                                $isSelected = (bool) $existingRow;
                                $mb = $existingRow->pivot->mb ?? 1;
                                $md = $existingRow->pivot->md ?? 0;
                            @endphp
                            <tr>
                                <td><input type="checkbox" class="form-check-input" name="symptoms[{{ $symptom->id }}][selected]" value="1" {{ $isSelected ? 'checked' : '' }}></td>
                                <td class="text-muted">{{ $symptom->kode }}</td>
                                <td>{{ $symptom->deskripsi }}</td>
                                <td><input type="number" step="1" min="0" max="1" name="symptoms[{{ $symptom->id }}][mb]" value="{{ $mb }}" class="form-control form-control-sm"></td>
                                <td><input type="number" step="1" min="0" max="1" name="symptoms[{{ $symptom->id }}][md]" value="{{ $md }}" class="form-control form-control-sm"></td>
                                <td class="fw-semibold" style="color:var(--mk-primary)">{{ number_format($mb - $md, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" class="btn mt-3 btn-mk-primary">Simpan Perubahan</button>
    </form>
</x-layouts.admin>
