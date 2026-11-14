@extends('web.layouts.app')

@section('title', ($product->meta_title ?: $product->name).' - '.config('app.name'))
@section('description', $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->short_description), 160))
@section('keywords', $product->meta_key)
@section('robots', $product->robots())
@section('canonical', route('products.show', $product->slug))

@section('content')
    <article class="mx-auto max-w-5xl px-4 py-10" x-data="{ main: @js($product->image_url ?? ($product->gallery->first()?->url)) }">
        <nav class="text-sm text-slate-500">
            <a href="{{ route('home') }}" class="hover:text-emerald-600">Trang chủ</a> /
            <a href="{{ route('products.index') }}" class="hover:text-emerald-600">Sản phẩm</a>
            @if ($product->category->is_active)
                / <a href="{{ route('products.category', $product->category->slug) }}" class="hover:text-emerald-600">{{ $product->category->name }}</a>
            @endif
        </nav>

        <div class="mt-6 grid gap-8 md:grid-cols-2">
            <div>
                <template x-if="main">
                    <img :src="main" alt="{{ $product->name }}" class="w-full rounded-lg border border-slate-200 object-cover">
                </template>
                @if (! $product->image_url && $product->gallery->isEmpty())
                    <div class="h-72 w-full rounded-lg bg-slate-100"></div>
                @endif

                @if ($product->gallery->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($product->gallery as $media)
                            @php($thumb = $media->variantUrl('thumb_400') ?? $media->url)
                            <button type="button" @click="main = @js($media->variantUrl('thumb_800') ?? $media->url)" class="overflow-hidden rounded border border-slate-200 hover:border-emerald-600">
                                <img src="{{ $thumb }}" alt="" class="h-16 w-16 object-cover" loading="lazy">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h1 class="text-3xl font-bold leading-tight">{{ $product->name }}</h1>
                <p class="mt-2 text-sm text-slate-500">
                    @if ($product->brand) Thương hiệu: {{ $product->brand->name }} @endif
                    @if ($product->sku) &middot; SKU: {{ $product->sku }} @endif
                </p>

                <p class="mt-4 text-2xl">
                    <span class="font-bold {{ $product->on_sale ? 'text-red-600' : '' }}">{{ number_format($product->final_price, 0, ',', '.') }} đ</span>
                    @if ($product->on_sale)
                        <span class="ml-2 text-base text-slate-400 line-through">{{ number_format($product->price, 0, ',', '.') }} đ</span>
                    @endif
                </p>

                <p class="mt-2 text-sm {{ $product->stock > 0 ? 'text-emerald-700' : 'text-red-600' }}">
                    {{ $product->stock > 0 ? 'Còn hàng' : 'Hết hàng' }}
                </p>

                @if ($product->short_description)
                    <p class="mt-4 text-slate-700">{!! nl2br(e($product->short_description)) !!}</p>
                @endif

                @if ($product->tags->isNotEmpty())
                    <div class="mt-4 flex flex-wrap gap-2 text-sm">
                        @foreach ($product->tags as $tag)
                            <a href="{{ route('products.tag', $tag->slug) }}" class="rounded-full border border-slate-300 px-3 py-1 text-slate-600 hover:bg-slate-50">#{{ $tag->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        @if ($product->content)
            <h2 class="mt-10 text-xl font-semibold">Mô tả chi tiết</h2>
            {{-- Nội dung HTML đã được lọc khi lưu (HtmlSanitizer); nội dung cũ dạng văn bản thuần thì được escape --}}
            <div class="rich-content mt-3 text-slate-800">{!! \App\Support\HtmlSanitizer::render($product->content) !!}</div>
        @endif

        @if (! empty($product->specs))
            <h2 class="mt-10 text-xl font-semibold">Thông số kỹ thuật</h2>
            <table class="mt-3 w-full border-collapse text-sm">
                @foreach ($product->specs as $row)
                    <tr class="border-b border-slate-200">
                        <th class="w-1/3 bg-slate-50 px-3 py-2 text-left font-medium">{{ $row['name'] ?? '' }}</th>
                        <td class="px-3 py-2">{{ $row['value'] ?? '' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    </article>

    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-5xl px-4 pb-10">
            <h2 class="text-xl font-semibold">Sản phẩm liên quan</h2>
            <ul class="mt-3 list-disc space-y-1 pl-5">
                @foreach ($related as $item)
                    <li><a href="{{ route('products.show', $item->slug) }}" class="text-emerald-700 hover:underline">{{ $item->name }}</a></li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
