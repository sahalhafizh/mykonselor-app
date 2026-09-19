@props(['title' => 'Dashboard Admin'])
<!DOCTYPE html>
<html lang="id">
<head>
    <x-head :title="$title" />
    @livewireStyles
</head>
<body class="mk-admin-shell">
    <a class="mk-skip-link" href="#main-content">Lewati navigasi</a>
    @php
        $menu = [
            ['route' => 'admin.security', 'icon' => 'bi-shield-lock', 'label' => 'Keamanan Akun'],
            ['route' => 'admin.dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
            ['route' => 'admin.users.index', 'icon' => 'bi-people', 'label' => 'Pengguna'],
            ['route' => 'admin.rules.index', 'icon' => 'bi-diagram-3', 'label' => 'Aturan Sistem Pakar'],
            ['route' => 'admin.reports.index', 'icon' => 'bi-file-earmark-bar-graph', 'label' => 'Laporan'],
            ['route' => 'admin.articles.index', 'icon' => 'bi-journal-text', 'label' => 'Artikel'],
            ['route' => 'admin.referrals.index', 'icon' => 'bi-person-heart', 'label' => 'Pengajuan Rujukan'],
        ];
    @endphp

    <div class="d-flex min-vh-100">
        <aside class="mk-admin-sidebar d-none d-lg-flex flex-column sticky-top p-3">
            <x-brand :admin="true" class="mb-4" />
            <ul class="nav nav-pills flex-column gap-1">
                @foreach ($menu as $item)
                    <li class="nav-item">
                        <a href="{{ route($item['route']) }}"
                           class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'active' : '' }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                            @if ($item['route'] === 'admin.referrals.index' && $adminPendingReferralCount > 0)
                                <span class="badge text-bg-warning ms-auto">{{ $adminPendingReferralCount }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-auto">
                <a href="{{ route('welcome') }}" class="small text-muted d-block mb-2"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">Keluar</button>
                </form>
            </div>
        </aside>

        <div class="offcanvas offcanvas-start mk-admin-mobile-navigation" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
            <div class="offcanvas-header border-bottom">
                <div id="adminSidebarLabel"><x-brand :admin="true" /></div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup navigasi"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column">
                <p class="mk-menu-label">Menu Admin</p>
                <ul class="nav nav-pills flex-column gap-1">
                    @foreach ($menu as $item)
                        <li class="nav-item">
                            <a href="{{ route($item['route']) }}"
                               class="nav-link d-flex align-items-center gap-2 {{ request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])) ? 'active' : '' }}">
                                <i class="bi {{ $item['icon'] }}"></i>
                                <span>{{ $item['label'] }}</span>
                                @if ($item['route'] === 'admin.referrals.index' && $adminPendingReferralCount > 0)
                                    <span class="badge text-bg-warning ms-auto">{{ $adminPendingReferralCount }}</span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
                <form method="POST" action="{{ route('logout') }}" class="mt-auto">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100">Keluar</button>
                </form>
            </div>
        </div>

        <div class="flex-fill min-w-0">
            <nav class="navbar sticky-top mk-navbar mk-admin-topbar border-bottom">
                <div class="container-fluid px-3 px-md-4">
                    <button class="btn btn-sm btn-outline-secondary d-lg-none mk-menu-button" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Buka navigasi admin">
                        <i class="bi bi-list"></i>
                    </button>
                    <span class="fw-semibold d-lg-none mk-admin-topbar-title">Area Admin</span>
                    <div class="ms-auto"><x-theme-toggle /></div>
                </div>
            </nav>

            <main id="main-content" tabindex="-1" class="container-fluid mk-admin-content">
                @if (session('status'))
                    <x-alert type="success" title="Proses berhasil" :dismissible="true" class="mb-4">{{ session('status') }}</x-alert>
                @endif
                @if ($errors->has('admin_password'))
                    <x-alert type="danger" title="Verifikasi belum berhasil" class="mb-4">{{ $errors->first('admin_password') }}</x-alert>
                @endif
                <x-data-mode-notice />
                {{ $slot }}
            </main>
        </div>
    </div>
    <x-confirm-dialog />
    @livewireScripts
</body>
</html>
