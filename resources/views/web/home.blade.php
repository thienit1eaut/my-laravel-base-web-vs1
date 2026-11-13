@extends('web.layouts.app')

@section('title', 'Trang chủ - ' . config('app.name'))

@section('content')
    <section class="mx-auto max-w-6xl px-4 py-16 text-center">
        <h1 class="text-3xl font-bold md:text-5xl">Cửa hàng xe đạp và linh kiện</h1>
        <p class="mt-4 text-slate-600">
            Đây là phần web hiển thị cho khách hàng (Blade + Tailwind + Alpine.js).
        </p>

        <div x-data="{ count: 0 }" class="mt-8">
            <button
                type="button"
                class="rounded-md bg-emerald-600 px-5 py-2 text-white hover:bg-emerald-700"
                @click="count++"
            >
                Alpine đang hoạt động: đã bấm <span x-text="count"></span> lần
            </button>
        </div>
    </section>
@endsection