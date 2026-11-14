<script setup>
import { ref, useId } from 'vue';

// Nhập thẻ dạng chip: gõ tên rồi nhấn Enter hoặc dấu phẩy để thêm, Backspace khi ô trống để bỏ thẻ cuối.
// Gợi ý từ các thẻ đã có (suggestions). Giá trị là mảng tên thẻ.
// Dùng: <TagInput v-model="form.tags" label="Thẻ" :suggestions="tagOptions" :error="form.errors.tags" />
const props = defineProps({
    label: String,
    error: String,
    hint: String,
    suggestions: { type: Array, default: () => [] },
    max: { type: Number, default: 20 },
});

const model = defineModel({ type: Array, default: () => [] });

const listId = useId();
const draft = ref('');

const add = (raw) => {
    const name = String(raw).replace(/\s+/g, ' ').trim().replace(/,/g, '');

    if (!name || model.value.length >= props.max) return;

    // Không thêm trùng (không phân biệt hoa thường)
    if (!model.value.some((tag) => tag.toLowerCase() === name.toLowerCase())) {
        model.value = [...model.value, name];
    }
};

const commit = () => {
    add(draft.value);
    draft.value = '';
};

const remove = (index) => {
    model.value = model.value.filter((_, i) => i !== index);
};

const onKeydown = (event) => {
    if (event.key === 'Enter' || event.key === ',') {
        event.preventDefault();
        commit();
    } else if (event.key === 'Backspace' && draft.value === '' && model.value.length) {
        remove(model.value.length - 1);
    }
};
</script>

<template>
    <div>
        <span v-if="label" class="block text-[14px] font-semibold text-slate-800">{{ label }}</span>

        <div
            class="mt-1 flex flex-wrap items-center gap-1.5 rounded-[4px] border bg-white px-2 py-1"
            :class="error ? 'border-red-500' : 'border-[#8c8f94] focus-within:border-indigo-600'"
        >
            <span
                v-for="(tag, index) in model"
                :key="tag"
                class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2 py-0.5 text-[13px] text-indigo-800"
            >
                {{ tag }}
                <button type="button" class="text-indigo-500 hover:text-red-600" :aria-label="`Bỏ thẻ ${tag}`" @click="remove(index)">
                    &times;
                </button>
            </span>

            <input
                v-model="draft"
                type="text"
                :list="listId"
                maxlength="100"
                class="min-w-[8rem] flex-1 border-0 bg-transparent p-0 py-0.5 text-[14px] leading-5 text-slate-800 focus:outline-none focus:ring-0"
                placeholder="Gõ tên thẻ rồi nhấn Enter"
                @keydown="onKeydown"
                @blur="commit"
            />
            <datalist :id="listId">
                <option v-for="name in suggestions" :key="name" :value="name" />
            </datalist>
        </div>

        <p v-if="hint && !error" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>
