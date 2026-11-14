<script setup>
import { computed } from 'vue';

// Chọn nhiều mục bằng danh sách tick (cuộn được). Giá trị là mảng id.
// Dùng: <MultiSelectField v-model="form.extra_category_ids" label="Danh mục phụ" :options="categories" :hide-id="form.category_id" />
const props = defineProps({
    label: String,
    error: String,
    hint: String,
    options: { type: Array, default: () => [] }, // [{ id, name }]
    hideId: { type: [Number, String], default: null }, // mục không hiện (vd danh mục chính đã chọn)
    emptyText: { type: String, default: 'Chưa có mục nào.' },
});

const model = defineModel({ type: Array, default: () => [] });

const visible = computed(() => props.options.filter((option) => String(option.id) !== String(props.hideId ?? '')));

const toggle = (id) => {
    model.value = model.value.includes(id) ? model.value.filter((value) => value !== id) : [...model.value, id];
};
</script>

<template>
    <div>
        <span v-if="label" class="block text-[14px] font-semibold text-slate-800">{{ label }}</span>

        <div
            class="mt-1 max-h-40 overflow-y-auto rounded-[4px] border bg-white px-2 py-1"
            :class="error ? 'border-red-500' : 'border-[#8c8f94]'"
        >
            <label
                v-for="option in visible"
                :key="option.id"
                class="flex cursor-pointer items-center gap-2 py-0.5 text-[14px] text-slate-700"
            >
                <input type="checkbox" :checked="model.includes(option.id)" @change="toggle(option.id)" />
                {{ option.name }}
            </label>
            <p v-if="visible.length === 0" class="py-1 text-[14px] text-slate-500">{{ emptyText }}</p>
        </div>

        <p v-if="hint && !error" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>
