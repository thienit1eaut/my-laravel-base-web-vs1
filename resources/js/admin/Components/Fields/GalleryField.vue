<script setup>
import { ref } from 'vue';
import Button from '../UI/Button.vue';
import MediaPicker from '../UI/MediaPicker.vue';
import { usePermission } from '../../Composables/usePermission.js';

// Thư viện nhiều ảnh lấy từ Thư viện ảnh. Giá trị v-model là mảng id ảnh (đúng thứ tự hiển thị).
// "items" lưu thêm đường dẫn ảnh nhỏ để hiện trước; truyền "initial" là [{id, url}] khi sửa.
// Dùng: <GalleryField v-model="form.gallery_ids" :initial="product.gallery" :error="form.errors.gallery_ids" />
const props = defineProps({
    label: String,
    error: String,
    hint: String,
    initial: { type: Array, default: () => [] },
    max: { type: Number, default: 20 },
});

const model = defineModel({ type: Array, default: () => [] });

const { can } = usePermission();
const items = ref(props.initial.map((item) => ({ id: item.id, url: item.url })));
const picking = ref(false);

const sync = () => (model.value = items.value.map((item) => item.id));

const onSelect = (media) => {
    picking.value = false;

    if (items.value.length >= props.max || items.value.some((item) => item.id === media.id)) return;

    items.value.push({ id: media.id, url: media.thumb_url ?? media.url });
    sync();
};

const remove = (index) => {
    items.value.splice(index, 1);
    sync();
};

const move = (index, step) => {
    const target = index + step;
    if (target < 0 || target >= items.value.length) return;

    [items.value[index], items.value[target]] = [items.value[target], items.value[index]];
    sync();
};
</script>

<template>
    <div>
        <span v-if="label" class="block text-[14px] font-semibold text-slate-800">{{ label }}</span>

        <div class="mt-1 flex flex-wrap gap-2">
            <div
                v-for="(item, index) in items"
                :key="item.id"
                class="w-24 overflow-hidden rounded border border-slate-300 bg-white"
            >
                <img :src="item.url" alt="" class="h-24 w-24 object-cover" />
                <div class="flex items-center justify-between px-1 py-0.5 text-[13px]">
                    <button type="button" class="px-1 text-slate-600 hover:text-indigo-700 disabled:opacity-30" :disabled="index === 0" title="Lên trước" @click="move(index, -1)">&larr;</button>
                    <button type="button" class="px-1 text-red-600 hover:text-red-800" title="Bỏ ảnh" @click="remove(index)">&times;</button>
                    <button type="button" class="px-1 text-slate-600 hover:text-indigo-700 disabled:opacity-30" :disabled="index === items.length - 1" title="Ra sau" @click="move(index, 1)">&rarr;</button>
                </div>
            </div>

            <p v-if="!items.length" class="py-2 text-xs text-slate-500">Chưa có ảnh nào trong thư viện.</p>
        </div>

        <div class="mt-2">
            <Button v-if="can('medias.view')" variant="secondary" :disabled="items.length >= max" @click="picking = true">
                Thêm ảnh từ thư viện
            </Button>
            <p v-else class="text-xs text-slate-500">Bạn không có quyền xem Thư viện ảnh nên không thể chọn ảnh.</p>
        </div>

        <p v-if="hint && !error" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>

        <MediaPicker :show="picking" @close="picking = false" @select="onSelect" />
    </div>
</template>
