<x-layouts.app title="Pengisian Selesai">
    <div class="card mx-auto" style="max-width:42rem"><div class="card-body p-4 p-md-5">
        <span class="mk-icon-tile mb-3"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
        <h1 class="h4 fw-bold">Terima kasih, pengisian Anda sudah selesai.</h1>
        <p class="text-muted">Jawaban telah tersimpan pada {{ $assessment->completed_at?->copy()->timezone(config('app.display_timezone'))->translatedFormat('d F Y, H:i') }} WIB.</p>
        <p>Data digunakan sesuai persetujuan penelitian. Skor dan interpretasi individual ditinjau oleh peneliti/admin berwenang. Halaman ini merupakan tanda pengisian selesai dan tidak menetapkan kondisi kesehatan Anda.</p>
        <p class="small text-muted">Jika Anda membutuhkan dukungan, Anda tetap dapat mengakses layanan bantuan profesional tanpa menunggu hasil penelitian.</p>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('assessment.referral', $assessment) }}" class="btn btn-mk-primary">Lihat Bantuan Profesional</a>
            <a href="{{ route('assessment.history') }}" class="btn btn-outline-secondary">Riwayat Pengisian</a>
        </div>
    </div></div>
</x-layouts.app>
