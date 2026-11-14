@extends('web.layouts.app')

@section('title', ($article->meta_title ?: $article->title).' - '.config('app.name'))
@section('description', $article->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $article->excerpt), 160))
@section('keywords', $article->meta_key)
@section('robots', $article->robots())
@section('canonical', route('articles.show', $article->slug))

@section('content')
    <article class="mx-auto max-w-3xl px-4 py-10">
        <nav class="text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-emerald-600">Trang chủ</a> /
            <a href="{{ route('articles.index') }}" class="hover:text-emerald-600">Bài viết</a>
            @if ($article->category->is_active)
                / <a href="{{ route('articles.category', $article->category->slug) }}" class="hover:text-emerald-600">{{ $article->category->name }}</a>
            @endif
        </nav>

        <h1 class="mt-4 text-3xl font-bold leading-tight">{{ $article->title }}</h1>
        <p class="mt-2 text-sm text-slate-500">
            {{ $article->published_at?->format('d/m/Y H:i') }}
            @if ($article->publisher) &middot; {{ $article->publisher->name }} @endif
        </p>

        @if ($article->image_url)
            <img src="{{ $article->image_url }}" alt="{{ $article->title }}" class="mt-6 w-full rounded-lg object-cover">
        @endif

        @if ($article->excerpt)
            <p class="mt-6 text-lg font-medium text-slate-700">{{ $article->excerpt }}</p>
        @endif

        {{-- Nội dung HTML đã được lọc khi lưu (HtmlSanitizer); nội dung cũ dạng văn bản thuần thì được escape --}}
        <div class="rich-content mt-6 text-slate-800">{!! \App\Support\HtmlSanitizer::render($article->content) !!}</div>

        @if ($article->tags->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2 text-sm">
                @foreach ($article->tags as $tag)
                    <a href="{{ route('articles.tag', $tag->slug) }}" class="rounded-full border border-slate-300 px-3 py-1 text-slate-600 hover:bg-slate-50">#{{ $tag->name }}</a>
                @endforeach
            </div>
        @endif
    </article>

    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-3xl px-4 pb-10">
            <h2 class="text-xl font-semibold">Bài viết liên quan</h2>
            <ul class="mt-3 list-disc space-y-1 pl-5">
                @foreach ($related as $item)
                    <li><a href="{{ route('articles.show', $item->slug) }}" class="text-emerald-700 hover:underline">{{ $item->title }}</a></li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
