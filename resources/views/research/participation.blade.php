<x-layouts.app title="Partisipasi Penelitian">
    <div class="card mx-auto" style="max-width:42rem"><div class="card-body p-4">
        <h1 class="h4 fw-bold">Partisipasi Penelitian</h1>
        @if (! \App\Support\ResearchStudy::isResearch())
            <p class="text-muted">Anda sedang menggunakan mode demo lokal. Gunakan data fiktif untuk demonstrasi.</p>
            <a class="btn btn-mk-primary" href="{{ route('assessment.create') }}">Buka Skrining Demo</a>
        @elseif (! \App\Support\ResearchStudy::isReady())
            <p class="text-muted">Pengelola sedang menyiapkan periode dan informasi penelitian. Pengisian belum dibuka.</p>
        @else
            <x-research-consent />
            @if (! \App\Support\ResearchStudy::isOpen())
                <p class="text-muted">Pengisian hanya tersedia selama periode penelitian. Data yang sudah dikirim tetap tercatat.</p>
            @elseif (\App\Support\ResearchStudy::hasCurrentConsent($participation))
                <p>Semester yang didaftarkan: <strong>{{ $participation->semester }}</strong>.</p>
                @if (\App\Support\ResearchStudy::eligible(auth()->user()))
                    <p class="text-success">Identitas dan kelayakan peserta sudah diverifikasi.</p>
                    <a class="btn btn-mk-primary" href="{{ route('assessment.create') }}">Buka Skrining</a>
                @else
                    <p class="text-muted">Persetujuan sudah tercatat. Tunggu pengelola memeriksa nama, NIM, program studi, dan semester Anda.</p>
                    @if(config('privacy.contact_email'))<a class="btn btn-outline-secondary" href="mailto:{{ config('privacy.contact_email') }}">Hubungi Pengelola</a>@endif
                @endif
            @endif
            @if (\App\Support\ResearchStudy::isOpen() && (! \App\Support\ResearchStudy::hasCurrentConsent($participation) || ! $participation?->verified_at))
                @if ($errors->any())<x-alert type="danger" title="Periksa persetujuan">{{ $errors->first() }}</x-alert>@endif
                <form method="POST" action="{{ route('research.participation.store') }}">
                    @csrf
                    <label class="form-label" for="research-semester">Semester saat mengikuti penelitian</label>
                    @if ($participation?->verified_at)
                        <input id="research-semester" class="form-control mb-3" value="{{ $participation->semester }}" readonly>
                        <input type="hidden" name="semester" value="{{ $participation->semester }}">
                    @else
                        <select id="research-semester" name="semester" class="form-select mb-3" required>
                            <option value="">Pilih semester</option>
                            @foreach ([7, 8] as $semester)<option value="{{ $semester }}" @selected((string) old('semester', $participation?->semester) === (string) $semester)>Semester {{ $semester }}</option>@endforeach
                        </select>
                    @endif
                    <div class="form-check mk-clickable-check mb-3">
                        <input id="research-consent" name="research_consent" type="checkbox" value="1" class="form-check-input" required>
                        <label class="form-check-label small" for="research-consent">Saya memenuhi kriteria peserta, telah membaca informasi penelitian, dan bersedia berpartisipasi secara sukarela.</label>
                    </div>
                    <button class="btn btn-mk-primary" type="submit">Simpan Persetujuan</button>
                    @if($participation)<p class="small text-muted mt-2 mb-0">Perbaikan semester memerlukan pemeriksaan ulang oleh pengelola.</p>@endif
                </form>
            @endif
        @endif
        <a class="d-block small mt-3" href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
    </div></div>
</x-layouts.app>
