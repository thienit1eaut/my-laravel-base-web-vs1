<script setup>
import { ref } from 'vue';
import Button from '../UI/Button.vue';
import Icon from '../UI/Icon.vue';
import MediaPicker from '../UI/MediaPicker.vue';
import { usePermission } from '../../Composables/usePermission.js';

const props = defineProps({
    label: String,
    error: String,
    hint: String,
    currentUrl: String, // ảnh đang có (khi sửa)
});

const model = defineModel(); // id ảnh trong thư viện, null nếu chưa chọn

const { can } = usePermission();

const url = ref(props.currentUrl ?? null);
const picking = ref(false);

const onSelect = (media) => {
    model.value = media.id;
    url.value = media.thumb_url ?? media.url;
    picking.value = false;
};

const clear = () => {
    model.value = null;
    url.value = null;
};
</script>

<template>
    <div>
        <span v-if="label" class="block text-[14px] font-semibold text-slate-800">{{ label }}</span>

        <div class="mt-1 flex items-center gap-3">
            <div
                class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded border border-slate-300 bg-slate-50 text-slate-400"
            >
                <img v-if="url" :src="url" alt="" class="h-full w-full object-cover" />
                <Icon v-else name="photo" size="h-8 w-8" />
            </div>

            <div class="space-y-1.5">
                <div class="flex flex-wrap gap-2">
                    <Button v-if="can('medias.view')" variant="secondary" @click="picking = true">
                        Chọn từ thư viện
                    </Button>
                    <Button v-if="url" variant="danger" @click="clear">Bỏ ảnh</Button>
                </div>
                <p v-if="!can('medias.view')" class="text-xs text-slate-500">
                    Bạn không có quyền xem Thư viện ảnh nên không thể chọn ảnh.
                </p>
            </div>
        </div>

        <p v-if="hint && !error" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>

        <MediaPicker :show="picking" :selected-id="model ?? undefined" @close="picking = false" @select="onSelect" />
    </div>
</template>