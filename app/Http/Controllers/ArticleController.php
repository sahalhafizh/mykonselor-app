<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['search' => ['nullable', 'string', 'max:100'], 'kategori' => ['nullable', 'string', 'max:100']]);
        $articles = Article::published()
            ->when($request->kategori, fn ($q) => $q->where('kategori', $request->kategori))
            ->when($request->search, fn ($q) => $q->where('judul', 'like', "%{$request->search}%"))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = Article::published()->distinct()->pluck('kategori');

        return view('articles.index', compact('articles', 'categories'));
    }

    public function show(Article $article): View
    {
        $isDraftVisible = $article->status === 'published' || (auth()->check() && auth()->user()->isAdmin());
        abort_unless($isDraftVisible, 404);

        $related = Article::published()
            ->where('kategori', $article->kategori)
            ->where('id', '!=', $article->id)
            ->limit(3)
            ->get();

        return view('articles.show', compact('article', 'related'));
    }
}
