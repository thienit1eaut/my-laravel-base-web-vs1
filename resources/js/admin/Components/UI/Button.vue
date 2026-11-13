<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    variant: { type: String, default: 'primary' }, // primary | secondary | danger
    href: String, // có href = liên kết, không có = nút bấm
    type: { type: String, default: 'button' },
});

const base =
    'inline-flex items-center justify-center gap-1 rounded-[3px] border px-2.5 py-1 text-[13px] leading-5 transition-colors disabled:cursor-not-allowed disabled:opacity-60';

const variants = {
    primary: 'border-indigo-600 bg-indigo-600 text-white hover:border-indigo-700 hover:bg-indigo-700',
    secondary:
        'border-indigo-600 bg-slate-50 text-indigo-600 hover:border-indigo-800 hover:bg-slate-100 hover:text-indigo-800',
    danger: 'border-red-600 bg-slate-50 text-red-600 hover:bg-red-600 hover:text-white',
};
</script>

<template>
    <Link v-if="href" :href="href" :class="[base, variants[variant]]">
        <slot />
    </Link>
    <button v-else :type="type" :class="[base, variants[variant]]">
        <slot />
    </button>
</template>