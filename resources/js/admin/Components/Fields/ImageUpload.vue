<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import Button from '../UI/Button.vue';

const props = defineProps({
    label: String,
    error: String,
    hint: String,
    currentUrl: String, // ảnh đang có (khi sửa)
});

const file = defineModel({ default: null }); // File vừa chọn
const removed = defineModel('removed', { type: Boolean, default: false }); // đánh dấu xóa ảnh hiện có

const fileInput = ref(null);
const previewUrl = ref(null);

const revoke = () => {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value);
};

watch(file, (value) => {
    revoke();
    previewUrl.value = value ? URL.createObjectURL(value) : null;
});

onBeforeUnmount(revoke);

// Ưu tiên ảnh vừa chọn, sau đó đến ảnh hiện có (trừ khi đã đánh dấu xóa)
const shownUrl = computed(() => previewUrl.value ?? (removed.value ? null : props.currentUrl));

const onPick = (event) => {
    file.value = event.target.files[0] ?? null;
    if (file.value) removed.value = false;
};

const clear = () => {
    if (file.value) {
        // Đang có ảnh mới chọn: bỏ lựa chọn đó
        file.value = null;
        if (fileInput.value) fileInput.value.value = '';
    } else if (props.currentUrl) {
        // Không có ảnh mới: đánh dấu xóa ảnh hiện có
        removed.value = true;
    }
};
</script>

<template>
    <div>
        <span v-if="label" class="block text-[13px] font-semibold text-slate-800">{{ label }}</span>

        <div class="mt-1 flex items-center gap-3">
            <div
                class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full border border-slate-300 bg-slate-50 text-center text-[11px] text-slate-400"
            >
                <img v-if="shownUrl" :src="shownUrl" alt="" class="h-full w-full object-cover" />
                <span v-else>Chưa có ảnh</span>
            </div>

            <div class="flex flex-wrap gap-2">
                <Button variant="secondary" @click="fileInput.click()">Chọn ảnh</Button>
                <Button v-if="shownUrl" variant="danger" @click="clear">Xóa ảnh</Button>
            </div>
        </div>

        <input
            ref="fileInput"
            type="file"
            accept="image/png,image/jpeg,image/webp"
            class="hidden"
            @change="onPick"
        />

        <p v-if="hint && !error" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>
    </div>
</template>