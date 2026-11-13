<script setup>
import { onBeforeUnmount, watch } from 'vue';
import Icon from './Icon.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: String,
    maxWidth: { type: String, default: 'max-w-3xl' }, // class Tailwind
});

const emit = defineEmits(['close']);

const onKey = (e) => {
    if (e.key === 'Escape') emit('close');
};

// Khi mở: khóa cuộn nền và lắng nghe phím Esc
watch(
    () => props.show,
    (visible) => {
        if (visible) {
            document.addEventListener('keydown', onKey);
            document.body.style.overflow = 'hidden';
        } else {
            document.removeEventListener('keydown', onKey);
            document.body.style.overflow = '';
        }
    },
);

onBeforeUnmount(() => {
    document.removeEventListener('keydown', onKey);
    document.body.style.overflow = '';
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 p-4"
            @mousedown.self="emit('close')"
        >
            <div :class="['mt-8 w-full rounded bg-white shadow-xl', maxWidth]" role="dialog" aria-modal="true">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-2.5">
                    <h2 class="truncate text-sm font-semibold text-slate-800">{{ title }}</h2>
                    <button
                        type="button"
                        class="rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                        aria-label="Đóng"
                        @click="emit('close')"
                    >
                        <Icon name="x-mark" size="h-4 w-4" />
                    </button>
                </div>
                <div class="p-4">
                    <slot />
                </div>
            </div>
        </div>
    </Teleport>
</template>