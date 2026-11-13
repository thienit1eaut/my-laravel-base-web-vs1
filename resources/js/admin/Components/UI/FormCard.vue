<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    processing: { type: Boolean, default: false },
    submitLabel: { type: String, default: 'Lưu' },
    cancelHref: String,
});

defineEmits(['submit']);
</script>

<template>
    <form class="max-w-2xl space-y-5 rounded-xl bg-white p-6 shadow-sm" @submit.prevent="$emit('submit')">
        <slot />

        <div class="flex items-center gap-3 pt-2">
            <button
                type="submit"
                :disabled="processing"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60"
            >
                {{ submitLabel }}
            </button>
            <Link v-if="cancelHref" :href="cancelHref" class="text-sm text-slate-600 hover:underline">
                Hủy
            </Link>
        </div>
    </form>
</template>