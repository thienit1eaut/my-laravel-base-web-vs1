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
import SelectField from '../../Components/Fields/SelectField.vue';
import Icon from '../../Components/UI/Icon.vue';
import QuickToggle from '../../Components/UI/QuickToggle.vue';
import { usePermission } from '../../Composables/usePermission.js';

const { can } = usePermission();
// Sửa nhanh ngay trên dòng: cần quyền sửa module; server kiểm tra lại (AdminController::quickUpdate)
const canQuick = () => can('articles.update');
const quickUrl = (row) => `/admin/articles/${row.id}/quick`;

defineOptions({ layout: AdminLayout });

const props = defineProps({
    articles: Object,
    categories: Array,
    filters: Object,
});

const columns = [
    { key: 'image', label: 'Hình ảnh', class: 'w-20' },
    { key: 'title', label: 'Tiêu đề', class: 'font-medium' },
    { key: 'category', label: 'Danh mục' },
    { key: 'publisher', label: 'Người đăng' },
    { key: 'published_at', label: 'Ngày đăng' },
    { key: 'is_active', label: 'Trạng thái' },
];

const selected = ref([]);
const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? '');

const reload = () => {
    const params = {};
    if (search.value) params.search = search.value;
    if (categoryId.value) params.category_id = categoryId.value;

    router.get('/admin/articles', params, { preserveState: true, replace: true });
};

// Tìm kiếm tự động sau khi ngừng gõ 300ms; đổi danh mục thì lọc ngay
let timer;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(reload, 300);
});
watch(categoryId, reload);

const remove = (article) => {
    if (confirm(`Xóa bài viết "${article.title}"?`)) {
        router.delete(`/admin/articles/${article.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Bài viết" />

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <SearchInput v-model="search" placeholder="Tìm theo tiêu đề..." />
        <SelectField v-model="categoryId" :options="categories" placeholder="Tất cả danh mục" class="w-48" />
        <Button v-if="can('articles.create')" href="/admin/articles/create" class="ml-auto">
            Thêm bài viết
        </Button>
    </div>

    <DataTable
        v-model:selected="selected"
        :columns="columns"
        :rows="articles.data"
        empty-text="Chưa có bài viết nào."
    >
        <template #cell-image="{ row }">
            <img v-if="row.image_url" :src="row.image_url" alt="" class="h-12 w-12 rounded object-cover" />
            <span v-else class="flex h-12 w-12 items-center justify-center rounded bg-slate-200 text-slate-400">
                <Icon name="photo" size="h-4 w-4" />
            </span>
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
                :view-href="row.public_url"
                :view-disabled-reason="row.public_issue ?? undefined"
                view-external
                :edit-href="can('articles.update') ? `/admin/articles/${row.id}/edit` : undefined"
                :deletable="can('articles.delete')"
                @delete="remove(row)"
            />
        </template>
    </DataTable>

    <Pagination :links="articles.links" />
</template>
