@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
    $config = [
        'success' => ['icon' => 'bi-check2-circle', 'title' => 'Berhasil'],
        'danger' => ['icon' => 'bi-exclamation-circle', 'title' => 'Periksa kembali'],
        'warning' => ['icon' => 'bi-exclamation-triangle', 'title' => 'Perhatian'],
        'info' => ['icon' => 'bi-info-circle', 'title' => 'Informasi'],
    ][$type] ?? ['icon' => 'bi-info-circle', 'title' => 'Informasi'];

    $resolvedTitle = $title ?: $config['title'];
@endphp

<div {{ $attributes->class([
        'alert mk-alert',
        'mk-alert-' . $type,
        'alert-dismissible fade show' => $dismissible,
    ]) }} role="{{ $type === 'danger' ? 'alert' : 'status' }}">
    <span class="mk-alert-icon" aria-hidden="true"><i class="bi {{ $config['icon'] }}"></i></span>
    <div class="mk-alert-body">
        <p class="mk-alert-title">{{ $resolvedTitle }}</p>
        <div class="mk-alert-message">{{ $slot }}</div>
    </div>
    @if ($dismissible)
        <button type="button" class="btn-close mk-alert-close" data-bs-dismiss="alert" aria-label="Tutup pesan"></button>
    @endif
</div>
