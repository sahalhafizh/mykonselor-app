<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        return view('admin.articles.index', ['articles' => Article::latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.articles.form', ['article' => new Article]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::limit(Str::slug($data['judul']) ?: 'artikel', 180, '').'-'.strtolower((string) Str::ulid());
        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        Article::create($data);

        return redirect()->route('admin.articles.index')->with('status', 'Artikel berhasil dibuat.');
    }

    public function edit(Article $article): View
    {
        return view('admin.articles.form', compact('article'));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $data = $this->validated($request);
        if ($data['status'] === 'published' && ! $article->published_at) {
            $data['published_at'] = now();
        }

        $article->update($data);

        return redirect()->route('admin.articles.index')->with('status', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return back()->with('status', 'Artikel berhasil dihapus.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'judul' => 'required|string|max:255',
            'konten' => 'required|string|max:100000',
            'kategori' => 'nullable|string|max:100',
            'penulis' => 'nullable|string|max:100',
            'status' => 'required|in:draft,published',
            'gambar_sampul' => 'nullable|url:http,https|max:255',
        ]);
    }
}
