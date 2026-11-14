<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\ProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class ProductCategoryController extends AdminController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:product-categories.view', only: ['index']),
            new Middleware('can:product-categories.create', only: ['create', 'store']),
            new Middleware('can:product-categories.update', only: ['edit', 'update', 'quick']),
            new Middleware('can:product-categories.delete', only: ['destroy']),
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

    public function quick(Request $request, ProductCategory $productCategory): RedirectResponse
    {
        return $this->quickUpdate($request, $productCategory);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $categories = ProductCategory::query()
            ->with(['image', 'parent:id,name'])
            ->withCount('products')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (ProductCategory $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'parent' => $category->parent?->name,
                'slug' => $category->slug,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
                'products_count' => $category->products_count,
                'image_url' => $category->image_url,
                // Chỉ danh mục đang bật mới có trang công khai để mở
                'public_url' => $category->is_active ? $category->url : null,
            ]);

        return Inertia::render('ProductCategories/Index', [
            'categories' => $categories,
            'filters' => ['search' => $search],
            'title' => 'Danh mục sản phẩm',
            'breadcrumbs' => [['label' => 'Sản phẩm'], ['label' => 'Danh mục sản phẩm']],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('ProductCategories/Create', [
            'parents' => ProductCategory::pathOptions(),
            'title' => 'Thêm danh mục sản phẩm',
            'breadcrumbs' => [
                ['label' => 'Sản phẩm'],
                ['label' => 'Danh mục sản phẩm', 'href' => '/admin/product-categories'],
                ['label' => 'Thêm mới'],
            ],
        ]);
    }

    public function store(ProductCategoryRequest $request): RedirectResponse
    {
        ProductCategory::create($request->validated());

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Đã thêm danh mục sản phẩm.');
    }

    public function edit(ProductCategory $productCategory): Response
    {
        return Inertia::render('ProductCategories/Edit', [
            'category' => [
                ...$productCategory->only([
                    'id', 'parent_id', 'name', 'slug', 'description', 'image_id',
                    'meta_title', 'meta_description', 'meta_key', 'no_index', 'no_follow', 'sort_order', 'is_active',
                ]),
                'image_url' => $productCategory->image_url,
            ],
            'parents' => ProductCategory::pathOptions([$productCategory->id, ...$productCategory->descendantIds()]),
            'title' => 'Sửa danh mục sản phẩm',
            'breadcrumbs' => [
                ['label' => 'Sản phẩm'],
                ['label' => 'Danh mục sản phẩm', 'href' => '/admin/product-categories'],
                ['label' => 'Sửa'],
            ],
        ]);
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory): RedirectResponse
    {
        $productCategory->update($request->validated());

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Đã cập nhật danh mục sản phẩm.');
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        if ($productCategory->children()->exists()) {
            return back()->with('error', 'Danh mục còn danh mục con, hãy chuyển hoặc xóa các danh mục con trước.');
        }

        // Không xóa danh mục còn sản phẩm để tránh mất sản phẩm hoặc link chết
        if ($productCategory->products()->exists()) {
            return back()->with('error', 'Danh mục còn sản phẩm, hãy chuyển hoặc xóa các sản phẩm trước.');
        }

        $productCategory->delete();

        return redirect()->route('admin.product-categories.index')
            ->with('success', 'Đã xóa danh mục sản phẩm.');
    }
}
