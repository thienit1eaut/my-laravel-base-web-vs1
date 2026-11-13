<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    status: Number,
    message: String,
});

const titles = {
    403: 'Không có quyền truy cập',
    404: 'Không tìm thấy',
    419: 'Phiên làm việc hết hạn',
    500: 'Lỗi hệ thống',
    503: 'Đang bảo trì',
};
</script>

<template>
    <Head :title="titles[status] ?? 'Có lỗi xảy ra'" />

    <div class="flex min-h-screen items-center justify-center bg-slate-100 px-4">
        <div class="w-full max-w-md rounded-xl bg-white p-8 text-center shadow-sm">
            <p class="text-5xl font-bold text-slate-300">{{ status }}</p>
            <h1 class="mt-3 text-xl font-semibold text-slate-800">{{ titles[status] ?? 'Có lỗi xảy ra' }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ message }}</p>
            <a
                href="/admin"
                class="mt-6 inline-block rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
            >
                Về trang quản trị
            </a>
        </div>
    </div>
</template>