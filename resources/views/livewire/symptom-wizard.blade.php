<div class="mx-auto" style="max-width:42rem">
    @if ($showIntroduction)
        <div class="card shadow-sm mk-fade-in">
            <div class="card-body p-4 p-md-5">
                <span class="mk-icon-tile mb-3"><i class="bi bi-clipboard2-check"></i></span>
                <h2 class="h4 fw-bold">Sebelum Memulai</h2>
                <p class="text-muted">Skrining berisi 21 pertanyaan dan memerlukan sekitar lima menit. Pilih jawaban yang paling menggambarkan frekuensi kondisi Anda selama satu minggu terakhir.</p>
                <ul class="text-muted small ps-3">
                    <li class="mb-2">Tidak ada jawaban benar atau salah.</li>
                    <li class="mb-2">Jawaban tersimpan pada setiap langkah sehingga dapat dilanjutkan kembali.</li>
                    <li>Hasil merupakan skrining awal, bukan diagnosis medis.</li>
                    @if (\App\Support\ResearchStudy::isResearch())<li class="mt-2">Setelah pengisian, Anda menerima tanda selesai. Skor individual ditinjau oleh peneliti berwenang.</li>@endif
                </ul>
                <button wire:click="start" wire:loading.attr="disabled" type="button" class="btn btn-mk-primary mt-2">Mulai Menjawab <i class="bi bi-arrow-right ms-1"></i></button>
            </div>
        </div>
    @else
        <div class="mb-4">
            <div class="d-flex justify-content-between small text-muted mb-2"><span>Pertanyaan {{ $this->currentIndex + 1 }} dari {{ $this->totalSteps }}</span><span>{{ $this->progressPercent }}%</span></div>
            <div class="progress" role="progressbar" aria-label="Kemajuan skrining" aria-valuenow="{{ $this->progressPercent }}" aria-valuemin="0" aria-valuemax="100" style="height:.55rem">
                <div class="progress-bar" style="width:{{ $this->progressPercent }}%;background:var(--mk-primary)"></div>
            </div>
        </div>

        @if ($errorMessage)<x-alert type="danger" title="Jawaban belum tersimpan" class="mb-3">{{ $errorMessage }}</x-alert>@endif

        @if (! $showSummary && $this->currentSymptom)
            <div wire:key="step-{{ $this->currentIndex }}" class="card shadow-sm mk-fade-in">
                <div class="card-body p-4 p-md-5">
                    <h2 class="h5 fw-bold mb-2">{{ $this->currentSymptom->deskripsi }}</h2>
                    <p class="text-muted small mb-4">Seberapa sering Anda mengalami hal ini dalam satu minggu terakhir?</p>
                    <div class="row g-2 mk-answer-grid">
                        @foreach ($this->scaleOptions() as $index => $label)
                            @php $isActive = ($answers[$this->currentSymptom->id] ?? null) === $index; @endphp
                            <div class="col-6"><button wire:click="selectAnswer({{ $index }})" wire:loading.attr="disabled" type="button" aria-pressed="{{ $isActive ? 'true' : 'false' }}" class="mk-answer-card {{ $isActive ? 'active' : '' }}">{{ $label }}</button></div>
                        @endforeach
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button wire:click="back" wire:loading.attr="disabled" type="button" @disabled($this->currentIndex === 0) class="btn btn-sm btn-link text-muted p-0"><i class="bi bi-arrow-left"></i> Kembali</button>
                        <span wire:loading role="status" class="small mk-primary-text">Menyimpan...</span>
                    </div>
                </div>
            </div>
        @else
            <div class="card shadow-sm"><div class="card-body p-4 p-md-5">
                <h2 class="h5 fw-bold mb-2">Ringkasan Jawaban</h2>
                <p class="text-muted small">Anda telah menjawab {{ count($answers) }} dari {{ $this->totalSteps }} pertanyaan. Pastikan seluruh pertanyaan telah terjawab.</p>
                <div class="border-top" style="max-height:22rem;overflow-y:auto">
                    @foreach (\App\Models\Symptom::whereIn('id', $symptomIds)->orderBy('kode')->get() as $symptom)
                        <div class="mk-answer-summary-row border-bottom py-2 small"><span>{{ $symptom->deskripsi }}</span><span class="fw-semibold mk-primary-text">{{ $this->scaleOptions()[$answers[$symptom->id] ?? null] ?? 'Belum dijawab' }}</span></div>
                    @endforeach
                </div>
                <div class="d-grid d-sm-flex justify-content-sm-between align-items-sm-center gap-2 gap-sm-3 mt-4">
                    <button wire:click="back" wire:loading.attr="disabled" type="button" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Edit Jawaban</button>
                    <button wire:click="submit" wire:loading.attr="disabled" type="button" class="btn btn-mk-primary">
                        <span wire:loading.remove wire:target="submit">{{ \App\Support\ResearchStudy::isResearch() ? 'Selesaikan Pengisian' : 'Lihat Hasil Skrining' }}</span>
                        <span wire:loading wire:target="submit"><span class="spinner-border spinner-border-sm me-1"></span> Menyimpan pengisian...</span>
                    </button>
                </div>
            </div></div>
        @endif

        <p class="small text-center text-muted mt-3"><i class="bi bi-cloud-check me-1"></i> Jawaban tersimpan otomatis pada setiap langkah.</p>
    @endif
</div>
