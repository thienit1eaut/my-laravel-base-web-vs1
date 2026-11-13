<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Button from '../../Components/UI/Button.vue';
import Icon from '../../Components/UI/Icon.vue';
import Modal from '../../Components/UI/Modal.vue';
import Pagination from '../../Components/UI/Pagination.vue';
import SearchInput from '../../Components/Fields/SearchInput.vue';
import InputField from '../../Components/Fields/InputField.vue';
import { usePermission } from '../../Composables/usePermission.js';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    medias: Object,
    filters: Object,
    counts: Object,
    trashCount: Number,
    accept: String,
    types: Array,
});

const { can } = usePermission();

const typeIcons = { image: 'photo', document: 'document', audio: 'music', video: 'video' };

/* ---------- Tìm kiếm và lọc theo loại tệp ---------- */
const search = ref(props.filters.search ?? '');
const type = ref(props.filters.type ?? '');

const reload = () => {
    const query = {};
    if (search.value) query.search = search.value;
    if (type.value) query.type = type.value;

    router.get('/admin/medias', query, { preserveState: true, replace: true });
};

// Tìm kiếm tự chạy sau khi ngừng gõ 300ms
let timer;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(reload, 300);
});

const setType = (value) => {
    type.value = value;
    reload();
};

const tabs = computed(() => [
    { value: '', label: 'Tất cả', count: props.counts.all },
    ...props.types.map((t) => ({ value: t.value, label: t.label, count: props.counts[t.value] })),
]);

const limitsHint = computed(() => props.types.map((t) => `${t.label} ≤ ${t.max_mb} MB`).join(' · '));

/* ---------- Tải tệp lên ---------- */
const fileInput = ref(null);
const dragging = ref(false);
const upload = useForm({ files: [] });
const uploadErrors = computed(() => Object.values(upload.errors));

