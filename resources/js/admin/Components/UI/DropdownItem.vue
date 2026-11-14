<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: String, // có href = liên kết, không có = nút bấm (gắn @click ở nơi dùng)
    danger: Boolean,
});
</script>

<template>
    <Link
        v-if="href"
        :href="href"
        role="menuitem"
        class="block w-full px-3 py-1.5 text-left text-[14px] hover:bg-slate-100"
        :class="danger ? 'text-red-600' : 'text-slate-700'"
    >
        <slot />
    </Link>
    <button
        v-else
        type="button"
        role="menuitem"
        class="block w-full px-3 py-1.5 text-left text-[14px] hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent"
        :class="danger ? 'text-red-600' : 'text-slate-700'"
    >
        <slot />
    </button>
</template>