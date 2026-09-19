@props(['title' => 'Beranda'])
<!DOCTYPE html>
<html lang="id">
<head>
    <x-head :title="$title" />
    @livewireStyles
</head>
<body class="mk-page-shell">
    <a class="mk-skip-link" href="#main-content">Lewati navigasi</a>
    <nav class="navbar sticky-top mk-navbar mk-main-navbar border-bottom">
        <div class="container mk-navbar-inner">
            <x-brand />

            <div class="d-flex align-items-center gap-2 ms-auto">
                <div class="d-none d-lg-flex align-items-center gap-2 mk-desktop-nav">
                    <nav class="d-flex align-items-center mk-desktop-menu" aria-label="Navigasi utama">
                        <a class="nav-link {{ request()->routeIs('welcome') ? 'active' : '' }}" href="{{ route('welcome') }}">Beranda</a>
                        @auth
                            <a class="nav-link {{ request()->routeIs('dashboard', 'admin.dashboard') ? 'active' : '' }}" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}">Dashboard</a>
                            @if (auth()->user()->isMahasiswa())
                                <a class="nav-link {{ request()->routeIs('assessment.create') ? 'active' : '' }}" href="{{ route('assessment.create') }}">Skrining</a>
                                <a class="nav-link {{ request()->routeIs('assessment.history', 'assessment.result', 'assessment.referral') ? 'active' : '' }}" href="{{ route('assessment.history') }}">Riwayat</a>
                            @endif
                        @endauth
                        <a class="nav-link {{ request()->routeIs('articles.*') ? 'active' : '' }}" href="{{ route('articles.index') }}">Artikel</a>
                    </nav>
                    @auth
                        @if (auth()->user()->isMahasiswa())
                            <a class="btn btn-sm btn-outline-secondary mk-profile-nav-button {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                               href="{{ route('profile.show') }}" aria-label="Buka profil" title="Profil Saya">
                                <i class="bi bi-person" aria-hidden="true"></i>
                            </a>
                        @endif
                    @endauth
                    <x-theme-toggle />
                    @auth
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-sm btn-outline-danger">Keluar</button></form>
                    @else
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('login') }}">Masuk</a>
                        <a class="btn btn-sm btn-mk-primary" href="{{ route('register') }}">Daftar</a>
                    @endauth
                </div>
                @auth
                    @if (auth()->user()->isMahasiswa())
                        <a class="btn btn-sm btn-outline-secondary d-lg-none mk-profile-nav-button {{ request()->routeIs('profile.*') ? 'active' : '' }}"
                           href="{{ route('profile.show') }}" aria-label="Buka profil" title="Profil Saya">
                            <i class="bi bi-person" aria-hidden="true"></i>
                        </a>
                    @endif
                @endauth
                <div class="d-lg-none"><x-theme-toggle /></div>
                <button class="btn btn-sm btn-outline-secondary d-lg-none mk-menu-button" type="button" data-bs-toggle="offcanvas" data-bs-target="#mkMobileNavigation" aria-controls="mkMobileNavigation" aria-label="Buka navigasi">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
            </div>
        </div>
    </nav>

    <div class="offcanvas offcanvas-end mk-mobile-navigation" tabindex="-1" id="mkMobileNavigation" aria-labelledby="mkMobileNavigationLabel">
        <div class="offcanvas-header border-bottom">
            <div id="mkMobileNavigationLabel"><x-brand /></div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup navigasi"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <p class="mk-menu-label">Navigasi</p>
            <nav class="nav nav-pills flex-column gap-1" aria-label="Navigasi utama">
                <a class="nav-link {{ request()->routeIs('welcome') ? 'active' : '' }}" href="{{ route('welcome') }}"><i class="bi bi-house"></i><span>Beranda</span></a>
                @auth
                    <a class="nav-link {{ request()->routeIs('dashboard', 'admin.dashboard') ? 'active' : '' }}" href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
                    @if (auth()->user()->isMahasiswa())
                        <a class="nav-link {{ request()->routeIs('assessment.create') ? 'active' : '' }}" href="{{ route('assessment.create') }}"><i class="bi bi-clipboard2-pulse"></i><span>Skrining</span></a>
                        <a class="nav-link {{ request()->routeIs('assessment.history', 'assessment.result', 'assessment.referral') ? 'active' : '' }}" href="{{ route('assessment.history') }}"><i class="bi bi-clock-history"></i><span>Riwayat</span></a>
                    @endif
                @endauth
                <a class="nav-link {{ request()->routeIs('articles.*') ? 'active' : '' }}" href="{{ route('articles.index') }}"><i class="bi bi-journal-text"></i><span>Artikel</span></a>
            </nav>
            <div class="mt-auto pt-4">
                @auth
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn-outline-danger w-100"><i class="bi bi-box-arrow-right me-1"></i> Keluar</button></form>
                @else
                    <div class="d-grid gap-2">
                        <a class="btn btn-outline-secondary" href="{{ route('login') }}">Masuk</a>
                        <a class="btn btn-mk-primary" href="{{ route('register') }}">Daftar Akun</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <main id="main-content" tabindex="-1" class="container mk-page-content flex-grow-1">
        @if (session('status'))
            <x-alert type="success" title="Proses berhasil" :dismissible="true" class="mb-4">{{ session('status') }}</x-alert>
        @endif

        <x-data-mode-notice />
        {{ $slot }}
    </main>

    <x-footer />
    <x-confirm-dialog />
    @livewireScripts
</body>
</html>
