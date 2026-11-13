<script setup>
defineProps({
    // gray | success | danger | warning | info
    variant: { type: String, default: 'gray' },
});

const classes = {
    gray: 'bg-slate-200 text-slate-700',
    success: 'bg-emerald-100 text-emerald-700',
    danger: 'bg-red-100 text-red-700',
    warning: 'bg-amber-100 text-amber-700',
    info: 'bg-sky-100 text-sky-700',
};
</script>

<template>
    <span
        class="inline-block rounded-full px-1.5 py-px text-[11px] font-medium"
        :class="classes[variant] ?? classes.gray"
    >
        <slot />
    </span>
</template>