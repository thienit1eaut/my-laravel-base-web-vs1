<script setup>
import { ref, watch } from 'vue';
import Button from './Button.vue';
import Modal from './Modal.vue';
import SearchInput from '../Fields/SearchInput.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    selectedId: Number, // ảnh đang được chọn (để đánh dấu)
});

const emit = defineEmits(['close', 'select']);

const items = ref([]);
const page = ref(1);
const lastPage = ref(1);
const search = ref('');
const loading = ref(false);
const failed = ref(false);

let controller;

const load = async () => {
    controller?.abort();
    const current = (controller = new AbortController());

    loading.value = true;
    failed.value = false;

    const params = new URLSearchParams({ picker: '1', type: 'image', page: page.value });
    if (search.value) params.set('search', search.value);

    try {
        const response = await fetch(`/admin/medias?${params}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: current.signal,
        });

        if (!response.ok) throw new Error(`HTTP ${response.status}`);

        const json = await response.json();
        items.value = json.data;
        lastPage.value = json.last_page;
    } catch (error) {
        if (error.name !== 'AbortError') failed.value = true;
    } finally {
        if (!current.signal.aborted) loading.value = false;
    }
};

// Mở cửa sổ thì tải trang đầu
watch(
    () => props.show,
    (visible) => {
        if (visible) {
            page.value = 1;
            load();
        }
    },
);

// Tìm kiếm tự chạy sau khi ngừng gõ 300ms
let timer;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        page.value = 1;
        load();
    }, 300);
});

const go = (target) => {
    page.value = target;
    load();
};
</script>

<template>
    <Modal :show="show" title="Chọn ảnh từ thư viện" max-width="max-w-4xl" @close="emit('close')">
        <div class="mb-3 flex items-center gap-3">
            <SearchInput v-model="search" placeholder="Tìm theo tên, tiêu đề..." />
            <span v-if="loading" class="text-xs text-slate-500">Đang tải...</span>
            <a href="/admin/medias" target="_blank" class="ml-auto text-xs text-indigo-600 hover:underline">
                Mở thư viện để tải ảnh mới
            </a>
        </div>

        <p v-if="failed" class="py-8 text-center text-[14px] text-red-600">
            Không tải được thư viện ảnh. Bạn có thể thiếu quyền xem Thư viện ảnh.
        </p>

        <div v-else-if="items.length" class="grid max-h-[55vh] grid-cols-3 gap-2 overflow-y-auto sm:grid-cols-5 md:grid-cols-6">
            <button
                v-for="media in items"
                :key="media.id"
                type="button"
                :title="media.name"
                class="aspect-square overflow-hidden rounded border"
                :class="
                    media.id === selectedId
                        ? 'border-indigo-600 ring-2 ring-indigo-600'
                        : 'border-slate-300 hover:border-indigo-600'
                "
                @click="emit('select', media)"
            >
                <img :src="media.thumb_url" :alt="media.name" loading="lazy" class="h-full w-full object-cover" />
            </button>
        </div>

        <p v-else-if="!loading" class="py-8 text-center text-[14px] text-slate-500">Không có ảnh nào.</p>

        <div v-if="lastPage > 1" class="mt-3 flex items-center justify-between text-[14px]">
            <Button variant="secondary" :disabled="page <= 1 || loading" @click="go(page - 1)">‹ Trước</Button>
            <span class="text-slate-500">Trang {{ page }} / {{ lastPage }}</span>
            <Button variant="secondary" :disabled="page >= lastPage || loading" @click="go(page + 1)">Sau ›</Button>
        </div>
    </Modal>
</template>