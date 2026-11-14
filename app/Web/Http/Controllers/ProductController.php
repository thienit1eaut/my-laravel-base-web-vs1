<?php

namespace App\Web\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTag;
use Illuminate\Contracts\View\View;

class ProductController
{
    public function index(): View
    {
        return view('web.products.index', [
            'category' => null,
            'tag' => null,
            'categories' => $this->categories(),
            'children' => collect(),
            'products' => $this->listing(Product::public()),
        ]);
    }

    // Sản phẩm theo danh mục (gồm cả danh mục con)
    public function category(string $slug): View
    {
        $category = ProductCategory::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('web.products.index', [
            'category' => $category,
            'tag' => null,
            'categories' => $this->categories(),
            'children' => $category->children()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']),
            'products' => $this->listing(Product::public()->inCategories([$category->id, ...$category->descendantIds()])),
        ]);
    }

    public function tag(string $slug): View
    {
        $tag = ProductTag::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('web.products.index', [
            'category' => null,
            'tag' => $tag,
            'categories' => $this->categories(),
            'children' => collect(),
            'products' => $this->listing(Product::public()->withTag($tag->id)),
        ]);
    }

    // Chi tiết sản phẩm (sản phẩm đang ẩn trả về 404)
    public function show(string $slug): View
    {
        $product = Product::public()->with([
            'category', 'brand:id,name', 'image', 'gallery',
            'tags' => fn ($query) => $query->where('is_active', true)->orderBy('name'),
        ])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::public()
            ->select(['id', 'category_id', 'name', 'slug', 'price', 'sale_price', 'image_id'])
            ->with('image')
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->id)
            ->latest('id')
            ->limit(4)
            ->get();

        return view('web.products.show', compact('product', 'related'));
    }

    private function listing($query)
    {
        // Không tải cột content (rất dài) cho trang danh sách; simplePaginate bỏ truy vấn COUNT
        return $query
            ->select(['id', 'category_id', 'name', 'slug', 'price', 'sale_price', 'stock', 'image_id'])
            ->with(['category:id,name,slug', 'image'])
            ->latest('id')
            ->simplePaginate(12);
    }

    private function categories()
    {
        return ProductCategory::where('is_active', true)->whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);
    }
}
