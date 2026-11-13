<script setup>
import { Link } from '@inertiajs/vue3';
import Icon from './Icon.vue';

defineProps({
    icon: { type: String, required: true },
    label: { type: String, required: true }, // hiện khi rê chuột, đồng thời là nhãn cho trình đọc màn hình
    href: String,                            // có href = liên kết, không có = nút bấm
    variant: { type: String, default: 'default' }, // default | danger
});

const colors = {
    default: 'text-slate-500 hover:bg-slate-100 hover:text-indigo-600',
    danger: 'text-slate-500 hover:bg-red-50 hover:text-red-600',
};
</script>

<template>
    <Link
        v-if="href"
        :href="href"
        :title="label"
        :aria-label="label"
        class="inline-flex rounded-md p-1.2"
        :class="colors[variant]"
    >
        <Icon :name="icon" />
    </Link>
    <button
        v-else
        type="button"
        :title="label"
        :aria-label="label"
        class="inline-flex rounded-md p-1.2"
        :class="colors[variant]"
    >
        <Icon :name="icon" />
    </button>
</template>