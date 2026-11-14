<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\ArticleCategoryRequest;
use App\Models\ArticleCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class ArticleCategoryController extends AdminController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:article-categories.view', only: ['index']),
            new Middleware('can:article-categories.create', only: ['create', 'store']),
            new Middleware('can:article-categories.update', only: ['edit', 'update', 'quick']),
            new Middleware('can:article-categories.delete', only: ['destroy']),
        ];
    }

    /** Trường được sửa nhanh ngay trên trang danh sách => luật kiểm tra (xem AdminController::quickUpdate). */
    protected function quickRules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function quick(Request $request, ArticleCategory $articleCategory): RedirectResponse
    {
        return $this->quickUpdate($request, $articleCategory);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $categories = ArticleCategory::query()
            ->with(['image', 'parent:id,name'])
            ->withCount('articles')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (ArticleCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'parent' => $category->parent?->name,
                'slug' => $category->slug,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
                'articles_count' => $category->articles_count,
                'image_url' => $category->image_url,
                // Chỉ danh mục đang bật mới có trang công khai để mở
                'public_url' => $category->is_active ? $category->url : null,
            ]);

        return Inertia::render('ArticleCategories/Index', [
            'categories' => $categories,
            'filters' => ['search' => $search],
            'title' => 'Danh mục bài viết',
            'breadcrumbs' => [['label' => 'Bài viết'], ['label' => 'Danh mục bài viết']],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ArticleCategories/Create', [
            'parents' => ArticleCategory::pathOptions(),
            'title' => 'Thêm danh mục bài viết',
            'breadcrumbs' => [
                ['label' => 'Bài viết'],
                ['label' => 'Danh mục bài viết', 'href' => '/admin/article-categories'],
                ['label' => 'Thêm mới'],
            ],
        ]);
    }

    public function store(ArticleCategoryRequest $request): RedirectResponse
    {
        ArticleCategory::create($request->validated());

        return redirect()->route('admin.article-categories.index')
            ->with('success', 'Đã thêm danh mục bài viết.');
    }

    public function edit(ArticleCategory $articleCategory): Response
    {
        return Inertia::render('ArticleCategories/Edit', [
            'category' => [
                ...$articleCategory->only([
                    'id', 'parent_id', 'name', 'slug', 'description', 'image_id',
                    'meta_title', 'meta_description', 'meta_key', 'no_index', 'no_follow', 'sort_order', 'is_active',
                ]),
                'image_url' => $articleCategory->image_url,
            ],
            'parents' => ArticleCategory::pathOptions([$articleCategory->id, ...$articleCategory->descendantIds()]),
            'title' => 'Sửa danh mục bài viết',
            'breadcrumbs' => [
                ['label' => 'Bài viết'],
                ['label' => 'Danh mục bài viết', 'href' => '/admin/article-categories'],
                ['label' => 'Sửa'],
            ],
        ]);
    }

    public function update(ArticleCategoryRequest $request, ArticleCategory $articleCategory): RedirectResponse
    {
        $articleCategory->update($request->validated());

        return redirect()->route('admin.article-categories.index')
            ->with('success', 'Đã cập nhật danh mục bài viết.');
    }

    public function destroy(ArticleCategory $articleCategory): RedirectResponse
    {
        if ($articleCategory->children()->exists()) {
            return back()->with('error', 'Danh mục còn danh mục con, hãy chuyển hoặc xóa các danh mục con trước.');
        }

        // Không xóa danh mục còn bài viết để tránh mất bài hoặc link chết
        if ($articleCategory->articles()->exists()) {
            return back()->with('error', 'Danh mục còn bài viết, hãy chuyển hoặc xóa các bài viết trước.');
        }

        $articleCategory->delete();

        return redirect()->route('admin.article-categories.index')
            ->with('success', 'Đã xóa danh mục bài viết.');
    }
}
