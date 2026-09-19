<x-layouts.admin title="Artikel">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <h1 class="h3 fw-bold mb-0">Manajemen Artikel</h1>
        <a href="{{ route('admin.articles.create') }}" class="btn btn-mk-primary mk-mobile-full-button">
            <i class="bi bi-plus-lg"></i> Artikel Baru
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-surface">
                    <tr><th>Judul</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($articles as $article)
                        <tr>
                            <td>{{ $article->judul }}</td>
                            <td class="text-muted">{{ $article->kategori }}</td>
                            <td>
                                <span class="badge {{ $article->status === 'published' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ ucfirst($article->status) }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('admin.articles.edit', $article) }}" class="btn btn-sm btn-link p-0 me-2" style="color:var(--mk-primary)">Edit</a>
                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="d-inline"
                                      data-confirm="Artikel yang dihapus tidak lagi tersedia untuk pengguna."
                                      data-confirm-title="Hapus artikel ini?" data-confirm-action="Hapus Artikel" data-confirm-tone="danger">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-link p-0 text-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Data belum tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $articles->links() }}</div>
</x-layouts.admin>
