<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\ArticleRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tag;
use App\Support\HtmlSanitizer;
use App\Support\PageCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends AdminController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:articles.view', only: ['index']),
            new Middleware('can:articles.create', only: ['create', 'store']),
            new Middleware('can:articles.update', only: ['edit', 'update', 'quick']),
            new Middleware('can:articles.delete', only: ['destroy']),
        ];
    }

    /** Trường được sửa nhanh ngay trên trang danh sách => luật kiểm tra (xem AdminController::quickUpdate). */
    protected function quickRules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function quick(Request $request, Article $article): RedirectResponse
    {
        return $this->quickUpdate($request, $article);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category_id');

        $articles = Article::query()
            ->select(['id', 'category_id', 'public_by', 'title', 'slug', 'image_id', 'is_active', 'published_at'])
            ->with(['image', 'category:id,name', 'publisher:id,name'])
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($categoryId, fn ($query) => $query->inCategories([(int) $categoryId]))
            ->latest('published_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Article $article) => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'category' => $article->category?->name,
                'publisher' => $article->publisher?->name,
                'is_active' => $article->is_active,
                'published_at' => $article->published_at?->format('d/m/Y H:i'),
                'image_url' => $article->image ? ($article->image->variantUrl('thumb_400') ?? $article->image->url) : null,
                // Nút "Xem" luôn hiện: bài đang bật thì mở ở tab mới, bài đang ẩn thì xám kèm lý do
                'public_url' => $article->url,
                'public_issue' => $article->publicIssue(),
            ]);

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'categories' => $this->categories(),
            'filters' => ['search' => $search, 'category_id' => $categoryId],
            'title' => 'Bài viết',
            'breadcrumbs' => [['label' => 'Bài viết'], ['label' => 'Danh sách bài viết']],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Articles/Create', [
            'categories' => $this->categories(),
            'tagOptions' => $this->tagOptions(),
            'title' => 'Thêm bài viết',
            'breadcrumbs' => [
                ['label' => 'Bài viết'],
                ['label' => 'Danh sách bài viết', 'href' => '/admin/articles'],
                ['label' => 'Thêm mới'],
            ],
        ]);
    }

    public function store(ArticleRequest $request): RedirectResponse
    {
        $data = $this->sanitized($request->validated());
        $data['published_at'] = now(); // ngày đăng = ngày tạo, không hẹn giờ

        $extra = Arr::pull($data, 'extra_category_ids') ?? [];
        $tags = Arr::pull($data, 'tags') ?? [];

        DB::transaction(function () use ($data, $extra, $tags) {
            $article = Article::create($data);
            $this->syncCategories($article, $extra);
            $this->syncTags($article, $tags);
        });

        return redirect()->route('admin.articles.index')
            ->with('success', 'Đã thêm bài viết.');
    }

    public function edit(Article $article): Response
    {
        return Inertia::render('Articles/Edit', [
            'article' => [
                ...$article->only([
                    'id', 'category_id', 'title', 'slug', 'excerpt', 'content', 'image_id',
                    'meta_title', 'meta_description', 'meta_key', 'no_index', 'no_follow', 'is_active',
                ]),
                'image_url' => $article->image_url,
                // Danh mục phụ = mọi danh mục của bài trừ danh mục chính
                'extra_category_ids' => $article->categories()->pluck('article_categories.id')
                    ->map(fn ($id) => (int) $id)
                    ->reject(fn ($id) => $id === (int) $article->category_id)
                    ->values()->all(),
                'tags' => $article->tags()->orderBy('name')->pluck('name')->all(),
            ],
            'categories' => $this->categories(),
            'tagOptions' => $this->tagOptions(),
            'title' => 'Sửa bài viết',
            'breadcrumbs' => [
                ['label' => 'Bài viết'],
                ['label' => 'Danh sách bài viết', 'href' => '/admin/articles'],
                ['label' => 'Sửa'],
            ],
        ]);
    }

    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $data = $this->sanitized($request->validated());
        $extra = Arr::pull($data, 'extra_category_ids') ?? [];
        $tags = Arr::pull($data, 'tags') ?? [];

        DB::transaction(function () use ($article, $data, $extra, $tags) {
            $article->update($data);
            $this->syncCategories($article, $extra);
            $this->syncTags($article, $tags);
        });

        return redirect()->route('admin.articles.index')
            ->with('success', 'Đã cập nhật bài viết.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.articles.index')
            ->with('success', 'Đã xóa bài viết.');
    }

    // Danh sách danh mục để chọn, tên kèm đường dẫn cha
    private function categories(): array
    {
        return ArticleCategory::pathOptions();
    }

    // Bảng trung gian luôn chứa danh mục chính + danh mục phụ đã chọn + mọi danh mục cha của chúng
    private function syncCategories(Article $article, array $extraIds): void
    {
        $selected = collect($extraIds)->push($article->category_id)->map(fn ($id) => (int) $id)->unique();

        $ids = $selected
            ->flatMap(fn (int $id) => [$id, ...ArticleCategory::ancestorIds($id)])
            ->unique()
            ->values()
            ->all();

        $article->categories()->sync($ids);

        // Bảng trung gian vừa đổi sau khi bài được lưu, nên làm mới cache trang web một lần nữa
        PageCache::flush();
    }

    // Gợi ý khi gõ thẻ trong form bài viết
    private function tagOptions(): array
    {
        return Tag::orderBy('name')->limit(500)->pluck('name')->all();
    }

    // Gắn thẻ cho bài theo tên: thẻ đã có thì dùng lại, chưa có thì tạo mới nếu người dùng có quyền tags.create
    private function syncTags(Article $article, array $names): void
    {
        $names = collect($names)
            ->map(fn ($name) => trim((string) preg_replace('/\s+/u', ' ', (string) $name)))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->take(20)
            ->values();

        $existing = Tag::whereIn('name', $names)->get()->keyBy(fn (Tag $tag) => mb_strtolower($tag->name));
        $canCreate = Gate::allows('tags.create');
        $ids = [];

        foreach ($names as $name) {
            $tag = $existing->get(mb_strtolower($name));

            if (! $tag && $canCreate) {
                $tag = Tag::create(['name' => $name, 'is_active' => true]);
            }

            if ($tag) {
                $ids[] = $tag->id;
            }
        }

        $article->tags()->sync($ids);
        PageCache::flush();
    }

    // Nội dung từ trình soạn thảo là HTML: lọc bỏ thẻ/thuộc tính nguy hiểm trước khi lưu
    private function sanitized(array $data): array
    {
        $data['content'] = HtmlSanitizer::clean($data['content'] ?? null);

        return $data;
    }
}
