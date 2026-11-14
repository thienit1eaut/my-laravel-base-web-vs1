<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\TagRequest;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends AdminController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:tags.view', only: ['index']),
            new Middleware('can:tags.create', only: ['create', 'store']),
            new Middleware('can:tags.update', only: ['edit', 'update', 'quick']),
            new Middleware('can:tags.delete', only: ['destroy']),
        ];
    }

    /** Trường được sửa nhanh ngay trên trang danh sách => luật kiểm tra (xem AdminController::quickUpdate). */
    protected function quickRules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function quick(Request $request, Tag $tag): RedirectResponse
    {
        return $this->quickUpdate($request, $tag);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $tags = Tag::query()
            ->withCount('articles')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'articles_count' => $tag->articles_count,
                'is_active' => $tag->is_active,
                'public_url' => $tag->is_active ? $tag->url : null,
            ]);

        return Inertia::render('Tags/Index', [
            'tags' => $tags,
            'filters' => ['search' => $search],
            'title' => 'Thẻ bài viết',
            'breadcrumbs' => [['label' => 'Bài viết'], ['label' => 'Thẻ bài viết']],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Tags/Create', [
            'title' => 'Thêm thẻ',
            'breadcrumbs' => [
                ['label' => 'Bài viết'],
                ['label' => 'Thẻ bài viết', 'href' => '/admin/tags'],
                ['label' => 'Thêm mới'],
            ],
        ]);
    }

    public function store(TagRequest $request): RedirectResponse
    {
        Tag::create($request->validated());

        return redirect()->route('admin.tags.index')->with('success', 'Đã thêm thẻ.');
    }

    public function edit(Tag $tag): Response
    {
        return Inertia::render('Tags/Edit', [
            'tag' => $tag->only(['id', 'name', 'slug', 'description', 'meta_title', 'meta_description', 'meta_key', 'no_index', 'no_follow', 'is_active']),
            'title' => 'Sửa thẻ',
            'breadcrumbs' => [
                ['label' => 'Bài viết'],
                ['label' => 'Thẻ bài viết', 'href' => '/admin/tags'],
                ['label' => 'Sửa'],
            ],
        ]);
    }

    public function update(TagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return redirect()->route('admin.tags.index')->with('success', 'Đã cập nhật thẻ.');
    }

    // Xóa thẻ thì các liên kết với bài viết tự mất (bài viết không bị xóa)
    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', 'Đã xóa thẻ.');
    }
}
