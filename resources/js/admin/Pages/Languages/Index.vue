<script setup>
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import DataTable from '../../Components/UI/DataTable.vue';
import RowActions from '../../Components/UI/RowActions.vue';
import Badge from '../../Components/UI/Badge.vue';
import Button from '../../Components/UI/Button.vue';
import QuickToggle from '../../Components/UI/QuickToggle.vue';
import QuickNumber from '../../Components/UI/QuickNumber.vue';
import { usePermission } from '../../Composables/usePermission.js';

const { can } = usePermission();
const canQuick = () => can('languages.update');
const quickUrl = (row) => `/admin/languages/${row.id}/quick`;

defineOptions({ layout: AdminLayout });

defineProps({
    languages: Array,
});

const columns = [
    { key: 'name', label: 'Ngôn ngữ', class: 'font-medium' },
    { key: 'code', label: 'Mã', class: 'font-mono text-slate-500' },
    { key: 'prefix', label: 'Tiền tố link', class: 'font-mono text-slate-500' },
    { key: 'is_default', label: 'Ngôn ngữ chính' },
    { key: 'sort_order', label: 'Thứ tự' },
    { key: 'is_active', label: 'Trạng thái' },
];

const makeDefault = (language) => {
    if (confirm(`Đặt "${language.name}" làm ngôn ngữ chính? Bài chưa dịch sẽ hiển thị theo ngôn ngữ này.`)) {
        router.patch(`/admin/languages/${language.id}/default`, {}, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Ngôn ngữ" />

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <p class="text-sm text-slate-500">
            Ngôn ngữ đang bật sẽ xuất hiện trên website và trong form soạn nội dung. Không có chức năng xóa, hãy tắt ngôn ngữ không dùng.
        </p>
        <Button v-if="can('languages.create')" href="/admin/languages/create" class="ml-auto">Thêm ngôn ngữ</Button>
    </div>

    <DataTable :columns="columns" :rows="languages" empty-text="Chưa có ngôn ngữ nào.">
        <template #cell-is_default="{ row }">
            <Badge v-if="row.is_default" variant="success">Ngôn ngữ chính</Badge>
            <Button v-else-if="can('languages.default')" variant="secondary" @click="makeDefault(row)">Đặt làm chính</Button>
        </template>

        <template #cell-sort_order="{ row }">
            <QuickNumber v-if="canQuick()" :url="quickUrl(row)" field="sort_order" :value="row.sort_order" />
            <template v-else>{{ row.sort_order }}</template>
        </template>

        <template #cell-is_active="{ row }">
            <QuickToggle
                v-if="canQuick()"
                :url="quickUrl(row)"
                field="is_active"
                :value="row.is_active"
                :disabled="row.is_default"
                label="Đang bật"
            />
            <Badge v-else :variant="row.is_active ? 'success' : 'gray'">{{ row.is_active ? 'Đang bật' : 'Tắt' }}</Badge>
        </template>

        <template #actions="{ row }">
            <RowActions :edit-href="can('languages.update') ? `/admin/languages/${row.id}/edit` : undefined" />
        </template>
    </DataTable>
</template>