const send = (fileList) => {
    const files = Array.from(fileList ?? []);
    if (files.length === 0 || !can('medias.create')) return;

    upload.clearErrors();
    upload.files = files;
    upload.post('/admin/medias', {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => upload.reset(),
        onFinish: () => {
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

const onPick = (event) => send(event.target.files);
const onDrop = (event) => {
    dragging.value = false;
    send(event.dataTransfer.files);
};

/* ---------- Chọn nhiều tệp để chuyển vào thùng rác ---------- */
const selected = ref([]);

// Khi dữ liệu đổi (chuyển trang, tìm kiếm), bỏ các tệp đã chọn không còn hiển thị
watch(
    () => props.medias.data,
    (rows) => {
        const keys = new Set(rows.map((row) => row.id));
        selected.value = selected.value.filter((id) => keys.has(id));
    },
);

const allChecked = computed(
    () => props.medias.data.length > 0 && props.medias.data.every((m) => selected.value.includes(m.id)),
);
const toggleAll = () => {
    selected.value = allChecked.value ? [] : props.medias.data.map((m) => m.id);
};

const trashMany = (ids, message) => {
    if (!confirm(message)) return;

    router.delete('/admin/medias', {
        data: { ids },
        preserveScroll: true,
        onSuccess: () => {
            selected.value = selected.value.filter((id) => !ids.includes(id));
        },
    });
};

const trashSelected = () =>
    trashMany([...selected.value], `Chuyển ${selected.value.length} tệp đã chọn vào thùng rác?`);

/* ---------- Cửa sổ chi tiết ---------- */
const activeId = ref(null);
// Lấy từ dữ liệu hiện tại: sau khi chuyển vào thùng rác, cửa sổ tự đóng
const active = computed(() => props.medias.data.find((m) => m.id === activeId.value) ?? null);

const edit = useForm({ title: '', alt: '' });
const copiedUrl = ref(null);

const open = (media) => {
    activeId.value = media.id;
    edit.title = media.title ?? '';
    edit.alt = media.alt ?? '';
    edit.clearErrors();
    copiedUrl.value = null;
};

const close = () => {
    activeId.value = null;
};

const save = () => {
    edit.put(`/admin/medias/${active.value.id}`, {
        preserveScroll: true,
        onSuccess: close,
    });
};

const trashActive = () => trashMany([active.value.id], `Chuyển "${active.value.name}" vào thùng rác?`);

const copyUrl = async (url) => {
    try {
        await navigator.clipboard.writeText(url);
        copiedUrl.value = url;
        setTimeout(() => {
            if (copiedUrl.value === url) copiedUrl.value = null;
        }, 2000);
    } catch {
        // Trình duyệt không cho ghi clipboard: ô URL tự chọn sẵn khi bấm vào để Ctrl+C
    }
};
</script>

<template>
    <Head title="Thư viện ảnh" />

    <!-- Khu vực tải tệp lên -->
    <div v-if="can('medias.create')" class="mb-3">
        <div
            role="button"
            tabindex="0"
            class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded border-2 border-dashed px-4 py-5 text-center text-[13px] transition-colors"
            :class="
                dragging
                    ? 'border-indigo-600 bg-indigo-50 text-indigo-700'
                    : 'border-slate-300 bg-white text-slate-500 hover:border-indigo-600'
            "
            @click="fileInput.click()"
            @keydown.enter="fileInput.click()"
            @dragover.prevent="dragging = true"
            @dragleave.prevent="dragging = false"
            @drop.prevent="onDrop"
        >
            <Icon name="upload" size="h-6 w-6" class="pointer-events-none" />
            <span class="pointer-events-none">
                Kéo thả tệp vào đây hoặc <span class="text-indigo-600 underline">chọn từ máy</span>
            </span>
            <span class="pointer-events-none text-xs text-slate-400">
                Ảnh, PDF, MP3/M4A, MP4/WebM · {{ limitsHint }} · tối đa 20 tệp mỗi lần
            </span>
        </div>

        <input ref="fileInput" type="file" multiple :accept="accept" class="hidden" @change="onPick" />

        <div v-if="upload.processing" class="mt-2 h-1.5 overflow-hidden rounded bg-slate-200">
            <div
                class="h-full bg-indigo-600 transition-all"
                :style="{ width: (upload.progress?.percentage ?? 0) + '%' }"
            />
        </div>

        <ul v-if="uploadErrors.length" class="mt-2 space-y-0.5 text-xs text-red-600">
            <li v-for="(error, index) in uploadErrors" :key="index">{{ error }}</li>
        </ul>
    </div>

    <!-- Lọc theo loại tệp -->
    <div class="mb-3 flex flex-wrap items-center gap-x-1 gap-y-1 text-[13px]">
        <template v-for="(tab, index) in tabs" :key="tab.value">
            <span v-if="index > 0" class="text-slate-300">|</span>
            <button
                type="button"
                :class="type === tab.value ? 'font-semibold text-slate-800' : 'text-indigo-600 hover:text-indigo-800'"
                @click="setType(tab.value)"
            >
                {{ tab.label }} <span class="text-slate-500">({{ tab.count }})</span>
            </button>
        </template>
    </div>

    <!-- Thanh công cụ -->
    <div class="mb-3 flex flex-wrap items-center gap-3">
        <SearchInput v-model="search" placeholder="Tìm theo tên, tiêu đề..." />

        <label v-if="can('medias.delete') && medias.data.length" class="flex items-center gap-1.5 text-[13px]">
            <input type="checkbox" :checked="allChecked" @change="toggleAll" />
            Chọn tất cả
        </label>

        <Button v-if="selected.length" variant="danger" @click="trashSelected">
            Chuyển {{ selected.length }} tệp vào thùng rác
        </Button>

        <div class="ml-auto flex items-center gap-3">
            <span class="text-xs text-slate-500">Tổng: {{ medias.total }} tệp</span>
            <Button v-if="can('medias.trash.view')" variant="secondary" href="/admin/medias/trash">
                <Icon name="trash" size="h-3.5 w-3.5" /> Thùng rác ({{ trashCount }})
            </Button>
        </div>
    </div>

    <!-- Lưới tệp -->
    <div
        v-if="medias.data.length"
        class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8"
    >
        <div
            v-for="media in medias.data"
            :key="media.id"
            class="relative aspect-square overflow-hidden rounded border bg-slate-100"
            :class="selected.includes(media.id) ? 'border-indigo-600 ring-2 ring-indigo-600' : 'border-slate-300'"
        >
            <button type="button" class="block h-full w-full" :title="media.name" @click="open(media)">
                <img
                    v-if="media.type === 'image'"
                    :src="media.thumb_url"
                    :alt="media.alt ?? media.name"
                    loading="lazy"
                    class="h-full w-full object-cover"
                />
                <span v-else class="flex h-full flex-col items-center justify-center gap-1 p-2 text-slate-500">
                    <Icon :name="typeIcons[media.type]" size="h-8 w-8" />
                    <span class="text-[11px] font-semibold uppercase">{{ media.extension }}</span>
                    <span class="line-clamp-2 break-all text-center text-[11px]">{{ media.name }}</span>
                </span>
            </button>

            <label
                v-if="can('medias.delete')"
                class="absolute left-1 top-1 flex rounded bg-white/90 p-1 shadow"
                @click.stop
            >
                <input v-model="selected" type="checkbox" :value="media.id" aria-label="Chọn tệp" />
            </label>
        </div>
    </div>

    <div v-else class="rounded border border-slate-300 bg-white py-10 text-center text-[13px] text-slate-500">
        {{ filters.search || filters.type ? 'Không tìm thấy tệp nào.' : 'Thư viện chưa có tệp nào.' }}
    </div>

    <Pagination :links="medias.links" />

    <!-- Cửa sổ chi tiết -->
    <Modal :show="!!active" :title="active?.name" @close="close">
        <div v-if="active" class="grid gap-4 md:grid-cols-[1fr_18rem]">
            <!-- Xem trước theo loại tệp -->
            <div class="flex min-h-40 items-center justify-center rounded bg-slate-100 p-2">
                <img
                    v-if="active.type === 'image'"
                    :src="active.url"
                    :alt="active.alt ?? active.name"
                    class="max-h-[60vh] max-w-full object-contain"
                />
                <video
                    v-else-if="active.type === 'video'"
                    :src="active.url"
                    controls
                    preload="metadata"
                    class="max-h-[60vh] max-w-full"
                />
                <div v-else-if="active.type === 'audio'" class="w-full space-y-3 p-4 text-center">
                    <Icon name="music" size="h-10 w-10" class="mx-auto text-slate-400" />
                    <audio :src="active.url" controls preload="metadata" class="w-full" />
                </div>
                <div v-else class="space-y-2 p-6 text-center">
                    <Icon name="document" size="h-12 w-12" class="mx-auto text-slate-400" />
                    <a
                        :href="active.url"
                        target="_blank"
                        rel="noopener"
                        class="text-indigo-600 underline hover:text-indigo-800"
                    >
                        Mở tệp {{ active.extension.toUpperCase() }} trong tab mới
                    </a>
                </div>
            </div>

            <div class="max-h-[70vh] space-y-3 overflow-y-auto pr-1 text-[13px]">
                <dl class="space-y-1 text-slate-600">
                    <div class="flex justify-between gap-2">
                        <dt>Loại tệp</dt>
                        <dd class="text-slate-800">{{ active.type_label }} ({{ active.extension }})</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt>Dung lượng</dt>
                        <dd class="text-slate-800">{{ active.size_label }}</dd>
                    </div>
                    <div v-if="active.width" class="flex justify-between gap-2">
                        <dt>Số đo</dt>
                        <dd class="text-slate-800">{{ active.width }} × {{ active.height }} px</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt>Tải lên bởi</dt>
                        <dd class="text-slate-800">{{ active.uploaded_by ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt>Ngày tải</dt>
                        <dd class="text-slate-800">{{ active.created_at }}</dd>
                    </div>
                </dl>

                <!-- URL: tệp gốc, WebP, thumbnail -->
                <div v-for="item in active.urls" :key="item.url">
                    <label class="block font-semibold text-slate-800">{{ item.label }}</label>
                    <div class="mt-1 flex gap-1">
                        <input
                            :value="item.url"
                            readonly
                            class="min-w-0 flex-1 rounded-[4px] border border-[#8c8f94] bg-slate-50 px-2 py-1 text-xs text-slate-600"
                            @focus="$event.target.select()"
                        />
                        <Button variant="secondary" @click="copyUrl(item.url)">
                            {{ copiedUrl === item.url ? 'Đã chép' : 'Sao chép' }}
                        </Button>
                    </div>
                </div>

                <form class="space-y-3" @submit.prevent="save">
                    <InputField
                        v-model="edit.title"
                        name="title"
                        label="Tiêu đề"
                        :error="edit.errors.title"
                        :disabled="!can('medias.update')"
                    />
                    <InputField
                        v-if="active.type === 'image'"
                        v-model="edit.alt"
                        name="alt"
                        label="Văn bản thay thế (alt)"
                        hint="Mô tả ngắn nội dung ảnh, tốt cho SEO."
                        :error="edit.errors.alt"
                        :disabled="!can('medias.update')"
                    />

                    <div class="flex items-center gap-2 pt-1">
                        <Button v-if="can('medias.update')" type="submit" :disabled="edit.processing">Lưu</Button>
                        <Button v-if="can('medias.delete')" variant="danger" class="ml-auto" @click="trashActive">
                            Chuyển vào thùng rác
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </Modal>
</template>