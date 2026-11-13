<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AdminLayout from '../Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

defineProps({
    serverTime: String,
});

const count = ref(0);
</script>

<template>
    <Head title="Tổng quan" />

    <div class="rounded-xl bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold">Xin chào, đây là phần quản trị</h2>
        <p class="mt-2 text-slate-600">
            Giờ máy chủ: <span class="font-mono">{{ serverTime }}</span>
        </p>
        <button
            type="button"
            class="mt-4 rounded-md bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"
            @click="count++"
        >
            Vue đang hoạt động: đã bấm {{ count }} lần
        </button>
    </div>
</template>