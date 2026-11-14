<script setup>
import { ref, watch } from 'vue';
import InputField from './InputField.vue';
import { slugify } from '../../Composables/useSlug.js';

// Ô nhập đường dẫn (slug): tự tạo từ ô "source" (thường là tên/tiêu đề) khi người dùng gõ,
// hoặc người dùng tự nhập. Đã tự nhập thì không bị ghi đè nữa (trừ khi bấm "Tạo lại từ tên").
// Khi sửa (editing = true) slug cũ được giữ nguyên để không làm đổi URL đã public (ảnh hưởng SEO).
const props = defineProps({
    name: { type: String, default: 'slug' },
    label: { type: String, default: 'Đường dẫn (slug)' },
    error: String,
    source: { type: String, default: '' },
    prefix: String,                          // ví dụ '/bai-viet/' để xem trước URL
    editing: { type: Boolean, default: false },
});

const model = defineModel({ type: String, default: '' });

const manual = ref(props.editing || (model.value !== '' && model.value !== slugify(props.source)));

watch(
    () => props.source,
    (value) => {
        if (!manual.value) {
            model.value = slugify(value);
        }
    },
);

// Đang gõ: loại ký tự không hợp lệ nhưng cho phép dấu '-' ở cuối để gõ tiếp được
const onInput = (raw) => {
    const clean = slugify(raw);
    model.value = clean && /[\s-]$/.test(raw) ? `${clean}-` : clean;
    manual.value = model.value !== '';
};

// Rời khỏi ô: chuẩn hóa hẳn; để trống thì quay lại tự tạo theo tên
const onBlur = () => {
    model.value = slugify(model.value);

    if (model.value === '') {
        manual.value = false;
        model.value = slugify(props.source);
    }
};

const regenerate = () => {
    manual.value = false;
    model.value = slugify(props.source);
};
</script>

<template>
    <div>
        <InputField
            :model-value="model"
            :name="name"
            :label="label"
            :error="error"
            autocomplete="off"
            placeholder="Tự tạo từ tên, hoặc tự nhập"
            @update:model-value="onInput"
            @blur="onBlur"
        />
        <div class="mt-1 flex flex-wrap items-center gap-x-3 text-xs text-slate-500">
            <span v-if="prefix && model" class="font-mono">{{ prefix }}{{ model }}</span>
            <button
                v-if="manual && source"
                type="button"
                class="text-indigo-600 hover:underline"
                @click="regenerate"
            >
                Tạo lại từ tên
            </button>
        </div>
    </div>
</template>
