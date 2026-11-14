<script setup>
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

// Form có tab. Mỗi tab là một slot cùng tên với 'key' của tab (ví dụ #info, #content, #seo).
// Tab chứa trường bị lỗi validate sẽ được đánh dấu đỏ và tự mở khi có lỗi.
// Dùng: <TabbedFormCard :tabs="tabs" :errors="form.errors" ...><template #info>...</template></TabbedFormCard>
const props = defineProps({
    tabs: { type: Array, required: true }, // [{ key, label, fields: [] }]
    errors: { type: Object, default: () => ({}) },
    processing: { type: Boolean, default: false },
    submitLabel: { type: String, default: 'Lưu' },
    cancelHref: String,
});

defineEmits(['submit']);

const active = ref(props.tabs[0]?.key);

const belongsTo = (errorKey) => {
    const found = props.tabs.find((tab) =>
        (tab.fields ?? []).some((field) => errorKey === field || errorKey.startsWith(`${field}.`)),
    );

    // Lỗi của trường không thuộc tab nào thì tính vào tab đầu tiên
    return found?.key ?? props.tabs[0]?.key;
};

const tabsWithError = computed(() => new Set(Object.keys(props.errors).map(belongsTo)));

// Khi vừa có lỗi mà tab đang mở không có lỗi, chuyển sang tab lỗi đầu tiên
watch(
    () => props.errors,
    (errors) => {
        if (Object.keys(errors).length === 0 || tabsWithError.value.has(active.value)) return;

        active.value = props.tabs.find((tab) => tabsWithError.value.has(tab.key))?.key ?? active.value;
    },
);
</script>

<template>
    <form class="max-w-5xl rounded-xl bg-white shadow-sm" @submit.prevent="$emit('submit')">
        <div v-if="tabs.length > 1" class="flex gap-1 border-b border-slate-200 px-4 pt-2" role="tablist">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                role="tab"
                :aria-selected="active === tab.key"
                class="-mb-px flex items-center gap-1.5 border-b-2 px-3 py-2 text-[14px] font-medium transition-colors"
                :class="[
                    active === tab.key ? 'border-indigo-600' : 'border-transparent hover:text-slate-800',
                    tabsWithError.has(tab.key)
                        ? 'text-red-600'
                        : active === tab.key
                          ? 'text-indigo-600'
                          : 'text-slate-500',
                ]"
                @click="active = tab.key"
            >
                {{ tab.label }}
                <span v-if="tabsWithError.has(tab.key)" class="h-1.5 w-1.5 rounded-full bg-red-600" aria-label="Có lỗi" />
            </button>
        </div>

        <div class="space-y-5 p-6">
            <!-- v-show (không v-if) để các trường ở tab ẩn vẫn được giữ nguyên trạng thái -->
            <div v-for="(tab, index) in tabs" v-show="active === tab.key" :key="tab.key" class="space-y-5">
                <slot :name="tab.key">
                    <slot v-if="index === 0" />
                </slot>
            </div>

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
        </div>
    </form>
</template>
