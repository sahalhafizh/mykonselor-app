@props(['title'])
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="color-scheme" content="light dark">
<title>MyKonselor - {{ $title }}</title>
@if (file_exists(public_path(config('branding.favicon_path'))))
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset(config('branding.favicon_path')) }}">
@endif
<script nonce="{{ Illuminate\Support\Facades\Vite::cspNonce() }}">
    (() => {
        let saved = 'light';
        try { saved = localStorage.getItem('mk-theme'); } catch (_) { /* Storage may be unavailable. */ }
        const theme = saved === 'dark' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-theme', theme);
        document.documentElement.setAttribute('data-bs-theme', theme);
        try {
            if (localStorage.getItem('mk-reduce-motion') === 'true') {
                document.documentElement.setAttribute('data-motion', 'reduced');
            }
        } catch (_) { /* The system preference still applies when storage is unavailable. */ }
    })();
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
