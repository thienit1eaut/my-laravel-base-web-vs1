<script setup>
defineOptions({ inheritAttrs: false });

const model = defineModel();
</script>

<template>
    <input
        v-model="model"
        type="search"
        v-bind="$attrs"
        class="w-full rounded-[4px] border border-[#8c8f94] bg-white px-2 py-1 text-[14px] leading-5 text-slate-800 focus:border-indigo-600 focus:outline-none focus:ring-1 focus:ring-indigo-600 sm:w-64"
    />
</template>