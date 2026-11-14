<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

// Ô số sửa nhanh: nhấn Enter hoặc rời ô để lưu, Esc để hủy. Lỗi thì hoàn lại giá trị cũ.
// Dùng: <QuickNumber :url="quickUrl(row)" field="sort_order" :value="row.sort_order" />
const props = defineProps({
    url: { type: String, required: true },
    field: { type: String, required: true },
    value: { type: [Number, String], default: 0 },
    disabled: { type: Boolean, default: false },
    min: { type: [Number, String], default: 0 },
    width: { type: String, default: 'w-16' }, // lớp Tailwind độ rộng ô, vd 'w-28' cho ô giá
});

const current = ref(props.value);
const saving = ref(false);

watch(() => props.value, (value) => (current.value = value));

const save = () => {
    if (saving.value || String(current.value) === String(props.value)) return;

    const previous = props.value;
    saving.value = true;

    router.patch(props.url, { [props.field]: current.value === '' ? null : Number(current.value) }, {
        preserveScroll: true,
        preserveState: true,
        onError: () => (current.value = previous),
        onFinish: () => (saving.value = false),
    });
};

const cancel = (event) => {
    current.value = props.value;
    event.target.blur();
};
</script>

<template>
    <input
        v-model="current"
        type="number"
        :min="min"
        :disabled="disabled || saving"
        :aria-label="field"
        :class="width"
        class="rounded border border-slate-300 bg-white px-1.5 py-0.5 text-[14px] text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:opacity-50"
        @keydown.enter.prevent="$event.target.blur()"
        @keydown.esc="cancel"
        @blur="save"
    />
</template>
