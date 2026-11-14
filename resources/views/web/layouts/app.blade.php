<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @endif
    @hasSection('keywords')
        <meta name="keywords" content="@yield('keywords')">
    @endif
    @hasSection('robots')
        <meta name="robots" content="@yield('robots')">
    @endif
    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @endif

    @vite(['resources/css/web/app.css', 'resources/js/web/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-800 antialiased">
    <header class="border-b border-slate-200" x-data="{ open: false }">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between px-4 py-4">
            <a href="{{ route('home') }}" class="text-lg font-bold">{{ config('app.name') }}</a>

            <button type="button" class="rounded border border-slate-300 px-3 py-1 text-sm md:hidden" @click="open = !open">
                Menu
            </button>

            <nav :class="open ? 'flex' : 'hidden'" class="w-full flex-col gap-2 pt-4 text-sm md:flex md:w-auto md:flex-row md:gap-6 md:pt-0">
                <a href="{{ route('home') }}" class="hover:text-emerald-600">Trang chủ</a>
                <a href="#" class="hover:text-emerald-600">Sản phẩm</a>
                <a href="{{ route('products.index') }}" class="hover:text-emerald-600">Sản phẩm</a>
                <a href="{{ route('articles.index') }}" class="hover:text-emerald-600">Bài viết</a>
                <a href="#" class="hover:text-emerald-600">Liên hệ</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-slate-200 py-6 text-center text-sm text-slate-500">
        &copy; {{ date('Y') }} {{ config('app.name') }}
    </footer>
</body>
</html>