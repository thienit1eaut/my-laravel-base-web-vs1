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
import QuickToggle from '../../Components/UI/QuickToggle.vue';
import QuickNumber from '../../Components/UI/QuickNumber.vue';
import { usePermission } from '../../Composables/usePermission.js';

const { can } = usePermission();
// Sửa nhanh ngay trên dòng: cần quyền sửa module; server kiểm tra lại (AdminController::quickUpdate)
const canQuick = () => can('article-categories.update');
const quickUrl = (row) => `/admin/article-categories/${row.id}/quick`;

defineOptions({ layout: AdminLayout });

const props = defineProps({
    categories: Object,
    filters: Object,
});

const columns = [
    { key: 'image', label: 'Hình ảnh', class: 'w-20' },
    { key: 'name', label: 'Tên', class: 'font-medium' },
    { key: 'slug', label: 'Slug', class: 'font-mono text-slate-500' },
    { key: 'parent', label: 'Danh mục cha' },
    { key: 'articles_count', label: 'Số bài viết' },
    { key: 'sort_order', label: 'Thứ tự' },
    { key: 'is_active', label: 'Trạng thái' },
];

const selected = ref([]);
const search = ref(props.filters.search ?? '');

let timer;
watch(search, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/admin/article-categories', value ? { search: value } : {}, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const remove = (category) => {
    if (confirm(`Xóa danh mục "${category.name}"?`)) {
        router.delete(`/admin/article-categories/${category.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Danh mục bài viết" />

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <SearchInput v-model="search" placeholder="Tìm theo tên..." />
        <Button v-if="can('article-categories.create')" href="/admin/article-categories/create" class="ml-auto">
            Thêm danh mục
        </Button>
    </div>

    <DataTable
        v-model:selected="selected"
        :columns="columns"
        :rows="categories.data"
        empty-text="Chưa có danh mục bài viết nào."
    >
        <template #cell-image="{ row }">
            <img v-if="row.image_url" :src="row.image_url" alt="" class="h-12 w-12 rounded object-cover" />
            <span v-else class="flex h-12 w-12 items-center justify-center rounded bg-slate-200 text-slate-400">
                <Icon name="photo" size="h-4 w-4" />
            </span>
        </template>

        <template #cell-sort_order="{ row }">
            <QuickNumber v-if="canQuick('sort_order')" :url="quickUrl(row)" field="sort_order" :value="row.sort_order" />
            <template v-else>{{ row.sort_order }}</template>
        </template>

        <template #cell-is_active="{ row }">
            <QuickToggle
                v-if="canQuick('is_active')"
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
                :edit-href="can('article-categories.update') ? `/admin/article-categories/${row.id}/edit` : undefined"
                :deletable="can('article-categories.delete')"
                @delete="remove(row)"
            />
        </template>
    </DataTable>

    <Pagination :links="categories.links" />
</template>
