<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import DataTable from '../../Components/UI/DataTable.vue';
import RowActions from '../../Components/UI/RowActions.vue';
import Pagination from '../../Components/UI/Pagination.vue';
import Badge from '../../Components/UI/Badge.vue';
import Button from '../../Components/UI/Button.vue';
import SearchInput from '../../Components/Fields/SearchInput.vue';
import QuickToggle from '../../Components/UI/QuickToggle.vue';
import { usePermission } from '../../Composables/usePermission.js';

const { can } = usePermission();

defineOptions({ layout: AdminLayout });

const props = defineProps({
    tags: Object,
    filters: Object,
});

// Sửa nhanh ngay trên dòng: cần quyền sửa module; server kiểm tra lại (AdminController::quickUpdate)
const canQuick = () => can('product-tags.update');
const quickUrl = (row) => `/admin/product-tags/${row.id}/quick`;

const columns = [
    { key: 'name', label: 'Tên thẻ', class: 'font-medium' },
    { key: 'slug', label: 'Slug', class: 'font-mono text-slate-500' },
    { key: 'products_count', label: 'Số sản phẩm' },
    { key: 'is_active', label: 'Trạng thái' },
];

const selected = ref([]);
const search = ref(props.filters.search ?? '');

let timer;
watch(search, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/admin/product-tags', value ? { search: value } : {}, { preserveState: true, replace: true });
    }, 300);
});

const remove = (tag) => {
    const note = tag.products_count > 0 ? ` Thẻ đang gắn với ${tag.products_count} sản phẩm (sản phẩm không bị xóa).` : '';

    if (confirm(`Xóa thẻ "${tag.name}"?${note}`)) {
        router.delete(`/admin/product-tags/${tag.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Thẻ sản phẩm" />

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <SearchInput v-model="search" placeholder="Tìm theo tên..." />
        <Button v-if="can('product-tags.create')" href="/admin/product-tags/create" class="ml-auto">Thêm thẻ</Button>
    </div>

    <DataTable v-model:selected="selected" :columns="columns" :rows="tags.data" empty-text="Chưa có thẻ nào.">
        <template #cell-is_active="{ row }">
            <QuickToggle
                v-if="canQuick()"
                :url="quickUrl(row)"
                field="is_active"
                :value="row.is_active"
                label="Hiển thị"
            />
            <Badge v-else :variant="row.is_active ? 'success' : 'gray'">
                {{ row.is_active ? 'Hiển thị' : 'Ẩn' }}
            </Badge>
        </template>

        <template #actions="{ row }">
            <RowActions
                :view-href="row.public_url ?? undefined"
                view-external
                :edit-href="can('product-tags.update') ? `/admin/product-tags/${row.id}/edit` : undefined"
                :deletable="can('product-tags.delete')"
                @delete="remove(row)"
            />
        </template>
    </DataTable>

    <Pagination :links="tags.links" />
</template>
