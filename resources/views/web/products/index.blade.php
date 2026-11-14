@extends('web.layouts.app')

@php($heading = $category ? $category->name : ($tag ? 'Thẻ: '.$tag->name : 'Sản phẩm'))

@section('title', ($category?->meta_title ?: $tag?->meta_title ?: $heading).' - '.config('app.name'))
@section('keywords', $category?->meta_key ?: $tag?->meta_key)
@section('robots', ($category ?? $tag)?->robots() ?? 'index,follow')
@section('description', $category?->meta_description ?: $tag?->meta_description ?: ($category?->description ?? $tag?->description ?? 'Xe đạp và linh kiện chính hãng.'))

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $heading }}</h1>
        @if ($category?->description || $tag?->description)
            <p class="mt-2 text-slate-600">{{ $category?->description ?? $tag?->description }}</p>
        @endif

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ route('products.index') }}"
               class="rounded-full border px-3 py-1 {{ $category || $tag ? 'border-slate-300 hover:bg-slate-50' : 'border-emerald-600 bg-emerald-600 text-white' }}">
                Tất cả
            </a>
            @foreach ($categories as $item)
                <a href="{{ route('products.category', $item->slug) }}"
                   class="rounded-full border px-3 py-1 {{ $category?->id === $item->id ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 hover:bg-slate-50' }}">
                    {{ $item->name }}
                </a>
            @endforeach
        </div>

        @if ($children->isNotEmpty())
            <div class="mt-2 flex flex-wrap gap-2 text-sm">
                @foreach ($children as $child)
                    <a href="{{ route('products.category', $child->slug) }}" class="rounded-full border border-slate-300 px-3 py-1 hover:bg-slate-50">{{ $child->name }}</a>
                @endforeach
            </div>
        @endif

        @forelse ($products as $product)
            @if ($loop->first)
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @endif

            <article class="overflow-hidden rounded-lg border border-slate-200">
                <a href="{{ route('products.show', $product->slug) }}">
                    @if ($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-44 w-full object-cover" loading="lazy">
                    @else
                        <div class="h-44 w-full bg-slate-100"></div>
                    @endif
                </a>
                <div class="p-4">
                    <p class="text-xs text-slate-500">{{ $product->category?->name }}</p>
                    <h2 class="mt-1 font-semibold">
                        <a href="{{ route('products.show', $product->slug) }}" class="hover:text-emerald-600">{{ $product->name }}</a>
                    </h2>
                    <p class="mt-2">
                        <span class="font-semibold {{ $product->on_sale ? 'text-red-600' : '' }}">{{ number_format($product->final_price, 0, ',', '.') }} đ</span>
                        @if ($product->on_sale)
                            <span class="ml-1 text-xs text-slate-400 line-through">{{ number_format($product->price, 0, ',', '.') }} đ</span>
                        @endif
                    </p>
                    @if ($product->stock <= 0)
                        <p class="mt-1 text-xs text-red-600">Hết hàng</p>
                    @endif
                </div>
            </article>

            @if ($loop->last)
                </div>
            @endif
        @empty
            <p class="mt-8 text-slate-500">Chưa có sản phẩm nào.</p>
        @endforelse

        @if ($products->hasPages())
            <div class="mt-8 flex items-center justify-between text-sm">
                @if ($products->onFirstPage())
                    <span class="text-slate-300">&larr; Trang trước</span>
                @else
                    <a href="{{ $products->previousPageUrl() }}" class="text-emerald-600 hover:underline">&larr; Trang trước</a>
                @endif

                <span class="text-slate-500">Trang {{ $products->currentPage() }}</span>

                @if ($products->hasMorePages())
                    <a href="{{ $products->nextPageUrl() }}" class="text-emerald-600 hover:underline">Trang sau &rarr;</a>
                @else
                    <span class="text-slate-300">Trang sau &rarr;</span>
                @endif
            </div>
        @endif
    </section>
@endsection
