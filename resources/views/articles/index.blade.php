<x-layouts.app title="Artikel">
    <h1 class="h3 fw-bold mb-2">Artikel Edukasi</h1>
    <p class="text-muted mb-4">Bacaan seputar kesehatan mental untuk mahasiswa.</p>

    <form method="GET" class="row g-2 mb-4 mk-filter-form">
        <div class="col-12 col-sm-auto flex-sm-grow-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari artikel..." class="form-control form-control-sm">
        </div>
        <div class="col-8 col-sm-auto">
            <select name="kategori" onchange="this.form.submit()" class="form-select form-select-sm">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $kategori)
                    <option value="{{ $kategori }}" @selected(request('kategori') === $kategori)>{{ $kategori }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4 col-sm-auto">
            <button type="submit" class="btn btn-sm btn-mk-primary w-100">Cari</button>
        </div>
    </form>

    <div class="row g-4">
        @forelse ($articles as $article)
            <div class="col-md-6 col-lg-4">
                <a href="{{ route('articles.show', $article) }}" class="card h-100 text-decoration-none">
                    <div class="card-body">
                        <span class="badge mb-3" style="background:var(--mk-primary-soft); color:var(--mk-text)">{{ $article->kategori }}</span>
                        <p class="fw-semibold mb-2" style="color:var(--mk-text)">{{ $article->judul }}</p>
                        <p class="small text-muted">{{ Str::limit($article->konten, 100) }}</p>
                        <p class="small text-muted mb-0">{{ $article->published_at?->translatedFormat('d F Y') }}</p>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12"><div class="card text-center"><div class="card-body py-5"><span class="mk-icon-tile mb-3"><i class="bi bi-journal-x"></i></span><p class="text-muted mb-0">Belum ada artikel tersedia.</p></div></div></div>
        @endforelse
    </div>

    <div class="mt-4">{{ $articles->links() }}</div>
</x-layouts.app>
