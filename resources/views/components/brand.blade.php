@props(['admin' => false])
<a {{ $attributes->class(['mk-brand text-decoration-none d-inline-flex align-items-center gap-2']) }} href="{{ route('welcome') }}">
    @if (file_exists(public_path(config('branding.logo_path'))))
        <img src="{{ asset(config('branding.logo_path')) }}" alt="Logo Universitas Pamulang" class="mk-brand-logo">
    @else
        <span class="mk-brand-mark" aria-hidden="true"><i class="bi bi-mortarboard"></i></span>
    @endif
    <span>MyKonselor</span>
    @if ($admin)
        <span class="badge text-bg-secondary">Admin</span>
    @endif
</a>
