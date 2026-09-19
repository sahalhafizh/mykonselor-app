@props(['title'])
<!DOCTYPE html>
<html lang="id">
<head>
    <x-head :title="$title" />
</head>
<body class="mk-page-shell bg-surface mk-auth-page">
    <a class="mk-skip-link" href="#main-content">Lewati navigasi</a>
    <nav class="navbar border-bottom">
        <div class="container">
            <x-brand />
            <x-theme-toggle />
        </div>
    </nav>

    <main id="main-content" tabindex="-1" class="container flex-grow-1 d-flex align-items-center justify-content-center mk-auth-content">
        {{ $slot }}
    </main>

    <x-footer />
</body>
</html>
