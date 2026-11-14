<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\ProductRequest;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use App\Support\HtmlSanitizer;
use App\Support\PageCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends AdminController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:products.view', only: ['index']),
            new Middleware('can:products.create', only: ['create', 'store']),
            new Middleware('can:products.update', only: ['edit', 'update', 'quick']),
            new Middleware('can:products.delete', only: ['destroy']),
        ];
    }

    /** Trường được sửa nhanh ngay trên trang danh sách => luật kiểm tra (xem AdminController::quickUpdate). */
    protected function quickRules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
            'stock' => ['required', 'integer', 'min:0', 'max:4000000000'],
        ];
    }

    public function quick(Request $request, Product $product): RedirectResponse
    {
        return $this->quickUpdate($request, $product);
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category_id');

        $products = Product::query()
            ->select(['id', 'category_id', 'brand_id', 'public_by', 'name', 'slug', 'sku', 'price', 'sale_price', 'stock', 'image_id', 'is_active'])
            ->with(['image', 'category:id,name', 'brand:id,name', 'publisher:id,name'])
            ->when($search !== '', fn ($query) => $query->where(
                fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%")
            ))
            ->when($categoryId, fn ($query) => $query->inCategories([(int) $categoryId]))
            ->latest('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'sku' => $product->sku,
                'category' => $product->category?->name,
                'brand' => $product->brand?->name,
                'publisher' => $product->publisher?->name,
                'price' => $product->price,
                'sale_price' => $product->sale_price,
                'stock' => $product->stock,
                'is_active' => $product->is_active,
                'image_url' => $product->image ? ($product->image->variantUrl('thumb_400') ?? $product->image->url) : null,
                'public_url' => $product->url,
                'public_issue' => $product->publicIssue(),
            ]);

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => ProductCategory::pathOptions(),
            'filters' => ['search' => $search, 'category_id' => $categoryId],
            'title' => 'Sản phẩm',
            'breadcrumbs' => [['label' => 'Sản phẩm'], ['label' => 'Danh sách sản phẩm']],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create', [
            ...$this->formOptions(),
            'title' => 'Thêm sản phẩm',
            'breadcrumbs' => [
                ['label' => 'Sản phẩm'],
                ['label' => 'Danh sách sản phẩm', 'href' => '/admin/products'],
                ['label' => 'Thêm mới'],
            ],
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $this->sanitized($request->validated());
        [$extra, $tags, $gallery] = $this->pullRelations($data);

        DB::transaction(function () use ($data, $extra, $tags, $gallery) {
            $product = Product::create($data);
            $this->syncRelations($product, $extra, $tags, $gallery);
        });

        return redirect()->route('admin.products.index')->with('success', 'Đã thêm sản phẩm.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Edit', [
            'product' => [
                ...$product->only([
                    'id', 'category_id', 'brand_id', 'name', 'slug', 'sku', 'price', 'sale_price', 'stock',
                    'short_description', 'content', 'image_id', 'meta_title', 'meta_description', 'meta_key', 'no_index', 'no_follow', 'is_active',
                ]),
                'specs' => $product->specs ?? [],
                'image_url' => $product->image_url,
                'extra_category_ids' => $product->categories()->pluck('product_categories.id')
                    ->map(fn ($id) => (int) $id)
                    ->reject(fn ($id) => $id === (int) $product->category_id)
                    ->values()->all(),
                'tags' => $product->tags()->orderBy('name')->pluck('name')->all(),
                // Thư viện ảnh: id + đường dẫn ảnh nhỏ, theo thứ tự đã sắp
                'gallery' => $product->gallery->map(fn ($media) => [
                    'id' => $media->id,
                    'url' => $media->variantUrl('thumb_400') ?? $media->url,
                ])->values()->all(),
            ],
            ...$this->formOptions(),
            'title' => 'Sửa sản phẩm',
            'breadcrumbs' => [
                ['label' => 'Sản phẩm'],
                ['label' => 'Danh sách sản phẩm', 'href' => '/admin/products'],
                ['label' => 'Sửa'],
            ],
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $this->sanitized($request->validated());
        [$extra, $tags, $gallery] = $this->pullRelations($data);

        DB::transaction(function () use ($product, $data, $extra, $tags, $gallery) {
            $product->update($data);
            $this->syncRelations($product, $extra, $tags, $gallery);
        });

        return redirect()->route('admin.products.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Đã xóa sản phẩm.');
    }

    private function formOptions(): array
    {
        return [
            'categories' => ProductCategory::pathOptions(),
            'brands' => Brand::orderBy('name')->get(['id', 'name'])->map(fn ($b) => ['id' => $b->id, 'name' => $b->name])->all(),
            'tagOptions' => ProductTag::orderBy('name')->limit(500)->pluck('name')->all(),
        ];
    }

    /** Tách các trường quan hệ (danh mục phụ, thẻ, thư viện ảnh) ra khỏi dữ liệu cột của sản phẩm. */
    private function pullRelations(array &$data): array
    {
        return [
            Arr::pull($data, 'extra_category_ids') ?? [],
            Arr::pull($data, 'tags') ?? [],
            Arr::pull($data, 'gallery_ids') ?? [],
        ];
    }

    private function syncRelations(Product $product, array $extra, array $tags, array $gallery): void
    {
        $this->syncCategories($product, $extra);
        $this->syncTags($product, $tags);
        $this->syncGallery($product, $gallery);

        // Các bảng trung gian vừa đổi sau khi sản phẩm được lưu, nên làm mới cache trang web một lần nữa
        PageCache::flush();
    }

    // Bảng trung gian luôn chứa danh mục chính + danh mục phụ đã chọn + mọi danh mục cha của chúng
    private function syncCategories(Product $product, array $extraIds): void
    {
        $ids = collect($extraIds)->push($product->category_id)->map(fn ($id) => (int) $id)->unique()
            ->flatMap(fn (int $id) => [$id, ...ProductCategory::ancestorIds($id)])
            ->unique()->values()->all();

        $product->categories()->sync($ids);
    }

    // Gắn thẻ theo tên: thẻ đã có thì dùng lại, chưa có thì tạo mới nếu người dùng có quyền product-tags.create
    private function syncTags(Product $product, array $names): void
    {
        $names = collect($names)
            ->map(fn ($name) => trim((string) preg_replace('/\s+/u', ' ', (string) $name)))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->take(20)
            ->values();

        $existing = ProductTag::whereIn('name', $names)->get()->keyBy(fn (ProductTag $tag) => mb_strtolower($tag->name));
        $canCreate = Gate::allows('product-tags.create');
        $ids = [];

        foreach ($names as $name) {
            $tag = $existing->get(mb_strtolower($name));

            if (! $tag && $canCreate) {
                $tag = ProductTag::create(['name' => $name, 'is_active' => true]);
            }

            if ($tag) {
                $ids[] = $tag->id;
            }
        }

        $product->tags()->sync($ids);
    }

    // Thư viện ảnh: lưu theo đúng thứ tự người dùng sắp
    private function syncGallery(Product $product, array $mediaIds): void
    {
        $sync = [];

        foreach (array_values($mediaIds) as $position => $id) {
            $sync[(int) $id] = ['sort_order' => $position];
        }

        $product->gallery()->sync($sync);
    }

    // Nội dung từ trình soạn thảo là HTML: lọc bỏ thẻ/thuộc tính nguy hiểm trước khi lưu
    private function sanitized(array $data): array
    {
        $data['content'] = HtmlSanitizer::clean($data['content'] ?? null);

        return $data;
    }
}
