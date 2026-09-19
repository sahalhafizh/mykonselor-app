<x-layouts.app :title="$article->judul">
    <div class="mx-auto" style="max-width:36rem">
        <a href="{{ route('articles.index') }}" class="small text-muted d-inline-block mb-3">&larr; Kembali</a>

        <span class="badge mb-3" style="background:var(--mk-primary-soft); color:var(--mk-text)">{{ $article->kategori }}</span>

        <h1 class="h3 fw-bold mb-2">{{ $article->judul }}</h1>
        <p class="small text-muted mb-4">{{ $article->penulis }} &middot; {{ $article->published_at?->translatedFormat('d F Y') }}</p>

        <div class="mb-5 mk-reading-body">{{ $article->konten }}</div>

        @if ($related->isNotEmpty())
            <div>
                <p class="fw-semibold mb-3">Artikel Terkait</p>
                <div class="row g-3">
                    @foreach ($related as $r)
                        <div class="col-md-4">
                            <a href="{{ route('articles.show', $r) }}" class="card text-decoration-none h-100">
                                <div class="card-body">
                                    <p class="small fw-medium mb-0" style="color:var(--mk-text)">{{ $r->judul }}</p>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
