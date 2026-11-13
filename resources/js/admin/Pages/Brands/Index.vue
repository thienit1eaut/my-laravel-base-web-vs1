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
import Icon from '../../Components/UI/Icon.vue';
import { usePermission } from '../../Composables/usePermission.js';

const { can } = usePermission();

defineOptions({ layout: AdminLayout });

const props = defineProps({
    brands: Object,
    filters: Object,
});

const columns = [
    { key: 'image', label: 'Hình ảnh', class: 'w-20' },
    { key: 'name', label: 'Tên', class: 'font-medium' },
    { key: 'slug', label: 'Slug', class: 'font-mono text-slate-500' },
    { key: 'sort_order', label: 'Thứ tự' },
    { key: 'is_active', label: 'Trạng thái' },
];

// Danh sách id các dòng đang được chọn (dùng cho thao tác hàng loạt sau này)
const selected = ref([]);

const search = ref(props.filters.search ?? '');

// Tìm kiếm tự động sau khi ngừng gõ 300ms
let timer;
watch(search, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/admin/brands', value ? { search: value } : {}, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const remove = (brand) => {
    if (confirm(`Xóa thương hiệu "${brand.name}"?`)) {
        router.delete(`/admin/brands/${brand.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Thương hiệu" />

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <SearchInput v-model="search" placeholder="Tìm theo tên..." />
        <Button v-if="can('brands.create')" href="/admin/brands/create" class="ml-auto">
            Thêm thương hiệu
        </Button>
    </div>

    <DataTable
        v-model:selected="selected"
        :columns="columns"
        :rows="brands.data"
        empty-text="Chưa có thương hiệu nào."
    >
        <template #cell-image="{ row }">
            <img v-if="row.image_url" :src="row.image_url" alt="" class="h-12 w-12 rounded object-cover" />
            <span v-else class="flex h-12 w-12 items-center justify-center rounded bg-slate-200 text-slate-400">
                <Icon name="photo" size="h-4 w-4" />
            </span>
        </template>

        <template #cell-is_active="{ row }">
            <Badge :variant="row.is_active ? 'success' : 'gray'">
                {{ row.is_active ? 'Hiển thị' : 'Ẩn' }}
            </Badge>
        </template>

        <template #actions="{ row }">
            <RowActions
                :edit-href="can('brands.update') ? `/admin/brands/${row.id}/edit` : undefined"
                :deletable="can('brands.delete')"
                @delete="remove(row)"
            />
        </template>
    </DataTable>

    <Pagination :links="brands.links" />
</template>