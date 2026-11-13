<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Button from '../../Components/UI/Button.vue';
import DataTable from '../../Components/UI/DataTable.vue';
import Icon from '../../Components/UI/Icon.vue';
import Pagination from '../../Components/UI/Pagination.vue';
import { usePermission } from '../../Composables/usePermission.js';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    medias: Object,
});

const { can } = usePermission();

const typeIcons = { image: 'photo', document: 'document', audio: 'music', video: 'video' };

const columns = [
    { key: 'preview', label: '', class: 'w-14' },
    { key: 'name', label: 'Tên tệp' },
    { key: 'type_label', label: 'Loại' },
    { key: 'size_label', label: 'Dung lượng' },
    { key: 'deleted_at', label: 'Xóa lúc' },
];

const selected = ref([]);

const done = {
    preserveScroll: true,
    onSuccess: () => {
        selected.value = [];
    },
};

const restore = (ids) => router.post('/admin/medias/trash/restore', { ids }, done);

const destroy = (ids) => {
    if (!confirm(`Xóa vĩnh viễn ${ids.length} tệp? Tệp gốc, WebP và thumbnail sẽ bị xóa, không thể khôi phục.`)) return;

    router.delete('/admin/medias/trash', { data: { ids }, ...done });
};

const empty = () => {
    if (!confirm('Dọn sạch thùng rác? Tất cả tệp trong thùng rác sẽ bị xóa vĩnh viễn, không thể khôi phục.')) return;

    router.delete('/admin/medias/trash/empty', done);
};
</script>

<template>
    <Head title="Thùng rác" />

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <Button variant="secondary" href="/admin/medias">← Về thư viện</Button>
        <span class="text-xs text-slate-500">Tệp trong thùng rác vẫn chiếm dung lượng cho đến khi xóa vĩnh viễn.</span>

        <Button
            v-if="can('medias.trash.empty') && medias.total > 0"
            variant="danger"
            class="ml-auto"
            @click="empty"
        >
            Dọn sạch thùng rác
        </Button>
    </div>

    <DataTable
        v-model:selected="selected"
        :columns="columns"
        :rows="medias.data"
        :selectable="can('medias.trash.restore') || can('medias.trash.delete')"
        empty-text="Thùng rác trống."
    >
        <template #cell-preview="{ row }">
            <img v-if="row.type === 'image'" :src="row.thumb_url" alt="" class="h-10 w-10 rounded object-cover" />
            <span v-else class="flex h-10 w-10 items-center justify-center rounded bg-slate-200 text-slate-500">
                <Icon :name="typeIcons[row.type]" size="h-5 w-5" />
            </span>
        </template>

        <template #cell-name="{ row }">
            <div class="font-semibold text-slate-800">{{ row.name }}</div>
            <div v-if="row.title" class="text-xs text-slate-500">{{ row.title }}</div>
        </template>

        <template #bulk="{ selected: ids }">
            <Button v-if="can('medias.trash.restore')" variant="secondary" @click="restore(ids)">Khôi phục</Button>
            <Button v-if="can('medias.trash.delete')" variant="danger" @click="destroy(ids)">
                Xóa vĩnh viễn
            </Button>
        </template>

        <template #actions="{ row }">
            <div class="flex justify-end gap-1">
                <Button v-if="can('medias.trash.restore')" variant="secondary" @click="restore([row.id])">
                    Khôi phục
                </Button>
                <Button v-if="can('medias.trash.delete')" variant="danger" @click="destroy([row.id])">
                    Xóa vĩnh viễn
                </Button>
            </div>
        </template>
    </DataTable>

    <Pagination :links="medias.links" />
</template>