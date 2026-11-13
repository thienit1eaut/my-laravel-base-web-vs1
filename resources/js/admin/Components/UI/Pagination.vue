<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: { type: Array, default: () => [] },
});

// Laravel trả về nút "Trước" (đầu), "Sau" (cuối) và các số trang ở giữa
const items = computed(() =>
    props.links.map((link, index) => ({
        ...link,
        text: index === 0 ? '‹' : index === props.links.length - 1 ? '›' : link.label,
    })),
);
</script>

<template>
    <nav v-if="links.length > 3" class="mt-3 flex flex-wrap gap-1">
        <template v-for="(item, index) in items" :key="index">
            <Link
                v-if="item.url"
                :href="item.url"
                class="rounded-[3px] border px-2 py-0.5 text-[13px]"
                :class="item.active
                    ? 'border-indigo-600 bg-indigo-600 text-white'
                    : 'border-slate-300 bg-white text-indigo-600 hover:border-indigo-600'"
            >
                {{ item.text }}
            </Link>
            <span
                v-else
                class="rounded-[3px] border border-slate-200 bg-slate-50 px-2 py-0.5 text-[13px] text-slate-400"
            >
                {{ item.text }}
            </span>
        </template>
    </nav>
</template>