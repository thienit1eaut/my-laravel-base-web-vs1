<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import DataTable from '../../Components/UI/DataTable.vue';
import RowActions from '../../Components/UI/RowActions.vue';
import DropdownItem from '../../Components/UI/DropdownItem.vue';
import Pagination from '../../Components/UI/Pagination.vue';
import Badge from '../../Components/UI/Badge.vue';
import Button from '../../Components/UI/Button.vue';
import { usePermission } from '../../Composables/usePermission.js';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    roles: Object,
    filters: Object,
});

const { can } = usePermission();

const columns = [
    { key: 'name', label: 'Vai trò' },
    { key: 'users_count', label: 'Số tài khoản' },
    { key: 'permissions_count', label: 'Số quyền' },
];

// Danh sách id các dòng đang được chọn (dùng cho thao tác hàng loạt sau này)
const selected = ref([]);

const search = ref(props.filters.search ?? '');

let timer;
watch(search, (value) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        router.get('/admin/roles', value ? { search: value } : {}, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const remove = (role) => {
    if (confirm(`Xóa vai trò "${role.name}"?`)) {
        router.delete(`/admin/roles/${role.id}`, { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Vai trò" />

    <div class="mb-3 flex flex-wrap items-center gap-2">
        <SearchInput v-model="search" placeholder="Tìm theo tên..." />
        <Button v-if="can('roles.create')" href="/admin/roles/create" class="ml-auto">Thêm vai trò</Button>
    </div>

    <DataTable
        v-model:selected="selected"
        :columns="columns"
        :rows="roles.data"
        empty-text="Chưa có vai trò nào."
    >
        <template #cell-name="{ row }">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-indigo-600">{{ row.name }}</span>
                <Badge v-if="row.is_system" variant="info">Hệ thống</Badge>
            </div>
            <div v-if="row.description" class="text-xs text-slate-500">{{ row.description }}</div>
        </template>

        <template #cell-permissions_count="{ row }">
            <span v-if="row.is_system">Toàn quyền</span>
            <span v-else>{{ row.permissions_count }}</span>
        </template>

        <template #actions="{ row }">
            <RowActions
                :edit-href="can('roles.update') ? `/admin/roles/${row.id}/edit` : undefined"
                :deletable="can('roles.delete') && !row.is_system"
                @delete="remove(row)"
            >
                <template v-if="can('roles.permissions.update') && !row.is_system" #more>
                    <DropdownItem :href="`/admin/roles/${row.id}/permissions`">Phân quyền</DropdownItem>
                </template>
            </RowActions>
        </template>
    </DataTable>

    <Pagination :links="roles.links" />
</template>