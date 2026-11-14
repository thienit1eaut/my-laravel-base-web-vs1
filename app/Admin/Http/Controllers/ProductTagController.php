<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\ProductTagRequest;
use App\Models\ProductTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class ProductTagController extends AdminController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:product-tags.view', only: ['index']),
            new Middleware('can:product-tags.create', only: ['create', 'store']),
            new Middleware('can:product-tags.update', only: ['edit', 'update', 'quick']),
            new Middleware('can:product-tags.delete', only: ['destroy']),
        ];
    }

    /** Trường được sửa nhanh ngay trên trang danh sách => luật kiểm tra (xem AdminController::quickUpdate). */
    protected function quickRules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function quick(Request $request, ProductTag $productTag): RedirectResponse
    {
        return $this->quickUpdate($request, $productTag);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $tags = ProductTag::query()
            ->withCount('products')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (ProductTag $productTag) => [
                'id' => $productTag->id,
                'name' => $productTag->name,
                'slug' => $productTag->slug,
                'products_count' => $productTag->products_count,
                'is_active' => $productTag->is_active,
                'public_url' => $productTag->is_active ? $productTag->url : null,
            ]);

        return Inertia::render('ProductTags/Index', [
            'tags' => $tags,
            'filters' => ['search' => $search],
            'title' => 'Thẻ sản phẩm',
            'breadcrumbs' => [['label' => 'Sản phẩm'], ['label' => 'Thẻ sản phẩm']],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ProductTags/Create', [
            'title' => 'Thêm thẻ',
            'breadcrumbs' => [
                ['label' => 'Sản phẩm'],
                ['label' => 'Thẻ sản phẩm', 'href' => '/admin/product-tags'],
                ['label' => 'Thêm mới'],
            ],
        ]);
    }

    public function store(ProductTagRequest $request): RedirectResponse
    {
        ProductTag::create($request->validated());

        return redirect()->route('admin.product-tags.index')->with('success', 'Đã thêm thẻ.');
    }

    public function edit(ProductTag $productTag): Response
    {
        return Inertia::render('ProductTags/Edit', [
            'tag' => $productTag->only(['id', 'name', 'slug', 'description', 'meta_title', 'meta_description', 'meta_key', 'no_index', 'no_follow', 'is_active']),
            'title' => 'Sửa thẻ',
            'breadcrumbs' => [
                ['label' => 'Sản phẩm'],
                ['label' => 'Thẻ sản phẩm', 'href' => '/admin/product-tags'],
                ['label' => 'Sửa'],
            ],
        ]);
    }

    public function update(ProductTagRequest $request, ProductTag $productTag): RedirectResponse
    {
        $productTag->update($request->validated());

        return redirect()->route('admin.product-tags.index')->with('success', 'Đã cập nhật thẻ.');
    }

    // Xóa thẻ thì các liên kết với sản phẩm tự mất (sản phẩm không bị xóa)
    public function destroy(ProductTag $productTag): RedirectResponse
    {
        $productTag->delete();

        return redirect()->route('admin.product-tags.index')->with('success', 'Đã xóa thẻ.');
    }
}
