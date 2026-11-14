<?php

namespace App\Web\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tag;
use Illuminate\Contracts\View\View;

class ArticleController
{
    // Danh sách tất cả bài viết đang hiển thị
    public function index(): View
    {
        return view('web.articles.index', [
            'category' => null,
            'categories' => $this->categories(),
            'children' => collect(),
            'articles' => $this->listing(Article::public()),
        ]);
    }

    // Bài viết theo danh mục
    public function category(string $slug): View
    {
        $category = ArticleCategory::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('web.articles.index', [
            'category' => $category,
            'categories' => $this->categories(),
            'children' => $category->children()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']),
            // Bài của danh mục này và mọi danh mục con
            'articles' => $this->listing(Article::public()->inCategories([$category->id, ...$category->descendantIds()])),
        ]);
    }

    // Bài viết theo thẻ
    public function tag(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('web.articles.index', [
            'category' => null,
            'tag' => $tag,
            'categories' => $this->categories(),
            'children' => collect(),
            'articles' => $this->listing(Article::public()->withTag($tag->id)),
        ]);
    }

    // Chi tiết bài viết (chỉ bài đang hiển thị; bài ẩn hoặc chưa đến giờ đăng trả về 404)
    public function show(string $slug): View
    {
        $article = Article::public()->with([
            'category', 'image', 'publisher:id,name',
            'tags' => fn ($query) => $query->where('is_active', true)->orderBy('name'),
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Article::public()
            ->select(['id', 'category_id', 'title', 'slug', 'image_id', 'published_at'])
            ->with('image')
            ->where('category_id', $article->category_id)
            ->whereKeyNot($article->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        return view('web.articles.show', compact('article', 'related'));
    }

    private function listing($query)
    {
        // Không tải cột content (rất dài) cho trang danh sách; simplePaginate bỏ truy vấn COUNT
        return $query
            ->select(['id', 'category_id', 'title', 'slug', 'excerpt', 'image_id', 'published_at'])
            ->with(['category:id,name,slug', 'image'])
            ->latest('published_at')
            ->latest('id')
            ->simplePaginate(9);
    }

    private function categories()
    {
        return ArticleCategory::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);
    }
}
