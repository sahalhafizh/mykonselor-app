<x-layouts.admin title="Artikel">
    <h1 class="h3 fw-bold mb-4">{{ $article->exists ? 'Edit Artikel' : 'Artikel Baru' }}</h1>

    <form method="POST" action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}" class="col-lg-8">
        @csrf
        @if ($article->exists) @method('PUT') @endif

        @if ($errors->any())
            <x-alert type="danger" title="Artikel belum disimpan" class="mb-3">
                <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </x-alert>
        @endif

        <div class="mb-3">
            <label class="form-label" for="article_judul">Judul</label>
            <input type="text" id="article_judul" name="judul" value="{{ old('judul', $article->judul) }}" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label" for="article_kategori">Kategori</label>
            <input type="text" id="article_kategori" name="kategori" value="{{ old('kategori', $article->kategori) }}" placeholder="Stres, Kecemasan, Depresi, Self-care" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label" for="article_penulis">Penulis</label>
            <input type="text" id="article_penulis" name="penulis" value="{{ old('penulis', $article->penulis) }}" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label" for="article_konten">Konten</label>
            <textarea id="article_konten" name="konten" rows="8" class="form-control">{{ old('konten', $article->konten) }}</textarea>
        </div>
        <div class="mb-4">
            <label class="form-label" for="article_status">Status</label>
            <select id="article_status" name="status" class="form-select">
                <option value="draft" @selected(old('status', $article->status) === 'draft')>Draft</option>
                <option value="published" @selected(old('status', $article->status) === 'published')>Published</option>
            </select>
        </div>

        <button type="submit" class="btn btn-mk-primary">Simpan</button>
    </form>
</x-layouts.admin>
