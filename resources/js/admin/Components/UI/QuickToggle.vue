<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

// Nút bật/tắt gửi ngay lên server (PATCH .../quick). Lỗi thì tự hoàn lại giá trị cũ.
// Dùng: <QuickToggle :url="quickUrl(row)" field="is_active" :value="row.is_active" :disabled="..." />
const props = defineProps({
    url: { type: String, required: true },
    field: { type: String, required: true },
    value: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    label: String,
});

const current = ref(props.value);
const saving = ref(false);

watch(() => props.value, (value) => (current.value = value));

const toggle = () => {
    if (props.disabled || saving.value) return;

    const previous = current.value;
    current.value = !previous;
    saving.value = true;

    router.patch(props.url, { [props.field]: current.value }, {
        preserveScroll: true,
        preserveState: true,
        onError: () => (current.value = previous),
        onFinish: () => (saving.value = false),
    });
};
</script>

<template>
    <button
        type="button"
        role="switch"
        :aria-checked="current"
        :aria-label="label"
        :disabled="disabled || saving"
        class="relative inline-flex h-[18px] w-8 shrink-0 cursor-pointer items-center rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-1 disabled:cursor-not-allowed disabled:opacity-50"
        :class="current ? 'bg-indigo-600' : 'bg-slate-300'"
        @click="toggle"
    >
        <span
            class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform"
            :class="current ? 'translate-x-[16px]' : 'translate-x-[2px]'"
        />
    </button>
</template>
