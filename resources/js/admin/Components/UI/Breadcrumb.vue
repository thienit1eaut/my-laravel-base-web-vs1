<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from './Icon.vue';

// items: [{ label: 'Sản phẩm' }, { label: 'Thương hiệu', href: '/admin/brands' }, { label: 'Thêm mới' }]
// Mục cuối là trang hiện tại (không có liên kết). Biểu tượng nhà luôn dẫn về /admin.
defineProps({
    items: { type: Array, default: () => [] },
});
</script>

<template>
    <nav aria-label="Breadcrumb" class="flex flex-wrap items-center gap-1 text-xs text-slate-500">
        <Link href="/admin" class="inline-flex text-indigo-600 hover:text-indigo-800" aria-label="Tổng quan">
            <Icon name="home" size="h-3.5 w-3.5" />
        </Link>

        <template v-for="(item, index) in items" :key="index">
            <Icon name="chevron-right" size="h-3 w-3" class="text-slate-400" />
            <Link
                v-if="item.href && index < items.length - 1"
                :href="item.href"
                class="text-indigo-600 hover:text-indigo-800 hover:underline"
            >
                {{ item.label }}
            </Link>
            <span v-else :class="index === items.length - 1 ? 'font-medium text-slate-700' : ''">
                {{ item.label }}
            </span>
        </template>
    </nav>
</template>