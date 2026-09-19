<x-layouts.app title="Rujukan">
    <div class="mx-auto" style="max-width:58rem">
        <div class="mb-4"><h1 class="h3 fw-bold mb-1">Rujukan dan Bantuan Profesional</h1><p class="text-muted mb-0">Pilih jalur bantuan yang sesuai atau kirim pengajuan agar dapat ditindaklanjuti oleh admin.</p></div>

        @php
            $topKey = collect($result->clusters())
                ->sortByDesc(fn ($cluster) => match ($cluster['severity']) { 'berat' => 3, 'sedang' => 2, 'ringan' => 1, default => 0 })
                ->keys()
                ->first();
        @endphp

        <div class="card mb-4 {{ in_array($result->highest_severity, ['sedang', 'berat']) ? 'border-warning' : '' }}">
            <div class="card-body p-4">
                <span class="badge text-bg-{{ \App\Models\AssessmentResult::severityBadgeColor($result->highest_severity) }} mb-3">Tingkat keparahan tertinggi: {{ ucfirst($result->highest_severity) }}</span>
                <p class="mb-0">{{ $diseases[$topKey]?->panduanUntuk($result->highest_severity) ?? 'Pertimbangkan untuk memperoleh pendampingan dari tenaga profesional melalui layanan yang tersedia.' }}</p>
            </div>
        </div>

        <h2 class="h5 fw-bold mb-3">Pilihan Layanan</h2>
        <div class="row g-3 mb-4">
            @foreach ($referralServices as $service)
                <div class="col-md-6">
                    <div class="mk-referral-service d-flex flex-column">
                        <div class="d-flex align-items-start gap-3 mb-2"><span class="mk-icon-tile flex-shrink-0"><i class="bi {{ $service['icon'] }}"></i></span><h3 class="h6 fw-bold mb-0 mt-2">{{ $service['name'] }}</h3></div>
                        <p class="small text-muted">{{ $service['description'] }}</p>
                        <div class="mt-auto d-flex flex-column align-items-start gap-2">
                            @if (! empty($service['whatsapp_url']) && ! empty($service['whatsapp_number']))
                                <a href="{{ $service['whatsapp_url'] }}" target="_blank" rel="noopener noreferrer"
                                   aria-label="Hubungi {{ $service['name'] }} melalui WhatsApp di nomor {{ $service['whatsapp_number'] }} (tab baru)"
                                   class="btn btn-sm btn-mk-primary mk-mobile-full-button">
                                    <i class="bi bi-whatsapp me-1" aria-hidden="true"></i> WhatsApp {{ $service['whatsapp_number'] }}
                                </a>
                            @endif
                            @if ($service['contact_url'] && $service['contact_label'])
                                <a href="{{ $service['contact_url'] }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary mk-mobile-full-button">
                                    @if (str_contains($service['contact_url'], 'wa.me'))<i class="bi bi-whatsapp me-1" aria-hidden="true"></i>@else<i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>@endif
                                    {{ $service['contact_label'] }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card"><div class="card-body p-4">
            <h2 class="h5 fw-bold mb-1">Ajukan Kebutuhan Konseling</h2>
            <p class="small text-muted">Pengajuan ditinjau oleh admin dan bukan layanan darurat.</p>
            @if ($existingRequest)
                <div class="alert alert-info mt-3 mb-0">
                    <div class="d-flex flex-wrap justify-content-between gap-2"><span>Pengajuan Anda sudah tercatat.</span><span class="badge {{ $existingRequest->statusBadgeClass() }}">{{ $existingRequest->statusLabel() }}</span></div>
                    <small class="d-block mt-2">Diajukan {{ $existingRequest->created_at->copy()->timezone(config('app.display_timezone'))->translatedFormat('d F Y, H:i') }} WIB</small>
                </div>
            @else
                <p class="small text-muted mb-3">Admin akan meninjau catatan Anda dan membantu mengarahkan kebutuhan rujukan sesuai informasi yang tersedia.</p>
                <form method="POST" action="{{ route('assessment.referral.store', $assessment) }}">
                    @csrf
                    <label for="catatan" class="form-label">Catatan Tambahan <span class="text-muted">(opsional)</span></label>
                    <textarea id="catatan" name="catatan" rows="4" maxlength="1000" placeholder="Jelaskan kebutuhan yang ingin Anda sampaikan." class="form-control mb-3">{{ old('catatan') }}</textarea>
                    @error('catatan')<div class="text-danger small mb-3">{{ $message }}</div>@enderror
                    <button type="submit" class="btn btn-mk-primary mk-mobile-full-button"><i class="bi bi-send me-1"></i> Kirim Pengajuan</button>
                </form>
            @endif
        </div></div>
    </div>
</x-layouts.app>
