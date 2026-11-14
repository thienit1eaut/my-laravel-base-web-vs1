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
import QuickNumber from '../../Components/UI/QuickNumber.vue';
import { usePermission } from '../../Composables/usePermission.js';

const { can } = usePermission();
// Sửa nhanh ngay trên dòng: cần quyền sửa module; server kiểm tra lại (AdminController::quickUpdate)
const canQuick = () => can('products.update');
const quickUrl = (row) => `/admin/products/${row.id}/quick`;

defineOptions({ layout: AdminLayout });

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const columns = [
    { key: 'image', label: 'Hình ảnh', class: 'w-20' },
    { key: 'name', label: 'Tên sản phẩm', class: 'font-medium' },
    { key: 'sku', label: 'SKU', class: 'font-mono text-slate-500' },
    { key: 'category', label: 'Danh mục' },
    { key: 'brand', label: 'Thương hiệu' },
    { key: 'price', label: 'Giá' },
    { key: 'stock', label: 'Tồn kho' },
    { key: 'publisher', label: 'Người đăng' },
    { key: 'is_active', label: 'Trạng thái' },
];

const money = (value) => new Intl.NumberFormat('vi-VN').format(value) + ' đ';

const selected = ref([]);
const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? '');

const reload = () => {
    const params = {};
    if (search.value) params.search = search.value;
    if (categoryId.value) params.category_id = categoryId.value;

    router.get('/admin/products', params, { preserveState: true, replace: true });
};

// Tìm kiếm tự động sau khi ngừng gõ 300ms; đổi danh mục thì lọc ngay
let timer;
watch(search, () => {
    clearTimeout(timer);
    timer = setTimeout(reload, 300);
});
watch(categoryId, reload);

const remove = (product) => {
    if (confirm(`Xóa sản phẩm "${product.name}"?`)) {
        router.delete(`/admin/products/${product.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Sản phẩm" />

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <SearchInput v-model="search" placeholder="Tìm theo tên hoặc SKU..." />
        <SelectField v-model="categoryId" :options="categories" placeholder="Tất cả danh mục" class="w-48" />
        <Button v-if="can('products.create')" href="/admin/products/create" class="ml-auto">
            Thêm sản phẩm
        </Button>
    </div>

    <DataTable
        v-model:selected="selected"
        :columns="columns"
        :rows="products.data"
        empty-text="Chưa có sản phẩm nào."
    >
        <template #cell-image="{ row }">
            <img v-if="row.image_url" :src="row.image_url" alt="" class="h-12 w-12 rounded object-cover" />
            <span v-else class="flex h-12 w-12 items-center justify-center rounded bg-slate-200 text-slate-400">
                <Icon name="photo" size="h-4 w-4" />
            </span>
        </template>

        <template #cell-price="{ row }">
            <span v-if="row.sale_price !== null && row.sale_price < row.price">
                <span class="font-medium text-red-600">{{ money(row.sale_price) }}</span>
                <span class="ml-1 text-xs text-slate-400 line-through">{{ money(row.price) }}</span>
            </span>
            <template v-else>{{ money(row.price) }}</template>
        </template>

        <template #cell-stock="{ row }">
            <QuickNumber v-if="canQuick('stock')" :url="quickUrl(row)" field="stock" :value="row.stock" width="w-20" />
            <template v-else>{{ row.stock }}</template>
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
                :edit-href="can('products.update') ? `/admin/products/${row.id}/edit` : undefined"
                :deletable="can('products.delete')"
                @delete="remove(row)"
            />
        </template>
    </DataTable>

    <Pagination :links="products.links" />
</template>
