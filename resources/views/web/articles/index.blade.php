@extends('web.layouts.app')

@php($tag = $tag ?? null)
@php($heading = $category ? $category->name : ($tag ? 'Thẻ: '.$tag->name : 'Bài viết'))

@section('title', ($category?->meta_title ?: $tag?->meta_title ?: $heading).' - '.config('app.name'))
@section('keywords', $category?->meta_key ?: $tag?->meta_key)
@section('robots', ($category ?? $tag)?->robots() ?? 'index,follow')
@section('description', $category?->meta_description ?: $tag?->meta_description ?: ($category?->description ?? $tag?->description ?? 'Tin tức, kinh nghiệm và hướng dẫn về xe đạp và linh kiện.'))

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-10">
        <h1 class="text-3xl font-bold">{{ $heading }}</h1>
        @if ($category?->description || $tag?->description)
            <p class="mt-2 text-slate-600">{{ $category?->description ?? $tag?->description }}</p>
        @endif

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
            <a href="{{ route('articles.index') }}"
               class="rounded-full border px-3 py-1 {{ $category ? 'border-slate-300 hover:bg-slate-50' : 'border-emerald-600 bg-emerald-600 text-white' }}">
                Tất cả
            </a>
            @foreach ($categories as $item)
                <a href="{{ route('articles.category', $item->slug) }}"
                   class="rounded-full border px-3 py-1 {{ $category?->id === $item->id ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-slate-300 hover:bg-slate-50' }}">
                    {{ $item->name }}
                </a>
            @endforeach
        </div>

        @if ($children->isNotEmpty())
            <div class="mt-2 flex flex-wrap gap-2 text-sm">
                @foreach ($children as $child)
                    <a href="{{ route('articles.category', $child->slug) }}" class="rounded-full border border-slate-300 px-3 py-1 hover:bg-slate-50">{{ $child->name }}</a>
                @endforeach
            </div>
        @endif

        @forelse ($articles as $article)
            @if ($loop->first)
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @endif

            <article class="overflow-hidden rounded-lg border border-slate-200">
                <a href="{{ route('articles.show', $article->slug) }}">
                    @if ($article->image_url)
                        <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="h-44 w-full object-cover" loading="lazy">
                    @else
                        <div class="h-44 w-full bg-slate-100"></div>
                    @endif
                </a>
                <div class="p-4">
                    <p class="text-xs text-slate-500">
                        {{ $article->category?->name }} &middot; {{ $article->published_at?->format('d/m/Y') }}
                    </p>
                    <h2 class="mt-1 font-semibold">
                        <a href="{{ route('articles.show', $article->slug) }}" class="hover:text-emerald-600">{{ $article->title }}</a>
                    </h2>
                    @if ($article->excerpt)
                        <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $article->excerpt }}</p>
                    @endif
                </div>
            </article>

            @if ($loop->last)
                </div>
            @endif
        @empty
            <p class="mt-8 text-slate-500">Chưa có bài viết nào.</p>
        @endforelse

        @if ($articles->hasPages())
            <div class="mt-8 flex items-center justify-between text-sm">
                @if ($articles->onFirstPage())
                    <span class="text-slate-300">&larr; Trang trước</span>
                @else
                    <a href="{{ $articles->previousPageUrl() }}" class="text-emerald-600 hover:underline">&larr; Trang trước</a>
                @endif

                <span class="text-slate-500">Trang {{ $articles->currentPage() }}</span>

                @if ($articles->hasMorePages())
                    <a href="{{ $articles->nextPageUrl() }}" class="text-emerald-600 hover:underline">Trang sau &rarr;</a>
                @else
                    <span class="text-slate-300">Trang sau &rarr;</span>
                @endif
            </div>
        @endif
    </section>
@endsection
