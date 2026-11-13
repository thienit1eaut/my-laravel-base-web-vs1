<?php

namespace App\Admin\Http\Controllers;

use App\Admin\Http\Requests\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class BrandController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:brands.view', only: ['index']),
            new Middleware('can:brands.create', only: ['create', 'store']),
            new Middleware('can:brands.update', only: ['edit', 'update']),
            new Middleware('can:brands.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        $brands = Brand::query()
            ->with('image')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Brand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'sort_order' => $brand->sort_order,
                'is_active' => $brand->is_active,
                'image_url' => $brand->image_url,
            ]);

        return Inertia::render('Brands/Index', [
            'brands' => $brands,
            'filters' => ['search' => $search],
            'title' => 'Thương hiệu',
            'breadcrumbs' => [['label' => 'Sản phẩm'], ['label' => 'Thương hiệu']],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Brands/Create', [
            'title' => 'Thêm thương hiệu',
            'breadcrumbs' => [
                ['label' => 'Sản phẩm'],
                ['label' => 'Thương hiệu', 'href' => '/admin/brands'],
                ['label' => 'Thêm mới'],
            ],
        ]);
    }

    public function store(BrandRequest $request): RedirectResponse
    {
        Brand::create($request->validated());

        return redirect()->route('admin.brands.index')
            ->with('success', 'Đã thêm thương hiệu.');
    }

    public function edit(Brand $brand): Response
    {
        return Inertia::render('Brands/Edit', [
            'brand' => [
                ...$brand->only(['id', 'name', 'slug', 'description', 'image_id', 'sort_order', 'is_active']),
                'image_url' => $brand->image_url,
            ],
            'title' => 'Sửa thương hiệu',
            'breadcrumbs' => [
                ['label' => 'Sản phẩm'],
                ['label' => 'Thương hiệu', 'href' => '/admin/brands'],
                ['label' => 'Sửa'],
            ],
        ]);
    }

    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        $brand->update($request->validated());

        return redirect()->route('admin.brands.index')
            ->with('success', 'Đã cập nhật thương hiệu.');
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Đã xóa thương hiệu.');
    }
}