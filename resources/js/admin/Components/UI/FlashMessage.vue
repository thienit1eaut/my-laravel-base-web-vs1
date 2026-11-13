<script setup>
import { onBeforeUnmount, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Icon from './Icon.vue';

const DURATION = 5000; // tự đóng sau 5 giây

const page = usePage();
const toasts = ref([]);
const timers = new Map();
let seq = 0;

const remove = (id) => {
    clearTimeout(timers.get(id));
    timers.delete(id);
    toasts.value = toasts.value.filter((toast) => toast.id !== id);
};

const push = (type, message) => {
    const id = ++seq;
    toasts.value.push({ id, type, message });
    timers.set(id, setTimeout(() => remove(id), DURATION));
};

// Mỗi lần server trả về dữ liệu mới (đổi trang hoặc gửi form) thì đọc lại flash
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) push('success', flash.success);
        if (flash?.error) push('error', flash.error);
    },
    { immediate: true },
);

onBeforeUnmount(() => timers.forEach((timer) => clearTimeout(timer)));
</script>

<template>
    <div class="pointer-events-none fixed right-3 top-14 z-[60] flex w-80 max-w-[calc(100vw-1.5rem)] flex-col gap-2">
        <TransitionGroup
            enter-active-class="transition duration-200"
            enter-from-class="translate-x-4 opacity-0"
            leave-active-class="transition duration-150"
            leave-to-class="opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                role="status"
                class="pointer-events-auto relative flex items-start gap-2 overflow-hidden rounded border border-slate-300 bg-white py-2 pl-4 pr-2 text-[13px] text-slate-800 shadow-lg"
            >
                <span
                    class="absolute inset-y-0 left-0 w-1"
                    :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-red-600'"
                />
                <Icon
                    :name="toast.type === 'success' ? 'check-circle' : 'exclamation-circle'"
                    size="h-4 w-4"
                    class="mt-0.5 shrink-0"
                    :class="toast.type === 'success' ? 'text-emerald-600' : 'text-red-600'"
                />
                <p class="flex-1">{{ toast.message }}</p>
                <button
                    type="button"
                    class="shrink-0 rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                    aria-label="Đóng thông báo"
                    @click="remove(toast.id)"
                >
                    <Icon name="x-mark" size="h-3.5 w-3.5" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>