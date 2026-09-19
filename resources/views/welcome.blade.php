<!DOCTYPE html>
<html lang="id">
<head>
    <x-head title="Beranda" />
</head>
<body class="mk-page-shell mk-welcome-page">
    <a class="mk-skip-link" href="#main-content">Lewati navigasi</a>
    <nav class="navbar sticky-top mk-navbar mk-main-navbar border-bottom">
        <div class="container mk-navbar-inner">
            <x-brand />
            <div class="d-flex align-items-center gap-2 ms-auto">
                <div class="d-none d-lg-flex align-items-center gap-2 mk-desktop-nav">
                    <nav class="d-flex align-items-center mk-desktop-menu" aria-label="Navigasi utama">
                        <a class="nav-link active" href="{{ route('welcome') }}">Beranda</a>
                        <a class="nav-link" href="#cara-kerja">Cara Kerja</a>
                        <a class="nav-link" href="{{ route('articles.index') }}">Artikel</a>
                    </nav>
                    <x-theme-toggle />
                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="btn btn-sm btn-mk-primary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-secondary">Masuk</a>
                        <a href="{{ route('register') }}" class="btn btn-sm btn-mk-primary">Daftar</a>
                    @endauth
                </div>
                <div class="d-lg-none"><x-theme-toggle /></div>
                <button class="btn btn-sm btn-outline-secondary d-lg-none mk-menu-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#publicMobileNavigation" aria-controls="publicMobileNavigation" aria-label="Buka navigasi">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-end mk-mobile-navigation" tabindex="-1" id="publicMobileNavigation" aria-labelledby="publicMobileNavigationLabel">
        <div class="offcanvas-header border-bottom">
            <div id="publicMobileNavigationLabel"><x-brand /></div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup navigasi"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <p class="mk-menu-label">Navigasi</p>
            <nav class="nav nav-pills flex-column gap-1" aria-label="Navigasi utama">
                <a class="nav-link active" href="{{ route('welcome') }}"><i class="bi bi-house"></i><span>Beranda</span></a>
                <a class="nav-link" href="#cara-kerja" data-bs-dismiss="offcanvas"><i class="bi bi-signpost-2"></i><span>Cara Kerja</span></a>
                <a class="nav-link" href="{{ route('articles.index') }}"><i class="bi bi-journal-text"></i><span>Artikel Edukasi</span></a>
            </nav>
            <div class="mt-auto pt-4 d-grid gap-2">
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="btn btn-mk-primary">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-secondary">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-mk-primary">Daftar Akun</a>
                @endauth
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="container mt-3">
            <x-alert type="success" title="Proses berhasil" :dismissible="true">{{ session('status') }}</x-alert>
        </div>
    @endif

    <main id="main-content" tabindex="-1" class="flex-grow-1">
        <section class="container mk-hero-section">
            <div class="row align-items-center g-4 g-xl-5">
                <div class="col-lg-6">
                    <div class="mk-hero-copy">
                        <p class="small fw-bold text-uppercase mk-primary-text mk-hero-kicker mb-3">Skrining kesehatan mental mahasiswa</p>
                        <h1 class="mk-hero-title fw-bold mb-3 mb-md-4">Kenali kondisi mental Anda melalui <span class="mk-hero-emphasis">skrining awal</span> yang terarah.</h1>
                        <p class="mk-hero-description text-muted mb-4">
                            @if (\App\Support\ResearchStudy::isResearch())
                                Ikuti penelitian tentang gejala stres, kecemasan, dan depresi pada mahasiswa TI UNPAM semester 7–8 melalui 21 pertanyaan DASS-21. Hasil individual ditinjau oleh peneliti berwenang.
                            @else
                            Jawab 21 pertanyaan DASS-21 untuk memperoleh gambaran tingkat Stres, Kecemasan, dan Depresi,
                            disertai tingkat keyakinan sistem berdasarkan metode Certainty Factor.
                            @endif
                        </p>
                        <div class="d-grid d-sm-flex gap-2 gap-sm-3 mk-hero-actions">
                            <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('assessment.create')) : route('register') }}" class="btn btn-lg btn-mk-primary">
                                <i class="bi bi-clipboard2-pulse me-1"></i> Mulai Skrining
                            </a>
                            <a href="#cara-kerja" class="btn btn-lg btn-outline-secondary">Pelajari Sistem <i class="bi bi-arrow-down-short ms-1" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mk-hero-visual">
                        <div class="mk-hero-motion" id="hero-motion" aria-hidden="true">
                            <span class="mk-hero-orbit mk-hero-orbit-outer"></span>
                            <span class="mk-hero-orbit mk-hero-orbit-inner"></span>
                            <span class="mk-hero-bubble mk-hero-bubble-one"><i class="bi bi-stars"></i></span>
                            <span class="mk-hero-bubble mk-hero-bubble-two"><i class="bi bi-chat-heart"></i></span>
                            <span class="mk-hero-bubble mk-hero-bubble-three"></span>
                            <span class="mk-hero-center"><i class="bi bi-heart-pulse"></i></span>
                        </div>
                        <button type="button" class="mk-motion-toggle" data-motion-toggle aria-controls="hero-motion" aria-label="Jeda animasi" aria-pressed="false" title="Jeda animasi" hidden>
                            <i class="bi bi-pause" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section id="cara-kerja" class="border-top bg-surface">
            <div class="container mk-section-space mk-explainer-section">
                <div class="mk-section-heading text-center">
                    <p class="mk-section-eyebrow">Mengenal sistem</p>
                    <h2 class="h3 fw-bold">Informasi MyKonselor</h2>
                    <p class="text-muted mb-0">Pilih topik untuk membaca penjelasan lengkap.</p>
                </div>

                <div class="mk-explainer-list">
                    <details class="mk-explainer-item" name="informasi-mykonselor" data-scroll-reveal>
                        <summary>
                            <span class="mk-explainer-icon"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span>
                            <span class="mk-explainer-label">
                                <span class="mk-explainer-title">MyKonselor dan Konsep Skrining Awal</span>
                                <span class="mk-explainer-subtitle">Memahami fungsi sistem serta batas hasil yang diberikan.</span>
                            </span>
                            <span class="mk-explainer-toggle" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </summary>
                        <div class="mk-explainer-panel">
                            <div class="mk-explainer-content">
                                <p>
                                    MyKonselor adalah sistem skrining awal berbasis web yang membantu mahasiswa mengenali
                                    gambaran kondisi kesehatan mental secara lebih terarah. Skrining awal merupakan langkah
                                    pengenalan kondisi, bukan pemeriksaan untuk menetapkan diagnosis medis.
                                </p>
                                <p class="mb-0">
                                    Sistem menghitung DASS-21 untuk menggambarkan tingkat gejala stres, kecemasan, dan
                                    depresi, serta Certainty Factor untuk menunjukkan tingkat keyakinan sistem
                                    berdasarkan jawaban dan aturan yang digunakan. Kedua perhitungan disimpan secara terpisah
                                    dan tidak menggantikan konsultasi dengan psikolog atau tenaga kesehatan profesional.
                                    @if (\App\Support\ResearchStudy::isResearch()) Skor dan interpretasi individual tersedia bagi peneliti/admin berwenang; peserta menerima tanda pengisian selesai. @endif
                                </p>
                            </div>
                        </div>
                    </details>

                    <details class="mk-explainer-item" name="informasi-mykonselor" data-scroll-reveal style="--mk-reveal-delay: 70ms">
                        <summary>
                            <span class="mk-explainer-icon"><i class="bi bi-signpost-2" aria-hidden="true"></i></span>
                            <span class="mk-explainer-label">
                                <span class="mk-explainer-title">Cara Kerja Sistem</span>
                                <span class="mk-explainer-subtitle">Alur dari persiapan, pengisian, hingga tindak lanjut.</span>
                            </span>
                            <span class="mk-explainer-toggle" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </summary>
                        <div class="mk-explainer-panel">
                            <div class="mk-explainer-content">
                                <p>
                                    <strong>1. Menjawab pertanyaan.</strong> Mahasiswa menjawab 21 pertanyaan DASS-21
                                    berdasarkan kondisi yang dirasakan dalam satu minggu terakhir. Proses pengisian
                                    membutuhkan waktu sekitar lima menit.
                                </p>
                                <p>
                                    <strong>2. Sistem mengolah jawaban.</strong> Setelah jawaban dikirim, MyKonselor
                                    menghitung skor DASS-21 dan tingkat keyakinan Certainty Factor melalui dua proses yang
                                    terpisah. Pengguna cukup menjawab sesuai pengalaman selama satu minggu terakhir.
                                </p>
                                <p class="mb-0">
                                    @if (\App\Support\ResearchStudy::isResearch())
                                        <strong>3. Pengisian selesai.</strong> Peserta menerima tanda selesai dan tetap dapat mengakses bantuan profesional. Peneliti meninjau hasil serta rekap tingkat gejala pada responden; hasil ini tidak menetapkan diagnosis.
                                    @else
                                    <strong>3. Meninjau hasil.</strong> Hasil disajikan dengan kategori, penjelasan,
                                    riwayat skrining, serta jalur bantuan yang tersedia agar pengguna dapat menentukan
                                    langkah selanjutnya dengan lebih terinformasi.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </details>

                    <details class="mk-explainer-item" name="informasi-mykonselor" data-scroll-reveal style="--mk-reveal-delay: 140ms">
                        <summary>
                            <span class="mk-explainer-icon"><i class="bi bi-bullseye" aria-hidden="true"></i></span>
                            <span class="mk-explainer-label">
                                <span class="mk-explainer-title">Tujuan MyKonselor</span>
                                <span class="mk-explainer-subtitle">Alasan sistem dibuat untuk mendukung mahasiswa.</span>
                            </span>
                            <span class="mk-explainer-toggle" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </summary>
                        <div class="mk-explainer-panel">
                            <div class="mk-explainer-content">
                                <p>
                                    MyKonselor dikembangkan sebagai bagian dari penelitian akademik mengenai skrining awal kesehatan mental mahasiswa. Sistem menyimpan jawaban secara terstruktur dan membantu peneliti melihat gambaran tingkat gejala pada responden. Riwayat pengisian dan informasi bantuan tetap tersedia bagi peserta.
                                </p>
                                <p class="mb-0">
                                    Antarmuka dapat digunakan melalui perangkat seluler, tablet, maupun desktop. Riwayat
                                    hanya dapat diakses oleh pemilik akun dan admin yang berwenang. Tujuan akhirnya adalah
                                    membantu pengguna mengenali kapan perlu mencari dukungan lebih lanjut, bukan menggantikan
                                    peran tenaga kesehatan profesional.
                                </p>
                            </div>
                        </div>
                    </details>
                </div>
            </div>
        </section>

        @if ($latestArticles->isNotEmpty())
            <section class="container mk-section-space" id="artikel">
                <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
                    <div>
                        <p class="mk-section-eyebrow">Baca seperlunya</p>
                        <h2 class="h3 fw-bold mb-0">Artikel Edukasi Terbaru</h2>
                    </div>
                    <a class="mk-inline-link" href="{{ route('articles.index') }}">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="row g-4">
                    @foreach ($latestArticles as $article)
                        <div class="col-md-4">
                            <a href="{{ route('articles.show', $article) }}" class="card h-100 text-decoration-none mk-article-card">
                                <div class="card-body mk-card-body">
                                    <span class="badge bg-surface text-muted border mb-3">{{ $article->kategori }}</span>
                                    <div class="mk-article-heading">
                                        <h3 class="h6 fw-bold mb-0" style="color:var(--mk-text)">{{ $article->judul }}</h3>
                                        <span class="mk-article-arrow" aria-hidden="true"><i class="bi bi-arrow-up-right"></i></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="container pb-4 pb-md-5">
            <div class="alert alert-secondary d-flex gap-3 mb-4 mk-disclaimer">
                <i class="bi bi-info-circle fs-5"></i>
                <div><strong>Penting:</strong> MyKonselor merupakan alat skrining awal. Hasil yang diberikan bukan diagnosis medis dan tidak menggantikan pemeriksaan, konsultasi, atau penanganan oleh psikolog maupun tenaga kesehatan profesional.</div>
            </div>
            <div class="card text-center bg-surface mk-cta-card">
                <div class="card-body mk-cta-card-body">
                    <h2 class="h3 fw-bold mb-2">Siap memulai skrining awal?</h2>
                    <p class="text-muted mb-3">Luangkan sekitar lima menit dan jawab sesuai kondisi Anda.</p>
                    <a href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('admin.dashboard') : route('assessment.create')) : route('register') }}" class="btn btn-mk-primary">Mulai Skrining</a>
                </div>
            </div>
        </section>
    </main>

    <x-footer />
</body>
</html>
